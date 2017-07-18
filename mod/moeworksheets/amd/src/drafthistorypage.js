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
* @package   mod_moeworksheets
* @module    mod_moeworksheets/dfrafthistorypage
**/
define(['jquery', 'jqueryui'], function($){
	var drafthistorypage = function() {};
	
	drafthistorypage.prototype.init = function() {
	
		$( 'table button' ).click(function() {
			var id = this.className;
			id = id.replace("newwin_","long")
			var middleman = $ ('#'+id).html();
			$ ('#id_contenteditable').html( middleman);
			$ ('#draftcontiner').show();
		});
		
		$( '.showdraft_button').click(function() {
			$ ('#draftcontiner').hide();
		});
		
	    $(document).ready(function(event){
	        $("#draftcontiner").draggable({cancel: '.editor_atto_content'});
	        $("#draftcontiner").resizable({containment: "#draftview"});

	        });
		
	
	};
	
	return new drafthistorypage();
});