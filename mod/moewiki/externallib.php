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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External moewiki API
 *
 * @package    mod_moewiki
 * @since      Moodle 3.1
 * @copyright  2016 SysBind
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

class mod_moewiki_external extends external_api {
    
    public static function create_parameters() {
        return new external_function_parameters(array(
            new external_single_structure(array(
                'ranges' => new external_single_structure(array(
                    'start'       => new external_value(PARAM_TEXT, "The annotaion start element"),
                    'end'         => new external_value(PARAM_TEXT, "The annotation end element"),
                    'startOffset' => new external_value(PARAM_INT, "The start offset position of the annotaion in the element"),
                    'endOffset'   => new external_value(PARAM_INT, "The end offset position of the annotaion in the element")
                )),
                'quote' => new external_value(PARAM_TEXT),
                'links' => new external_multiple_structure(new external_single_structure(array(
                    'type' => new external_value(PARAM_SAFEPATH),
                    'rel'  => new external_value(PARAM_ALPHAEXT),
                    'href' => new external_value(PARAM_URL),
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
                    )),
                ), "annotaion permissino", false),
                'text' => new external_value(PARAM_TEXT)
            )),
        ));
    }
    
    public static function create($annotation) {
        
    }
    
    public static function create_returns() {
        return new
    }
    
    public static function search_parameters() {
        return new external_function_parameters(array(
            'wikiid' => new external_value(PARAM_INT, "The wiki ID")            
        ));    
    }
    
    public static function search($wikiid) {
        $annotation = array(); 
        $annotation[] = new stdClass();
        $annotation[0]->ranges = new stdClass();
        $annotation[0]->ranges->start = "/div[4]/div[1]/p[1]";
        $annotation[0]->ranges->end = "/div[4]/div[1]/p[1]";
        $annotation[0]->ranges->startOffset = 14;
        $annotation[0]->ranges->endOffset = 21;
        $annotation[0]->quote = "walla,walla";
        $annotation[0]->text = "this is my annotaion";
        return $annotation;
    }
    
    public static function search_returns() {
        return new external_multiple_structure(
            new external_single_structure(array(
                'ranges' => new external_single_structure(array(
                        'start'       => new external_value(PARAM_TEXT, "The annotaion start element"),
                        'end'         => new external_value(PARAM_TEXT, "The annotation end element"),
                        'startOffset' => new external_value(PARAM_INT, "The start offset position of the annotaion in the element"),
                        'endOffset'   => new external_value(PARAM_INT, "The end offset position of the annotaion in the element")
                    )),
                'quote' => new external_value(PARAM_TEXT),
                'links' => new external_multiple_structure(new external_single_structure(array(
                    'type' => new external_value(PARAM_SAFEPATH),
                    'rel'  => new external_value(PARAM_ALPHAEXT),
                    'href' => new external_value(PARAM_URL),
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
                    )),
                ), "annotaion permissino", false),
                'text' => new external_value(PARAM_TEXT)
            )));
    }
}