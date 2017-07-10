/**
 * @package    local_notes
 */

/**
 * @module local_notes/resolved
 */

define(['jquery', 'core/ajax'],function($, ajax){
	function Resolved() {
				
	}
		
	Resolved.prototype.reopen = function (params){
			$('#resolvedAnnotation button').click(function(){
				var obj = {};
				var button = $(this);
				obj.id = button.attr('id');
				obj.pagename = button.parent().siblings('.pagename').text();
				obj.subwiki = params.subwiki;
				obj.moduleid = params.moduleid;
				var data = {};
				data.methodname = 'notes_reopen';
				data.args = obj;
				var promises = ajax.call([data]);
				promises[0].done(function(response) {
				       if(response.success){
				    	   button.parents("tr").remove();  
				       }
				});
			});
	};
	
	return new Resolved();
});