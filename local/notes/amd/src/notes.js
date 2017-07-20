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
				globalcontent = $('.editor_atto_content').html();
				if (globalcontent == '' || globalcontent == undefined){
					globalcontent = $('#note').html();
					if (globalcontent.indexOf("form") !== -1) {
						globalcontent = $('#note_content').html();
					}
				}
				var args = {
					'content' : globalcontent,
					'namespace' : params.namespace,
					'id' : params.namespaceid,
				};
				ajax.call([ {
					'methodname' : 'insert_notes',
					'args' : args
				} ]);
			};

			note.prototype.init = function(params) {
				globalcontent = params.content;

				$(document).ready(function() {
					$('.editor_atto_content').text(globalcontent);
				});

				function is_editor_content_chenge() {
					var content = $('.editor_atto_content').html();
					if (content != globalcontent) {
						globalcontent = content;
						return true;
					} else {
						return false;
					}
				}

				$('#note_toggle_button, #id_submitbutton').click(function() {
					if (is_editor_content_chenge()) {
						note.prototype.insert_new_notes_version(params);
					}
				});

				// add draggable and resizable for note
				$(document).ready(function() {
					$("#note_warp").draggable({
						cancel : '.editor_atto_content_wrap'
					});
					$("#note_warp").resizable();
					$("#id_submitbutton").val("סגור");

				});
				// hide show note
				$("#note_toggle_button").click(function() {
					$('#note_warp').toggle();
				});
				// prevent submit note
				$("#id_submitbutton").click(function() {
					event.preventDefault();
					$('#note_warp').toggle();
				});
				annotation.merkannotaion(params, Note);
			};
			
			var Note = new note();
			return Note;
		});