<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', 'jackca_add_shortcodes');

function jackca_add_shortcodes()
{
	$list = apply_filters('jackca_add_shortcodes', array(
		'jack-monthly-calendar-2',
		'jack-weekly-calendar',
		'jack-weekly-list-views',
		'jack-2-list-views'
	));

	foreach( $list as $shortcode ) {
		add_shortcode( $shortcode, 'jackca_shortcode_display' );
	}
}

function jackca_shortcode_display( $atts = [], $content = null, $tag = '' )
{
	$file = JACKCA_PLUGIN_DIR . "/templates/shortcodes/".$tag.".php";

	if ( !file_exists($file) ) {
		return '<p>Shortcode '.$tag.' not implemented yet</p>';
	}

	$atts = array_change_key_case((array)$atts, CASE_LOWER);
	ob_start();
	include($file);
	return ob_get_clean();
}
