<?php
/**
 * Debug Bar Localization, a WordPress plugin.
 *
 * @package     WordPress\Plugins\Debug Bar Localization
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/debug-bar-localization
 * @since       1.0
 * @version     1.0
 *
 * @copyright   2016 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 */

// Avoid direct calls to this file.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'Debug_Bar_Localization_logger' ) ) {

	/**
	 * Class to log all load_..._textdomain calls made within WP.
	 */
	class Debug_Bar_Localization_Logger {

		/**
		 * Log of all text domain load calls.
		 *
		 * Contains \Debug_Bar_Localization_Log_Domain_Entry objects.
		 *
		 * @var array
		 */
		private $load_log = array();

		/**
		 * Array of all text domains which were unloaded during this page load.
		 *
		 * @var array
		 */
		private $unload_log = array();

		/**
		 * Counter holding the number of file load requests made.
		 *
		 * @var int
		 */
		private $counter = 0;

		/**
		 * Array of all text domains for which translation were requested.
		 *
		 * @var array
		 */
		private $domain_log = array();


		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'load_textdomain', array( $this, 'log_textdomain_calls' ), 10, 2 );
			add_action( 'unload_textdomain', array( $this, 'log_unload_calls' ), 10 );

			require_once dirname( __FILE__ ) . '/class-debug-bar-localization-log-domain-entry.php';

			/*
			 * Deal with the fall-out of Trac ticket 21319 which was merged in WP 4.5.
			 * @see https://core.trac.wordpress.org/ticket/21319
			 */
			if ( version_compare( $GLOBALS['wp_version'], '4.4.99', '>=' ) ) {
				add_filter( 'gettext', array( $this, 'log_requested_domains' ), 10, 3 );
				add_filter( 'gettext_with_context', array( $this, 'log_requested_domains' ), 10, 4 );
				add_filter( 'ngettext', array( $this, 'log_requested_domains' ), 10, 5 );
				add_filter( 'ngettext_with_context', array( $this, 'log_requested_domains' ), 10, 6 );
			}
		}


		/**
		 * Log an individual load_.._textdomain call.
		 *
		 * @param string $domain  The text domain for which the call was made.
		 * @param string $mo_file The full path to the MO file WP will try to load.
		 */
		public function log_textdomain_calls( $domain, $mo_file ) {
			if ( ! isset( $this->load_log[ $domain ] ) ) {
				$this->load_log[ $domain ] = new Debug_Bar_Localization_Log_Domain_Entry( $domain );
			}
			$this->load_log[ $domain ]->add_file( $mo_file );
			$this->counter++;
		}


		/**
		 * Log an individual UNload_textdomain call.
		 *
		 * @param string $domain The text domain which WP will unload.
		 */
		public function log_unload_calls( $domain ) {
			$this->unload_log[ $domain ] = $domain;
		}


		/**
		 * Log the domain when a get_text translation request is made.
		 *
		 * @params mixed The first param will hold the translation(s) and is expected to be returned.
		 *               The last param always holds the $domain.
		 *
		 * @return string Unchanged translation.
		 */
		public function log_requested_domains() {
			$args               = func_get_args();
			$this->domain_log[] = array_pop( $args );
			return $args[0];
		}


		/**
		 * Get access to a private property.
		 *
		 * @param string $property Property name.
		 *
		 * @return mixed The value of the property or null if the property does not exist.
		 */
		public function __get( $property ) {
			if ( isset( $this->{$property} ) ) {
				return $this->{$property};
			}
		}


		/**
		 * Get a list of all domains for which translations have been requested.
		 *
		 * @return array
		 */
		public function get_requested_domains() {
			if ( version_compare( $GLOBALS['wp_version'], '4.4.99', '<' ) ) {
				return array_keys( $GLOBALS['l10n'] );
			}
			else {
				return array_unique( $this->domain_log );
			}
		}


		/**
		 * Filter the logs based on add-on type.
		 *
		 * @param string $type Add-on type. Valid values: 'core', 'theme', 'muplugin', 'plugin' or 'unknown'.
		 *
		 * @return array
		 */
		public function filter_logs_on_type( $type ) {
			$filtered = array();

			if ( ! empty( $this->load_log ) ) {
				foreach ( $this->load_log as $domain => $logs ) {
					if ( $logs->get_type() === $type ) {
						$filtered[ $domain ] = $logs;
					}
				}
			}

			if ( ! empty( $filtered ) ) {
				ksort( $filtered );
			}

			return $filtered;
		}
	} // End of class.

} // End of class exists.
