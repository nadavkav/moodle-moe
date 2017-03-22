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
* @package   mod_quizsbs
* @module    mod_quizsbs/loadcintent
**/
define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification){
	var LoadContent = function () {
		
	};
	
	LoadContent.prototype.init = function (){
		$('.content').click(function(event){
			var element = event.target;
			var getcontent = [{
			    'methodname': 'mod_quizsbs_get_content_preview',
			    'args': {
			    	'id': $(element).attr('id')
			    }
			}];
			var promises = ajax.call(getcontent);
			
			promises[0].done(function(response){
				$('#htmlcontent').html('');
				$('#csscontent').html('');
				$('#javascriptcontent').html('');
				for (var index in response) {
					switch (response[index].type) {
						case 0:
							$('#htmlcontent').html(response[index].content);
							break;
						case 3:
							$('#csscontent').html("<style>" + response[index].content + "</style>");
							break;
						case 2:
							$('#javascriptcontent').html("<script>" + response[index].content + "</script>");
							break;
					}
				}
			});
		});
		$('#aprroved').click(function(){
			var contentsids = [];
			$('input[type="checkbox"]:checked').each(function(index, element){
				contentsids.push($(element).val());
			});
			var getcontent = [{
			    'methodname': 'mod_quizsbs_add_content_to_subject',
			    'args': {
			    	'ids': contentsids,
			    	'subjectid': $('heading h2').attr('id')
			    }
			}];
			var promises = ajax.call(getcontent);
			promises[0].done(function(){
				notification.addNotification({
				       message: M.util.get_string('changessuccessfulsave', 'mod_quizsbs'),
				       type: "success"
				     });
				 $('html, body').animate({scrollTop: $('#user-notifications').offset().top}, 'fast');
			});
		});
	};
	return new LoadContent();
});