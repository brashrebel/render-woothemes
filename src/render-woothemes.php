<?php
//{{HEADER}}

// Exit if loaded directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Define all plugin constants.

/**
 * The version of Render.
 *
 * @since 0.1.0
 */
define( 'RENDER_WOOTHEMES_VERSION', '0.1.0' );

/**
 * The absolute server path to Render's root directory.
 *
 * @since 0.1.0
 */
define( 'RENDER_WOOTHEMES_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The URI to Render's root directory.
 *
 * @since 0.1.0
 */
define( 'RENDER_WOOTHEMES_URL', get_template_directory_uri() );

/**
 * Class Render_Woothemes
 *
 * Initializes and loads the plugin.
 *
 * @since   0.1.0
 *
 * @package Render_Woothemes
 */
class Render_Woothemes {

	/**
	 * The reason for deactivation.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public $deactivate_reasons = array();

	/**
	 * The plugin text domain.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $text_domain = 'Render_Woothemes';

	/**
	 * Constructs the plugin.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, '_init' ) );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since  0.1.0
	 * @access private
	 */
	public function _init() {

		// Requires Render
		if ( ! defined( 'RENDER_ACTIVE' ) ) {
			$this->deactivate_reasons[] = __( 'Render is not active', self::$text_domain );
		}

		// Requires Woothemes
		if ( ! class_exists( 'WF' ) ) {
			$this->deactivate_reasons[] = __( 'A Woothemes theme is not active', self::$text_domain );
		}

		// 0.1.3 is when extension integration was introduced
		if ( defined( 'RENDER_VERSION' ) && version_compare( RENDER_VERSION, '0.1.0', '<' ) ) {
			$this->deactivate_reasons[] = sprintf(
				__( 'This plugin requires at least Render version %s. You have version %s installed.', self::$text_domain ),
				'0.1.3',
				RENDER_VERSION
			);
		}

		// Bail if issues
		if ( ! empty( $this->deactivate_reasons ) ) {
			add_action( 'admin_notices', array( $this, '_notice' ) );

			return;
		}

		// Add the shortcodes to Render
		$this->_add_shortcodes();

		// Translation ready
		load_plugin_textdomain( 'Render_Woothemes', false, RENDER_WOOTHEMES_PATH . '/languages' );

		// Add Woothemes styles to tinymce
		// TODO add theme's style sheets to editor
		add_filter( 'render_editor_styles', array( __CLASS__, '_add_woo_style' ) );
		//add_filter( 'render_editor_styles', array( __CLASS__, '_add_render_edd_style' ) );

		// Licensing
		render_setup_license( 'render_woothemes', 'Woothemes', RENDER_WOOTHEMES_VERSION, __FILE__ );

		// Remove media button
		render_disable_tinymce_button( 'woothemes_shortcodes_button', 'Woothemes Shortcodes', 11 );
	}

	/**
	 * Adds the Woothemes stylesheet to the TinyMCE.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 *
	 * @return array The styles.
	 */
	public static function _add_woo_style( $styles ) {

		global $wp_styles;

		$styles[] = RENDER_WOOTHEMES_URL . "/style.css";
		$styles[] = RENDER_WOOTHEMES_URL . "/functions/css/shortcodes.css";
		$styles[] = RENDER_WOOTHEMES_URL . "/functions/css/shortcode-icon.css";

		return $styles;
	}

	/**
	 * Adds the Render Woothemes stylesheet to the TinyMCE through Render.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 *
	 * @return array The styles.
	 */
	public static function _add_render_edd_style( $styles ) {
		// TODO add the theme's stylesheets
		//$styles[] = RENDER_WOOTHEMES_URL . "/assets/css/render-edd.min.css";

		return $styles;
	}

	/**
	 * Add data and inputs for all Woothemes shortcodes and pass them through Render's function.
	 *
	 * @since 0.1.0
	 */
	private function _add_shortcodes() {

		foreach (
			array(
				// box
				array(
					'code'        => 'box',
					'function'    => 'woo_shortcode_box',
					'title'       => __( 'Box', self::$text_domain ),
					'description' => __( 'Displays your content in a nice box.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(
						'type'   => array(
							'label'      => __( 'Type', 'Render_Woothemes' ),
							'type'       => 'selectbox',
							'properties' => array(
								'options' => array(
									'info'     => __( 'Info', 'Render_Woothemes' ),
									'alert'    => __( 'Alert', 'Render_Woothemes' ),
									'tick'     => __( 'Tick', 'Render_Woothemes' ),
									'download' => __( 'Download', 'Render_Woothemes' ),
									'note'     => __( 'Note', 'Render_Woothemes' ),
								),
							),
						),
						'size'   => array(
							'label'      => __( 'Size', 'Render_Woothemes' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'medium' => __( 'Medium', 'Render_Woothemes' ),
									'large'  => __( 'Large', 'Render_Woothemes' ),
								),
							),
						),
						'style'  => array(
							'label'      => __( 'Corners', 'Render_Woothemes' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									''        => __( 'Square', 'Render_Woothemes' ),
									'rounded' => __( 'Rounded', 'Render_Woothemes' ),
								),
							),
						),
						'border' => array(
							'label'      => __( 'Border', 'Render_Woothemes' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'none' => __( 'None', 'Render_Woothemes' ),
									'full' => __( 'Full', 'Render_Woothemes' ),
								),
							),
						),
						'icon'   => array(
							'label' => __( 'Icon (URL)', 'Render_Woothemes' ),
						),
					),
					'wrapping'    => true,
					'render'      => array(
						'noStyle' => true,
					),
				),
				// button
				array(
					'code'        => 'button',
					'function'    => 'woo_shortcode_button',
					'title'       => __( 'Button', self::$text_domain ),
					'description' => __( 'Displays a button.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(
						'size'   => array(
							'label'      => __( 'Size', 'Render_Woothemes' ),
							'type'       => 'selectbox',
							'properties' => array(
								'options' => array(
									'small'  => __( 'Small', 'Render_Woothemes' ),
									'medium' => __( 'Medium', 'Render_Woothemes' ),
									'large'  => __( 'Large', 'Render_Woothemes' ),
									'xl'     => __( 'Extra Large', 'Render_Woothemes' ),
								),
							),
						),
						'style'  => array(
							'label'      => __( 'Style', 'Render_Woothemes' ),
							'type'       => 'selectbox',
							'properties' => array(
								'options' => array(
									'info'     => __( 'Info', 'Render_Woothemes' ),
									'alert'    => __( 'Alert', 'Render_Woothemes' ),
									'tick'     => __( 'Tick', 'Render_Woothemes' ),
									'download' => __( 'Download', 'Render_Woothemes' ),
									'note'     => __( 'Note', 'Render_Woothemes' ),
								),
							),
						),
						'color'  => array(
							'label'   => __( 'Color', 'Render' ),
							'type'    => 'colorpicker',
							'default' => RENDER_PRIMARY_COLOR,
						),
						'border' => array(
							'label'   => __( 'Border', 'Render' ),
							'type'    => 'colorpicker',
							'default' => RENDER_PRIMARY_COLOR_DARK,
						),
						'text'   => array(
							'label'      => __( 'Dark or light text?', 'Render_Woothemes' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									''     => __( 'Light', 'Render_Woothemes' ),
									'dark' => __( 'Dark', 'Render_Woothemes' ),
								),
							),
						),
						'class'  => array(
							'label' => __( 'Custom CSS class', 'Render_Woothemes' ),
						),
						'link'   => render_sc_attr_template( 'link' ),
						'window' => array(
							'label'      => __( 'Target', 'Render' ),
							'type'       => 'checkbox',
							'properties' => array(
								'label' => __( 'Open link in new window?', 'Render' ),
							),
						),
					),
					'wrapping'    => true,
					'render'      => array(
						'noStyle' => true,
					),
				),
				// related_posts
				array(
					'code'        => 'related_posts',
					'function'    => 'woo_shortcode_related_posts',
					'title'       => __( 'Related_posts', self::$text_domain ),
					'description' => __( 'Displays a related_posts.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(
						'image' => array(
							'label'       => __( 'Image', 'Render_Woothemes' ),
							'type'        => 'slider',
							'description' => __( 'If set to 0, no image will show.', 'Render_Woothemes' ),
							'properties'  => array(
								'value' => 0,
								'max'   => 1000,
							),
						),
						'limit' => array(
							'label'       => __( 'Limit', 'Render_Woothemes' ),
							'type'        => 'slider',
							'description' => __( 'The number of posts to show.', 'Render_Woothemes' ),
							'properties'  => array(
								'value' => 5,
								'max'   => 12,
							),
						),
					),
					'render'      => true,
				),
				// tweetmeme
				array(
					'code'        => 'tweetmeme',
					'function'    => 'woo_shortcode_tweetmeme',
					'title'       => __( 'Tweetmeme', self::$text_domain ),
					'description' => __( 'Displays a tweetmeme.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// twitter
				array(
					'code'        => 'twitter',
					'function'    => 'woo_shortcode_twitter',
					'title'       => __( 'Twitter', self::$text_domain ),
					'description' => __( 'Displays a twitter.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// digg
				array(
					'code'        => 'digg',
					'function'    => 'woo_shortcode_digg',
					'title'       => __( 'Digg', self::$text_domain ),
					'description' => __( 'Displays a digg.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fblike
				array(
					'code'        => 'fblike',
					'function'    => 'woo_shortcode_fblike',
					'title'       => __( 'Fblike', self::$text_domain ),
					'description' => __( 'Displays a fblike.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// twocol_one
				array(
					'code'        => 'twocol_one',
					'function'    => 'woo_shortcode_twocol_one',
					'title'       => __( 'Twocol_one', self::$text_domain ),
					'description' => __( 'Displays a twocol_one.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// twocol_one_last
				array(
					'code'        => 'twocol_one_last',
					'function'    => 'woo_shortcode_twocol_one_last',
					'title'       => __( 'Twocol_one_last', self::$text_domain ),
					'description' => __( 'Displays a twocol_one_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// threecol_one
				array(
					'code'        => 'threecol_one',
					'function'    => 'woo_shortcode_threecol_one',
					'title'       => __( 'Threecol_one', self::$text_domain ),
					'description' => __( 'Displays a threecol_one.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// threecol_one_last
				array(
					'code'        => 'threecol_one_last',
					'function'    => 'woo_shortcode_threecol_one_last',
					'title'       => __( 'Threecol_one_last', self::$text_domain ),
					'description' => __( 'Displays a threecol_one_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// threecol_two
				array(
					'code'        => 'threecol_two',
					'function'    => 'woo_shortcode_threecol_two',
					'title'       => __( 'Threecol_two', self::$text_domain ),
					'description' => __( 'Displays a threecol_two.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// threecol_two_last
				array(
					'code'        => 'threecol_two_last',
					'function'    => 'woo_shortcode_threecol_two_last',
					'title'       => __( 'Threecol_two_last', self::$text_domain ),
					'description' => __( 'Displays a threecol_two_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fourcol_one
				array(
					'code'        => 'fourcol_one',
					'function'    => 'woo_shortcode_fourcol_one',
					'title'       => __( 'Fourcol_one', self::$text_domain ),
					'description' => __( 'Displays a fourcol_one.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fourcol_one_last
				array(
					'code'        => 'fourcol_one_last',
					'function'    => 'woo_shortcode_fourcol_one_last',
					'title'       => __( 'Fourcol_one_last', self::$text_domain ),
					'description' => __( 'Displays a fourcol_one_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fourcol_two
				array(
					'code'        => 'fourcol_two',
					'function'    => 'woo_shortcode_fourcol_two',
					'title'       => __( 'Fourcol_two', self::$text_domain ),
					'description' => __( 'Displays a fourcol_two.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fourcol_two_last
				array(
					'code'        => 'fourcol_two_last',
					'function'    => 'woo_shortcode_fourcol_two_last',
					'title'       => __( 'Fourcol_two_last', self::$text_domain ),
					'description' => __( 'Displays a fourcol_two_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fourcol_three
				array(
					'code'        => 'fourcol_three',
					'function'    => 'woo_shortcode_fourcol_three',
					'title'       => __( 'Fourcol_three', self::$text_domain ),
					'description' => __( 'Displays a fourcol_three.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fourcol_three_last
				array(
					'code'        => 'fourcol_three_last',
					'function'    => 'woo_shortcode_fourcol_three_last',
					'title'       => __( 'Fourcol_three_last', self::$text_domain ),
					'description' => __( 'Displays a fourcol_three_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fivecol_one
				array(
					'code'        => 'fivecol_one',
					'function'    => 'woo_shortcode_fivecol_one',
					'title'       => __( 'Fivecol_one', self::$text_domain ),
					'description' => __( 'Displays a fivecol_one.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fivecol_one_last
				array(
					'code'        => 'fivecol_one_last',
					'function'    => 'woo_shortcode_fivecol_one_last',
					'title'       => __( 'Fivecol_one_last', self::$text_domain ),
					'description' => __( 'Displays a fivecol_one_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fivecol_two
				array(
					'code'        => 'fivecol_two',
					'function'    => 'woo_shortcode_fivecol_two',
					'title'       => __( 'Fivecol_two', self::$text_domain ),
					'description' => __( 'Displays a fivecol_two.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fivecol_two_last
				array(
					'code'        => 'fivecol_two_last',
					'function'    => 'woo_shortcode_fivecol_two_last',
					'title'       => __( 'Fivecol_two_last', self::$text_domain ),
					'description' => __( 'Displays a fivecol_two_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fivecol_three
				array(
					'code'        => 'fivecol_three',
					'function'    => 'woo_shortcode_fivecol_three',
					'title'       => __( 'Fivecol_three', self::$text_domain ),
					'description' => __( 'Displays a fivecol_three.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fivecol_three_last
				array(
					'code'        => 'fivecol_three_last',
					'function'    => 'woo_shortcode_fivecol_three_last',
					'title'       => __( 'Fivecol_three_last', self::$text_domain ),
					'description' => __( 'Displays a fivecol_three_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fivecol_four
				array(
					'code'        => 'fivecol_four',
					'function'    => 'woo_shortcode_fivecol_four',
					'title'       => __( 'Fivecol_four', self::$text_domain ),
					'description' => __( 'Displays a fivecol_four.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fivecol_four_last
				array(
					'code'        => 'fivecol_four_last',
					'function'    => 'woo_shortcode_fivecol_four_last',
					'title'       => __( 'Fivecol_four_last', self::$text_domain ),
					'description' => __( 'Displays a fivecol_four_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_one
				array(
					'code'        => 'sixcol_one',
					'function'    => 'woo_shortcode_sixcol_one',
					'title'       => __( 'Sixcol_one', self::$text_domain ),
					'description' => __( 'Displays a sixcol_one.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_one_last
				array(
					'code'        => 'sixcol_one_last',
					'function'    => 'woo_shortcode_sixcol_one_last',
					'title'       => __( 'Sixcol_one_last', self::$text_domain ),
					'description' => __( 'Displays a sixcol_one_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_two
				array(
					'code'        => 'sixcol_two',
					'function'    => 'woo_shortcode_sixcol_two',
					'title'       => __( 'Sixcol_two', self::$text_domain ),
					'description' => __( 'Displays a sixcol_two.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_two_last
				array(
					'code'        => 'sixcol_two_last',
					'function'    => 'woo_shortcode_sixcol_two_last',
					'title'       => __( 'Sixcol_two_last', self::$text_domain ),
					'description' => __( 'Displays a sixcol_two_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_three
				array(
					'code'        => 'sixcol_three',
					'function'    => 'woo_shortcode_sixcol_three',
					'title'       => __( 'Sixcol_three', self::$text_domain ),
					'description' => __( 'Displays a sixcol_three.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_three_last
				array(
					'code'        => 'sixcol_three_last',
					'function'    => 'woo_shortcode_sixcol_three_last',
					'title'       => __( 'Sixcol_three_last', self::$text_domain ),
					'description' => __( 'Displays a sixcol_three_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_four
				array(
					'code'        => 'sixcol_four',
					'function'    => 'woo_shortcode_sixcol_four',
					'title'       => __( 'Sixcol_four', self::$text_domain ),
					'description' => __( 'Displays a sixcol_four.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_four_last
				array(
					'code'        => 'sixcol_four_last',
					'function'    => 'woo_shortcode_sixcol_four_last',
					'title'       => __( 'Sixcol_four_last', self::$text_domain ),
					'description' => __( 'Displays a sixcol_four_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_five
				array(
					'code'        => 'sixcol_five',
					'function'    => 'woo_shortcode_sixcol_five',
					'title'       => __( 'Sixcol_five', self::$text_domain ),
					'description' => __( 'Displays a sixcol_five.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// sixcol_five_last
				array(
					'code'        => 'sixcol_five_last',
					'function'    => 'woo_shortcode_sixcol_five_last',
					'title'       => __( 'Sixcol_five_last', self::$text_domain ),
					'description' => __( 'Displays a sixcol_five_last.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// hr
				array(
					'code'        => 'hr',
					'function'    => 'woo_shortcode_hr',
					'title'       => __( 'Hr', self::$text_domain ),
					'description' => __( 'Displays a hr.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// divider
				array(
					'code'        => 'divider',
					'function'    => 'woo_shortcode_divider',
					'title'       => __( 'Divider', self::$text_domain ),
					'description' => __( 'Displays a divider.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// divider_flat
				array(
					'code'        => 'divider_flat',
					'function'    => 'woo_shortcode_divider_flat',
					'title'       => __( 'Divider_flat', self::$text_domain ),
					'description' => __( 'Displays a divider_flat.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// quote
				array(
					'code'        => 'quote',
					'function'    => 'woo_shortcode_quote',
					'title'       => __( 'Quote', self::$text_domain ),
					'description' => __( 'Displays a quote.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// ilink
				array(
					'code'        => 'ilink',
					'function'    => 'woo_shortcode_ilink',
					'title'       => __( 'Ilink', self::$text_domain ),
					'description' => __( 'Displays a ilink.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// toggle
				array(
					'code'        => 'toggle',
					'function'    => 'woo_shortcode_toggle',
					'title'       => __( 'Toggle', self::$text_domain ),
					'description' => __( 'Displays a toggle.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// fbshare
				array(
					'code'        => 'fbshare',
					'function'    => 'woo_shortcode_fbshare',
					'title'       => __( 'Fbshare', self::$text_domain ),
					'description' => __( 'Displays a fbshare.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// contact_form
				array(
					'code'        => 'contact_form',
					'function'    => 'woo_shortcode_contactform',
					'title'       => __( 'Contact_form', self::$text_domain ),
					'description' => __( 'Displays a contact_form.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// tabs
				array(
					'code'        => 'tabs',
					'function'    => 'woo_shortcode_tabs',
					'title'       => __( 'Tabs', self::$text_domain ),
					'description' => __( 'Displays a tabs.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// tab
				array(
					'code'        => 'tab',
					'function'    => 'woo_shortcode_tab_single',
					'title'       => __( 'Tab', self::$text_domain ),
					'description' => __( 'Displays a tab.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// dropcap
				array(
					'code'        => 'dropcap',
					'function'    => 'woo_shortcode_dropcap',
					'title'       => __( 'Dropcap', self::$text_domain ),
					'description' => __( 'Displays a dropcap.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// highlight
				array(
					'code'        => 'highlight',
					'function'    => 'woo_shortcode_highlight',
					'title'       => __( 'Highlight', self::$text_domain ),
					'description' => __( 'Displays a highlight.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// abbr
				array(
					'code'        => 'abbr',
					'function'    => 'woo_shortcode_abbreviation',
					'title'       => __( 'Abbr', self::$text_domain ),
					'description' => __( 'Displays a abbr.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// typography
				array(
					'code'        => 'typography',
					'function'    => 'woo_shortcode_typography',
					'title'       => __( 'Typography', self::$text_domain ),
					'description' => __( 'Displays a typography.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// unordered_list
				array(
					'code'        => 'unordered_list',
					'function'    => 'woo_shortcode_unorderedlist',
					'title'       => __( 'Unordered_list', self::$text_domain ),
					'description' => __( 'Displays a unordered_list.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// ordered_list
				array(
					'code'        => 'ordered_list',
					'function'    => 'woo_shortcode_orderedlist',
					'title'       => __( 'Ordered_list', self::$text_domain ),
					'description' => __( 'Displays a ordered_list.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// social_icon
				array(
					'code'        => 'social_icon',
					'function'    => 'woo_shortcode_socialicon',
					'title'       => __( 'Social_icon', self::$text_domain ),
					'description' => __( 'Displays a social_icon.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// linkedin_share
				array(
					'code'        => 'linkedin_share',
					'function'    => 'woo_shortcode_linkedin_share',
					'title'       => __( 'Linkedin_share', self::$text_domain ),
					'description' => __( 'Displays a linkedin_share.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// google_plusone
				array(
					'code'        => 'google_plusone',
					'function'    => 'woo_shortcode_google_plusone',
					'title'       => __( 'Google_plusone', self::$text_domain ),
					'description' => __( 'Displays a google_plusone.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// twitter_follow
				array(
					'code'        => 'twitter_follow',
					'function'    => 'woo_shortcode_twitter_follow',
					'title'       => __( 'Twitter_follow', self::$text_domain ),
					'description' => __( 'Displays a twitter_follow.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// stumbleupon
				array(
					'code'        => 'stumbleupon',
					'function'    => 'woo_shortcode_stumbleupon',
					'title'       => __( 'Stumbleupon', self::$text_domain ),
					'description' => __( 'Displays a stumbleupon.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// pinterest
				array(
					'code'        => 'pinterest',
					'function'    => 'woo_shortcode_pinterest',
					'title'       => __( 'Pinterest', self::$text_domain ),
					'description' => __( 'Displays a pinterest.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// view_full_article
				array(
					'code'        => 'view_full_article',
					'function'    => 'woo_shortcode_view_full_article',
					'title'       => __( 'View_full_article', self::$text_domain ),
					'description' => __( 'Displays a view_full_article.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// custom_field
				array(
					'code'        => 'custom_field',
					'function'    => 'woo_shortcode_custom_field',
					'title'       => __( 'Custom_field', self::$text_domain ),
					'description' => __( 'Displays a custom_field.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// post_date
				array(
					'code'        => 'post_date',
					'function'    => 'woo_shortcode_post_date',
					'title'       => __( 'Post_date', self::$text_domain ),
					'description' => __( 'Displays a post_date.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// post_time
				array(
					'code'        => 'post_time',
					'function'    => 'woo_shortcode_post_time',
					'title'       => __( 'Post_time', self::$text_domain ),
					'description' => __( 'Displays a post_time.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// post_author
				array(
					'code'        => 'post_author',
					'function'    => 'woo_shortcode_post_author',
					'title'       => __( 'Post_author', self::$text_domain ),
					'description' => __( 'Displays a post_author.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// post_author_link
				array(
					'code'        => 'post_author_link',
					'function'    => 'woo_shortcode_post_author_link',
					'title'       => __( 'Post_author_link', self::$text_domain ),
					'description' => __( 'Displays a post_author_link.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// post_author_posts_link
				array(
					'code'        => 'post_author_posts_link',
					'function'    => 'woo_shortcode_post_author_posts_link',
					'title'       => __( 'Post_author_posts_link', self::$text_domain ),
					'description' => __( 'Displays a post_author_posts_link.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// post_comments
				array(
					'code'        => 'post_comments',
					'function'    => 'woo_shortcode_post_comments',
					'title'       => __( 'Post_comments', self::$text_domain ),
					'description' => __( 'Displays a post_comments.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// post_tags
				array(
					'code'        => 'post_tags',
					'function'    => 'woo_shortcode_post_tags',
					'title'       => __( 'Post_tags', self::$text_domain ),
					'description' => __( 'Displays a post_tags.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// post_categories
				array(
					'code'        => 'post_categories',
					'function'    => 'woo_shortcode_post_categories',
					'title'       => __( 'Post_categories', self::$text_domain ),
					'description' => __( 'Displays a post_categories.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// post_edit
				array(
					'code'        => 'post_edit',
					'function'    => 'woo_shortcode_post_edit',
					'title'       => __( 'Post_edit', self::$text_domain ),
					'description' => __( 'Displays a post_edit.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// footer_backtotop
				array(
					'code'        => 'footer_backtotop',
					'function'    => 'woo_shortcode_footer_backtotop',
					'title'       => __( 'Footer_backtotop', self::$text_domain ),
					'description' => __( 'Displays a footer_backtotop.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// footer_childtheme_link
				array(
					'code'        => 'footer_childtheme_link',
					'function'    => 'woo_shortcode_footer_childtheme_link',
					'title'       => __( 'Footer_childtheme_link', self::$text_domain ),
					'description' => __( 'Displays a footer_childtheme_link.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// footer_wordpress_link
				array(
					'code'        => 'footer_wordpress_link',
					'function'    => 'woo_shortcode_footer_wordpress_link',
					'title'       => __( 'Footer_wordpress_link', self::$text_domain ),
					'description' => __( 'Displays a footer_wordpress_link.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// footer_woothemes_link
				array(
					'code'        => 'footer_woothemes_link',
					'function'    => 'woo_shortcode_footer_woothemes_link',
					'title'       => __( 'Footer_woothemes_link', self::$text_domain ),
					'description' => __( 'Displays a footer_woothemes_link.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// footer_loginout
				array(
					'code'        => 'footer_loginout',
					'function'    => 'woo_shortcode_footer_loginout',
					'title'       => __( 'Footer_loginout', self::$text_domain ),
					'description' => __( 'Displays a footer_loginout.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// site_copyright
				array(
					'code'        => 'site_copyright',
					'function'    => 'woo_shortcode_site_copyright',
					'title'       => __( 'Site_copyright', self::$text_domain ),
					'description' => __( 'Displays a site_copyright.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),
// site_credit
				array(
					'code'        => 'site_credit',
					'function'    => 'woo_shortcode_site_credit',
					'title'       => __( 'Site_credit', self::$text_domain ),
					'description' => __( 'Displays a site_credit.', self::$text_domain ),
					'tags'        => 'woothemes',
					'atts'        => array(),
				),

			) as $shortcode
		) {

			$shortcode['category'] = 'ecommerce';
			$shortcode['source']   = 'Woothemes';

			render_add_shortcode( $shortcode );
			render_add_shortcode_category( array(
				'id'    => 'ecommerce',
				'label' => __( 'Ecommerce', self::$text_domain ),
				'icon'  => 'dashicons-cart',
			) );
		}
	}

	/**
	 * Display a notice in the admin if Woothemes and Render are not both active.
	 *
	 * @since  0.1.0
	 * @access private
	 */
	public function _notice() {
		?>
		<div class="error">
			<p>
				<?php _e( 'Render Woothemes is not active due to the following errors:', self::$text_domain ); ?>
			</p>

			<ul>
				<?php foreach ( $this->deactivate_reasons as $reason ) : ?>
					<li>
						<?php echo "&bull; $reason"; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php
	}
}

$render_edd = new Render_Woothemes();

add_action( 'admin_notices', function () {
	global $shortcode_tags;
	//var_dump($shortcode_tags);
//	echo "<ul>";
//	foreach ( $shortcode_tags as $k => $s ) {
//		echo '<li>// ' . $k;
//		echo "<br/>array(<br/>
//					'code'        => '" . $k . "',<br/>
//					'function'    => '" . $s . "',<br/>
//					'title'       => __( '" . ucfirst( $k ) . "', self::\$text_domain ),<br/>
//					'description' => __( 'Displays a " . $k . ".', self::\$text_domain ),<br/>
//					'tags'        => 'woothemes',<br/>
//					'atts'        => array(<br/>
//					),<br/>
//					),";
//		echo '</li>';
//	}
//	echo "</ul>";
} );