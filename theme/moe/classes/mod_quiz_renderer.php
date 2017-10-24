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

defined('MOODLE_INTERNAL') || die;


class theme_moe_mod_quiz_renderer extends mod_quiz_renderer
{
	
	/**
	 * Creates any controls a the page should have.
	 *
	 * @param quiz_attempt $attemptobj
	 */
	public function summary_page_controls($attemptobj) {
		$output = '';
		
		// Return to place button.
		if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
			$button = new single_button(
					new moodle_url($attemptobj->attempt_url(null, $attemptobj->get_currentpage())),
					get_string('returnattempt', 'quiz'));
			$output .= $this->container($this->container($this->render($button),
					'controls'), 'submitbtns mdl-align');
		}
		
		// Finish attempt button.
		$options = array(
				'attempt' => $attemptobj->get_attemptid(),
				'finishattempt' => 1,
				'timeup' => 0,
				'slots' => '',
				'sesskey' => sesskey(),
		);
		
		$button = new single_button(
				new moodle_url($attemptobj->processattempt_url(), $options),
				get_string('submitallandfinish', 'quiz'));
		$button->id = 'responseform';
		if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
			$button->add_action(new confirm_action(get_string('confirmclose', 'quiz'), null,
					get_string('submitallandfinish', 'quiz')));
		}
		
		$duedate = $attemptobj->get_due_date();
		$message = '';
		if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
			$message ="<p class='olympicmark'>" . get_string('overduemustbesubmittedby', 'quiz', userdate($duedate)) . "</p>";
			
		} else if ($duedate) {
			$message = get_string('mustbesubmittedby', 'quiz', userdate($duedate));
		}
		
		$output .= $this->countdown_timer($attemptobj, time());
		$output .= $this->container($message . $this->container(
				$this->render($button), 'controls'), 'submitbtns mdl-align');
		
		return $output;
	}
	
	
	
	
	
    /**
     * Display a quiz navigation button.
     *
     * @param quiz_nav_question_button $button
     * @return string HTML fragment.
     */
    protected function render_quiz_nav_question_button(quiz_nav_question_button $button) {
        if (is_numeric($button->number)) {
            $qnostring = 'questionnonav';
        } else {
            $qnostring = 'questionnonavinfo';
        }

        $classes = array('qnbutton', $button->stateclass, $button->navmethod, $qnostring);
        $extrainfo = array();

        if ($button->currentpage) {
            $classes[] = 'thispage';
            $extrainfo[] = get_string('onthispage', 'quiz');
        }

        // Flagged?
        if ($button->flagged) {
            $classes[] = 'flagged';
            $flaglabel = get_string('flagged', 'question');
        } else {
            $flaglabel = '';
        }
        $extrainfo[] = \html_writer::tag('span', $flaglabel, array('class' => 'flagstate'));

        $a = new \stdClass();
        $a->number = $button->number;
        $a->attributes = implode(' ', $extrainfo);
        $tagcontents = \html_writer::tag('span', '', array('class' => 'thispageholder')) .
            \html_writer::tag('span', '', array('class' => 'trafficlight')) .
            get_string($qnostring, 'quiz', $a);
        $tagattributes = array('class' => implode(' ', $classes), 'id' => $button->id,
            'title' => $button->statestring, 'data-quiz-page' => $button->page);

        if ($button->url) {
            return \html_writer::link($button->url, $tagcontents, $tagattributes);
        } else {
            return \html_writer::tag('span', $tagcontents, $tagattributes);
        }
    }

    /**
     * Outputs the navigation block panel
     *
     * @param quiz_nav_panel_base $panel instance of quiz_nav_panel_base
     */
    public function navigation_panel(quiz_nav_panel_base $panel) {

        $output = '';
        $userpicture = $panel->user_picture();
        if ($userpicture) {
            $fullname = fullname($userpicture->user);
            if ($userpicture->size === true) {
                $fullname = html_writer::div($fullname);
            }
            $output .= html_writer::tag('div', $this->render($userpicture) . $fullname,
                array('id' => 'user-picture', 'class' => 'clearfix'));
        }
        $output .= $panel->render_before_button_bits($this);

        $bcc = $panel->get_button_container_class();
        $output .= html_writer::start_tag('div', array('class' => "qn_buttons clearfix $bcc"));
        $buttons = $panel->get_question_buttons($panel);
        foreach ($buttons as $key => $button) {
            if ($button instanceof \quiz_nav_section_heading) {
                $output .= '<a href="'.$buttons[$key+1]->url->out().'">'.$this->render($button).'</a>';
            } else {
                $output .= $this->render($button);
            }

        }
        $output .= html_writer::end_tag('div');

        $output .= html_writer::tag('div', $panel->render_end_bits($this),
            array('class' => 'othernav'));

        $this->page->requires->js_init_call('M.mod_quiz.nav.init', null, false,
            quiz_get_js_module());

        return $output;
    }

}
