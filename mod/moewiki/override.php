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

/**
 * Handles what happens when a user with appropriate permission attempts to
 * override a wiki page editing lock.
 *
 * @copyright &copy; 2007 SysBind
 * @author avi@sysbind.co.il
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moewiki
 */

require_once(dirname(__FILE__) . '/../../config.php');
require($CFG->dirroot.'/mod/moewiki/basicpage.php');

if (!data_submitted()) {
    print_error("Only POST requests accepted");
}

if (!has_capability('mod/moewiki:overridelock', $context)) {
    print_error("You do not have the capability to override editing locks");
}

$pageversion = moewiki_get_current_page($subwiki, $pagename, MOEWIKI_GETPAGE_ACCEPTNOVERSION);
moewiki_override_lock($pageversion->pageid);

$redirpage = optional_param('redirpage', '', PARAM_ALPHA);

if ($redirpage != '') {
    redirect($redirpage.'.php?'.moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_URL), '', 0);
} else {
    redirect('edit.php?'.moewiki_display_wiki_parameters($pagename, $subwiki, $cm, MOEWIKI_PARAMS_URL), '', 0);
}
