jQuery(document).ready(function($){
	 if($('.smtp-check').length >0 ){
	 	$('.smtp-check').on('click' , function(){
	 		var __this = $(this) ,tmm_ajax_img = $(this).find('.ajax-img').eq(0);
	 		tmm_ajax_img.show();
	 		_secure = __this.parents('span').eq(0).find('select').eq(0).val()
	 		$.ajax({
	 			url: ajaxurl,
	 			type: 'POST',
	 			dataType: 'json',
	 			data: {
	 				'action': 'tiniymassmailer_smtpcheck' ,
	 				'name':__this.parents('span').eq(0).find('.smtp-n').eq(0).val() , 
	 				'host':__this.parents('span').eq(0).find('.smtp-h').eq(0).val() , 
	 				'user':__this.parents('span').eq(0).find('.smtp-u').eq(0).val() ,
	 				'pass':__this.parents('span').eq(0).find('.smtp-p').eq(0).val() ,
	 				'port':__this.parents('span').eq(0).find('.smtp-po').eq(0).val(),
	 				'secure':_secure,

	 			},
	 		})
	 		.done(function(data) {
	 			tmm_ajax_img.hide();
	 			__this.html(data.msg);
	 			console.log("success");
	 		})
	 		.fail(function() {
	 			tmm_ajax_img.hide();
	 			console.log("error");
	 		})
	 		.always(function() {
	 			tmm_ajax_img.hide();
	 			console.log("complete");
	 		});
	 		
	 	});
	 }
	 
	 if($('#tinymassmailer_start_sending').length >0 ){
	 	$('#tinymassmailer_start_sending').on('click' , function(){
	 		tinyMCE.triggerSave();
	 		if($('#tinymassmailer_editor').val() == '') return;
	 		if($('#tinymassmailer_subject').val() == '') return;
	 		$.ajax({
	 			url: ajaxurl,
	 			type: 'POST',
	 			dataType: 'json',
	 			data: {action: 'tiniymassmailer_start_sending' , 'text' : $('#tinymassmailer_editor').val() , 'subject': $('#tinymassmailer_subject').val() , 'tinymassmailer_start_from':$('#tinymassmailer_start_from').val() },
	 		})
	 		.done(function(data) {
	 			alert(data.msg);
	 			$('.send-mail.add_new_send').slideUp();
	 			console.log("success");
	 		})
	 		.fail(function() {
	 			console.log("error");
	 		})
	 		.always(function() {
	 			console.log("complete");
	 		});
	 		
	 	});
	 }


	 $('.remove-me').on('click' , function(){
	 	$(this).parent('span').eq(0).remove();
	 });

	 $('.confirmshow-full-text').on('click' , function(e){
	 	// e.preventDefault();
	 });

});