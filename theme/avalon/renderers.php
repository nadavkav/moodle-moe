<?php

class theme_avalon_core_renderer extends core_renderer {
    //public function pix_url($imagename, $component = 'moodle') {
        //if($this->page->theme>name == 'avalon')
        //{
            //$this->page->theme->dir = 'C:\\wamp\\www\\moodle2\\theme\\avalon_summer';
            //$url = $this->page->theme->pix_url($imagename, $component);

            //$this->page->theme->dir = 'C:\\wamp\\www\\moodle2\\theme\\avalon';
            //$url->params['']
            //return $url;
        //}
        //else
        //{
        //    return $this->page->theme->pix_url($imagename, $component);
        //}
    //}

    /**
     * Renders a custom menu object (located in outputcomponents.php)
     *
     * The custom menu this method override the render_custom_menu function
     * in outputrenderers.php
     * @staticvar int $menucount
     * @param custom_menu $menu
     * @return string
     */
   /* protected function render_custom_menu(custom_menu $menu) {

        if (!right_to_left()) { // Keep YUI3 navmenu for LTR UI
            parent::render_custom_menu($menu);
        }

        // If the menu has no children return an empty string
        if (!$menu->has_children()) {
            return '';
        }

        // Add a login or logout link
        if (isloggedin()) {
            $branchlabel = get_string('logout');
            $branchurl   = new moodle_url('/login/logout.php');
        } else {
            $branchlabel = get_string('login');
            $branchurl   = new moodle_url('/login/index.php');
        }
        $branch = $menu->add($branchlabel, $branchurl, $branchlabel, -1);

        // Initialise this custom menu
        $content = html_writer::start_tag('ul', array('class'=>'dropdown dropdown-horizontal'));
        // Render each child
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item);
        }
        // Close the open tags
        $content .= html_writer::end_tag('ul');
        // Return the custom menu
        return $content;
    }*/

    /**
     * Renders a custom menu node as part of a submenu
     *
     * The custom menu this method override the render_custom_menu_item function
     * in outputrenderers.php
     *
     * @see render_custom_menu()
     *
     * @staticvar int $submenucount
     * @param custom_menu_item $menunode
     * @return string
     */
  /* protected function render_custom_menu_item(custom_menu_item $menunode) {

        if (!right_to_left()) { // Keep YUI3 navmenu for LTR UI
            parent::render_custom_menu_item($menunode);
        }

        // Required to ensure we get unique trackable id's
        static $submenucount = 0;
        $content = html_writer::start_tag('li');
        if ($menunode->has_children()) {
            // If the child has menus render it as a sub menu
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $content .= html_writer::start_tag('span', array('class'=>'customitem'));
            $content .= html_writer::link($url, $menunode->get_text(), array('title'=>$menunode->get_title()));
            $content .= html_writer::end_tag('span');
            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode);
            }
            $content .= html_writer::end_tag('ul');
        } else {
            // The node doesn't have children so produce a final menuitem

            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('title'=>$menunode->get_title()));
        }
        $content .= html_writer::end_tag('li');
        // Return the sub menu
        return $content;
    }      */

    /***
     *  This renderers a custom menu in the page footer
     * @param string The custom menu string. If emtpy loads this from theme's config
     * @return string  HTML rendered menu
     */
    public function footer_custom_menu($custommenuitems = ''){

        global $PAGE;

        //Get custom menu string
        if (empty($custommenuitems) && !empty($PAGE->theme->settings->collegefooter)) {
            //Get menu string from config
            $custommenuitems = $PAGE->theme->settings->collegefooter;
        }
        else if (!empty($custommenuitems)){
            return '';
        }

        //Parse string into custom_menu object
        $custommenu = new custom_menu($custommenuitems, current_language());

        //Render HTML
        $content = $this->render_footer_custom_menu($custommenu);

        return $content;
    }

    protected function render_footer_custom_menu($menu){

        // If the menu has no children return an empty string
        if (!$menu->has_children()) {
            return '';
        }

        $content = '';

        // Render each child
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_footer_custom_menu_item($item);
        }

        // Return the custom menu
        return $content;
    }

    protected function render_footer_custom_menu_item(custom_menu_item $menunode){

        //Render column start tag
        $content = html_writer::start_tag('div',array('class' => 'section'));

        //Render column  headline
        $content .= html_writer::start_tag('h3');
        if ($menunode->get_url() !== null) {
            $content .= html_writer::link($menunode->get_url(), $menunode->get_text(), array('title'=>$menunode->get_title(), 'target' => '_blank'));
        }
        else {
            $content .=  $menunode->get_text();
        }

        //Render column headline end tag
        $content .= html_writer::end_tag('h3');

        //Render columns children
        foreach ($menunode->get_children() as $subnode){

            //For each child render a link (or an empty link)
            if ($subnode->get_url() !== null) {
                $content .= html_writer::link($subnode->get_url(), $subnode->get_text(), array('title'=>$subnode->get_title(), 'target' => '_blank'));
            }
            else {
                $content .= html_writer::link('', $subnode->get_text(), array('title'=>$subnode->get_title()));
            }

        }

        //Render column end tag
        $content .=  html_writer::end_tag('div');

        return $content;

    }

    protected function render_course_teachers()
    {
        global $CFG, $COURSE, $DB, $OUTPUT;

        $context = context_course::instance($COURSE->id);
        /// first find all roles that are supposed to be displayed
        if (!empty($CFG->coursecontact)) {
            $managerroles = explode(',', $CFG->coursecontact);
            $namesarray = array();
            $rusers = array();

            if (!isset($COURSE->managers)) {
                $rusers = get_role_users($managerroles, $context, true,
                    'ra.id AS raid, u.id, u.username, u.firstname, u.lastname, u.firstnamephonetic,
                     u.lastnamephonetic, u.middlename, u.alternatename ,u.aim,
                     r.name AS rolename, r.sortorder, r.id AS roleid',
                    'r.sortorder ASC, u.lastname ASC');
            } else {
                //  use the managers array if we have it for performance reasons
                //  populate the datastructure like output of get_role_users();
                foreach ($COURSE->managers as $manager) {
                    $u = new stdClass();
                    $u = $manager->user;
                    $u->roleid = $manager->roleid;
                    $u->rolename = $manager->rolename;

                    $rusers[] = $u;
                }
            }

            /// Rename some of the role names if needed
            if (isset($context)) {
                $aliasnames = $DB->get_records('role_names', array('contextid' => $context->id), '', 'roleid,contextid,name');
            }

            $namesarray = array();
            $namestitlearray = array();
            $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);
            foreach ($rusers as $ra) {
                if (isset($namesarray[$ra->id])) {
                    //  only display a user once with the highest sortorder role
                    continue;
                }

                if (isset($aliasnames[$ra->roleid])) {
                    $ra->rolename = $aliasnames[$ra->roleid]->name;
                }

                $fullname = '';
                if (!empty($ra->aim)) {
                    $fullname .= $ra->aim.' ';
                }
                $fullname .= fullname($ra, $canviewfullnames);
                $namesarray[$ra->id] = html_writer::link(new moodle_url('/user/view.php', array('id' => $ra->id, 'course' => $COURSE->id)), $fullname);
                $namestitlearray[$ra->id] =  $fullname;
            }

            if (!empty($namesarray)) {
                if (count($namesarray) > 1) {
                    $teacherslabel = '<a class="teachers_all" title="' . get_string('teachers_all', 'theme_avalon') . '" href="'
                        . new moodle_url('/user/index.php', array('contextid' => $context->id, 'roleid' => 3, 'mode' => 1)) . '">'
                        . get_string('teachers', 'theme_avalon') . '(' . count($namesarray)
                        . '):<img class="teachers_list_icon" src="' . new moodle_url('/theme/avalon/pix/teachers_list_icon.png') . '" /></a>';
                } else {
                    $teacherslabel = get_string('teacher', 'theme_avalon') . ': ';
                }
//            echo html_writer::start_tag('ul', array('class'=>'teachers'));
//            foreach ($namesarray as $name) {
//                echo html_writer::tag('li', $name);
//            }
//            echo html_writer::end_tag('ul');
                $teachertitlelist = '';
                foreach ($namestitlearray as $name) {
                    $teachertitlelist .= "$name, ";
                }
                echo html_writer::start_tag('div', array('class' => 'teachers', 'title' => rtrim($teachertitlelist, ', ')));
                $teacherlist = $teacherslabel;
                foreach ($namesarray as $name) {
                    $teacherlist .= "$name, ";
                }
                echo rtrim($teacherlist, ', ');
                echo html_writer::end_tag('div');
            }
        }
    }

    /**
     *  This appends the subtheme component to the imagename and returns the result of $OUTPUT->pix_url
     * @param $imagename
     * @param $component
     * @param $subtheme
     * @return moodle_url
     */
    protected function avalon_pix_url($imagename, $component, $subtheme) {
        global $OUTPUT;
        return $OUTPUT->pix_url($subtheme.'/'.$imagename, $component);

    }

//    protected function render_user_picture(user_picture $userpicture) {
//        //$output = html_writer::start_tag('span',array('style'=>'border:2px solid red;'));
//        $output = html_writer::start_tag('span',array('id'=>'imgrole'));
//        $output .= parent::render_user_picture($userpicture);
//        //$output .= html_writer::end_tag('span');
//        $output .= html_writer::tag('img','',array('id'=>'roleimg',
//            'src'=>new moodle_url('/theme/avalon/pix/teachers_list_icon.png'),
//            'style'=>'position: relative;display: block;top: 27px;right: 110px;') );
//        $output .= html_writer::end_tag('span');
//        return $output;
//    }


    // TODO: finish user picture renderer (ask emil for a "teacher" image)

    protected function render_user_picture(user_picture $userpicture) {
        global $CFG, $DB, $PAGE, $USER;

        $user = $userpicture->user;

        if ($userpicture->alttext) {
            if (!empty($user->imagealt)) {
                $alt = $user->imagealt;
            } else {
                $alt = get_string('pictureof', '', fullname($user));
            }
        } else {
            $alt = '';
        }

        if (empty($userpicture->size)) {
            $size = 35;
        } else if ($userpicture->size === true or $userpicture->size == 1) {
            $size = 100;
        } else {
            $size = $userpicture->size;
        }

        $class = $userpicture->class;

        if ($user->picture == 0) {
            $class .= ' defaultuserpic';
        }

        if (user_has_role_assignment($user->id, 3 /* editingteacher */, $PAGE->context->id)) {
            $class .= ' teacher';
        }

        $src = $userpicture->get_url($this->page, $this);

        $attributes = array('src' => $src, 'alt' => $alt, 'title' => $alt, 'class' => $class, 'width' => $size, 'height' => $size);

        if (empty($userpicture->courseid)) {
            $courseid = $this->page->course->id;
        } else {
            $courseid = $userpicture->courseid;
        }

        // get the image html output fisrt
        $output = html_writer::start_tag('div', array('class'=>'profilepicture'));
        if (user_has_role_assignment($USER->id, 3 /* editingteacher */, $PAGE->context->id)
            OR user_has_role_assignment($USER->id, 1 /* manager */, $PAGE->context->id)
            OR array_key_exists($USER->id,get_admins()) ) {
            if ($userpicture->popup != 'off' and $size >= 35) {
                $output .= $this->user_action_menu($user->id, $courseid, $attributes);
            }
        } else {
            $output .= html_writer::empty_tag('img', $attributes);
        }

        $output .= html_writer::end_tag('div');

        /*
        if (user_has_role_assignment($user->id,3,$PAGE->context->id)) {
            $output .= html_writer::start_tag('div',array('style'=>'position: relative;top: -20px;right: 20px;'));
            $output .= html_writer::empty_tag('img', array('id'=>'roleimg',
                'src'=>new moodle_url('/theme/avalon/pix_core/i/grademark.png')) );
            $output .= html_writer::end_tag('div');
        }
        */

        // then wrap it in link if needed
        if (!$userpicture->link) {
            return $output;
        }

        if ($courseid == SITEID) {
            $url = new moodle_url('/user/profile.php', array('id' => $user->id));
        } else {
            $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
        }

        $attributes = array('href' => $url);

        /* Disabled. Now it is used for User's Action menu.
        if ($userpicture->popup) {

            $id = html_writer::random_id('userpicture');
            $attributes['id'] = $id;
            $this->add_action_handler(new popup_action('click', $url), $id);

        }
        */
        return html_writer::tag('a', $output, $attributes);

        //return $output;
        //return html_writer::tag('div', $output, array('onclick'=>'alert("hello")'));
    }

    function user_action_menu($userid, $courseid = SITEID, $attributes ) {

        global $USER, $CFG, $DB;

        $edit = '';
        $actions = array();

        // Action URLs

        // View user's profile
        if ($courseid == SITEID) {
            $url = new moodle_url('/user/profile.php', array('id' => $userid));
        } else {
            $url = new moodle_url('/user/view.php', array('id' => $userid, 'course' => $courseid));
        }
        $actions[$url->out(false)] = get_string('user_viewprofile','theme_avalon');

        // View user's complete report
        $url = new moodle_url('/report/outline/user.php',
            array('id' => $userid, 'course'=>$courseid, 'mode'=>'complete'));
        $actions[$url->out(false)] = get_string('user_completereport','theme_avalon');

        // View user's outline report
        $url = new moodle_url('/report/outline/user.php',
            array('id' => $userid, 'course'=>$courseid, 'mode'=>'outline'));
        $actions[$url->out(false)] = get_string('user_outlinereport','theme_avalon');

        // Edit user's profile
        $url = new moodle_url('/user/editadvanced.php', array('id' => $userid, 'course'=>$courseid));
        $actions[$url->out(false)] = get_string('user_editprofile','theme_avalon');

        // Send private message
        if ($USER->id != $userid) {
            $url = new moodle_url('/message/index.php', array('id'=>$userid));
            $actions[$url->out(false)] = get_string('user_sendmessage','theme_avalon');
        }

        // Completion enabled in course? Display user's link to completion report.
        $coursecompletion = $DB->get_field('course', 'enablecompletion', array('id' => $courseid));
        if (!empty($CFG->enablecompletion) AND $coursecompletion) {
            $url = new moodle_url('/blocks/completionstatus/details.php', array('user' => $userid, 'course'=>$courseid));
            $actions[$url->out(false)] = get_string('user_coursecompletion','theme_avalon');
        }

        // All user's mdl_log HITS
        $url = new moodle_url('/report/log/user.php', array('id' => $userid, 'course'=>$courseid, 'mode'=>'all'));
        $actions[$url->out(false)] = get_string('user_courselogs','theme_avalon');

        // User's grades in course ID
        $url = new moodle_url('/grade/report/user/index.php', array('userid' => $userid, 'id'=>$courseid));
        $actions[$url->out(false)] = get_string('user_coursegrades','theme_avalon');

        // Login as ...
        $coursecontext = context_course::instance($courseid);
        if ($USER->id != $userid && !\core\session\manager::is_loggedinas() && has_capability('moodle/user:loginas', $coursecontext) && !is_siteadmin($userid)) {
            $url = new moodle_url('/course/loginas.php', array('id'=>$courseid, 'user'=>$userid, 'sesskey'=>sesskey()));
            $actions[$url->out(false)] = get_string('user_loginas','theme_avalon');
        }


        // Setup the menu
        $edit .= $this->container_start(array('yui3-menu', 'yui3-menubuttonnav', 'useractionmenu'), 'useractionselect' . $userid);
        $edit .= $this->container_start(array('yui3-menu-content'));
        $edit .= html_writer::start_tag('ul');
        $edit .= html_writer::start_tag('li', array('class'=>'menuicon'));

        //$menuicon = $this->pix_icon('t/contextmenu', get_string('actions'));
        //$menuicon = $this->pix_icon('t/switch_minus', get_string('actions'));
        $menuicon = html_writer::empty_tag('img', $attributes); //$attributes['src'];
        $edit .= $this->action_link('#menu' . $userid, $menuicon, null, array('class'=>'yui3-menu-label'));
        $edit .= $this->container_start(array('yui3-menu', 'yui3-loading'), 'menu' . $userid);
        $edit .= $this->container_start(array('yui3-menu-content'));
        $edit .= html_writer::start_tag('ul');

        foreach ($actions as $url => $description) {
            $edit .= html_writer::start_tag('li', array('class'=>'yui3-menuitem'));
            $edit .= $this->action_link($url, $description, null, array('class'=>'yui3-menuitem-content', 'target'=>'_new'));
            //$edit .= $this->add_action_handler(new popup_action('click', $url), array('id'=>html_writer::random_id('userpicture')));
            $edit .= html_writer::end_tag('li');
        }
        $edit .= html_writer::end_tag('ul');
        $edit .= $this->container_end();
        $edit .= $this->container_end();
        $edit .= html_writer::end_tag('li');
        $edit .= html_writer::end_tag('ul');

        $edit .= $this->container_end();
        $edit .= $this->container_end();

        return $edit;
    }
}

if (file_exists($CFG->dirroot . "/course/renderer.php") AND $CFG->version >= 2013051402 ) {

    include_once($CFG->dirroot . "/course/renderer.php");

    class theme_avalon_core_course_renderer extends core_course_renderer {

        public function frontpage_my_courses() {
            //echo parent::frontpage_my_courses();

            global $USER, $CFG, $DB;

            if (!isloggedin() or isguestuser()) {
                return '';
            }

            list($categories, $childrencats, $roles, $filterbycategory, $filterbyrole, $filterbysemester, $html) = filter_courses_form();
            echo $html;

            $output = '';
            if (!empty($CFG->navsortmycoursessort)) {
                // sort courses the same as in navigation menu
                $sortorder = 'visible DESC,'. $CFG->navsortmycoursessort.' ASC';
            } else {
                $sortorder = 'visible DESC,sortorder ASC';
            }
            $courses  = enrol_get_my_courses('summary, summaryformat', $sortorder);

            // Commented out by Nitzan Bar 14-09-2014.
            // If a user hasn't access a course yet it won't show up on the course list.
            // TODO: Need to add unaccessed courses to the $sortedcourses list (Need to work on performance as well)
            //if (isset($CFG->sortcoursesbylastaccess)) {
            //    $lastaccesscourselist = $DB->get_records('user_lastaccess', array('userid'=>$USER->id), 'timeaccess DESC');
            //    $sortedcourses = array();
            //    foreach ($lastaccesscourselist as $lastaccesscourse) {
            //        foreach ($courses as $course) {
            //            if ($lastaccesscourse->courseid == $course->id) {
            //                $sortedcourses[$course->id] = $course;
            //                $sortedcourses[$course->id]->lastaccess = $lastaccesscourse->timeaccess;
            //            }
            //        }
            //    }
            //    $courses = $sortedcourses; // overwrite $courses with $sortedcourses by lastaccess
            //}

            // Initiate semester list.
            $semesterlist = array('-1'=>get_string('all'));
            foreach (explode(',',get_string('semesterlist','theme_avalon')) as $semester) {
                $semesterlist[] = $semester;
            }

            $rhosts   = array();
            $rcourses = array();
            if (!empty($CFG->mnet_dispatcher_mode) && $CFG->mnet_dispatcher_mode==='strict') {
                $rcourses = get_my_remotecourses($USER->id);
                $rhosts   = get_my_remotehosts();
            }

            if (!empty($courses) || !empty($rcourses) || !empty($rhosts)) {

                // Remove courses which are not chosen by Category or Role
                foreach ($courses as $key => $course) {
                    $course->context = context_course::instance($course->id, MUST_EXIST);
                    if ($filterbyrole > 0 && !user_has_role_assignment($USER->id, $filterbyrole, $course->context->id)){
                        //continue;
                        unset($courses[$key]);
                    }
                    if ($filterbycategory > 0) {
                        if (isset($CFG->showonlytopcategories)) {  //Show courses from his category and all children categories
                            if (!array_key_exists($course->category, $childrencats) && $course->category != $filterbycategory) {
                                //continue;   //Course id not in category or in child category
                                unset($courses[$key]);
                            }
                        } else {   //Show only courses in THIS category
                            if ($course->category != $filterbycategory) {
                                //continue;
                                unset($courses[$key]);
                            }
                        }
                    }
                    if ($filterbysemester >= 0 and mb_strpos($course->fullname, $semesterlist[$filterbysemester]) === false) {
                        unset($courses[$key]);

                    }
                }

                $chelper = new coursecat_helper();
                if (count($courses) > $CFG->frontpagecourselimit) {
                    // There are more enrolled courses than we can display, display link to 'My courses'.
                    $totalcount = count($courses);
                    $courses = array_slice($courses, 0, $CFG->frontpagecourselimit, true);
                    $chelper->set_courses_display_options(array(
                        'viewmoreurl' => new moodle_url('/my/'),
                        'viewmoretext' => new lang_string('mycourses')
                    ));
                } else {
                    // All enrolled courses are displayed, display link to 'All courses' if there are more courses in system.
                    $chelper->set_courses_display_options(array(
                        'viewmoreurl' => new moodle_url('/course/index.php'),
                        'viewmoretext' => new lang_string('fulllistofcourses')
                    ));
                    $totalcount = $DB->count_records('course') - 1;
                }
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->
                    set_attributes(array('class' => 'frontpage-course-list-enrolled'));
                $output .= $this->coursecat_courses($chelper, $courses, $totalcount);

                // MNET
                if (!empty($rcourses)) {
                    // at the IDP, we know of all the remote courses
                    $output .= html_writer::start_tag('div', array('class' => 'courses'));
                    foreach ($rcourses as $course) {
                        $output .= $this->frontpage_remote_course($course);
                    }
                    $output .= html_writer::end_tag('div'); // .courses
                } elseif (!empty($rhosts)) {
                    // non-IDP, we know of all the remote servers, but not courses
                    $output .= html_writer::start_tag('div', array('class' => 'courses'));
                    foreach ($rhosts as $host) {
                        $output .= $this->frontpage_remote_host($host);
                    }
                    $output .= html_writer::end_tag('div'); // .courses
                }
            }
            return $output;
        }
    }
}

if (file_exists($CFG->dirroot . "/user/renderer.php") AND $CFG->version >= 2013051402 ) {

    include_once($CFG->dirroot . "/user/renderer.php");

    class theme_avalon_core_user_renderer extends core_user_renderer {

        public function courselist($user) {

            global $CFG;

            list($categories, $childrencats, $roles, $filterbycategory, $filterbyrole, $filterbysemester, $html) = filter_courses_form();
            echo $html;

            if ($mycourses = enrol_get_all_users_courses($user->id, true, NULL, 'visible DESC,sortorder ASC')) {
                $shown=0;
                $courselisting = '';

                // Remove courses which are not chosen by Category or Role
                // TODO: might be a good idea to merge this loop with the following, original, $mycourses loop
                foreach ($mycourses as $key => $course) {
                    $course->context = context_course::instance($course->id, MUST_EXIST);
                    if ($filterbyrole > 0 && !user_has_role_assignment($user->id, $filterbyrole, $course->context->id)){
                        //continue;
                        unset($mycourses[$key]);
                    }
                    if ($filterbycategory > 0){
                        if (isset($CFG->showonlytopcategories)) {  //Show courses from his category and all children categories
                            if (!array_key_exists($course->category, $childrencats) && $course->category != $filterbycategory) {
                                //continue;   //Course id not in category or in child category
                                unset($mycourses[$key]);
                            }
                        } else {   //Show only courses in THIS category
                            if ($course->category != $filterbycategory) {
                                //continue;
                                unset($mycourses[$key]);
                            }
                        }
                    }
                }

                foreach ($mycourses as $mycourse) {
                    if ($mycourse->category) {
                        context_helper::preload_from_record($mycourse);
                        $ccontext = context_course::instance($mycourse->id);
                        $class = '';
                        if ($mycourse->visible == 0) {
                            if (!has_capability('moodle/course:viewhiddencourses', $ccontext)) {
                                continue;
                            }
                            $class = 'class="dimmed"';
                        }
                        $courselisting .= "<a href=\"{$CFG->wwwroot}/user/view.php?id={$user->id}&amp;course={$mycourse->id}\" $class >" . $ccontext->get_context_name(false) . "</a><br/>";
                    }
                    $shown++;
                    if($shown==$CFG->frontpagecourselimit) { // was fix for 20
                        $courselisting.= "...";
                        break;
                    }
                }
                echo html_writer::tag('dt', get_string('courseprofiles'));
                echo html_writer::tag('dd', rtrim($courselisting,', '));
            }
        }

    //    public function custom_actions($user) {
    //
    //    }

    }

    /*  Filter courses by categories and roles form function
     *  Used in:
     *      theme_avalon_core_user_renderer::courselist()
     *      theme_avalon_core_course_renderer::frontpage_my_courses()
    */
    function filter_courses_form() {
        global $DB, $CFG;

        $html = '';

        $filterbycategory = optional_param('filterByCategory', $CFG->defaultcoursecategroy, PARAM_INT);
        $filterbyrole = optional_param('filterByRole', -1, PARAM_INT);
        $filterbysemester = optional_param('filterBySemester', -1, PARAM_INT);

        $semesterlist = array('-1'=>get_string('all'));
        foreach (explode(',',get_string('semesterlist','theme_avalon')) as $semester) {
            $semesterlist[] = $semester;
        }

        if (isset($CFG->showonlytopcategories) && $CFG->showonlytopcategories) {
            $showonlytopcategories = true;
        } else{
            $showonlytopcategories = false;
        }

        require_once($CFG->libdir . '/coursecatlib.php');

        $childrencats = array();
        $categories['-1'] =  get_string('showallcourses', 'theme_avalon');    //Add all courses option (No filter)
        if ($showonlytopcategories) {  //Show only top categories

            foreach (coursecat::get(0)->get_children() as $category) {
                $categories[$category->id] = $category->name;
            }

            if ($filterbycategory > 0){ //If filter is set get a list of child categories
                foreach (coursecat::get($filterbycategory)->get_children() as $category) {
                    $childrencats[$category->id] = $category->name;
                }
            }
        } else {  //Show all categories
            $fullcategories = coursecat::make_categories_list();
            $categories = array_merge($categories, $fullcategories);
        }

        $rolestudent = $DB->get_record('role', array('shortname'=>'student'));
        $roleteacher = $DB->get_record('role', array('shortname'=>'editingteacher'));

        $roles = array();
        $roles['-1'] =  get_string('anyrole', 'theme_avalon');    //Add all courses option (No filter)
        $roles[$rolestudent->id] = $rolestudent->name;
        $roles[$roleteacher->id] = $roleteacher->name;

        $html .= html_writer::start_tag('form',array('id'=>'frmFilters', 'action'=>'', 'method'=>'post'));
        $html .= html_writer::start_tag('div',array('style'=>'width:90%;margin-left:auto;margin-right:auto;padding: 20px 0;'));
        $html .= html_writer::start_tag('h4');

        $html .= get_string('filterby', 'theme_avalon');
        $html .= get_string('filterbycategory', 'theme_avalon');
        $html .= html_writer::select($categories, 'filterByCategory', $filterbycategory, '',
            array(  'onchange' => 'this.form.submit()',
                'style'=>'margin-left:auto;margin-right:auto;'));
        $html .= '&nbsp;&nbsp;';

        $html .= get_string('filterbyrole', 'theme_avalon');
        $html .= html_writer::select($roles, 'filterByRole', $filterbyrole, get_string('choose'),
            array('onchange' => 'this.form.submit()'));

        $html .= get_string('filterbysemester', 'theme_avalon');
        $html .= html_writer::select($semesterlist, 'filterBySemester', $filterbysemester, get_string('choose'),
            array('onchange' => 'this.form.submit()'));

        $html .= html_writer::start_tag('h4');
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('form');

        return array($categories, $childrencats, $roles, $filterbycategory, $filterbyrole, $filterbysemester, $html);

    }

}

if (file_exists($CFG->dirroot . "/blocks/course_overview/renderer.php") AND $CFG->version >= 2013051402 ) {
    include_once($CFG->dirroot . "/blocks/course_overview/renderer.php");

    class theme_avalon_block_course_overview_renderer extends block_course_overview_renderer {

        public function course_overview($courses, $overviews) {
            global $CFG, $USER;

            list($categories, $childrencats, $roles, $filterbycategory, $filterbyrole, $filterbysemester, $html) = filter_courses_form();

            // Initiate semester list.
            $semesterlist = array('-1'=>get_string('all'));
            foreach (explode(',',get_string('semesterlist','theme_avalon')) as $semester) {
                $semesterlist[] = $semester;
            }

            // Remove courses which are not chosen by Category / Role / Semester
            foreach ($courses as $key => $course) {
                $course->context = context_course::instance($course->id, MUST_EXIST);
                if ($filterbyrole > 0 && !user_has_role_assignment($USER->id, $filterbyrole, $course->context->id)){
                    //continue;
                    unset($courses[$key]);
                }
                if ($filterbycategory > 0) {
                    if (isset($CFG->showonlytopcategories)) {  //Show courses from his category and all children categories
                        if (!array_key_exists($course->category, $childrencats) && $course->category != $filterbycategory) {
                            //continue;   //Course id not in category or in child category
                            unset($courses[$key]);
                        }
                    } else {   //Show only courses in THIS category
                        if ($course->category != $filterbycategory) {
                            //continue;
                            unset($courses[$key]);
                        }
                    }
                }
                if ($filterbysemester >= 0 and mb_strpos($course->fullname, $semesterlist[$filterbysemester]) === false) {
                    unset($courses[$key]);

                }
            }

            return $html . parent::course_overview($courses, $overviews);


        }
    }
}
