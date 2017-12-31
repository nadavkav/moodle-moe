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
define(['jquery', 'jqueryui', 'core/ajax', 'core/str', 'core/notification' ],function($, jqui, ajax, str, notification) {
	var Approv_request_helper = function() {};

	Approv_request_helper.prototype.init = function(courseid){
		
		$('.activityitem').on('dragstart',function(event){
			$("[id^=section]").addClass('highlight');
			event.originalEvent.dataTransfer.setData('text/html', event.target.id);
		});
		
		$('.activityitem').on('dragend',function(event){
			$("[id^=section]").removeClass('highlight');
		});
		
		$('.sectionitem').on('dragstart',function(event){
			$(".course-content").addClass('drop');
			event.originalEvent.dataTransfer.setData('text/html', event.target.id);
		});
		
		$('.sectionitem').on('dragend',function(event){
			$(".course-content").removeClass('drop');
		});
		
		$("#newitems").click(function() {
			$("#newactivitieslist").toggleClass('hidden');
		});	
		
		$("#updateitems").click(function() {
			$("#updatedactivites").toggleClass('hidden');
		});	
		
		$("#newsection").click(function() {
			$("#newsectionlist").toggleClass('hidden');
		});	
		
		$('li.section').on('drop dragover dragleave',function(event){
			switch (event.type){
				case 'dragover':
					event.preventDefault();
					$(event.target).closest('[id^=section-]').addClass('drop');
					break;
				case 'dragleave':
					$(event.target).closest('[id^=section-]').removeClass('drop');
					break;
				case 'drop':
					var data = event.originalEvent.dataTransfer.getData("text/html");
					//mac case
					if(data.indexOf("meta") > -1) {
						data = data.replace( /^\D+/g, '');
						data = data.substring(3);
					}
				    if(!$('#' + data).hasClass('activityitem') && !$('#' + data).hasClass('sectionitem')){
				    	break;
				    }
				    runprograssbar();
				    
				    if($('#' + data).hasClass('activityitem')){
					var promises = ajax.call([
						{methodname: 'block_import_remote_course_activity', args: {
							'cmid': data,
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
				    
				    if($('#' + data).hasClass('sectionitem')){
						var promises = ajax.call([
							{methodname: 'block_import_remote_course_section', args: {
								'cmid': data,
								'courseid': courseid,
							}}
						]);
					    promises[0].done(function(response) {
					       if(response.status== 'success'){
					    	   location.reload();
					       }
					    });
						break;
					    }
			}
		});
		
		$("#newconfirmyes").click(function() {
			var promises = ajax.call([
				{methodname: 'block_import_remote_course_delete_act', args: {
					'course_id': courseid,
					'type' : 'new'
				}}
			]);
		    promises[0].done(function(response) {
		       if(response.result == true){
		    	   location.reload();
		       } else {
		    	   editaPresent = str.get_string('errordelete', 'block_import_remote_course');
		    	   $.when(editaPresent).done(function(localizedEditString) {
		    	         text = localizedEditString;
		    	    });
		    	   editaPresent = str.get_string('confirm', 'core');
		    	   $.when(editaPresent).done(function(localizedEditString) {
		    	         confirm = localizedEditString;
		    	    });
		    	   notification.alert('', text, confirm);
		       }
		    });
		});	
		
		$("#updateconfirmyes").click(function() {
			var promises = ajax.call([
				{methodname: 'block_import_remote_course_delete_act', args: {
					'course_id': courseid,
					'type' : 'update'
				}}
			]);
		    promises[0].done(function(response) {
		       if(response.result == true){
		    	   location.reload();
		       } else {
		    	   editaPresent = str.get_string('errordelete', 'block_import_remote_course');
		    	   $.when(editaPresent).done(function(localizedEditString) {
		    	         text = localizedEditString;
		    	    });
		    	   editaPresent = str.get_string('confirm', 'core');
		    	   $.when(editaPresent).done(function(localizedEditString) {
		    	         confirm = localizedEditString;
		    	    });
		    	   notification.alert('', text, confirm);
		       }
		    });
		});	
		
		$("#newsectionsconfirmyes").click(function() {
			var promises = ajax.call([
				{methodname: 'block_import_remote_course_delete_act', args: {
					'course_id': courseid,
					'type' : 'section'
				}}
			]);
		    promises[0].done(function(response) {
		       if(response.result == true){
		    	   location.reload();
		       } else {
		    	   editaPresent = str.get_string('errordelete', 'block_import_remote_course');
		    	   $.when(editaPresent).done(function(localizedEditString) {
		    	         text = localizedEditString;
		    	    });
		    	   editaPresent = str.get_string('confirm', 'core');
		    	   $.when(editaPresent).done(function(localizedEditString) {
		    	         confirm = localizedEditString;
		    	    });
		    	   notification.alert('', text, confirm);
		       }
		    });
		});	
	};
	
	function runprograssbar() {
		$('#progres').modal('show');
	}
	
	return new Approv_request_helper();
});
