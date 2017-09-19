<?php

class theme_moe_core_renderer extends theme_bootstrapbase_core_renderer
{

    protected function render_custom_menu(custom_menu $menu)
    {
        global $CFG;

        $hasdisplaymycourses = theme_moe_get_setting('mycourses_dropdown');

        if (isloggedin() && ! isguestuser() && $hasdisplaymycourses) {

            $branchlabel = get_string('mycourses');
            $branchurl = new moodle_url('#');
            $branchtitle = $branchlabel;
            $branchsort = 10000;
            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

            if ($mycourses = enrol_get_my_courses(NULL, 'visible DESC, fullname ASC')) {
                foreach ($mycourses as $mycourse) {
                    $branch->add($mycourse->shortname, new moodle_url('/course/view.php', array(
                        'id' => $mycourse->id
                    )), $mycourse->fullname);
                }
            } else {
                $hometext = get_string('myhome');
                $homelabel = $hometext;
                $branch->add($homelabel, new moodle_url('/my/index.php'), $hometext);
            }
        }
        return parent::render_custom_menu($menu);
    }

    public function footer()
    {
        global $CFG, $DB, $USER;

        $output = $this->container_end_all(true);

        $footer = $this->opencontainers->pop('header/footer');

        if (debugging() and $DB and $DB->is_transaction_started()) {}

        $footer = str_replace($this->unique_end_html_token, $this->page->requires->get_end_code(), $footer);

        $this->page->set_state(moodle_page::STATE_DONE);

        if (! empty($this->page->theme->settings->persistentedit) && property_exists($USER, 'editing') && $USER->editing && ! $this->really_editing) {
            $USER->editing = false;
        }

        return $output . $footer;
    }

    public function footerblocks($region, $classes = array(), $tag = 'aside')
    {
        $classes = (array) $classes;
        $classes[] = 'block-region';
        $attributes = array(
            'id' => 'block-region-' . preg_replace('#[^a-zA-Z0-9_\-]+#', '-', $region),
            'class' => join(' ', $classes),
            'data-blockregion' => $region,
            'data-droptarget' => '1'
        );
        return html_writer::tag($tag, $this->blocks_for_region($region), $attributes);
    }

    public function edit_button(moodle_url $url)
    {
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $btn = 'btn-danger';
            $title = get_string('turneditingoff');
            $icon = 'fa-power-off';
        } else {
            $url->param('edit', 'on');
            $btn = 'btn-success';
            $title = get_string('turneditingon');
            $icon = 'fa-edit';
        }
        return html_writer::tag('a', html_writer::start_tag('i', array(
            'class' => $icon . ' fa fa-fw'
        )) . html_writer::end_tag('i'), array(
            'href' => $url,
            'class' => 'btn ' . $btn,
            'title' => $title
        ));
    }

    /**
     * Outputs a heading
     *
     * @param string $text
     *            The text of the heading
     * @param int $level
     *            The level of importance of the heading. Defaulting to 2
     * @param string $classes
     *            A space-separated list of CSS classes. Defaulting to null
     * @param string $id
     *            An optional ID
     * @return string the HTML to output.
     */
    public function heading($text, $level = 2, $classes = null, $id = null)
    {
        $level = (integer) $level;
        if ($level < 1 or $level > 6) {
            throw new coding_exception('Heading level must be an integer between 1 and 6.');
        }
        $text = html_writer::tag('span', $text);
        return html_writer::tag('h' . $level, $text, array(
            'id' => $id,
            'class' => renderer_base::prepare_classes($classes)
        ));
    }

    /**
     * Renders tabtree
     *
     * @param tabtree $tabtree
     * @return string
     */
    protected function render_tabtree(tabtree $tabtree)
    {
        global $OUTPUT, $COURSE, $CFG;
        $data = new stdClass();
        if (empty($tabtree->subtree)) {
            return '';
        }
        $firstrow = $secondrow = '';
        foreach ($tabtree->subtree as $tab) {
            $firstrow .= $this->render($tab);
            if (($tab->selected || $tab->activated) && ! empty($tab->subtree) && $tab->subtree !== array()) {
                $secondrow = $this->tabtree($tab->subtree);
            }
        }
        $data->firstrow = $firstrow;
        $data->secondrow = $secondrow;
        $path = $CFG->dirroot . '/theme/moe/templates/'. 'format_' . $COURSE->format . '/tabtree.mustache';
        if ( file_exists($path) ) {
            return $this->render_from_template('format_' . $COURSE->format . '/tabtree', $data);
        } else {
            return $this->render_from_template('format_onetopic/tabtree', $data);
        }
    }

    /**
     * Outputs a box.
     *
     * @param string $contents The contents of the box
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other attributes to give the box.
     * @return string the HTML to output.
     */
    public function box($contents, $classes = 'generalbox', $id = null, $attributes = array()) {
        return $this->box_start($classes, $id, $attributes) . $contents . $this->box_end();
    }
}

class theme_moe_format_topics_renderer extends format_topics_renderer
{

    protected function get_nav_links($course, $sections, $sectionno)
    {
        $course = course_get_format($course)->get_course();
        $previousarrow = '<i class="fa fa-chevron-circle-left"></i>';
        $nextarrow = '<i class="fa fa-chevron-circle-right"></i>';
        $canviewhidden = has_capability('moodle/course:viewhiddensections', context_course::instance($course->id)) or ! $course->hiddensections;

        $links = array(
            'previous' => '',
            'next' => ''
        );
        $back = $sectionno - 1;
        while ($back > 0 and empty($links['previous'])) {
            if ($canviewhidden || $sections[$back]->uservisible) {
                $params = array(
                    'id' => 'previous_section'
                );
                if (! $sections[$back]->visible) {
                    $params = array(
                        'class' => 'dimmed_text'
                    );
                }
                $previouslink = html_writer::start_tag('div', array(
                    'class' => 'nav_icon'
                ));
                $previouslink .= $previousarrow;
                $previouslink .= html_writer::end_tag('div');
                $previouslink .= html_writer::start_tag('span', array(
                    'class' => 'text'
                ));
                $previouslink .= html_writer::start_tag('span', array(
                    'class' => 'nav_guide'
                ));
                $previouslink .= get_string('previoussection', 'theme_moe');
                $previouslink .= html_writer::end_tag('span');
                $previouslink .= html_writer::empty_tag('br');
                $previouslink .= get_section_name($course, $sections[$back]);
                $previouslink .= html_writer::end_tag('span');
                $links['previous'] = html_writer::link(course_get_url($course, $back), $previouslink, $params);
            }
            $back --;
        }

        $forward = $sectionno + 1;
        while ($forward <= $course->numsections and empty($links['next'])) {
            if ($canviewhidden || $sections[$forward]->uservisible) {
                $params = array(
                    'id' => 'next_section'
                );
                if (! $sections[$forward]->visible) {
                    $params = array(
                        'class' => 'dimmed_text'
                    );
                }
                $nextlink = html_writer::start_tag('div', array(
                    'class' => 'nav_icon'
                ));
                $nextlink .= $nextarrow;
                $nextlink .= html_writer::end_tag('div');
                $nextlink .= html_writer::start_tag('span', array(
                    'class' => 'text'
                ));
                $nextlink .= html_writer::start_tag('span', array(
                    'class' => 'nav_guide'
                ));
                $nextlink .= get_string('nextsection', 'theme_moe');
                $nextlink .= html_writer::end_tag('span');
                $nextlink .= html_writer::empty_tag('br');
                $nextlink .= get_section_name($course, $sections[$forward]);
                $nextlink .= html_writer::end_tag('span');
                $links['next'] = html_writer::link(course_get_url($course, $forward), $nextlink, $params);
            }
            $forward ++;
        }

        return $links;
    }

    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection)
    {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        if (! ($sectioninfo = $modinfo->get_section_info($displaysection))) {
            print_error('unknowncoursesection', 'error', null, $course->fullname);
            return;
        }

        if (! $sectioninfo->uservisible) {
            if (! $course->hiddensections) {
                echo $this->start_section_list();
                echo $this->section_hidden($displaysection);
                echo $this->end_section_list();
            }
            return;
        }

        echo $this->course_activity_clipboard($course, $displaysection);
        $thissection = $modinfo->get_section_info(0);
        if ($thissection->summary or ! empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
            echo $this->start_section_list();
            echo $this->section_header($thissection, $course, true, $displaysection);
            echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
            echo $this->courserenderer->course_section_add_cm_control($course, 0, $displaysection);
            echo $this->section_footer();
            echo $this->end_section_list();
        }

        echo html_writer::start_tag('div', array(
            'class' => 'single-section'
        ));

        $thissection = $modinfo->get_section_info($displaysection);

        $sectionnavlinks = $this->get_nav_links($course, $modinfo->get_section_info_all(), $displaysection);
        $sectiontitle = '';
        $sectiontitle .= html_writer::start_tag('div', array(
            'class' => 'section-navigation header headingblock'
        ));

        $titleattr = 'mdl-align title';
        if (! $thissection->visible) {
            $titleattr .= ' dimmed_text';
        }
        $sectiontitle .= html_writer::tag('div', get_section_name($course, $displaysection), array(
            'class' => $titleattr
        ));
        $sectiontitle .= html_writer::end_tag('div');
        echo $sectiontitle;

        echo $this->start_section_list();

        echo $this->section_header($thissection, $course, true, $displaysection);

        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();

        echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
        echo $this->courserenderer->course_section_add_cm_control($course, $displaysection, $displaysection);
        echo $this->section_footer();
        echo $this->end_section_list();

        $sectionbottomnav = '';
        $sectionbottomnav .= html_writer::start_tag('nav', array(
            'id' => 'section_footer'
        ));
        $sectionbottomnav .= $sectionnavlinks['previous'];
        $sectionbottomnav .= $sectionnavlinks['next'];

        $sectionbottomnav .= html_writer::empty_tag('br', array(
            'style' => 'clear:both'
        ));
        $sectionbottomnav .= html_writer::end_tag('nav');
        echo $sectionbottomnav;

        echo html_writer::end_tag('div');
    }
}