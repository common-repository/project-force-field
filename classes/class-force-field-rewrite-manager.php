<?php
/**
 * The Force Field Manager definition.
 *
 * @package ProjectForceField
 * @subpackage Base
 */

if ( ! class_exists( 'OG_Force_Field_Rewrite_Manager' ) ) :

	/**
	 * Force Field Rewrite Manager.
	 *
	 * This class does all the heavy lifting in regards to retrieving,
	 * storing, and general manipulating of mod_rewrite rules.
	 *
	 * @since 1.0
	 * @package ProjectForceField
	 * @subpackage Base
	 */
	class OG_Force_Field_Rewrite_Manager {

		/**
		 * Denotes the beginning and end of the Force Field section in the .htaccess file.
		 *
		 * @var string
		 */
		const MARKER = "OGFF";

		/**
		 * File Manager used for read and write operations.
		 *
		 * @var object {@see FZ_Base_File_Manager}
		 */
		private $file_manager;

		/**
		 * The file that the mod_rewrite rules are read and written to.
		 *
		 * @var string
		 */
		private $filename;

		/**
		 * The new login filename.
		 *
		 * @var string
		 */
		private $new_login;

		/**
		 * Are pretty permalinks enabled.
		 *
		 * @var boolean
		 */
		private $permalinks_enabled;

		function __construct( FZ_Base_File_Manager $file_manager, $filename, $new_login, $permalinks_enabled = true ) {

			if ( null == $filename ) {
				trigger_error( 'OG_Force_Field_Rewrite_Manager must be instantiated with a $filename', E_USER_ERROR);
			}

			if ( null == $new_login ) {
				trigger_error( 'OG_Force_Field_Rewrite_Manager must be instantiated with a $new_login', E_USER_ERROR);
			}

			$this->file_manager = $file_manager;

			$this->filename = $filename;

			$this->new_login = $new_login;

			$this->permalinks_enabled = $permalinks_enabled;
		}

		/**
		 * Creates the BEGIN and END sections for the Force Field in the .htaccess file.
		 *
		 * @since 1.0.0
		 * @todo Check for errors.
		 *
		 * @return bool True if successful.
		 */
		public function shields_up( $optional_blocks = null ) {

			$file_contents = $this->file_manager->get_file_contents( $this->filename );

			$file_contents = $this->add_force_field_section_to_contents( $file_contents, $optional_blocks );

			if ( true === $file_contents ) {
				return true;
			}

			$result = $this->file_manager->put_file_contents( $this->filename, $file_contents );

			return $result;
		}

		/**
		 * Removes the BEGIN and END sections for the Force Field in the .htaccess file.
		 *
		 * @since 1.0.0
		 * @todo Check for errors.
		 *
		 * @return bool True if successful.
		 */
		public function shields_down() {

			$file_contents = $this->file_manager->get_file_contents( $this->filename );

			$file_contents = $this->remove_force_field_section_from_contents( $file_contents );

			if ( true === $file_contents ) {
				return true;
			}

			$results = $this->file_manager->put_file_contents( $this->filename, $file_contents );

			return $results;
		}

		/**
		 * An easy way to remove clear out and replace the Force Field lines in the .htaccess file.
		 *
		 * This function is particularly useful between version updates.
		 *
		 * @since 0.6.0
		 */
		public function reset_shields( $optional_blocks = null ) {

			$file_contents = $this->file_manager->get_file_contents( $this->filename );

			$result_contents = $this->remove_force_field_section_from_contents( $file_contents );

			if ( true === $result_contents ) {
				$result_contents = $file_contents;
			}
			$file_contents = $result_contents;

			$file_contents = $this->add_force_field_section_to_contents( $file_contents, $optional_blocks );

			$result = $this->file_manager->put_file_contents( $this->filename, $file_contents );

			return $result;
		}

		/**
		 * Checks if the rewrite rule is up-to-date with the newest login, updating if needed.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True when up-to-date.
		 */
		public function check_polarity( $newest_login, $optional_blocks = null ) {
			$force_field_lines = $this->get_force_field_lines();
			
			if ( count( $force_field_lines ) === 0 ) {
				$this->shields_down();
				$this->shields_up();
			}

			$rewrite_line  = 'RewriteRule ^' . preg_quote( $newest_login ) . '$ wp-login.php [NC,L]';
			$line_position = array_search( $rewrite_line, $force_field_lines );

			$anti_enum_line = 'RewriteCond %{QUERY_STRING} ^/?author=([0-9]*)';
			$anti_position  = array_search( $anti_enum_line, $force_field_lines );

			$anti_enum_good = ( false !== $anti_position && $this->permalinks_enabled )
						   || ( false === $anti_position && ! $this->permalinks_enabled );

			if ( false !== $line_position && $anti_enum_good ) {
				return true;
			}

			$this->new_login = $newest_login;

			$force_field_lines = $this->generate_force_field_section_lines( $optional_blocks );

			$results = $this->save_force_field_lines( $force_field_lines );

			return $results;
		}

		private function get_force_field_lines() {
			return $this->file_manager->get_contents_from_marker( $this->filename, self::MARKER );
		}

		private function save_force_field_lines( $force_field_lines ) {
			return $this->file_manager->put_contents_array_with_marker( $this->filename, self::MARKER, $force_field_lines );
		}

		private function add_force_field_section_to_contents( $contents, $optional_blocks = null ) {

			$section_start = array_search( '# BEGIN ' . self::MARKER, $contents );

			if ( false !== $section_start ) {
				return true;
			}

			$ogff_sections = $this->generate_force_field_section_content( $optional_blocks );

			if ( '' == $contents[0] ) {
				array_shift( $contents );
			}
			
			array_splice( $contents, 0, 0, $ogff_sections );

			return $contents;
		}

		public function generate_force_field_section_content( $optional_blocks = null ) {

			$section_lines = $this->generate_force_field_section_lines( $optional_blocks );
			$ogff_sections = array(
				'',
				'# BEGIN ' . self::MARKER,
				'# END ' . self::MARKER,
				''
			);

			array_splice( $ogff_sections, 2, 0, $section_lines );

			return $ogff_sections;
		}

		private function generate_force_field_section_lines( $optional_blocks = null ) {
			$wp_login_file = preg_quote( 'wp-login.php' );

			$lines = array(
				'<IfModule mod_rewrite.c>',
				'RewriteEngine On',
				'RewriteRule ^' . preg_quote( $this->new_login ) . '$ wp-login.php [NC,L]',
				'RewriteCond %{THE_REQUEST} ' . $wp_login_file . ' [NC]',
				'RewriteRule ^' . $wp_login_file . '$ - [F]',
				'</IfModule>'
			);

			if ( $this->permalinks_enabled ) {
				$anti_enum = array(
					'RewriteCond %{REQUEST_URI}  ^/$',
					'RewriteCond %{QUERY_STRING} ^/?author=([0-9]*)',
					'RewriteRule ^(.*)$ - [F]'
				);
				array_splice( $lines, 5, 0, $anti_enum );
			}

			if ( is_array( $optional_blocks ) && 0 < count( $optional_blocks ) ) {
				$optional_lines = array();
				foreach ( $optional_blocks as $optional_block ) {
					$optional_lines[] = 'RewriteRule ^' . preg_quote( $optional_block ) . '$ - [F]';
				}
				array_splice( $lines, 5, 0, $optional_lines );
			}

			return $lines;
		}

		private function remove_force_field_section_from_contents( $contents ) {

			$force_field_start = array_search( '# BEGIN ' . self::MARKER, $contents );
			$force_field_end   = array_search( '# END ' . self::MARKER, $contents );

			if ( false === $force_field_start || false === $force_field_end ) {
				return true;
			}

			array_splice( $contents, $force_field_start, $force_field_end - $force_field_start + 1 );

			// Trim extra empty lines from the top of the file.
			if ( $contents[ 0 ] == '' ) {
				$trim_start = 0;
				$trim_end   = 0;

				for ( $i = 1; $i < count( $contents ); $i+= 1 ) {
					if ( $contents[ $i ] != '' ) {
						$trim_end = $i - 1;
						break;
					}
				}

				if ( 0 < $trim_end - $trim_start ) {
					array_splice( $contents, $trim_start, $trim_end - $trim_start );
				}
			} else {
				// Make sure there's at least one empty line at the top of the file.
				$contents = array_merge( array( '' ), $contents );
			}

			return $contents;
		}

	}

endif;