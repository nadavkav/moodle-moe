define(['jquery'], function($) {
	
	var Floatingheaders = function() {
	    };


// DOM Ready      
Floatingheaders.prototype.init = function() {
   var clonedHeaderRow;
   
	function UpdateTableHeaders() {
		   $(".persist-area").each(function() {
		   
		       var el             = $(this),
		           offset         = el.offset(),
		           scrollTop      = $(window).scrollTop(),
		           floatingHeader = $(".floatingHeader", this);
		       
		       if ((scrollTop > offset.top) && (scrollTop < offset.top + el.height())) {
		           floatingHeader.css({
		            "visibility": "visible"
		           });
		       } else {
		           floatingHeader.css({
		            "visibility": "hidden"
		           });      
		       }
		       $(".floatingHeader th").each(function(index){
		   	    $(this).width(function(){
		   	        return $(".persist-header th").eq(index).width()+1;
		   	    });
		   	});
		   });
		}
   $(".persist-area").each(function() {
       clonedHeaderRow = $(".persist-header", this);
       clonedHeaderRow
         .before(clonedHeaderRow.clone())
         .css("width", clonedHeaderRow.width())
         .addClass("floatingHeader");
         
   });
   
   $(window)
    .scroll(UpdateTableHeaders)
    .trigger("scroll");

   $(document).ready(function() {
     
     function stop(){
 		clearInterval(interval);
     }  
     function update(){
    		var arrOfTable1=[],
    	    i=0;

    		$('.persist-header th').each(function() {
    			var mWid = $(this).width()+1; 
    			arrOfTable1.push(mWid);
    		});

    		$('.floatingHeader th').each(function() {
    			$(this).css("min-width",arrOfTable1[i]+"px");
    			$(this).css("background-color","white");
    			i++; 
    		});

    	}
 	function finddiv(){
		if( document.getElementById("fullscreenpadding") && (document.getElementById("fullscreenfloat"))){
			document.getElementById("fullscreenpadding").addEventListener("click", update);
			document.getElementById("fullscreenfloat").addEventListener("click", update);	
			stop();
		}
	}
    var interval = setInterval(finddiv, 1000);

   });	

};



return new Floatingheaders();
});
