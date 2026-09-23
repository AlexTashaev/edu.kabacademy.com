/**
 * Google Apps Script bound to the teachers' Google Doc.
 * Receives feedback responses from Moodle (local_kabfeedbackgdoc) and
 * inserts them into the document, newest first, with an empty
 * "Ответ преподавателя:" line under each question.
 *
 * Setup (once):
 *   1. Open the Google Doc → Extensions → Apps Script.
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
var NEWEST_FIRST = true;       // insert new entries above older ones
var ANSWER_LABEL = 'Ответ преподавателя:';
var MAX_REMEMBERED_IDS = 500;  // dedupe window (completed ids already inserted)

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
    insertEntry_(data);
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

function insertEntry_(d) {
  var body = DocumentApp.getActiveDocument().getBody();
  var at = NEWEST_FIRST ? firstEntryIndex_(body) : body.getNumChildren();
  var ins = new Inserter_(body, at);

  var who = d.anonymous || !d.user ? 'Аноним' : d.user.fullname;
  var h = ins.paragraph(d.datetime + ' — ' + who);
  h.setHeading(DocumentApp.ParagraphHeading.HEADING2);

  var meta = ins.paragraph('');
  meta.appendText(d.feedback.name).setLinkUrl(d.feedback.url);
  meta.appendText(' · ' + d.course.fullname);
  if (d.user && d.user.email) {
    meta.appendText(' · ');
    meta.appendText(d.user.email).setLinkUrl('mailto:' + d.user.email);
  }
  meta.appendText(' · ');
  meta.appendText('ответ в Moodle').setLinkUrl(d.responseurl);
  meta.editAsText().setFontSize(9).setForegroundColor('#666666');

  (d.answers || []).forEach(function (a) {
    if (!a.value) { return; }
    var p = ins.paragraph('');
    p.appendText(a.name + ': ').setBold(true);
    p.appendText(a.value).setBold(false);
  });

  var ans = ins.paragraph('');
  ans.appendText(ANSWER_LABEL + ' ').setBold(true).setForegroundColor('#1a73e8');
  ans.appendText(' ').setBold(false).setForegroundColor('#000000');

  ins.rule();
}

/** Index of the first HEADING2 paragraph (= newest existing entry), or end of body. */
function firstEntryIndex_(body) {
  var n = body.getNumChildren();
  for (var i = 0; i < n; i++) {
    var el = body.getChild(i);
    if (el.getType() === DocumentApp.ElementType.PARAGRAPH &&
        el.asParagraph().getHeading() === DocumentApp.ParagraphHeading.HEADING2) {
      return i;
    }
  }
  return n;
}

/** Sequential inserter that keeps a running index so entries stay in order. */
function Inserter_(body, index) {
  this.body = body;
  this.i = index;
}
Inserter_.prototype.paragraph = function (text) {
  var p = this.body.insertParagraph(this.i++, text);
  p.setHeading(DocumentApp.ParagraphHeading.NORMAL);
  return p;
};
Inserter_.prototype.rule = function () {
  this.body.insertHorizontalRule(this.i++);
};

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

/** Run manually from the editor to check the layout without Moodle. */
function testInsert() {
  insertEntry_({
    completedid: 0,
    datetime: '23.09.2026 14:05',
    anonymous: false,
    user: { fullname: 'Тест Тестов', email: 'test@example.com' },
    course: { fullname: 'МАК · осень 2026' },
    feedback: { name: 'Вопрос по теме урока 1 к вебинару с преподавателями', url: 'https://edu.kabacademy.com/mod/feedback/view.php?id=13407' },
    responseurl: 'https://edu.kabacademy.com/mod/feedback/show_entries.php?id=13407',
    answers: [
      { name: 'Буду присутствовать на вебинаре', value: 'в 17:00 изр' },
      { name: 'Ваш вопрос по теме урока 1', value: 'Что значит «намерение» в практическом смысле?' }
    ]
  });
}
