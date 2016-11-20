<?php
use assignfeedback_editpdf\annotation;

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * External moewiki API
 *
 * @package mod_moewiki
 * @since Moodle 3.1
 * @copyright 2016 SysBind
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ("$CFG->libdir/externallib.php");
require_once ("$CFG->dirroot/mod/moewiki/locallib.php");

class mod_moewiki_external extends external_api
{

    public static function create_parameters()
    {
        return new external_function_parameters(array(
            'ranges' => new external_multiple_structure(new external_single_structure(array(
                'start' => new external_value(PARAM_TEXT, "The annotaion start element"),
                'end' => new external_value(PARAM_TEXT, "The annotation end element"),
                'startOffset' => new external_value(PARAM_INT, "The start offset position of the annotaion in the element"),
                'endOffset' => new external_value(PARAM_INT, "The end offset position of the annotaion in the element")
            ))),
            'quote' => new external_value(PARAM_TEXT),
            'page' => new external_value(PARAM_INT),
            'permissions' => new external_single_structure(array(
                    'read' => new external_single_structure(array(
                        new external_value(PARAM_TEXT,'',false, 'null',true)
                    ),'',false,array()),
                    'delete' => new external_single_structure(array(
                        new external_value(PARAM_TEXT,'',false, 'null',true)
                    ),'',false,array()),
                    'admin' => new external_single_structure(array(
                        new external_value(PARAM_TEXT,'',false, 'null',true)
                    ),'',false,array()),
                    'update' => new external_single_structure(array(
                        new external_value(PARAM_TEXT,'',false, 'null',true)
                    ),'',false,array()),
                ), "annotaion permissino", false, array()),
            'text' => new external_value(PARAM_TEXT),
            'userpage' => new external_value(PARAM_INT),
            'parent' => new external_value(PARAM_INT,'parent annotation',false, null, true)
        ));
    }

    public static function create($ranges, $quote, $page, $permissions, $text, $userpage,$parent = null)
    {
        global $DB, $USER, $PAGE;
        
        $annotation = new stdClass();
        $annotation->pageid = $page;
        $annotation->userid = $USER->id;
        $annotation->created = time();
        $annotation->quote = $quote;
        $annotation->text = $text;
        $annotation->updated = $annotation->created;
        $annotation->userpage = $userpage;
        $annotation->resolved = 0;
        $annotation->parent = $parent;
        $annotation->id = $DB->insert_record('moewiki_annotations', $annotation);
        
        foreach ($ranges as $range) {
            $rangeobj = new stdClass();
            $rangeobj->start = $range['start'];
            $rangeobj->end = $range['end'];
            $rangeobj->startoffset = $range['startOffset'];
            $rangeobj->endoffset = $range['endOffset'];
            $rangeobj->annotationid = $annotation->id;
            $DB->insert_record('moewiki_annotations_ranges', $rangeobj);
        }
        
        $permission = new stdClass();
        $permission->annotationid = (int)$annotation->id;
        $permission->read_prem = ($permissions['read'][0] == 'null') ? null : (int)$permissions['read'][0];
        $permission->delete_prem = ($permissions['delete'][0] == 'null') ? $USER->id : (int)$permissions['delete'][0];
        $permission->admin_prem = ($permissions['admin'][0] == 'null') ? $USER->id : (int)$permissions['admin'][0];
        $permission->update_prem = ($permissions['update'][0] == 'null') ? null: (int)$permissions['update'][0];
        if($DB->insert_record('moewiki_annotations_permiss', $permission)) {
            $annotation->permissions = new stdClass();
            $annotation->permissions->read = array($permission->read_prem);
            $annotation->permissions->delete = array($permission->delete_prem);
            $annotation->permissions->admin = array($permission->admin_prem);
            $annotation->permissions->update = array($permission->update_prem);
        }
        $annotation->ranges = $ranges;
        $user = new stdClass();
        $user->id = $annotation->userid;
        $user = $DB->get_record('user', array('id' => $user->id));
        $userpicture = new user_picture($user);
        $cm = moewiki_get_cm($page);
        $PAGE->set_cm($cm);
        $annotation->username =  $user->firstname . ' ' . $user->lastname;
        $annotation->userpicture = $userpicture->get_url($PAGE)->out();
        return $annotation;
    }

    public static function create_returns()
    {
        return new external_single_structure(array(
            'id'      => new external_value(PARAM_INT),
            'created' => new external_value(PARAM_TEXT),
            'quote'   => new external_value(PARAM_TEXT),
            'text'    => new external_value(PARAM_TEXT),
            'updated' => new external_value(PARAM_TEXT),
            'ranges'  => new external_multiple_structure(new external_single_structure(array(
                'start'       => new external_value(PARAM_TEXT, "The annotaion start element"),
                'end'         => new external_value(PARAM_TEXT, "The annotation end element"),
                'startOffset' => new external_value(PARAM_INT, "The start offset position of the annotaion in the element"),
                'endOffset'   => new external_value(PARAM_INT, "The end offset position of the annotaion in the element")
            ))),
            'permissions' => new external_single_structure(array(
                'read' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'delete' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'admin' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'update' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
            ), "annotaion permissino", false, array()),
            'userid' => new external_value(PARAM_INT),
            'parent' => new external_value(PARAM_INT,'annotation parent',false, null, true),
            'userpicture' => new external_value(PARAM_URL),
            'username' => new external_value(PARAM_TEXT),
        ));
    }

    public static function search_parameters()
    {
        return new external_function_parameters(array(
            'wikiid' => new external_value(PARAM_INT, "The wiki ID"),
        ));
    }

    public static function search($wikiid)
    {
        global $DB, $PAGE, $USER;
        
        $annotations = $DB->get_records('moewiki_annotations', array(
            'pageid' => $wikiid,
            'resolved' => 0,
        ));
        $annotationsreturn = array();
        $total=0;
        foreach ($annotations as $key => $annotation) {
            $permissions = $DB->get_record('moewiki_annotations_permiss', array('annotationid' => $annotation->id));
            if($permissions->read_prem != null && $permissions->read_prem != $USER->id && !has_capability('moodle/site:config', context_system::instance())){
                continue;
            }
            $annotationsreturn[$key] = new stdClass();
            $annotationsreturn[$key]->id = $annotation->id;
            $annotationsreturn[$key]->ranges = array();
            $ranges = $DB->get_records('moewiki_annotations_ranges', array(
                'annotationid' => $annotation->id
            ));
            foreach ($ranges as $rangekey => $range) {
                $annotationsreturn[$key]->ranges[$rangekey] = new stdClass();
                $annotationsreturn[$key]->ranges[$rangekey]->end = $range->end;
                $annotationsreturn[$key]->ranges[$rangekey]->endOffset = $range->endoffset;
                $annotationsreturn[$key]->ranges[$rangekey]->start = $range->start;
                $annotationsreturn[$key]->ranges[$rangekey]->startOffset = $range->startoffset;
            }
            $annotationsreturn[$key]->permissions = new stdClass();
            $annotationsreturn[$key]->permissions->read = array($permissions->read_prem);
            $annotationsreturn[$key]->permissions->delete = array($permissions->delete_prem);
            $annotationsreturn[$key]->permissions->admin = array($permissions->admin_prem);
            $annotationsreturn[$key]->permissions->update = array($permissions->update_prem);
            $annotationsreturn[$key]->quote = $annotation->quote;
            $annotationsreturn[$key]->text = $annotation->text;
            $annotationsreturn[$key]->created = $annotation->created;
            $annotationsreturn[$key]->updated = $annotation->updated;
            $annotationsreturn[$key]->userid = $annotation->userid;
            $annotationsreturn[$key]->parent = $annotation->parent;
            $user = new stdClass();
            $user->id = $annotation->userid;
            $user = $DB->get_record('user', array('id' => $user->id));
            $userpicture = new user_picture($user);
            $cm = moewiki_get_cm($wikiid);
            $PAGE->set_cm($cm);
            $annotationsreturn[$key]->username =  $user->firstname . ' ' . $user->lastname;
            $annotationsreturn[$key]->userpicture = $userpicture->get_url($PAGE)->out();
            $total++;
        }
        return array(
            'total' => $total,
            'rows'  => $annotationsreturn);
    }

    public static function search_returns()
    {
        return new external_single_structure(array(
        'total' => new external_value(PARAM_INT),
        'rows'  => new external_multiple_structure(new external_single_structure(array(
            'id'     => new external_value(PARAM_INT),
            'ranges' => new external_multiple_structure(new external_single_structure(array(
                'start' => new external_value(PARAM_TEXT, "The annotaion start element"),
                'end' => new external_value(PARAM_TEXT, "The annotation end element"),
                'startOffset' => new external_value(PARAM_INT, "The start offset position of the annotaion in the element"),
                'endOffset' => new external_value(PARAM_INT, "The end offset position of the annotaion in the element")
            ))),
            'quote' => new external_value(PARAM_TEXT),
            'links' => new external_multiple_structure(new external_single_structure(array(
                'type' => new external_value(PARAM_SAFEPATH),
                'rel' => new external_value(PARAM_ALPHAEXT),
                'href' => new external_value(PARAM_URL)
            )), "annotauion links", false),
           'permissions' => new external_single_structure(array(
                'read' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'delete' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'admin' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'update' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
            ), "annotaion permissino", false, array()),
            'text' => new external_value(PARAM_TEXT),
            'created' => new external_value(PARAM_TEXT),
            'updated' => new external_value(PARAM_TEXT),
            'userid' => new external_value(PARAM_INT),
            'userpicture' => new external_value(PARAM_URL),
            'parent' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT),
        )))));
    }
    
    public static function reopen_parameters () {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT,'annotation_id'),
        ));
    }
    
    public static function reopen($id) {
        return array('success' => moewiki_reopen_annotation($id));
    }
    
    public static function reopen_returns(){
        return new external_function_parameters(array(
            'success' => new external_value(PARAM_BOOL),
        ));
    }
    
    public static function delete_parameters () {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT,'annotation id'),
        ));
    }
    
    public static function delete($id) {
        global $DB;
        
        if($DB->delete_records('moewiki_annotations_ranges',array('annotationid' => $id)) &&
            $DB->delete_records('moewiki_annotations_permiss',array('annotationid' => $id))) {
            $DB->delete_records('moewiki_annotations',array('id' => $id));
        }
        return null;
    }
    
    public static function delete_returns(){
        return null;
    }
    
    public static function resolved_parameters () {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT,'annotation id'),
        ));
    }
    
    public static function resolved($id) {
        global $DB;
        
        $annotaion = new stdClass();
        $annotaion->id = (int)$id;
        $annotaion->resolved =1;
        if($DB->update_record('moewiki_annotations', $annotaion)){
            if($childs = $DB->get_records('moewiki_annotations',array('parent' => $annotaion->id))){
                foreach ($childs as $childsannotation){
                    $childsannotation->resolved = 1;
                    $DB->update_record('moewiki_annotations', $childsannotation);
                }
            }
            return array(
                'success' => true,
                'childes' => array(array_keys($childs)),
            );
        }
        return array('successs' => false);
    }
    
    public static function resolved_returns() {
        return new external_function_parameters(array(
            'success' => new external_value(PARAM_BOOL),
            'childes' => new external_multiple_structure(new external_single_structure(array(
                new external_value(PARAM_INT),
            ))),
        ));
    }
    
    public static function update_parameters () {
        return new external_function_parameters(array(
            'id'      => new external_value(PARAM_INT),
            'created' => new external_value(PARAM_TEXT),
            'quote'   => new external_value(PARAM_TEXT),
            'text'    => new external_value(PARAM_TEXT),
            'updated' => new external_value(PARAM_TEXT),
            'ranges'  => new external_multiple_structure(new external_single_structure(array(
                'start'       => new external_value(PARAM_TEXT, "The annotaion start element"),
                'end'         => new external_value(PARAM_TEXT, "The annotation end element"),
                'startOffset' => new external_value(PARAM_INT, "The start offset position of the annotaion in the element"),
                'endOffset'   => new external_value(PARAM_INT, "The end offset position of the annotaion in the element")
            ))),
            'userid' => new external_value(PARAM_INT),
            'permissions' => new external_single_structure(array(
                'read' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'delete' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'admin' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'update' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
            ), "annotaion permissino", false, array()),
            'userpicture' => new external_value(PARAM_URL),
            'parent' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT),
        ));
    }
    
    public static function update($id, $created, $quote, $text, $updated, $ranges, $userid, $permissions) {
        global $DB, $PAGE;
        
        $annotation = new stdClass();
        $annotation->id = $id;
        $annotation->text = $text;
        $annotation->updated = time();
        $annotation->quote = $quote;
        $annotation->userid = $userid;
        $DB->update_record('moewiki_annotations', $annotation);
        $annotation =  $DB->get_record('moewiki_annotations', array('id' => $id));
        $annotation->ranges = $ranges;
        $permission = $DB->get_record('moewiki_annotations_permiss', array('annotationid' => $id));
        $permission->read_prem = ($permissions['read'][0] == 'null') ? null : (int)$permissions['read'][0];
        $permission->delete_prem = ($permissions['delete'][0] == 'null') ? $permission->delete_prem : (int)$permissions['delete'][0];
        $permission->admin_prem = ($permissions['admin'][0] == 'null') ? $permission->admin_prem : (int)$permissions['admin'][0];
        $permission->update_prem = ($permissions['update'][0] == 'null') ? null: (int)$permissions['update'][0];
        $DB->update_record('moewiki_annotations_permiss', $permission);
        $annotation->permissions = new stdClass();
        $annotation->permissions->read = array($permission->read_prem);
        $annotation->permissions->delete = array($permission->delete_prem);
        $annotation->permissions->admin = array($permission->admin_prem);
        $annotation->permissions->update = array($permission->update_prem);
        $user = new stdClass();
        $user->id = $annotation->userid;
        $user = $DB->get_record('user', array('id' => $user->id));
        $userpicture = new user_picture($user);
        $cm = moewiki_get_cm($annotation->pageid);
        $PAGE->set_cm($cm);
        $annotation->username =  $user->firstname . ' ' . $user->lastname;
        $annotation->userpicture = $userpicture->get_url($PAGE)->out();
        return $annotation;
    }
    
    public static function update_returns() {
        return new external_single_structure(array(
            'id'      => new external_value(PARAM_INT),
            'created' => new external_value(PARAM_TEXT),
            'quote'   => new external_value(PARAM_TEXT),
            'text'    => new external_value(PARAM_TEXT),
            'updated' => new external_value(PARAM_TEXT),
            'ranges'  => new external_multiple_structure(new external_single_structure(array(
                'start'       => new external_value(PARAM_TEXT, "The annotaion start element"),
                'end'         => new external_value(PARAM_TEXT, "The annotation end element"),
                'startOffset' => new external_value(PARAM_INT, "The start offset position of the annotaion in the element"),
                'endOffset'   => new external_value(PARAM_INT, "The end offset position of the annotaion in the element")
            ))),
            'permissions' => new external_single_structure(array(
                'read' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'delete' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'admin' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
                'update' => new external_single_structure(array(
                    new external_value(PARAM_TEXT,'',false, 'null',true)
                ),'',false,array()),
            ), "annotaion permissino", false, array()),
            'userid' => new external_value(PARAM_INT),
            'parent' => new external_value(PARAM_INT,'annotation parent',false, null, true),
            'userpicture' => new external_value(PARAM_URL),
            'username' => new external_value(PARAM_TEXT),
        ));
    }
    
    public static function create_version_parameters(){
        return new external_function_parameters(array(
            'text' => new external_value(PARAM_TEXT),
        ));
    }
    
    public static function create_version($text){
        moewiki_save_new_version_section();
    }
    
    public static function create_version_returns(){
        
    }
}