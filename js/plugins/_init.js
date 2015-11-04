// Scripts - Client

jQuery(function($) {
	if( $('#google-map').length > 0 ) {
		var map_id = $('#google-map').data('id');
		var map_type = $('#google-map').data('type');

		$( '#gmsl_map .location_search' ).submit(function() {
			$( '#google-map' ).html('<p style="width: 100%; text-align: center; position: absolute; top: 50%; margin: -15px 0 0;">Loading...</p>');
			var searched_address = $(this).find('input[type=search]').val();
			setTimeout(function() {
				$.ajax({
					type: 'POST',
					url:  ajax_call.ajaxurl,
					data: { action: 'map_loader', id: map_id, type: map_type, address: searched_address },
					error: function(data) { console.log('fail'); },
					success: function(data) {
						var locations = JSON.parse(data);
						var length = locations.length;
						var half_type = '';
						$('.locations_return').html('<h3>Possible Matches:</h3>' );
						for( i = 0; i < length; i++ ) {
							if( i % 2 == 0 ) {
								half_type = ' right';
							} else {
								half_type = ' left';
							}
							$('.locations_return').append('<div class="spec_location half'+half_type+'"><h4>'+locations[i]['title']+'</h4><p>'+locations[i]['address']+'<br/><a class="directions" target="_blank" href="http://maps.google.com/?q='+locations[i]['address']+'">Directions</a></p></div>');
						}
						$('#google-map').google_map_it({
							locations: locations
						});
						return false;
					}
				});
			}, 1000);
			return false;
		});
		$.ajax({
			type: 'POST',
			url:  ajax_call.ajaxurl,
			data: { action: 'map_loader', id: map_id, type: map_type },
			error: function(data) { console.log('fail'); },
			success: function(data) {
				var map_object = JSON.parse(data);
				var locations = map_object['locations'];
				var styles = map_object['styles']['colors'];
				var marker = map_object['styles']['marker'];
				$('#google-map').google_map_it({
					locations: locations,
					styles: styles,
					marker: marker
				});
			}
		});
	}
});