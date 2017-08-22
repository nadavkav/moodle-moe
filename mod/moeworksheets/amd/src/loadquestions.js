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
* @module    mod_moeworksheets/loadquestions
**/
define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification){
	var Question = function() {
		
	};
	
	Question.prototype.init = function(){
		$('.question').click(function(event){
			var element = event.target;
			var getquestion = [{
				'methodname': 'mod_moeworksheets_get_question_preview',
				'args': {
					'id': $(element).attr('id'),
					'cmid': $('#questionconnect heading h2').data('cmid')
				}
			}];
			var promises = ajax.call(getquestion);
			
			promises[0].done(function(response){
				$('#question_preview').html(response.questionhtml);
			});
		});
		$('#aprroved').click(function(){
			var questionsids = [];
			$('input[type="checkbox"]:checked').each(function(index, element){
				questionsids.push($(element).val());
			});
			var getcontent = [{
			    'methodname': 'mod_moeworksheets_add_question_to_content',
			    'args': {
			    	'ids': questionsids,
			    	'contentid': $('heading h2').attr('id'),
			    	'cmid': $('#questionconnect heading h2').data('cmid')
			    }
			}];
			var promises = ajax.call(getcontent);
			promises[0].done(function(){
				notification.addNotification({
				       message: M.util.get_string('changessuccessfulsave', 'mod_moeworksheets'),
				       type: "success"
				     });
				 $('html, body').animate({scrollTop: $('#user-notifications').offset().top}, 'fast');
			});
		});
	};
	
	return new Question();
});