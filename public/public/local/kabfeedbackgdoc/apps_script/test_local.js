/**
 * Runs Code.gs against an in-memory stand-in for the Spreadsheet service:
 *   node test_local.js
 * Covers the logic (columns, dedupe, batches, text safety, time tabs), not Google itself.
 */
'use strict';
const fs = require('fs');
const path = require('path');
const vm = require('vm');

let sheetIds = 0;
class Sheet {
  constructor(parent, name) {
    this.parent = parent; this.name = name; this.cells = new Map(); this.id = ++sheetIds;
    this.maxRows = 1000; this.maxCols = 26; this.frozen = 0; this.hidden = new Set(); this.widths = {};
  }
  getSheetId() { return this.id; }
  cell(r, c) {
    const k = r + ',' + c;
    if (!this.cells.has(k)) { this.cells.set(k, { value: '', note: '' }); }
    return this.cells.get(k);
  }
  peek(r, c) { return this.cells.get(r + ',' + c) || { value: '', note: '' }; }
  used(axis) {
    let max = 0;
    for (const [k, v] of this.cells) {
      if (v.value === '' || v.value === null || v.value === undefined) { continue; }
      max = Math.max(max, Number(k.split(',')[axis]));
    }
    return max;
  }
  getName() { return this.name; }
  setName(n) { this.name = n; return this; }
  getLastRow() { return this.used(0); }
  getLastColumn() { return this.used(1); }
  getMaxRows() { return this.maxRows; }
  getMaxColumns() { return this.maxCols; }
  insertRowsAfter(after, n) { this.maxRows += n; }
  insertColumnsAfter(after, n) { this.maxCols += n; }
  hideColumns(c) { this.hidden.add(c); }
  showColumns(c) { this.hidden.delete(c); }
  setColumnWidth(c, w) { this.widths[c] = w; }
  getFrozenRows() { return this.frozen; }
  setFrozenRows(n) { this.frozen = n; }
  getParent() { return this.parent; }
  deleteRow(row) {
    const moved = new Map();
    for (const [k, v] of this.cells) {
      const [r, c] = k.split(',').map(Number);
      if (r === row) { continue; }
      moved.set((r > row ? r - 1 : r) + ',' + c, v);
    }
    this.cells = moved;
    this.maxRows--;
  }
  deleteRows(start, n) { for (let i = 0; i < n; i++) { this.deleteRow(start); } }
  rows() { return this.getLastRow() - 1; }
  getRange(r, c, nr = 1, nc = 1) {
    if (r < 1 || c < 1 || nr < 1 || nc < 1 || r + nr - 1 > this.maxRows || c + nc - 1 > this.maxCols) {
      throw new Error(`range out of bounds: ${r},${c},${nr},${nc} (grid ${this.maxRows}x${this.maxCols})`);
    }
    return new Range(this, r, c, nr, nc);
  }
  /** Sheets API when called without a title; with one, what a person does when they insert a column of their own. */
  insertColumnBefore(col, title) {
    const moved = new Map();
    for (const [k, v] of this.cells) {
      const [r, c] = k.split(',').map(Number);
      moved.set(r + ',' + (c >= col ? c + 1 : c), v);
    }
    this.cells = moved;
    this.hidden = new Set([...this.hidden].map((c) => (c >= col ? c + 1 : c)));
    this.maxCols++;
    if (title !== undefined) { this.cell(1, col).value = title; }
    return this;
  }
  row(r) {
    const out = [];
    for (let c = 1; c <= this.getLastColumn(); c++) { out.push(this.peek(r, c).value); }
    return out;
  }
}

/**
 * What Sheets makes of a string, as seen on a live spreadsheet: it is parsed like
 * typing, whatever the call and the cell format. Only the leading apostrophe keeps
 * it as it is; the apostrophe itself is not part of the value.
 */
function entered(v) {
  if (typeof v !== 'string') { return v; }
  if (v.startsWith("'")) { return v.slice(1); }
  if (/^[=+]/.test(v) || /^[+-]?[0-9]+([.,][0-9]+)?$/.test(v) || /^[0-9]+:[0-9]+(:[0-9]+)?$/.test(v)) { return '#PARSED ' + v; }
  return v;
}

class Range {
  constructor(sh, r, c, nr, nc) { Object.assign(this, { sh, r, c, nr, nc }); }
  each(fn) {
    for (let i = 0; i < this.nr; i++) { for (let j = 0; j < this.nc; j++) { fn(this.sh.cell(this.r + i, this.c + j), i, j); } }
    return this;
  }
  grid(fn) {
    const out = [];
    for (let i = 0; i < this.nr; i++) {
      out.push([]);
      for (let j = 0; j < this.nc; j++) { out[i].push(fn(this.sh.peek(this.r + i, this.c + j))); }
    }
    return out;
  }
  fits(values) {
    if (values.length !== this.nr || values.some((row) => row.length !== this.nc)) {
      throw new Error(`data ${values.length}x${values[0] && values[0].length} does not match range ${this.nr}x${this.nc}`);
    }
  }
  getValues() { return this.grid((cell) => cell.value); }
  getDisplayValues() { return this.grid((cell) => (cell.value instanceof Date ? cell.value.toISOString() : String(cell.value))); }
  getRichTextValues() {
    return this.grid((cell) => ({ getText: () => String(cell.value), getLinkUrl: () => cell.link || null }));
  }
  getNotes() { return this.grid((cell) => cell.note || ''); }
  setNote(note) { return this.each((cell) => { cell.note = note; }); }
  setValue(v) { return this.each((cell) => { cell.value = entered(v); cell.rich = false; }); }
  setValues(values) { this.fits(values); return this.each((cell, i, j) => { cell.value = entered(values[i][j]); cell.rich = false; }); }
  setRichTextValue(rt) { return this.setRichTextValues([[rt]]); }
  setRichTextValues(values) {
    this.fits(values);
    return this.each((cell, i, j) => {
      const rt = values[i][j];
      if (!rt || rt.kind !== 'rich') { throw new Error('not a RichTextValue'); }
      cell.value = entered(rt.text); cell.link = rt.link; cell.rich = true;
    });
  }
  setNumberFormat(f) { return this.each((cell) => { cell.format = f; }); }
  setFontWeight(w) { return this.each((cell) => { cell.bold = (w === 'bold'); }); }
  setWrap(w) { return this.each((cell) => { cell.wrap = w; }); }
  setBackground(c) { return this.each((cell) => { cell.bg = c; }); }
  setFontColor(c) { return this.each((cell) => { cell.color = c; }); }
  getBackgrounds() { return this.grid((cell) => cell.bg || '#ffffff'); }
  getFontColors() { return this.grid((cell) => cell.color || '#000000'); }
  setBackgrounds(m) { this.fits(m); return this.each((cell, i, j) => { cell.bg = m[i][j] === '#ffffff' ? undefined : m[i][j]; }); }
  setFontColors(m) { this.fits(m); return this.each((cell, i, j) => { cell.color = m[i][j] === '#000000' ? undefined : m[i][j]; }); }
  setFontSize(n) { return this.each((cell) => { cell.size = n; }); }
  setVerticalAlignment() { return this; }
}

class Spreadsheet {
  constructor(name, first = 'Лист1') { this.name = name; this.tz = 'America/Los_Angeles'; this.sheets = [new Sheet(this, first)]; }
  getName() { return this.name; }
  getSpreadsheetTimeZone() { return this.tz; }
  setSpreadsheetTimeZone(tz) { this.tz = tz; }
  getSheets() { return this.sheets.slice(); }
  getSheetByName(n) { return this.sheets.find((s) => s.name === n) || null; }
  insertSheet(n, index) {
    const s = new Sheet(this, n);
    this.sheets.splice(index === undefined ? this.sheets.length : index, 0, s);
    return s;
  }
  tabs() { return this.sheets.map((s) => s.name); }
}

const books = {
  questions: new Spreadsheet('Задать вопрос преподавателю ОК Осень 2026 (Moodle)', 'Untitled'),
  groups: new Spreadsheet('Учебные группы Осень 2026 (Moodle)'),
  foreign: new Spreadsheet('Студенты Осень 2026'),
};
const props = { seen: JSON.stringify(['2631:1790336304']) };

const sandbox = {
  SpreadsheetApp: {
    openById(id) { if (!books[id]) { throw new Error('no such spreadsheet: ' + id); } return books[id]; },
    getActiveSpreadsheet() { return null; },
    newRichTextValue() {
      const rt = { kind: 'rich', text: '', link: null };
      const b = { setText(t) { rt.text = t; return b; }, setLinkUrl(u) { rt.link = u; return b; }, build() { return rt; } };
      return b;
    },
  },
  LockService: { getScriptLock() { return { waitLock() {}, releaseLock() {} }; } },
  PropertiesService: { getScriptProperties() { return { getProperty(k) { return props[k] || null; } }; } },
  ContentService: {
    MimeType: { JSON: 'json' },
    createTextOutput(text) { return { text, setMimeType() { return this; } }; },
  },
  Logger: { log() {} },
};
vm.createContext(sandbox);
let code = fs.readFileSync(path.join(__dirname, 'Code.gs'), 'utf8');
code = code.replace("var SECRET = 'CHANGE_ME'", "var SECRET = 's3'").replace("var SPREADSHEET_ID = ''", "var SPREADSHEET_ID = 'questions'");
vm.runInContext(code, sandbox);
// Dates the script will meet in cells must come from its own realm: it checks them with instanceof.
const SDate = vm.runInContext('Date', sandbox);

let fails = 0;
function check(what, got, want) {
  const ok = JSON.stringify(got) === JSON.stringify(want);
  if (!ok) { fails++; }
  console.log((ok ? 'ok   ' : 'FAIL ') + what + (ok ? '' : '\n     got:  ' + JSON.stringify(got) + '\n     want: ' + JSON.stringify(want)));
}
function post(body) {
  return JSON.parse(sandbox.doPost({ postData: { contents: JSON.stringify(Object.assign({ secret: 's3' }, body)) } }).text);
}
function question(completedid, timestamp, text, slot = 'в 8:00 изр', extra) {
  return Object.assign({
    event: 'feedback_response', completedid, timestamp, anonymous: false,
    user: { fullname: 'Мария М', email: 'm@example.com', city: 'Ptz', groups: ['11ж'] },
    feedback: { name: 'Вопрос по теме урока 1 к вебинару с преподавателями' },
    responseurl: 'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=13448&showcompleted=' + completedid,
    answers: [
      { itemid: 165, name: 'Буду присутствовать на вебинаре', type: 'multichoice', value: slot },
      { itemid: 164, name: 'Ваш вопрос по теме урока 1', type: 'textarea', value: text },
    ],
  }, extra || {});
}
function signup(completedid, name, phone, more) {
  return {
    event: 'feedback_response', completedid, timestamp: 1790000000 + completedid, anonymous: false,
    target: { spreadsheet: 'groups', sheet: '', layout: 'generic' },
    user: { fullname: name + ' (Moodle)', email: name + '@example.com', city: '', groups: [] },
    feedback: { name: 'Учебные группы' },
    responseurl: 'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=12951&showcompleted=' + completedid,
    answers: [
      { itemid: 152, name: 'Ваше имя ', type: 'textfield', value: name },
      { itemid: 159, name: 'Пол', type: 'multichoice', value: 'Ж' },
      { itemid: 158, name: 'Номер телефона в WhatsApp в международном формате +(код) номер ', type: 'textfield', value: phone },
    ].concat(more || []),
  };
}
// Header of the teachers' table as v6 made it (QHEAD) and as v7 makes it (QHEAD7).
const QHEAD = ['Отметка времени', 'Имя', 'Город', 'Буду участвовать в вебинаре', 'Мой вопрос', 'Номер группы', 'Email',
  'Ссылка в Moodle', 'Форма', 'Ответ преподавателя', 'ID ответа'];
const QHEAD7 = QHEAD.slice(0, 9).concat(['Повторная отправка'], QHEAD.slice(9));

// --- the teachers' table as it was before time tabs: one tab, header with notes, rows of all times ---
const qs = books.questions;
const u = qs.sheets[0];
QHEAD.forEach((t, i) => { u.cell(1, i + 1).value = t; });
['time', 'name', 'city', 'participate', 'question', 'groups', 'email', 'url', 'form', 'answer', 'key']
  .forEach((id, i) => { u.cell(1, i + 1).note = 'moodle:' + id + '\nСлужебная метка'; });
u.hidden.add(11);
[['в 8:00 изр', 'r2900t1790900000', ''], ['в 20:00 изр', 'r2901t1790900001', 'Ответ: да'], ['в 17:00 изр', 'r2902t1790900002', ''],
  ['в 20:00 изр', 'r2903t1790900003', ''], ['когда-нибудь', 'r2904t1790900004', '']].forEach((x, i) => {
  const r = i + 2;
  u.cell(r, 1).value = new SDate(1790900000000 + i * 1000);
  u.cell(r, 2).value = 'Студент ' + i; u.cell(r, 4).value = x[0]; u.cell(r, 5).value = 'вопрос ' + i;
  u.cell(r, 8).value = 'открыть'; u.cell(r, 8).link = 'https://edu.kabacademy.com/mod/feedback/show_entries.php?showcompleted=' + (2900 + i);
  u.cell(r, 9).value = 'Вопрос по теме урока 1'; u.cell(r, 10).value = x[2]; u.cell(r, 11).value = x[1];
});

check('ping', JSON.parse(sandbox.doGet().text), { ok: true, ping: 'local_kabfeedbackgdoc', version: 8 });
check('wrong secret', post({ secret: 'nope', event: 'feedback_response' }), { ok: false, error: 'forbidden' });
check('unknown event', post({ event: 'x' }), { ok: false, error: 'unknown event' });

// --- time tabs ---------------------------------------------------------------------------------
check('question goes to the tab of its webinar time', post(question(3000, 1790500000, '- почему так?\n=1+1')),
  { ok: true, written: 1, duplicates: 0, tables: [qs.name] });
check('time tab created first, header with notes, key hidden', [qs.tabs(), qs.getSheetByName('8:00').row(1),
  [...qs.getSheetByName('8:00').hidden], qs.getSheetByName('8:00').peek(1, 5).note.split('\n')[0]],
  [['8:00', 'Untitled'], QHEAD7, [12], 'moodle:question']);
const t8 = qs.getSheetByName('8:00');
check('question row', t8.row(2).slice(1), ['Мария М', 'Ptz', 'в 8:00 изр', '- почему так?\n=1+1', '11ж', 'm@example.com',
  'открыть', 'Вопрос по теме урока 1 к вебинару с преподавателями', '', '', 'r3000t1790500000']);
// The Date comes from the script's own realm, so instanceof would not see it from here.
check('question time is a date', [Object.prototype.toString.call(t8.peek(2, 1).value), t8.peek(2, 1).value.getTime(),
  t8.peek(2, 1).format], ['[object Date]', 1790500000000, 'dd.MM.yyyy H:mm:ss']);
check('question text is literal', [t8.peek(2, 5).rich, t8.peek(2, 5).wrap, t8.peek(2, 5).format], [true, true, '@']);
check('font size 13 on the header and on the whole row', [t8.peek(1, 1).size, t8.peek(1, 12).size, t8.peek(2, 1).size, t8.peek(2, 5).size,
  t8.peek(2, 10).size, t8.peek(2, 12).size], [13, 13, 13, 13, 13, 13]);
check('question link', t8.peek(2, 8).link, 'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=13448&showcompleted=3000');
check('old tab untouched', u.getLastRow(), 6);
check('timezone fixed', qs.tz, 'Asia/Jerusalem');

check('tabs stay in clock order whatever comes first', [
  post(question(3001, 1790500001, 'вечером', 'в 20:00 изр')).written,
  post(question(3002, 1790500002, 'днём', 'в 17:00 изр')).written,
  qs.tabs()], [1, 1, ['8:00', '17:00', '20:00', 'Untitled']]);
// --- moving what landed in the single tab before time tabs existed ------------------------------
const moved = sandbox.moveRowsByTime();
check('rows moved by their time, teacher answer kept', [moved, qs.tabs(), qs.getSheetByName('20:00').getLastRow(),
  qs.getSheetByName('20:00').row(3).slice(1), qs.getSheetByName('20:00').peek(3, 8).link],
  [{ '8:00': 1, '20:00': 2, '17:00': 1, left: 1 }, ['8:00', '17:00', '20:00', 'Без времени'], 4,
    ['Студент 1', '', 'в 20:00 изр', 'вопрос 1', '', '', 'открыть', 'Вопрос по теме урока 1', '', 'Ответ: да', 'r2901t1790900001'],
    'https://edu.kabacademy.com/mod/feedback/show_entries.php?showcompleted=2901']);
check('the row without a time stays, the rest of the old tab is gone', [u.getName(), u.getLastRow(), u.row(2).slice(1, 5)],
  ['Без времени', 2, ['Студент 4', '', 'когда-нибудь', 'вопрос 4']]);
check('v6 table: the new column went in front of the hidden key, the key moved with its rows',
  [u.row(1), [...u.hidden], u.peek(2, 12).value, u.peek(2, 11).value, u.peek(1, 11).note.split('\n')[0]],
  [QHEAD.slice(0, 10).concat(['Повторная отправка', 'ID ответа']), [12], 'r2904t1790900004', '', 'moodle:repeat']);
check('moved rows are known to the dedupe', post(question(2901, 1790900001, 'x', 'в 20:00 изр')).duplicate, true);
check('running it again moves nothing', sandbox.moveRowsByTime(), { left: 1 });

check('a time written differently still finds its tab', [post(question(3003, 1790500003, 'x', 'В 08:00 (изр.)')).written,
  t8.getLastRow(), qs.tabs().length], [1, 4, 4]);
check('no time in the answer: the renamed fallback tab', [post(question(3004, 1790500004, 'x', 'не знаю')).written,
  qs.tabs(), u.peek(3, 4).value], [1, ['8:00', '17:00', '20:00', 'Без времени'], 'не знаю']);
check('no participate answer at all: fallback tab', [post({ event: 'feedback_response', completedid: 3005, timestamp: 1790500005,
  anonymous: true, feedback: { name: 'Вопрос к вебинару следующей недели' }, answers: [{ itemid: 1, name: 'Ваш вопрос', value: 'q' }] }).written,
  u.getLastRow(), u.peek(4, 5).value], [1, 4, 'q']);
check('explicit tab from Moodle wins over the time', [post(question(3006, 1790500006, 'x', 'в 8:00 изр',
  { target: { spreadsheet: 'questions', sheet: 'Архив', layout: 'questions' } })).written, qs.tabs().slice(-1)[0],
  qs.getSheetByName('Архив').peek(2, 5).value], [1, 'Архив', 'x']);

check('cron retry is a duplicate', post(question(3000, 1790500000, 'x')),
  { ok: true, written: 0, duplicates: 1, tables: [qs.name], duplicate: true });
// --- re-submissions (the form allows multiple submissions, the student changed their mind) ---
check('edited answer is a new row, marked as a repeat', post(question(3000, 1790500999, 'ещё вопрос')),
  { ok: true, written: 1, duplicates: 0, tables: [qs.name], repeats: 1 });
check('the new row names the previous one and what changed; the changed cell is highlighted',
  [t8.peek(5, 10).value, t8.peek(5, 10).bg, t8.peek(5, 5).bg, t8.peek(5, 4).bg, t8.peek(5, 2).bg],
  ['Повторная отправка, прежний ответ — строка 2. Изменилось: Мой вопрос', '#fff2cc', '#fff2cc', undefined, undefined]);
check('the previous row is greyed out and points to the new one',
  [t8.peek(2, 10).value, t8.peek(2, 1).bg, t8.peek(2, 5).color, t8.peek(2, 12).value, t8.peek(2, 5).value],
  ['Устарел, новый ответ — строка 5', '#efefef', '#888888', 'r3000t1790500000', '- почему так?\n=1+1']);
const t17 = qs.getSheetByName('17:00');
check('edited answer with another time goes to that tab', [post(question(3000, 1790501000, 'ещё', 'в 17:00 изр')).repeats,
  t17.getLastRow()], [1, 4]);
check('the previous row is found on the other tab, both rows point across tabs',
  [t17.peek(4, 10).value, t17.peek(4, 4).bg, t17.peek(4, 5).bg, t8.peek(5, 10).value, t8.peek(5, 1).bg],
  ['Повторная отправка, прежний ответ — строка 5 на вкладке «8:00». Изменилось: Буду участвовать в вебинаре; Мой вопрос',
    '#fff2cc', '#fff2cc', 'Устарел, новый ответ — строка 4 на вкладке «17:00»', '#efefef']);
check('the oldest row keeps its own pointer', t8.peek(2, 10).value, 'Устарел, новый ответ — строка 5');
check('key remembered by v4 is a duplicate', post(question(2631, 1790336304, 'x')).duplicate, true);
check('test payload is never a duplicate', [post(question(0, 1, 'a')).written, post(question(0, 1, 'a')).written], [1, 1]);
check('rows in 8:00 so far', t8.getLastRow(), 7);

// A teacher inserts a column of their own and renames ours.
t8.insertColumnBefore(5, 'Кто отвечает');
t8.cell(1, 6).value = 'Вопрос';
check('after the table was rearranged', post(question(3007, 1790600000, 'после перестановки')).written, 1);
check('row follows the notes', [t8.peek(8, 5).value, t8.peek(8, 6).value, t8.peek(8, 13).value, t8.getLastColumn()],
  ['', 'после перестановки', 'r3007t1790600000', 13]);

t8.maxRows = 8;
check('grid grows when full', [post(question(3008, 1790700000, 'ещё')).written, t8.getLastRow(), t8.maxRows > 9], [1, 9, true]);

// --- a form with a table of its own -----------------------------------------------------------
const g = books.groups.sheets[0];
check('batch delivered', post({ event: 'feedback_batch', responses: [
  signup(2617, 'Анна', '+972501234567'),
  signup(2618, 'Белла', '=HYPERLINK("http://x")'),
  signup(2617, 'Анна', '+972501234567'),
  signup(2619, 'Вера', '-', [{ itemid: 155, name: 'Дополнительная информация', type: 'textfield', value: "'в кавычках' и 12:30" }]),
] }), { ok: true, written: 3, duplicates: 1, tables: [books.groups.name] });
check('generic header', g.row(1), ['Отметка времени', 'Имя в Moodle', 'Email', 'Повторная отправка', 'Ваше имя', 'Пол',
  'Номер телефона в WhatsApp в международном формате +(код) номер', 'Дополнительная информация',
  'Группа в Moodle', 'Ссылка в Moodle', 'ID ответа']);
check('generic has no time tabs', books.groups.tabs(), ['Лист1']);
check('generic rows keep the order', [g.peek(2, 5).value, g.peek(3, 5).value, g.peek(4, 5).value], ['Анна', 'Белла', 'Вера']);
check('phones and formulas stay text', [g.peek(2, 7).value, g.peek(2, 7).rich, g.peek(3, 7).value, g.peek(3, 7).rich],
  ['+972501234567', true, '=HYPERLINK("http://x")', true]);
check('missing item is empty, apostrophe survives', [g.peek(2, 8).value, g.peek(4, 8).value], ['', "'в кавычках' и 12:30"]);
check('text cells are plain text', [g.peek(2, 7).format, g.peek(2, 11).format, g.peek(1, 7).format], ['@', '@', '@']);
check('generic key hidden', [[...g.hidden], g.peek(4, 11).value], [[11], 'r2619t1790002619']);
check('first answers carry no repeat mark', [g.peek(2, 4).value, g.peek(2, 4).bg, g.peek(2, 1).bg], ['', undefined, undefined]);
check('resend of everything adds nothing', post({ event: 'feedback_batch', responses: [
  signup(2617, 'Анна', '+972501234567'), signup(2618, 'Белла', 'x'), signup(2619, 'Вера', '-'),
] }), { ok: true, written: 0, duplicates: 3, tables: [books.groups.name] });

g.cell(1, 7).value = 'Телефон';
g.insertColumnBefore(1, 'Группа WhatsApp');
check('single after rename and insert', post(signup(2620, 'Галя', '+79991234567',
  [{ itemid: 999, name: 'Новый пункт формы', type: 'textfield', value: 'да' }])).written, 1);
check('new item gets a column in front of the hidden key', [g.peek(1, 12).value, g.peek(5, 12).value, g.peek(5, 8).value,
  g.peek(5, 1).value, g.peek(5, 13).value, [...g.hidden]], ['Новый пункт формы', 'да', '+79991234567', '', 'r2620t1790002620', [13]]);

const wide = [];
for (let i = 0; i < 30; i++) { wide.push({ itemid: 5000 + i, name: 'Пункт ' + i, type: 'textfield', value: String(i) }); }
check('more columns than the grid has', [post(signup(2621, 'Дина', '1', wide)).written, g.maxCols >= 42, g.peek(6, 42).value], [1, true, '29']);

check('mixed batch goes to both tables', post({ event: 'feedback_batch', responses: [
  signup(2622, 'Ева', '2'), question(3009, 1790800000, 'вперемешку'),
] }), { ok: true, written: 2, duplicates: 0, tables: [books.groups.name, qs.name] });

// --- re-submissions in a generic table ----------------------------------------------------------
// Columns by now: 1 Группа WhatsApp (theirs), 2 time, 3 user, 4 email, 5 repeat, 6 name, 7 sex, 8 Телефон, 9 extra, …, 43 key.
check('changed phone: new row 8 marked, old row 2 greyed',
  [post(Object.assign(signup(2617, 'Анна', '+972500000000'), { timestamp: 1790010000 })).repeats,
    g.peek(8, 5).value, g.peek(8, 8).bg, g.peek(8, 7).bg, g.peek(8, 5).bg,
    g.peek(2, 5).value, g.peek(2, 1).bg, g.peek(2, 8).color, g.peek(2, 8).value],
  [1, 'Повторная отправка, прежний ответ — строка 2. Изменилось: Телефон', '#fff2cc', undefined, '#fff2cc',
    'Устарел, новый ответ — строка 8', '#efefef', '#888888', '+972501234567']);
check('same answers again: marked, nothing highlighted but the mark',
  [post(Object.assign(signup(2618, 'Белла', '=HYPERLINK("http://x")'), { timestamp: 1790010001 })).repeats,
    g.peek(9, 5).value, g.peek(9, 8).bg, g.peek(9, 5).bg, g.peek(3, 5).value],
  [1, 'Повторная отправка, прежний ответ — строка 3. Ответы те же', undefined, '#fff2cc', 'Устарел, новый ответ — строка 9']);
check('third time: the previous row is the latest one, not the first',
  [post(Object.assign(signup(2617, 'Анна', '+972500000001'), { timestamp: 1790020000 })).repeats,
    g.peek(10, 5).value, g.peek(8, 5).value, g.peek(8, 1).bg, g.peek(2, 5).value],
  [1, 'Повторная отправка, прежний ответ — строка 8. Изменилось: Телефон', 'Устарел, новый ответ — строка 10', '#efefef',
    'Устарел, новый ответ — строка 8']);
check('an item answered for the first time counts as a change',
  [post(Object.assign(signup(2617, 'Анна', '+972500000001',
    [{ itemid: 155, name: 'Дополнительная информация', type: 'textfield', value: 'хочу в группу' }]), { timestamp: 1790040000 })).repeats,
    g.peek(11, 5).value, g.peek(11, 9).bg, g.peek(11, 8).bg],
  [1, 'Повторная отправка, прежний ответ — строка 10. Изменилось: Дополнительная информация', '#fff2cc', undefined]);
check('two submissions of one response in a batch: the second points at the first',
  [post({ event: 'feedback_batch', responses: [signup(2630, 'Зоя', '1'),
    Object.assign(signup(2630, 'Зоя', '2'), { timestamp: 1790030000 })] }),
    g.peek(13, 5).value, g.peek(12, 5).value, g.peek(12, 1).bg, g.peek(12, 5).bg],
  [{ ok: true, written: 2, duplicates: 0, tables: [books.groups.name], repeats: 1 },
    'Повторная отправка, прежний ответ — строка 12. Изменилось: Телефон', 'Устарел, новый ответ — строка 13', '#efefef', '#efefef']);
check('resend after all that adds nothing and marks nothing', [post({ event: 'feedback_batch', responses: [
  signup(2617, 'Анна', 'x'), Object.assign(signup(2617, 'Анна', 'x'), { timestamp: 1790040000 }),
] }), g.getLastRow()], [{ ok: true, written: 0, duplicates: 2, tables: [books.groups.name] }, 13]);

// --- guards -----------------------------------------------------------------------------------
const before = JSON.stringify([...books.foreign.sheets[0].cells]);
const refused = post(Object.assign(signup(1, 'x', 'y'), { target: { spreadsheet: 'foreign', layout: 'generic' } }));
check('table without the mark is refused and untouched', [refused.ok, /\(Moodle\)/.test(refused.error),
  JSON.stringify([...books.foreign.sheets[0].cells]) === before], [false, true, true]);
check('unknown table', post(Object.assign(signup(1, 'x', 'y'), { target: { spreadsheet: 'nope', layout: 'generic' } })).ok, false);
check('named tab is created after the others', [post(Object.assign(signup(2623, 'Жанна', '3'),
  { target: { spreadsheet: 'groups', sheet: 'Весна', layout: 'generic' } })).written,
  books.groups.tabs(), books.groups.getSheetByName('Весна').peek(2, 5).value], [1, ['Лист1', 'Весна'], 'Жанна']);
check('empty batch', post({ event: 'feedback_batch', responses: [] }), { ok: true, written: 0, duplicates: 0, tables: [] });

// --- a form closes: its rows leave the time tabs for the archive --------------------------------
// By now the rows of form 13448 (link ...?id=13448) sit on 8:00 (3000, 3003, 3000 edited, two test
// payloads, 3007, 3008, 3009), 17:00 (3002, 3000 edited again), 20:00 (3001), Без времени (3004) and
// one on Архив already (3006, explicit tab). The "Студент" rows have no id in their link and another
// form name; 3005 has no link at all.
const ar = qs.getSheetByName('Архив');
t8.cell(2, 12).value = 'ответ на первый';   // a teacher's answer in the outdated row travels too (t8 has an extra column 5)
const archived = post({ event: 'feedback_archive', form: { cmid: 13448, name: 'Вопрос по теме урока 1 к вебинару с преподавателями' } });
const times = ar.getRange(2, 1, ar.rows(), 1).getValues().map((r) => (r[0] && r[0].getTime ? r[0].getTime() : 0));
check('archive: every row of the form moved, from every tab', [archived, ar.rows()],
  [{ ok: true, moved: 12, from: { '8:00': 8, '17:00': 2, '20:00': 1, 'Без времени': 1 }, table: qs.name }, 13]);
check('the row that was there before stays first; the moved ones follow in order of time',
  [ar.peek(2, 12).value, ar.peek(3, 12).value, ar.peek(5, 12).value, ar.peek(14, 12).value,
    times.slice(1).every((v, i, a) => i === 0 || v >= a[i - 1])],
  ['r3006t1790500006', 'r0t1', 'r3000t1790500000', 'r3009t1790800000', true]);
check('what stays: rows of other forms', [t8.rows(), t8.peek(2, 2).value, t17.rows(), qs.getSheetByName('20:00').rows(), u.rows(),
  u.peek(3, 5).value], [1, 'Студент 0', 1, 2, 2, 'q']);
const rowOf = (key) => { for (let r = 2; r <= ar.getLastRow(); r++) { if (ar.peek(r, 12).value === key) { return r; } } return 0; };
const old = rowOf('r3000t1790500000');
check('an outdated row keeps its grey, its pointer text and the teacher answer', [ar.peek(old, 1).bg, ar.peek(old, 5).color,
  ar.peek(old, 10).value, ar.peek(old, 11).value, ar.peek(old, 8).link, ar.peek(old, 5).value],
  ['#efefef', '#888888', 'Устарел, новый ответ — строка 5', 'ответ на первый',
    'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=13448&showcompleted=3000', '- почему так?' + String.fromCharCode(10) + '=1+1']);
const edited = rowOf('r3000t1790501000');
check('a changed answer keeps its yellow', [ar.peek(edited, 4).bg, ar.peek(edited, 5).bg, ar.peek(edited, 2).bg, ar.peek(edited, 2).size],
  ['#fff2cc', '#fff2cc', undefined, 13]);
check('archived rows still count for the dedupe', post(question(3003, 1790500003, 'x', 'В 08:00 (изр.)')).duplicate, true);
check('asking again moves nothing', post({ event: 'feedback_archive', form: { cmid: 13448, name: 'x' } }).moved, 0);
check('a form that never wrote here', post({ event: 'feedback_archive', form: { cmid: 99999 } }), { ok: true, moved: 0, from: {}, table: qs.name });
check('archive needs a cmid', post({ event: 'feedback_archive', form: {} }).ok, false);

// --- restyling what is already there ---------------------------------------------------------
g.cell(2, 7).size = undefined; g.cell(1, 1).size = undefined;
check('applyFontSize covers every tab of a table', [sandbox.applyFontSize('groups')['Лист1'], g.peek(2, 7).size, g.peek(1, 1).size,
  books.groups.getSheetByName('Весна').peek(2, 5).size], [13, 13, 13, 13]);

console.log(fails ? `\n${fails} FAILED` : '\nall passed');
process.exit(fails ? 1 : 0);
