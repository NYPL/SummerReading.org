$(function(){
	
	$('#custom-add-below').click(function(){
		
		var mediatype;
		var dest;
		mediatype = $('input.mediatype_below:checked').val();
		
		switch (mediatype) {
		case '0':
			dest = Drupal.settings.mediaURLBook;
			break;
		case '1':
			dest = Drupal.settings.mediaURLVideo;
			break;
		case '2':
			dest = Drupal.settings.mediaURLMusic;
			break;
		case '3':
			dest = Drupal.settings.mediaURLGame;
			break;
		default:
			dest = Drupal.settings.mediaURLBook;
		}
		window.location = dest;
	});

	$('#custom-add-above').click(function(){
		
		var mediatype;
		var dest;
		mediatype = $('input.mediatype_above:checked').val();
		
		switch (mediatype) {
		case '0':
			dest = Drupal.settings.mediaURLBook;
			break;
		case '1':
			dest = Drupal.settings.mediaURLVideo;
			break;
		case '2':
			dest = Drupal.settings.mediaURLMusic;
			break;
		case '3':
			dest = Drupal.settings.mediaURLGame;
			break;
		default:
			dest = Drupal.settings.mediaURLBook;
		}
		window.location = dest;
	});

});
