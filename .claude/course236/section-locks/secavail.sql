CREATE TABLE _kab_bk20261014_secavail AS SELECT id, section, name, availability FROM mdl_course_sections WHERE course=236 AND id BETWEEN 1732 AND 1744;
START TRANSACTION;
UPDATE mdl_course_sections SET availability='{"op":"&","c":[],"showc":[]}', timemodified=UNIX_TIMESTAMP() WHERE course=236 AND id BETWEEN 1732 AND 1744 AND name LIKE 'Урок %';
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;
COMMIT;
SELECT (SELECT COUNT(*) FROM _kab_bk20261014_secavail) backed_up;
SELECT id, section, name, availability FROM mdl_course_sections WHERE course=236 AND id BETWEEN 1732 AND 1746 ORDER BY section;
