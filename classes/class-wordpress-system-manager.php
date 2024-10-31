<?php
/**
 * The WordPress Systems Manager Class.
 *
 * @package FaisonZ
 * @subpackage WP
 */

if ( ! class_exists( 'FZ_WordPress_System_Manager' ) ) :

	/**
	 * The System Manager for WordPress.
	 *
	 * @since 1.0
	 * @package FaisonZ
	 * @subpackage WP
	 */
	class FZ_WordPress_System_Manager extends FZ_Base_System_Manager {

		public function get_htaccess_path() {
			$home_path = get_home_path();
			$home_path = apply_filters( 'fz_home_path', $home_path );

			$htaccess_file = $home_path . '.htaccess';

			return $htaccess_file;
		}

		public function is_mod_rewrite_enabled() {
			return got_mod_rewrite();
		}

		public function get_file_manager() {
			return new FZ_WordPress_File_Manager();
		}
	}

endif;