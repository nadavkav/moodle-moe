/**
 * @package    mod_moewiki
 */

/**
 * @module mod_moewiki/annotation
 */

define([ 'mod_moewiki/jqueryselection' ], function() {
	var annotation = {
		merkannotaion : function() {
			$('.moewiki_content').mousedown(function() {
				$('.moewiki_content').mouseup(function() {
					$('.moewiki_content p').selection();
				});
			});

		},
	};
	return annotation;
});