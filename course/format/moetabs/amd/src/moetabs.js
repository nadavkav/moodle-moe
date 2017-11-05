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
       		
    	Moetabs.prototype.init = function(tabpos) {
    		

    		var tabposGLOBAL = tabpos+1;
    		    		    		
    		$( ".format-moetabs .dragscroll li" ).each(function() {
    			
    			$(this).css("margin-right", "0px");
    		});
    		
    		$(".format-moetabs #tabmoveright").on("click", function() {
    			
    			var  aviablescrolltoleft=  $('.fa').width(); 
    			if ( $('.format-moetabs .dragscroll').scrollLeft() > aviablescrolltoleft) {
    			tabposGLOBAL--;
    			}
    					
    			if (tabposGLOBAL < 2 ) { // check the limit of the right side (its start from child two )
    				tabposGLOBAL = 2;
    			}
    			
    			
    			// hold the width size of the tab button for the scrolling value 
    			var scrolingvalue =  $(".format-moetabs .dragscroll li:nth-child("+ tabposGLOBAL +")").width();
 
    			$(".format-moetabs .nav-tabs").animate({
    				scrollLeft: '+='+scrolingvalue+'px' }, 50);
    			
    		});
    		
    		$(".format-moetabs #tabmoveleft").on("click", function() {
    	   		

    				
    			var maxelements = $(".format-moetabs .dragscroll").children().length;
    			
    			if (tabposGLOBAL > maxelements ) { // check the limit from the left side 
    				tabposGLOBAL = maxelements;
    			}
    			
    			if ( $('.format-moetabs .dragscroll').scrollLeft() > 0 ) {
    				
    			var scrolingvalue =  $(".format-moetabs .dragscroll li:nth-child("+ tabposGLOBAL +")").width();
    			
    			$(".format-moetabs .nav-tabs").animate({
    				scrollLeft: '-='+scrolingvalue+'px' }, 50);

    				tabposGLOBAL++;
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