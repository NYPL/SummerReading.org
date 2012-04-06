var map;

function initialize() {
  var markers = Drupal.settings.markers;
  
  var myLatlng = new google.maps.LatLng(markers[1].split('|')[1],markers[1].split('|')[2]);
  var myOptions = {
    zoom: 12,
    center: myLatlng,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  }

  map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
  
  for (var i = 0; i < markers.length; i++) {
    
	var markerArray=markers[i].split('|');
	var lat=markerArray[1];
	var lon=markerArray[2];
	var name=markerArray[0];
	var address=markerArray[3];
	var dst=markerArray[4];
	var total=markerArray[5];
	var total2=markerArray[6];
	var location = new google.maps.LatLng(lat,lon);
	var icon='/sites/summerreading.org/files/images/circle_red.png'
	
	if(total>=1000)
	  icon='/sites/summerreading.org/files/images/circle_green.png'
	if(total>=500 && total<1000 )
	  icon='/sites/summerreading.org/files/images/circle_yellow.png'
	
	var marker = new google.maps.Marker({
		position: location, 
		map: map,
		icon: icon
	});
	
	//marker.setTitle(name);
	attachSecretMessage(marker, name, address, dst, total, total2);
  }
}

// The five markers show a secret message when clicked
// but that message is not within the marker's instance data
function attachSecretMessage(marker, name, address, dst, total, total2) {
  var infowindow = new google.maps.InfoWindow(
      { 
	    content: '<b><a href="/'+dst+'">'+name+'</a><br/>Participants: '+total+'<br/>Books Logged: '+total2+'</b><br/>'+address,
        size: new google.maps.Size(50,50)
      });
  google.maps.event.addListener(marker, 'click', function() {
    infowindow.open(map,marker);
  });
}

function loadScript() {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.src = "http://maps.google.com/maps/api/js?sensor=false&callback=initialize";
    document.body.appendChild(script);
}

window.onload = loadScript;

