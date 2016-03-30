/**
 * 
 */

$(function () {
	//run only on tab mode
	if($(".format-onetopic .nav-tabs").length) {
		var right = 0;
		var btnswidth;
		var direction = 0;
		var selectedpos;
		var firstright;
		
		//limit tabs area width to inject left and right buttons
		$(".format-onetopic .nav-tabs").width($(".format-onetopic .single-section.onetopic").width() - 91);
		
		//order the tabs
		$(".format-onetopic .nav-tabs li").each(function (index) {
			
			$(this).css('position', 'absolute');
			$(this).css('right', right);
			$(this).css('top', 0);
			$(this).css('width', $(this).outerWidth());
			$(this).css('display', 'block');
			
			right = right + 3 + $(this).outerWidth();
		});
		$(".format-onetopic .nav-tabs").prepend('<li style="background-color: #FF0000; width: ' + right + 'px;  visibility: hidden;" >asdf</li>');
		$(".format-onetopic .nav-tabs").addClass('dragscroll');
		
		$(".format-onetopic .nav-tabs").animate({
											scrollLeft: $(".format-onetopic .nav-tabs li.active :first").offset().left 
										}, 200);
		
		$("#tabmoveright").on("click", function() {
			$(".format-onetopic .nav-tabs").animate({
				scrollLeft: '+=70' }, 50);
		});
		
		$("#tabmoveleft").on("click", function() {
			$(".format-onetopic .nav-tabs").animate({
				scrollLeft: '-=70' }, 50);
		});
		
		$(window).resize(function () {
			$(".format-onetopic .nav-tabs").width($(".format-onetopic .single-section.onetopic").width() - 91);
			$(".format-onetopic .nav-tabs").animate({
				scrollLeft: $(".format-onetopic .nav-tabs li.active :first").offset().left 
			}, 10);
		});
		
		
	}
});