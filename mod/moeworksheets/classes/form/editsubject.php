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

namespace mod_moeworksheets\form;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @author avi
 *
 */
class editsubject extends \moodleform {

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
        $mform =& $this->_form;

        $mform->addElement('header', 'categoryheader', get_string('editsubject', 'moeworksheets'));
        $mform->addElement('text', 'subjectname', get_string('subjectname', 'moeworksheets'));
        $mform->setType('subjectname', PARAM_TEXT);
        $this->add_action_buttons();
    }
}

