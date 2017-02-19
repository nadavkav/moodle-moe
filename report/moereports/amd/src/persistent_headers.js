define(['jquery'], function($) {
	
	var floatingheaders = function() {
	    };
	

// DOM Ready      
floatingheaders.prototype.init = function() {
   var clonedHeaderRow;

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
   
   
   
function UpdateTableHeaders() {
	   $(".persist-area").each(function() {
	   
	       var el             = $(this),
	           offset         = el.offset(),
	           scrollTop      = $(window).scrollTop(),
	           floatingHeader = $(".floatingHeader", this)
	       
	       if ((scrollTop > offset.top) && (scrollTop < offset.top + el.height())) {
	           floatingHeader.css({
	            "visibility": "visible"
	           });
	       } else {
	           floatingHeader.css({
	            "visibility": "hidden"
	           });      
	       };
	       $(".floatingHeader th").each(function(index){
	   	    var index2 = index;
	   	    $(this).width(function(index2){
	   	        return $(".persist-area th").eq(index).width()+1;
	   	    });
	   	});
	   });
	}
}
return new floatingheaders;
});
