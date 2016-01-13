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

if ( ! class_exists( 'Debug_Bar_Localization_Log_MO_file_Entry' ) ) {

	/**
	 * Class to hold information on an individual MO file.
	 */
	class Debug_Bar_Localization_Log_MO_file_Entry {

		/**
		 * Keyword used for the 'unknown' add-on type.
		 *
		 * @const string
		 */
		const UNKNOWN_TYPE = 'unknown';

		/**
		 * The full path to the MO file.
		 *
		 * @var string
		 */
		private $mo_file;

		/**
		 * The type of add-on this MO file applies to.
		 * Possible values: 'core', 'theme', 'muplugin', 'plugin' or 'unknown'.
		 *
		 * @var string
		 */
		private $type = '';

		/**
		 * Best guess of whether WP will load this file or not.
		 *
		 * @var bool
		 */
		private $loaded = false;

		/**
		 * Human readable file permissions in numeric format.
		 *
		 * @var string
		 */
		private $file_permissions = '';


		/**
		 * Constructor, set all properties.
		 *
		 * @param string $mo_file The full path to the file WP will try to load.
		 */
		public function __construct( $mo_file ) {
			$this->mo_file = $mo_file;
			$this->set_type();
			$this->set_loaded();
			$this->set_file_permissions();
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
		 * Magic method - get a string representation of this object, in this case, the filename.
		 *
		 * @return string
		 */
		public function __toString() {
			return (string) $this->mo_file;
		}


		/**
		 * Set the $type property based on a best guess the type of WP add-on this text-domain file is used for.
		 */
		private function set_type() {
			if ( false !== strpos( $this->mo_file, WPMU_PLUGIN_DIR ) ) {
				$this->type = 'muplugin';
			}
			else if ( false !== strpos( $this->mo_file, WP_PLUGIN_DIR ) || false !== strpos( $this->mo_file, WP_LANG_DIR . '/plugins/' ) ) {
				$this->type = 'plugin';
			}
			else if ( ( false !== strpos( $this->mo_file, get_stylesheet_directory() ) || false !== strpos( $this->mo_file, get_template_directory() ) ) ||  false !== strpos( $this->mo_file, WP_LANG_DIR . '/themes/' ) ) {
				$this->type = 'theme';
			}
			else if ( false !== strpos( $this->mo_file, WP_LANG_DIR ) ) {
				$this->type = 'core';
			}
			else {
				$this->type = self::UNKNOWN_TYPE;
			}
		}


		/**
		 * Set the $loaded property based on a best guess of whether this file would be loaded.
		 */
		private function set_loaded() {
			$this->loaded = ( @is_readable( $this->mo_file ) && ! is_dir( $this->mo_file ) );
		}


		/**
		 * Set the $file_permissions property to a human readable string if the file exists.
		 */
		protected function set_file_permissions() {
			if ( file_exists( $this->mo_file ) ) {
				$this->file_permissions = substr( sprintf( '%o', fileperms( $this->mo_file ) ), -4 );
			}
		}
	} // End of class.

} // End of class exists.
