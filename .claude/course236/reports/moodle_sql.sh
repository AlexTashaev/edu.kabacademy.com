#!/bin/sh
# Только чтение: SQL к БД Moodle на самом web-18 для kk1_activity.py (cron).
# Доступ к БД берёт из config.php Moodle на сервере, ничего секретного не печатает.
# Вывод — batch-формат mysql (TSV с заголовком). Запуск: moodle_sql.sh "SELECT ..."
CFG=/sites/edu.kabacademy.com/public/config.php
eval "$(/usr/bin/php82 -r '
$c = file_get_contents("'"$CFG"'");
foreach (array("dbhost", "dbname", "dbuser", "dbpass") as $k) {
    if (preg_match("/CFG->{$k}\s*=\s*[\x27\"]([^\x27\"]*)[\x27\"]/", $c, $m)) {
        printf("%s=%s\n", strtoupper($k), escapeshellarg($m[1]));
    }
}
')"
export MYSQL_PWD="$DBPASS"
exec mysql -h "$DBHOST" -u "$DBUSER" "$DBNAME" --default-character-set=utf8mb4 -e "$1"
