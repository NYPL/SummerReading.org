
$(function(){
	
	$('#edit-borough').change(function(){
	    //clear school input
		$('#edit-school').val('');
		//reset policy div
		if($('#edit-borough option:selected').text()=="Brooklyn") 
			$('#brooklyn_policy').css('display', '');
		else
			$('#brooklyn_policy').css('display', 'none');
	    if($('#edit-borough option:selected').text()=="Queens") 
			$('#queens_policy').css('display', '');
		else
			$('#queens_policy').css('display', 'none');
		
		//library card only for m, x, s
		if(!Drupal.settings.luid && ($('#edit-borough option:selected').text()=="Manhattan" || 
		  $('#edit-borough option:selected').text()=="Bronx" || $('#edit-borough option:selected').text()=="Staten Island"))
			$('#librarycard').css('display', '');
		else
			$('#librarycard').css('display', 'none');
			
		var option;
		var branches;

		$('#edit-branch').children().remove();
		$.cookie('summerreading_boroughSelect', $('#edit-borough option:selected').val());
		
		if ($('#edit-borough option:selected').text() == "-- Please select --") {
			option = document.createElement("option");
			option.value = "";
			option.text = "...";
			$('#edit-branch').get(0)[$('#edit-branch option').length] = option;
			$('#edit-branch-hidden').val('');
			return;
		}
		
		option = document.createElement("option");
		option.value = "";
		option.text = "-- Please select --";
		$('#edit-branch').get(0)[$('#edit-branch option').length] = option;

		switch ($('#edit-borough option:selected').text()) {
		case 'Bronx':
			branches = Drupal.settings.branchesBronx;
			break;
		case 'Brooklyn':
			branches = Drupal.settings.branchesBrook;
			break;
		case 'Manhattan':
			branches = Drupal.settings.branchesMan;
			break;
		case 'Staten Island':
			branches = Drupal.settings.branchesStat;
			break;
		case 'Queens':
			branches = Drupal.settings.branchesQueens;
			break;
		default:
			// not in nyc
			$('#edit-branch').children().remove();
			branches = Drupal.settings.branchesOther;
			
		}
		
		$.each(branches, function(key, value) { 
			option = document.createElement("option");
			option.value = key;
			option.text = value;
			$('#edit-branch').get(0)[$('#edit-branch option').length] = option;
		});
		
		$('#edit-branch').trigger("change"); 
		
	});
	

	$('#edit-branch').change(function(){
		$('#edit-branch-hidden').val($('#edit-branch option:selected').val());
		$.cookie(window.location + "_branchSelect", $('#edit-branch option:selected').val());
	});
	
	// default the selections
	var boroughChanged = false;
	
	if ($.cookie('summerreading_boroughSelect') != null && $.cookie('summerreading_boroughSelect') != '') {
		// cookie override
		$('#edit-borough').val($.cookie('summerreading_boroughSelect'));
		boroughChanged = true;
	}
	if (Drupal.settings.boroughSelect != '') {
		$('#edit-borough').val(Drupal.settings.boroughSelect);
		boroughChanged =  true;
	}
	if (boroughChanged)
		$('#edit-borough').trigger("change"); 
	
	if ($.cookie(window.location + "_branchSelect") != null && $.cookie(window.location + "_branchSelect") != '') {
		// cookie override
		$('#edit-branch').val($.cookie(window.location + "_branchSelect"));
		$('#edit-branch-hidden').val($.cookie(window.location + "_branchSelect"));
	}
	if (Drupal.settings.branchSelect != '') {
		// was set in profile
		$('#edit-branch').val(Drupal.settings.branchSelect);
		$('#edit-branch-hidden').val(Drupal.settings.branchSelect);
	}
	if (Drupal.settings.school != '') {
		 $('#edit-school').val(Drupal.settings.school);
	} 

});

