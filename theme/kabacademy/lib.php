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
 * Library functions for Kabacademy theme.
 *
 * The theme is applied per course (course "forcetheme"), while the site keeps
 * plain Boost whose branding lives in Boost's own settings (Raw SCSS etc. in
 * the DB). A child theme does NOT see the parent's settings, so every SCSS
 * callback below feeds Boost's callbacks with Boost's theme_config — the child
 * always mirrors the live Boost branding — and appends its own additions from
 * scss/pre.scss and scss/kab.scss.
 *
 * Note: changing Boost settings bumps only Boost's theme revision; run "Purge
 * caches" so courses on this theme pick the change up too.
 *
 * @package    theme_kabacademy
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Boost's theme_config, for mirroring its settings.
 *
 * @return theme_config
 */
function theme_kabacademy_boost_config() {
    static $boost = null;
    if ($boost === null) {
        $boost = theme_config::load('boost');
    }
    return $boost;
}

/**
 * Returns the main SCSS content (Boost preset, as configured in Boost).
 *
 * @param theme_config $theme The theme config object.
 * @return string SCSS content.
 */
function theme_kabacademy_get_main_scss_content($theme) {
    return theme_boost_get_main_scss_content(theme_kabacademy_boost_config());
}

/**
 * Get compiled css.
 *
 * @return string compiled css.
 */
function theme_kabacademy_get_precompiled_css() {
    return theme_boost_get_precompiled_css();
}

/**
 * Get SCSS to prepend: Boost's (brandcolor, Raw initial SCSS) + ours.
 *
 * @param theme_config $theme The theme config object.
 * @return string SCSS to prepend.
 */
function theme_kabacademy_get_pre_scss($theme) {
    return theme_boost_get_pre_scss(theme_kabacademy_boost_config())
        . "\n" . file_get_contents(__DIR__ . '/scss/pre.scss');
}

/**
 * Get extra SCSS: Boost's (Raw SCSS from the DB — the site branding) + ours.
 *
 * @param theme_config $theme The theme config object.
 * @return string Extra SCSS.
 */
function theme_kabacademy_get_extra_scss($theme) {
    return theme_boost_get_extra_scss(theme_kabacademy_boost_config())
        . "\n" . file_get_contents(__DIR__ . '/scss/kab.scss');
}
