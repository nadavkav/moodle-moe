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
		this.scrollWidth = ($('a.qnbutton').size()) * ($('a.qnbutton').width()+10);
		$('.allbuttons').width(this.scrollWidth);
	};
	
	Navigation.prototype.init = function(){
		this.checkPosition();
		this.scrollWidth = $('a.qnbutton').size() * $('a.qnbutton').width();
		$('.fa-caret-left').click(function(){
			$('.allbuttons').css('right', '-=324');
			Navigation.prototype.checkPosition();
		});
		$('.fa-caret-right').click(function(){
			$('.allbuttons').css('right', '+=324');
			Navigation.prototype.checkPosition();
		});
	};
	
	Navigation.prototype.checkPosition = function(){
		this.scrollWidth = $('a.qnbutton').size() * $('a.qnbutton').width();
		if($('.allbuttons').css('right').replace('px', '') === 0){
			$('.fa-caret-right').css('visibility', 'hidden');
		} else {
			$('.fa-caret-right').css('visibility', 'visible');
			if($('.allbuttons').css('right').replace('px', '') <= -this.scrollWidth){
				$('.fa-caret-left').css('visibility', 'hidden');
			} else {
				$('.fa-caret-left').css('visibility', 'visible');
			}
		}
	};
	
	return new Navigation();
});