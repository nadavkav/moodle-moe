/**
 * @package    local_notes
 */

/**
 * @module local_notes/annotation
 */

define([ 'jquery', 'local_notes/annotator', 'core/ajax', 'local_notes/autosize'], function($, annotator, ajax, autosize) {
	var noteid;
	var pagename;
	var userid;
	var groupid;
	var moduleid;
	var annotation = {
		merkannotaion : function(params) {
			function Remarks() {
				return {
					beforeAnnotationCreated: function(annotation) {
						annotation.page = params.noteid;
						annotation.userpage = params.userpage;
					},
					annotationEditorShown : function(annotation){
						return '<img src="'+ annotation.userpicture +'" />';
					}
				};
			}
			var app = new annotator.App();
			app.include(annotator.ui.main, {
			    element: document.querySelector('.editor_atto_content'),
			});
			app.include(annotator.identity.simple);
			app.include(annotator.authz.acl);
			app.include(Remarks);
			app.include(this.moodlestorage);
			app.start().then(function () {
			     var promise = app.annotations.store.query(params.noteid);
			     noteid = params.noteid;
			     pagename = params.pagename;
			     userid = params.userpage;
			     groupid = params.groupid;
			     moduleid = params.id;
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
		    function savenewversion (){
				var args = {
					    'text': $('.editor_atto_content').html(),
					    'noteid': noteid,
					    'pagename': pagename,
					    'userid': userid,
					    'groupid': groupid,
					    'id': moduleid
				};
				ajax.call([{
					'methodname': 'notes_create_ver',
					'args':args
				}]);
		    }
		    var storage = {
					create : function(annotation) {
						var result = this.ajaxcall('create', annotation);
						result.then(function(annotation){
							var Highlighter = new annotator.ui.highlighter.Highlighter(document.querySelector('.editor_atto_content'));
							Highlighter.drawnewannotation(annotation);
							savenewversion();
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
							savenewversion();
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
						savenewversion();
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