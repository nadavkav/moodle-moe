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
 * The classes_report report viewed event.
 *
 * @package    report_log
 * @copyright  2013 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_moereports\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The report_log report viewed event class.
 *
 * @property-read array $other {
 *      Extra information about the event.
 *
 *      - int groupid: Group to display.
 *      - int date: Date to display logs from.
 *      - int modid: Module id for which logs were displayed.
 *      - string modaction: Module action.
 *      - string logformat: Log format in which logs were displayed.
 * }
 *
 * @package    report_moereports
 * @since      Moodle 3.1
 * @copyright  2018 Meir Ifrach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class classes_report_viewed extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('readreportmoereports', 'report_moereports');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return get_string('readreportmoereportsdesc', 'report_moereports', $this->userid);
    }


    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/user/profile.php', array('id' => $this->userid));
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['date'])) {
            throw new \coding_exception('The \'date\' value must be set in other.');
        }

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_other_mapping() {
        return ;
    }
}
