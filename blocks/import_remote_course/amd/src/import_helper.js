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
define(['jquery', 'jqueryui', 'core/ajax'],function($, jqui, ajax) {
	var Approv_request_helper = function() {};

	Approv_request_helper.prototype.init = function(courseid){
		$('#modlist, #section').draggable({ scroll: true });
		$('.activityitem').on('dragstart',function(event){
			event.originalEvent.dataTransfer.setData('text/html', event.target.id);
		});
		$("#newitems").click(function() {
			$("#newactivitieslist").toggleClass('hidden');
		});	
		
		$('li.section').on('drop dragover',function(event){
			switch (event.type){
				case 'dragover':
					event.preventDefault();
					break;
				case 'drop':
					var data = event.originalEvent.dataTransfer.getData("text/html");
				    if(!$('#' + data).hasClass('activityitem')){
				    	break;
				    }
					var promises = ajax.call([
						{methodname: 'block_import_remote_course_activity', args: {
							'cmid': $('#' + data).data('cmid'),
							'courseid': courseid,
							'sectionid' : $(this).attr('id').replace('section-', '')
						}}
					]);
				    promises[0].done(function(response) {
				       if(response.status== 'success'){
				    	   location.reload();
				       }
				    });
					break;
			}
		});
	};
	
	return new Approv_request_helper();
});
