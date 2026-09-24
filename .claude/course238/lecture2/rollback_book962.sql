-- Откат схем урока 2: content глав из _kab_bk20260924_book962.
UPDATE mdl_book_chapters c JOIN _kab_bk20260924_book962 b ON b.id=c.id SET c.content=b.content, c.timemodified=b.timemodified WHERE c.bookid=962;
SELECT id, pagenum, title, LENGTH(content) len FROM mdl_book_chapters WHERE bookid=962 ORDER BY pagenum;
