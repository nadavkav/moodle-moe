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
 *
 * @param int $pagid
 * @throws moodle_exception
 *
 * return course_module object
 */
function moewiki_get_cm($pageid) {
    global $DB;
    if (is_number($pageid)) {
        $page = $DB->get_record('moewiki_pages', array('id' => (int)$pageid));
        $subwiki = $DB->get_record('moewiki_subwikis', array('id' => $page->subwikiid));
        $moewiki = $DB->get_record('moewiki', array('id' => $subwiki->wikiid));
        list($course, $cm) = get_course_and_cm_from_instance($moewiki->id, 'moewiki', $moewiki->course);
        return $cm;
    } else {
        throw new moodle_exception('invalid param');
    }
}