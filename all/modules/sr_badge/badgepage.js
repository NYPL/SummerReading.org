$(function(){
	
	// see more winners
	$('a.seemore').live('click', function(){
		var vars = $.string($(this).attr("id")).gsub(/.*[__]/, "").str;
		var elem = vars.split("|");
		var pageno = elem[0];
		var bid = elem[1];
		$('#badge-winners__' + pageno).load(Drupal.settings.basePathFull + "badgewinners/" + pageno + "/" + bid);
	});
	
});