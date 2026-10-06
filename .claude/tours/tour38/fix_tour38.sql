-- Тур 38: бэкап и снятие флагов shipped_* (см. README.md).
-- Фильтр темы и тексты шагов правятся через формы Moodle — так сбрасывается кэш tool_usertours.

CREATE TABLE IF NOT EXISTS _kab_bk20260929_tour38_tours AS
  SELECT * FROM mdl_tool_usertours_tours WHERE id = 38;
CREATE TABLE IF NOT EXISTS _kab_bk20260929_tour38_steps AS
  SELECT * FROM mdl_tool_usertours_steps WHERE tourid = 38;

UPDATE mdl_tool_usertours_tours
   SET configdata = JSON_REMOVE(configdata, '$.shipped_tour', '$.shipped_filename', '$.shipped_version')
 WHERE id = 38
   AND JSON_VALID(configdata)
   AND JSON_EXTRACT(configdata, '$.shipped_filename') IS NOT NULL;

SELECT id, enabled, configdata FROM mdl_tool_usertours_tours WHERE id = 38;
SELECT (SELECT COUNT(*) FROM _kab_bk20260929_tour38_tours) AS bk_tours,
       (SELECT COUNT(*) FROM _kab_bk20260929_tour38_steps) AS bk_steps;
