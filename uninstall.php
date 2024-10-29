<?php 

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}

$_ENV['ORBISIUS_WP_ADMIN_UI_SIMPLIFICATOR_UNINSTALL'] = 1;

require_once(dirname(__FILE__) . '/wp-admin-ui-simplificator.php');

$orb_wp_simple_ui_obj = Orbisius_WP_Admin_UI_Simplificator::get_instance();
$orb_wp_simple_ui_obj->on_uninstall();
