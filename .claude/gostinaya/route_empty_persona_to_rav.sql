-- 2026-09-10: студенты без метки kab_persona идут в «Гостиная · Михаэль Лайтман» (cmid 13364),
-- универсальная гостиная 13366 скрыта. Применено на проде прямым SQL.
-- Бэкап: _kab_bk20260910_gost_avail (id, visible, availability).
-- Параллельно в WP: товар 666513 _kab_lesson_url -> .../mod/lesson/view.php?id=13364
CREATE TABLE IF NOT EXISTS _kab_bk20260910_gost_avail AS
  SELECT id, visible, availability, NOW() bk_at FROM mdl_course_modules WHERE id IN (13364,13366);
UPDATE mdl_course_modules SET availability='{"op":"|","show":false,"c":[{"type":"profile","cf":"kab_persona","op":"isequalto","v":"rav"},{"type":"profile","cf":"kab_persona","op":"isempty"}]}' WHERE id=13364;
UPDATE mdl_course_modules SET visible=0, visibleold=0 WHERE id=13366;
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;
-- Откат:
-- UPDATE mdl_course_modules cm JOIN _kab_bk20260910_gost_avail b ON b.id=cm.id SET cm.availability=b.availability, cm.visible=b.visible, cm.visibleold=b.visible;
-- UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;
