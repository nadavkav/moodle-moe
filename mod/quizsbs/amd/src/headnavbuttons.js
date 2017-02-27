define(['jquery'], function($) {

	var Nav = function() {
	};

	Nav.prototype.init = function(report) {
	
		$(document).ready(function() {
			$('.mod_quizsbs-next-nav-show').click(function(){
				$('.mod_quizsbs-next-nav').trigger("click");
			});
   	
			$('.mod_quizsbs-prev-nav-show').click(function(){
				$('.mod_quizsbs-prev-nav').trigger("click");
			});

    
			if (! $( '.mod_quizsbs-prev-nav' ).length ) {   	 
				$( '.mod_quizsbs-prev-nav-show' ).hide();
			}
			$('.mod_quizsbs-next-nav-show').val($('.mod_quizsbs-next-nav').val());
		});
	}
	return new Nav;
});