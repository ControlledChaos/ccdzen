<?php
/**
 * Additional features to allow styling of the templates.
 *
 * @package    WordPress
 * @subpackage CCDzen
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SVG icons related functions and filters.
 *
 * @since  1.0.0
 * @access public
 */
final class Oops_Templates {

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

			// Count our number of active panels.
			$instance ->panel_count();

			// Checks to see if we're on the front page or not.
			$instance ->is_frontpage();

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

		// Adds custom classes to the array of body classes.
		add_filter( 'body_class', [ $this, 'body_classes' ] );

	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param array $classes Classes for the body element.
	 * @return array
	 */
	public function body_classes( $classes ) {

		// Add class of group-blog to blogs with more than 1 published author.
		if ( is_multi_author() ) {
			$classes[] = 'group-blog';
		}

		// Add class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		// Add class if we're viewing the Customizer for easier styling of theme options.
		if ( is_customize_preview() ) {
			$classes[] = 'ccdzen-customizer';
		}

		// Add class on front page.
		if ( is_front_page() && 'posts' !== get_option( 'show_on_front' ) ) {
			$classes[] = 'ccdzen-front-page';
		}

		// Add a class if there is a custom header.
		if ( has_header_image() ) {
			$classes[] = 'has-header-image';
		}

		// Add class if sidebar is used.
		if ( is_active_sidebar( 'sidebar-1' ) && ! is_page() ) {
			$classes[] = 'has-sidebar';
		}

		// Add class for one or two column page layouts.
		if ( is_page() || is_archive() ) {
			if ( 'one-column' === get_theme_mod( 'page_layout' ) ) {
				$classes[] = 'page-one-column';
			} else {
				$classes[] = 'page-two-column';
			}
		}

		// Add class if the site title and tagline is hidden.
		if ( 'blank' === get_header_textcolor() ) {
			$classes[] = 'title-tagline-hidden';
		}

		// Get the colorscheme or the default if there isn't one.
		$colors    = Oops_Customizer::sanitize_colorscheme( get_theme_mod( 'colorscheme', 'light' ) );
		$classes[] = 'colors-' . $colors;

		return $classes;

	}

	/**
	 * Count our number of active panels.
	 *
	 * Primarily used to see if we have any panels active, duh.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return int
	 */
	public static function panel_count() {

		$panel_count = 0;

		/**
		 * Filter number of front page sections in CCDzen.
		 *
		 * @since CCDzen 1.0
		 *
		 * @param int $num_sections Number of front page sections.
		 */
		$num_sections = apply_filters( 'ccdzen_front_page_sections', 4 );

		// Create a setting and control for each of the sections available in the theme.
		for ( $i = 1; $i < ( 1 + $num_sections ); $i++ ) {
			if ( get_theme_mod( 'panel_' . $i ) ) {
				$panel_count++;
			}
		}

		return $panel_count;

	}

	/**
	 * Checks to see if we're on the front page or not.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function is_frontpage() {

		return ( is_front_page() && ! is_home() );

	}

}

/**
 * Put an instance of the class into a function.
 *
 * @since  1.0.0
 * @access public
 * @return object Returns an instance of the class.
 */
function ccdzen_templates() {

	return Oops_Templates::instance();

}

// Run an instance of the class.
ccdzen_templates();