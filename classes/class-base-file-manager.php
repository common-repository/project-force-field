<?php
/**
 * Base definition for File Management.
 *
 * @package FaisonZ
 * @subpackage Base
 */

if ( ! class_exists( 'FZ_Base_File_Manager' ) ) :
	/**
	 * Base File Manager class that defines the functions needed in an implemented class.
	 *
	 * @since 1.0
	 * @package FaisonZ
	 * @subpackage Base
	 */
	class FZ_Base_File_Manager {

		/**
		 * Check if the file exists.
		 *
		 * @since 1.0.0
		 *
		 * @param string $filename The file to check.
		 * @return bool True if the file exists.
		 */
		public function file_exists( $filename ) {
			trigger_error( 'file_exists() must be implemented in child class.', E_USER_ERROR);
		}

		/**
		 * Check if the file can be modified in code.
		 *
		 * @since 1.0.0
		 *
		 * @param string $filename The file to check.
		 * @return bool True if the file is writable, or the file doesn't exist and the directory is writable.
		 */
		public function is_file_writeable( $filename ) {
			trigger_error( 'is_file_writeable() must be implemented in child class.', E_USER_ERROR);
		}

		/**
		 * Get all the lines of a file and return it as an array.
		 *
		 * @since 1.0.0
		 *
		 * @param string $filename The file from which to get contents.
		 * @return array All the lines in a file, without line endings.
		 */
		public function get_file_contents( $filename ) {
			trigger_error( 'get_file_contents() must be implemented in child class.', E_USER_ERROR);
		}

		/**
		 * Write an array of contents to a file.
		 *
		 * This creates a new file if not found, and overwrites existing files.
		 *
		 * @since 1.0.0
		 *
		 * @param string $filename The file to put content into.
		 * @param array $contents An array of string data to write to the file.
		 * @return bool True on success.
		 */
		public function put_file_contents( $filename, $contents ) {
			trigger_error( 'put_file_contents() must be implemented in child class.', E_USER_ERROR);
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
		public function prepend_file_with_content( $filename, $data ) {
			trigger_error( 'prepend_file_with_content() must be implemented in child class.', E_USER_ERROR);
		}

		/**
		 * This retrieves all the lines between BEGIN and END markers.
		 *
		 * @since 1.0.0
		 *
		 * @param string $filename The file path you're getting content from.
		 * @param string $marker The marker used to flag the beginning and ending of the target content.
		 * @return array An array of strings from a file (.htaccess ) from between BEGIN and END markers.
		 */
		public function get_contents_from_marker( $filename, $marker ) {
			trigger_error( 'get_contents_array() must be implemented in child class.', E_USER_ERROR);
		}

		/**
		 * This adds the $data to the $filename between BEGIN and END markers.
		 *
		 * Inserts an array of strings into a file (.htaccess ), placing it between
		 * BEGIN and END markers. Replaces existing marked info. Retains surrounding data.
		 *
		 * @since 1.0.0
		 *
		 * @param string $filename The file path you're writing $data to.
		 * @param string $marker The marker used to flag the beginning and ending of the target content.
		 * @param array $data An array of strings  to insert into a file (.htaccess ) between BEGIN and END markers.
		 * @return bool True on write success, false on failure.
		 */
		public function put_contents_array_with_marker( $filename, $marker, $data ) {
			trigger_error( 'put_contents_array() must be implemented in child class.', E_USER_ERROR);
		}

	}

endif;