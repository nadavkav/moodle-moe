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

/** Make sure this isn't being directly accessed */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/moewiki/locallib.php');

class mod_moewiki_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $COURSE;

        $mform =& $this->_form;
        $data    = $this->_customdata['data'];

        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name and intro
        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $this->standard_intro_elements(get_string('wikiintro', 'wiki'));

        $mform->addElement('header', 'wikifieldset', get_string('wikisettings', 'wiki'));

        // Subwikis
        $subwikisoptions = array();
        $subwikisoptions[MOEWIKI_SUBWIKIS_SINGLE] = get_string('subwikis_single', 'moewiki');
        $subwikisoptions[MOEWIKI_SUBWIKIS_GROUPS] = get_string('subwikis_groups', 'moewiki');
        $subwikisoptions[MOEWIKI_SUBWIKIS_INDIVIDUAL] = get_string('subwikis_individual', 'moewiki');
        $mform->addElement('select', 'subwikis', get_string("subwikis", "moewiki"), $subwikisoptions);
        $mform->addHelpButton('subwikis', 'subwikis', 'moewiki');

        // Annotation
        $annotationoptions = array('0' => get_string('no'), '1' => get_string('yes'));
        $mform->addElement('select', 'annotation', get_string('annotationsystem', 'moewiki'), $annotationoptions);
        $mform->addHelpButton('annotation', 'annotationsystem', 'moewiki');

        // Editing timeout
        $timeoutoptions = array();
        $timeoutoptions[0] = get_string('timeout_none', 'moewiki');
        $timeoutoptions[15*60] = get_string('numminutes', '', 15);
        $timeoutoptions[30*60] = get_string('numminutes', '', 30);
        $timeoutoptions[60*60] = get_string('numminutes', '', 60);
        $timeoutoptions[120*60] = get_string('numhours', '', 2);
        $timeoutoptions[240*60] = get_string('numhours', '', 4);
        if (debugging('', DEBUG_DEVELOPER)) {
            // This is not a language string because it's only for developer
            // debugging, lots of which requires English...
            $timeoutoptions[3*60] = '3 minutes (for testing)';
        }
        $mform->addElement('select', 'timeout', get_string("timeout", "moewiki"), $timeoutoptions);
        $mform->addHelpButton('timeout', 'timeout', 'moewiki');

        // Read-only controls.
        $mform->addElement('date_selector', 'editbegin', get_string('editbegin', 'moewiki'), array('optional' => true));
        $mform->addHelpButton('editbegin', 'editbegin', 'moewiki');
        $mform->addElement('date_selector', 'editend', get_string('editend', 'moewiki'), array('optional' => true));
        $mform->addHelpButton('editend', 'editend', 'moewiki');

        // Display any template usage warning messages.
        if ((!empty($this->current->id)) && (moewiki_has_subwikis($this->current->id))) {
            $mform->addElement('static', 'name1', get_string('note', 'moewiki'), get_string('subwikiexist', 'moewiki'));
        }
        if (isset($this->current->template)) {
            $mform->addElement('static', 'name2', get_string('note', 'moewiki'), get_string('templatefileexists', 'moewiki',
                    $this->current->template));
        }
        // Template - previously on creation, but allow to add now add anytime.
        $filepickeroptions = array();
        $filepickeroptions['accepted_types'] = array('.xml', '.zip');
        $filepickeroptions['maxbytes'] = $COURSE->maxbytes;
        $mform->addElement('filepicker', 'template_file', get_string('template', 'moewiki'), null, $filepickeroptions);
        $mform->addHelpButton('template_file', 'template', 'moewiki');

        // Wordcount
        $wordcountoptions = array('0' => get_string('no'), '1' => get_string('yes'));
        $mform->addElement('select', 'enablewordcount', get_string('showwordcounts', 'moewiki'), $wordcountoptions);
        $mform->addHelpButton('enablewordcount', 'showwordcounts', 'moewiki');
        $mform->setDefault('enablewordcount', 1);

        // Enable the allow import course wiki pages into this wiki.
        $mform->addElement('checkbox', 'allowimport', get_string('allowimport', 'moewiki', 0));
        $mform->addHelpButton('allowimport', 'allowimport', 'moewiki');

        $this->standard_grading_coursemodule_elements();

        // Standard stuff
        $this->standard_coursemodule_elements();

        // Disable the 'completion with grade' if grading is turned off
        if ($mform->elementExists('completionusegrade')) {
            $mform->disabledIf('completionusegrade', 'grade', 'eq', 0);
        }

        $this->add_action_buttons();

        $this->set_data($data);
    }

    public function add_completion_rules() {
        $mform =& $this->_form;

        $group = array();
        $group[] =& $mform->createElement('checkbox', 'completionpagesenabled', ' ', get_string('completionpages', 'moewiki'));
        $group[] =& $mform->createElement('text', 'completionpages', ' ', array('size' => 3));
        $mform->setType('completionpages', PARAM_INT);
        $mform->addGroup($group, 'completionpagesgroup', get_string('completionpagesgroup', 'moewiki'), array(' '), false);
        $mform->disabledIf('completionpages', 'completionpagesenabled', 'notchecked');

        $group = array();
        $group[] =& $mform->createElement('checkbox', 'completioneditsenabled', ' ', get_string('completionedits', 'moewiki'));
        $group[] =& $mform->createElement('text', 'completionedits', ' ', array('size' => 3));
        $mform->setType('completionedits', PARAM_INT);
        $mform->addGroup($group, 'completioneditsgroup', get_string('completioneditsgroup', 'moewiki'), array(' '), false);
        $mform->disabledIf('completionedits', 'completioneditsenabled', 'notchecked');

        return array('completionpagesgroup', 'completioneditsgroup');
    }

    public function completion_rule_enabled($data) {
        return
            ((!empty($data['completionpagesenabled']) && $data['completionpages'] != 0)) ||
            ((!empty($data['completioneditsenabled']) && $data['completionedits'] != 0));
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Turn off completion settings if the checkboxes aren't ticked
        $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
        if (empty($data->completionpagesenabled) || !$autocompletion) {
            $data->completionpages = 0;
        }
        if (empty($data->completioneditsenabled) || !$autocompletion) {
            $data->completionedits = 0;
        }

        if (empty($data->allowimport)) {
            $data->allowimport = 0;
        }

        return $data;
    }

    public function data_preprocessing(&$default_values) {
        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completionpagesenabled'] = !empty($default_values['completionpages']) ? 1 : 0;
        if (empty($default_values['completionpages'])) {
            $default_values['completionpages'] = 1;
        }
        $default_values['completioneditsenabled'] = !empty($default_values['completionedits']) ? 1 : 0;
        if (empty($default_values['completionedits'])) {
            $default_values['completionedits'] = 1;
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ( (($data['subwikis'] == 0) || ($data['subwikis'] == 2) ) && ($data['groupmode'] > 0) ) {
            $errors['groupmode'] = get_string('errorcoursesubwiki', 'moewiki');
        }
        if ( ($data['subwikis'] == 1) && ($data['groupmode'] == 0) ) {
            $errors['groupmode'] = get_string('errorgroupssubwiki', 'moewiki');
        }
        return $errors;
    }
}
