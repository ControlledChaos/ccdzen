<?php
/**
 * CCDzen back compat functionality.
 *
 * Prevents CCDzen from running on WordPress versions prior to 4.7,
 * since this theme is not meant to be backward compatible beyond that and
 * relies on many newer functions and markup changes introduced in 4.7.
 *
 * @package    WordPress
 * @subpackage CCDzen
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CCDzen back compat functionality.
 *
 * @since  1.0.0
 * @access public
 */
final class Oops_Back_Compat {

	/**
	 * Get an instance of the class.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object Returns the instance.
	 */
	public static function instance() {

		// Varialbe for the instance to be used outside the class.
		static $instance = null;

		if ( is_null( $instance ) ) {

			// Set variable for new instance.
			$instance = new self;

		}

		// Return the instance.
		return $instance;

	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return self
	 */
	private function __construct() {

		// Prevent switching to CCDzen on old versions of WordPress.
		add_action( 'after_switch_theme', [ $this, 'switch_theme' ] );

		// Prevents the Customizer from being loaded on WordPress versions prior to 4.7.
		add_action( 'load-customize.php', [ $this, 'customize' ] );

		// Prevents the Theme Preview from being loaded on WordPress versions prior to 4.7.
		add_action( 'template_redirect', [ $this, 'preview' ] );

	}

	/**
	 * Prevent switching to CCDzen on old versions of WordPress.
	 *
	 * Switches to the default theme.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	function switch_theme() {

		switch_theme( WP_DEFAULT_THEME );
		unset( $_GET['activated'] );
		add_action( 'admin_notices', [ $this, 'upgrade_notice' ] );

	}

	/**
	 * Adds a message for unsuccessful theme switch.
	 *
	 * Prints an update nag after an unsuccessful attempt to switch to
	 * CCDzen on WordPress versions prior to 4.7.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 * @global string $wp_version WordPress version.
	 */
	function upgrade_notice() {

		$message = sprintf( __( 'CCDzen requires at least WordPress version 4.7. You are running version %s. Please upgrade and try again.', 'ccdzen' ), $GLOBALS['wp_version'] );
		printf( '<div class="error"><p>%s</p></div>', $message );

	}

	/**
	 * Prevents the Customizer from being loaded on WordPress versions prior to 4.7.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 * @global string $wp_version WordPress version.
	 */
	function customize() {

		wp_die(
			sprintf( __( 'CCDzen requires at least WordPress version 4.7. You are running version %s. Please upgrade and try again.', 'ccdzen' ), $GLOBALS['wp_version'] ), '', array(
				'back_link' => true,
			)
		);

	}

	/**
	 * Prevents the Theme Preview from being loaded on WordPress versions prior to 4.7.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 * @global string $wp_version WordPress version.
	 */
	function preview() {

		if ( isset( $_GET['preview'] ) ) {
			wp_die( sprintf( __( 'CCDzen requires at least WordPress version 4.7. You are running version %s. Please upgrade and try again.', 'ccdzen' ), $GLOBALS['wp_version'] ) );
		}

	}

}

/**
 * Put an instance of the class into a function.
 *
 * @since  1.0.0
 * @access public
 * @return object Returns an instance of the class.
 */
function ccdzen_back_compat() {

	return Oops_Back_Compat::instance();

}

// Run an instance of the class.
ccdzen_back_compat();