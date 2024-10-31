<?php
/**
 * Base definition for File Management.
 *
 * @package FaisonZ
 * @subpackage WP
 */

if ( ! class_exists( 'FZ_WordPress_File_Manager' ) ) :

	/**
	 * File Manager class for use in WordPress.
	 *
	 * @since 1.0
	 * @package FaisonZ
	 * @subpackage WP
	 * @uses FZ_Base_File_Manager Extends class
	 */
	class FZ_WordPress_File_Manager extends FZ_Base_File_Manager {

		public function file_exists( $filename, $wp_filesystem = null ) {

			if ( null == $wp_filesystem ) {
				WP_Filesystem();
				global $wp_filesystem;
			}

			return $wp_filesystem->exists( $filename );
		}

		/**
		 * Check if the file can be modified in code.
		 *
		 * @since 1.0.0
		 *
		 * @param string $filename The file to check.
		 * @return bool True if the file is writable, or the file doesn't exist and the directory is writable.
		 */
		public function is_file_writeable( $filename, $wp_filesystem = null ) {

			if ( null == $wp_filesystem ) {
				WP_Filesystem();
				global $wp_filesystem;
			}

			if ( $wp_filesystem->exists( $filename ) ) {
				return $wp_filesystem->is_writable( $filename );
			} else {
				return $wp_filesystem->is_writable( dirname( $filename ) );
			}
		}

		/**
		 * Get all the lines of a file and return it as an array.
		 *
		 * @since 1.0.0
		 * @todo Add Error Checking.
		 *
		 * @param string $filename The file from which to get contents.
		 * @return array All the lines in a file, without line endings.
		 */
		public function get_file_contents( $filename, $wp_filesystem = null ) {

			if ( null == $wp_filesystem ) {
				WP_Filesystem();
				global $wp_filesystem;
			}

			$contents = $wp_filesystem->get_contents_array( $filename );

			// Check if nothing was returned
			if( false === $contents ) {
				$contents = array();
			}

			$contents = array_map( 'rtrim', $contents );

			return $contents;
		}

		/**
		 * Write an array of contents to a file.
		 *
		 * This creates a new file if not found, and overwrites existing files.
		 *
		 * @since 1.0.0
		 * @todo Add error checking.
		 *
		 * @param string $filename The file to put content into.
		 * @param array $contents An array of string data to write to the file.
		 * @return bool True on success.
		 */
		public function put_file_contents( $filename, $contents, $wp_filesystem = null ) {

			if ( null == $wp_filesystem ) {
				WP_Filesystem();
				global $wp_filesystem;
			}

			$results = $wp_filesystem->put_contents( $filename, implode( "\n", $contents ) );

			return $results;
		}

		/**
		 * Insert the each item in $data as lines to the start of the specified $filename.
		 *
		 * @since 1.0.0
		 *
		 * @param string $filename The file path you're prepending content to.
		 * @param array An array of strings to write to the beginning of a file.
		 * @return bool True on success, false on failure.
		 */
		public function prepend_file_with_content( $filename, $data, $wp_filesystem = null ) {

			$file_contents = $this->get_file_contents( $filename, $wp_filesystem );

			$file_contents = array_merge( $data, $file_contents );

			$results = $this->put_file_contents( $filename, $file_contents, $wp_filesystem );

			return $results;
		}

		/**
		 * This uses {@see extract_from_markers()} to get all the lines between BEGIN and END markers.
		 *
		 * @see extract_from_markers() Used to retrieve all lines in $filename.
		 * @since 1.0.0
		 *
		 * @param string $filename The file path you're getting content from.
		 * @param string $marker The marker used to flag the beginning and ending of the target content.
		 * @return array An array of strings from a file (.htaccess ) from between BEGIN and END markers.
		 */
		public function get_contents_from_marker( $filename, $marker ) {

			$contents = extract_from_markers( $filename, $marker );

			return $contents;
		}

		/**
		 * This uses {@see insert_with_markers()} to add the $data to the $filename.
		 *
		 * Inserts an array of strings into a file (.htaccess ), placing it between
		 * BEGIN and END markers. Replaces existing marked info. Retains surrounding data.
		 *
		 * @see insert_with_markers() Used to write all lines to $filename.
		 * @since 1.0.0
		 *
		 * @param string $filename The file path you're writing $data to.
		 * @param string $marker The marker used to flag the beginning and ending of the target content.
		 * @param array $data An array of strings  to insert into a file (.htaccess ) between BEGIN and END markers.
		 * @return bool True on write success, false on failure.
		 */
		public function put_contents_array_with_marker( $filename, $marker, $data, $wp_filesystem = null ) {
			return insert_with_markers( $filename, $marker, $data );
		}

	}

endif;