#!/bin/sh
# SQL *file* runner for Moodle DB — same cred handling as /tmp/kab_moodle_sql2.sh,
# but feeds mysql from a file (for large multi-statement updates).
# Usage: kab_moodle_sqlfile.sh /path/to/file.sql
eval "$(/usr/bin/php82 -r '
$c = file_get_contents("/sites/edu.kabacademy.com/public/config.php");
foreach (array("dbhost","dbname","dbuser","dbpass") as $k) {
    if (preg_match("/CFG->{$k}\s*=\s*[\x27\"]([^\x27\"]*)[\x27\"]/", $c, $m)) {
        printf("%s=%s\n", strtoupper($k), escapeshellarg($m[1]));
    }
}
')"
export MYSQL_PWD="$DBPASS"
exec mysql -h "$DBHOST" -u "$DBUSER" "$DBNAME" --default-character-set=utf8mb4 < "$1"
