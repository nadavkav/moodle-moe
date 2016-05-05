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
			app.include(annotator.identity.simple);
			app.include(this.moodlestorage);
			app.start().then(function () {
			     var promise = app.annotations.store.query(params);
			     promise.then(function(data){
			    	 app.annotations.runHook('annotationsLoaded',[data.rows]);
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
						var reg = /id=[0-9]+/;
						annotation.page = reg.exec(window.location.search);
						annotation.page = annotation.page[0].substring(3);
						return this.ajaxcall('create', annotation);
					},
					query : function(wikiid){
						return this.search(wikiid);
					},
					search: function(wikiid){
						return this.ajaxcall('search', {'wikiid': wikiid});
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