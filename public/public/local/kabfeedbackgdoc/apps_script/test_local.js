/**
 * Runs Code.gs against an in-memory stand-in for the Spreadsheet service:
 *   node test_local.js
 * Covers the logic (columns, dedupe, batches, text safety), not Google itself.
 */
'use strict';
const fs = require('fs');
const path = require('path');
const vm = require('vm');

class Sheet {
  constructor(parent, name) {
    this.parent = parent; this.name = name; this.cells = new Map();
    this.maxRows = 1000; this.maxCols = 26; this.frozen = 0; this.hidden = new Set(); this.widths = {};
  }
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
  getLastRow() { return this.used(0); }
  getLastColumn() { return this.used(1); }
  getMaxRows() { return this.maxRows; }
  getMaxColumns() { return this.maxCols; }
  insertRowsAfter(after, n) { this.maxRows += n; }
  insertColumnsAfter(after, n) { this.maxCols += n; }
  hideColumns(c) { this.hidden.add(c); }
  setColumnWidth(c, w) { this.widths[c] = w; }
  getFrozenRows() { return this.frozen; }
  setFrozenRows(n) { this.frozen = n; }
  getParent() { return this.parent; }
  getRange(r, c, nr = 1, nc = 1) {
    if (r < 1 || c < 1 || nr < 1 || nc < 1 || r + nr - 1 > this.maxRows || c + nc - 1 > this.maxCols) {
      throw new Error(`range out of bounds: ${r},${c},${nr},${nc} (grid ${this.maxRows}x${this.maxCols})`);
    }
    return new Range(this, r, c, nr, nc);
  }
  /** Test helper: what a person does when they insert a column of their own. */
  insertColumnBefore(col, title) {
    const moved = new Map();
    for (const [k, v] of this.cells) {
      const [r, c] = k.split(',').map(Number);
      moved.set(r + ',' + (c >= col ? c + 1 : c), v);
    }
    this.cells = moved;
    this.hidden = new Set([...this.hidden].map((c) => (c >= col ? c + 1 : c)));
    this.maxCols++;
    this.cell(1, col).value = title;
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
  getDisplayValues() { return this.grid((cell) => (cell.value instanceof Date ? cell.value.toISOString() : String(cell.value))); }
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
  setVerticalAlignment() { return this; }
}

class Spreadsheet {
  constructor(name) { this.name = name; this.tz = 'America/Los_Angeles'; this.sheets = [new Sheet(this, 'Лист1')]; }
  getName() { return this.name; }
  getSpreadsheetTimeZone() { return this.tz; }
  setSpreadsheetTimeZone(tz) { this.tz = tz; }
  getSheets() { return this.sheets; }
  getSheetByName(n) { return this.sheets.find((s) => s.name === n) || null; }
  insertSheet(n) { const s = new Sheet(this, n); this.sheets.push(s); return s; }
}

const books = {
  questions: new Spreadsheet('Задать вопрос преподавателю ОК Осень 2026 (Moodle)'),
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

let fails = 0;
function check(what, got, want) {
  const ok = JSON.stringify(got) === JSON.stringify(want);
  if (!ok) { fails++; }
  console.log((ok ? 'ok   ' : 'FAIL ') + what + (ok ? '' : '\n     got:  ' + JSON.stringify(got) + '\n     want: ' + JSON.stringify(want)));
}
function post(body) {
  return JSON.parse(sandbox.doPost({ postData: { contents: JSON.stringify(Object.assign({ secret: 's3' }, body)) } }).text);
}
function question(completedid, timestamp, text, extra) {
  return Object.assign({
    event: 'feedback_response', completedid, timestamp, anonymous: false,
    user: { fullname: 'Мария М', email: 'm@example.com', city: 'Ptz', groups: ['11ж'] },
    feedback: { name: 'Вопрос по теме урока 1 к вебинару с преподавателями' },
    responseurl: 'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=13448&showcompleted=' + completedid,
    answers: [
      { itemid: 165, name: 'Буду присутствовать на вебинаре', type: 'multichoice', value: 'в 8:00 изр' },
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

// --- the teachers' table as it is today: header without notes, eight test rows -----------------
const q = books.questions.sheets[0];
['Отметка времени', 'Имя', 'Город', 'Буду участвовать в вебинаре', 'Мой вопрос', 'Номер группы', 'Email',
  'Ссылка в Moodle', 'Форма', 'Ответ преподавателя'].forEach((t, i) => { q.cell(1, i + 1).value = t; });
for (let r = 2; r <= 9; r++) { q.cell(r, 1).value = new Date(); q.cell(r, 2).value = 'Мария'; }

check('ping', JSON.parse(sandbox.doGet().text), { ok: true, ping: 'local_kabfeedbackgdoc', version: 5 });
check('wrong secret', post({ secret: 'nope', event: 'feedback_response' }), { ok: false, error: 'forbidden' });
check('unknown event', post({ event: 'x' }), { ok: false, error: 'unknown event' });

check('question delivered', post(question(3000, 1790500000, '- почему так?\n=1+1')),
  { ok: true, written: 1, duplicates: 0, tables: [books.questions.name] });
check('question row', q.row(10).slice(1), ['Мария М', 'Ptz', 'в 8:00 изр', '- почему так?\n=1+1', '11ж', 'm@example.com',
  'открыть', 'Вопрос по теме урока 1 к вебинару с преподавателями', '', 'r3000t1790500000']);
// The Date comes from the script's own realm, so instanceof would not see it from here.
check('question time is a date', [Object.prototype.toString.call(q.peek(10, 1).value), q.peek(10, 1).value.getTime(),
  q.peek(10, 1).format], ['[object Date]', 1790500000000, 'dd.MM.yyyy H:mm:ss']);
check('question text is literal', [q.peek(10, 5).rich, q.peek(10, 5).wrap], [true, true]);
check('question link', q.peek(10, 8).link, 'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=13448&showcompleted=3000');
check('old header adopted, key column appended hidden', [q.getLastColumn(), [...q.hidden], q.peek(1, 11).value,
  q.peek(1, 5).note.split('\n')[0], q.peek(1, 11).note.split('\n')[0]], [11, [11], 'ID ответа', 'moodle:question', 'moodle:key']);
check('timezone fixed', books.questions.tz, 'Asia/Jerusalem');
check('cron retry is a duplicate', post(question(3000, 1790500000, 'x')),
  { ok: true, written: 0, duplicates: 1, tables: [books.questions.name], duplicate: true });
check('edited answer is a new row', post(question(3000, 1790500999, 'ещё вопрос')).written, 1);
check('key remembered by v4 is a duplicate', post(question(2631, 1790336304, 'x')).duplicate, true);
check('test payload is never a duplicate', [post(question(0, 1, 'a')).written, post(question(0, 1, 'a')).written], [1, 1]);
check('rows so far', q.getLastRow(), 13);

// A teacher inserts a column of their own and renames ours.
q.insertColumnBefore(5, 'Кто отвечает');
q.cell(1, 6).value = 'Вопрос';
check('after the table was rearranged', post(question(3001, 1790600000, 'после перестановки')).written, 1);
check('row follows the notes', [q.peek(14, 5).value, q.peek(14, 6).value, q.peek(14, 12).value, q.getLastColumn()],
  ['', 'после перестановки', 'r3001t1790600000', 12]);

q.maxRows = 14;
check('grid grows when full', [post(question(3002, 1790700000, 'ещё')).written, q.getLastRow(), q.maxRows > 15], [1, 15, true]);

// --- a form with a table of its own -----------------------------------------------------------
const g = books.groups.sheets[0];
check('batch delivered', post({ event: 'feedback_batch', responses: [
  signup(2617, 'Анна', '+972501234567'),
  signup(2618, 'Белла', '=HYPERLINK("http://x")'),
  signup(2617, 'Анна', '+972501234567'),
  signup(2619, 'Вера', '-', [{ itemid: 155, name: 'Дополнительная информация', type: 'textfield', value: "'в кавычках' и 12:30" }]),
] }), { ok: true, written: 3, duplicates: 1, tables: [books.groups.name] });
check('generic header', g.row(1), ['Отметка времени', 'Имя в Moodle', 'Email', 'Ваше имя', 'Пол',
  'Номер телефона в WhatsApp в международном формате +(код) номер', 'Дополнительная информация',
  'Группа в Moodle', 'Ссылка в Moodle', 'ID ответа']);
check('generic rows keep the order', [g.peek(2, 4).value, g.peek(3, 4).value, g.peek(4, 4).value], ['Анна', 'Белла', 'Вера']);
check('phones and formulas stay text', [g.peek(2, 6).value, g.peek(2, 6).rich, g.peek(3, 6).value, g.peek(3, 6).rich],
  ['+972501234567', true, '=HYPERLINK("http://x")', true]);
check('missing item is empty, apostrophe survives', [g.peek(2, 7).value, g.peek(4, 7).value], ['', "'в кавычках' и 12:30"]);
check('text cells are plain text', [g.peek(2, 6).format, g.peek(2, 10).format, g.peek(1, 6).format], ['@', '@', '@']);
check('generic key hidden', [[...g.hidden], g.peek(4, 10).value], [[10], 'r2619t1790002619']);
check('resend of everything adds nothing', post({ event: 'feedback_batch', responses: [
  signup(2617, 'Анна', '+972501234567'), signup(2618, 'Белла', 'x'), signup(2619, 'Вера', '-'),
] }), { ok: true, written: 0, duplicates: 3, tables: [books.groups.name] });

g.cell(1, 6).value = 'Телефон';
g.insertColumnBefore(1, 'Группа WhatsApp');
check('single after rename and insert', post(signup(2620, 'Галя', '+79991234567',
  [{ itemid: 999, name: 'Новый пункт формы', type: 'textfield', value: 'да' }])).written, 1);
check('new item gets a column at the end', [g.peek(1, 12).value, g.peek(5, 12).value, g.peek(5, 7).value, g.peek(5, 1).value,
  g.peek(5, 11).value], ['Новый пункт формы', 'да', '+79991234567', '', 'r2620t1790002620']);

const wide = [];
for (let i = 0; i < 30; i++) { wide.push({ itemid: 5000 + i, name: 'Пункт ' + i, type: 'textfield', value: String(i) }); }
check('more columns than the grid has', [post(signup(2621, 'Дина', '1', wide)).written, g.maxCols >= 42, g.peek(6, 42).value], [1, true, '29']);

check('mixed batch goes to both tables', post({ event: 'feedback_batch', responses: [
  signup(2622, 'Ева', '2'), question(3003, 1790800000, 'вперемешку'),
] }), { ok: true, written: 2, duplicates: 0, tables: [books.groups.name, books.questions.name] });

// --- guards -----------------------------------------------------------------------------------
const before = JSON.stringify([...books.foreign.sheets[0].cells]);
const refused = post(Object.assign(signup(1, 'x', 'y'), { target: { spreadsheet: 'foreign', layout: 'generic' } }));
check('table without the mark is refused and untouched', [refused.ok, /\(Moodle\)/.test(refused.error),
  JSON.stringify([...books.foreign.sheets[0].cells]) === before], [false, true, true]);
check('unknown table', post(Object.assign(signup(1, 'x', 'y'), { target: { spreadsheet: 'nope', layout: 'generic' } })).ok, false);
check('named tab is created', [post(Object.assign(signup(2623, 'Жанна', '3'),
  { target: { spreadsheet: 'groups', sheet: 'Весна', layout: 'generic' } })).written,
  books.groups.getSheetByName('Весна').peek(2, 4).value], [1, 'Жанна']);
check('empty batch', post({ event: 'feedback_batch', responses: [] }), { ok: true, written: 0, duplicates: 0, tables: [] });

console.log(fails ? `\n${fails} FAILED` : '\nall passed');
process.exit(fails ? 1 : 0);
