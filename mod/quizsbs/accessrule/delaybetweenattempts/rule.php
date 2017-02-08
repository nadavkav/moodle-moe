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
 * Implementaton of the quizsbsaccess_delaybetweenattempts plugin.
 *
 * @package    quizsbsaccess
 * @subpackage delaybetweenattempts
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quizsbs/accessrule/accessrulebase.php');


/**
 * A rule imposing the delay between attempts settings.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizsbsaccess_delaybetweenattempts extends quizsbs_access_rule_base {

    public static function make(quizsbs $quizsbsobj, $timenow, $canignoretimelimits) {
        if (empty($quizsbsobj->get_quizsbs()->delay1) && empty($quizsbsobj->get_quizsbs()->delay2)) {
            return null;
        }

        return new self($quizsbsobj, $timenow);
    }

    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        if ($this->quizsbs->attempts > 0 && $numprevattempts >= $this->quizsbs->attempts) {
            // No more attempts allowed anyway.
            return false;
        }
        if ($this->quizsbs->timeclose != 0 && $this->timenow > $this->quizsbs->timeclose) {
            // No more attempts allowed anyway.
            return false;
        }
        $nextstarttime = $this->compute_next_start_time($numprevattempts, $lastattempt);
        if ($this->timenow < $nextstarttime) {
            if ($this->quizsbs->timeclose == 0 || $nextstarttime <= $this->quizsbs->timeclose) {
                return get_string('youmustwait', 'quizsbsaccess_delaybetweenattempts',
                        userdate($nextstarttime));
            } else {
                return get_string('youcannotwait', 'quizsbsaccess_delaybetweenattempts');
            }
        }
        return false;
    }

    /**
     * Compute the next time a student would be allowed to start an attempt,
     * according to this rule.
     * @param int $numprevattempts number of previous attempts.
     * @param object $lastattempt information about the previous attempt.
     * @return number the time.
     */
    protected function compute_next_start_time($numprevattempts, $lastattempt) {
        if ($numprevattempts == 0) {
            return 0;
        }

        $lastattemptfinish = $lastattempt->timefinish;
        if ($this->quizsbs->timelimit > 0) {
            $lastattemptfinish = min($lastattemptfinish,
                    $lastattempt->timestart + $this->quizsbs->timelimit);
        }

        if ($numprevattempts == 1 && $this->quizsbs->delay1) {
            return $lastattemptfinish + $this->quizsbs->delay1;
        } else if ($numprevattempts > 1 && $this->quizsbs->delay2) {
            return $lastattemptfinish + $this->quizsbs->delay2;
        }
        return 0;
    }

    public function is_finished($numprevattempts, $lastattempt) {
        $nextstarttime = $this->compute_next_start_time($numprevattempts, $lastattempt);
        return $this->timenow <= $nextstarttime &&
        $this->quizsbs->timeclose != 0 && $nextstarttime >= $this->quizsbs->timeclose;
    }
}
