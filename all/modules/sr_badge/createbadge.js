$(function(){

	//$(document).ready(function(){
		$("#edit-details-codequantity1-wrapper").css("display", "none"); 
		
		$('#edit-details-badgetype').change(function(){
			$("#edit-details-codequantity1-wrapper").css("display", "none"); 
			
			//if ($('#edit-details-badgetype option:selected').val() == Drupal.settings.type_code) {
			//	$('#action-description').html("Enter the badge code. (spaces will be ignored)");
			//}
			
			switch ($('#edit-details-badgetype option:selected').val()) {
				case Drupal.settings.type_code:
					$('#action-description').html("Enter a 'secret' Badge Code. (alpha-numeric text, caps and spaces are ignored)");
					break;
				case Drupal.settings.type_login:
					$('#action-description').html("Enter a quantity of Website Logins.");
					break;
				case Drupal.settings.type_add2log:
					$('#action-description').html("Enter a quantity of Media Log Items.");
					break;
				case Drupal.settings.type_review:
					$('#action-description').html("Enter a quantity of Media Reviews.");
					break;
				case Drupal.settings.type_like:
					$('#action-description').html("Enter a quantity of Review Likes. (liking the reviews of others)");
					break;
				case Drupal.settings.type_wasliked:
					$('#action-description').html("Enter a quantity of Review Likes. (your reviews getting liked)");
					break;
				case Drupal.settings.type_code_add2log:
				{
					$('#action-description').html("Enter a 'secret' Badge Code. (alpha-numeric text, caps and spaces are ignored)");
					$("#edit-details-codequantity1-wrapper").css("display", "block"); 
					break;
				}
				default:
					$('#action-description').html("&nbsp;");
			};
		});
		
		if ($('#edit-details-badgetype option:selected').val()!= null && $('#edit-details-badgetype option:selected').val()!= '') {
			typeChanged =  true;
		}
		if (typeChanged)
			$('#edit-details-badgetype').trigger("change"); 
  // });	
});
