<?php
namespace local_notes\local;
require_once("$CFG->libdir/formslib.php");

class notes_form extends \moodleform {

    function definition() {
        global $CFG;
        $draft = $this->_form; // Don't forget the underscore!
        $draft->addElement('editor', 'content', get_string('draftbutton', 'moeworksheets'));
        $draft->setType('fieldname', PARAM_RAW);
        $this->add_action_buttons(false,false);
    }
}

