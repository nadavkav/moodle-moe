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

namespace mod_quizsbs\form;
require_once(__DIR__ . '/../../../../config.php');

/**
 *
 * @author avi
 *
 */
class content_load extends \moodleform {

    /**
     *
     * @param mixed $action
     *            the action attribute for the form. If empty defaults to auto detect the
     *            current url. If a moodle_url object then outputs params as hidden variables.
     *
     * @param mixed $customdata
     *            if your form defintion method needs access to data such as $course
     *            $cm, etc. to construct the form definition then pass it in this array. You can
     *            use globals for somethings.
     *
     * @param string $method
     *            if you set this to anything other than 'post' then _GET and _POST will
     *            be merged and used as incoming data to the form.
     *
     * @param string $target
     *            target frame for form submission. You will rarely use this. Don't use
     *            it if you don't need to as the target attribute is deprecated in xhtml strict.
     *
     * @param mixed $attributes
     *            you can pass a string of html attributes here or an array.
     *
     * @param bool $editable
     *
     * @param array $ajaxformdata
     *            Forms submitted via ajax, must pass their data here, instead of relying on _GET and _POST.
     *
     */

    protected $cmid;

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true,
                                    $ajaxformdata = null) {
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    /**
     * (non-PHPdoc)
     *
     * @see moodleform::definition()
     *
     */
    protected function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $quizsbjects = $DB->get_records('quizsbs_subject', array('quizsbsid' => $this->_customdata['structure']->get_quizsbsid()));
        $mform->addElement('header', 'categoryheader', get_string('loadcontent', 'quizsbs'));
        $mform->addElement('text', 'additionalcontentname', get_string('additionalcontentname', 'quizsbs'));
        $mform->setType('additionalcontentname', PARAM_TEXT);
        $mform->addRule('additionalcontentname', get_string('error'), 'required');
        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'contenttype', '', get_string('editor', 'quizsbs'), 0);
        $radioarray[] = $mform->createElement('radio', 'contenttype', '', get_string('javascriptapp', 'quizsbs'), 2);

        $mform->addGroup($radioarray, 'contentradio', '', array(' '), false);
        $mform->setDefault('contenttype', 0);

        $mform->addElement('editor', 'htmleditor', get_string('editor', 'quizsbs'),null , array(
            'subdirs' => true,
            'maxbytes' => 0,
            'maxfiles' => 99,
            'changeformat' => 0,
            'context' => null,
            'noclean' => 0,
            'trusttext' => 0,
            'enable_filemanagement' => true
        ));
        $mform->setType('htmleditor', PARAM_RAW);
        $mform->addElement('textarea', 'csseditor', get_string('csseditor', 'quizsbs'), 'wrap="virtual" rows="20" cols="50"');
        $mform->setType('csseditor', PARAM_RAW);
        $mform->disabledIf('csseditor', 'contenttype', 'neq', '2');
        $mform->addElement('textarea', 'javascripteditor', get_string('javascripteditor', 'quizsbs'), 'wrap="virtual" rows="20" cols="50"');
        $mform->disabledIf('javascripteditor', 'contenttype', 'neq', '2');
        $mform->setType('javascripteditor', PARAM_RAW);
        $mform->addElement('hidden', 'id', 'id', null);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'createdate', 'createdate', null);
        $mform->setType('createdate', PARAM_INT);
        $this->add_action_buttons();
    }
}

