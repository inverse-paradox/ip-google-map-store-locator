<?php
/*
	Plugin Name:  IP Google Map Store Locator
	Plugin URI: https://github.com/joshhannan/ip-google-map-store-locator
	Description:  Plugin that utilizes Google Maps to build complex Store Locator, as well as directions functionality.
	Version: 1.0
	Author: <a href="http://github.com/joshhannan">Josh Hannan</a>
	Author URI: http://www.inverseparadox.com
*/

/*======================================================================
	SETUP PLUGIN - BUILD POST TYPE, TAXONOMY, EDIT LOCATION PAGE
======================================================================*/
	 
	function gmsl_add_settings_link( $links, $file ) {
		$gmsl_settings_link = '<a href="' . admin_url( 'edit.php?post_type=gmsl_locations&page=gmsl_settings' ) . '">' . __( 'Settings', 'IP Google Map Store Locator' ) . '</a>';
		array_unshift( $links, $gmsl_settings_link );
		return $links;
	}
	$gmsl_plugin_file = 'ip-google-map-store-locator/index.php';
	add_filter( "plugin_action_links_{$gmsl_plugin_file}", 'gmsl_add_settings_link', 10, 2 );

	function create_post_type() {
		register_post_type( 'gmsl_locations',
			array(
				'labels' => array(
					'name' => __( 'Locations' ),
					'singular_name' => __( 'Location' )
				),
				'public' => true,
				'publicly_queryable' => true,
				'show_in_nav_menus' => false,
				'hierarchical' => false,
				'has_archive' => true,
				'supports' => array(
					'title',
					'revisions'
				),
				'menu_icon' => 'dashicons-location',
				'register_meta_box_cb' => 'add_location_information_metabox'
			)
		);
	}
	add_action( 'init', 'create_post_type' );

	function create_location_taxonomy() {
		$labels = array(
			'name' => 'Location Types',
			'singular_name' => 'Location Type'
		);
		$args = array( 'labels' => $labels, 'hierarchical' => true );
		register_taxonomy( 'gmsl_location_types', 'gmsl_locations', $args );
	}
	add_action( 'init', 'create_location_taxonomy' );

	// Add the Meta Boxes
	function add_location_information_metabox() {
		add_meta_box( 'gmsl_locations_fields', 'Location Information', 'gmsl_meta_box_callback', 'gmsl_locations' );
	}
	add_action( 'add_meta_boxes', 'add_location_information_metabox' );

	function gmsl_meta_box_callback( $post ) {
		//wp_nonce_field( 'gmsl_lat_long_save', 'gmsl_lat_long_nonce' );

		$gmsl_address = get_post_meta( $post->ID, 'gmsl_address', true );
		$gmsl_phone = get_post_meta( $post->ID, 'gmsl_phone', true );
		$gmsl_lat = get_post_meta( $post->ID, 'gmsl_lat', true );
		$gmsl_lng = get_post_meta( $post->ID, 'gmsl_lng', true );

		if( empty( $gmsl_lat ) && empty( $gmsl_lng ) ) :
			$address = str_replace( '\n', ' ', $gmsl_address );
			$address = str_replace( ' ', '+', $gmsl_address );
			$location_json = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $address );
			$location_json = json_decode($location_json);
			$location_lat = $location_json->results[0]->geometry->location->lat;
			$location_lng = $location_json->results[0]->geometry->location->lng;
			$gmsl_lat = $location_lat;
			$gmsl_lng = $location_lng;
		endif;

		echo '<div class="field" style="margin: 0 0 15px;">';
		echo '<p>Enter Address.  To clear to next line, insert \n.</p>';
		echo '<label style="display: inline-block; margin: 0 15px 0 0; min-width: 75px;" for="gmsl_address">';
		_e( 'Address', 'gmsl_locations' );
		echo '</label>';
		echo '<input type="text" id="gmsl_address" name="gmsl_address" value="' . esc_attr( $gmsl_address ) . '" size="50" /></div>';

		echo '<div class="field" style="margin: 0 0 15px;"><label style="display: inline-block; margin: 0 15px 0 0; min-width: 75px;" for="gmsl_phone">';
		_e( 'Phone', 'gmsl_locations' );
		echo '</label> ';
		echo '<input type="tel" id="gmsl_phone" name="gmsl_phone" value="' . esc_attr( $gmsl_phone ) . '" size="10" /></div>';

		echo '<div class="field" style="margin: 0 0 15px;"><label style="display: inline-block; margin: 0 15px 0 0; min-width: 75px;" for="gmsl_lat">';
		_e( 'Latitude', 'gmsl_locations' );
		echo '</label> ';
		echo '<input type="text" id="gmsl_lat" name="gmsl_lat" value="' . esc_attr( $gmsl_lat ) . '" size="25" /></div>';

		echo '<div class="field" style="margin: 0 0 15px;"><label style="display: inline-block; margin: 0 15px 0 0; min-width: 75px;" or="gmsl_lng">';
		_e( 'Longitude', 'gmsl_locations' );
		echo '</label> ';
		echo '<input type="text" id="gmsl_lng" name="gmsl_lng" value="' . esc_attr( $gmsl_lng ) . '" size="25" /></div>';
	}


	function gmsl_locations_save_meta_box_data( $post_id ) {
		// Sanitize user input.
		$gmsl_address_data = sanitize_text_field( $_POST['gmsl_address'] );
		$gmsl_phone_data = sanitize_text_field( $_POST['gmsl_phone'] );
		$gmsl_lat_data = sanitize_text_field( $_POST['gmsl_lat'] );
		$gmsl_lng_data = sanitize_text_field( $_POST['gmsl_lng'] );

		// Update the meta field in the database.
		update_post_meta( $post_id, 'gmsl_address', $gmsl_address_data );
		update_post_meta( $post_id, 'gmsl_phone', $gmsl_phone_data );
		update_post_meta( $post_id, 'gmsl_lat', $gmsl_lat_data );
		update_post_meta( $post_id, 'gmsl_lng', $gmsl_lng_data );
	}
	add_action( 'save_post', 'gmsl_locations_save_meta_box_data' );

	//Uninstall
	function gmsl_uninstall() {
		if ( ! current_user_can( 'activate_plugins' ) )
			return;

			//If the user is preserving the settings then don't delete them
		$options = get_option('gmsl_settings');
		$gmsl_preserve_settings = $options[ 'gmsl_preserve_settings' ];
		if($gmsl_preserve_settings) return;

		//Settings
		delete_option( 'gmsl_settings' );
	}
	register_uninstall_hook( __FILE__, 'gmsl_uninstall' );

/*======================================================================
	SETTINGS PAGE SETUP
======================================================================*/

	function add_gmsl_settings_page() {
		add_submenu_page('edit.php?post_type=gmsl_locations', 'Settings', 'Settings', 'manage_options', 'gmsl_settings', 'gmsl_settings_page' );
		add_action( 'admin_init', 'register_gmsl_settings' );
	}
	add_action('admin_menu', 'add_gmsl_settings_page');

	function register_gmsl_settings() {
		register_setting( 'gmsl_settings', 'gmsl_title_text' );
		register_setting( 'gmsl_settings', 'gmsl_search_type' );
		register_setting( 'gmsl_settings', 'gmsl_map_marker_image' );
		register_setting( 'gmsl_settings', 'gmsl_map_style' );
		register_setting( 'gmsl_settings', 'gmsl_scrollwheel' );
		register_setting( 'gmsl_settings', 'gmsl_map_style' );
		register_setting( 'gmsl_settings', 'gmsl_map_icon' );
	}


	function gmsl_settings_page() {
		// Set Defaults
		$gmsl_settings_defaults = array(
			'gmsl_title_text' => '',
			'gmsl_search_type' => 'title',
			'gmsl_map_marker_image' => '/images/map_marker_default.png',
			'gmsl_map_style' => 'default',
			'gmsl_scrollwheel' => 'true',
			'gmsl_load_css' => 'true',
			'gmsl_map_style' => '',
			'gmsl_map_icon' => get_bloginfo('url') . '/wp-content/plugins/ip-google-map-store-locator/images/map_marker_default.png'
		);
		$options = wp_parse_args( get_option( 'gmsl_settings' ), $gmsl_settings_defaults );
		update_option( 'gmsl_settings', $options );

		$gmsl_settings = get_option('gmsl_settings');
		if( $_POST['update_settings'] == 'Y' ) {
			$gmsl_settings['gmsl_title_text'] = $_POST['gmsl_title_text'];
			$gmsl_settings['gmsl_search_type'] = $_POST['gmsl_search_type'];
			$gmsl_settings['gmsl_map_marker_image'] = $_POST['gmsl_map_marker_image'];
			$gmsl_settings['gmsl_scrollwheel'] = $_POST['gmsl_scrollwheel'];
			$gmsl_settings['gmsl_load_css'] = $_POST['gmsl_load_css'];
			$gmsl_settings['gmsl_map_style'] = $_POST['gmsl_map_style'];
			$gmsl_settings['gmsl_map_icon'] = $_POST['gmsl_map_icon'];
			update_option( "gmsl_settings", $gmsl_settings );
		}
?>
<script type="text/javascript">
/*
	jQuery(function($) {
		$('#gmsl_settings_form').submit(function() {
			var text = $('#gmsl_title_text').val();
			console.log( text );
			return false;
		});
	});
*/
</script>
<div class="wrap">
	<h2>Google Map Store Locator Settings</h2>
	<form id="gmsl_settings_form" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="update_settings" value="Y" />
		<table class="form-table">
			<tr valign="top"><th scope="row"><?php _e('Scrollwheel?'); ?></th>
				<td>
					<select id="gmsl_scrollwheel" name="gmsl_scrollwheel">
<?php
		// for image field
		wp_enqueue_media();

		$search_types = array(
			'true' => 'On',
			'false' => 'Off'
		);
		foreach( $search_types as $type => $value ) :
			$selected = '';
			$type_that_is_set = $gmsl_settings['gmsl_scrollwheel'];
			if( $type == $type_that_is_set ) :
				$selected = 'selected="selected"';
			endif;
			echo '<option value="' . esc_attr( $type ) . '" ' . $selected . '>' . $value . '</option>';
		endforeach;
?>
					</select>
				</td>
			</tr>
<?php /*
			<tr valign="top"><th scope="row"><?php _e('Search Type'); ?></th>
				<td>
					<select id="gmsl_search_type" name="gmsl_search_type">
<?php
		$search_types = array(
			'' => '',
			'title' => 'Title',
			'distance' => 'Distance'
		);
		foreach( $search_types as $type => $value ) :
			$selected = '';
			$type_that_is_set = $gmsl_settings['gmsl_search_type'];
			if( $type == $type_that_is_set ) :
				$selected = 'selected="selected"';
			endif;
			echo '<option value="' . esc_attr( $type ) . '" ' . $selected . '>' . $value . '</option>';
		endforeach;
?>
					</select>
				</td>
			</tr>
			<tr valign="top"><th scope="row">Map Marker:</th>
				<td><input type="file" name="gmsl_map_marker_image" value="<?php echo esc_attr( $gmsl_settings['gmsl_map_marker_image'] ); ?>" /></td>
			</tr>
			<tr valign="top"><th scope="row">Map Style:</th>
				<td>
					<select name="gmsl_map_style">
<?php
		$map_types = array(
			'default' => 'Default',
			'blue' => 'Blue',
			'green' => 'Green'
		);
		foreach( $map_types as $type => $value ) :
			$selected = '';
			$type_that_is_set = $gmsl_settings['gmsl_map_style'];
			if( $type == $type_that_is_set ) :
				$selected = 'selected="selected"';
			endif;
			echo '<option value="' . esc_attr( $type ) . '" ' . $selected . '>' . $value . '</option>';
		endforeach;
?>
					</select>
				</td>
			</tr>
*/ ?>
			<tr valign="top"><th scope="row">Display Map CSS?</th>
				<td>
					<select name="gmsl_load_css">
<?php
		$css_types = array(
			'true' => 'Yes',
			'false' => 'No'
		);
		foreach( $css_types as $type => $value ) :
			$selected = '';
			$type_that_is_set = $gmsl_settings['gmsl_load_css'];
			if( $type == $type_that_is_set ) :
				$selected = 'selected="selected"';
			endif;
			echo '<option value="' . esc_attr( $type ) . '" ' . $selected . '>' . $value . '</option>';
		endforeach;
?>
					</select>
				</td>
			</tr>
			<tr valign="top"><th scope="row">Map Style?:</th>
				<td>
					<textarea id="gmsl_map_style" name="gmsl_map_style" cols="75" rows="10"><?php echo esc_attr( stripslashes( $gmsl_settings['gmsl_map_style'] ) ); ?></textarea>
				</td>
			</tr>
			<tr valign="top"><th scope="row">Map Icon?:</th>
				<td>
					<label for="gmsl_map_icon">Image</label><br/>
					<input type="text" readonly style="background: none; border: none; box-shadow: none; width: auto; min-width: 630px; font-size: 12px; padding: 10px 0;" name="gmsl_map_icon" id="gmsl_map_icon" class="regular-text" value="<?php echo esc_attr( $gmsl_settings['gmsl_map_icon'] ); ?>"><br/>
					<input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Image">
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#upload-btn').click(function(e) {
			e.preventDefault();
			var image = wp.media({ 
				title: 'Upload Image',
				// mutiple: true if you want to upload multiple files at once
				multiple: false
			}).open()
			.on('select', function(e){
				// This will return the selected image from the Media Uploader, the result is an object
				var uploaded_image = image.state().get('selection').first();
				// We convert uploaded_image to a JSON object to make accessing it easier
				// Output to the console uploaded_image
				var image_object = uploaded_image.toJSON();
				// Let's assign the url value to the input field
				var image_url = image_object.url;
				$('#gmsl_map_icon').val(image_url);
			});
		});
	});
</script>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div><!--/wrap-->
<?php
	}

/*======================================================================
	FRONT END - AJAX LOADER
======================================================================*/

	function map_loader() {
		$location_id = $_POST['id'];
		$location_type = $_POST['type'];
		$searched_location = $_POST['address'];
		$count = 0;
		$desired_location = '';
		$desired_locations = array();
		// WHAT TYPE OF MAP ARE WE GETTING
		if( $location_type != '' ) :
			$setting = 'type';
		elseif( $location_id != '' ) :
			$setting = 'id';
		else :
			$setting = '';
		endif;
		if( $location_type != '' )  :
			if( $searched_location != '' ) :
				$args = array(
					'post_type' => 'gmsl_locations',
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'gmsl_location_types',
							'field' => 'slug',
							'terms' => $location_type
						)
					),
					's' => $searched_location,
					'meta_query' => array(),
					'posts_per_page' => -1
				);
			else :
				$args = array(
					'post_type' => 'gmsl_locations',
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'gmsl_location_types',
							'field' => 'slug',
							'terms' => $location_type
						)
					),
					'posts_per_page' => -1
				);
			endif;
		else :
			$args = array(
				'post_type' => 'gmsl_locations',
				'order' => 'ASC',
				'posts_per_page' => -1
			);
		endif;
		$locations = get_posts( $args );
		if( !empty( $locations ) ) :
			foreach( $locations as $location ) :
				$count++;
				switch( $setting ) {
					case 'id' :
						if( $location_id == $location->ID ) :
							$address = str_replace( '\n', '<br/>', get_post_meta( $location->ID, 'gmsl_address', true ) );
							$desired_location = array(
								'id' => $location->ID,
								'title' => $location->post_title,
								'lat' => get_post_meta( $location->ID, 'gmsl_lat', true ),
								'lng' => get_post_meta( $location->ID, 'gmsl_lng', true ),
								'address' => $address,
								'phone' => get_post_meta( $location->ID, 'gmsl_phone', true ),
								'directions' => $location_directions
							);
							$desired_locations[] = $desired_location;
						endif;
					break;
					case 'type' :
						$address = str_replace( '\n', '<br/>', get_post_meta( $location->ID, 'gmsl_address', true ) );
						$desired_location = array(
							'id' => $location->ID,
							'title' => $location->post_title,
							'lat' => get_post_meta( $location->ID, 'gmsl_lat', true ),
							'lng' => get_post_meta( $location->ID, 'gmsl_lng', true ),
							'address' => $address,
							'phone' => get_post_meta( $location->ID, 'gmsl_phone', true ),
							'directions' => $location_directions
						);
						$desired_locations[] = $desired_location;
					break;

					default :
				}
			endforeach;
			// SEE IF MAP NEEDS A STYLE
			$settings = get_option( 'gmsl_settings' );
			$map_style = stripslashes( $settings['gmsl_map_style'] );
			$map_icon = esc_attr( $settings['gmsl_map_icon'] );
			$map_object = array(
				'styles' => array(
					'colors' => $map_style,
					'marker' => $map_icon
				),
				'locations' => $desired_locations
			);
			echo json_encode( $map_object, JSON_PRETTY_PRINT );
			exit;
		else :
			exit;
		endif;
	}
	function queue_scripts_styles() {
		wp_register_script( 'google_map_api', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array('jquery'), null, true );
		wp_register_script( 'google_map_api_infobox', 'wp-content/plugins/ip-google-map-store-locator/js/libs/infobox.js', array('jquery', 'google_map_api'), null, true );
		wp_register_script('google_map_it', get_bloginfo('url') . '/wp-content/plugins/ip-google-map-store-locator/js/store_locator.js', array('jquery', 'google_map_api', 'google_map_api_infobox' ), null, true );
		wp_localize_script( 'google_map_it', 'ajax_call', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_register_style( 'wp_store_locator', get_bloginfo('url') . '/wp-content/plugins/ip-google-map-store-locator/css/global.css', false, null );
		$gmsl_settings = get_option( 'gmsl_settings' );
		if( $gmsl_settings['gmsl_load_css'] == 'true' ) :
			wp_enqueue_style( 'wp_store_locator' );
		endif;
	}
	add_action('wp_enqueue_scripts', 'queue_scripts_styles');
	add_action("wp_ajax_nopriv_map_loader", "map_loader");
	add_action("wp_ajax_map_loader", "map_loader");

	function ip_get_nearby_stores($lat, $long, $distance) {
		global $wpdb;
		$nearbyCities = $wpdb->get_results(
			"SELECT DISTINCT
			latitude.post_id,
			latitude.meta_key,
			latitude.meta_value as cityLat,
			longitude.meta_value as cityLong,
			((ACOS(SIN($lat * PI() / 180) * SIN(latitude.meta_value * PI() / 180) + COS($lat * PI() / 180) * COS(latitude.meta_value * PI() / 180) * COS(($long - longitude.meta_value) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance,
			wp_posts.post_title
			FROM
			wp_postmeta AS latitude
			LEFT JOIN wp_postmeta as longitude ON latitude.post_id = longitude.post_id
			INNER JOIN wp_posts ON wp_posts.ID = latitude.post_id
			WHERE latitude.meta_key = 'latitude' AND longitude.meta_key = 'longitude'
			HAVING distance < $distance
			ORDER BY distance ASC;"
		);
		if($nearbyCities){
			return $nearbyCities;
		}
	}

/*======================================================================
	FRONT END - STORE LOCATOR SHORTCODE
======================================================================*/

	function store_locator( $atts, $content ) {
		$a = shortcode_atts( array(
			'id' => '',
			'type' => '',
			"search" => false
		), $atts );

		wp_enqueue_script( 'google_map_api' );
		wp_enqueue_script( 'google_map_api_infobox' );
		wp_enqueue_script( 'google_map_it' );

		$scrollwheel_setting = get_option( 'gmsl_settings' );
		$scrollwheel_setting = $scrollwheel_setting['gmsl_scrollwheel'];

		if( $a['search'] == true ) :
			$search = '<form class="location_search contact_form" id="location_search"><div class="wrap"><input type="hidden" name="post_type" value="gmsl_locations" /><div class="left two_thirds"><input type="search" name="intended_location"></div><div class="right third"><input type="submit" class="button" value="submit" /></div></div></form>';
			$locations_return = '<div class="locations_return block"></div>';
		else :
			$search = '';
			$locations_return = '';
		endif;

		if( $a['search'] == true && $search_position == 'top' ) :
			return '<div id="gmsl_map">' . $search . $locations_return . '<div id="google-map" style="min-height: 400px; position: relative;" data-id="' . $a['id'] . '" data-type="' . $a['type'] . '" data-scrollwheel="' . $scrollwheel_setting . '"><img width="32" height="32" style="position: absolute; display: block; top: 50%; left: 50%; margin: -16px 0 0 -16px;" src="' . plugins_url() . '/ip-google-map-store-locator/images/ajaxloader.gif" /></div></div>';
		else :
			return '<div id="gmsl_map"><div id="google-map" style="min-height: 400px; position: relative;" data-id="' . $a['id'] . '" data-type="' . $a['type'] . '" data-scrollwheel="' . $scrollwheel_setting . '"><img width="32" height="32" style="position: absolute; display: block; top: 50%; left: 50%; margin: -16px 0 0 -16px;" src="' . plugins_url() . '/ip-google-map-store-locator/images/ajaxloader.gif" /></div>' . $search . $locations_return . '</div>';
		endif;
	}
	add_shortcode('store_locator', 'store_locator');

/*======================================================================
	FRONT END - LOCATION LIST SHORTCODE
======================================================================*/

	function gmsl_location_list( $atts, $content ) {
		$a = shortcode_atts( array(
			'id' => '',
			'type' => ''
		), $atts );
		if( $a['type'] == '' ) :
			if( $a['id'] == '' ) :
				$args = array(
					'post_type' => 'gmsl_locations',
					'order' => 'ASC',
					'posts_per_page' => -1
				);
				$gmsl_list = get_posts( $args );
			else :
				$gmsl_list = get_post( $a['id'], 'object' );
			endif;
		else :
			$args = array(
				'post_type' => 'gmsl_locations',
				'order' => 'ASC',
				'tax_query' => array(
					array(
						'taxonomy' => 'gmsl_location_types',
						'field' => 'slug',
						'terms' => $a['type']
					)
				),
				'posts_per_page' => -1
			);
			$gmsl_list = get_posts( $args );
		endif;
		if( !empty( $gmsl_list ) ) :
			$gmsl_location_html = '<div class="gmsl_location_list block">';
			foreach( $gmsl_list as $location ) :
				if( get_post_meta( $location->ID, 'gmsl_address', true ) ) :
					$address = get_post_meta( $location->ID, 'gmsl_address', true );
					$address = str_replace( '\n', '<br />', $address );
					$gmsl_address = '<p class="gmsl_location_address">' . $address . '</p>';
					$formatted_address = str_replace(' ', '+', get_post_meta( $location->ID, 'gmsl_address', true ) );
					$gmsl_directions_link = '<a class="gmsl_location_directions_link" target="_blank" href="http://maps.google.com/?q=' . $formatted_address . '">Directions</a>';
				else :
					$gmsl_address = '';
					$gmsl_directions_link = '';
				endif;
				if( get_post_meta( $location->ID, 'gmsl_phone', true ) ) :
					$gmsl_phone = '<p class="gmsl_location_phone"><a href="tel:' . get_post_meta( $location->ID, 'gmsl_phone', true ) . '">' . get_post_meta( $location->ID, 'gmsl_phone', true ) . '</a></p>';
				else :
					$gmsl_phone = '';
				endif;
				$gmsl_location_html .= '<div id="gmsl_location_' . $location->ID  . '" class="gmsl_location">';
				$gmsl_location_html .= '<h3 class="gmsl_location_title">' . $location->post_title . '</h3>';
				$gmsl_location_html .= $gmsl_address;
				$gmsl_location_html .= $gmsl_phone;
				$gmsl_location_html .= $gmsl_directions_link;
				$gmsl_location_html .= '</div><!--/gmsl_location-->';
			endforeach;
			$gmsl_location_html .= '</div><!-/block-->';
			return $gmsl_location_html;
		endif;
	}
	add_shortcode('gmsl_location_list', 'gmsl_location_list');
	
/*======================================================================
	END PLUGIN
======================================================================*/