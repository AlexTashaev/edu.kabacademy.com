START TRANSACTION;
INSERT INTO mdl_question_bank_entries (questioncategoryid, idnumber, ownerid, nextversion) SELECT questioncategoryid, NULL, ownerid, NULL FROM mdl_question_bank_entries WHERE id=65106;
SET @qbe = LAST_INSERT_ID();
INSERT INTO mdl_question (parent,name,questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,stamp,timecreated,timemodified,createdby,modifiedby)
  SELECT parent,'У7-07 (новый слот)',questiontext,questiontextformat,generalfeedback,generalfeedbackformat,defaultmark,penalty,qtype,length,CONCAT('edu.kabacademy.com+l7q7+',UNIX_TIMESTAMP()),UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),createdby,modifiedby FROM mdl_question WHERE id=68805;
SET @q = LAST_INSERT_ID();
INSERT INTO mdl_question_versions (questionbankentryid, version, questionid, status) VALUES (@qbe, 1, @q, 'ready');
INSERT INTO mdl_qtype_multichoice_options (questionid,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction)
  SELECT @q,layout,single,shuffleanswers,correctfeedback,correctfeedbackformat,partiallycorrectfeedback,partiallycorrectfeedbackformat,incorrectfeedback,incorrectfeedbackformat,answernumbering,shownumcorrect,showstandardinstruction FROM mdl_qtype_multichoice_options WHERE questionid=68805;
INSERT INTO mdl_question_answers (question, answer, answerformat, fraction, feedback, feedbackformat)
  SELECT @q, answer, answerformat, fraction, feedback, feedbackformat FROM mdl_question_answers WHERE question=68805 ORDER BY id;
COMMIT;
SELECT @qbe qbe, @q q;
SELECT question, GROUP_CONCAT(id ORDER BY id) ids FROM mdl_question_answers WHERE question=@q GROUP BY question;
