<?php
namespace local_notes\local;
require_once("$CFG->libdir/formslib.php");

class notes_form extends \moodleform {

        function definition() {
            global $CFG;
            $note = $this->_form; // Don't forget the underscore!
            $note->addElement('editor', 'content', get_string('draftbutton', 'moeworksheets'));
            $note->setType('content', PARAM_RAW);
            $note->setDefault('content', array('text' => $this->_customdata['content']));
            $this->add_action_buttons(false,false);
     }
}

