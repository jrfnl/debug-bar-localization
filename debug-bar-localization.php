<?php
/**
 * Debug Bar Localization, a WordPress plugin.
 *
 * @package     WordPress\Plugins\Debug Bar Localization
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/debug-bar-localization
 * @version     1.1
 *
 * @copyright   2016 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Debug Bar Localization
 * Plugin URI:  https://wordpress.org/plugins/debug-bar-localization/
 * Description: Debug Bar Localization adds a new panel to the Debug Bar which displays information on the locale for your install and the language files loaded.
 * Version:     1.1
 * Author:      Juliette Reinders Folmer
 * Author URI:  http://www.adviesenzo.nl/
 * Depends:     Debug Bar
 * Text Domain: debug-bar-localization
 * Domain Path: /languages
 * Copyright:   2016 Juliette Reinders Folmer
 */

// Avoid direct calls to this file.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 * Make sure the plugin slug for this plugin is always available.
 */
if ( ! defined( 'DB_LOCALIZATION_BASENAME' ) ) {
	define( 'DB_LOCALIZATION_BASENAME', plugin_basename( __FILE__ ) );
}


if ( ! function_exists( 'db_localization_has_parent_plugin' ) ) {
	add_action( 'admin_init', 'db_localization_has_parent_plugin' );

	/**
	 * Show admin notice & de-activate itself if the debug-bar parent plugin is not active.
	 */
	function db_localization_has_parent_plugin() {
		if ( is_admin() && ( ! class_exists( 'Debug_Bar' ) && current_user_can( 'activate_plugins' ) ) && is_plugin_active( DB_LOCALIZATION_BASENAME ) ) {
			add_action( 'admin_notices', create_function( null, 'echo \'<div class="error"><p>\', sprintf( __( \'Activation failed: Debug Bar must be activated to use the <strong>Debug Bar Localization</strong> Plugin. %sVisit your plugins page to install & activate.\', \'debug-bar-localization\' ), \'<a href="\' . admin_url( \'plugin-install.php?tab=search&s=debug+bar\' ) . \'">\' ), \'</a></p></div>\';' ) );

			deactivate_plugins( DB_LOCALIZATION_BASENAME, false, is_network_admin() );

			// Add to recently active plugins list.
			$insert = array(
				DB_LOCALIZATION_BASENAME => time(),
			);

			if ( ! is_network_admin() ) {
				update_option( 'recently_activated', ( $insert + (array) get_option( 'recently_activated' ) ) );
			} else {
				update_site_option( 'recently_activated', ( $insert + (array) get_site_option( 'recently_activated' ) ) );
			}

			// Prevent trying again on page reload.
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}


if ( version_compare( $wp_version, '4.0', '>' ) ) {

	if ( ! function_exists( 'debug_bar_localization_panel' ) ) {
		add_filter( 'debug_bar_panels', 'debug_bar_localization_panel' );

		/**
		 * Add the Debug Bar Localization panel to the Debug Bar.
		 *
		 * @param array $panels Existing debug bar panels.
		 *
		 * @return array
		 */
		function debug_bar_localization_panel( $panels ) {
			if ( ! class_exists( 'Debug_Bar_Localization' ) ) {
				require_once 'class-debug-bar-localization.php';
			}
			$panels[] = new Debug_Bar_Localization();
			return $panels;
		}
	}


	if ( ! function_exists( 'db_localization_network_load_first' ) ) {
		add_filter( 'pre_update_site_option_active_sitewide_plugins', 'db_localization_network_load_first', 999 );

		/**
		 * Rearrange the order of the 'active_sitewide_plugins' array before it's saved to make sure
		 * this plugin is loaded first so it can catch all load text domain calls - including
		 * the ones which are made *way* too early.
		 *
		 * @param array $value New value of the Network option.
		 *
		 * @return array
		 */
		function db_localization_network_load_first( $value ) {
			if ( ! is_array( $value ) || ! isset( $value[ DB_LOCALIZATION_BASENAME ] ) ) {
				return $value;
			}

			$this_plugin = $value[ DB_LOCALIZATION_BASENAME ];
			$insert      = array(
				DB_LOCALIZATION_BASENAME => $this_plugin,
			);
			unset( $value[ DB_LOCALIZATION_BASENAME ] );
			return array_merge( $this_plugin, $value );
		}
	}


	if ( ! function_exists( 'db_localization_load_first' ) ) {
		add_filter( 'pre_update_option_active_plugins', 'db_localization_load_first', 999 );

		/**
		 * Rearrange the order of the 'active plugins' array before it's saved to make sure
		 * this plugin is loaded first so it can catch all load text domain calls - including
		 * the ones which are made *way* too early.
		 *
		 * @param array $value New value of the option.
		 *
		 * @return array
		 */
		function db_localization_load_first( $value ) {
			if ( ! is_array( $value ) ) {
				return $value;
			}

			$key = array_search( DB_LOCALIZATION_BASENAME, $value, true );
			if ( false !== $key ) {
				unset( $value[ $key ] );
				array_unshift( $value, DB_LOCALIZATION_BASENAME );
			}

			return $value;
		}
	}

	require_once dirname( __FILE__ ) . '/class-debug-bar-localization-logger.php';
	$GLOBALS['db_localization_logger'] = new Debug_Bar_Localization_Logger();
}
