define(['jquery'], function($){
	var ContentPreview = function() {};
	
	ContentPreview.prototype.init = function(cmid, url, additional, sess) {
		var cp = this;
		$("#id_preview").click(function () {
			$("input[name=savenshow]").val("1");
			$("#mform1").submit();
		});
		this.showform($('[name="contenttype"]:checked').val());
		$('[name="contenttype"]').click(function(event){
			var target = event.target;
			cp.showform($(target).val());
		});
		if(cmid !== null) {
			var win = window.open(url + "/mod/quizsbs/startattempt.php?cmid=" 
					+ cmid + "&additional=" + additional + "&sesskey=" + sess, "_blank");
			win.focus();
		}
	};
	
	ContentPreview.prototype.showform = function(val) {
		switch(val){
			case '1':
				$('#fitem_id_html_editor, #fitem_id_javascripteditor, #fitem_id_csseditor').css('display', 'none');
				$('#fitem_id_app').css('display','block');
				break;
			case '2':
				$('#fitem_id_app').css('display', 'none');
				$('#fitem_id_html_editor, #fitem_id_javascripteditor, #fitem_id_csseditor').css('display','block');
				break;
			default:
				$('#fitem_id_app, #fitem_id_javascripteditor, #fitem_id_csseditor').css('display', 'none');
				$('#fitem_id_html_editor').css('display','block');
				break;
		}
	};
	
	return new ContentPreview();
});