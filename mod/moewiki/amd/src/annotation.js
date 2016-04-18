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
			     app.annotations.load();
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
		moodlestorage: function () {
			return {
				create : function(annotaion) {
					return this.ajaxcall('create', annotaion);
				},
				query : function(){
					return this.search();
				},
				search: function(){
					return this.ajaxcall('search', {});
				},
				ajaxcall: function(action,obj){
					var id = obj && obj.id;
					var url = this.options.prefix;
					var data = {};
					data.methodname = 'moe_wiki_'+action;
					data.args = obj;
					return ajax.call([data]);
				},
			};
		}
	};
	return annotation;
});