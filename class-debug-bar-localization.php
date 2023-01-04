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


if ( ! class_exists( 'Debug_Bar_Localization' ) && class_exists( 'Debug_Bar_Panel' ) ) {

	/**
	 * This class extends the functionality provided by the parent plugin "Debug Bar" by adding a
	 * panel showing information about the the locale for your install and the language files loaded.
	 */
	class Debug_Bar_Localization extends Debug_Bar_Panel {

		/**
		 * Version in which the styles were last updated.
		 * Used to break out of the cache.
		 *
		 * @const string
		 */
		const STYLES_VERSION = '1.1';

		/**
		 * Plugin slug.
		 *
		 * @const string
		 */
		const NAME = 'debug-bar-localization';

		/**
		 * Array holding the available translations as known by WP.
		 *
		 * @var array
		 */
		private $wp_translations = array();

		/**
		 * The various load_..._textdomain() call types.
		 * Key is the internal name used by this plugin, value is the title used for
		 * the section displaying those calls.
		 *
		 * @var array
		 */
		private $load_call_types = array();

		/**
		 * The logger object which contains all logged calls.
		 *
		 * @var \Debug_Bar_Localization_Logger
		 */
		private $logger = array();


		/**
		 * The domains which have been requested in translation calls.
		 *
		 * @var array
		 */
		private $domains = array();


		/**
		 * Constructor.
		 */
		public function init() {
			$this->load_textdomain( self::NAME );
			$this->title( __( 'Localization', 'debug-bar-localization' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			if ( ! function_exists( 'wp_get_available_translations' ) ) {
				require_once ABSPATH . 'wp-admin/includes/translation-install.php';
			}
			$this->wp_translations = wp_get_available_translations();

			$this->load_call_types = array(
				'core'     => __( 'WP Core', 'debug-bar-localization' ),
				'theme'    => __( 'Themes', 'debug-bar-localization' ),
				'muplugin' => __( 'Must-Use Plugins', 'debug-bar-localization' ),
				'plugin'   => __( 'Plugins', 'debug-bar-localization' ),
				'unknown'  => __( 'Unknown', 'debug-bar-localization' ),
			);
		}


		/**
		 * Load the plugin text strings.
		 *
		 * Compatible with use of the plugin in the must-use plugins directory.
		 *
		 * {@internal No longer needed since WP 4.6, though the language loading in
		 * WP 4.6 only looks at the `wp-content/languages/` directory and disregards
		 * any translations which may be included with the plugin.
		 * This is acceptable for plugins hosted on org, especially if the plugin
		 * is new and never shipped with it's own translations, but not when the plugin
		 * is hosted elsewhere.
		 * Can be removed if/when the minimum required version for this plugin is ever
		 * upped to 4.6. The `languages` directory can be removed in that case too.
		 * See: {@link https://core.trac.wordpress.org/ticket/34213} and
		 * {@link https://core.trac.wordpress.org/ticket/34114} }}
		 *
		 * @param string $domain Text domain to load.
		 */
		protected function load_textdomain( $domain ) {
			if ( function_exists( '_load_textdomain_just_in_time' ) ) {
				return;
			}

			if ( is_textdomain_loaded( $domain ) ) {
				return;
			}

			$lang_path = dirname( plugin_basename( __FILE__ ) ) . '/languages';
			if ( false === strpos( __FILE__, basename( WPMU_PLUGIN_DIR ) ) ) {
				load_plugin_textdomain( $domain, false, $lang_path );
			} else {
				load_muplugin_textdomain( $domain, $lang_path );
			}
		}


		/**
		 * Enqueue css file.
		 */
		public function enqueue_scripts() {
			$suffix = ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );
			wp_enqueue_style( self::NAME, plugins_url( 'css/debug-bar-localization' . $suffix . '.css', __FILE__ ), array( 'debug-bar' ), self::STYLES_VERSION );
		}


		/**
		 * Should the tab be visible ?
		 * You can set conditions here so something will for instance only show on the front- or the
		 * back-end.
		 */
		public function prerender() {
			$this->set_visible( true );
		}


		/**
		 * Render the tab content.
		 */
		public function render() {
			$this->logger  = $GLOBALS['db_localization_logger'];
			$this->domains = $this->logger->get_requested_domains();

			// Prep data for the headers.
			$current_locale           = get_locale();
			$current_language_native  = $current_locale;
			$current_language_english = $current_locale;
			if ( isset( $this->wp_translations[ $current_locale ] ) ) {
				$current_language_native  = $this->wp_translations[ $current_locale ]['native_name'];
				$current_language_english = $this->wp_translations[ $current_locale ]['english_name'];
			}

			$unique_text_domains = array_unique( array_merge( $this->domains, array_keys( $this->logger->load_log ) ) );

			echo '
		<h2><span>', esc_html__( 'Current locale:', 'debug-bar-localization' ), '</span>', esc_html( $current_locale ), '</h2>
		<h2><span>', esc_html__( 'Current language:', 'debug-bar-localization' ), '</span>', esc_html( $current_language_native ), '<small>(', esc_html( $current_language_english ), ')</small></h2>
		<h2><span>', esc_html__( 'WPLANG:', 'debug-bar-localization' ), '</span>';

			if ( defined( 'WPLANG' ) ) {
				echo esc_html( WPLANG );

			} else {
				echo '<small>', esc_html__( '(not defined)', 'debug-bar-localization' ), '</small>';
			}

			echo '</h2>
		<h2><span>', wp_kses_post( __( 'Text domains<br />seen:', 'debug-bar-localization' ) ), '</span>', absint( count( $unique_text_domains ) ), '</h2>
		<h2><span>', wp_kses_post( __( 'Number of attempts<br />made to load<br />a translation:', 'debug-bar-localization' ) ), '</span>', absint( $this->logger->counter ), '</h2>';

			$this->render_installed_lang_section();
			$this->render_no_load_textdomain_section();
			$this->render_unload_textdomain_section();
			$this->render_load_textdomain_section();
			$this->render_inefficient_load_textdomain_section();
		}


		/**
		 * Render the 'Installed languages' section which shows the languages installed for WP Core.
		 */
		protected function render_installed_lang_section() {
			$available      = get_available_languages();
			$current_locale = get_locale();
			$loaded_class   = ' class="loaded"';

			echo '
			<div id="db-localization-available-languages">
				<h3>', esc_html__( 'Installed languages', 'debug-bar-localization' ), '</h3>

				<table class="debug-bar-table ', esc_attr( self::NAME ), '">
					<thead>
						<tr>
							<th>', esc_html__( 'Locale', 'debug-bar-localization' ), '</th>
							<th>', esc_html__( 'Language (native name)', 'debug-bar-localization' ), '</th>
							<th>', esc_html__( 'Language (English name)', 'debug-bar-localization' ), '</th>
							<th>', esc_html__( 'WP Core translation last updated:', 'debug-bar-localization' ), '</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th>' . sprintf( /* translators: %s: Locale. */ esc_html__( '%s:', 'debug-bar-localization' ), 'en_US' ) . '</th>
							<td>English (United States)</td>
							<td>-</td>
							<td>-</td>
						</tr>';

			if ( ! empty( $available ) && is_array( $available ) ) {
				foreach ( $available as $locale ) {
					$class = ( $current_locale === $locale ) ? $loaded_class : '';
					echo // WPCS: xss ok.
					'
						<tr>
							<th', $class, '>', sprintf( /* translators: %s: Locale. */ esc_html__( '%s:', 'debug-bar-localization' ), esc_html( $this->wp_translations[ $locale ]['language'] ) ), '</th>
							<td', $class, '>', esc_html( $this->wp_translations[ $locale ]['native_name'] ), '</td>
							<td', $class, '>', esc_html( $this->wp_translations[ $locale ]['english_name'] ), '</td>
							<td', $class, '>', esc_html( $this->wp_translations[ $locale ]['updated'] ), '</td>
						</tr>';
				}
				unset( $locale );
			}

			echo '
					</tbody>
				</table>
			</div>';
		}


		/**
		 * Render the 'Text-domain without a load call' section.
		 */
		protected function render_no_load_textdomain_section() {
			$diff = array_diff( $this->domains, array_keys( $this->logger->load_log ) );

			if ( ! empty( $diff ) && is_array( $diff ) ) {
				echo '
			<div id="db-localization-no-load-textdomain">
				<h3>', esc_html__( 'Textdomains without a "load" call', 'debug-bar-localization' ), '</h3>
				<p>', esc_html__( 'To allow for text strings to be localized, the text-domain for the theme/plugin has to be loaded and all text strings used, have to be wrapped in a translation function.', 'debug-bar-localization' ), '</p>
				<p>',
				/* translators: %s is a function call code snippet. */
				sprintf( esc_html__( 'The below text-domains were used in translation functions, however the text-domain was never loaded using a %s call.', 'debug-bar-localization' ), '<code>load_{default|muplugin|plugin|theme|child_theme}_textdomain()</code>' ), '</p>
				<ul>';
				foreach ( $diff as $missing ) {
					echo '
					<li>', esc_html( $missing ), '</li>';
				}
				echo '
				</ul>
			</div>';
			}
		}


		/**
		 * Render the 'Potentially inefficient load text-domain calls' section.
		 */
		protected function render_inefficient_load_textdomain_section() {
			$diff = array_diff( array_keys( $this->logger->load_log ), $this->domains );

			// Remove the db-pretty-output domain as it is normally used *after* this panel.
			$diff = array_diff( $diff, array( 'db-pretty-output' ) );

			if ( ! empty( $diff ) && is_array( $diff ) ) {
				$kses_allowed = array(
					'em' => array(),
				);

				echo '
			<div id="db-localization-inefficient-load-textdomain">
				<h3>', esc_html__( 'Potentially inefficient calls', 'debug-bar-localization' ), '</h3>
				<p>', esc_html__( 'Loading a text domain when it will not be used is inefficient. Lean, or lazy loading is a programming best practice which comes down to only loading files if and when needed.', 'debug-bar-localization' ), '</p>
				<p>', wp_kses( __( 'The below textdomains <em>were</em> loaded, but were <em>not used</em> in a localization call during this page load.', 'debug-bar-localization' ), $kses_allowed ), esc_html__( 'This is not always "wrong", but these calls could benefit from a visual code inspection.', 'debug-bar-localization' ), '</p>
				<ul>';
				foreach ( $diff as $unused ) {
					echo '
					<li>', esc_html( $unused ), '</li>';
				}
				echo '
				</ul>
			</div>';
			}
		}


		/**
		 * Render the 'Unloaded text-domains' section showing which text-domains were unloaded during this page load.
		 *
		 * Excludes the 'default' domain as that will always be unloaded if a translation is used.
		 */
		protected function render_unload_textdomain_section() {
			$unloaded = $this->logger->unload_log;

			/*
			 * Don't show the 'default' domain as that text domain is always unloaded if the language is set
			 * to anything other than English.
			 */
			unset( $unloaded['default'] );

			if ( ! empty( $unloaded ) && is_array( $unloaded ) ) {
				echo '
			<div id="db-localization-unload-textdomain">
				<h3>', esc_html__( 'Textdomains which were unloaded during this page load', 'debug-bar-localization' ), '</h3>
				<ul>';
				foreach ( $unloaded as $domain ) {
					echo '
					<li>', esc_html( $domain ), '</li>';
				}
				echo '
				</ul>
			</div>';
			}
		}


		/**
		 * Render the 'Loaded text-domains' section.
		 */
		protected function render_load_textdomain_section() {
			if ( $this->logger->counter > 0 ) {
				echo '
			<div id="db-localization-load-textdomain-calls">
				<h3>', esc_html__( 'Load textdomain calls made', 'debug-bar-localization' ), '</h3>';

				foreach ( $this->load_call_types as $type => $unused ) {
					$this->render_load_textdomain_table( $type );
				}

				echo '
			</div>';

			} else {
				$kses_allowed = array(
					'code' => array(),
				);

				echo '
				<hr />
				<p>', wp_kses( __( 'No text domain load calls made. This should never happen...', 'debug-bar-localization' ), $kses_allowed ), '</p>';
			}
		}


		/**
		 * Render a loaded text-domains table for a a particular type of add-on.
		 *
		 * @param string $type The add-on type to create the table for.
		 */
		protected function render_load_textdomain_table( $type ) {
			$logs            = $this->logger->filter_logs_on_type( $type );
			$is_plugins_page = ( is_admin() && 'plugins' === get_current_screen()->base );

			if ( ! empty( $logs ) && is_array( $logs ) ) {
				echo '
				<h4>',
				/* translators: %s = type of the load textdomain call, i.e. core, plugins etc. */
				sprintf( esc_html__( 'For %s:', 'debug-bar-localization' ), esc_html( $this->load_call_types[ $type ] ) ), '</h4>';

				echo // WPCS: xss ok.
				'
		<table class="debug-bar-table ', self::NAME, '">', $this->get_table_header( count( $logs ) > 5 ), '
			<tbody>';

				foreach ( $logs as $domain => $domain_object ) {
					$string_count   = $domain_object->count_translated_strings();
					$string_count   = ( 0 === $string_count ) ? '-' : $string_count;
					$loaded         = $domain_object->has_translation_loaded();
					$domain_class   = ( true === $loaded ) ? 'loaded' : 'not-loaded';
					$has_duplicates = $domain_object->has_duplicate_files();

					echo '
				<tr';

					if ( true === $has_duplicates && ! $is_plugins_page ) {
						echo ' class="has-duplicates"';
					}

					echo '>
					<th class="', esc_attr( $domain_class ), '">', esc_html( $domain ), '</th>
					<td>', esc_html( $string_count ), '</td>
					<td>';

					if ( true === $loaded ) {
						$this->render_last_updated( $domain );
					} else {
						echo '-';
					}

					echo '</td>
					<td>';

					$this->render_file_list( $domain_object );

					if ( true === $has_duplicates && ! $is_plugins_page ) {
						echo '
				</tr>
				<tr class="has-duplicates">
					<td colspan="4" class="duplicates-warning">',
							sprintf(
								/* translators: %s = code snippet. */
								esc_html__( 'WordPress tried to load the same .mo file more than once. This can happen if the requested translation is not found and the %s call for this domain was made several times. Please contact the theme or plugin developer to get this fixed.', 'debug-bar-localization' ),
								'<code>load_..._textdomain()</code>'
							), '</td>';
					}

					echo '
					</td>
				</tr>';
				}

				echo '
			</tbody>
		</table>';
			}
		}


		/**
		 * Create the table header for a 'load text domain' table.
		 *
		 * @param bool $double Whether or not to repeat the column labels at the end of the table.
		 *
		 * @return string
		 */
		protected function get_table_header( $double ) {
			static $header_row;

			/* Create header row. */
			if ( ! isset( $header_row ) ) {
				$header_row = '
				<tr>
					<th class="col-1">' . esc_html__( 'Text domain', 'debug-bar-localization' ) . '</th>
					<th class="col-2">' . esc_html__( 'Translated strings', 'debug-bar-localization' ) . '</th>
					<th class="col-3">' . esc_html__( 'Last updated', 'debug-bar-localization' ) . '</th>
					<th class="col-4">' . esc_html__( 'Source files tried', 'debug-bar-localization' ) . '</th>
				</tr>';
			}

			$table_header = '
			<thead>
			' . $header_row . '
			</thead>';

			if ( true === $double ) {
				$table_header .= '
			<tfoot>
			' . $header_row . '
			</tfoot>';
			}

			return $table_header;
		}


		/**
		 * Render the content for the "Last updated" cell based on the headers found in the MO file.
		 *
		 * @param string $domain The current text domain.
		 */
		protected function render_last_updated( $domain ) {
			if ( ! isset( $GLOBALS['l10n'][ $domain ] ) ) {
				echo '-';
				return;
			}

			$x_generator   = $GLOBALS['l10n'][ $domain ]->get_header( 'X-Generator' );
			$revision_date = $GLOBALS['l10n'][ $domain ]->get_header( 'PO-Revision-Date' );

			if ( false === $revision_date ) {
				echo '-';
				return;
			}

			$generator = __( 'unknown', 'debug-bar-localization' );
			if ( ! empty( $x_generator ) && is_string( $x_generator ) ) {
				if ( false !== strpos( $x_generator, 'GlotPress' ) ) {
					$generator = 'GlotPress';
				} elseif ( false !== strpos( $x_generator, 'Poedit' ) ) {
					$generator = 'Poedit';
				} else {
					$generator = $x_generator;
				}
			}

			echo wp_kses_post( sprintf(
				/* translators: 1: date, 2: translation program name. */
				__( '%1$s via %2$s', 'debug-bar-localization' ),
				substr( $revision_date, 0, 10 ),
				'<em>' . esc_html( $generator ) . '</em>'
			) );
		}


		/**
		 * Render a list of the files which WP attempted to load to obtain a translation.
		 *
		 * @param object $domain_object The domain object for the current text domain.
		 */
		protected function render_file_list( $domain_object ) {
			$files = $domain_object->get_files();

			if ( ! empty( $files ) && is_array( $files ) ) {
				echo '
							<ul>';
				foreach ( $files as $file ) {
					if ( $file->loaded ) {
						echo '
								<li class="loaded">', esc_html( $file ), ' <span>(', esc_html( $file->file_permissions ), ')</span></li>';
					} else {
						echo '
								<li class="not-loaded">', esc_html( $file ), '</li>';
					}
				}
				echo '
							</ul>';

			} else {
				echo '-';
			}
		}
	} // End of class Debug_Bar_Localization.

} // End of class_exists wrapper.
