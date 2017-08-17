// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
* @package   mod_moeworksheets
* @module    mod_moeworksheets/navigation
**/

define(['jquery','jqueryui'], function($, jqui){
	var Navigation = function(){
		this.buttonSize = $('.qnbutton').width()+2+3.9;
		this.scrollWidth = ($('.qnbutton').size()) * this.buttonSize;
		$('.allbuttons').width(this.scrollWidth);
		this.currentButton = parseInt($('.qnbutton.thispage').data('quizsbs-page'))+1;
		this.maxButtonInScroller = Math.floor(this.scrollWidth/this.buttonSize) - 1;
	};
	
	Navigation.prototype.pix2int = function(pix) {
		return parseInt(pix.replace('px', ''));
	};
	
	Navigation.prototype.init = function() {
		var initialoffset = 0;
		if(this.currentButton >= this.maxButtonInScroller) {
			initialoffset = this.buttonSize * (this.currentButton - Math.round((this.maxButtonInScroller/2)));
			if(initialoffset < 0) {
				initialoffset = 0;
			}
		}
		
		if(initialoffset && $('.qnbutton').length > 10) {
			$('.allbuttons').css('right', -initialoffset);
		}
		this.checkPosition();
		$('.fa-caret-left').click(function(){
			if(parseInt($('.allbuttons').css('right').replace('px', '')) -324 < -this.scrollWidth) {
				$('.allbuttons').css('right', -this.scrollWidth + $('#scrollbar').width()*23/24);
			} else {	
				$('.allbuttons').css('right', '-=324');
			}
			Navigation.prototype.checkPosition();
		});
		$('.fa-caret-right').click(function(){
			if(parseInt($('.allbuttons').css('right').replace('px', '')) + 324 <= 0){
				$('.allbuttons').css('right', '+=324');
			} else {
				$('.allbuttons').css('right', '0');
			}
			Navigation.prototype.checkPosition();
		});
		$('.mod_moeworksheets-next-nav-show').click(function(){
			$('.mod_moeworksheets-next-nav').trigger("click");
		});
	
		$('.mod_moeworksheets-prev-nav-show').click(function(){
			$('.mod_moeworksheets-prev-nav').trigger("click");
		});


		if (! $( '.mod_moeworksheets-prev-nav' ).length ) {   	 
			$( '.mod_moeworksheets-prev-nav-show' ).hide();
		}
		$('.mod_moeworksheets-next-nav-show').val($('.mod_moeworksheets-next-nav').val());
		
		if (this.pix2int($('.wraper').css("height")) > this.pix2int($('#questionbox').css("height"))) {
			$('#questionbox').css("height",this.pix2int($('.wraper').css("height")));
		}
		if ($('.droparea').css('width') != $('.dropbackground').css('width')) {
			$('.ddarea').css('width',
					this.pix2int($('.dropbackground').css("width")));
			$('.droparea').css("width",
					this.pix2int($('.dropbackground').css("width")));
		}
		
		$(document).ready(function() {
		    	$("#backtocourse").click(function(){        
		    	    $.post(window.location.href, $("#responseform").serialize(), function(data) {

		    	    });
		        window.location.replace($(this).attr("href"));
		    });
		});	
	};
	
	Navigation.prototype.checkPosition = function(){
		this.scrollWidth = $('.qnbutton').size() * ($('.qnbutton').width()+6);
		if(this.scrollWidth > $('#scrollbar').width()){
			if($('.allbuttons').css('right').replace('px', '') >= 0){
				$('.fa-caret-right').css('visibility', 'hidden');
				$('.fa-caret-right').css('margin-right', '-60px');
				$('.fa-caret-left').css('visibility', 'visible');
			} else {
				$('.fa-caret-right').css({
					'visibility': 'visible',
					'margin-right': '0px'
				});
				$('.fa-caret-right').removeAttr('dispalay');
				if($('.allbuttons').css('right').replace('px', '')  <= -this.scrollWidth + $('#scrollbar').width() * 23/24){
					$('.fa-caret-left').css('visibility', 'hidden');
				} else {
					$('.fa-caret-left').css('visibility', 'visible');
				}
			}
		} else {
			$('.fa-caret-left').css('visibility', 'hidden');
			$('.fa-caret-right').css('visibility', 'hidden');
			$('.fa-caret-right').css('display', 'none');
		}
	};
	
	return new Navigation();
});