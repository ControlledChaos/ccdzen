<?php
/**
 * CCDzen Customizer.
 *
 * @package    WordPress
 * @subpackage CCDzen
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CCDzen Customizer.
 *
 * @since  1.0.0
 * @access public
 */
class Oops_Customizer {

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return self
	 */
	public function __construct() {

		// Register Customizer options.
		add_action( 'customize_register', [ $this, 'customize_register' ] );

		// Bind JS handlers to instantly live-preview changes.
		add_action( 'customize_preview_init', [ $this, 'customize_preview_js' ] );

		// Load dynamic logic for the customizer controls area.
		add_action( 'customize_controls_enqueue_scripts', [ $this, 'panels_js' ] );

	}

	/**
	 * Register Customizer options.
	 *
	 * Add postMessage support for site title and description for the Theme Customizer.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 * @param  WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function customize_register( $wp_customize ) {

		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

		$wp_customize->selective_refresh->add_partial(
			'blogname', [
				'selector'        => '.site-title a',
				'render_callback' => [ $this, 'customize_partial_blogname' ],
			]
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription', [
				'selector'        => '.site-description',
				'render_callback' => [ $this, 'customize_partial_blogdescription' ],
			]
		);

		/**
		 * Custom colors.
		 */
		$wp_customize->add_setting(
			'colorscheme', [
				'default'           => 'light',
				'transport'         => 'postMessage',
				'sanitize_callback' => [ $this, 'sanitize_colorscheme' ],
			]
		);

		$wp_customize->add_setting(
			'colorscheme_hue', [
				'default'           => 250,
				'transport'         => 'postMessage',
				'sanitize_callback' => 'absint', // The hue is stored as a positive integer.
			]
		);

		$wp_customize->add_control(
			'colorscheme', [
				'type'     => 'radio',
				'label'    => __( 'Color Scheme', 'ccdzen' ),
				'choices'  => [
					'light'  => __( 'Light', 'ccdzen' ),
					'dark'   => __( 'Dark', 'ccdzen' ),
					'custom' => __( 'Custom', 'ccdzen' ),
				],
				'section'  => 'colors',
				'priority' => 5,
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize, 'colorscheme_hue', [
					'mode'     => 'hue',
					'section'  => 'colors',
					'priority' => 6,
				]
			)
		);

		/**
		 * Theme options.
		 */
		$wp_customize->add_section(
			'theme_options', [
				'title'    => __( 'Theme Options', 'ccdzen' ),
				'priority' => 130, // Before Additional CSS.
			]
		);

		$wp_customize->add_setting(
			'page_layout', [
				'default'           => 'two-column',
				'sanitize_callback' => [ $this, 'sanitize_page_layout' ],
				'transport'         => 'postMessage',
			]
		);

		$wp_customize->add_control(
			'page_layout', [
				'label'           => __( 'Page Layout', 'ccdzen' ),
				'section'         => 'theme_options',
				'type'            => 'radio',
				'description'     => __( 'When the two-column layout is assigned, the page title is in one column and content is in the other.', 'ccdzen' ),
				'choices'         => [
					'one-column' => __( 'One Column', 'ccdzen' ),
					'two-column' => __( 'Two Column', 'ccdzen' ),
				],
				'active_callback' => [ $this, 'is_view_with_layout_option' ],
			]
		);

		/**
		 * Front page featured images.
		 */
		$wp_customize->add_setting(
			'front_page_featured_image', [
				'default'           => 'no',
				'transport'         => 'refresh',
				'sanitize_callback' => [ $this, 'sanitize_front_page_featured_image' ],
			]
		);

		$wp_customize->add_control(
			'front_page_featured_image', [
				'section'     => 'theme_options',
				'type'        => 'radio',
				'label'       => __( 'Front Page Featured Image', 'ccdzen' ),
				'description' => __( 'Display the featured image from page used as a static front page?', 'ccdzen' ),
				'choices'     => [
					'no'  => __( 'No', 'ccdzen' ),
					'yes' => __( 'Yes', 'ccdzen' ),
				],
			]
		);

		$wp_customize->add_setting(
			'front_panel_featured_images', [
				'default'           => 'no',
				'transport'         => 'refresh',
				'sanitize_callback' => [ $this, 'sanitize_front_panel_featured_images' ],
			]
		);

		$wp_customize->add_control(
			'front_panel_featured_images', [
				'section'     => 'theme_options',
				'type'        => 'radio',
				'label'       => __( 'Front Page Featured Images', 'ccdzen' ),
				'description' => __( 'Display featured images from pages used in front page panels?', 'ccdzen' ),
				'choices'     => [
					'no'  => __( 'No', 'ccdzen' ),
					'yes' => __( 'Yes', 'ccdzen' ),
				]
			]
		);

		$wp_customize->add_setting(
			'front_panel_number', [
				'default'           => '4',
				'transport'         => 'refresh',
				'sanitize_callback' => [ $this, 'sanitize_front_panel_number' ]
			]
		);

		$wp_customize->add_control(
			'front_panel_number', [
				'section'     => 'theme_options',
				'type'        => 'select',
				'label'       => __( 'Number of Panels', 'ccdzen' ),
				'description' => __( 'Select the maximum number of panels allowed on the front page.', 'ccdzen' ),
				'choices'     => [
					'1'  => __( 'One', 'ccdzen' ),
					'2'  => __( 'Two', 'ccdzen' ),
					'3'  => __( 'Three', 'ccdzen' ),
					'4'  => __( 'Four', 'ccdzen' ),
					'5'  => __( 'Five', 'ccdzen' ),
					'6'  => __( 'Six', 'ccdzen' ),
					'7'  => __( 'Seven', 'ccdzen' ),
					'8'  => __( 'Eight', 'ccdzen' ),
					'9'  => __( 'Nine', 'ccdzen' ),
					'10' => __( 'Ten', 'ccdzen' ),
					'11' => __( 'Eleven', 'ccdzen' ),
					'12' => __( 'Twelve', 'ccdzen' ),
				]
			]
		);

		/**
		 * Filter number of front page sections in CCDzen.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param int $num_sections Number of front page sections.
		 * @return void
		 */
		$input        = '';
		$num_sections = $this->sanitize_front_panel_number( get_theme_mod( 'front_panel_number' ) );
		$sections     = apply_filters( 'ccdzen_front_page_sections', $num_sections );

		// Create a setting and control for each of the sections available in the theme.
		for ( $i = 1; $i < ( 1 + $sections ); $i++ ) {
			$wp_customize->add_setting(
				'panel_' . $i, [
					'default'           => false,
					'sanitize_callback' => 'absint',
					'transport'         => 'postMessage',
				]
			);

			$wp_customize->add_control(
				'panel_' . $i, [
					/* translators: %d is the front page section number */
					'label'           => sprintf( __( 'Front Page Section %d Content', 'ccdzen' ), $i ),
					'description'     => ( 1 !== $i ? '' : __( 'Select pages to feature in each area from the dropdowns. Add an image to a section by setting a featured image in the page editor. Empty sections will not be displayed.', 'ccdzen' ) ),
					'section'         => 'theme_options',
					'type'            => 'dropdown-pages',
					'allow_addition'  => true,
					'active_callback' => [ $this, 'is_static_front_page' ],
				]
			);

			$wp_customize->selective_refresh->add_partial(
				'panel_' . $i, [
					'selector'            => '#panel' . $i,
					'render_callback'     => [ $this, 'front_page_section' ],
					'container_inclusive' => true,
				]
			);
		}

	}

	/**
	 * Sanitize the page layout options.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $input Page layout.
	 * @return string
	 */
	public static function sanitize_page_layout( $input ) {

		$valid = [
			'one-column' => __( 'One Column', 'ccdzen' ),
			'two-column' => __( 'Two Column', 'ccdzen' ),
		];

		if ( array_key_exists( $input, $valid ) ) {
			return $input;
		}

		return '';

	}

	/**
	 * Sanitize the colorscheme.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param string $input Color scheme.
	 * @return string
	 */
	public static function sanitize_colorscheme( $input ) {

		$valid = [
			'light',
			'dark',
			'custom'
		];

		if ( in_array( $input, $valid, true ) ) {
			return $input;
		}

		return 'light';

	}

	/**
	 * Render the site title for the selective refresh partial.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 *
	 * @see    customize_register()
	 */
	public function customize_partial_blogname() {

		bloginfo( 'name' );

	}

	/**
	 * Render the site tagline for the selective refresh partial.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 *
	 * @see    customize_register()
	 */
	public function customize_partial_blogdescription() {

		bloginfo( 'description' );

	}

	/**
	 * Return whether we're previewing the front page and it's a static page.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function is_static_front_page() {

		return ( is_front_page() && ! is_home() );

	}

	/**
	 * Return whether we're on a view that supports a one or two column layout.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function is_view_with_layout_option() {

		// This option is available on all pages. It's also available on archives when there isn't a sidebar.
		return ( is_page() || ( is_archive() && ! is_active_sidebar( 'sidebar-1' ) ) );

	}

	/**
	 * Selects the maximum number of panels allowed on the front page.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public static function sanitize_front_panel_number( $input ) {

		$valid = [
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
			'7',
			'8',
			'9',
			'10',
			'11',
			'12'
		];

		if ( in_array( $input, $valid ) ) {
			return $input;
		}

		return '4';

	}

	/**
	 * Return whether to use featured images for front page panels.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public static function sanitize_front_page_featured_image( $input ) {

		$valid = [
			'no',
			'yes'
		];

		if ( in_array( $input, $valid, true ) ) {
			return $input;
		}

		return 'no';

	}

	/**
	 * Return whether to use featured images for front page panels.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public static function sanitize_front_panel_featured_images( $input ) {

		$valid = [
			'no',
			'yes'
		];

		if ( in_array( $input, $valid, true ) ) {
			return $input;
		}

		return 'no';

	}

	/**
	 * Bind JS handlers to instantly live-preview changes.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function customize_preview_js() {

		wp_enqueue_script( 'ccdzen-customize-preview', get_theme_file_uri( '/assets/js/customize-preview.js' ), [ 'customize-preview' ], '1.0', true );

	}

	/**
	 * Load dynamic logic for the customizer controls area.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function panels_js() {

		wp_enqueue_script( 'ccdzen-customize-controls', get_theme_file_uri( '/assets/js/customize-controls.js' ), [], '1.0', true );

	}

}

// Run an instance of the class.
$ccdzen_customizer = new Oops_Customizer();