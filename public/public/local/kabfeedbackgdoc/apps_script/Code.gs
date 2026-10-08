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
 * A re-submission (the form allows multiple submissions, the student changed their
 * mind) is a new row, as any response. It is marked in the "Повторная отправка"
 * column, the answers that differ from the previous row are highlighted, and the
 * previous row is greyed out with a pointer to the new one. See markRepeat_().
 *
 * When Moodle reports a form closed ("feedback_archive"), the rows of that form
 * leave the time tabs for the ARCHIVE_TAB tab, colours and answers included. See archive_().
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

var VERSION = 8;
var TARGET_TITLE_MARK = '(Moodle)'; // a spreadsheet must carry this in its title to accept rows; '' = no check
var FONT_SIZE = 13;            // everything the script writes; applyFontSize() restyles what is already there
// "questions" layout: when Moodle reports a form closed, its rows leave the time tabs for this tab.
var ARCHIVE_TAB = 'Архив';
// "questions" layout: a tab per webinar time ("8:00", "17:00", "20:00"), taken from the
// "Буду участвовать" answer, so that each webinar's teachers see their own questions.
// Tabs are created on demand, in clock order, before the other tabs.
var TIME_TABS = true;
var NO_TIME_TAB = 'Без времени'; // where a question without a recognisable time goes
var NOTE_PREFIX = 'moodle:';
var NOTE_HINT = '\nСлужебная метка: по ней скрипт находит колонку. Заголовок можно переименовать, колонку — двигать.';
var DATE_FORMAT = 'dd.MM.yyyy H:mm:ss';
var TEXT_FORMAT = '@';         // "plain text" number format, see literal_()
var MAX_CELL = 49000;          // Sheets refuses cells over 50 000 characters
var CHANGED_BG = '#fff2cc';    // a re-submitted answer that differs from the previous one
var OLD_BG = '#efefef';        // the row a re-submission replaced
var OLD_FG = '#888888';

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
  { id: 'repeat',      title: 'Повторная отправка', width: 220 },
  { id: 'answer',      title: 'Ответ преподавателя' },
  { id: 'key',         title: 'ID ответа', hidden: true }
];

var GENERIC_LEAD = [
  { id: 'time',   title: 'Отметка времени', width: 140 },
  { id: 'user',   title: 'Имя в Moodle', width: 180 },
  { id: 'email',  title: 'Email', width: 200 },
  { id: 'repeat', title: 'Повторная отправка', width: 220 }
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
    if (data.event === 'feedback_archive') {
      return respond(archive_(data));
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
 * feedback_completed record: an edited answer must become a new row. Such a row
 * (a completed id the table already has) is marked, see markRepeat_().
 */
function handle_(responses) {
  var groups = {};
  var order = [];
  responses.forEach(function (d) {
    var t = d.target || {};
    var generic = t.layout === 'generic';
    var sheet = t.sheet || '';
    if (!generic && !sheet && TIME_TABS) {
      sheet = timeTab_(d) || NO_TIME_TAB;
    }
    var g = [t.spreadsheet || '', sheet, generic ? 'generic' : 'questions'].join('|');
    if (!groups[g]) {
      groups[g] = { spreadsheet: t.spreadsheet || '', sheet: sheet, generic: generic, items: [] };
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
    // The previous row of a re-submitted question may sit on another time tab.
    var index = index_(sh, cols, !group.generic);
    var rows = [];
    var repeats = [];
    group.items.forEach(function (d) {
      var key = key_(d);
      var id = Number(d.completedid); // completedid 0 = test payloads, never deduped, never a repeat
      if (id) {
        if (index.seen[key] || legacy[String(d.completedid) + ':' + String(d.timestamp)]) { result.duplicates++; return; }
        index.seen[key] = true;
      }
      var cells = group.generic ? genericCells_(d, key) : questionCells_(d, key);
      var at = rows.length;
      rows.push(cells);
      if (id) {
        var prev = latest_(index.byId[id]);
        if (prev) { repeats.push({ at: at, cells: cells, prev: prev }); }
        (index.byId[id] = index.byId[id] || []).push({ sheet: sh, at: at, cells: cells, ts: Number(d.timestamp) || 0 });
      }
    });
    var first = writeRows_(sh, cols, rows);
    repeats.forEach(function (r) {
      markRepeat_(sh, cols, first + r.at, r.cells, r.prev, first);
    });
    result.written += rows.length;
    if (repeats.length) { result.repeats = (result.repeats || 0) + repeats.length; }
    result.tables.push(sh.getParent().getName());
  });
  if (responses.length === 1 && result.duplicates === 1) { result.duplicate = true; }
  return result;
}

/**
 * Rows the table already has: every key (dedupe) and, per completed id, where
 * its rows are (repeats). With allTabs, every tab of the spreadsheet that has a
 * key column is read: the questions table keeps a tab per webinar time, and a
 * student who changed the time changed tabs with it.
 */
function index_(sh, cols, allTabs) {
  var index = { seen: {}, byId: {} };
  var sheets = allTabs ? sh.getParent().getSheets() : [sh];
  sheets.forEach(function (s) {
    var col = sameSheet_(s, sh) ? cols.key : keyCol_(s);
    var last = s.getLastRow();
    if (!col || last < 2) { return; }
    s.getRange(2, col, last - 1, 1).getDisplayValues().forEach(function (r, i) {
      var key = String(r[0] || '').trim();
      if (!key) { return; }
      index.seen[key] = true;
      var m = /^r(\d+)t(\d+)$/.exec(key);
      if (m) {
        (index.byId[m[1]] = index.byId[m[1]] || []).push({ sheet: s, row: i + 2, ts: Number(m[2]) });
      }
    });
  });
  return index;
}

/** Column id → column number of a tab, from the header notes, without creating anything. */
function noteColumns_(s) {
  var cols = {};
  var width = s.getLastColumn();
  if (width < 1) { return cols; }
  s.getRange(1, 1, 1, width).getNotes()[0].forEach(function (note, i) {
    var id = noteId_(note);
    if (id && !cols[id]) { cols[id] = i + 1; }
  });
  return cols;
}

/** The key column of a tab, found by its header note; 0 when the tab has none. */
function keyCol_(s) {
  return noteColumns_(s).key || 0;
}

/**
 * Moodle reports a form closed ("feedback_archive" with form.cmid): its rows leave
 * every tab of the table for ARCHIVE_TAB, in order of time, with all their columns,
 * colours and the teachers' answers. A row belongs to a form by the cmid in its
 * "Ссылка в Moodle" link (…show_entries.php?id=<cmid>…); a row without a link, by
 * the form name. Asking again moves nothing more.
 */
function archive_(d) {
  var t = d.target || {};
  var cmid = Number(d.form && d.form.cmid);
  if (!cmid) { throw new Error('form.cmid missing'); }
  var name = norm_(d.form && d.form.name);
  var archive = sheet_(t.spreadsheet, ARCHIVE_TAB);
  var ss = archive.getParent();
  var records = [];
  var from = {};
  ss.getSheets().forEach(function (s) {
    if (sameSheet_(s, archive)) { return; }
    var cols = noteColumns_(s);
    var last = s.getLastRow();
    if (!cols.key || !cols.url || last < 2) { return; }
    var links = s.getRange(2, cols.url, last - 1, 1).getRichTextValues();
    var forms = cols.form ? s.getRange(2, cols.form, last - 1, 1).getDisplayValues() : null;
    var rows = [];
    for (var i = 0; i < last - 1; i++) {
      var link = links[i][0] ? links[i][0].getLinkUrl() : null;
      var m = link ? /[?&]id=(\d+)/.exec(link) : null;
      var mine = m ? Number(m[1]) === cmid : (!!name && !!forms && norm_(forms[i][0]) === name);
      if (mine) { rows.push(i + 2); }
    }
    if (!rows.length) { return; }
    records = records.concat(readRows_(s, cols, rows));
    deleteRows_(s, rows);
    from[s.getName()] = rows.length;
  });
  records.sort(function (a, b) { return a.time - b.time; });
  appendRecords_(archive, ensureColumns_(archive, QUESTION_COLUMNS), records);
  return { ok: true, moved: records.length, from: from, table: ss.getName() };
}

/** Apps Script hands out a new Sheet object on every call, so identity is by id. */
function sameSheet_(a, b) {
  return a.getSheetId() === b.getSheetId();
}

/** The most recent of a response's rows: the latest timestamp, the last row on a tie. */
function latest_(entries) {
  var best = null;
  (entries || []).forEach(function (e) {
    if (!best || e.ts >= best.ts) { best = e; }
  });
  return best;
}

/** Whether a column holds an answer of the student (what a re-submission may change). */
function compared_(id) {
  return /^item\d+$/.test(id) || id === 'participate' || id === 'question';
}

function sameText_(a, b) {
  return String(a || '').replace(/\s+/g, ' ').trim() === String(b || '').replace(/\s+/g, ' ').trim();
}

/** Column id → text of a row, as the sheet shows it. */
function rowValues_(s, cols, row) {
  var values = s.getRange(row, 1, 1, s.getLastColumn()).getDisplayValues()[0];
  var out = {};
  Object.keys(cols).forEach(function (id) { out[id] = values[cols[id] - 1]; });
  return out;
}

/** Column id → text of a row that is still in memory (written in this very batch). */
function cellValues_(cells) {
  var out = {};
  cells.forEach(function (c) { out[c.id] = (c.value instanceof Date) ? '' : String(c.value || ''); });
  return out;
}

function where_(s, row, here) {
  return 'строка ' + row + (sameSheet_(s, here) ? '' : ' на вкладке «' + s.getName() + '»');
}

function setText_(s, row, col, text) {
  if (!col) { return; }
  s.getRange(row, col).setNumberFormat(TEXT_FORMAT).setRichTextValue(richText_({ value: text })).setWrap(true);
}

/**
 * A re-submission: the new row says it is one and names the previous row, the
 * answers that changed get CHANGED_BG; the previous row is greyed out and points
 * to the new one. The previous row may be on another tab (questions layout), or
 * earlier in the same batch (then prev.at is its index and prev.cells its values).
 */
function markRepeat_(sh, cols, row, cells, prev, first) {
  var prevSheet = prev.sheet;
  var prevRow = (prev.at !== undefined) ? first + prev.at : prev.row;
  var prevCols = sameSheet_(prevSheet, sh) ? cols : ensureColumns_(prevSheet, QUESTION_COLUMNS);
  var titles = sh.getRange(1, 1, 1, sh.getLastColumn()).getDisplayValues()[0];
  var now = cellValues_(cells);
  var before = prev.cells ? cellValues_(prev.cells) : rowValues_(prevSheet, prevCols, prevRow);
  var changed = Object.keys(cols).filter(function (id) {
    return compared_(id) && !sameText_(now[id], before[id]);
  });

  var text = 'Повторная отправка, прежний ответ — ' + where_(prevSheet, prevRow, sh) + '. ' +
    (changed.length ? 'Изменилось: ' + changed.map(function (id) { return titles[cols[id] - 1]; }).join('; ') : 'Ответы те же');
  setText_(sh, row, cols.repeat, text);
  changed.forEach(function (id) { sh.getRange(row, cols[id]).setBackground(CHANGED_BG); });
  if (cols.repeat) { sh.getRange(row, cols.repeat).setBackground(CHANGED_BG); }

  prevSheet.getRange(prevRow, 1, 1, prevSheet.getLastColumn()).setBackground(OLD_BG).setFontColor(OLD_FG);
  setText_(prevSheet, prevRow, prevCols.repeat, 'Устарел, новый ответ — ' + where_(sh, row, prevSheet));
}

/**
 * Key of a row, e.g. "r2631t1790336304". Letters on purpose: Sheets reads
 * "2631:1790336304" as a duration and stores something else.
 */
function key_(d) {
  return 'r' + String(d.completedid) + 't' + String(d.timestamp);
}

/** Name of the time tab for a "participate" answer: "в 8:00 изр" → "8:00"; '' when there is no time in it. */
function timeTabOf_(value) {
  var m = /(\d{1,2}):(\d{2})/.exec(String(value || ''));
  return m ? (parseInt(m[1], 10) + ':' + m[2]) : '';
}

/** Minutes since midnight for a time tab name, null for any other tab. */
function tabMinutes_(name) {
  var m = /^(\d{1,2}):(\d{2})$/.exec(String(name || ''));
  return m ? parseInt(m[1], 10) * 60 + parseInt(m[2], 10) : null;
}

/** Time tab of a response (questions layout): from its "participate" answer. */
function timeTab_(d) {
  var rule = COLUMN_RULES.filter(function (r) { return r.col === 'participate'; })[0];
  var tab = '';
  (d.answers || []).forEach(function (a) {
    if (!tab && rule && rule.re.test(a.name || '')) { tab = timeTabOf_(a.value); }
  });
  return tab;
}

/** Where a new tab goes: time tabs first, in clock order; anything else after the existing tabs. */
function tabIndex_(ss, name) {
  var minutes = tabMinutes_(name);
  var sheets = ss.getSheets();
  if (minutes === null) { return sheets.length; }
  var index = 0;
  sheets.forEach(function (s) {
    var m = tabMinutes_(s.getName());
    if (m !== null && m < minutes) { index++; }
  });
  return index;
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
  if (!sh && name) { sh = ss.insertSheet(name, tabIndex_(ss, name)); }
  if (!sh) { throw new Error('sheet not found'); }
  return sh;
}

/**
 * Map column id → column number, creating what is missing.
 * A header without a note but with the expected title is adopted (tables that
 * existed before notes, or a header typed by hand); otherwise the column is
 * appended after the last used one - or, when the hidden key column is the last
 * one, inserted in front of it, so that a new visible column does not end up
 * behind a hidden one.
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
      if (def.id !== 'key' && cols.key && cols.key === width) {
        col = cols.key;
        sh.insertColumnBefore(col);
        sh.showColumns(col);
        titles.splice(col - 1, 0, '');
        notes.splice(col - 1, 0, '');
        Object.keys(cols).forEach(function (id) { if (cols[id] >= col) { cols[id]++; } });
        width++;
      } else {
        col = width + 1;
        width = col;
        if (col > sh.getMaxColumns()) {
          sh.insertColumnsAfter(sh.getMaxColumns(), col - sh.getMaxColumns());
        }
      }
      sh.getRange(1, col).setNumberFormat(TEXT_FORMAT)
        .setRichTextValue(richText_({ value: def.title }))
        .setFontWeight('bold').setFontSize(FONT_SIZE).setWrap(true).setVerticalAlignment('top');
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
  sh.getRange(first, 1, rows.length, sh.getLastColumn()).setFontSize(FONT_SIZE);
  return first;
}

// ---------------------------------------------------------------------------
// Moving rows between tabs. A record is a row lifted off a tab with everything the
// script knows about it: cells by column id, link, backgrounds and font colours.

/** Lift the given rows (row numbers) off a tab. */
function readRows_(s, cols, rowNumbers) {
  var last = s.getLastRow();
  var width = s.getLastColumn();
  var range = s.getRange(2, 1, last - 1, width);
  var values = range.getValues();
  var rich = range.getRichTextValues();
  var bgs = range.getBackgrounds();
  var fgs = range.getFontColors();
  return rowNumbers.map(function (r) {
    var i = r - 2;
    var rec = { cells: [], bg: {}, fg: {}, time: 0 };
    Object.keys(cols).forEach(function (id) {
      var c = cols[id] - 1;
      var link = rich[i][c] ? rich[i][c].getLinkUrl() : null;
      rec.cells.push({ id: id, value: values[i][c], link: link || undefined, wrap: id === 'question' || id === 'repeat' });
      rec.bg[id] = bgs[i][c];
      rec.fg[id] = fgs[i][c];
      if (id === 'time' && values[i][c] instanceof Date) { rec.time = values[i][c].getTime(); }
    });
    return rec;
  });
}

/** Append records to a tab, colours included (the grey of an outdated row, the yellow of a change). */
function appendRecords_(to, cols, records) {
  if (!records.length) { return 0; }
  var first = writeRows_(to, cols, records.map(function (r) { return r.cells; }));
  var width = to.getLastColumn();
  var bg = records.map(function () { var row = []; for (var c = 0; c < width; c++) { row.push('#ffffff'); } return row; });
  var fg = records.map(function () { var row = []; for (var c = 0; c < width; c++) { row.push('#000000'); } return row; });
  records.forEach(function (rec, i) {
    Object.keys(cols).forEach(function (id) {
      if (rec.bg[id]) { bg[i][cols[id] - 1] = rec.bg[id]; }
      if (rec.fg[id]) { fg[i][cols[id] - 1] = rec.fg[id]; }
    });
  });
  to.getRange(first, 1, records.length, width).setBackgrounds(bg).setFontColors(fg);
  return first;
}

/** Delete rows (row numbers, any order) from a tab, bottom up, in runs. */
function deleteRows_(s, rowNumbers) {
  var rows = rowNumbers.slice().sort(function (a, b) { return b - a; });
  var i = 0;
  while (i < rows.length) {
    var end = rows[i];
    var start = end;
    while (i + 1 < rows.length && rows[i + 1] === start - 1) { i++; start = rows[i]; }
    s.deleteRows(start, end - start + 1);
    i++;
  }
}

// ---------------------------------------------------------------------------
// Maintenance, run from the editor.

/**
 * After switching TIME_TABS on: moves the rows that landed in the former default
 * tab (SHEET_NAME, or the first tab that is not a time tab) into the time tabs -
 * every column, the teachers' answers included - and names that tab NO_TIME_TAB.
 * Rows without a recognisable time stay. Safe to run again: it only moves what is there.
 */
function moveRowsByTime() {
  var ss = SPREADSHEET_ID ? SpreadsheetApp.openById(SPREADSHEET_ID) : SpreadsheetApp.getActiveSpreadsheet();
  var from = SHEET_NAME ? ss.getSheetByName(SHEET_NAME)
    : ss.getSheets().filter(function (s) { return tabMinutes_(s.getName()) === null && s.getName() !== ARCHIVE_TAB; })[0];
  if (!from) { throw new Error('no tab to move rows from'); }
  var cols = ensureColumns_(from, QUESTION_COLUMNS);
  var moved = {};
  var last = from.getLastRow();
  if (last >= 2) {
    var slots = from.getRange(2, cols.participate, last - 1, 1).getDisplayValues();
    var byTab = {};
    slots.forEach(function (r, i) {
      var tab = timeTabOf_(r[0]);
      if (tab) { (byTab[tab] = byTab[tab] || []).push(i + 2); }
    });
    var remove = [];
    Object.keys(byTab).forEach(function (tab) {
      var records = readRows_(from, cols, byTab[tab]);
      var sh = sheet_(SPREADSHEET_ID, tab);
      appendRecords_(sh, ensureColumns_(sh, QUESTION_COLUMNS), records);
      moved[tab] = records.length;
      remove = remove.concat(byTab[tab]);
    });
    deleteRows_(from, remove);
  }
  if (from.getName() !== NO_TIME_TAB && !ss.getSheetByName(NO_TIME_TAB)) {
    from.setName(NO_TIME_TAB);
  }
  moved.left = from.getLastRow() - 1;
  Logger.log(JSON.stringify(moved));
  return moved;
}

/**
 * The archive took a form too early (08.10.2026: the form closed at 19:00, the rows
 * were needed at the 20:00 webinar): put the rows of the form named in UNARCHIVE_CMID
 * back on their time tabs. Then drop its cmid from the plugin's "archivedcmids"
 * setting, or the task will not archive it again. Safe to run again.
 */
var UNARCHIVE_CMID = 0;
function unarchiveForm() {
  var cmid = Number(UNARCHIVE_CMID);
  if (!cmid) { throw new Error('set UNARCHIVE_CMID first'); }
  var ss = SPREADSHEET_ID ? SpreadsheetApp.openById(SPREADSHEET_ID) : SpreadsheetApp.getActiveSpreadsheet();
  var archive = ss.getSheetByName(ARCHIVE_TAB);
  var out = { moved: {}, left: 0 };
  if (archive) {
    var cols = noteColumns_(archive);
    var last = archive.getLastRow();
    if (cols.key && cols.url && cols.participate && last >= 2) {
      var links = archive.getRange(2, cols.url, last - 1, 1).getRichTextValues();
      var slots = archive.getRange(2, cols.participate, last - 1, 1).getDisplayValues();
      var byTab = {};
      for (var i = 0; i < last - 1; i++) {
        var link = links[i][0] ? links[i][0].getLinkUrl() : null;
        var m = link ? /[?&]id=(\d+)/.exec(link) : null;
        if (!m || Number(m[1]) !== cmid) { continue; }
        var tab = timeTabOf_(slots[i][0]) || NO_TIME_TAB;
        (byTab[tab] = byTab[tab] || []).push(i + 2);
      }
      var remove = [];
      Object.keys(byTab).forEach(function (tab) {
        var records = readRows_(archive, cols, byTab[tab]);
        var sh = sheet_(SPREADSHEET_ID, tab);
        appendRecords_(sh, ensureColumns_(sh, QUESTION_COLUMNS), records);
        out.moved[tab] = records.length;
        remove = remove.concat(byTab[tab]);
      });
      deleteRows_(archive, remove);
    }
    out.left = archive.getLastRow() - 1;
  }
  Logger.log(JSON.stringify(out));
  return out;
}

/**
 * Font size FONT_SIZE for everything already in a table (the script writes new rows
 * that way itself). Without an argument - the default table.
 */
function applyFontSize(spreadsheetId) {
  var id = spreadsheetId || SPREADSHEET_ID;
  var ss = id ? SpreadsheetApp.openById(id) : SpreadsheetApp.getActiveSpreadsheet();
  var done = {};
  ss.getSheets().forEach(function (s) {
    var rows = s.getLastRow();
    var cols = s.getLastColumn();
    if (rows < 1 || cols < 1) { return; }
    s.getRange(1, 1, rows, cols).setFontSize(FONT_SIZE);
    done[s.getName()] = rows;
  });
  Logger.log(JSON.stringify(done));
  return done;
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
