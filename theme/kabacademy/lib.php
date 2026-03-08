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
 * @package    theme_kabacademy
 * @copyright  2026 Kabacademy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string SCSS content.
 */
function theme_kabacademy_get_main_scss_content($theme) {
    return theme_boost_get_main_scss_content($theme);
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
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return string SCSS to prepend.
 */
function theme_kabacademy_get_pre_scss($theme) {
    return theme_boost_get_pre_scss($theme);
}

/**
 * Get extra SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string Extra SCSS.
 */
function theme_kabacademy_get_extra_scss($theme) {
    $scss = theme_boost_get_extra_scss($theme);

    // Custom progress bar styles for block_myoverview.
    $scss .= '
    .block_myoverview .dashboard-progress-bar {
        .progress {
            overflow: hidden;

            .progress-bar {
                transition: width 0.3s ease;
            }
        }
    }
    ';

    return $scss;
}
