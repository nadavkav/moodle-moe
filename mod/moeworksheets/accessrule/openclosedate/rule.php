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
 * Implementaton of the moeworksheetsaccess_openclosedate plugin.
 *
 * @package    moeworksheetsaccess
 * @subpackage openclosedate
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/moeworksheets/accessrule/accessrulebase.php');


/**
 * A rule enforcing open and close dates.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moeworksheetsaccess_openclosedate extends moeworksheets_access_rule_base {

    public static function make(moeworksheets $moeworksheetsobj, $timenow, $canignoretimelimits) {
        // This rule is always used, even if the moeworksheets has no open or close date.
        return new self($moeworksheetsobj, $timenow);
    }

    public function description() {
        $result = array();
        if ($this->timenow < $this->moeworksheets->timeopen) {
            $result[] = get_string('moeworksheetsnotavailable', 'moeworksheetsaccess_openclosedate',
                    userdate($this->moeworksheets->timeopen));
            if ($this->moeworksheets->timeclose) {
                $result[] = get_string('moeworksheetscloseson', 'moeworksheets', userdate($this->moeworksheets->timeclose));
            }

        } else if ($this->moeworksheets->timeclose && $this->timenow > $this->moeworksheets->timeclose) {
            $result[] = get_string('moeworksheetsclosed', 'moeworksheets', userdate($this->moeworksheets->timeclose));

        } else {
            if ($this->moeworksheets->timeopen) {
                $result[] = get_string('moeworksheetsopenedon', 'moeworksheets', userdate($this->moeworksheets->timeopen));
            }
            if ($this->moeworksheets->timeclose) {
                $result[] = get_string('moeworksheetscloseson', 'moeworksheets', userdate($this->moeworksheets->timeclose));
            }
        }

        return $result;
    }

    public function prevent_access() {
        $message = get_string('notavailable', 'moeworksheetsaccess_openclosedate');

        if ($this->timenow < $this->moeworksheets->timeopen) {
            return $message;
        }

        if (!$this->moeworksheets->timeclose) {
            return false;
        }

        if ($this->timenow <= $this->moeworksheets->timeclose) {
            return false;
        }

        if ($this->moeworksheets->overduehandling != 'graceperiod') {
            return $message;
        }

        if ($this->timenow <= $this->moeworksheets->timeclose + $this->moeworksheets->graceperiod) {
            return false;
        }

        return $message;
    }

    public function is_finished($numprevattempts, $lastattempt) {
        return $this->moeworksheets->timeclose && $this->timenow > $this->moeworksheets->timeclose;
    }

    public function end_time($attempt) {
        if ($this->moeworksheets->timeclose) {
            return $this->moeworksheets->timeclose;
        }
        return false;
    }

    public function time_left_display($attempt, $timenow) {
        // If this is a teacher preview after the close date, do not show
        // the time.
        if ($attempt->preview && $timenow > $this->moeworksheets->timeclose) {
            return false;
        }
        // Otherwise, return to the time left until the close date, providing that is
        // less than moeworksheets_SHOW_TIME_BEFORE_DEADLINE.
        $endtime = $this->end_time($attempt);
        if ($endtime !== false && $timenow > $endtime - moeworksheets_SHOW_TIME_BEFORE_DEADLINE) {
            return $endtime - $timenow;
        }
        return false;
    }
}
