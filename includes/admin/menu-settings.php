<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// REGISTER MENU SETTINGS
//-----------------------------------
// https://wordpress.stackexchange.com/questions/41207/how-do-i-enqueue-styles-scripts-on-certain-wp-admin-pages

function jacka_register_menu_settings(){

	$role = 'manage_options';

	$my_page = add_submenu_page('options-general.php', 'Settings JACK', __('JACK Calendar','jacka'), $role, JACKCA_SETTINGS, 'jacka_display_menu_settings' );
	//add_submenu_page('edit.php?post_type=jack_activity', 'Settings', __('Settings','jacka'), $role, JACKCA_SETTINGS, 'jacka_display_menu_settings' );

    add_action('load-' . $my_page, 'jacka_load_settings_script');
}
add_action('admin_menu', 'jacka_register_menu_settings');


function jacka_load_settings_script()
{
    add_action('admin_enqueue_scripts', 'jacka_enqueue_admin_js');
}

function jacka_enqueue_admin_js()
{
	$assets_dir = JACKCA_PLUGIN_URL . 'assets/';
	wp_enqueue_script( 'jacka-settings-script', $assets_dir . 'jack-settings/js/main.js', [], JACKCA_VERSION, true);
	wp_localize_script('jacka-settings-script', 'JACKCA_CALENDAR',[
		'endpoint' => 'https://app.jackrabbitclass.com/jr3.0/Openings/OpeningsJson',
    ]);
}



// DISPLAY MENU SETTINGS
//-----------------------------------

function jacka_display_menu_settings($args){

	$tabs = array(
		'general'       => __('General','jacka'),
		'list'       => __('List view','jacka'),
		'list2'       => __('2 List views','jacka'),
	);

	$active_default = 'general';
	$active_tab = ( isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs ) ) ? $_GET['tab'] : $active_default;
	$active_section = isset( $_GET['section'] ) ? $_GET['section'] : '';

	ob_start();

	?>
	<div class="wrap">
		<h2><?php
			echo _x('Jack Settings','settings','jacka');
			?>
		</h2>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( $tabs as $tab_id => $tab_data ) {

				// Tab with one section
				if ( is_string( $tab_data ) ) {
					$tab_name = $tab_data;
				}
				// Tab with several sections
				else {
					$tab_name = $tab_data['name'];
				}

				// URL
				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id,
					'section' => false
				) );

				// First section if exists
				if ( is_array( $tab_data ) ) {
					$keys = array_keys( $tab_data['sections'] );
					$tab_url = add_query_arg( array(
						'settings-updated' => false,
						'tab' => $tab_id,
						'section' => $keys[0]
					) );
				}

				$active = ($active_tab == $tab_id) ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . ' tab-'.strtolower($tab_name).'">';
				echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
	</div>
	<?php settings_errors(); ?>
	<div id="tab_container">

		<?php

		//List of sections if exists
		$tab = $tabs[$active_tab];

		if ( is_array($tab) ) {

			echo '<ul class="iform-options-sections">';

			$count = count( $tab['sections'] );
			$index = 1;
			foreach( $tab['sections'] as $section_key => $section_name ) {

				$tab_sec_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $active_tab,
					'section' => $section_key,
					'erm-message' => false
				) );

				echo '<li>';
				$class = ( $section_key == $active_section ? 'current' : '' ) ;
				echo '<a href="' . esc_url( $tab_sec_url ) . '" class="'.$class.'">' . $section_name . '</a>';
				if ($index++ < $count ) { echo ' | '; }
				echo '</li>';

			}
			echo '</ul>';
		}
		?>

		<form method="post" action="options.php">
			<table class="form-table">
				<?php
				settings_fields( JACKCA_SETTINGS );
				do_settings_fields( JACKCA_SETTINGS.'_' . $active_tab.$active_section, JACKCA_SETTINGS.'_' . $active_tab.$active_section );
				?>
			</table>

			<?php submit_button(); ?>
		</form>

	</div>
	<?php
	echo ob_get_clean();

	do_action('jacka_settings_displayed');
}
