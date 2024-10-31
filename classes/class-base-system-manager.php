<?php
/**
 * Base definition for System Management.
 *
 * @package FaisonZ
 * @subpackage Base
 */

if ( ! class_exists( 'FZ_Base_System_Manager' ) ) :
	/**
	 * Base System Manager class that defines the functions needed in an implemented class.
	 *
	 * @since 1.0
	 * @package FaisonZ
	 * @subpackage Base
	 */
	class FZ_Base_System_Manager {

		/**
		 * Get the htaccess file's path.
		 *
		 * @since 1.0.0
		 *
		 * @return string The file path for the .htaccess file.
		 */
		public function get_htaccess_path() {
			trigger_error( 'get_htaccess_path() must be implemented in child class.', E_USER_ERROR);
		}

		/**
		 * Checks if mod_rewrite is enabled.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if mod_rewrite is enabled.
		 */
		public function is_mod_rewrite_enabled() {
			trigger_error( 'is_mod_rewrite_enabled() must be implemented in child class.', E_USER_ERROR);
		}

		/**
		 * Get File Manager for this system.
		 *
		 * @since 1.0.0
		 *
		 * @return object The appropriate file manager for this system.
		 */
		public function get_file_manager() {
			trigger_error( 'get_file_manager() must be implemented in child class.', E_USER_ERROR);
		}

	}

endif;