/**
 * 
 */

$(function () {
	//run only on tab mode
	if($(".nav-tabs").length) {
		var right = 0;
		var btnswidth;
		var direction = 0;
		var selectedpos;
		var firstright;
		
		//limit tabs area width to inject left and right buttons
		$(".nav-tabs").width($(".tabs-container").parent().width() - 91);
		
		//order the tabs
		$(".nav-tabs li").each(function (index) {
			
			$(this).css('position', 'absolute');
			$(this).css('right', right);
			$(this).css('top', 0);
			$(this).css('width', $(this).outerWidth());
			$(this).css('display', 'block');
			
			right = right + 3 + $(this).outerWidth();
		});
		if(right > $(".nav-tabs").width()){
			$('#tabmoveleft').css('display','block');
			$('#tabmoveright').css('display','block');
		}
		$(".nav-tabs").prepend('<li style="background-color: #FF0000; width: ' + right + 'px;  visibility: hidden;" >asdf</li>');
		$(".nav-tabs").addClass('dragscroll');
		
		$(".nav-tabs").animate({
			scrollLeft: $(".nav-tabs li.active :first").offset().left }, 200);
		
		$("#tabmoveright").on("click", function() {
			$(".nav-tabs").animate({
				scrollLeft: '+=70' }, 50);
		});
		
		$("#tabmoveleft").on("click", function() {
			$(".nav-tabs").animate({
				scrollLeft: '-=70' }, 50);
		});
		
		$(window).resize(function () {
			$(".nav-tabs").width($(".tabs-container").parent().width() - 91);
			$(".nav-tabs").animate({
				scrollLeft: $(".nav-tabs li.active :first").offset().left 
			}, 10);
		});
		
		
	}
});