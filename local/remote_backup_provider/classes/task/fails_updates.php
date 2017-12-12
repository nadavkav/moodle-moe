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
namespace local_remote_backup_provider\task;

use core\task\scheduled_task;
use \curl;
use local_remote_backup_provider\fail;

defined('MOODLE_INTERNAL') || die();

global  $CFG;
require_once($CFG->libdir . '/filelib.php');
/**
 * @package    local_remote_backup_provider
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fails_updates extends scheduled_task {


    /**
     * Return localised event name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('cronjub', 'local_remote_backup_provider');
    }


    /**
     * send nottification to all fails subscribers
     */
    public function execute() {
        global $DB;
        $fails = $DB->get_records('remote_backup_provider_fails');
        foreach ($fails as $fail) {
            $fail = new fail($fail->id);
            $url = explode('/', $fail->get_url());
            mtrace("Try to update ". $url[2]);
            if ($fail->send()) {
                mtrace("Succes to update ". $url[2]);
            } else {
                mtrace("faild to update ". $url[2]);
            }
        }
    }
}
