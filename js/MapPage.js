$(function () {
				$('#DrinkMap').gmap();
	            var yourStartLatLng = new google.maps.LatLng(40.017131, -105.281990);
	            $('#DrinkMap').gmap({ 'center': yourStartLatLng, 'zoom': 4});
	            $('#DrinkMap').gmap('addMarker', { 'position': new google.maps.LatLng(40.017131, -105.281990) });
	            $('#DrinkMap').gmap('addMarker', { 'position': new google.maps.LatLng(40.018215, -105.281990) });
	            $('#DrinkMap').gmap('addMarker', { 'position': new google.maps.LatLng(42.37, -71.13) });
	            $('#DrinkMap').gmap('addMarker', { 'position': new google.maps.LatLng(47.6097, -122.1419 ) });
	        });