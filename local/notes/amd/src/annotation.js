/**
 * @package    local_notes
 */

/**
 * @module local_notes/annotation
 */

define([ 'jquery', 'local_notes/annotator', 'core/ajax', 'local_notes/autosize'], function($, annotator, ajax, autosize) {
	var noteid;
	var globalnote;
	var globalparams;
	var annotation = {
		merkannotaion : function(params, Note) {
			function Remarks() {
				return {
					annotationEditorShown : function(annotation){
						return '<img src="'+ annotation.userpicture +'" />';
					}
				};
			}
			var app = new annotator.App();
			app.include(annotator.ui.main, {
			    element: document.querySelector('#note'),
			});
			app.include(annotator.identity.simple);
			app.include(annotator.authz.acl);
			app.include(Remarks);
			app.include(this.moodlestorage);
			app.start().then(function () {
			     var promise = app.annotations.store.query(params.noteid);
			     noteid = params.noteid;
			     globalnote = Note;
			     globalparams = params;
			     promise.then(function(data){
			    	 if(params.admin){
			    		 for (var index in data.rows){
			    			 data.rows[index].permissions.update.push(params.userid);
			    			 data.rows[index].permissions['delete'].push(params.userid);
						 }
			    	 }
			    	 app.ident.identity = params.userid;
			    	 app.annotations.runHook('annotationsLoaded',[data.rows]);			    	
			     });
			});
			autosize($('div.annotator-outer.annotator-editor textarea'));
		},
		moodlestorage: function (options) {
			// This gets overridden on app start
		    var notify = function () {};

		    if (typeof options === 'undefined' || options === null) {
		        options = {};
		    }

		    // Use the notifier unless an onError handler has been set.
		    options.onError = options.onError || function (msg) {
		        notify(msg, 'error');
		    };
		    var storage = {
					create : function(annotation) {
						annotation.noteid = noteid;
						var result = this.ajaxcall('create', annotation);
						result.then(function(annotation){
							var Highlighter = new annotator.ui.highlighter.Highlighter(document.querySelector('#note'));
							Highlighter.drawnewannotation(annotation);
							globalparams.noteid = noteid;
							globalnote.insert_new_notes_version(globalparams);
						});
						
						return result;
					},
					query : function(noteid){
						return this.search(noteid);
					},
					search: function(noteid){
						return this.ajaxcall('search', {
							'noteid': noteid
						});
					},
					'delete' : function(annotation){
						$('[data-annotation-id=' + annotation.id + ']').contents().unwrap();
						var result = this.ajaxcall('delete',{'id' : annotation.id});
						result.then(function(){
							globalnote.insert_new_notes_version(globalparams);
						});
						return result;
					},
					update: function(annotation){
						annotation.permissions['delete'] = [annotation.permissions['delete'][0]];
						var result = this.ajaxcall('update', annotation);
						return result;
					},
					resolved: function(annotation){
						this.ajaxcall('resolved',{'id' : annotation.id});
						$("[data-annotation-id=" + annotation.id +"]").removeClass('annotator-hl')
						.addClass('annotator-hl-resolved');
						globalnote.insert_new_notes_version(globalparams);
					},
					ajaxcall: function(action,obj){
						var data = {};
						data.methodname = 'notes_'+action;
						data.args = obj;
						return ajax.call([data])[0];
					}
				};

		    return {
		        configure: function (registry) {
		            registry.registerUtility(storage, 'storage');
		        },

		        start: function (app) {
		            notify = app.notify;
		        }
		    };
		    
		}
		
	};
	return annotation;
});