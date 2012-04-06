$(function(){
	
	$('#usermedia-toggle').click(function(){
		if ($('#usermedia-toggle').val() == Drupal.settings.media_msgAdd) {
			// add it
			
			$.post("logtoggle/1/" + Drupal.settings.media_mid);
			$('#media-header-metadata-logstatus').html(Drupal.settings.logstatus);
			$('#usermedia-toggle').val(Drupal.settings.media_msgRemove);
			
		} else {
			// remove it
			$.post("logtoggle/0/" + Drupal.settings.media_mid);
			$('#media-header-metadata-logstatus').html("");
			$('#usermedia-toggle').val(Drupal.settings.media_msgAdd);
		}
	});
	
});
