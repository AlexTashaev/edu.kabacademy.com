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
 * Campaigns: list, link for the landing, preview, delete.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_kabsignup\campaigns;

admin_externalpage_setup('local_kabsignup_campaigns');
$context = context_system::instance();
require_capability('local/kabsignup:manage', $context);
$p = 'local_kabsignup';
$pageurl = new moodle_url('/local/kabsignup/campaigns.php');

$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && ($campaign = campaigns::get($delete))) {
    if (optional_param('confirm', 0, PARAM_BOOL) && confirm_sesskey()) {
        campaigns::delete($delete);
        redirect($pageurl, get_string('campaign_deleted', $p), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('campaign_deleteconfirm', $p, s($campaign->slug)),
        new moodle_url($pageurl, ['delete' => $delete, 'confirm' => 1, 'sesskey' => sesskey()]), $pageurl);
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('campaigns', $p));
echo html_writer::tag('p', get_string('campaigns_intro', $p));
echo html_writer::div($OUTPUT->single_button(new moodle_url('/local/kabsignup/campaign.php'),
    get_string('campaign_add', $p), 'get', ['type' => 'primary']), 'mb-3');

$rows = campaigns::all();
if (!$rows) {
    echo $OUTPUT->notification(get_string('campaigns_none', $p), \core\output\notification::NOTIFY_INFO, false);
} else {
    $courseids = array_filter(array_map(function($c) {
        return (int)$c->courseid;
    }, $rows));
    $coursenames = $courseids ? $DB->get_records_list('course', 'id', $courseids, '', 'id, fullname') : [];
    $table = new html_table();
    $table->head = [get_string('campaign_slug', $p), get_string('campaign_name', $p), get_string('campaign_course', $p),
        get_string('campaign_link', $p), get_string('campaign_thankyouurl', $p), ''];
    $table->attributes['class'] = 'generaltable';
    foreach ($rows as $c) {
        $link = campaigns::signup_url($c);
        $course = isset($coursenames[$c->courseid]) ? format_string($coursenames[$c->courseid]->fullname) : '—';
        $actions = html_writer::link(new moodle_url('/local/kabsignup/campaign.php', ['id' => $c->id]),
                get_string('edit'))
            . ' · ' . html_writer::link(new moodle_url($link, ['preview' => 1]), get_string('campaign_preview', $p),
                ['target' => '_blank'])
            . ' · ' . html_writer::link(new moodle_url($pageurl, ['delete' => $c->id]), get_string('delete'));
        $slug = s($c->slug) . (empty($c->visible) ? ' ' . html_writer::span(get_string('campaign_hidden', $p),
            'badge bg-secondary') : '');
        $table->data[] = [
            $slug,
            s($c->name),
            $course,
            html_writer::tag('code', s($link->out(false))),
            s($c->thankyouurl),
            $actions,
        ];
    }
    echo html_writer::table($table);
}
echo html_writer::tag('p', get_string('campaigns_paramsnote', $p), ['class' => 'text-muted']);
echo $OUTPUT->footer();
