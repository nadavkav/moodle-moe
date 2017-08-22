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
 * Implementaton of the moeworksheetsaccess_offlinemode plugin.
 *
 * @package   moeworksheetsaccess_offlinemode
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/moeworksheets/accessrule/accessrulebase.php');


/**
 * The access rule class implementation for the moeworksheetsaccess_offlinemode plugin.
 *
 * A rule that hijacks the standard attempt.php page, and replaces it with
 * different script which loads all the questions at once and then allows the
 * student to keep working, even if the network connection is lost. However,
 * if the network is working, responses are saved back to the server.
 *
 * @copyright  2014 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moeworksheetsaccess_offlinemode extends moeworksheets_access_rule_base {

    /** @var string the URL path to our replacement attempt script. */
    const ATTEMPT_URL = '/mod/moeworksheets/accessrule/offlinemode/attempt.php';

    public static function make(moeworksheets $moeworksheetsobj, $timenow, $canignoretimelimits) {

        if (empty($moeworksheetsobj->get_moeworksheets()->offlinemode_enabled) ||
                !self::is_compatible_behaviour($moeworksheetsobj->get_moeworksheets()->preferredbehaviour)) {
            return null;
        }

        return new self($moeworksheetsobj, $timenow);
    }

    public static function add_settings_form_fields(
            mod_moeworksheets_mod_form $moeworksheetsform, MoodleQuickForm $mform) {
        $config = get_config('moeworksheetsaccess_offlinemode');
        $mform->addElement('selectyesno', 'offlinemode_enabled',
                get_string('offlinemodeenabled', 'moeworksheetsaccess_offlinemode'));
        $mform->addHelpButton('offlinemode_enabled',
                'offlinemodeenabled', 'moeworksheetsaccess_offlinemode');
        $mform->setDefault('offlinemode_enabled', !empty($config->defaultenabled));
        $mform->setAdvanced('offlinemode_enabled', !empty($config->defaultenabled_adv));

        foreach (question_engine::get_behaviour_options(null) as $behaviour => $notused) {
            if (!self::is_compatible_behaviour($behaviour)) {
                $mform->disabledIf('offlinemode_enabled', 'preferredbehaviour',
                        'eq', $behaviour);
            }
        }
    }

    /**
     * Given the moeworksheets "How questions behave" setting, can the fault-tolerant mode work
     * with that behaviour?
     * @param string $behaviour the internal name (e.g. 'interactive') of an archetypal behaviour.
     * @return boolean whether fault-tolerant mode can be used.
     */
    public static function is_compatible_behaviour($behaviour) {
        $unusedoptions = question_engine::get_behaviour_unused_display_options($behaviour);
        // Sorry, double negative here. The heuristic is that:
        // The behaviour is compatible if we don't need to show specific feedback during the attempt.
        return in_array('specificfeedback', $unusedoptions);
    }

    public static function save_settings($moeworksheets) {
        global $DB;
        if (empty($moeworksheets->offlinemode_enabled)) {
            $DB->delete_records('moeworksheetsaccess_offlinmo', array('moeworksheetsid' => $moeworksheets->id));
        } else {
            if (!$DB->record_exists('moeworksheetsaccess_offlinmo', array('moeworksheetsid' => $moeworksheets->id))) {
                $record = new stdClass();
                $record->moeworksheetsid = $moeworksheets->id;
                $record->enabled = 1;
                $DB->insert_record('moeworksheetsaccess_offlinmo', $record);
            }
        }
    }

    public static function delete_settings($moeworksheets) {
        global $DB;
        $DB->delete_records('moeworksheetsaccess_offlinmo', array('moeworksheetsid' => $moeworksheets->id));
    }

    public static function get_settings_sql($moeworksheetsid) {
        return array(
            'COALESCE(offlinemode.enabled, 0) AS offlinemode_enabled',
            'LEFT JOIN {moeworksheetsaccess_offlinmo} offlinemode ON offlinemode.moeworksheetsid = moeworksheets.id',
            array());
    }

    public function description() {
        if (!$this->moeworksheetsobj->has_capability('moeworksheetsaccess/offlinemode:uploadresponses')) {
            return '';
        }

        return get_string('description', 'moeworksheetsaccess_offlinemode',
                html_writer::link(new moodle_url('/mod/moeworksheets/accessrule/offlinemode/upload.php',
                        array('id' => $this->moeworksheetsobj->get_cmid())),
                        get_string('descriptionlink', 'moeworksheetsaccess_offlinemode')));
    }

    public function setup_attempt_page($page) {
        if ($page->pagetype == 'mod-moeworksheets-attempt' || $page->pagetype == 'mod-moeworksheets-summary') {
            redirect(new moodle_url(self::ATTEMPT_URL, $page->url->params()));
        }
    }
}
