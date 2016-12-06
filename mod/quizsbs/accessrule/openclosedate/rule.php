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
 * Implementaton of the quizsbsaccess_openclosedate plugin.
 *
 * @package    quizsbsaccess
 * @subpackage openclosedate
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quizsbs/accessrule/accessrulebase.php');


/**
 * A rule enforcing open and close dates.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizsbsaccess_openclosedate extends quizsbs_access_rule_base {

    public static function make(quizsbs $quizsbsobj, $timenow, $canignoretimelimits) {
        // This rule is always used, even if the quizsbs has no open or close date.
        return new self($quizsbsobj, $timenow);
    }

    public function description() {
        $result = array();
        if ($this->timenow < $this->quizsbs->timeopen) {
            $result[] = get_string('quizsbsnotavailable', 'quizsbsaccess_openclosedate',
                    userdate($this->quizsbs->timeopen));
            if ($this->quizsbs->timeclose) {
                $result[] = get_string('quizsbscloseson', 'quizsbs', userdate($this->quizsbs->timeclose));
            }

        } else if ($this->quizsbs->timeclose && $this->timenow > $this->quizsbs->timeclose) {
            $result[] = get_string('quizsbsclosed', 'quizsbs', userdate($this->quizsbs->timeclose));

        } else {
            if ($this->quizsbs->timeopen) {
                $result[] = get_string('quizsbsopenedon', 'quizsbs', userdate($this->quizsbs->timeopen));
            }
            if ($this->quizsbs->timeclose) {
                $result[] = get_string('quizsbscloseson', 'quizsbs', userdate($this->quizsbs->timeclose));
            }
        }

        return $result;
    }

    public function prevent_access() {
        $message = get_string('notavailable', 'quizsbsaccess_openclosedate');

        if ($this->timenow < $this->quizsbs->timeopen) {
            return $message;
        }

        if (!$this->quizsbs->timeclose) {
            return false;
        }

        if ($this->timenow <= $this->quizsbs->timeclose) {
            return false;
        }

        if ($this->quizsbs->overduehandling != 'graceperiod') {
            return $message;
        }

        if ($this->timenow <= $this->quizsbs->timeclose + $this->quizsbs->graceperiod) {
            return false;
        }

        return $message;
    }

    public function is_finished($numprevattempts, $lastattempt) {
        return $this->quizsbs->timeclose && $this->timenow > $this->quizsbs->timeclose;
    }

    public function end_time($attempt) {
        if ($this->quizsbs->timeclose) {
            return $this->quizsbs->timeclose;
        }
        return false;
    }

    public function time_left_display($attempt, $timenow) {
        // If this is a teacher preview after the close date, do not show
        // the time.
        if ($attempt->preview && $timenow > $this->quizsbs->timeclose) {
            return false;
        }
        // Otherwise, return to the time left until the close date, providing that is
        // less than quizsbs_SHOW_TIME_BEFORE_DEADLINE.
        $endtime = $this->end_time($attempt);
        if ($endtime !== false && $timenow > $endtime - quizsbs_SHOW_TIME_BEFORE_DEADLINE) {
            return $endtime - $timenow;
        }
        return false;
    }
}
