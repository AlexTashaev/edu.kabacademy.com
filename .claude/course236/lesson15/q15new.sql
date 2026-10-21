-- One extra question (slot 7) for quiz 1249 «Проверьте себя: Тест по уроку 15»: copy of 68869 (slot 6), bank entry 65154.
START TRANSACTION;
INSERT INTO mdl_question_bank_entries (questioncategoryid, idnumber, ownerid, nextversion) SELECT questioncategoryid, NULL, ownerid, NULL FROM mdl_question_bank_entries WHERE id=65154;
SET @qbe7 = LAST_INSERT_ID();
INSERT INTO mdl_question (parent,name,questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,stamp,timecreated,timemodified,createdby,modifiedby)
  SELECT parent,'У15-07 (новый слот)',questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,CONCAT('edu.kabacademy.com+l15q7+',UNIX_TIMESTAMP()),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),createdby,modifiedby FROM mdl_question WHERE id=68869;
SET @q7 = LAST_INSERT_ID();
INSERT INTO mdl_question_versions (questionbankentryid, version, questionid, status) VALUES (@qbe7, 1, @q7, 'ready');
INSERT INTO mdl_qtype_multichoice_options (questionid,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction)
  SELECT @q7,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction FROM mdl_qtype_multichoice_options WHERE questionid=68869;
INSERT INTO mdl_question_answers (question, answer, answerformat, fraction, feedback, feedbackformat)
  SELECT @q7, answer, answerformat, fraction, feedback, feedbackformat FROM mdl_question_answers WHERE question=68869 ORDER BY id;
COMMIT;
SELECT @qbe7 qbe7, @q7 q7;
SELECT question, GROUP_CONCAT(id ORDER BY id) ids FROM mdl_question_answers WHERE question=@q7 GROUP BY question;
