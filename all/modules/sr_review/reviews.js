$(function(){
		/*preload popup window background image*/
		var popupImage = new Image();
		popupImage.src = "/sites/all/themes/summerreading/images/report-popup.png";

		/*modal window for review report */
		var modalWindow = {
			parent:"body",
			windowId:null,
			content:null,
			close:function()
			{
				$(".modal-window").remove();
				$("#review-report-overlay").remove();
			},
			open:function()
			{
				var modal = "";
				modal += "<div id=\"review-report-overlay\"></div>";
				modal += "<div id=\"" + this.windowId + "\" class=\"modal-window\">";
				modal += this.content;
				modal += "</div>";	

				$(this.parent).append(modal);

				$("#close-btn").click(function(){modalWindow.close(); return false;});
				$("#mail").click(function(){modalWindow.close();});
				$("#review-report-overlay").click(function(){modalWindow.close();});
			}
	    };



	// show review entry box
	$('.add-review').live('click', function(){
		var mid = $.string($(this).attr("id")).gsub(/.*[__]/, "").str;
		var warning = "<div class='review-safety'>" + Drupal.settings.review_warning + "</div>";
		var box = "<textarea name='review-text__" + mid + "' rows='12' cols='40'></textarea><br>";
		var btnSave = "<input type='button' class='review-text-save' id='review-text-save__" + mid + "' value='Save Review' />";
		var btnCancel = "<input type='button' class='review-text-cancel' id='review-text-cancel__" + mid + "' value='Cancel' />";
		$('#media-reviews-add__' + mid).html("<form class='media_reviews-form' id='media-reviews-form__" + mid + "'>" + warning + box + btnSave + " " + btnCancel + "</form>");
		
	});

	// cancel review entry box
	$('input.review-text-cancel').live('click', function(){
		var mid = $.string($(this).attr("id")).gsub(/.*[__]/, "").str;
		// when changing div internal html, update also sr_review.module file
		var addReview = "<a href='javascript:void();' class='add-review' id='add-review__" + mid + "'></a>";
		$('#media-reviews-add__' + mid).html(addReview);
	});

	// save review
	$('input.review-text-save').live('click', function(){
		var mid = $.string($(this).attr("id")).gsub(/.*[__]/, "").str;
		$.post(Drupal.settings.basePathFull + "savereview/" + mid, $('#media-reviews-form__' + mid).serialize(), function(data) {
			$('#media-reviews__' + mid).load(Drupal.settings.basePathFull + "reviews/" + mid + "/" + Drupal.settings.review_caller);
		});
	});

	// like review
	$('a.review-likethis').live('click', function(){
		var rid = $.string($(this).attr("id")).gsub(/.*[__]/, "").str;
		$.post(Drupal.settings.basePathFull + "savelike/" + rid, function(data) {
			$('#media-reviews-item__' + rid).load(Drupal.settings.basePathFull + "review/" + rid + "/" + Drupal.settings.review_caller);
		});	  
	});
	
    // report review
	$('a.review-reportthis').live('click', function(){
	
		var rid = $.string($(this).attr("id")).gsub(/.*[__]/, "").str;
		$.post(Drupal.settings.basePathFull + "report/" + rid, function(data) {
			var openMyModal = function()
			{
				modalWindow.windowId = "myModal";
				modalWindow.content = '<div id="review-popup"><div id="review-popup-inner"><h1>We\'re On It!</h1><p>Thank you for reporting this review.</p>';
				modalWindow.content += '<p>We\'ll be checking in to this topic.</p><p id="review-popup-submit">To submit a message to the Administrator, '; 
				modalWindow.content += '<a href="mailto:summerreading@nypl.org" id="mail">click here</a></p><a href="#" id="close-btn"></a></div></div>';
				modalWindow.open();
			};			
			openMyModal();
		});	  
	});
	
	// see more review
	$('a.review-content-more').live('click', function(){
		var rid = $.string($(this).attr("id")).gsub(/.*[__]/, "").str;
		$('#review-content__' + rid).load(Drupal.settings.basePathFull + "reviewbody/" + rid); 
	});

	
	// see more medialog items
	$('a.seemore').live('click', function(){
		var vars = $.string($(this).attr("id")).gsub(/.*[__]/, "").str;
		var elem = vars.split("|");
		var pageno = elem[0];
		var uid = elem[1];
		$('#media-log-page__' + pageno).load(Drupal.settings.basePathFull + "medialogpage/" + pageno + "/" + uid, function(response, status, xhr) {
				//alert(response);
				$('input.fivestar-submit').css('display', 'none');
				$('#media-log-page__' + pageno + '.media-log-page .fivestar-widget').fivestar();
		});
	});	
	
	/*
	// see more medialibrarylog items
	$('a.seemorelog').live('click', function(){
		var vars = $.string($(this).attr("id")).gsub(/.*[__]/, "").str;
		var elem = vars.split("|");
		var pageno = elem[0];
		var uid = elem[1];
		$('#media-log-page__' + pageno).load(Drupal.settings.basePathFull + "medialibrarylog/" + pageno + "/" + uid, function(response, status, xhr) {
				//alert(response);
				
		});
		
	});
	*/
	 
});
