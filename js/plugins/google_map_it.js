(function($) {
	$.fn.google_map_it = function( options ) {
		var settings = $.extend({
			locations:  [
				{ title: 'Title', lat: '41.6706', lng: '-71.2783' }
			],
			styles: [
			],
			marker: ''
		}, options );
		var marker_title;
		var latlng;
		var markers;
		var marker;
		var i;
		var locations_count;
		var styles = JSON.parse(settings.styles);
		var map_icon = settings.marker;
		$(this).each(function() {
			var scrollwheel = $(this).data('scrollwheel');
			var map;
			var bounds = new google.maps.LatLngBounds();
			var mapOptions = {
				zoom: 4,
				maxZoom: 15,
				scrollwheel: scrollwheel,
				styles: styles,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			locations_count = settings.locations.length;
			map = new google.maps.Map( document.getElementById('google-map'), mapOptions );
			var infowindow = new google.maps.InfoWindow();
			for( i = 0; i < locations_count; i++ ) {
				marker = new google.maps.Marker({
					position: new google.maps.LatLng( settings.locations[i]['lat'], settings.locations[i]['lng'] ),
					icon: map_icon,
					map: map
				});
				bounds.extend(marker.position);
				google.maps.event.addListener(marker, 'click', (function(marker, i) {
					var phone_number = '';
					if( settings.locations[i]['phone'] != '' ) {
						phone_number = '<p><strong>P:</strong>&nbsp;&nbsp;<a href="tel:'+settings.locations[i]['phone']+'">'+settings.locations[i]['phone']+'</a></p>';
					} else {
						phone_number = '';
					}
						var info = '<div class="info"><h3>'+settings.locations[i]['title']+'</h3><p>'+settings.locations[i]['address']+'</p>'+phone_number+'<div class="directions block"><a target="_blank"href="http://maps.google.com/?q='+settings.locations[i]['address']+'">Directions</a></div></div>';
						var infobox = new InfoBox({
							content: info,
							disableAutoPan: false,
							maxWidth: 300,
							pixelOffset: new google.maps.Size( 22, -45),
							zIndex: null,
							closeBoxMargin: "-10px -10px 0px 0px",
							infoBoxClearance: new google.maps.Size(1, 1)

						});
					return function() {
						map.panTo(marker.getPosition());
						infobox.setContent( info );
						infobox.open(map, marker);
					};
				})(marker, i));
			}
			map.fitBounds(bounds);
		});
		return this;
	}; //  END GOOGLE MAP IT FUNCTION
}(jQuery));