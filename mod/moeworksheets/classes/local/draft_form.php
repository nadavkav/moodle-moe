<?php
namespace mod_moeworksheets\local;
require_once("$CFG->libdir/formslib.php");

class draft_form extends \moodleform {
    function definition() {
        global $CFG;
        $draft = $this->_form; // Don't forget the underscore!
        $draft->addElement('editor', 'fieldname', get_string('draftbutton', 'moeworksheets'));
        $draft->setType('fieldname', PARAM_RAW);
    }
}

