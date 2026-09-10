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

namespace theme_kabacademy;

use core\hook\output\before_footer_html_generation;

/**
 * Hook callbacks for the Kabacademy theme.
 *
 * @package    theme_kabacademy
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Adds a small script that makes URL fragments work with accordion content.
     *
     * Course pages built on the .kab-* design system use <details> accordions
     * (e.g. «Как здесь всё устроено»). Links from other activities point to a
     * section via "#id", but a fragment does not expand a closed <details>
     * and the fixed navbar hides the target. The script opens the targeted
     * <details> (and any <details> ancestors) and scrolls to it below the
     * navbar, on page load and on every hash change.
     *
     * @param before_footer_html_generation $hook
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        global $PAGE;
        // Plugin hooks fire site-wide; only act on pages rendered by this theme.
        // ($PAGE->theme is a magic property: no isset() on it.)
        $theme = $PAGE->theme;
        if (!$theme || $theme->name !== 'kabacademy') {
            return;
        }
        $js = <<<'JS'
(function () {
    function openHashTarget() {
        var hash = window.location.hash;
        if (!hash || hash.length < 2) {
            return;
        }
        var id;
        try {
            id = decodeURIComponent(hash.slice(1));
        } catch (e) {
            id = hash.slice(1);
        }
        var target = document.getElementById(id);
        if (!target) {
            return;
        }
        var details = target.closest('details');
        if (!details) {
            return;
        }
        while (details) {
            details.open = true;
            details = details.parentElement ? details.parentElement.closest('details') : null;
        }
        var navbar = document.querySelector('.navbar.fixed-top');
        var offset = (navbar ? navbar.offsetHeight : 0) + 12;
        // 'instant': the theme sets html {scroll-behavior: smooth} and a smooth
        // scroll started during page load gets cancelled by later layout work.
        var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({top: top, behavior: 'instant'});
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', openHashTarget);
    } else {
        openHashTarget();
    }
    // Re-align once everything (images) has loaded and shifted the layout.
    window.addEventListener('load', openHashTarget);
    window.addEventListener('hashchange', openHashTarget);
})();
JS;
        $hook->add_html('<script>' . $js . '</script>');
    }
}
