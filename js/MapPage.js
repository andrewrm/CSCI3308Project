$(function () {
				//$('#DrinkMap').gmap();
	            var yourStartLatLng = new google.maps.LatLng(40.017131, -105.281990);
	            $('#DrinkMap').gmap({ 'center': yourStartLatLng, 'zoom': 14});
	            $('#DrinkMap').gmap('addMarker', { 'position': new google.maps.LatLng(40.017131, -105.281990) });
	            $('#DrinkMap').gmap('addMarker', { 'position': new google.maps.LatLng(40.018215, -105.281990) });
	            $('#DrinkMap').gmap('addMarker', { 'position': new google.maps.LatLng(40.019205, -105.272413) });
	            $('#DrinkMap').gmap('addMarker', { 'position': new google.maps.LatLng(40.019661, -105.279840 ) });
	        });