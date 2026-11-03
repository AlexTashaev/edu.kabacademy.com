<?php
// ⚠️ SCAFFOLD. Read-only WS-функция: роли пользователя по курсам (для Person Card чата поддержки).
// Один SQL-запрос вместо N вызовов core_user_get_course_user_profiles (та схлопывает по userid).
namespace local_kabchat\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

class get_user_course_roles extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'Moodle user id'),
        ]);
    }

    /**
     * @return array список курсов пользователя с его ролями: [{courseid, fullname, roles:[shortname]}]
     */
    public static function execute(int $userid): array {
        global $DB;
        $params = self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);
        $userid = $params['userid'];

        // RO-доступ: служебный токен (Edwiser superadmin) имеет moodle/role:review на уровне системы.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/role:review', $context);

        $sql = "SELECT ra.id AS raid, c.id AS courseid, c.fullname, r.shortname
                  FROM {role_assignments} ra
                  JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = :clevel
                  JOIN {course} c ON c.id = ctx.instanceid
                  JOIN {role} r ON r.id = ra.roleid
                 WHERE ra.userid = :userid
              ORDER BY c.id";
        $rows = $DB->get_records_sql($sql, ['clevel' => CONTEXT_COURSE, 'userid' => $userid]);

        $bycourse = [];
        foreach ($rows as $row) {
            $cid = (int) $row->courseid;
            if (!isset($bycourse[$cid])) {
                $bycourse[$cid] = ['courseid' => $cid, 'fullname' => $row->fullname, 'roles' => []];
            }
            $bycourse[$cid]['roles'][] = $row->shortname;
        }
        return array_values($bycourse);
    }

    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'courseid' => new external_value(PARAM_INT, 'Course id'),
                'fullname' => new external_value(PARAM_RAW, 'Course full name'),
                'roles'    => new external_multiple_structure(
                    new external_value(PARAM_ALPHANUMEXT, 'Role shortname'),
                    'Роли пользователя в этом курсе (shortname)'
                ),
            ])
        );
    }
}
