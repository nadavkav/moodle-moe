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
 * A {@link qubaid_condition} representing all the attempts by one user at a given moeworksheets.
 *
 * @package   mod_moeworksheets
 * @category  question
 * @copyright 2015 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_moeworksheets\question;
defined('MOODLE_INTERNAL') || die();


/**
 * A {@link qubaid_condition} representing all the attempts by one user at a given moeworksheets.
 *
 * @copyright 2015 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qubaids_for_users_attempts extends \qubaid_join {
    /**
     * Constructor.
     *
     * This takes the same arguments as {@link moeworksheets_get_user_attempts()}.
     *
     * @param int $moeworksheetsid the moeworksheets id.
     * @param int $userid the userid.
     * @param string $status 'all', 'finished' or 'unfinished' to control
     * @param bool $includepreviews defaults to false.
     */
    public function __construct($moeworksheetsid, $userid, $status = 'finished', $includepreviews = false) {
        $where = 'moeworksheetsa.moeworksheets = :moeworksheetsamoeworksheets AND moeworksheetsa.userid = :userid';
        $params = array('moeworksheetsamoeworksheets' => $moeworksheetsid, 'userid' => $userid);

        if (!$includepreviews) {
            $where .= ' AND preview = 0';
        }

        switch ($status) {
            case 'all':
                break;

            case 'finished':
                $where .= ' AND state IN (:state1, :state2)';
                $params['state1'] = \moeworksheets_attempt::FINISHED;
                $params['state2'] = \moeworksheets_attempt::ABANDONED;
                break;

            case 'unfinished':
                $where .= ' AND state IN (:state1, :state2)';
                $params['state1'] = \moeworksheets_attempt::IN_PROGRESS;
                $params['state2'] = \moeworksheets_attempt::OVERDUE;
                break;
        }

        parent::__construct('{moeworksheets_attempts} moeworksheetsa', 'moeworksheetsa.uniqueid', $where, $params);
    }
}
