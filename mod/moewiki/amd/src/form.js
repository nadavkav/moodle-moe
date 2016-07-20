/**
 * @package    mod_moewiki
 */

/**
 * @module mod_moewiki/form
 */

define(['jquery'], function($){
	function Form() {
		this.template_text = null;
	}
	
	Form.prototype.init = function(){
		var form = this;
		$('#id_subwikis').change(function(){
			if($(this).val() === "0"){
				form.template_text = $('#fitem_id_template_text').detach();
			} else {
				$('#fitem_id_template_file').after(form.template_text);
				form.template_text = null;
			}
		});
	};
	
	return new Form();
});