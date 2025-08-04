<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class JACKCA_Settings {

	private $options;
	private $set_name;

	public function __construct( $name = 'jacka_settings' ) {
		$this->set_name = $name;
		$this->options = get_option( $this->set_name, array() );
		add_action( 'admin_init', array( $this, 'register_settings') );
	}

	public function list_of_settings() {

		$list = array(

			'general' => apply_filters('jacka_settings_general', array(
				'jacka_weekends_message' => array(
					'name' => __( 'Add custom box for Saturdays/Sundays in the calendar', 'jacka' ),
					'desc' => __( 'Enter the message: .... Click {here} to learn more' , 'jacka' ),
					'type' => 'text',
					'size' => 'large',
					'std' => 'These days are typically saved for bday parties. Click {here} to learn more'
				),
				'jackca_weekends_link' => array(
					'name' => __( 'Add custom box for Saturdays/Sundays in the calendar', 'jacka' ),
					'desc' => __( 'Enter the link' , 'jacka' ),
					'type' => 'text',
					'size' => 'large',
					'std' => 'https://'
				),
				'jackca_filter_class_type' => array(
					'name' => __( 'Custom filter class-type', 'jacka' ),
					'desc' => '[option1:week]<br>category1:Summer Camp Apollo Beach<br>category1:Creative Junk Therapy Camps<br>category2:Summer Camp Themed<br>category3:...<br>...<br>[option2:list]<br>category2:...<br>category3:...<br>...<br>[name:week/list] -> week / list is the default view when selecting this option',
					'type' => 'textarea',
					'size' => 'large',
					'std' => '[]'
				),
				'jackca_location_colors' => array(
					'name' => __( 'Location colors', 'jacka' ),
					'desc' => 'Location is extracted from the name. (Location - class name)<br>Apollo Beach:#FF0000<br>Wesley Chapel:#00FF00<br>...',
					'type' => 'textarea_colors',
					'size' => 'large',
					'std' => ''
				),
			)),
			'list' => apply_filters('jacka_settings_list', array(
				'jacka_list_last_categories' => array(
					'name' => __( 'Categories at the bottom', 'jacka' ),
					'desc' => __( 'Enter categories that should be displayed at the bottom of the list view' , 'jacka' ).'<br>'.__('category3:One Time Event Contracted
<br>category3:One Time Event Owner Registration'),
					'type' => 'textarea',
					'size' => 'large',
					'std' => ''
				),
			)),
			'list2' => apply_filters('jacka_settings_list', array(
				'jacka_images' => array(
					'name' => __( 'List of Images for Class names [associated words for each image]', 'jacka' ),
					'desc' => __( 'One image per row with the format: url [word1, word2,...]' , 'jacka' ).'<br>'.__('Last row will be the default image'),
					'type' => 'textarea',
					'size' => 'large',
					'std' => ''
				),
			)),
		);

		return apply_filters( 'jacka_registered_settings', $list );
	}

	public function register_settings() {

		if ( false == get_option( $this->set_name ) ) {
			add_option( $this->set_name );
		}

		foreach( $this->list_of_settings() as $tab => $settings ) {

			// Manage tab with several sections inside (name_) underscore at the end means sub settings
			if (preg_match('/.+_$/', $tab)) {

				foreach($settings as $sec=>$sub_settings) {
					$this->add_settings_section( $tab.$sec );
					$this->add_settings_fields( $tab.$sec, $sub_settings );
				}
			}
			else {
				$this->add_settings_section( $tab);
				$this->add_settings_fields( $tab, $settings );
			}
		}

		register_setting( $this->set_name, $this->set_name, array( $this, 'sanitize') );
	}

	public function add_settings_section( $tab ) {

		add_settings_section(
			$this->set_name . '_' . $tab,
			__return_null(),
			'__return_false',
			$this->set_name . '_' . $tab
		);
	}

	public function add_settings_fields( $tab, $settings ) {

		foreach( $settings as $key => $option ) {

			//echo '<pre>'; print_r( $option ); echo '</pre>';

			$name = isset( $option['name'] ) ? $option['name'] : '';

			$callback = is_callable( array($this, 'show_'.$option['type']) ) ? array($this, 'show_'.$option['type']) : array($this, 'show_missing');

			add_settings_field(
				$this->set_name . '[' . $key . ']',
				$name,
				$callback,
				$this->set_name . '_' . $tab, // Page
				$this->set_name . '_' . $tab, // Section
				array(
					'id'      => $key,
					'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
					'name'    => isset( $option['name'] ) ? $option['name'] : null,
					'section' => $tab,
					'size'    => isset( $option['size'] ) ? $option['size'] : null,
					'options' => isset( $option['options'] ) ? $option['options'] : '',
					'std'     => isset( $option['std'] ) ? $option['std'] : '',
					'callback'=> isset( $option['callback'] ) ? $option['callback'] : '',
					'options' => isset( $option['options'] ) ? $option['options'] : ''
				)
			);
		}
	}



	public function get( $key , $default = false ) {
		return empty( $this->options[$key] ) ? $default : $this->options[$key];
	}

	public function get_all( $key ) {
		return $this->options;
	}

	public function sanitize( $input ) {

		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}

		// Get tab & section
		parse_str( $_POST['_wp_http_referer'], $referrer );
		//echo '<pre>'; print_r( $_POST['_wp_http_referer'] ); echo '</pre>';
		//echo '<pre>referrer'; print_r( $referrer ); echo '</pre>';

		$saved    = get_option( $this->set_name, array() );
		if( ! is_array( $saved ) ) {
			$saved = array();
		}

		// Get list of settings
		$settings = $this->list_of_settings();
		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general'; // TAB, First key by default
		$section  = isset( $referrer['section'] ) ? $referrer['tab'] : ''; // SECTION

		$input = $input ? $input : array();

		// Sanitize tab section
		$input = apply_filters( 'jacka_settings_' . $tab . $section . '_sanitize', $input );


		// Ensure checkbox is passed
		if( !empty($settings[$tab]) ) {

			// Has sections inside tab
			if ( preg_match('/.+_$/', $tab ) ) {
				$comprobar = $settings[$tab][$section];
			}
			// No sections inside tab
			else {
				$comprobar = $settings[ $tab ];
			}

			foreach ( $comprobar as $key => $setting ) {
				// Single checkbox
				if ( isset( $settings[ $tab ][ $key ][ 'type' ] ) && 'checkbox' == $settings[ $tab ][ $key ][ 'type' ] ) {
					$input[ $key ] = ! empty( $input[ $key ] );
				}
				// Multicheck list
				if ( isset( $settings[ $tab ][ $key ][ 'type' ] ) && 'multicheck' == $settings[ $tab ][ $key ][ 'type' ] ) {
					if( empty( $input[ $key ] ) ) {
						$input[ $key ] = array();
					}
				}
			}

		}


		// Loop each input to be saved and sanitize
		foreach( $input as $key => $value ) {

			// With sections inside tab
			if ( preg_match('/.+_$/', $tab ) ) {
				$type = isset( $settings[$tab][$section][$key]['type'] ) ? $settings[$tab][$section][$key]['type'] : false;
			}
			// No sections inside tab
			else {
				$type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;
			}

			// Specific sanitize. Ex. ipp_settings_sanitize_textarea
			$input[$key]  = apply_filters( JACKCA_SETTINGS.'_sanitize_'.$type , $value, $key );

			// General sanitize
			$input[$key]  = apply_filters( JACKCA_SETTINGS.'_sanitize' , $value, $key );
		}

		add_settings_error( 'man-notices', '', __( 'Settings updated.', 'jacka' ), 'updated' );

		$result = array_merge( $saved, $input );
		return $result;
	}


	// Show fields, depends on type
	//-------------------------------------

	public function show_missing( $args ) {
		printf( __( 'The callback function for setting <strong>%s</strong> is missing.', 'jacka' ), $args['id'] );
	}

	public function get_pages( $force = false ) {

		$pages = get_pages();
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}

		return $pages_options;
	}

	public function show_checkbox( $args ) {

		$checked = isset($this->options[$args['id']]) ? checked(1, $this->options[$args['id']], false) : '';
		$html = '<input type="checkbox" id="'.$this->set_name.'[' . $args['id'] . ']" name="'.$this->set_name.'[' . $args['id'] . ']" value="1" ' . $checked . '/>';
		$html .= '<label for="'.$this->set_name.'[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
		echo $html;
	}

	public function show_checkbox_required_true( $args ) {

		$html = '<input type="hidden" id="'.$this->set_name.'[' . $args['id'] . ']" name="'.$this->set_name.'[' . $args['id'] . ']" value="1" checked="checked" />';
		$html .= '<label for="'.$this->set_name.'[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
		echo $html;
	}

	public function show_text( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="'.$this->set_name.'[' . $args['id'] . ']" name="'.$this->set_name.'[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<label for="'.$this->set_name.'[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	public function show_input_number( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="number" class="' . $size . '-text" id="'.$this->set_name.'[' . $args['id'] . ']" name="'.$this->set_name.'[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<label for="'.$this->set_name.'[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	public function show_regex( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="'.$this->set_name.'[' . $args['id'] . ']" name="'.$this->set_name.'[' . $args['id'] . ']" value="' . $value . '"/>';
		$html .= '<label for="'.$this->set_name.'[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	public function show_select( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}

		$html = '<select id="'.$this->set_name.'[' . $args['id'] . ']" name="'.$this->set_name.'[' . $args['id'] . ']">';
		foreach( $args['options'] as $key => $name ) {
			$html .= '<option value="'.$key.'" '.selected($key,$value,false).'>'.$name.'</option>';
		}
		$html .= '</select>';
		$html .= '<label for="'.$this->set_name.'[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	public function show_textarea( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<textarea class="'.$size.'-text" cols="50" rows="10" id="'.$this->set_name.'[' . $args['id'] . ']" name="'.$this->set_name.'[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
		$html .= '<label for="'.$this->set_name.'[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	public function show_textarea_colors( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<textarea id="jacka-colors" class="'.$size.'-text" cols="50" rows="10" id="'.$this->set_name.'[' . $args['id'] . ']" name="'.$this->set_name.'[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
		$html .= '<label for="'.$this->set_name.'[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		// Para arrancar vue
		$html .= '<div id="app-jacka-settings-colors"></div>';
		echo $html;
	}

	public function show_roles( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<textarea class="'.$size.'-text" cols="50" rows="10" id="'.$this->set_name.'[' . $args['id'] . ']" name="'.$this->set_name.'[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
		$html .= '<label for="'.$this->set_name.'[' . $args['id'] . ']"> <br>'  . $args['desc'] . '</label>';

		echo $html;

		$roles = jacka_get_editable_roles();
		$keys = array_keys($roles);
		echo '<br><div>ROLES: '.implode(', ',$keys).'</div>';
		echo '<br><div>PRICES: wholesale,retail</div><br>';
		echo '<div>By default the price is the Woocommerce price. <br>Type here 1 role per row with the price to use. </br>Example: </br><br><strong>shop_manager(wholesale)<br>customer(retail)</strong></div>';

		//echo '<pre>'; print_r( $keys ); echo '</pre>';
	}

	public function show_separation_line( $args ) {
		echo '<hr>';
	}

	public function show_radio( $args ) {

		foreach( $args['options'] as $key => $value ) {
			$checked = false;
			if ( isset( $this->options[ $args['id'] ] ) && $this->options[ $args['id'] ] == $key ) {
				$checked = true;
			} else if ( !isset( $this->options[ $args['id'] ]) && isset( $args['std'] ) && $args['std'] == $key ) {
				$checked = true;
			}
			echo '<input name="'.$this->set_name.'[' . $args['id'] . ']"" id="'.$this->set_name.'[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
			echo '<label for="'.$this->set_name.'[' . $args['id'] . '][' . $key . ']">' . $value . '</label><br/>';
		}
		echo '<p class="description">' . $args['desc'] . '</p>';
	}


	/*public function show_categories_images( $args )
	{
		// Listado stores
		// Fetch all stores categories from all stores
		//
		$assets_dir = JACKCA_PLUGIN_URL . 'assets/';

		// List of wpsl_store_id from postmeta
		global $wpdb;
		$meta_key = jacka_get_store_custom_field();
		$sql = "SELECT meta_value FROM {$wpdb->base_prefix}postmeta WHERE meta_key='{$meta_key}'";
		$stores = $wpdb->get_col($sql);


		$args = [
			'settings_name' => $this->set_name,
			'field_name' => $args['id'],
			'field_value' => $this->options[$args['id']],
			'endpoint' => jacka_get_url_endpoint(),
			'stores' => $stores
		];

		wp_register_script('jack-categories-images', $assets_dir.'jack-categories-images/js/main.js', [], JACKCA_VERSION, true);
		wp_localize_script('jack-categories-images', 'JACKCA', $args);
		wp_enqueue_style( 'jack-categories-images', $assets_dir.'jack-categories-images/css/index.css', array(), JACKCA_VERSION );
		wp_enqueue_script('jack-categories-images');

		echo '<div id="app-categories-images"></div>';
	}*/

	public function show_callback( $args ) {
		$func = $args['callback'];
		if (is_callable($func)) {
			call_user_func( $func, $args );
		}
	}

}

// Sanitize text
function jacka_settings_sanitize_text( $value ) {
	return trim( $value );
}
add_filter('jacka_settings_sanitize_text','jacka_settings_sanitize_text');


// Sanitize textarea
function jacka_settings_sanitize_textarea( $value ) {
	return $value;
}
add_filter('jacka_settings_sanitize_textarea','jacka_settings_sanitize_textarea');
