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
define(['jquery', 'core/ajax', 'core/notification', 'core/templates'], function($, ajax, notification, templates){
	var Connect = function() {};
	
	Connect.prototype.init = function() {
		$('[name="content"]').click(function(event){
			var element = event.target;
			var data = [{
				'methodname' : 'mod_quizsbs_load_connected_content',
				'args' : {
					'id' : $(element).val()
				}
			}];
			var promises = ajax.call(data);
			promises[0].done(function(response){
				var promise = templates.render('mod_quizsbs/connectsubject', response);
				promise.done(function(source) {
					$('#subjectlist').html(source);
					$('#subject'+$('[name="content"]:checked').data('subject')).attr('checked','checked');
				});
				promise = templates.render('mod_quizsbs/questionlist', response);
				promise.done(function(source) {
					$('#questionlist').html(source);
				});
			});
		});
		$('#connectsubject').click(function(){
			var questionsids = [];
			$('input[type="checkbox"]:checked').each(function(index, element){
				questionsids.push($(element).val());
			});
			var getcontent = [{
			    'methodname': 'mod_quizsbs_add_question_to_content',
			    'args': {
			    	'ids': questionsids,
			    	'contentid': $('[name="content"]:checked').val(),
			    	'cmid': $('#contentlist').data('cmid')
			    }
			}];
			var promises = ajax.call(getcontent);
			getcontent = [{
			    'methodname': 'mod_quizsbs_add_subject_to_content',
			    'args': {
			    	'id': $('input[name="subject"]:checked').val(),
			    	'contentid': $('[name="content"]:checked').val(),
			    	'cmid': $('#contentlist').data('cmid')
			    }
			}];
			promises = ajax.call(getcontent);
			promises[0].done(function(){
				$('[name="content"]:checked').data('subject', $('input[name="subject"]:checked').val());
				notification.addNotification({
				       message: M.util.get_string('changessuccessfulsave', 'mod_quizsbs'),
				       type: "success"
				     });
				 $('html, body').animate({scrollTop: $('#user-notifications').offset().top}, 'fast');
			});
		});
	};
	
	return new Connect();
});