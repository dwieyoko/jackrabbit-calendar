<?php
/*

/wp-content/uploads/2023/09/kids-decorating-cupcakes.jpg [cinnamon, lil' chef, cookies]
/wp-content/uploads/2023/09/118207144_3164898033557429_306494838328623372_n.jpg [breakfast for dinner]
/wp-content/uploads/2023/09/119729421_3268310403216191_589505699587473070_n.jpg [apple]
/wp-content/uploads/2023/07/camps.png [camps]
/wp-content/uploads/2023/07/family-classes.png [family, cheese]
/wp-content/uploads/2023/07/kids-night-out.png [kids, night]
/wp-content/uploads/2023/07/weekly-classes.png [week, weekly]
/wp-content/uploads/2023/07/birthday-parties.png [birthday, parties, world]
/wp-content/uploads/2023/09/special-abilities.jpg [special]
/wp-content/uploads/2023/09/halloween.jpg [halloween, pumpkin]
/wp-content/uploads/2023/07/adult-cooking-classes.png [adult]
/wp-content/uploads/2023/09/kids-decorating-cupcakes.jpg

*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (is_admin() || wp_doing_ajax())
{
	return '<div style="padding: 20px; background: #fadcb6; text-align: center;">Shortcode jack-2-list-views will be displayed here.</div>';
}

global $post;

/* Filtros posibles

campos normales: category1, category2, category3
location: location-name -> se extra del campo name -> Madrid - My class name -> Location es Madrid (al inicio)
age: tiene en cuenta los campos min_age y max_age, para el filtro cojo el campo min_age
class-type: se mapea desde los ajustes

filters=category1,category2,category3
filters='location-name,age,class-type'

*/

/** @var TYPE_NAME $atts */
$atts = shortcode_atts( array(
	'orgid' => 549617,
	'cat1' => false, // First filter preselected
	// Nuevo metodo de filtros
	'filters' => 'category1, category2, category3',
	'filters_names' => 'All cat1, All cat2, All cat3',
	'fee_label1' => 'Monthly Tuition',
	'fee_label2' => 'Tuition',
	'view' => 'week',
	'btn_list' => 'List 1',
	'btn_list_2' => 'List 2',
	'hide_class_type_first_load' => '',
	'hide_categories' => '',
    'show_categories' => ''
), $atts);

$orgid = $atts['orgid'];
$cat1 = $atts['cat1'];
$fee_label1 = $atts['fee_label1'];
$fee_label2 = $atts['fee_label2'];
$view = $atts['view'];
$hide_class_type_first_load = $atts['hide_class_type_first_load'];
$hide_categories = $atts['hide_categories'];
$show_categories = $atts['show_categories'];

$filters = explode(',', $atts['filters']);
$filters = array_map('trim', $filters);

$filters_names = explode(',', $atts['filters_names']);
$filters_names = array_map('trim', $filters_names);

// Scripts
$assets_dir = JACKCA_PLUGIN_URL . 'assets/';

wp_register_script('jack-2-list-views', $assets_dir.'jack-2-list-views/js/main.js', [], JACKCA_VERSION, true);
wp_localize_script('jack-2-list-views', 'JACKCA_CALENDAR', [
	'endpoint' => 'https://app.jackrabbitclass.com/jr3.0/Openings/OpeningsJson',
	'orgid' => $orgid,
	'filters' => $filters,
	'filters_names' => $filters_names,
	'message_weekends' => jackca_get_message_weekends(),
	'filter_classtype' => jacka_get_classtype_filter_options(),
	'location_colors' => jacka_get_location_colors(),
	'fee_labels' => [$fee_label1, $fee_label2],
	'view' => $view,
	'list_view_bottom_categories' => jacka_get_categories_at_the_bottom_of_the_list_view(),
	'btn_list' => $atts['btn_list'],
	'btn_list_2' => $atts['btn_list_2'],
	'hide_class_type_first_load' => $hide_class_type_first_load,
	'hide_categories' => $hide_categories,
	'show_categories' => $show_categories,
	'category_images' => jacka_get_list_categories_images()
] );
wp_enqueue_style( 'jack-2-list-views', $assets_dir.'jack-2-list-views/css/index.css', array(), JACKCA_VERSION );
wp_enqueue_script('jack-2-list-views');

?>

<div id="app-2-list-views"></div>
<div id="modal"></div>
