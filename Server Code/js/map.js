/* Author: 
	Varun Varada
*/

var MarkerGroup = function () {
	this.markers = new Array();
	this.getMarkerById = function ( id ) {
		for ( var i = 0; i < this.markers.length; i++ ) {
			var marker = this.markers[i];
			if ( marker.id == id ) { 
				return {index: i, marker: marker};
			}
		}
		return false;
	};
	this.addMarker = function ( marker ) {
		this.markers.push( marker );
	};
	this.removeMarker = function ( id ) {
		var i = this.getMarkerById( id );
		if ( i && i.index ) {
			this.markers.splice( i.index, 1 );
		}
	};
	this.showMarker = function ( marker, m ) {
		if ( typeof marker == 'string' ) {
			var i = this.getMarkerById( marker );
			if( i && i.marker && i.marker.getMap() != m ) {
				i.marker.setAnimation( map.markerAnimation );
				i.marker.setMap( m );
			}
		} else if ( typeof marker == 'number' && this.markers[marker].getMap() != m ) {
			this.markers[marker].setAnimation( map.markerAnimation );
			this.markers[marker].setMap( m );
		} else if ( typeof marker == 'object' && marker.getMap() != m ) {
			marker.setAnimation( map.markerAnimation );
			marker.setMap( m );
		}
	};
	this.hideMarker = function ( marker ) {
		if ( typeof marker == 'string' ) {
			var i = this.getMarkerById( marker );
			if( i && i.marker ) {
				i.marker.setMap( null );
			}
		} else if ( typeof marker == 'number' ) {
			this.markers[marker].setMap( null );
		} else if ( typeof marker == 'object' ) {
			marker.setMap( null );
		}
	};
	this.showAll = function ( m ) {
		for ( var i = 0; i < this.markers.length; i++ ) {
			if ( this.markers[i].getMap() != m ) {
				this.markers[i].setAnimation( map.markerAnimation );
				this.markers[i].setMap( m );
			}
		}
	};
	this.hideAll = function () {
		for ( var i = 0; i < this.markers.length; i++ ) {
			this.markers[i].setMap( null );
		}
	};
	return this;
};

var map = {
	map: null,
	geocoder: null,
	currentMarker: null,
	currentDisaster: null,
	infoWindow: null,
	infoWindowOpen: false,
	currentMode: 0,
	disastersURL: './populate',
	offersURL: './populate',
	disastersZoom: 2,
	offersZoom: 8,
	markerAnimation: google.maps.Animation.DROP,
	barMarkers: new MarkerGroup(),
	disasterMap: {
		'tornado': 1,
		'fire': 2,
		'hurricane': 3,
		'mudslide': 4,
		'hailstorm': 5,
		'earthquake': 6,
		'flood': 7
	},
	disasterIcons: {
		'1': '/img/tornado.png',
		'2': '/img/fire.png',
		'3': '/img/hurricane.png',
		'4': '/img/mudslide.png',
		'5': '/img/hailstorm.png',
		'6': '/img/earthquake.png',
		'7': '/img/flood.png'
	},
	offerIcons: {
		'1': '/img/relief.png',
		'2': '/img/relief.png'
	},
	init: function ( element ) {
		map.map = new google.maps.Map( document.getElementById( element ), map.options );
		map.geocoder = new google.maps.Geocoder();
		for ( var i in bars ) {
			map.addMarker( 'bar-' + bars[i].id, new google.maps.LatLng( bars[i].lat, bars[i].lng ), bars[i].name, bars[i] );
		}
		map.infoWindow = new google.maps.InfoWindow( {
			maxWidth: 320,
			padding: 20
		} );
		/*google.maps.event.addListener( map.infoWindow, 'closeclick', function () {
			map.infoWindowOpen = false;
			map.currentMarker = null;
		} );*/
	},
	go: function ( location, zoom ) {
		map.map.panTo( location );
		if ( zoom ) {
			map.map.setZoom( zoom );
		}
	},
	worldMapMode: function () {
		if ( map.currentMode != 1 ) 
		{
			map.disasterMarkers.hideAll();
			map.offerMarkers.hideAll();
			map.currentMode = 1;
			map.map.setOptions( map.options );			
		}
		
		$( '#map-modal input[type="text"]' ).val( '' );
		$( '#map-modal' ).show();
		$( '#map-overlay' ).fadeIn();
	},
	localMapMode: function ( zip ) {
		if ( map.currentMode != 2 && !CF.offeringRelief ) {
			map.disasterMarkers.hideAll();
			map.offerMarkers.hideAll();
			map.currentMode = 2;
			
			map.geocoder.geocode( { 'address': zip }, function ( results, status ) {
				if ( status == google.maps.GeocoderStatus.OK ) {
					$( '#map-overlay' ).fadeOut( function () {
						map.go( results[0].geometry.location, map.offersZoom );
					} );
				} else {
					alert( 'Geocode was not successful.' );
				}
			} );
			
			if ( map.disasterMarkers.markers.length == 0 ) {
				for ( var i in disasters ) {
					map.addMarker( 'disaster-' + disasters[i].id, new google.maps.LatLng( disasters[i].lat, disasters[i].lng ), disasters[i].type, disasters[i].name, disasters[i] );
				}
			} else {
				map.disasterMarkers.showAll( map.map );
			}
			
			$( '#map-toggle' ).attr( 'checked', 'checked' );
			$( '#map-toggle' ).trigger( 'change' );
		}
	},
	// Filters specified disaster type
	filterDisasters: function ( type ) {
		if ( type == 0 ) {
			map.disasterMarkers.showAll( map.map );
		} else {
			type = (typeof type == 'string') ? map.disasterMap[type] : type;
			for ( var i = 0; i < map.disasterMarkers.markers.length; ++i ) {
				if ( map.disasterMarkers.markers[i].type != type ) {
					map.disasterMarkers.hideMarker( i );
					if ( map.infoWindowOpen && (map.disasterMarkers.markers[i].getPosition() == map.infoWindow.getPosition()) ) {
						map.infoWindow.close();
						map.infoWindowOpen = false;
						map.currentMarker = null;
					}
				} else {
					map.disasterMarkers.showMarker( i, map.map );
				}
			}
		}
	},
	addMarker: function ( id, loc, title, info ) {
		var marker = new google.maps.Marker( {
			id: id,
			position: loc,
			map: map.map,
			barInfo: info,
			title: title,
			animation: map.markerAnimation
		} );
		map.barMarkers.addMarker( marker );
		map.addMarkerEvents( marker, id );
	},
	getMarkerByID: function ( id ) {
		if ( id.indexOf( 'disaster' ) != -1 ) {
			return map.disasterMarkers.getMarkerById( id );
		} else {
			return map.offerMarkers.getMarkerById( id );
		}
	},
	hideMarker: function ( id ) {
		if ( id.indexOf( 'disaster' ) != -1 ) {
			map.disasterMarkers.hideMarker( id );
		} else {
			map.offerMarkers.hideMarker( id );
		}
	},
	showMarker: function ( id ) {
		if ( id.indexOf( 'disaster' ) != -1 ) {
			map.disasterMarkers.showMarker( id, map.map );
		} else {
			map.offerMarkers.showMarker( id, map.map );
		}
	},
	showAllMarkers: function ( type ) {
		if ( id.indexOf( 'disaster' ) != -1 ) {
			map.disasterMarkers.showAll( map.map );
		} else {
			map.offerMarkers.showAll( map.map );
		}
	},
	hideAllMarkers: function ( type ) {
		if ( id.indexOf( 'disaster' ) != -1 ) {
			map.disasterMarkers.hideAll();
		} else {
			map.offerMarkers.hideAll();
		}
	},
	addMarkerEvents: function ( marker, id ) {
		//if ( marker.id.indexOf( 'disaster' ) != -1 ) {
			google.maps.event.addListener( marker, 'click', 
				function () {
					if ( !map.infoWindowOpen || map.currentMarker != marker.id ) {
						map.infoWindow.setContent( '<div class="infowindow">' +
							'<div class="title">' + marker.barInfo.name + '</div>' +
							'<div class="location">' + marker.barInfo.location + '</div>' +
						'</div>' );
						map.infoWindow.open( map.map, marker );
						map.infoWindowOpen = true;
						map.currentMarker = marker.id;
					} else {
						map.infoWindow.close();
						map.infoWindowOpen = false;
						map.currentMarker = null;
					}
				}
			);
		/*} else {
			google.maps.event.addListener( marker, 'click', 
				function () {
					if ( !map.infoWindowOpen || map.currentMarker != marker.id ) {
						//var date = new Date( marker.offerInfo.expires.substr( 0, 19 ).replace( 'T', ' ' ) );
						map.infoWindow.setContent( '<div class="infowindow">' +
							'<a href="mailto:' + marker.offerInfo.username + '" class="stitch-button">Contact</a>' +
							'<div class="title">Relief</div>' +
							'<div class="location">' + marker.offerInfo.location.replace( /, /g, '<br />' ).replace( '<br />USA', '' ).replace( /<br \/>([A-Z]{2}.*)/, ", $1" ) + '</div>' +
							'<div class="description">' + marker.offerInfo.description + '</div>' +
						'</div>' );
						map.infoWindow.open( map.map, marker );
						map.infoWindowOpen = true;
						map.currentMarker = marker.id;
					} else {
						map.infoWindow.close();
						map.infoWindowOpen = false;
						map.currentMarker = null;
					}
				}
			);
		}*/
	}
};

map.options = {
	disableDefaultUI: true,
	center: new google.maps.LatLng( 40.01798560, -105.2814560 ),
	zoom: 16,
	minZoom: 4,
	mapTypeId: google.maps.MapTypeId.ROADMAP,
	draggable: true,
	keyboardShortcuts: false,
	scrollwheel: false,
	panControl: true,
	zoomControl: true,
	scaleControl: false,
	streetViewControl: false
};

var CF = {
	options: {},
	init: function () {
		
		// Initialize map
		map.init( 'map' );
		/*$( window ).load( function () {
			if ( CF.loggedIn && !CF.offeringRelief && /\d{5}/.test( CF.options.userInfo.zip_code ) ) {
					map.localMapMode( CF.options.userInfo.zip_code );
			} else {
				map.worldMapMode();
			}
		} );
		
		// Bind event handlers
		$( '#find-relief' ).click( function ( e ) {
			e.preventDefault();
			
			if ( !CF.userSetup && !CF.offeringRelief )
				map.worldMapMode();
		} );
		
		$( '#login-button' ).click( function( e ) {
			e.preventDefault();
			
			if ( !CF.userSetup ) {
				if ( $( '#login-menu' ).is( ':visible' ) ) {
					$( '#login-menu' ).animate( { top: '-=10px', opacity: 0 }, function () { $( this ).hide() } );
				} else {
					$( '#login-menu').show();
					$( '#login-menu' ).animate( { top: '+=10px', opacity: 1 } );
				}
			}
		} );
		
		$( '#offer-relief' ).click( function( e ) {
			e.preventDefault();
			
			CF.offerRelief( false );
		} );
		
		$( '#about-relief' ).click( function( e ) {
			e.preventDefault();
			
			$( '.modal:visible' ).hide();
			$( '#map-overlay' ).fadeIn();
			$( '#about-modal' ).fadeIn();
		} );
		
		$( '#map-overlay').click( function(e) {
			if ( e.target.id === 'map-overlay' )
				$(this).fadeOut();
		});
		
		$( '#map-toggle' ).change( function() {
			if ( $( '#map-toggle' ).is( ':checked' ) ) {
				$( '#disasters-nav' ).animate( { top: '+=48px' } );
			} else {
				$( '#disasters-nav' ).animate( { top: '-=48px' } );
			}
		} );
		if ( $( '#map-toggle' ).is( ':checked' ) ) {
			$( '#disasters-nav' ).animate( { top: '+=48px' } );
		}
		
		$( '#map-modal form' ).submit( function ( e ) {
			e.preventDefault();
			
			var zip = $( '#map-modal input[type="text"]' ).val();
			if ( /\d{5}/.test( zip ) ) {
				map.localMapMode( zip );
			}
		} );
		$( '#map-modal a' ).click( function ( e ) {
			e.preventDefault();
			$( '#map-modal form' ).trigger( 'submit' );
		} );
		
		$( '#login-menu > form' ).submit( function ( e ) {
			e.preventDefault();
			
			if ( $( '#email' ).val() && $( '#password' ).val() )
				CF.login();
		} );
		
		$( '#user-modal> form' ).submit( function ( e ) {
			e.preventDefault();
			
			if ( $( '#firstname' ).val() && $( '#lastname' ).val() )
				CF.login();
		} );
		
		$( '#offer-modal> form' ).submit( function ( e ) {
			e.preventDefault();
			
			if ( $( '#offer-location' ).val() && $( '#offer-description' ).val() )
				CF.offerRelief( true );
		} );
		
		$( '#disasters-nav > a' ).each( function () {
			$( this ).addClass( 'active' );
			
			$( this ).click( function ( e ) {
				e.preventDefault();
				
				$( '#disasters-nav > a' ).each( function () {
					$( this ).removeClass( 'active' );
				} );
				
				if ( !$( this ).hasClass( CF.currentDisaster ) ) {
					CF.currentDisaster = $( this ).attr( 'class' );
					$( this ).addClass( 'active' );
				} else {
					CF.currentDisaster = null;
					$( '#disasters-nav > a' ).each( function () {
						$( this ).addClass( 'active' );
					} );
				}
				
				map.filterDisasters( (CF.currentDisaster != null) ? CF.currentDisaster : 0 );
			} );
		} );*/
	},
	login: function () {
		if ( !CF.loggedIn ) {
			if ( CF.userSetup ) {
				$.post( './login', {
					'username': $( '#email' ).val(),
					'password': $( '#password' ).val(),
					'firstname': $( '#firstname' ).val(),
					'lastname': $( '#lastname' ).val(),
					'zip_code': $( '#zipcode' ).val(),
					'phone': $( '#phone' ).val()
				}, function ( result ) {
					if ( result.response == 'success' ) {
						CF.loggedIn = true;
						CF.options.userInfo = result.userInfo;
						$( '#login-menu' ).html( result.content );
						
						$( '#map-overlay' ).fadeOut( function () {
							$( '#user-modal' ).hide();
							$( '#map-modal' ).show();
							
							CF.userSetup = false;
							$( '#login-button' ).trigger( 'click' );
							window.setTimeout( function () {
								$( '#login-button' ).trigger( 'click' );
							}, 2000 );
							
							if ( /\d{5}/.test( CF.options.userInfo.zip_code ) )
								map.localMapMode( CF.options.userInfo.zip_code );
						} );
					} else if ( result.response == 'failure' ) {
						var bcolor = $( '#user-modal > form' ).css( 'border-left-color' );
						$( '#user-modal > form' ).animate( {
							'borderTopColor': '#ff0000',
							'borderRightColor': '#ff0000',
							'borderBottomColor': '#ff0000',
							'borderLeftColor': '#ff0000'
						}, 'swing' ).delay( 1000 ).animate( {
							'borderTopColor': bcolor,
							'borderRightColor': bcolor,
							'borderBottomColor': bcolor,
							'borderLeftColor': bcolor
						}, 'swing' );
					} else if ( result.response == 'multiple' ) {
						alert( 'You have already logged into another location.' );
					}
				}, 'json' ).error( function () { alert( 'An error occurred while setting your account up.' ); } );
			} else {
				$.post( './login', {
					username: $( '#email' ).val(),
					password: $( '#password' ).val()
				}, function ( result ) {
					if ( result.response == 'success' ) {
						CF.loggedIn = true;
						CF.options.userInfo = result.userInfo;
						//$( '#user > a' ).html( 'welcome ' + CF.options.userInfo.firstname.toLowerCase() );
						$( '#login-menu' ).html( result.content );
						window.setTimeout( function () {
							$( '#login-button' ).trigger( 'click' );
						}, 2000 );
						
						if ( /\d{5}/.test( CF.options.userInfo.zip_code ) )
							map.localMapMode( CF.options.userInfo.zip_code );
					} else if ( result.response == 'newuser' ) {
						if ( $( '#map-overlay > div:visible' ).length != 0 ) {
							$( '#map-overlay > div:visible' ).fadeOut( function () {
								$( '#user-modal' ).fadeIn( function () {
									$( '#firstname' ).focus();
								} );
							} );
						} else {
							$( '#map-overlay > div' ).each( function () {
								$( this ).hide();
							} );
							$( '#user-modal' ).show();
							$( '#map-overlay' ).fadeIn();
						}
						$( '#login-button' ).trigger( 'click' );
						CF.userSetup = true;
					} else if ( result.response == 'failure' ) {
						var bcolor = $( '#login-menu > form' ).css( 'border-left-color' );
						$( '#login-menu > form' ).animate( {
							'borderTopColor': '#ff0000',
							'borderRightColor': '#ff0000',
							'borderBottomColor': '#ff0000',
							'borderLeftColor': '#ff0000'
						}, 'swing' ).delay( 1000 ).animate( {
							'borderTopColor': bcolor,
							'borderRightColor': bcolor,
							'borderBottomColor': bcolor,
							'borderLeftColor': bcolor
						}, 'swing' );
					} else if ( result.response == 'multiple' ) {
						alert( 'You have already logged into another location.' );
					}
				}, 'json' );
			}
		}
	},
	offerRelief: function ( submitting ) {
		submitting = (submitting) ? true : false;
		if ( CF.loggedIn ) {
			if ( CF.reliefFor != null ) {
				if ( !submitting ) {
					CF.offeringRelief = true;
					if ( $( '#map-overlay > div:visible' ).length != 0 ) 
					{
						$( '#map-overlay > div:visible' ).fadeOut( function () {
							$( '#offer-modal' ).fadeIn();
						} );
					} 
					else 
					{
						$( '#map-overlay > div' ).each( function () {
							$( this ).hide();
						} );
						
						$( '#offer-modal' ).show();
						$( '#map-overlay' ).fadeIn();
					}
				} else {
					var lat, lng, location;
					map.geocoder.geocode( { 'address': $( '#offer-location' ).val() }, function ( results, status ) {
						if ( status == google.maps.GeocoderStatus.OK ) {
							lat = results[0].geometry.location.lat();
							lng = results[0].geometry.location.lng();
							location = results[0].formatted_address;
							
							$.post( './offer', {
								'offer_type': $( '#offer-type' ).val(),
								'for_disaster': CF.reliefFor,
								'location': location,
								'lat': lat,
								'lng': lng,
								'description': $( '#offer-description' ).val()
							}, function ( result ) {
								if ( result.response == 'success' ) {
									$( '#map-overlay' ).fadeOut();
									CF.reliefFor = null;
									CF.offeringRelief = false;
								} else if ( result.response == 'error' ) {
									alert( result.error );
								}
							}, 'json' );
						} else if ( status == google.maps.GeocoderStatus.ZERO_RESULTS ) {
							$( '#offer-location' ).css( 'box-shadow', 'inset 0 0 5px rgba( 255, 0, 0, 0.74 )' );
						} else {
							alert( 'Geocode unsuccessful.' );
						}
					} );
				}
			} else {
				alert( 'Please select a disaster.' );
			}
		} else {
			if ( !$( '#login-menu' ).is( ':visible' ) )
				$( '#login-button' ).trigger( 'click' );
		}
	}
};


$(document).ready( function( e ) {
	CF.init();
});