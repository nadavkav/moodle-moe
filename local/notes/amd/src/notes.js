/**
 * @package    local_notes
 */

/**
 * @module local_notes/notes
 */

define([ 'jquery', 'local_notes/annotation', 'jqueryui', 'core/ajax' ],
		function($, annotation, yui, ajax) {

			var note = function() {};
			
			note.prototype.insert_new_notes_version = function(params) {
				globalcontent = $('#note .editor_atto_content').html();
				if (globalcontent == '' || globalcontent == undefined){
					globalcontent = $('#note .editor_atto_content').html();
					if (globalcontent.indexOf("form") !== -1) {
						globalcontent = $('#note_content').html();
					}
				}
				var args = {
					'content' : globalcontent,
					'namespace' : params.namespace,
					'id' : params.namespaceid,
					'show' : params.show,
				};
				ajax.call([ {
					'methodname' : 'insert_notes',
					'args' : args
				} ]);
			};

			note.prototype.init = function(params) {
				globalcontent = params.content;

				$(document).ready(function() {
					var refreshId = setInterval(function() {
						  if ($('#note .editor_atto_content').length > 0) {
							  $('#note .editor_atto_content').html(globalcontent);
							  clearInterval(refreshId);
						  }
						}, 1000);	
					
				});

				function is_editor_content_chenge() {
					var content = $('#note #id_contenteditable').html();
					if (content != globalcontent) {
						globalcontent = content;
						return true;
					} else {
						return false;
					}
				}

				$('#note_toggle_button, #id_submitbutton').click(function() {
					if (is_editor_content_chenge()) {
						params.show = 1;
						note.prototype.insert_new_notes_version(params);
					}
				});

				// add draggable and resizable for note
				$(document).ready(function() {
					$("#note_warp").draggable({
						cancel : '.editor_atto_content_wrap'
					}).resizable();
//					$("#note_warp").resizable();
					$("#id_submitbutton").val(M.util.get_string('close', 'local_notes'));
				});
				// hide show note
				$("#note_toggle_button").click(function() {
					$('#note_warp').toggle();
				});
				// prevent submit note
				$("#draft_warp #id_submitbutton").click(function() {
					event.preventDefault();
					$('#note_warp').toggle();
				});
				annotation.merkannotaion(params, Note);
			};
			
			var Note = new note();
			return Note;
		});