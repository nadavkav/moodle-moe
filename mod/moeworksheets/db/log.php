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
 * Definition of log events for the moeworksheets module.
 *
 * @package    mod_moeworksheets
 * @category   log
 * @copyright  2010 sysBind (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module'=>'moeworksheets', 'action'=>'add', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'update', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'view', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'report', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'attempt', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'submit', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'review', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'editquestions', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'preview', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'start attempt', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'close attempt', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'continue attempt', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'edit override', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'delete override', 'mtable'=>'moeworksheets', 'field'=>'name'),
    array('module'=>'moeworksheets', 'action'=>'view summary', 'mtable'=>'moeworksheets', 'field'=>'name'),
);