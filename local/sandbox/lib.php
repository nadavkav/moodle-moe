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
 * Local plugin "sandbox" - Library
 *
 * @package    local_sandbox
 * @copyright  2013 Alexander Bias, University of Ulm <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

define('SANDBOX_LEVEL_NOTICE', 1);
define('SANDBOX_LEVEL_WARNING', 2);
define('SANDBOX_LEVEL_ERROR', 3);


/**
 * Helper function for sending notification mails
 *
 * @param string $message The message
 * @param int $level Notification level
 * @return
 */
function local_sandbox_inform_admin($message, $level = SANDBOX_LEVEL_NOTICE) {
    // Get recipients
    $recipients = get_users_from_config(get_config('local_sandbox', 'notifyonerrors'), 'moodle/site:config');

    // If there are no recipients, don't execute.
    if (!is_array($recipients) || count($recipients) <= 0) {
        return false;
    }


    // If message level is below configured notice level, don't execute
    if ($level < get_config('local_sandbox', 'notifylevel')) {
        return false;
    }


    // Get subject
    if ($level > SANDBOX_LEVEL_WARNING) {
        $subject = get_string('emailsubjecterror', 'local_sandbox');
    }
    else if ($level > SANDBOX_LEVEL_NOTICE) {
        $subject = get_string('emailsubjectwarning', 'local_sandbox');
    }
    else {
        $subject = get_string('emailsubjectnotice', 'local_sandbox');
    }


    // Send mail
    foreach ($recipients as $r) {
        // Email the admin directly rather than putting these through the messaging system
        email_to_user($r, core_user::get_support_user(), $subject, $message);
    }
}
