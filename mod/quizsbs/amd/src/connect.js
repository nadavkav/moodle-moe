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
* @module    mod_quizsbs/loadcintent
**/
define(['jquery', 'core/ajax', 'core/notification', 'core/templates'], function($, ajax, notification, templates){
	var Connect = function() {};
	
	Connect.prototype.init = function() {
		$('#id_contents option').click(function(event){
			var element = event.target;
			var data = [{
					'methodname': 'mod_quizsbs_get_content_preview',
				    'args': {
				    	'id': $(element).val()
				    }
				}
			];
			var promises = ajax.call(data);
			promises[0].done(function(response){
				$('#htmlcontent').html('');
				$('#csscontent').html('');
				$('#javascriptcontent').html('');
				for (var index in response) {
					switch (response[index].type) {
						case 0:
							$('#htmlcontent').html(response[index].content);
							break;
						case 3:
							$('#csscontent').html("<style>" + response[index].content + "</style>");
							break;
						case 2:
							$('#javascriptcontent').html("<script>require(['jquery'], function($) {" +
									response[index].content + "});</script>");
							break;
					}
				}
			});
		});
	};
	
	return new Connect();
});