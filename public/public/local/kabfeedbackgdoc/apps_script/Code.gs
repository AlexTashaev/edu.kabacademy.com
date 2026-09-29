/**
 * Moodle feedback → Google Sheets (local_kabfeedbackgdoc), Apps Script web app.
 *
 * Receives feedback responses from Moodle and appends one row per response.
 * Moodle picks the table and the layout per form (payload.target):
 *
 *   "questions" (default) — the teachers' table, same columns as the Google Forms
 *       "(Ответы)" sheets:
 *       Отметка времени | Имя | Город | Буду участвовать в вебинаре | Мой вопрос |
 *       Номер группы | Email | Ссылка в Moodle | Форма | Ответ преподавателя
 *   "generic" — a form with its own table, one column per form item:
 *       Отметка времени | Имя в Moodle | Email | <пункт 1> | … | Группа в Moodle | Ссылка в Moodle
 *
 * Target spreadsheet: payload.target.spreadsheet (plugin settings), otherwise
 * SPREADSHEET_ID below. Its title must contain TARGET_TITLE_MARK, so a mistyped id
 * can never write into an unrelated table.
 *
 * Columns are found by a note on the header cell ("moodle:<id>"), not by position
 * or title: people may rename headers, move columns and add their own.
 *
 * Setup (once):
 *   1. script.google.com → New project, paste this file, set SECRET and SPREADSHEET_ID.
 *   2. Deploy → New deployment → type "Web app":
 *        Execute as: Me;  Who has access: Anyone.
 *      Authorize when asked. Copy the Web app URL (…/exec).
 *   3. Moodle → Site administration → Plugins → Local plugins →
 *      "Обратная связь → Google-таблица (KAB)": paste the URL and the same SECRET.
 *
 * After changing this code: Deploy → Manage deployments → edit → New version,
 * otherwise the /exec URL keeps running the old version. GET /exec reports VERSION.
 */

var SECRET = 'CHANGE_ME';      // must equal the plugin's "Shared secret" setting
var SPREADSHEET_ID = '';       // default table (payloads without target.spreadsheet); '' = the bound spreadsheet
var SHEET_NAME = '';           // tab of the default table; '' = first tab
var TIMEZONE = 'Asia/Jerusalem'; // spreadsheet time zone; new sheets default to US Pacific, so we fix it on first write

// ---- settings end: everything below is replaced as a whole when the script is updated ----

var VERSION = 5;
var TARGET_TITLE_MARK = '(Moodle)'; // a spreadsheet must carry this in its title to accept rows; '' = no check
var NOTE_PREFIX = 'moodle:';
var NOTE_HINT = '\nСлужебная метка: по ней скрипт находит колонку. Заголовок можно переименовать, колонку — двигать.';
var DATE_FORMAT = 'dd.MM.yyyy H:mm:ss';
var TEXT_FORMAT = '@';         // "plain text" number format, see literal_()
var MAX_CELL = 49000;          // Sheets refuses cells over 50 000 characters

// "questions" layout: which feedback item goes to which column, matched by item name.
var COLUMN_RULES = [
  { col: 'participate', re: /присутств|участв/i },
  { col: 'question',    re: /вопрос/i }
];

var QUESTION_COLUMNS = [
  { id: 'time',        title: 'Отметка времени', width: 140 },
  { id: 'name',        title: 'Имя' },
  { id: 'city',        title: 'Город' },
  { id: 'participate', title: 'Буду участвовать в вебинаре' },
  { id: 'question',    title: 'Мой вопрос' },
  { id: 'groups',      title: 'Номер группы' },
  { id: 'email',       title: 'Email' },
  { id: 'url',         title: 'Ссылка в Moodle' },
  { id: 'form',        title: 'Форма' },
  { id: 'answer',      title: 'Ответ преподавателя' },
  { id: 'key',         title: 'ID ответа', hidden: true }
];

var GENERIC_LEAD = [
  { id: 'time',  title: 'Отметка времени', width: 140 },
  { id: 'user',  title: 'Имя в Moodle', width: 180 },
  { id: 'email', title: 'Email', width: 200 }
];
var GENERIC_TAIL = [
  { id: 'groups', title: 'Группа в Moodle' },
  { id: 'url',    title: 'Ссылка в Moodle' },
  { id: 'key',    title: 'ID ответа', hidden: true }
];

function doPost(e) {
  var lock = LockService.getScriptLock();
  try {
    lock.waitLock(30000);
    var data = JSON.parse((e && e.postData && e.postData.contents) || '{}');
    if (SECRET && data.secret !== SECRET) {
      return respond({ ok: false, error: 'forbidden' });
    }
    if (data.event === 'feedback_response') {
      return respond(handle_([data]));
    }
    if (data.event === 'feedback_batch') {
      return respond(handle_(data.responses || []));
    }
    return respond({ ok: false, error: 'unknown event' });
  } catch (err) {
    return respond({ ok: false, error: String(err) });
  } finally {
    try { lock.releaseLock(); } catch (ignore) { /* lock was never taken */ }
  }
}

function doGet() {
  return respond({ ok: true, ping: 'local_kabfeedbackgdoc', version: VERSION });
}

function respond(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}

// ---------------------------------------------------------------------------

/**
 * Append the responses (in the given order) to their tables.
 * Moodle retries failed deliveries and "resend" repeats old ones, so every row
 * carries a key (completed id + timestamp) and known keys are skipped. The timestamp
 * is part of the key because a non-anonymous re-submission edits the same
 * feedback_completed record: an edited answer must become a new row.
 */
function handle_(responses) {
  var groups = {};
  var order = [];
  responses.forEach(function (d) {
    var t = d.target || {};
    var g = [t.spreadsheet || '', t.sheet || '', t.layout === 'generic' ? 'generic' : 'questions'].join('|');
    if (!groups[g]) {
      groups[g] = { spreadsheet: t.spreadsheet || '', sheet: t.sheet || '', generic: t.layout === 'generic', items: [] };
      order.push(g);
    }
    groups[g].items.push(d);
  });

  var legacy = legacySeen_();
  var result = { ok: true, written: 0, duplicates: 0, tables: [] };
  order.forEach(function (g) {
    var group = groups[g];
    var sh = sheet_(group.spreadsheet, group.sheet);
    var cols = ensureColumns_(sh, group.generic ? genericColumns_(group.items) : QUESTION_COLUMNS);
    var seen = keys_(sh, cols.key);
    var rows = [];
    group.items.forEach(function (d) {
      var key = key_(d);
      if (Number(d.completedid)) { // completedid 0 = test payloads, never deduped
        if (seen[key] || legacy[String(d.completedid) + ':' + String(d.timestamp)]) { result.duplicates++; return; }
        seen[key] = true;
      }
      rows.push(group.generic ? genericCells_(d, key) : questionCells_(d, key));
    });
    writeRows_(sh, cols, rows);
    result.written += rows.length;
    result.tables.push(sh.getParent().getName());
  });
  if (responses.length === 1 && result.duplicates === 1) { result.duplicate = true; }
  return result;
}

/**
 * Key of a row, e.g. "r2631t1790336304". Letters on purpose: Sheets reads
 * "2631:1790336304" as a duration and stores something else.
 */
function key_(d) {
  return 'r' + String(d.completedid) + 't' + String(d.timestamp);
}

function sheet_(spreadsheetId, sheetName) {
  var id = spreadsheetId || SPREADSHEET_ID;
  var ss = id ? SpreadsheetApp.openById(id) : SpreadsheetApp.getActiveSpreadsheet();
  if (!ss) { throw new Error('no spreadsheet configured'); }
  if (TARGET_TITLE_MARK && ss.getName().indexOf(TARGET_TITLE_MARK) === -1) {
    throw new Error('в названии таблицы нет пометки ' + TARGET_TITLE_MARK + ': ' + ss.getName());
  }
  if (TIMEZONE && ss.getSpreadsheetTimeZone() !== TIMEZONE) {
    ss.setSpreadsheetTimeZone(TIMEZONE);
  }
  var name = sheetName || (spreadsheetId ? '' : SHEET_NAME);
  var sh = name ? ss.getSheetByName(name) : ss.getSheets()[0];
  if (!sh && name) { sh = ss.insertSheet(name); }
  if (!sh) { throw new Error('sheet not found'); }
  return sh;
}

/**
 * Map column id → column number, creating what is missing.
 * A header without a note but with the expected title is adopted (tables that
 * existed before notes, or a header typed by hand); otherwise the column is
 * appended after the last used one.
 */
function ensureColumns_(sh, defs) {
  var width = sh.getLastColumn();
  var titles = [];
  var notes = [];
  if (width > 0) {
    var header = sh.getRange(1, 1, 1, width);
    titles = header.getDisplayValues()[0];
    notes = header.getNotes()[0];
  }
  var cols = {};
  notes.forEach(function (note, i) {
    var id = noteId_(note);
    if (id && !cols[id]) { cols[id] = i + 1; }
  });

  defs.forEach(function (def) {
    if (cols[def.id]) { return; }
    var col = 0;
    var want = norm_(def.title);
    for (var i = 0; i < titles.length; i++) {
      if (want && !noteId_(notes[i]) && norm_(titles[i]) === want) { col = i + 1; break; }
    }
    if (!col) {
      col = width + 1;
      width = col;
      if (col > sh.getMaxColumns()) {
        sh.insertColumnsAfter(sh.getMaxColumns(), col - sh.getMaxColumns());
      }
      sh.getRange(1, col).setNumberFormat(TEXT_FORMAT)
        .setRichTextValue(richText_({ value: def.title }))
        .setFontWeight('bold').setWrap(true).setVerticalAlignment('top');
      if (def.width) { sh.setColumnWidth(col, def.width); }
      if (def.hidden) { sh.hideColumns(col); }
    }
    var note = NOTE_PREFIX + def.id + NOTE_HINT;
    sh.getRange(1, col).setNote(notes[col - 1] ? note + '\n' + notes[col - 1] : note);
    titles[col - 1] = def.title;
    notes[col - 1] = note;
    cols[def.id] = col;
  });
  if (sh.getFrozenRows() < 1) { sh.setFrozenRows(1); }
  return cols;
}

function noteId_(note) {
  var m = /(?:^|\s)moodle:(\S+)/.exec(note || '');
  return m ? m[1] : '';
}

function norm_(s) {
  return String(s || '').replace(/\s+/g, ' ').trim().toLowerCase();
}

/** Keys of the rows already in the sheet. */
function keys_(sh, col) {
  var seen = {};
  var last = sh.getLastRow();
  if (!col || last < 2) { return seen; }
  sh.getRange(2, col, last - 1, 1).getDisplayValues().forEach(function (r) {
    if (r[0]) { seen[String(r[0]).trim()] = true; }
  });
  return seen;
}

/** Keys remembered by versions 1–4 in ScriptProperties (read only, no longer written). */
function legacySeen_() {
  var seen = {};
  try {
    JSON.parse(PropertiesService.getScriptProperties().getProperty('seen') || '[]')
      .forEach(function (k) { seen[k] = true; });
  } catch (ignore) { /* nothing remembered */ }
  return seen;
}

// ---------------------------------------------------------------------------
// Rows. A row is a list of cells { id, value, link?, wrap? }; id names the column.

function when_(d) {
  return d.timestamp ? new Date(d.timestamp * 1000) : new Date();
}

function questionCells_(d, key) {
  var parts = { participate: [], question: [], other: [] };
  (d.answers || []).forEach(function (a) {
    if (!a.value) { return; }
    var target = 'other';
    for (var i = 0; i < COLUMN_RULES.length; i++) {
      if (COLUMN_RULES[i].re.test(a.name || '')) { target = COLUMN_RULES[i].col; break; }
    }
    parts[target].push(target === 'other' ? (a.name + ': ' + a.value) : a.value);
  });
  var u = (!d.anonymous && d.user) ? d.user : null;
  return [
    { id: 'time',        value: when_(d) },
    { id: 'name',        value: u ? u.fullname : 'Аноним' },
    { id: 'city',        value: u ? (u.city || '') : '' },
    { id: 'participate', value: parts.participate.join(', ') },
    { id: 'question',    value: parts.question.concat(parts.other).join('\n\n'), wrap: true },
    { id: 'groups',      value: u ? (u.groups || []).join(', ') : '' },
    { id: 'email',       value: u ? (u.email || '') : '' },
    { id: 'url',         value: d.responseurl ? 'открыть' : '', link: d.responseurl },
    { id: 'form',        value: (d.feedback && d.feedback.name) || '' },
    { id: 'key',         value: key }
  ];
}

function itemId_(a) {
  return 'item' + a.itemid;
}

/** Column definitions of a generic table: fixed lead, one column per form item, fixed tail. */
function genericColumns_(responses) {
  var items = [];
  var known = {};
  responses.forEach(function (d) {
    (d.answers || []).forEach(function (a) {
      var id = itemId_(a);
      if (known[id]) { return; }
      known[id] = true;
      items.push({ id: id, title: norm_(a.name) ? String(a.name).replace(/\s+/g, ' ').trim() : ('Пункт ' + a.itemid), width: 180 });
    });
  });
  return GENERIC_LEAD.concat(items, GENERIC_TAIL);
}

function genericCells_(d, key) {
  var u = (!d.anonymous && d.user) ? d.user : null;
  var cells = [
    { id: 'time',  value: when_(d) },
    { id: 'user',  value: u ? u.fullname : 'Аноним' },
    { id: 'email', value: u ? (u.email || '') : '' }
  ];
  (d.answers || []).forEach(function (a) {
    cells.push({ id: itemId_(a), value: a.value || '' });
  });
  cells.push({ id: 'groups', value: u ? (u.groups || []).join(', ') : '' });
  cells.push({ id: 'url',    value: d.responseurl ? 'открыть' : '', link: d.responseurl });
  cells.push({ id: 'key',    value: key });
  return cells;
}

/**
 * Sheets parses what a script writes the way it parses typing, whatever the call
 * and the cell format: "+79991234567" turns into a number or into "=+79991234567",
 * "=HYPERLINK(…)" from a student into a live formula, "12:30" into a time,
 * "2631:1790336304" into a duration. The one thing that keeps an answer exactly as
 * typed is the leading apostrophe, Sheets' own "this is text" mark: it is not shown
 * and not part of the value. Checked on a live spreadsheet, 17 kinds of input.
 */
function literal_(text) {
  return text === '' ? '' : "'" + text;
}

/** Rich text, because a cell may carry a link; the text of a link is ours and needs no mark. */
function richText_(cell) {
  var text = (cell && cell.value !== null && cell.value !== undefined) ? String(cell.value) : '';
  if (text.length > MAX_CELL) { text = text.substring(0, MAX_CELL) + '…'; }
  if (cell && cell.link && text) {
    return SpreadsheetApp.newRichTextValue().setText(text).setLinkUrl(cell.link).build();
  }
  return SpreadsheetApp.newRichTextValue().setText(literal_(text)).build();
}

/**
 * Text cells also get the "plain text" format, so that a phone number corrected by
 * hand stays text.
 */
function writeRows_(sh, cols, rows) {
  if (!rows.length) { return 0; }
  var first = sh.getLastRow() + 1;
  var need = first + rows.length - 1;
  if (need > sh.getMaxRows()) {
    sh.insertRowsAfter(sh.getMaxRows(), need - sh.getMaxRows() + 200);
  }

  var kinds = {};   // column number → 'date' | 'text'
  var wraps = {};
  var matrix = rows.map(function (cells) {
    var byCol = {};
    cells.forEach(function (c) {
      var col = cols[c.id];
      if (!col) { return; }
      byCol[col] = c;
      kinds[col] = (c.value instanceof Date) ? 'date' : (kinds[col] || 'text');
      if (c.wrap) { wraps[col] = true; }
    });
    return byCol;
  });

  // Adjacent columns of one kind are written with a single call.
  var runs = [];
  Object.keys(kinds).map(Number).sort(function (a, b) { return a - b; }).forEach(function (col) {
    var last = runs[runs.length - 1];
    if (last && last.kind === kinds[col] && last.start + last.width === col) {
      last.width++;
    } else {
      runs.push({ kind: kinds[col], start: col, width: 1 });
    }
  });

  runs.forEach(function (run) {
    var range = sh.getRange(first, run.start, rows.length, run.width);
    var values = matrix.map(function (byCol) {
      var out = [];
      for (var i = 0; i < run.width; i++) {
        var c = byCol[run.start + i];
        out.push(run.kind === 'date' ? (c ? c.value : '') : richText_(c));
      }
      return out;
    });
    if (run.kind === 'date') {
      range.setNumberFormat(DATE_FORMAT).setValues(values);
    } else {
      range.setNumberFormat(TEXT_FORMAT).setRichTextValues(values);
    }
  });
  Object.keys(wraps).forEach(function (col) {
    sh.getRange(first, Number(col), rows.length, 1).setWrap(true);
  });
  return first;
}

// ---------------------------------------------------------------------------
// Manual checks from the editor (no Moodle needed). Delete the rows afterwards.

function testInsert() {
  Logger.log(JSON.stringify(handle_([{
    completedid: 0,
    timestamp: Math.floor(Date.now() / 1000),
    anonymous: false,
    user: { fullname: 'Тест Тестов', email: 'test@example.com', city: 'Хайфа', groups: ['11ж'] },
    feedback: { name: 'Вопрос по теме урока 1 к вебинару с преподавателями' },
    responseurl: 'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=13407',
    answers: [
      { itemid: 1, name: 'Буду присутствовать на вебинаре', value: 'в 17:00 изр' },
      { itemid: 2, name: 'Ваш вопрос по теме урока 1', value: '- что значит «намерение»?\n=1+1' }
    ]
  }])));
}

/** Set the id of a table whose title contains TARGET_TITLE_MARK before running. */
function testGeneric() {
  var spreadsheet = '';
  if (!spreadsheet) { throw new Error('set the spreadsheet id in testGeneric()'); }
  Logger.log(JSON.stringify(handle_([{
    completedid: 0,
    timestamp: Math.floor(Date.now() / 1000),
    anonymous: false,
    target: { spreadsheet: spreadsheet, layout: 'generic' },
    user: { fullname: 'Тест Тестов', email: 'test@example.com', groups: [] },
    responseurl: 'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=12951',
    answers: [
      { itemid: 1, name: 'Ваше имя', value: 'Тест' },
      { itemid: 2, name: 'Номер телефона в WhatsApp', value: '+972501234567' }
    ]
  }])));
}
