-- Five extra questions (slots 7–11) for quiz 1245 «Проверьте себя: Тест по уроку 11»: copies of 68837 (slot 6), bank entry 65130.
START TRANSACTION;
INSERT INTO mdl_question_bank_entries (questioncategoryid, idnumber, ownerid, nextversion) SELECT questioncategoryid, NULL, ownerid, NULL FROM mdl_question_bank_entries WHERE id=65130;
SET @qbe7 = LAST_INSERT_ID();
INSERT INTO mdl_question (parent,name,questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,stamp,timecreated,timemodified,createdby,modifiedby)
  SELECT parent,'У11-07 (новый слот)',questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,CONCAT('edu.kabacademy.com+l11q7+',UNIX_TIMESTAMP()),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),createdby,modifiedby FROM mdl_question WHERE id=68837;
SET @q7 = LAST_INSERT_ID();
INSERT INTO mdl_question_versions (questionbankentryid, version, questionid, status) VALUES (@qbe7, 1, @q7, 'ready');
INSERT INTO mdl_qtype_multichoice_options (questionid,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction)
  SELECT @q7,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction FROM mdl_qtype_multichoice_options WHERE questionid=68837;
INSERT INTO mdl_question_answers (question, answer, answerformat, fraction, feedback, feedbackformat)
  SELECT @q7, answer, answerformat, fraction, feedback, feedbackformat FROM mdl_question_answers WHERE question=68837 ORDER BY id;
INSERT INTO mdl_question_bank_entries (questioncategoryid, idnumber, ownerid, nextversion) SELECT questioncategoryid, NULL, ownerid, NULL FROM mdl_question_bank_entries WHERE id=65130;
SET @qbe8 = LAST_INSERT_ID();
INSERT INTO mdl_question (parent,name,questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,stamp,timecreated,timemodified,createdby,modifiedby)
  SELECT parent,'У11-08 (новый слот)',questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,CONCAT('edu.kabacademy.com+l11q8+',UNIX_TIMESTAMP()),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),createdby,modifiedby FROM mdl_question WHERE id=68837;
SET @q8 = LAST_INSERT_ID();
INSERT INTO mdl_question_versions (questionbankentryid, version, questionid, status) VALUES (@qbe8, 1, @q8, 'ready');
INSERT INTO mdl_qtype_multichoice_options (questionid,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction)
  SELECT @q8,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction FROM mdl_qtype_multichoice_options WHERE questionid=68837;
INSERT INTO mdl_question_answers (question, answer, answerformat, fraction, feedback, feedbackformat)
  SELECT @q8, answer, answerformat, fraction, feedback, feedbackformat FROM mdl_question_answers WHERE question=68837 ORDER BY id;
INSERT INTO mdl_question_bank_entries (questioncategoryid, idnumber, ownerid, nextversion) SELECT questioncategoryid, NULL, ownerid, NULL FROM mdl_question_bank_entries WHERE id=65130;
SET @qbe9 = LAST_INSERT_ID();
INSERT INTO mdl_question (parent,name,questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,stamp,timecreated,timemodified,createdby,modifiedby)
  SELECT parent,'У11-09 (новый слот)',questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,CONCAT('edu.kabacademy.com+l11q9+',UNIX_TIMESTAMP()),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),createdby,modifiedby FROM mdl_question WHERE id=68837;
SET @q9 = LAST_INSERT_ID();
INSERT INTO mdl_question_versions (questionbankentryid, version, questionid, status) VALUES (@qbe9, 1, @q9, 'ready');
INSERT INTO mdl_qtype_multichoice_options (questionid,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction)
  SELECT @q9,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction FROM mdl_qtype_multichoice_options WHERE questionid=68837;
INSERT INTO mdl_question_answers (question, answer, answerformat, fraction, feedback, feedbackformat)
  SELECT @q9, answer, answerformat, fraction, feedback, feedbackformat FROM mdl_question_answers WHERE question=68837 ORDER BY id;
INSERT INTO mdl_question_bank_entries (questioncategoryid, idnumber, ownerid, nextversion) SELECT questioncategoryid, NULL, ownerid, NULL FROM mdl_question_bank_entries WHERE id=65130;
SET @qbe10 = LAST_INSERT_ID();
INSERT INTO mdl_question (parent,name,questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,stamp,timecreated,timemodified,createdby,modifiedby)
  SELECT parent,'У11-10 (новый слот)',questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,CONCAT('edu.kabacademy.com+l11q10+',UNIX_TIMESTAMP()),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),createdby,modifiedby FROM mdl_question WHERE id=68837;
SET @q10 = LAST_INSERT_ID();
INSERT INTO mdl_question_versions (questionbankentryid, version, questionid, status) VALUES (@qbe10, 1, @q10, 'ready');
INSERT INTO mdl_qtype_multichoice_options (questionid,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction)
  SELECT @q10,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction FROM mdl_qtype_multichoice_options WHERE questionid=68837;
INSERT INTO mdl_question_answers (question, answer, answerformat, fraction, feedback, feedbackformat)
  SELECT @q10, answer, answerformat, fraction, feedback, feedbackformat FROM mdl_question_answers WHERE question=68837 ORDER BY id;
INSERT INTO mdl_question_bank_entries (questioncategoryid, idnumber, ownerid, nextversion) SELECT questioncategoryid, NULL, ownerid, NULL FROM mdl_question_bank_entries WHERE id=65130;
SET @qbe11 = LAST_INSERT_ID();
INSERT INTO mdl_question (parent,name,questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,stamp,timecreated,timemodified,createdby,modifiedby)
  SELECT parent,'У11-11 (новый слот)',questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,CONCAT('edu.kabacademy.com+l11q11+',UNIX_TIMESTAMP()),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),createdby,modifiedby FROM mdl_question WHERE id=68837;
SET @q11 = LAST_INSERT_ID();
INSERT INTO mdl_question_versions (questionbankentryid, version, questionid, status) VALUES (@qbe11, 1, @q11, 'ready');
INSERT INTO mdl_qtype_multichoice_options (questionid,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction)
  SELECT @q11,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction FROM mdl_qtype_multichoice_options WHERE questionid=68837;
INSERT INTO mdl_question_answers (question, answer, answerformat, fraction, feedback, feedbackformat)
  SELECT @q11, answer, answerformat, fraction, feedback, feedbackformat FROM mdl_question_answers WHERE question=68837 ORDER BY id;
COMMIT;
SELECT @qbe7 qbe7, @q7 q7, @qbe8 qbe8, @q8 q8, @qbe9 qbe9, @q9 q9, @qbe10 qbe10, @q10 q10, @qbe11 qbe11, @q11 q11;
SELECT question, GROUP_CONCAT(id ORDER BY id) ids FROM mdl_question_answers WHERE question IN (@q7, @q8, @q9, @q10, @q11) GROUP BY question;
