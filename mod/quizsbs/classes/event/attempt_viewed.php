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
 * The mod_quizsbs attempt viewed event.
 *
 * @package    mod_quizsbs
 * @copyright  2014 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_quizsbs\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_quizsbs attempt viewed event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int quizsbsid: the id of the quizsbs.
 * }
 *
 * @package    mod_quizsbs
 * @since      Moodle 2.7
 * @copyright  2014 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_viewed extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'quizsbs_attempts';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventattemptviewed', 'mod_quizsbs');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has viewed the attempt with id '$this->objectid' belonging to the user " .
            "with id '$this->relateduserid' for the quizsbs with course module id '$this->contextinstanceid'.";
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/quizsbs/review.php', array('attempt' => $this->objectid));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array
     */
    protected function get_legacy_logdata() {
        return array($this->courseid, 'quizsbs', 'continue attempt', 'review.php?attempt=' . $this->objectid,
            $this->other['quizsbsid'], $this->contextinstanceid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['quizsbsid'])) {
            throw new \coding_exception('The \'quizsbsid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'quizsbs_attempts', 'restore' => 'quizsbs_attempt');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['quizsbsid'] = array('db' => 'quizsbs', 'restore' => 'quizsbs');

        return $othermapped;
    }
}
