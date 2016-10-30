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
require_once($CFG->libdir . "/externallib.php");

require_once($CFG->dirroot .'/lib/moodlelib.php');
//require_once($CFG->dirroot .'/course/format/lm/lib.php');
require_once($CFG->dirroot .'/config.php');

//require_once('config.php');
require_once ('lib.php');
   
class ajax_external extends external_api
{
    /*public static function update_clock_ajax($content)
    {
       try {
          $content=getdate();
          return $content;
        } catch (Exception $ex) {
            error_log('update_section_ajax_error : '. $ex->getMessage());
        }
      }*/
    
      
      public static function update_clock_ajax()
      {
          try {
              $content=getdate();
              return $content;
          } catch (Exception $ex) {
              error_log('update_section_ajax_error : '. $ex->getMessage());
          }
      }
    
      
      public static function update_clock_ajax_parameters()
      {
          //return null;
          return new external_function_parameters(
          
             null
               
          
          );
      }  
      /*
    public static function update_clock_ajax_parameters()
    {
            return new external_function_parameters(array(
    
               'content' => new external_value( PARAM_TEXT),
           
    
        ));
    
    }
    */
    public static function update_clock_ajax_returns()
    {
    
           return new external_single_structure(array(
            'content'=> new external_value(PARAM_TEXT),
            
        ));
    }
    
    
   
   
    

}