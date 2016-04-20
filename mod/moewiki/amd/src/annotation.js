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
			app.include(this.moodlestorage);
			app.start().then(function () {
			     app.annotations.load(params);
			});
			/*var promises = ajax.call([
			    { methodname: 'moe_wiki_search', args: { wikiid: 186} }
			]);
			promises[0].done(function(response) {
			       console.log('mod_moewiki/pluginname is' + response);
			   }).fail(function(ex) {
			       // do something with the exception
				   console.log('mod_moewiki/pluginname is' + ex);
			   });*/
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
					create : function(annotaion) {
						return this.ajaxcall('create', annotaion);
					},
					query : function(wikiid){
						return this.search(wikiid);
					},
					search: function(wikiid){
						return this.ajaxcall('search', {'wikiid': wikiid});
					},
					ajaxcall: function(action,obj){
						var id = obj && obj.id;
						var data = {};
						data.methodname = 'moe_wiki_'+action;
						data.args = obj;
						return ajax.call([data]);
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