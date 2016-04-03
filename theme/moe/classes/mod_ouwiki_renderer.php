<?php
class theme_moe_mod_ouwiki_renderer extends mod_ouwiki_renderer {
    /**
     * Prints the header and (if applicable) group selector.
     *
     * @param object $ouwiki Wiki object
     * @param object $cm Course-modules object
     * @param object $course
     * @param object $subwiki Subwiki objecty
     * @param string $pagename Name of page
     * @param object $context Context object
     * @param string $afterpage If included, extra content for navigation string after page link
     * @param bool $hideindex If true, doesn't show the index/recent pages links
     * @param bool $notabs If true, prints the after-tabs div here
     * @param string $head Things to include inside html head
     * @param string $title
     * @param string $querytext for use when changing groups against search criteria
     */
    public function ouwiki_print_start($ouwiki, $cm, $course, $subwiki, $pagename, $context,
        $afterpage = null, $hideindex = null, $notabs = null, $head = '', $title='', $querytext = '') {
            global $USER, $OUTPUT;
            $output = '';
    
            if ($pagename == null) {
                $pagename = '';
            }
    
            ouwiki_print_header($ouwiki, $cm, $subwiki, $pagename, $afterpage, $head, $title);
    
            $canview = ouwiki_can_view_participation($course, $ouwiki, $subwiki, $cm);
            $page = basename($_SERVER['PHP_SELF']);
    
            // Print group/user selector
            $showselector = true;
            if (($page == 'userparticipation.php' && $canview != OUWIKI_MY_PARTICIPATION)
                || $page == 'participation.php'
                && (int)$ouwiki->subwikis == OUWIKI_SUBWIKIS_INDIVIDUAL) {
                    $showselector = false;
                }
                if ($showselector) {
                    $selector = $this->ouwiki_display_subwiki_selector($subwiki, $ouwiki, $cm,
                        $context, $course, $page, $querytext);
                    $output .= $selector;
                }
    
                // Print index link
                if (!$hideindex) {
                    $output .= html_writer::start_tag('div', array('id' => 'ouwiki_indexlinks'));
                    $output .= html_writer::start_tag('ul');
    
                    if ($page == 'wikiindex.php') {
                        $output .= html_writer::start_tag('li', array('id' => 'ouwiki_nav_index'));
                        $output .= html_writer::start_tag('span');
                        $output .= get_string('index', 'ouwiki');
                        $output .= html_writer::end_tag('span');
                        $output .= html_writer::end_tag('li');
                    } else {
                        $output .= html_writer::start_tag('li', array('id' => 'ouwiki_nav_index'));
                        $output .= html_writer::tag('a', get_string('index', 'ouwiki'),
                            array('href' => 'wikiindex.php?'.
                                ouwiki_display_wiki_parameters('', $subwiki, $cm, OUWIKI_PARAMS_URL)));
                        $output .= html_writer::end_tag('li');
                    }
                    if ($page == 'wikihistory.php') {
                        $output .= html_writer::start_tag('li', array('id' => 'ouwiki_nav_history'));
                        $output .= html_writer::start_tag('span');
                        $output .= get_string('wikirecentchanges', 'ouwiki');
                        $output .= html_writer::end_tag('span');
                        $output .= html_writer::end_tag('li');
                    } else {
                        $output .= html_writer::start_tag('li', array('id' => 'ouwiki_nav_history'));
                        $output .= html_writer::tag('a', get_string('wikirecentchanges', 'ouwiki'),
                            array('href' => 'wikihistory.php?'.
                                ouwiki_display_wiki_parameters('', $subwiki, $cm, OUWIKI_PARAMS_URL)));
                        $output .= html_writer::end_tag('li');
                    }
                    // Check for mod setting and ability to edit that enables this link.
                    if (($subwiki->canedit) && ($ouwiki->allowimport)) {
                        $output .= html_writer::start_tag('li', array('id' => 'ouwiki_import_pages'));
                        if ($page == 'import.php') {
                            $output .= html_writer::tag('span', get_string('import', 'ouwiki'));
                        } else {
                            $importlink = new moodle_url('/mod/ouwiki/import.php',
                                ouwiki_display_wiki_parameters($pagename, $subwiki, $cm, OUWIKI_PARAMS_ARRAY));
                            $output .= html_writer::link($importlink, get_string('import', 'ouwiki'));
                        }
                        $output .= html_writer::end_tag('li');
                    }
                    if ($canview == OUWIKI_USER_PARTICIPATION) {
                        $participationstr = get_string('participationbyuser', 'ouwiki');
                        $participationpage = 'participation.php?' .
                            ouwiki_display_wiki_parameters('', $subwiki, $cm, OUWIKI_PARAMS_URL);
                    } else if ($canview == OUWIKI_MY_PARTICIPATION) {
                        $participationstr = get_string('myparticipation', 'ouwiki');
                        $participationpage = 'userparticipation.php?' .
                            ouwiki_display_wiki_parameters('', $subwiki, $cm, OUWIKI_PARAMS_URL);
                            $participationpage .= '&user='.$USER->id;
                    }
    
                    if ($canview > OUWIKI_NO_PARTICIPATION) {
                        if (($cm->groupmode != 0) && isset($subwiki->groupid)) {
                            $participationpage .= '&group='.$subwiki->groupid;
                        }
                        if ($page == 'participation.php' || $page == 'userparticipation.php') {
                            $output .= html_writer::start_tag('li',
                                array('id' => 'ouwiki_nav_participation'));
                            $output .= html_writer::start_tag('span');
                            $output .= $participationstr;
                            $output .= html_writer::end_tag('span');
                            $output .= html_writer::end_tag('li');
                        } else {
                            $output .= html_writer::start_tag('li',
                                array('id' => 'ouwiki_nav_participation'));
                            $output .= html_writer::tag('a', $participationstr,
                                array('href' => $participationpage));
                            $output .= html_writer::end_tag('li');
                        }
                    }
    
                    $output .= html_writer::end_tag('ul');
    
                    $output .= html_writer::end_tag('div');
                } else {
                    $output .= html_writer::start_tag('div', array('id' => 'ouwiki_noindexlink'));
                    $output .= html_writer::end_tag('div');
                }
                if ($page == 'participation.php' || $page == 'userparticipation.php') {
                    $output .= $OUTPUT->heading($participationstr);
                }
    
                $output .= html_writer::start_tag('div', array('class' => 'clearer'));
                $output .= html_writer::end_tag('div');
                if ($notabs) {
                    $extraclass = $selector ? ' ouwiki_gotselector' : '';
                    $output .= html_writer::start_tag('div',
                        array('id' => 'ouwiki_belowtabs', 'class' => 'ouwiki_notabs'.$extraclass));
                    $output .= html_writer::end_tag('div');
                }
    
                return $output;
    }
    
    /**
     * Prints the subwiki selector if user has access to more than one subwiki.
     * Also displays the currently-viewing subwiki.
     *
     * @param object $subwiki Current subwiki object
     * @param object $ouwiki Wiki object
     * @param object $cm Course-module object
     * @param object $context Context for permissions
     * @param object $course Course object
     * @param string $actionurl
     * @param string $querytext for use when changing groups against search criteria
     */
    protected function ouwiki_display_subwiki_selector($subwiki, $ouwiki, $cm, $context, $course, $actionurl = 'view.php', $querytext = '') {
        global $USER, $DB, $OUTPUT;
    
        if ($ouwiki->subwikis == OUWIKI_SUBWIKIS_SINGLE) {
            return '';
        }
    
        $choicefield = '';
    
        switch($ouwiki->subwikis) {
            case OUWIKI_SUBWIKIS_GROUPS:
                $groups = groups_get_activity_allowed_groups($cm);
                uasort($groups, create_function('$a,$b', 'return strcasecmp($a->name,$b->name);'));
                $wikifor = htmlspecialchars($groups[$subwiki->groupid]->name);
    
                // Do they have more than one?
                if (count($groups) > 1) {
                    $choicefield = 'group';
                    $choices = $groups;
                }
                break;
    
            case OUWIKI_SUBWIKIS_INDIVIDUAL:
                $user = $DB->get_record('user', array('id' => $subwiki->userid),
                'username, ' . user_picture::fields());
                $wikifor = ouwiki_display_user($user, $cm->course);
                $usernamefields = user_picture::fields('u');
                if (has_capability('mod/ouwiki:viewallindividuals', $context)) {
                    // Get list of everybody...
                    $choicefield = 'user';
                    try {
                        $choices = $DB->get_records_sql('SELECT ' . $usernamefields .
                            ' FROM {ouwiki_subwikis} sw
                            INNER JOIN {user} u ON sw.userid = u.id
                            WHERE sw.wikiid = ?
                            ORDER BY u.lastname, u.firstname', array($ouwiki->id));
                    } catch (Exception $e) {
                        ouwiki_dberror($e);
                    }
    
                    foreach ($choices as $choice) {
                        $choice->name = fullname($choice);
                    }
    
                } else if (has_capability('mod/ouwiki:viewgroupindividuals', $context)) {
                    $choicefield = 'user';
                    $choices = array();
                    // User allowed to view people in same group
                    $theirgroups = groups_get_all_groups($cm->course, $USER->id,
                        $course->defaultgroupingid);
                    if (!$theirgroups) {
                        $theirgroups = array();
                    }
                    foreach ($theirgroups as $group) {
                        $members = groups_get_members($group->id, 'u.id, ' . $usernamefields);
                        foreach ($members as $member) {
                            $member->name = fullname($member);
                            $choices[$member->id] = $member;
                        }
                    }
                }
                break;
    
            default:
                ouwiki_error("Unexpected subwikis value: {$ouwiki->subwikis}");
        }
    
        $out = '<div class="ouw_subwiki">';
        if ($choicefield && count($choices) > 1) {
            $actionquery = '';
            if (!empty($querytext)) {
                $actionquery = '&amp;query=' . rawurlencode($querytext);
            }
            $actionurl = '/mod/ouwiki/'. $actionurl .'?id=' . $cm->id . $actionquery;
            $urlroot = new moodle_url($actionurl);
            if ($choicefield == 'user') {
                // Individuals.
                $individualsmenu = array();
                foreach ($choices as $choice) {
                    $individualsmenu[$choice->id] = $choice->name;
                }
                $select = new single_select($urlroot, 'user', $individualsmenu, $subwiki->userid, null, 'selectuser');
                $select->label = get_string('wikifor', 'ouwiki');
                $output = $OUTPUT->render($select);
                $out .= '<div class="individualselector">'.$output.'</div>';
            } else if ($choicefield == 'group') {
                // Group mode.
                $out .= groups_print_activity_menu($cm, $urlroot, true, true);
            }
        } else {
            $out .='<span>' . get_string('wikifor', 'ouwiki') ."</span> $wikifor";
        }
        $out .= '</div>';
    
        return $out;
    }
}