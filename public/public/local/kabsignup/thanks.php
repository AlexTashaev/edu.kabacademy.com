<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Built-in thank-you page: used when neither the campaign nor the course has
 * its own page configured.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$courseid = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/local/kabsignup/thanks.php', ['id' => $courseid]));
$PAGE->set_pagelayout('login');
$PAGE->set_cacheable(false);

$config = \local_kabsignup\manager::config();
$course = isset($config->courses[$courseid]) ? $DB->get_record('course', ['id' => $courseid]) : null;

if ($course) {
    $PAGE->set_course($course);
    $coursename = format_string($course->fullname, true, ['context' => context_course::instance($course->id)]);
    $PAGE->set_heading($coursename);
} else {
    $PAGE->set_context(context_system::instance());
    $PAGE->set_heading(format_string($SITE->fullname));
}
$PAGE->set_title(get_string('thanksheading', 'local_kabsignup'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('thanksheading', 'local_kabsignup'), 2, 'text-center mb-3');
if ($course) {
    $stringid = (isloggedin() && !isguestuser()) ? 'thankstextloggedin' : 'thankstext';
    echo $OUTPUT->box(get_string($stringid, 'local_kabsignup', $coursename), 'generalbox boxwidthnormal boxaligncenter');
    echo html_writer::div(
        $OUTPUT->single_button(new moodle_url('/course/view.php', ['id' => $course->id]),
            get_string('gotocourse', 'local_kabsignup'), 'get'),
        'text-center'
    );
} else {
    echo $OUTPUT->box(get_string('thankstextgeneric', 'local_kabsignup'), 'generalbox boxwidthnormal boxaligncenter');
}
echo $OUTPUT->footer();
