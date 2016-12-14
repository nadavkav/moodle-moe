<?php
namespace mod_quizsbs\output;

use mod_quizsbs\structure;

/**
 *
 * @author avi
 *
 */
class editcontent_renderer extends \plugin_renderer_base {

    /**
     *
     * @param moodle_page $page
     *
     * @param string $target
     *            one of rendering target constants
     *
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
    }

    public function editcontent_page(\quizsbs $quizsbsobj, structure $structure,
            \question_edit_contexts $contexts, \moodle_url $pageurl, array $pagevars) {

    }
}

