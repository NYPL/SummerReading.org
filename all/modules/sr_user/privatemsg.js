function movemsg()
{    
	if(Drupal.jsEnabled){
		$(document).ready(function(){
			  $('a.message_link').click(function(){ 
				$("#inbox-inner p, #inbox-inner .privatemsg-subject, #inbox-inner .privatemsg-message-body").fadeOut(200, function(){ /*callback function*/ });
				var moveTo=function(data){
					 //alert(data.data);
					 $('#msg_box').html(data.data);
				}
				$.ajax({
					type:'POST',
					url:this.href,
					dataType:'json',
					success: moveTo,
					data:'js=1'
				});
				return false;
			});
		});
	}
}
