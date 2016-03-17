/**
 * @package    mod_moewiki
 */

/**
 * @module mod_moewiki/annotation
 */

define([ 'jquery', 'core/yui' ], function($, Y) {
	var annotation = {
	    confirmDialogue : null,
	    displayDialogue: function(e) {
            if (e) {
                e.halt();
            }
            annotation.confirmDialogue.show();
        },
		merkannotaion : function(strings) {
			var selection;
			annotation.confirmDialogue = new M.core.dialogue({
                headerContent: 'Annotation Form',
                bodyContent: Y.one('#annotationFrom'),
                draggable: true,
                visible: false,
                center: true,
                modal: true,
                width: null,
                extraClasses: ['mod_mowiki_annotate_popup']
            });
			Y.one('#testmodal').on('click', annotation.displayDialogue);
			$('.moewiki_content').mousedown(function() {
				$('.moewiki_content').mouseup(function() {
					try {
			            if (selection = window.getSelection()) {
			            	if(selection.type != 'Range'){
			            		annotation.hide_annotaion(Y.one('#testmodal'));
			            	}
			            	annotation.show_annotaion(Y.one('#testmodal'));
			            }
			        } catch (e) {
			            /* give up */
			        }
				});
			});

		},
		hide_annotaion: function(element){
			element.setStyles({display: 'none'});
		},
		show_annotaion: function(element){
			element.setStyles({display: 'block'});
		},
		displayDialogue: function(e) {
            if (e) {
                e.halt();
            }
            annotation.confirmDialogue.show();
        },
	};
	return annotation;
});