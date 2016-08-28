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
 * Definition of log events
 *
 *
 * @package    mod_moewiki
 * @copyright  2012 SysBind
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB;

$logs = array(
    array('module' => 'moewiki', 'action' => 'add', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'annotate', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'diff', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'edit', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'entirewiki', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'history', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'lock', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'participation', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'revert', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'search', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'unlock', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'update', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'userparticipation', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'versiondelete', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'versionundelete', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'view', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'view all', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'viewold', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'wikihistory', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'wikiindex', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'page created', 'mtable' => 'moewiki', 'field' => 'name'),
    array('module' => 'moewiki', 'action' => 'page updated', 'mtable' => 'moewiki', 'field' => 'name')
);