-- Откат схем дизайнера (урок 2, курс 238, книга 962, cmid 13389): содержимое из _kab_bk20260930d_b962.
UPDATE mdl_book_chapters x JOIN _kab_bk20260930d_b962 b ON b.id=x.id SET x.content=b.content, x.timemodified=b.timemodified WHERE x.id IN (2258,2257);
SELECT id, LENGTH(content) len FROM mdl_book_chapters WHERE id IN (2258,2257) ORDER BY id;
