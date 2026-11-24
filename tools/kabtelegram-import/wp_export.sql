-- WordPress users with a linked Telegram (plugin wptelegram-login), for build.py.
-- Columns: wpid, email, moodle_user_id (Edwiser link), tgid, tgusername, tgfirstname.
SELECT u.ID AS wpid, u.user_email AS email,
       MAX(CASE WHEN m.meta_key = 'moodle_user_id' THEN m.meta_value END) AS mid,
       MAX(CASE WHEN m.meta_key = 'wptelegram_user_id' THEN m.meta_value END) AS tgid,
       MAX(CASE WHEN m.meta_key = 'wptelegram_username' THEN m.meta_value END) AS tguser,
       MAX(CASE WHEN m.meta_key = 'wptelegram_first_name' THEN m.meta_value END) AS tgfirst
  FROM wp_users u
  JOIN wp_usermeta m ON m.user_id = u.ID
                    AND m.meta_key IN ('moodle_user_id', 'wptelegram_user_id', 'wptelegram_username', 'wptelegram_first_name')
 WHERE u.ID IN (SELECT user_id FROM wp_usermeta WHERE meta_key = 'wptelegram_user_id' AND meta_value <> '')
 GROUP BY u.ID, u.user_email
 ORDER BY u.ID;
