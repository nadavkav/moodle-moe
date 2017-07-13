<?php
namespace local_notes\local;
require_once("$CFG->libdir/formslib.php");

class notes_form extends \moodleform {

//     protected $content;
//     public function __construct( $content) {
//         $this->content = $content;
//     }

        function definition() {
            global $CFG, $content;
            $note = $this->_form; // Don't forget the underscore!
            $note->addElement('editor', 'content', get_string('draftbutton', 'moeworksheets'));
            $note->setType('content', PARAM_RAW);
            $note->setDefault('content', 'text from the note form class');
            $this->add_action_buttons(false,false);
     }
}

