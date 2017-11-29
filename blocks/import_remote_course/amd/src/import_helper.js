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
 * @module     block_import_remote_course/import_helper
 * @package    block_import_remote_course
 * @copyright  2017 Sysbind
 */
define(['jquery', 'jqueryui'],function($, jqui) {
	var approv_request_helper = function() {};

	approv_request_helper.prototype.init = function(){
		$('#modlist, #section').draggable({ scroll: true });
		
		$( "#newitems, .close, #close" ).click(function() {
			$( "#modlist" ).toggle();
		});	
		
		$('[id^=section]').droppable({
			  accept: "tr",
		      drop: function( event, ui ) {
		    	  alert('dfgh');
		        }
		});
				
	};
	
	return new approv_request_helper();
});
