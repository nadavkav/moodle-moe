/**
 * @package    mod_moewiki
 */

/**
 * @module mod_moewiki/annotation
 */

define([ 'jquery', 'mod_moewiki/annotator', 'core/ajax'], function($, annotator, ajax) {
	var annotation = {
		merkannotaion : function(params) {
			var app = new annotator.App();
			app.include(annotator.ui.main, {
			    element: document.querySelector('.moewiki_content')
			});
			function remarks() {
				return {
					start: function (app) {
						viewer = new annotator.ui.viewer.Viewer({defaultFields: false});
						viewer.setRenderer(this.annotationEditorShown);
					},
					annotationsLoaded: function(annotations) {
						for (key in annotations) {
							
						} 
					},
					beforeAnnotationCreated: function(annotation) {
						annotation.page = params.wikiid;
						annotation.userpage = params.userpage;
					},
					annotationEditorShown : function(annotation){
						return '<img src="'+ annotation.userpicture +'" />';
					}
				};
			}
			/*var editor = new annotator.ui.editor.Editor();
			editor.addField({
			     label: 'My custom input field',
		         type:  'checkbox',
		         load:  remarks,
		         save:  remarks,
		       });*/
			app.include(annotator.identity.simple);
			app.include(annotator.authz.acl);
			app.include(remarks);
			app.include(this.moodlestorage);
			app.start().then(function () {
			     var promise = app.annotations.store.query(params.wikiid,params.userpage);
			     promise.then(function(data){
			    	 app.annotations.runHook('annotationsLoaded',[data.rows]);
			    	 app.ident.identity = params.userid;
			     });
			});
		},
		moodlestorage: function (options) {
			// This gets overridden on app start
		    var notify = function () {};

		    if (typeof options === 'undefined' || options === null) {
		        options = {};
		    }

		    // Use the notifier unless an onError handler has been set.
		    options.onError = options.onError || function (msg, xhr) {
		        console.error(msg, xhr);
		        notify(msg, 'error');
		    };

		    var storage = {
					create : function(annotation) {
						if(typeof annotation === 'undefined' || annotation === null){
							annotation = {};
						}
						return this.ajaxcall('create', annotation);
					},
					query : function(wikiid,userpage){
						return this.search(wikiid,userpage);
					},
					search: function(wikiid,userpage){
						return this.ajaxcall('search', {
							'wikiid': wikiid,
							'userpage' : userpage
						});
					},
					'delete' : function(annotation){
						return this.ajaxcall('delete',{'id' : annotation.id});
					},
					update: function(annotation){
						return this.ajaxcall('update', annotation);
					},
					ajaxcall: function(action,obj){
						var id = obj && obj.id;
						var data = {};
						data.methodname = 'moe_wiki_'+action;
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