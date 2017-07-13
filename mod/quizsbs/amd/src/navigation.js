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
* @package   mod_quizsbs
* @module    mod_quizsbs/navigation
**/

define(['jquery'], function($){
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
			var initialoffset = this.buttonSize * (this.currentButton - Math.round((this.maxButtonInScroller/2)));
			if(initialoffset < 0) {
				initialoffset = 0;
			}
		}
		
		if(initialoffset && $('.qnbutton').length > 10) {
			$('.allbuttons').css('right', -initialoffset);
		}
		this.checkPosition();
		$('.fa-caret-left').click(function(){
			if(parseInt($('.allbuttons').css('right').replace('px', '')) -324 < -scrollWidth) {
				$('.allbuttons').css('right', -scrollWidth + $('#scrollbar').width()*23/24);
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
				
		//adjust question side to the content side
		
		self = this;
		$( document ).ready(function() {
			//fix iframe size
			
			if ($('#app').length > 0){
				var x  = $('#app')[0].offsetHeight;
				if (x < 600 ) {
					x = 600;
					$('#app').height(x);
				}
				xwithpadding = x * 1.1;
			    $('#addtional_content').css('height', xwithpadding+'px');
			    $('#addtional_content .wraper').css('height', '97%');
			    $('#quizsbs_question').css('height', xwithpadding+'px');
			    if (self.pix2int($('.wraper').css("height")) > self.pix2int($('#questionbox').css("height"))) {
					var questionbixhight = self.pix2int($('#quizsbs_question').css('height'));
					$('#questionbox').css("height",questionbixhight - questionbixhight*0.03 - self.pix2int($('#subjectheader').css("height"))-14);
				}
			} else {
				var questionbixhight = self.pix2int($('#quizsbs_question').css('height'));
				$('#questionbox').css("height",self.pix2int($('#addtional_content').css('height'))  - self.pix2int($('#subjectheader').css("height"))-14);
			}
			
			if ($('.droparea').css('width') != $('.dropbackground').css('width')) {
				$('.ddarea').css('width',
					this.pix2int($('.dropbackground').css("width")));
				$('.droparea').css("width",
					this.pix2int($('.dropbackground').css("width")));
			}
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