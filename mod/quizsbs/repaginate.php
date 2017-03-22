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
 * Rest endpoint for ajax editing for paging operations on the quizsbs structure.
 *
 * @package   mod_quizsbs
 * @copyright 2014 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quizsbs/locallib.php');

$quizsbsid = required_param('quizsbsid', PARAM_INT);
$slotnumber = required_param('slot', PARAM_INT);
$repagtype = required_param('repag', PARAM_INT);

require_sesskey();
$quizsbsobj = quizsbs::create($quizsbsid);
require_login($quizsbsobj->get_course(), false, $quizsbsobj->get_cm());
require_capability('mod/quizsbs:manage', $quizsbsobj->get_context());
if (quizsbs_has_attempts($quizsbsid)) {
    $reportlink = quizsbs_attempt_summary_link_to_reports($quizsbsobj->get_quizsbs(),
                    $quizsbsobj->get_cm(), $quizsbsobj->get_context());
    throw new \moodle_exception('cannoteditafterattempts', 'quizsbs',
            new moodle_url('/mod/quizsbs/edit.php', array('cmid' => $quizsbsobj->get_cmid())), $reportlink);
}

$slotnumber++;
$repage = new \mod_quizsbs\repaginate($quizsbsid);
$repage->repaginate_slots($slotnumber, $repagtype);

$structure = $quizsbsobj->get_structure();
$slots = $structure->refresh_page_numbers_and_update_db();

redirect(new moodle_url('edit.php', array('cmid' => $quizsbsobj->get_cmid())));
