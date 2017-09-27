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
    			
/*    		    var btnsize = $('.format-moetabs #section-0 p').width();
    		    var zeroimgwidth = $('.format-moetabs .sectionzeroimg').width();
    		    $( '.format-moetabs .sectionzerobtn' ).css("margin-right", zeroimgwidth+"px" );*/
/*            // founction to place the button acording to the windows size 
    		var btnresponsive = function() {
    			
    			var btnsize = $('.format-moetabs #section-0 p').width();
                var btnheight = $('.format-moetabs #section-0').height()*2;
                var zeroimgwidth = $('.format-moetabs .sectionzeroimg').width();
                var zeroimgwidthwithmargin = $('.format-moetabs .sectionzeroimg').width() +  
				 ( parseInt($(".format-moetabs .sectionzeroimg").css("margin-left").replace("px","")) );
           
                $( '.format-moetabs .sectionzerobtn' ).css("top", btnheight+"px");
                
                var minbtnsize = 530; 
                
 			    if (  btnsize < minbtnsize ) {

 	        		$( '.format-moetabs .sectionzerobtn' ).css("left", zeroimgwidth+"px");
    				$( '.format-moetabs .sectionzerobtn' ).css("position", "unset");
    				$('.format-moetabs .littlesquare').hide();
    				$('.format-moetabs .sectionzerotext' ).css("display", "inline-block");
    				$( '.format-moetabs .sectionzerobtn' ).css("width", "100%" );
    			} else {

 	        		$( '.format-moetabs .sectionzerobtn' ).css("left", zeroimgwidthwithmargin+"px");
    				$( '.format-moetabs .sectionzerobtn' ).css("position", "absolute"); 
    				$('.format-moetabs .littlesquare').show();
       				$('.format-moetabs .sectionzerotext' ).css("display", "block");
       				$( '.format-moetabs .sectionzerobtn' ).css("width", btnsize );
    				
    			} 
    		};
    		
    		// show the button positin ofther calc for cleaner view
    		$( '.format-moetabs .sectionzerobtn' ).css("opacity", "unset");
    		
    		 //  defind the button size at firs load for mobile mevice
    		$( document ).ready( btnresponsive );

    		 //  defind the button size for pc responsive
    		$( window ).resize(function() {
    			btnresponsive();
    		});*/
    		
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