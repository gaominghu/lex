<?php

// THIS IS A COMMENT WITH A TEMPLATE ENTRY Lex

/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
* @package   Lex
* @author    gabrielstuff contact@soixantecircuits.fr
* @license   GPL-2.0+
* @link      http://soixantecircuits.fr
* @copyright 2014 gabrielstuff
 *
 * @wordpress-plugin
 * Plugin Name:       Lex
 * Plugin URI:        
 * Description:       @TODO
 * Version:           1.0.0
 * Author:            gabrielstuff
 * Author URI:        http://soixantecircuits.fr
 * Text Domain:       plugin-name-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages

 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-lex.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */

register_activation_hook( __FILE__, array( 'Lex', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Lex', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Lex', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-lex-admin.php' );
	add_action( 'plugins_loaded', array( 'Lex_Admin', 'get_instance' ) );

}
