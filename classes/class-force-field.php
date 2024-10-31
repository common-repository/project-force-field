<?php
/**
 * The WordPress Plugin class.
 *
 * @package OG_Force_Field
 * @subpackage WP
 */

if ( ! class_exists( 'OG_Force_Field' ) ) :

	/**
	 * The Force Field plugin class for WordPress.
	 *
	 * @since 1.0
	 * @package OG_Force_Field
	 * @subpackage WP
	 */
	class OG_Force_Field {

		const VERSION = '0.6.1';

		const VERSION_OPTION = 'ogff_version';

		const DEFAULT_NEW_LOGIN = 'safe-entrance.php';

		const CHECK_ATTACK_TASK = 'ogff_check_hits';

		const HITS_TAKEN_OPTION = 'ogff_hits_taken';

		const CHECK_ATTACK_START_OPTION = 'ogff_check_hits_start_time';

		const REVERSE_POLARITY_OPTION = 'ogff_reverse_polarity';

		const STABALIZE_POLARITY_TASK = 'ogff_stabalize_polarity';

		const OPTIONAL_BLOCKS = 'ogff_optional_blocks';

		const CHECK_ATTACK_TIME = 60;

		const POLARITY_REVERSE_TIME = 18000;

		const ATTACK_THRESHOLD = 30;

		private $system_manager;

		private $rewrite_manager;

		private $new_login;

		function __construct( FZ_Base_System_Manager $system_manager, $new_login ) {
			$this->system_manager = $system_manager;

			// Add actions to display important messages
			add_action( 'admin_footer', array( $this, 'display_warnings' ) );

			// Only add filters and hooks if Project Force Field is compatible with the site
			if ( $this->is_force_field_compatible() ) {

				$this->new_login = $new_login;

				// Add action to check for updates
				add_action( 'init', array( $this, 'update_force_field' ) );

				// Add action that makes sure the new login path is correct
				add_action( 'init', array( $this, 'check_polarity' ), 20 );

				// Add filters to fix the login url
				add_filter( 'site_url', array( $this, 'fix_the_login_path' ), 10, 4 );
				add_filter( 'network_site_url', array( $this, 'fix_network_login_path' ), 10, 3 );
				add_filter( 'wp_redirect', array( $this, 'fix_redirect_login_path' ) );

				// Add filters/actions for monitoring Brute Force Attacks
				add_filter( 'wp_login_errors', array( $this, 'sustain_hit' ) );
				add_action( self::CHECK_ATTACK_TASK, array( $this, 'check_hits' ) );
				add_action( self::STABALIZE_POLARITY_TASK, array( $this, 'stabalize_polarity' ) );

			}
		}

		/**
		 * Run upgrade scripts if needed.
		 *
		 * @since 0.6.0
		 */
		function update_force_field() {
			$last_version = get_option( self::VERSION_OPTION );

			if ( self::VERSION == $last_version ) {
				return;
			}

			if ( false === $last_version ) {
				// Update for versions pre-0.6.0
				$this->setup_rewrite_manager();
				$this->rewrite_manager->reset_shields();
			}

			update_option( self::VERSION_OPTION, self::VERSION );
		}

		/**
		 * Display general Force Field warnings in the WordPress Dashboard.
		 *
		 * These warnings should only be displayed to users with higher roles.
		 *
		 * @since 1.0.0
		 */
		public function display_warnings() {
			if ( ! $this->system_manager->is_mod_rewrite_enabled() ) {
				global $is_apache;
				if ( ! $is_apache ) {
					printf(
						'<div class="error"><p><strong>%s</strong> %s</p></div>',
						__( 'Project Force Field only works on Apache Servers.', 'project-force-field' ),
						__( 'You should uninstall Project Force Field. Sorry :-(', 'project-force-field' )
					);
				} else {
					printf(
						'<div class="error"><p><strong>%s</strong> %s</p></div>',
						__( 'Project Force Field requires the Apache Module mod_rewrite.', 'project-force-field' ),
						__( 'You need to install and enable mod_rewrite before Project Force Field will start working.', 'project-force-field' )
					);
				}
			}

			$file_manager = $this->system_manager->get_file_manager();
			if ( $file_manager->file_exists( ABSPATH . $this->new_login ) ) {
				printf(
					'<div class="error"><p><strong>%s</strong><br />%s</p></div>',
					__( "Invalid Value Defined for OGFF_LOGIN!", 'project-force-field' ),
					__( "The new login you specified actually exists as a file or directory, which has strange and horrible consequences! Currently using the plugin default.", 'project-force-field' )
				);
			}

			if ( count( explode( '/', $this->new_login ) ) > 1 ) {
				printf(
					'<div class="error"><p><strong>%s</strong><br />%s</p></div>',
					__( "Invalid Value Defined for OGFF_LOGIN!", 'project-force-field' ),
					__( "The new login you specified can't include any forward slashes ( / ). Currently using the plugin default.", 'project-force-field' )
				);
			}

			$htaccess = $this->system_manager->get_htaccess_path();
			if ( ! $file_manager->is_file_writeable( $htaccess ) ) {
				$ogff_section_content = $this->rewrite_manager->generate_force_field_section_content();
				if ( $ogff_section_content[0] == '' ) {
					unset( $ogff_section_content[0] );
				}
				$ogff_section_content = array_map( 'htmlentities', $ogff_section_content );
				$ogff_section_content = implode( '<br />', $ogff_section_content );

				printf(
					'<div class="error"><p><strong>%s</strong><br />%s</p><p><code>%s</code></p></div>',
					__( "Project Force Field can't modify the .htaccess file!", 'project-force-field' ),
					__( "You can add the following content to the top of your .htaccess file, but you won't receive automated Brute Force protection without write access to the .htaccess file.", 'project-force-field' ),
					$ogff_section_content
				);
			}

			if ( is_multisite() ) {
				printf(
					'<div class="error"><p><strong>%s</strong><br />%s</p></div>',
					__( "Project Force Field doesn't work on WordPress Multisite!", 'project-force-field' ),
					__( "I'm sorry for the inconvenience, but check back in the future for multisite support.", 'project-force-field' ),
					$ogff_section_content
				);
			}
		}

		/**
		 * Checks if Project Force Field is compatible with the current website.
		 *
		 * @since 0.5.1
		 * @todo Remove multisite failure when multisite is supported.
		 *
		 * @return bool true if Project Force Field is compatible with the current site.
		 */
		public function is_force_field_compatible() {
			if ( ! $this->system_manager->is_mod_rewrite_enabled() ) {
				global $is_apache;
				if ( ! $is_apache ) {
					return false;
				}
			}

			if ( is_multisite() ) {
				return false;
			}

			return true;
		}

		public function fix_the_login_path( $url, $path, $scheme, $blog_id ) {
			if ( ! preg_match( "/wp\-login\.php/", $path ) ) {
				return $url;
			}

			$path = $this->fix_path( $path );

			return get_site_url( $blog_id, $path, $scheme );
		}

		public function fix_network_login_path($url, $path, $scheme ) {
			if ( ! preg_match( "/wp\-login\.php/", $path ) ) {
				return $url;
			}

			$path = $this->fix_path( $path );

			return network_site_url( $path, $scheme );
		}

		public function fix_redirect_login_path( $location ) {
			if ( ! preg_match( "/wp\-login\.php/", $location ) ) {
				return $location;
			}

			$location = $this->fix_path( $location );

			return $location;
		}

		public function setup_rewrite_manager() {
			if ( null != $this->rewrite_manager ) {
				return;
			}

			global $wp_rewrite;

			$filename     = $this->system_manager->get_htaccess_path();
			$file_manager = $this->system_manager->get_file_manager();
			$login        = $this->get_new_login();
			$permalinks   = ( $wp_rewrite->using_permalinks() );

			$this->rewrite_manager = new OG_Force_Field_Rewrite_Manager( $file_manager, $filename, $login, $permalinks );
		}

		private function is_new_login_valid( $login ) {
			$file_manager = $this->system_manager->get_file_manager();
			if ( $file_manager->file_exists( ABSPATH . $login ) ) {
				return false;
			}

			if ( count( explode( '/', $login) ) > 1 ) {
				return false;
			}

			return true;
		}

		private function fix_path( $path ) {
			return preg_replace( "/wp\-login\.php/", $this->get_new_login(), $path );
		}

		public function check_polarity() {
			$this->setup_rewrite_manager();
			$optional_blocks = $this->get_optional_blocks();

			$this->rewrite_manager->check_polarity( $this->get_new_login(), $optional_blocks );
		}

		private function get_new_login() {
			$reverse_polarity = get_option( self::REVERSE_POLARITY_OPTION );

			if ( false == $reverse_polarity ) {
				if ( ! $this->is_new_login_valid( $this->new_login ) ) {
					return self::DEFAULT_NEW_LOGIN;
				}
				return $this->new_login;
			} else {
				return $reverse_polarity;
			}
		}

		private function get_optional_blocks() {
			if ( get_option( self::REVERSE_POLARITY_OPTION ) ) {
				$blocks = array( $this->new_login );

				$optional_blocks = get_option( self::OPTIONAL_BLOCKS );
				if ( false != $optional_blocks ) {
					$blocks = array_merge( $blocks, unserialize( $optional_blocks ) );
				}

				return $blocks;
			}

			return null;
		}

		private function add_optional_block() {
			$optional_blocks = get_option( self::OPTIONAL_BLOCKS );

			$new_block = $this->get_new_login();

			if ( false == $optional_blocks ) {
				$optional_blocks = array( $new_block );
			} else {
				$optional_blocks = unserialize( $optional_blocks );
				array_push( $optional_blocks, $new_block );
			}

			update_option( self::OPTIONAL_BLOCKS, serialize( $optional_blocks ) );
		}

		public function reverse_polarity() {	
			$polarity = rand( 0, 99999 );

			$reverse_login = sprintf( '%05d', $polarity );

			if ( false != get_option( self::REVERSE_POLARITY_OPTION ) ) {
				$this->add_optional_block();
			}

			update_option( self::REVERSE_POLARITY_OPTION, $polarity );

			if ( ! wp_next_scheduled( self::STABALIZE_POLARITY_TASK ) ) {
				$start_time = time();
				wp_schedule_single_event( $start_time + self::POLARITY_REVERSE_TIME, self::STABALIZE_POLARITY_TASK );
			}
		}

		public function stabalize_polarity() {
			update_option( self::REVERSE_POLARITY_OPTION, 0 );

			update_option(  self::OPTIONAL_BLOCKS, 0 );

			$this->check_polarity();
		}

		public function sustain_hit( $login_errors ) {
			$important_errors = array( 'invalid_username', 'incorrect_password' );

			if ( ! in_array( $login_errors->get_error_code(), $important_errors ) ) {
				return $login_errors;
			}

			$this->monitor_hit();

			return $login_errors;
		}

		public function check_hits() {
			$threshold = self::ATTACK_THRESHOLD;
			$minutes   = self::CHECK_ATTACK_TIME / 60;
			$hits      = $this->get_current_hits();

			if ( $threshold < ( $hits / $minutes ) ) {
				$this->reverse_polarity();
			}

			delete_option( self::CHECK_ATTACK_START_OPTION );
			$this->zero_out_hits();
		}

		private function monitor_hit() {
			$start_time = wp_next_scheduled( self::CHECK_ATTACK_TASK );
			if ( ! $start_time ) {
				$start_time = time();
				wp_schedule_single_event( $start_time + self::CHECK_ATTACK_TIME, self::CHECK_ATTACK_TASK );
				add_option( self::CHECK_ATTACK_START_OPTION, $start_time, '', 'no' );
			}

			if ( time() < $start_time + self::CHECK_ATTACK_TIME ) {
				$current_hits = $this->get_current_hits();
				update_option( self::HITS_TAKEN_OPTION, $current_hits + 1 );
			}
		}

		private function get_current_hits() {
			return get_option( self::HITS_TAKEN_OPTION, 0 );
		}

		public function activate() {
			$this->zero_out_hits();

			add_option( self::REVERSE_POLARITY_OPTION, 0, '', 'no' );

			add_option( self::OPTIONAL_BLOCKS, 0, '', 'no' );
		}

		public function deactivate() {
			$this->setup_rewrite_manager();

			$this->rewrite_manager->shields_down();

			$this->remove_hit_counter();

			delete_option( self::CHECK_ATTACK_START_OPTION );
			delete_option( self::REVERSE_POLARITY_OPTION );
			delete_option( self::OPTIONAL_BLOCKS );
			delete_option( self::VERSION_OPTION );

			$timestamp = wp_next_scheduled( self::CHECK_ATTACK_TASK );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, self::CHECK_ATTACK_TASK );
			}

			$timestamp = wp_next_scheduled( self::STABALIZE_POLARITY_TASK );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, self::STABALIZE_POLARITY_TASK );
			}
		}

		private function zero_out_hits() {
			if ( false === get_option( self::HITS_TAKEN_OPTION ) ) {
				add_option( self::HITS_TAKEN_OPTION, 0, '', 'no' );
			} else {
				update_option( self::HITS_TAKEN_OPTION, 0 );
			}
		}

		private function remove_hit_counter() {
			delete_option( self::HITS_TAKEN_OPTION );
		}
	}

endif;