CREATE TABLE _kab_bk20261014_nofilms AS SELECT * FROM mdl_book_chapters WHERE id IN (2272, 2285, 2298);
START TRANSACTION;
DELETE FROM mdl_book_chapters WHERE (id=2272 AND bookid=968 AND pagenum=4) OR (id=2285 AND bookid=970 AND pagenum=3) OR (id=2298 AND bookid=972 AND pagenum=2);
UPDATE mdl_book SET revision=revision+1, timemodified=UNIX_TIMESTAMP() WHERE id IN (968, 970, 972);
UPDATE mdl_course SET cacherev=cacherev+1 WHERE id=236;
COMMIT;
SELECT (SELECT COUNT(*) FROM _kab_bk20261014_nofilms) backed_up;
SELECT c.bookid, c.pagenum, c.title FROM mdl_book_chapters c JOIN mdl_book b ON b.id=c.bookid WHERE b.course=236 AND b.id IN (968, 970, 971, 972) ORDER BY c.bookid, c.pagenum;
