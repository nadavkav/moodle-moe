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
    			
            // founction to place the button acording to the windows size 
    		var btnresponsive = function() {
    			
                var btnsize = $('.format-moetabs .sectionzerotext').width() - 
    			( $('.format-moetabs .sectionzeroimg').width() +  
    					 ( parseInt($(".format-moetabs .sectionzeroimg").css("margin-left").replace("px","")) ) );
                var minbtnsize = 610;
 			    if ( btnsize < minbtnsize ) {
    				$( '.format-moetabs .sectionzerobtn' ).css("position", "unset");
    				$('.format-moetabs .littlesquare').hide();
    				$('.format-moetabs .sectionzerotext' ).css("margin-top", "10px");
    				$('.format-moetabs .sectionzerotext' ).css("display", "inline-block");
    				$( '.format-moetabs .sectionzerobtn' ).css("margin-bottom", "25px");
    				var containersize = $('.format-moetabs .sectionzerotext').width();
    				$( '.format-moetabs .sectionzerobtn' ).css("width", containersize); 
    			} else {
    				$( '.format-moetabs .sectionzerobtn' ).css("position", "absolute"); 
    				$('.format-moetabs .littlesquare').show();
    				$('.format-moetabs .sectionzerotext' ).css("margin-top", "0px");
       				$('.format-moetabs .sectionzerotext' ).css("display", "block");
    				if ( window.fullScreen ) {
          			  $( '.format-moetabs .sectionzerobtn' ).css("width", "766px");
      				} else {
      					$( '.format-moetabs .sectionzerobtn' ).css("width", btnsize );
      				}
    				
    			} 
    		};
    		
    		 //  defind the button size at firs load for mobile mevice
    		$( document ).ready( btnresponsive );

    		 //  defind the button size for pc responsive
    		$( window ).resize(function() {
    			btnresponsive();
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