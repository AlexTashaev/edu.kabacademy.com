-- Боевой курс 236: принудительная тема kabacademy (как на 238).
-- Откат: UPDATE mdl_course SET theme='', cacherev=cacherev+1 WHERE id=236;
UPDATE mdl_course SET theme='kabacademy', cacherev=cacherev+1 WHERE id=236;
SELECT id, theme, cacherev FROM mdl_course WHERE id=236;
