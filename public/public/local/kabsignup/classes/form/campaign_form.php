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

namespace local_kabsignup\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use local_kabsignup\campaigns;
use local_kabsignup\manager;

/**
 * Edit a campaign: slug and course, thank-you addresses, texts of the signup
 * page, colours and fonts.
 *
 * Custom data: courses (id => name of the courses open for signup).
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaign_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;
        $p = 'local_kabsignup';

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'hgeneral', get_string('campaign_general', $p));
        $mform->addElement('text', 'slug', get_string('campaign_slug', $p), ['size' => 30, 'maxlength' => 64]);
        $mform->setType('slug', PARAM_RAW_TRIMMED);
        $mform->addRule('slug', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('slug', 'campaign_slug', $p);
        $mform->addElement('text', 'name', get_string('campaign_name', $p), ['size' => 50, 'maxlength' => 255]);
        $mform->setType('name', PARAM_TEXT);
        $courses = [0 => get_string('campaign_nocourse', $p)] + $this->_customdata['courses'];
        $mform->addElement('select', 'courseid', get_string('campaign_course', $p), $courses);
        $mform->addHelpButton('courseid', 'campaign_course', $p);
        $mform->addElement('advcheckbox', 'visible', get_string('campaign_visible', $p));
        $mform->setDefault('visible', 1);
        $mform->addElement('advcheckbox', 'capi', get_string('campaign_capi', $p));
        $mform->setDefault('capi', 1);
        $mform->addHelpButton('capi', 'campaign_capi', $p);

        $mform->addElement('header', 'hthanks', get_string('campaign_thanks', $p));
        $mform->addElement('text', 'thankyouurl', get_string('campaign_thankyouurl', $p), ['size' => 70]);
        $mform->setType('thankyouurl', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('thankyouurl', 'campaign_thankyouurl', $p);
        $mform->addElement('text', 'thankyoufallback', get_string('campaign_thankyoufallback', $p), ['size' => 70]);
        $mform->setType('thankyoufallback', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('thankyoufallback', 'campaign_thankyoufallback', $p);

        $mform->addElement('header', 'hpage', get_string('campaign_page', $p));
        $mform->addElement('text', 'title', get_string('campaign_title', $p), ['size' => 60, 'maxlength' => 255]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addElement('textarea', 'intro', get_string('campaign_intro', $p), ['rows' => 3, 'cols' => 60]);
        $mform->setType('intro', PARAM_RAW);
        $mform->addElement('text', 'cardtitle', get_string('campaign_cardtitle', $p), ['size' => 60, 'maxlength' => 255]);
        $mform->setType('cardtitle', PARAM_TEXT);
        $mform->addElement('text', 'cardnote', get_string('campaign_cardnote', $p), ['size' => 60, 'maxlength' => 255]);
        $mform->setType('cardnote', PARAM_TEXT);
        $mform->addElement('text', 'cardimage', get_string('campaign_cardimage', $p), ['size' => 70]);
        $mform->setType('cardimage', PARAM_RAW_TRIMMED);
        $mform->addElement('text', 'pricelabel', get_string('campaign_pricelabel', $p), ['size' => 20, 'maxlength' => 64]);
        $mform->setType('pricelabel', PARAM_TEXT);
        $mform->addElement('text', 'buttonlabel', get_string('campaign_buttonlabel', $p), ['size' => 40, 'maxlength' => 100]);
        $mform->setType('buttonlabel', PARAM_TEXT);
        $mform->addElement('text', 'belowbutton', get_string('campaign_belowbutton', $p), ['size' => 70, 'maxlength' => 255]);
        $mform->setType('belowbutton', PARAM_TEXT);

        $mform->addElement('header', 'htheme', get_string('campaign_theme', $p));
        $mform->addElement('static', 'themehelp', '', get_string('campaign_theme_desc', $p));
        foreach (campaigns::COLOURS as $key) {
            $mform->addElement('text', 'theme_' . $key, get_string('campaign_colour_' . $key, $p),
                ['size' => 10, 'maxlength' => 7, 'placeholder' => '#rrggbb']);
            $mform->setType('theme_' . $key, PARAM_RAW_TRIMMED);
        }
        foreach (campaigns::THEMETEXT as $key) {
            $mform->addElement('text', 'theme_' . $key, get_string('campaign_' . $key, $p), ['size' => 60]);
            $mform->setType('theme_' . $key, PARAM_RAW_TRIMMED);
        }
        $mform->addHelpButton('theme_fontcss', 'campaign_fontcss', $p);

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Unique slug, valid addresses, colours and fonts.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $p = 'local_kabsignup';

        $slug = \core_text::strtolower(trim($data['slug'] ?? ''));
        if ($slug === '' || clean_param($slug, PARAM_ALPHANUMEXT) !== $slug) {
            $errors['slug'] = get_string('campaign_slug_invalid', $p);
        } else {
            $other = $DB->get_field('local_kabsignup_campaign', 'id', ['slug' => $slug]);
            if ($other && (int)$other !== (int)($data['id'] ?? 0)) {
                $errors['slug'] = get_string('campaign_slug_taken', $p);
            }
        }
        foreach (['thankyouurl', 'thankyoufallback'] as $key) {
            $value = trim($data[$key] ?? '');
            if ($value !== '' && manager::clean_target(str_replace('{pres}', 'x', $value)) === '') {
                $errors[$key] = get_string('campaign_badurl', $p);
            }
        }
        if (strpos(trim($data['thankyoufallback'] ?? ''), '{pres}') !== false) {
            $errors['thankyoufallback'] = get_string('campaign_nopres', $p);
        }
        foreach (['cardimage', 'theme_logo', 'theme_fontcss'] as $key) {
            $value = trim($data[$key] ?? '');
            if ($value !== '' && !campaigns::is_https($value) && ($value[0] !== '/' || strpos($value, '//') === 0)) {
                $errors[$key] = get_string('campaign_badurl', $p);
            }
        }
        foreach (campaigns::COLOURS as $key) {
            $value = trim($data['theme_' . $key] ?? '');
            if ($value !== '' && !campaigns::is_colour($value)) {
                $errors['theme_' . $key] = get_string('campaign_badcolour', $p);
            }
        }
        foreach (['fontheading', 'fontbody'] as $key) {
            $value = trim($data['theme_' . $key] ?? '');
            if ($value !== '' && !campaigns::is_font($value)) {
                $errors['theme_' . $key] = get_string('campaign_badfont', $p);
            }
        }
        return $errors;
    }
}
