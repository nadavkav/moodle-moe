$(function(){
	var floatstyle = '; float: right !important;';
	$('#page-footer').append('<button id="desktopview">Toggle Desktop View</button>');
	if (localStorage.getItem('desktopstate') == 'zoomed')
	{
		$('body').addClass('desktopview');
		if ($('#region-main').next('#block-region-side-post').length) // if the sidebar is before the main content float the sidebar won't work
		{
			$('#region-main').before($('#block-region-side-post'));
			$('#block-region-side-post').attr('style', function(i,s) { s = s?s:''; return s +  floatstyle; });
			localStorage.setItem('shiftedsidebar', '1');
		}
	}
	$('button#desktopview').click(function(){
		if (localStorage.getItem('desktopstate') == 'zoomed')
		{
			$('body').removeClass('desktopview');
			localStorage.setItem('desktopstate', '');
			if (localStorage.getItem('shiftedsidebar') == '1') // if the sidebar was moved, restore it
			{
				$('#region-main').after($('#block-region-side-post'));
				$('#block-region-side-post').attr('style', function(i,s) { s = s?s:''; return s.replace(floatstyle, ''); });
				localStorage.setItem('shiftedsidebar', '0');
			}
		} else {
			$('body').addClass('desktopview');
			localStorage.setItem('desktopstate', 'zoomed');
			if ($('#region-main').next('#block-region-side-post').length)
			{
				$('#region-main').before($('#block-region-side-post'));
				$('#block-region-side-post').attr('style', function(i,s) { s = s?s:''; return s + floatstyle; });
				localStorage.setItem('shiftedsidebar', '1');
			}
		}
	});
});
