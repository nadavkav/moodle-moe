<?php
namespace local_notes\local;
require_once("$CFG->libdir/formslib.php");

class notes_form extends \moodleform {

        function definition() {
            global $CFG;
            $editoropstion = array(
                'subdirs'=>0,
                'maxbytes'=>0,
                'maxfiles'=>0,
                'changeformat'=>0,
                'context'=>null,
                'noclean'=>0,
                'trusttext'=>0,
                'enable_filemanagement' => true);
            $note = $this->_form; // Don't forget the underscore!
            $note->addElement('static', 'heading','<h1>'.get_string('draftbutton', 'moeworksheets').'</h1>','',$editoropstion);
            $note->addElement('editor', 'content');
            $note->setType('content', PARAM_RAW);
            $note->setDefault('content', array('text' => $this->_customdata['content'], 'format' => FORMAT_HTML));
            $note->disable_form_change_checker();
            $this->add_action_buttons(false,false);
     }
}

