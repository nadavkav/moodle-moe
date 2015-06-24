$(function(){
	var message_count = 0;
	$('.block_messages .message > a').each(function(){message_count += ~~this.text;});
	console.log('Total Messages: '+message_count);
	$('.block_messages').prepend('<b class="message_count">'+message_count+'</b>');
});