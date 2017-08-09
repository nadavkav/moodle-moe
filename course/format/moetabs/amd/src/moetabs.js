/**
 * @package    format_moetabs
 * 
 */

/**
  * @module format_moetabs/moetabs
  */


define(['jquery'], function() {

    var moetabs = function() {

    	moetabs.prototype.init = function() {

    		$( '.sectionzerobtn' ).click(function() {
    			$( '#gridshadebox_content' ).removeClass( 'hide_content');
    			$( '#gridshadebox_close' ).css("display", "inline");
    			$( '#gridshadebox_content' ).addClass( 'absolute' );
    		});
    		
    		$( '#gridshadebox_close' ).click(function() {
    			$( '#gridshadebox_content' ).addClass( 'hide_content' );
    			$( '#gridshadebox_content' ).removeClass( 'absolute');
    		});
    		
        };

    };

    return new moetabs();
});