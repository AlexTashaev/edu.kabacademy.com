/**
 * «Активность студентов КК1»: принимает выгрузку kk1_activity.py и кладёт её
 * в таблицу новой вкладкой (имя — дата выгрузки, дд.мм.гггг) первой слева.
 * Повтор в тот же день заменяет вкладку этого дня; прошлые недели остаются.
 *
 * Установка: в таблице «Расширения → Apps Script», вставить этот файл, задать
 * SECRET (тот же, что в config.json выгрузки), Deploy → New deployment → Web app,
 * Execute as: Me, Who has access: Anyone. exec-URL деплоя — в config.json.
 * Обновление кода: Deploy → Manage deployments → карандаш → Version: New version
 * (New deployment дал бы новый URL).
 */
var SECRET = 'CHANGE_ME';
var VERSION = 1;
var DONE_BG = '#d9ead3';

function doPost(e) {
  var lock = LockService.getScriptLock();
  lock.waitLock(30000);
  try {
    var data = JSON.parse(e.postData.contents);
    if (!SECRET || SECRET === 'CHANGE_ME' || data.secret !== SECRET) {
      return respond({ ok: false, error: 'secret' });
    }
    var name = write_(data);
    return respond({ ok: true, sheet: name, rows: data.rows.length, version: VERSION });
  } catch (err) {
    return respond({ ok: false, error: String(err && err.stack || err) });
  } finally {
    lock.releaseLock();
  }
}

function doGet() {
  return respond({ ok: true, version: VERSION });
}

function respond(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}

function write_(data) {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var head = data.headers, rows = data.rows;
  var width = head.length, height = rows.length + 1;

  // Сначала собираем во временной вкладке: если что-то упадёт, прошлая выгрузка цела.
  var tmp = ss.getSheetByName(data.title + ' …');
  if (tmp) ss.deleteSheet(tmp);
  var sh = ss.insertSheet(data.title + ' …', 0);
  fit_(sh, height, width);

  var text = {}, date = {};
  (data.textCols || []).forEach(function (c) { text[c] = true; });
  (data.dateCols || []).forEach(function (c) { date[c] = true; });
  var values = [head].concat(rows.map(function (r) {
    return r.map(function (v, c) {
      if (v === '' || v === null) return '';
      if (text[c]) return "'" + v;
      if (date[c]) {
        var p = String(v).split('-');
        return new Date(+p[0], +p[1] - 1, +p[2], 12); // полдень: дата не съедет при разных поясах скрипта и таблицы
      }
      return v;
    });
  }));
  sh.getRange(1, 1, height, width).setValues(values);
  (data.dateCols || []).forEach(function (c) {
    sh.getRange(2, c + 1, Math.max(height - 1, 1), 1).setNumberFormat('dd.mm.yyyy');
  });
  if (data.notes) sh.getRange(1, 1, 1, width).setNotes([data.notes]);

  var top = sh.getRange(1, 1, 1, width);
  top.setFontWeight('bold').setWrap(true).setVerticalAlignment('top');
  sh.setRowHeight(1, 75);
  sh.setFrozenRows(1);
  sh.setFrozenColumns(1);
  sh.getRange(1, 1, height, width).createFilter();

  var widths = [200, 220, 60, 130];
  for (var c = 0; c < width; c++) {
    sh.setColumnWidth(c + 1, c < widths.length ? widths[c] : (c >= width - 2 ? 130 : 95));
  }
  if (data.markCols && height > 1 && data.markCols[1] > data.markCols[0]) {
    var marked = sh.getRange(2, data.markCols[0] + 1, height - 1, data.markCols[1] - data.markCols[0]);
    sh.setConditionalFormatRules([
      SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo('да').setBackground(DONE_BG).setRanges([marked]).build(),
      SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo('пройдена').setBackground(DONE_BG).setRanges([marked]).build()
    ]);
  }

  var old = ss.getSheetByName(data.title);
  if (old) ss.deleteSheet(old);
  sh.setName(data.title);
  ss.setActiveSheet(sh);
  ss.moveActiveSheet(1);
  return data.title;
}

// Новая вкладка — 1000×26: подогнать под выгрузку.
function fit_(sh, rows, cols) {
  var r = sh.getMaxRows(), c = sh.getMaxColumns();
  if (rows > r) sh.insertRowsAfter(r, rows - r);
  else if (rows < r) sh.deleteRows(rows + 1, r - rows);
  if (cols > c) sh.insertColumnsAfter(c, cols - c);
  else if (cols < c) sh.deleteColumns(cols + 1, c - cols);
}
