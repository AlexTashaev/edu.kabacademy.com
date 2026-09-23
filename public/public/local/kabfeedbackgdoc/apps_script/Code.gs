/**
 * Google Apps Script bound to the teachers' Google Sheet
 * ("Задать вопрос преподавателю … (Moodle)").
 * Receives feedback responses from Moodle (local_kabfeedbackgdoc) and appends
 * one row per response in the same layout as the Google Forms "(Ответы)" sheets:
 *
 *   Отметка времени | Имя | Город | Буду участвовать в вебинаре | Мой вопрос |
 *   Номер группы | Email | Ссылка в Moodle | Ответ преподавателя
 *
 * Setup (once):
 *   1. Open the Google Sheet → Extensions → Apps Script.
 *   2. Replace the default Code.gs with this file, set SECRET below.
 *   3. Deploy → New deployment → type "Web app":
 *        Execute as: Me;  Who has access: Anyone.
 *      Authorize when asked. Copy the Web app URL (…/exec).
 *   4. Moodle → Site administration → Plugins → Local plugins →
 *      "Feedback → Google Doc (KAB)": paste the URL and the same SECRET.
 *
 * After changing this code: Deploy → Manage deployments → edit → New version,
 * otherwise the /exec URL keeps running the old version.
 */

var SECRET = 'CHANGE_ME';      // must equal the plugin's "Shared secret" setting
var SPREADSHEET_ID = '';       // '' = the spreadsheet this script is bound to; set an id for a standalone script
var SHEET_NAME = '';           // '' = first sheet of the spreadsheet
var TIMEZONE = 'Asia/Jerusalem'; // spreadsheet time zone; new sheets default to US Pacific, so we fix it on first write
var MAX_REMEMBERED_IDS = 1000; // dedupe window (completed ids already inserted)

// Which feedback item goes to which column, matched by item name (case-insensitive regex).
var COLUMN_RULES = [
  { col: 'participate', re: /присутств|участв/i },
  { col: 'question',    re: /вопрос/i }
];

var HEADER = ['Отметка времени', 'Имя', 'Город', 'Буду участвовать в вебинаре', 'Мой вопрос',
              'Номер группы', 'Email', 'Ссылка в Moodle', 'Ответ преподавателя'];

function doPost(e) {
  var lock = LockService.getScriptLock();
  lock.waitLock(30000);
  try {
    var data = JSON.parse((e && e.postData && e.postData.contents) || '{}');
    if (SECRET && data.secret !== SECRET) {
      return respond({ ok: false, error: 'forbidden' });
    }
    if (data.event !== 'feedback_response') {
      return respond({ ok: false, error: 'unknown event' });
    }
    if (alreadyInserted_(data.completedid)) {
      return respond({ ok: true, duplicate: true });
    }
    appendRow_(data);
    remember_(data.completedid);
    return respond({ ok: true });
  } catch (err) {
    return respond({ ok: false, error: String(err) });
  } finally {
    lock.releaseLock();
  }
}

function doGet() {
  return respond({ ok: true, ping: 'local_kabfeedbackgdoc' });
}

function respond(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}

// ---------------------------------------------------------------------------

function sheet_() {
  var ss = SPREADSHEET_ID ? SpreadsheetApp.openById(SPREADSHEET_ID) : SpreadsheetApp.getActiveSpreadsheet();
  if (TIMEZONE && ss.getSpreadsheetTimeZone() !== TIMEZONE) {
    ss.setSpreadsheetTimeZone(TIMEZONE);
  }
  var sh = SHEET_NAME ? ss.getSheetByName(SHEET_NAME) : ss.getSheets()[0];
  if (!sh) { throw new Error('sheet not found: ' + SHEET_NAME); }
  if (sh.getLastRow() === 0) {
    sh.appendRow(HEADER);
    sh.getRange(1, 1, 1, HEADER.length).setFontWeight('bold');
    sh.setFrozenRows(1);
  }
  return sh;
}

function appendRow_(d) {
  var cols = { participate: [], question: [], other: [] };
  (d.answers || []).forEach(function (a) {
    if (!a.value) { return; }
    var target = 'other';
    for (var i = 0; i < COLUMN_RULES.length; i++) {
      if (COLUMN_RULES[i].re.test(a.name || '')) { target = COLUMN_RULES[i].col; break; }
    }
    cols[target].push(target === 'other' ? (a.name + ': ' + a.value) : a.value);
  });
  var question = cols.question.concat(cols.other).join('\n\n');

  var u = (!d.anonymous && d.user) ? d.user : null;
  var when = d.timestamp ? new Date(d.timestamp * 1000) : new Date();

  var row = [
    when,                                        // Отметка времени
    u ? u.fullname : 'Аноним',                   // Имя
    u ? (u.city || '') : '',                     // Город
    cols.participate.join(', '),                 // Буду участвовать в вебинаре
    question,                                    // Мой вопрос
    u ? (u.groups || []).join(', ') : '',        // Номер группы
    u ? (u.email || '') : '',                    // Email
    d.responseurl || '',                         // Ссылка в Moodle
    ''                                           // Ответ преподавателя
  ];

  var sh = sheet_();
  var r = sh.getLastRow() + 1;
  sh.getRange(r, 1, 1, row.length).setValues([row]);
  sh.getRange(r, 1).setNumberFormat('dd.MM.yyyy H:mm:ss');
  if (d.responseurl) {
    sh.getRange(r, 8).setRichTextValue(
      SpreadsheetApp.newRichTextValue().setText('открыть').setLinkUrl(d.responseurl).build());
  }
  sh.getRange(r, 5, 1, 1).setWrap(true);
}

// ---------------------------------------------------------------------------
// Dedupe: Moodle retries on failure, so the same completedid may arrive twice.

function alreadyInserted_(id) {
  if (!id) { return false; }
  var seen = JSON.parse(PropertiesService.getScriptProperties().getProperty('seen') || '[]');
  return seen.indexOf(Number(id)) !== -1;
}

function remember_(id) {
  if (!id) { return; }
  var props = PropertiesService.getScriptProperties();
  var seen = JSON.parse(props.getProperty('seen') || '[]');
  seen.push(Number(id));
  if (seen.length > MAX_REMEMBERED_IDS) {
    seen = seen.slice(seen.length - MAX_REMEMBERED_IDS);
  }
  props.setProperty('seen', JSON.stringify(seen));
}

/** Run manually from the editor to check the layout without Moodle. Delete the row afterwards. */
function testInsert() {
  appendRow_({
    completedid: 0,
    timestamp: Math.floor(Date.now() / 1000),
    anonymous: false,
    user: { fullname: 'Тест Тестов', email: 'test@example.com', city: 'Хайфа', groups: ['11ж'] },
    course: { fullname: 'МАК · осень 2026' },
    feedback: { name: 'Вопрос по теме урока 1 к вебинару с преподавателями', url: 'https://edu.kabacademy.com/mod/feedback/view.php?id=13407' },
    responseurl: 'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=13407',
    answers: [
      { name: 'Буду присутствовать на вебинаре', value: 'в 17:00 изр' },
      { name: 'Ваш вопрос по теме урока 1', value: 'Что значит «намерение» в практическом смысле?' }
    ]
  });
}
