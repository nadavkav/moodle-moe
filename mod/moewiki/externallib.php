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
/*                 'links' => new external_multiple_structure(new external_single_structure(array(
                    'type' => new external_value(PARAM_SAFEPATH),
                    'rel'  => new external_value(PARAM_ALPHAEXT),
                    'href' => new external_value(PARAM_URL),
                )), "annotauion links", false), */
                /* 'permissions' => new external_single_structure(array(
                    'read' => new external_single_structure(array(
                        'group' => new external_value(PARAM_ALPHAEXT)
                    )),
                    'delete' => new external_single_structure(array(
                        'group' => new external_value(PARAM_ALPHAEXT)
                    )),
                    'admin' => new external_single_structure(array(
                        'group' => new external_value(PARAM_ALPHAEXT)
                    )),
                    'update' => new external_single_structure(array(
                        'group' => new external_value(PARAM_ALPHAEXT)
                    )),
                ), "annotaion permissino", false), */
                'text' => new external_value(PARAM_TEXT)
        ));
    }

    public static function create($ranges, $quote, $page, $text)
    {
        global $DB, $USER;
        
        $annotation = new stdClass();
        $annotation->pageid = $page;
        $annotation->userid = $USER->id;
        $annotation->created = time();
        $annotation->quote = $quote;
        $annotation->text = $text;
        $annotation->updated = $annotation->created;
        $annotation->id = $DB->insert_record('moewiki_annotations', $annotation);
        
        foreach ($ranges as $range) {
            $rangeobj = new stdClass();
            $rangeobj->start = $range['start'];
            $rangeobj->end = $range['end'];
            $rangeobj->startoffset = $range['startOffset'];
            $rangeobj->endoffset = $range['endOffset'];
            $rangeobj->annotaionid = $annotation->id;
            $DB->insert_record('moewiki_annotations_ranges', $rangeobj);
        }
        $annotation->ranges = $ranges;
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
            'userid' => new external_value(PARAM_INT)
        ));
    }

    public static function search_parameters()
    {
        return new external_function_parameters(array(
            'wikiid' => new external_value(PARAM_INT, "The wiki ID")
        ));
    }

    public static function search($wikiid)
    {
        global $DB;
        
        $annotations = $DB->get_records('moewiki_annotations', array(
            'pageid' => $wikiid
        ));
        $annotationsreturn = array();
        $total=0;
        foreach ($annotations as $key => $annotation) {
            $annotationsreturn[$key] = new stdClass();
            $annotationsreturn[$key]->id = $annotation->id;
            $annotationsreturn[$key]->ranges = array();
            $ranges = $DB->get_records('moewiki_annotations_ranges', array(
                'annotaionid' => $annotation->id
            ));
            foreach ($ranges as $rangekey => $range) {
                $annotationsreturn[$key]->ranges[$rangekey] = new stdClass();
                $annotationsreturn[$key]->ranges[$rangekey]->end = $range->end;
                $annotationsreturn[$key]->ranges[$rangekey]->endOffset = $range->endoffset;
                $annotationsreturn[$key]->ranges[$rangekey]->start = $range->start;
                $annotationsreturn[$key]->ranges[$rangekey]->startOffset = $range->startoffset;
            }
            $annotationsreturn[$key]->quote = $annotation->quote;
            $annotationsreturn[$key]->text = $annotation->text;
            $annotationsreturn[$key]->created = $annotation->created;
            $annotationsreturn[$key]->updated = $annotation->updated;
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
                    'group' => new external_value(PARAM_ALPHAEXT)
                )),
                'delete' => new external_single_structure(array(
                    'group' => new external_value(PARAM_ALPHAEXT)
                )),
                'admin' => new external_single_structure(array(
                    'group' => new external_value(PARAM_ALPHAEXT)
                )),
                'update' => new external_single_structure(array(
                    'group' => new external_value(PARAM_ALPHAEXT)
                ))
            ), "annotaion permissino", false),
            'text' => new external_value(PARAM_TEXT),
            'created' => new external_value(PARAM_TEXT),
            'updated' => new external_value(PARAM_TEXT),
        )))));
    }
    
    public static function delete_parameters () {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT,'annotation id'),
        ));
    }
    
    public static function delete($id) {
        global $DB;
        
        if($DB->delete_records('moewiki_annotations_ranges',array('annotaionid' => $id))) {
            $DB->delete_records('moewiki_annotations',array('id' => $id));
        }
        return null;
    }
    
    public static function delete_returns(){
        return null;
    }
}