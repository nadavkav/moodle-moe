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
 * Local plugin "sandbox" - Upgrade plugin tasks
 *
 * @package    local_sandbox
 * @copyright  2013 Alexander Bias, University of Ulm <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_sandbox_upgrade($oldversion) {
    if ($oldversion < 2014051200) {
        echo html_writer::tag('div', get_string('upgrade_notice_2014051200', 'local_sandbox'), array('class' => 'alert alert-info'));

        unset_config('cronruntimehour', 'local_sandbox');
        unset_config('cronruntimemin', 'local_sandbox');
        unset_config('cronrunday', 'local_sandbox');
        upgrade_plugin_savepoint(true, 2014051200, 'local', 'sandbox');
    }

    return true;
}
