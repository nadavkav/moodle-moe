define(['jquery'], function($){
	var ContentPreview = function() {};
	
	ContentPreview.prototype.init = function(cmid, url, additional, sess) {
		$("#id_preview").click(function () {
			$("input[name=savenshow]").val("1");
			$("#mform1").submit();
		});
		
		if(cmid !== null) {
			var win = window.open(url + "/mod/quizsbs/startattempt.php?cmid=" 
					+ cmid + "&additional=" + additional + "&sesskey=" + sess, "_blank");
			win.focus();
		}
	};
	
	
	return new ContentPreview();
});