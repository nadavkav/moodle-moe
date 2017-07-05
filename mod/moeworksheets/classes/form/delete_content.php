<?php
namespace mod_moeworksheets\form;

/**
 *
 * @author avi
 *
 */
class delete_content extends \moodleform {

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

        $mform->addElement('header', 'fromheader', get_string('deletecontent', 'moeworksheets'));
        $mform->addElement('static', 'deleteapprove', get_string('approvedelete', 'moeworksheets'));
        $this->add_action_buttons();
    }
}

