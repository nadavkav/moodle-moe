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
* @module    mod_quizsbs/preflightcheck
**/
define(['query', 'core/ajax'], function($, ajax){
	var LoadContent = function () {
		
	}
	
	LoadContent.prototype.init = function (){
		$('.content').click(function(element){
			var getcontent = {
			    'method': 'mod_quizsbs_get_content_preview',
			    'args': {
			    	'id': $(element).attr('id')
			    }
			};
			var promises = ajax.call(getcontent);
			
			promises[0].done(function(response){
				
			}).fail(function(ex){
				
			});
		});
	};
	return new LoadContent();
});