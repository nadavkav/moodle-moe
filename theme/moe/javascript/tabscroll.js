/**
 * 
 */

$(function () {
	//run only on tab mode
	if($(".nav-tabs").length && !$(".path-mod-glossary .nav-tabs").length && !$("#page-mod-lightboxgallery-imageedit .nav-tabs").length) {
		var right = 0;
		var btnswidth;
		var direction = 0;
		var selectedpos;
		var firstright;
		
		//limit tabs area width to inject left and right buttons
		$(".nav-tabs").first().width($(".tabs-container").parent().width() - 91);
		
		//order the tabs
//		$('.nav-tabs').first().children('li').each(function (index) {
//			
//			$(this).css('position', 'absolute');
//			$(this).css('right', right);
//			$(this).css('top', 0);
//			$(this).css('width', $(this).outerWidth());
//			$(this).css('display', 'block');
//			
//			right = right + 3 + $(this).outerWidth();
//		});
		if(right > $(".nav-tabs").first().width()){
			$('#tabmoveleft').css('display','block');
			$('#tabmoveright').css('display','block');
		}
		$(".nav-tabs").first().prepend('<li style="background-color: #FF0000; width: ' + right + 'px;  visibility: hidden;" >asdf</li>');
		$(".nav-tabs").first().addClass('dragscroll');
		
		//figure at what left to put the selected tab
		var activeLiPos = $(".nav-tabs").first().offset().left + $(".nav-tabs").width();
		
		if($(".nav-tabs li.active :first").offset().left < $(".nav-tabs").first().offset().left + $(".tabs-container").parent().width()) {
			var offset = ($(".nav-tabs li.active :first").offset().left - activeLiPos) + $(".nav-tabs li.active :first").width();
			$(".nav-tabs").first().animate({
				scrollLeft: '+=' + offset }, 50);
		}

		$("#tabmoveright").on("click", function() {
			$(".nav-tabs").animate({
				scrollLeft: '+=70' }, 50);
		});
		
		$("#tabmoveleft").on("click", function() {
			$(".nav-tabs").animate({
				scrollLeft: '-=70' }, 50);
		});
		
		$(window).resize(function () {
			$(".nav-tabs").first().width($(".tabs-container").parent().width() - 91);
					
			var activeLiPos = $(".nav-tabs").offset().left + $(".nav-tabs").first().width();
			
			if($(".nav-tabs li.active :first").offset().left < $(".nav-tabs").offset().left + $(".tabs-container").parent().width()) {
				var offset = ($(".nav-tabs li.active :first").offset().left - activeLiPos) + $(".nav-tabs li.active :first").width();
				$(".nav-tabs").first().animate({
					scrollLeft: '+=' + offset }, 50);
			}
		});
		
		
	}
});