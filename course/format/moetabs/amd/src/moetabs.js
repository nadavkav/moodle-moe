/**
 * @package format_moetabs
 * 
 */

/**
  * @module format_moetabs/moetabs
  */


define(['jquery'], function() {

    var moetabs = function() {
    	
    	this.ruler = $('.format-moetabs ul.nav.nav-tabs');

    	 moetabs.prototype.init = function() {
    		
    		if(this.ruler.width() >= $('.format-moetabs .course-content .single-section').width()){
    			var error;
    			error = 'need arrow';
    		}

    		$( '.sectionzerobtn' ).click(function() {
    			$( '#gridshadebox_content' ).removeClass( 'hide_content');
    			$( '#gridshadebox_close' ).css("display", "inline");
    			$( '#gridshadebox_content' ).addClass( 'absolute' );
    			$( '#gridshadebox_overlay' ).css("display", "block"); 
    		});
    		
    		$( '#gridshadebox_close' ).click(function() {
    			$( '#gridshadebox_content' ).addClass( 'hide_content' );
    			$( '#gridshadebox_content' ).removeClass( 'absolute');
    			$( '#gridshadebox_overlay' ).css("display", "none");
    		});
    		
        };

    };

    return new moetabs();
});