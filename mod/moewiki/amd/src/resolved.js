/**
 * @package    mod_moewiki
 */

/**
 * @module mod_moewiki/resolved
 */

define(['jquery', 'core/ajax'],function($, ajax){
	function resolved() {
				
	}
		
	resolved.prototype.reopen = function (){
			$('#resolvedAnnotation button').click(function(e){
				var obj = {};
				var button = $(this);
				obj.id = button.attr('id');
				var data = {};
				data.methodname = 'moe_wiki_reopen';
				data.args = obj;
				var promises = ajax.call([data]);
				promises[0].done(function(response) {
				       if(response.success){
				    	   button.parents("tr").remove();  
				       }
				}).fail(function(ex) {
				      console.log(ex);
				});
				
			});
	}
	
	return new resolved();
});