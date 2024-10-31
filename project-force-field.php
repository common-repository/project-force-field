<?php
/*
 * Plugin Name: Project Force Field
 * Description: Changes the wp-login.php file to protect against the dime-a-dozen brute login attacks.
 * Author: Faison Zutavern
 * Author URI: http://www.orionweb.net/
 * Version: 0.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


// Load all the necessary classes.
$ogff_classes_dir = plugin_dir_path( __FILE__ ) . '/classes/';

$ogff_class_files   = array();
$ogff_class_files[] = 'class-base-file-manager.php';
$ogff_class_files[] = 'class-wordpress-file-manager.php';
$ogff_class_files[] = 'class-force-field-rewrite-manager.php';
$ogff_class_files[] = 'class-base-system-manager.php';
$ogff_class_files[] = 'class-wordpress-system-manager.php';
$ogff_class_files[] = 'class-force-field.php';

foreach ( $ogff_class_files as $file ) {
	require_once( $ogff_classes_dir . $file );
}

include_once( ABSPATH . 'wp-admin/includes/file.php' );
include_once( ABSPATH . 'wp-admin/includes/misc.php' );

$ogff_new_login = OG_Force_Field::DEFAULT_NEW_LOGIN;

if ( defined( 'OGFF_LOGIN' ) && OGFF_LOGIN ) {
	$ogff_new_login = OGFF_LOGIN;
}

$og_force_field = new OG_Force_Field( new FZ_WordPress_System_Manager(), $ogff_new_login );

if ( $og_force_field->is_force_field_compatible() ) {
	register_activation_hook( __FILE__, array( $og_force_field, 'activate' ) );
	register_deactivation_hook( __FILE__, array( $og_force_field, 'deactivate' ) );

	do_action( 'ogff_ready' );
}