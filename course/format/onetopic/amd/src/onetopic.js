/**
 * @package    format_onetopic
 * 
 */

/**
  * @module format_onetopic/onetopic
  */

define(['jquery'], function($) {
	
	/**
	 * @constructor
	 */
	var onetopic = function(){
		this.ruler = $('.format-onetopic ul.nav.nav-tabs');		
	};
	
	onetopic.prototype.init = function(){
		if(this.ruler.width() >= $('.format-onetopic .course-content .single-section').width()){
			var error;
			error = 'need arrow';
		}
	};
	
	return onetopic;
});