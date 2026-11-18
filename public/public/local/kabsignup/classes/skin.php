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

namespace local_kabsignup;

defined('MOODLE_INTERNAL') || die();

use html_writer;

/**
 * The look of the signup pages: the campaign's colours and fonts as CSS
 * variables (rules in styles.css), the logo, the course card and the phone
 * field with a country flag.
 *
 * @package    local_kabsignup
 * @copyright  2026 Kabbalah Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skin {

    /**
     * Before $OUTPUT->header(): body class, the campaign's font stylesheet and
     * the phone library.
     *
     * @param \moodle_page $page
     * @param \stdClass|null $campaign
     * @param bool $phone the page shows the signup form
     */
    public static function setup(\moodle_page $page, ?\stdClass $campaign, bool $phone = false): void {
        $page->add_body_class('kabsignup');
        $theme = campaigns::theme($campaign);
        if ($theme) {
            $page->add_body_class('kabsignup-camp');
        }
        if (!empty($theme['fontcss'])) {
            $page->requires->css(new \moodle_url($theme['fontcss']));
        }
        if ($phone) {
            $page->requires->css('/local/kabsignup/thirdparty/intl-tel-input/css/intlTelInput.min.css');
            $page->requires->js(new \moodle_url('/local/kabsignup/thirdparty/intl-tel-input/js/intlTelInput.min.js'));
            $page->requires->js(new \moodle_url('/local/kabsignup/js/phone.js', ['v' => self::version()]));
        }
    }

    /**
     * Right after $OUTPUT->header(): the colour variables of the campaign.
     *
     * @param \stdClass|null $campaign
     * @return string
     */
    public static function style(?\stdClass $campaign): string {
        $theme = campaigns::theme($campaign);
        if (!$theme) {
            return '';
        }
        // Values are validated by campaigns::clean_theme(): colours and font-family lists only.
        // A rule per key that is set, so an unset key keeps the site's own look.
        $b = 'body.kabsignup-camp';
        $c = $b . ' .login-container';
        $rules = [];
        if (isset($theme['bg'])) {
            $rules[] = "$b,$b #page-wrapper,$b #page{background:{$theme['bg']} !important}";
        }
        if (isset($theme['card'])) {
            $rules[] = "$c{background:{$theme['card']};border-color:transparent}";
        }
        if (isset($theme['ink'])) {
            $rules[] = "$c,$c h2,$c label,$c .ks-card,$c .generalbox{color:{$theme['ink']}}";
        }
        if (isset($theme['muted'])) {
            $rules[] = "$b .ks-card-note,$b .ks-below,$b #page-footer,$b #page-footer a{color:{$theme['muted']};opacity:1}";
        }
        if (isset($theme['accent'])) {
            $rules[] = "$c a{color:{$theme['accent']}}";
            $rules[] = "$c .form-control:focus{border-color:{$theme['accent']};box-shadow:none}";
        }
        if (isset($theme['button'])) {
            $rules[] = "$c .btn-primary,$c .btn-secondary{background:{$theme['button']};border-color:{$theme['button']}}";
        }
        if (isset($theme['buttontext'])) {
            $rules[] = "$c .btn-primary,$c .btn-secondary{color:{$theme['buttontext']}}";
        }
        if (isset($theme['fontheading'])) {
            $rules[] = "$c .ks-title,$c h2,$c .ks-card-title{font-family:{$theme['fontheading']}}";
        }
        if (isset($theme['fontbody'])) {
            $rules[] = "$c{font-family:{$theme['fontbody']}}";
        }
        return $rules ? html_writer::tag('style', implode("\n", $rules)) : '';
    }

    /**
     * Logo, heading, intro and the course card above the form.
     *
     * @param \stdClass|null $campaign
     * @param string $defaultheading
     * @param string $defaultintro HTML
     * @return string
     */
    public static function intro(?\stdClass $campaign, string $defaultheading, string $defaultintro): string {
        $theme = campaigns::theme($campaign);
        $out = self::logo($theme);
        $title = ($campaign && $campaign->title !== '') ? format_string($campaign->title) : $defaultheading;
        $out .= html_writer::tag('h2', $title, ['class' => 'ks-title text-center mb-3']);
        if ($campaign && trim((string)$campaign->intro) !== '') {
            $out .= html_writer::div(format_text($campaign->intro, FORMAT_HTML,
                ['context' => \context_system::instance()]), 'ks-intro');
        } else if ($defaultintro !== '') {
            $out .= html_writer::div($defaultintro, 'ks-intro');
        }
        if ($campaign && ($campaign->cardtitle !== '' || $campaign->pricelabel !== '')) {
            $card = '';
            if (campaigns::is_https((string)$campaign->cardimage)
                    || ((string)$campaign->cardimage !== '' && $campaign->cardimage[0] === '/')) {
                $card .= html_writer::empty_tag('img', ['src' => $campaign->cardimage, 'alt' => '',
                    'class' => 'ks-card-img', 'loading' => 'lazy']);
            }
            $text = html_writer::div(format_string($campaign->cardtitle), 'ks-card-title');
            if ($campaign->cardnote !== '') {
                $text .= html_writer::div(format_string($campaign->cardnote), 'ks-card-note');
            }
            $card .= html_writer::div($text, 'ks-card-text');
            if ($campaign->pricelabel !== '') {
                $card .= html_writer::div(format_string($campaign->pricelabel), 'ks-card-price');
            }
            $out .= html_writer::div($card, 'ks-card');
        }
        return $out;
    }

    /**
     * Just the logo and a heading, for the result pages.
     *
     * @param \stdClass|null $campaign
     * @param string $heading
     * @return string
     */
    public static function heading(?\stdClass $campaign, string $heading): string {
        return self::logo(campaigns::theme($campaign))
            . html_writer::tag('h2', $heading, ['class' => 'ks-title text-center mb-3']);
    }

    /**
     * Logo of the campaign.
     *
     * @param array $theme
     * @return string
     */
    protected static function logo(array $theme): string {
        if (empty($theme['logo'])) {
            return '';
        }
        return html_writer::div(html_writer::empty_tag('img', ['src' => $theme['logo'], 'alt' => '']), 'ks-logo');
    }

    /**
     * Country for the phone field: Cloudflare's CF-IPCountry when present.
     *
     * @return string two lower-case letters or ''
     */
    public static function country(): string {
        $cc = strtolower(trim((string)($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '')));
        return (preg_match('/^[a-z]{2}$/', $cc) && $cc !== 'xx' && $cc !== 't1') ? $cc : '';
    }

    /**
     * Plugin version, for cache-busting the static scripts.
     *
     * @return string
     */
    protected static function version(): string {
        return (string)get_config('local_kabsignup', 'version');
    }
}
