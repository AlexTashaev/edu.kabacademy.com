-- Two extra questions (slots 7 and 8) for quiz 1242 «Проверьте себя: Тест по уроку 8»: copies of 68813 (slot 6) in category 7048.
START TRANSACTION;
INSERT INTO mdl_question_bank_entries (questioncategoryid, idnumber, ownerid, nextversion) SELECT questioncategoryid, NULL, ownerid, NULL FROM mdl_question_bank_entries WHERE id=65112;
SET @qbe7 = LAST_INSERT_ID();
INSERT INTO mdl_question (parent,name,questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,stamp,timecreated,timemodified,createdby,modifiedby)
  SELECT parent,'У8-07 (новый слот)',questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,CONCAT('edu.kabacademy.com+l8q7+',UNIX_TIMESTAMP()),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),createdby,modifiedby FROM mdl_question WHERE id=68813;
SET @q7 = LAST_INSERT_ID();
INSERT INTO mdl_question_versions (questionbankentryid, version, questionid, status) VALUES (@qbe7, 1, @q7, 'ready');
INSERT INTO mdl_qtype_multichoice_options (questionid,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction)
  SELECT @q7,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction FROM mdl_qtype_multichoice_options WHERE questionid=68813;
INSERT INTO mdl_question_answers (question, answer, answerformat, fraction, feedback, feedbackformat)
  SELECT @q7, answer, answerformat, fraction, feedback, feedbackformat FROM mdl_question_answers WHERE question=68813 ORDER BY id;
INSERT INTO mdl_question_bank_entries (questioncategoryid, idnumber, ownerid, nextversion) SELECT questioncategoryid, NULL, ownerid, NULL FROM mdl_question_bank_entries WHERE id=65112;
SET @qbe8 = LAST_INSERT_ID();
INSERT INTO mdl_question (parent,name,questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,stamp,timecreated,timemodified,createdby,modifiedby)
  SELECT parent,'У8-08 (новый слот)',questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,CONCAT('edu.kabacademy.com+l8q8+',UNIX_TIMESTAMP()),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),createdby,modifiedby FROM mdl_question WHERE id=68813;
SET @q8 = LAST_INSERT_ID();
INSERT INTO mdl_question_versions (questionbankentryid, version, questionid, status) VALUES (@qbe8, 1, @q8, 'ready');
INSERT INTO mdl_qtype_multichoice_options (questionid,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction)
  SELECT @q8,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction FROM mdl_qtype_multichoice_options WHERE questionid=68813;
INSERT INTO mdl_question_answers (question, answer, answerformat, fraction, feedback, feedbackformat)
  SELECT @q8, answer, answerformat, fraction, feedback, feedbackformat FROM mdl_question_answers WHERE question=68813 ORDER BY id;
COMMIT;
SELECT @qbe7 qbe7, @q7 q7, @qbe8 qbe8, @q8 q8;
SELECT question, GROUP_CONCAT(id ORDER BY id) ids FROM mdl_question_answers WHERE question IN (@q7, @q8) GROUP BY question;
SELECT COUNT(*) attempts FROM mdl_quiz_attempts WHERE quiz=1242;
