<?php

/**
 * Plugin Name: Jackrabbit Calendar
 * Plugin URI:  https://
 * Description: Jackrabbit Calendar
 * Version:     1.4.8
 * Requires PHP: 5.6
 * Author:      Alejandro
 * Author URI:  http://
 * Donate link: https://
 * License:     GPLv2
 * Text Domain: apstore
 * Domain Path: /languages
 *
 * @link    https://
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Jack_Calendar') ) :

	final class Jack_Calendar
	{
		private static $singleton;
		public $settings;

		public static function singleton()
		{
			if ( !isset( self::$singleton ) && !( self::$singleton instanceof Jack_Calendar ) ) {

				self::$singleton = new Jack_Calendar;
				self::$singleton->setup_constants();

				add_action( 'plugins_loaded', array(self::$singleton, 'load_textdomain' ) );

				self::$singleton->includes();

				if ( class_exists('JACKCA_Settings')){
					self::$singleton->settings = new JACKCA_Settings( JACKCA_SETTINGS );
				}
			}
			return self::$singleton;
		}

		private function setup_constants()
		{
			// Version
			if ( ! defined( 'JACKCA_VERSION' ) ) {
				define( 'JACKCA_VERSION', '1.4.8' );
			}

			if ( ! defined( 'JACKCA_SITE_URL' ) ) {
				define( 'JACKCA_SITE_URL', get_option('siteurl') );
			}

			// Folder Path
			if ( ! defined( 'JACKCA_PLUGIN_DIR' ) ) {
				define( 'JACKCA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Folder URL
			if ( ! defined( 'JACKCA_PLUGIN_URL' ) ) {
				define( 'JACKCA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Root File
			if ( ! defined( 'JACKCA_PLUGIN_FILE' ) ) {
				define( 'JACKCA_PLUGIN_FILE', __FILE__ );
			}

			// Name for Setting
			if ( ! defined( 'JACKCA_SETTINGS' ) ) {
				define( 'JACKCA_SETTINGS', 'jacka_settings' );
			}

			define( 'JACKCA_POST_TYPE_ACTIVITY', 'jack_activity' );
			define( 'JACKCA_POST_TYPE_ACTIVITY_TYPE_TAXONOMY', 'jack_activity_type' );
			define( 'JACKCA_POST_TYPE_ACTIVITY_AGE_TAXONOMY', 'jack_activity_age' );
			define( 'JACKCA_POST_TYPE_ACTIVITY_LEVEL_TAXONOMY', 'jack_activity_level' );

			define( 'JACKCA_POST_TYPE_STORE', 'wpsl_stores' );

			define( 'JACKCA_JACKRABBIT_API_URL', 'https://app.jackrabbitclass.com/jr3.0/Openings/OpeningsJson');
			define('JACKCA_STORE_META_ORGID', 'wpsl_store_id');

		}

		public function load_textdomain() {
			load_plugin_textdomain( 'jackst', false, plugin_basename( dirname( __FILE__ ) ) . "/languages/" );
		}

		private function includes() {

			require_once JACKCA_PLUGIN_DIR . 'includes/functions.php';

			// Shortcodes
			require_once JACKCA_PLUGIN_DIR . 'includes/shortcodes.php';

			// Settings
			require_once JACKCA_PLUGIN_DIR . 'includes/admin/settings.php';

			if (is_admin())
			{
				require_once JACKCA_PLUGIN_DIR . 'includes/admin/menu-settings.php';
			}
		}
	}

	function JACKCA() {
		return Jack_Calendar::singleton();
	}

	JACKCA();

endif; // End if class_exists check.
