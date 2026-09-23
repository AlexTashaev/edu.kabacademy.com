<?php
/**
 * @package auth_kabtarget
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'KAB: keep target URL';
$string['auth_kabtargetdescription'] = 'Passes the page the user was heading to (wantsurl) to the external login form as the kab_target parameter, so that after passwordless login on kabacademy.com the user is taken to that exact page instead of the courses list. Does not authenticate anyone.';
$string['privacy:metadata'] = 'The KAB keep target URL plugin does not store any personal data.';
