/**
 * @package    mod_moewiki
 */

/**
 * @module mod_moewiki/annotation
 */

define([ 'jquery' ], function($) {
	var annotation = {
		merkannotaion : function() {
			var selection;
			$('.moewiki_content').mousedown(function() {
				$('.moewiki_content').mouseup(function() {
					try {
			            if (selection = window.getSelection()) {
			            	if(selection.type != 'Range'){
			            		return ;
			            	}
			            }
			        } catch (e) {
			            /* give up */
			        }
				});
			});

		},
	};
	return annotation;
});