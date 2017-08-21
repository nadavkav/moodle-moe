/**
 * @package format_moetabs
 * 
 */

/**
  * @module format_moetabs/moetabs
  */


define(['jquery'], function($) {
		
    var Moetabs = function() {
    	
        this.ruler = $('.format-moetabs ul.nav.nav-tabs');
       		
    	Moetabs.prototype.init = function() {
    		

    		$( window ).resize(function() {
    			
                var btnsize = $('.format-moetabs .sectionzerotext').width() - 
    			( $('.format-moetabs .sectionzeroimg').width() +  
    					 ( parseInt($(".format-moetabs .sectionzeroimg").css("margin-left").replace("px","")) * 2 ) );
                var minbtnsize = 610;
 			    if ( btnsize < minbtnsize ) {
    				$( '.format-moetabs .sectionzerobtn' ).css("position", "unset");
    				$('.format-moetabs .littlesquare').hide();
    				var containersize = $('.format-moetabs .sectionzerotext').width();
    				$( '.format-moetabs .sectionzerobtn' ).css("width", containersize); 
    			} else {
    				$( '.format-moetabs .sectionzerobtn' ).css("position", "absolute"); 
    				$('.format-moetabs .littlesquare').show();
    				if ( window.fullScreen ) {
          			  $( '.format-moetabs .sectionzerobtn' ).css("width", "766px");
      				} else {
      					$( '.format-moetabs .sectionzerobtn' ).css("width", btnsize );
      				}
    				
    			} 
    		});
    		
    		if(this.ruler.width() >= $('.format-moetabs .course-content .single-section').width()){
    			var error;
    			error = 'need arrow';
    		}
    		   		
    		$( '.format-moetabs .sectionzerobtn' ).click(function() {
    			$( '.format-moetabs #gridshadebox_content' ).removeClass( 'hide_content');
    			$( '.format-moetabs #gridshadebox_close' ).css("display", "inline");
    			$( '.format-moetabs #gridshadebox_content' ).addClass( 'absolute' );
    			$( '.format-moetabs #gridshadebox_overlay' ).css("display", "block"); 
    		});
    		
    		$( '.format-moetabs #gridshadebox_close' ).click(function() {
    			$( '.format-moetabs #gridshadebox_content' ).addClass( 'hide_content' );
    			$( '.format-moetabs #gridshadebox_content' ).removeClass( 'absolute');
    			$( '.format-moetabs #gridshadebox_overlay' ).css("display", "none");
    		});
    		
        };

    };

    return new Moetabs();
});