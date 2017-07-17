/**
 * @package    local_notes
 */

/**
 * @module local_notes/notes
 */

define([ 'jquery', 'local_notes/annotation','jqueryui','core/ajax'], function($, annotation,yui,ajax) {	
    
	var note = function(){};
	
	note.prototype.init = function(params) {
		globalcontent = params.content;
	
	
	$( document ).ready(function() {
		$('.editor_atto_content').text(globalcontent);
	});
	
	function is_editor_content_chenge(){
		var content = $('.editor_atto_content').html();
		if (content != globalcontent){
			globalcontent = content;
			return true;
		} else {
			return false;
		}
	}
	

    function insert_new_notes_version (){

		var args = {
			    'content': $('.editor_atto_content').html(),
			    'namespace': params.namespace,
			    'id': params.namespaceid,
		};
		ajax.call([{
			'methodname': 'insert_notes',
			'args':args
		}]);
    }
	$( '#note_toggle_button, #id_submitbutton').click(function() {
		if (is_editor_content_chenge()){
			insert_new_notes_version()
		    }
		});
	
	// add draggable and resizable for note
	$(document).ready(function(event){
		$("#note_warp").draggable({cancel: '.editor_atto_content_wrap'}); 
		$("#note_warp").resizable();
		$("#id_submitbutton").val("סגור");

		});
	// hide show note
	$("#note_toggle_button").click(function(){
		$('#note_warp').toggle();
		});
	// prevent submit note
	$("#id_submitbutton").click(function(event){
	    event.preventDefault();
	    $('#note_warp').toggle();
	});
	annotation.merkannotaion(params, this);
	};
		
	
	
	return new note;
});