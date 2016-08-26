<?php
/**
 * Debug Bar Localization, a WordPress plugin.
 *
 * @package     WordPress\Plugins\Debug Bar Localization
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/debug-bar-localization
 * @since       1.0
 * @version     1.1
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

if ( ! class_exists( 'Debug_Bar_Localization_Log_Domain_Entry' ) ) {

	/**
	 * Class to hold information on an individual text domain.
	 */
	class Debug_Bar_Localization_Log_Domain_Entry {

		/**
		 * The name of the text domain.
		 *
		 * @var string
		 */
		private $domain;

		/**
		 * Array of \Debug_Bar_Localization_Log_MO_file_Entry objects.
		 * One for each MO file WP tried to load for this text domain.
		 *
		 * @var array
		 */
		private $mo_files = array();


		/**
		 * Constructor
		 *
		 * @param string $domain Text domain.
		 */
		public function __construct( $domain ) {
			$this->domain = $domain;

			require_once dirname( __FILE__ ) . '/class-debug-bar-localization-log-mo-file-entry.php';
		}


		/**
		 * Log the loading of an MO file.
		 *
		 * @param string $mo_file Full path to the MO file WP is trying to load.
		 */
		public function add_file( $mo_file ) {
			// Make sure we have the name of the actual file as used after filtering.
			$actual_mo_file   = apply_filters( 'load_textdomain_mofile', $mo_file, $this->domain );
			$this->mo_files[] = new Debug_Bar_Localization_Log_MO_file_Entry( $actual_mo_file, $mo_file );
		}


		/**
		 * Magic method - get a string representation of this object, in this case, the name of the text domain.
		 *
		 * @return string
		 */
		public function __toString() {
			return (string) $this->domain;
		}


		/**
		 * Get the MO files which WP tried to load for this text domain.
		 *
		 * @return array
		 */
		public function get_files() {
			return $this->mo_files;
		}


		/**
		 * Get the type of add-on this text domain applies to.
		 *
		 * Best guess, uses the first not 'unknown' type it encounters. Defaults to 'unknown'.
		 *
		 * @return string
		 */
		public function get_type() {
			foreach ( $this->mo_files as $file ) {
				if ( Debug_Bar_Localization_Log_MO_file_Entry::UNKNOWN_TYPE !== $file->type ) {
					return $file->type;
				}
			}
			return Debug_Bar_Localization_Log_MO_file_Entry::UNKNOWN_TYPE;
		}


		/**
		 * Get a count of the number of files WP tried to load for this text domain.
		 *
		 * @return int
		 */
		public function count_files() {
			return count( $this->mo_files );
		}


		/**
		 * Check if load_..._textdomain() calls for this domain tried to load the same file.
		 *
		 * This is an indication of ineffective load_..._textdomain() calls and should be fixed in
		 * the plugin or theme.
		 * For the core plugins page were this also occurs, I've opened ticket #....
		 *
		 * @return bool True if duplicate files were found. False otherwise.
		 */
		public function has_duplicate_files() {
			$unique = array();

			// Create an array which only consists of unique filenames.
			foreach ( $this->mo_files as $file ) {
				$unique[ (string) $file ] = true;
			}

			return ( count( $unique ) < $this->count_files() );
		}


		/**
		 * Whether any translations where found for this text domain.
		 *
		 * @return bool
		 */
		public function has_translation_loaded() {
			foreach ( $this->mo_files as $file ) {
				if ( true === $file->loaded ) {
					return true;
				}
			}
			return false;
		}


		/**
		 * Get a count of the number of translated strings found for this text domain.
		 *
		 * @todo Figure out a way to deal with text domains which have been loaded, but not used yet, i.e.
		 * which are logged, but not in the $l10n global.
		 *
		 * @return int
		 */
		public function count_translated_strings() {
			if ( ! isset( $GLOBALS['l10n'][ $this->domain ] ) ) {
				return 0;
			} else {
				return count( $GLOBALS['l10n'][ $this->domain ]->entries );
			}
		}
	} // End of class.

} // End of class exists.
