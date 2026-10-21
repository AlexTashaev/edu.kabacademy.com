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
 * Ask for a login link: one field, the e-mail address.
 *
 * Whatever happens (unknown address, ineligible account, rate limit) the page
 * answers the same way, so it gives away nothing about who is registered.
 *
 * @package    auth_kabmagic
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$wantsurl = optional_param('wantsurl', '', PARAM_LOCALURL);
if ($wantsurl === '' && !empty($SESSION->wantsurl)) {
    $wantsurl = clean_param($SESSION->wantsurl, PARAM_LOCALURL);
}

$PAGE->set_url(new moodle_url('/auth/kabmagic/request.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('requesttitle', 'auth_kabmagic'));
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_cacheable(false);

if (!is_enabled_auth('kabmagic')) {
    throw new moodle_exception('notenabled', 'auth_kabmagic');
}
if (isloggedin() && !isguestuser()) {
    redirect(new moodle_url($wantsurl !== '' ? $wantsurl : '/my/'));
}

$loginurl = new moodle_url('/login/index.php');
$form = new \auth_kabmagic\form\request_form(null, ['wantsurl' => $wantsurl]);
if ($form->is_cancelled()) {
    redirect($loginurl);
}

if ($data = $form->get_data()) {
    \auth_kabmagic\manager::request($data->email, (string)($data->wantsurl ?? ''), getremoteaddr());
    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('sent', 'auth_kabmagic'), 'generalbox boxwidthnormal boxaligncenter');
    echo html_writer::div(
        $OUTPUT->single_button($loginurl, get_string('backtologin', 'auth_kabmagic'), 'get'),
        'text-center'
    );
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('requesttitle', 'auth_kabmagic'), 2, 'text-center mb-3');
echo $OUTPUT->box(get_string('requestintro', 'auth_kabmagic'), 'generalbox boxwidthnormal boxaligncenter');
$form->display();
echo $OUTPUT->footer();
