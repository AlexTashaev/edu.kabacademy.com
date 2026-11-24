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
 * Add or edit a campaign.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_kabsignup\campaigns;
use local_kabsignup\manager;

$id = optional_param('id', 0, PARAM_INT);
admin_externalpage_setup('local_kabsignup_campaigns', '', null, new moodle_url('/local/kabsignup/campaign.php', ['id' => $id]));
require_capability('local/kabsignup:manage', context_system::instance());
$p = 'local_kabsignup';
$listurl = new moodle_url('/local/kabsignup/campaigns.php');

$campaign = $id ? campaigns::get($id) : null;
if ($id && !$campaign) {
    throw new moodle_exception('invalidrecord', 'error', $listurl);
}

// Courses open for signup, plus the campaign's own one even if it was taken off the list.
$courseids = array_keys(manager::config()->courses);
if ($campaign && (int)$campaign->courseid > 0) {
    $courseids[] = (int)$campaign->courseid;
}
$courses = [];
foreach ($courseids ? $DB->get_records_list('course', 'id', array_unique($courseids), 'id', 'id, fullname') : [] as $c) {
    $courses[(int)$c->id] = $c->id . ' — ' . format_string($c->fullname);
}

$form = new \local_kabsignup\form\campaign_form(null, ['courses' => $courses]);
if ($campaign) {
    $form->set_data(campaigns::to_form($campaign));
}

if ($form->is_cancelled()) {
    redirect($listurl);
}
if ($data = $form->get_data()) {
    campaigns::save($data);
    redirect($listurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($campaign ? get_string('campaign_edit', $p, s($campaign->slug)) : get_string('campaign_add', $p));
$form->display();
echo $OUTPUT->footer();
