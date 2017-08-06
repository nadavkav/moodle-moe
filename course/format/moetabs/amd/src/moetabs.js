/**
 * @package    format_moetabs
 * 
 */

/**
  * @module format_moetabs/moetabs
  */

define(['jquery'], function($) {
	
	/**
	 * @constructor
	 */
	var moetabs = function(){
		this.ruler = $('.format-moetabs ul.nav.nav-tabs');		
	};
	
	moetabs.prototype.init = function(){
		if(this.ruler.width() >= $('.format-moetabs .course-content .single-section').width()){
			var error;
			error = 'need arrow';
		}
	};
	
	return moetabs;
});