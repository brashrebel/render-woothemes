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
 * @since 1.0.0
 */
define( 'RENDER_EDD_VERSION', '1.0.0' );

/**
 * The absolute server path to Render's root directory.
 *
 * @since 1.0.0
 */
define( 'RENDER_EDD_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The URI to Render's root directory.
 *
 * @since 1.0.0
 */
define( 'RENDER_EDD_URL', plugins_url( '', __FILE__ ) );

/**
 * Class Render_EDD
 *
 * Initializes and loads the plugin.
 *
 * @since   0.1.0
 *
 * @package Render_EDD
 */
class Render_EDD {

	/**
	 * The reason for deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $deactivate_reasons = array();

	/**
	 * The plugin text domain.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $text_domain = 'Render_EDD';

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

		// Requires Project Panorama
		if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
			$this->deactivate_reasons[] = __( 'Easy Digital Downloads is not active', self::$text_domain );
		}

		// 1.0.3 is when extension integration was introduced
		if ( defined( 'RENDER_VERSION' ) && version_compare( RENDER_VERSION, '1.0.0', '<' ) ) {
			$this->deactivate_reasons[] = sprintf(
				__( 'This plugin requires at least Render version %s. You have version %s installed.', self::$text_domain ),
				'1.0.3',
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
		load_plugin_textdomain( 'Render_EDD', false, RENDER_EDD_PATH . '/languages' );

		// Add EDD styles to tinymce
		add_filter( 'render_editor_styles', array( __CLASS__, '_add_edd_style' ) );
		add_filter( 'render_editor_styles', array( __CLASS__, '_add_render_edd_style' ) );

		// Licensing
		render_setup_license( 'render_edd', 'Easy Digital Downloads', RENDER_EDD_VERSION, __FILE__ );

		// Remove media button
		render_disable_tinymce_media_button( 'edd_media_button', 'Insert Download', 11 );
	}

	/**
	 * Adds the EDD stylesheet to the TinyMCE.
	 *
	 * EDD doesn't register the stylesheet, so I can't grab it that way, but Pippin mentioned I can just call the
	 * function to enqueue the style, grab the stylesheet, and then dequeue it pretty easily.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 * @return array The styles.
	 */
	public static function _add_edd_style( $styles ) {

		global $wp_styles;

		edd_register_styles();

		if ( isset( $wp_styles->registered['edd-styles'] ) ) {
			$styles[] = $wp_styles->registered['edd-styles']->src;
		}

		wp_dequeue_style( 'edd-styles' );

		return $styles;
	}

	/**
	 * Adds the Render EDD stylesheet to the TinyMCE through Render.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 * @return array The styles.
	 */
	public static function _add_render_edd_style( $styles ) {

		$styles[] = RENDER_EDD_URL . "/assets/css/render-edd.min.css";

		return $styles;
	}

	/**
	 * Add data and inputs for all EDD shortcodes and pass them through Render's function.
	 *
	 * @since 0.1.0
	 */
	private function _add_shortcodes() {

		global $edd_options;

		foreach (
			array(
				// Download Cart
				array(
					'code'        => 'download_cart',
					'function'    => 'edd_cart_shortcode',
					'title'       => __( 'Download Cart', self::$text_domain ),
					'description' => __( 'Lists items in cart.', self::$text_domain ),
					'tags'        => 'cart edd ecommerce downloads digital products',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Download Checkout
				array(
					'code'        => 'download_checkout',
					'function'    => 'edd_checkout_form_shortcode',
					'title'       => __( 'Download Checkout', self::$text_domain ),
					'description' => __( 'Displays the checkout form.', self::$text_domain ),
					'tags'        => 'cart edd ecommerce downloads digital products form',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Download History
				array(
					'code'        => 'download_history',
					'function'    => 'edd_download_history',
					'title'       => __( 'Download History', self::$text_domain ),
					'description' => __( 'Displays all the products a user has purchased with links to the files.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads digital products history files purchase',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Purchase History
				array(
					'code'        => 'purchase_history',
					'function'    => 'edd_purchase_history',
					'title'       => __( 'Purchase History', self::$text_domain ),
					'description' => __( 'Displays the complete purchase history for a user.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads digital products history purchase',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Download Discounts
				array(
					'code'        => 'download_discounts',
					'function'    => 'edd_discounts_shortcode',
					'title'       => __( 'Download Discounts', self::$text_domain ),
					'description' => __( 'Lists all the currently available discount codes on your site.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads digital products coupon discount code',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Profile Editor
				array(
					'code'        => 'edd_profile_editor',
					'function'    => 'edd_profile_editor_shortcode',
					'title'       => __( 'EDD Profile Editor', self::$text_domain ),
					'description' => __( 'Presents users with a form for updating their profile.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads digital user profile account',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Login
				array(
					'code'        => 'edd_login',
					'function'    => 'edd_login_form_shortcode',
					'title'       => __( 'EDD Login', self::$text_domain ),
					'description' => __( 'Displays a simple login form for non-logged in users.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads login users form',
					'atts'        => array(
						'redirect' => array(
							'label'       => __( 'Redirect', self::$text_domain ),
							'description' => __( 'Redirect to this page after login.', self::$text_domain ),
							'type'        => 'selectbox',
							'properties'  => array(
								'allowCustomInput' => true,
								'groups'           => array(),
								'callback'         => array(
									'groups'   => true,
									'function' => 'render_sc_post_list',
								),
								'placeholder'      => __( 'Same page', self::$text_domain ),
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Register
				array(
					'code'        => 'edd_register',
					'function'    => 'edd_register_form_shortcode',
					'title'       => __( 'EDD Register', self::$text_domain ),
					'description' => __( 'Displays a registration form for non-logged in users.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads login users form register signup',
					'atts'        => array(
						'redirect' => array(
							'label'       => __( 'Redirect', self::$text_domain ),
							'description' => __( 'Redirect to this page after login.', self::$text_domain ),
							'type'        => 'selectbox',
							'properties'  => array(
								'allowCustomInput' => true,
								'groups'           => array(),
								'callback'         => array(
									'groups'   => true,
									'function' => 'render_sc_post_list',
								),
								'placeholder'      => __( 'Same page', self::$text_domain ),
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Price
				array(
					'code'        => 'edd_price',
					'function'    => 'edd_download_price_shortcode',
					'title'       => __( 'Download Price', self::$text_domain ),
					'description' => __( 'Displays the price of a specific download.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads product price',
					'atts'        => array(
						'id'       => render_sc_attr_template( 'post_list', array(
							'required'   => true,
							'properties' => array(
								'no_options'  => __( 'No downloads available', self::$text_domain ),
								'placeholder' => __( 'Select a download', self::$text_domain ),
							),
						), array(
							'post_type' => 'download',
						) ),
						'price_id' => array(
							'label'       => __( 'Price ID', self::$text_domain ),
							'description' => __( 'Optional. For variable pricing.', self::$text_domain ),
						),
					),
					'render'      => true,
				),
				// Receipt
				array(
					'code'        => 'edd_receipt',
					'function'    => 'edd_receipt_shortcode',
					'title'       => __( 'Download Receipt', self::$text_domain ),
					'description' => __( 'Displays a the complete details of a completed purchase.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads purchase receipt confirmation order payment complete checkout',
					'atts'        => array(
						'error'       => array(
							'label'      => __( 'Error Message', self::$text_domain ),
							'properties' => array(
								'placeholder' => __( 'Sorry, trouble retrieving payment receipt.', 'edd' ),
							),
						),
						'price'       => array(
							'label'      => __( 'Hide Price', self::$text_domain ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'discount'    => array(
							'label'      => __( 'Hide Discounts', self::$text_domain ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'products'    => array(
							'label'      => __( 'Hide Products', self::$text_domain ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'date'        => array(
							'label'      => __( 'Hide Purchase Date', self::$text_domain ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'payment_key' => array(
							'label'      => __( 'Hide Payment Key', self::$text_domain ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'payment_id'  => array(
							'label'      => __( 'Hide Order Number', self::$text_domain ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Purchase Link
				array(
					'code'        => 'purchase_link',
					'function'    => 'edd_download_shortcode',
					'title'       => __( 'Download Purchase Link', self::$text_domain ),
					'description' => __( 'Displays a button which adds a specific product to the cart.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads purchase product buy button pay link checkout',
					'atts'        => array(
						'id'      => render_sc_attr_template( 'post_list', array(
							'label'       => __( 'Downloads', self::$text_domain ),
							'required'   => true,
							'properties' => array(
								'no_options'  => __( 'No downloads available', self::$text_domain ),
								'placeholder' => __( 'Select a download', self::$text_domain ),
							),
						), array(
							'post_type' => 'download',
						) ),
						'price'   => array(
							'label'      => __( 'Hide Price', self::$text_domain ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'text'    => array(
							'label'      => __( 'Link Text', self::$text_domain ),
							'properties' => array(
								'placeholder' => isset( $edd_options['add_to_cart_text'] ) && $edd_options['add_to_cart_text'] != '' ? $edd_options['add_to_cart_text'] : __( 'Purchase', 'edd' ),
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Style', self::$text_domain ),
						),
						'style'   => array(
							'label'      => __( 'Style', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'   => isset( $edd_options['button_style'] ) && $edd_options['button_style'] == 'plain',
								'values' => array(
									'button' => __( 'Button', self::$text_domain ),
									'plain'  => __( 'Text', self::$text_domain ),
								),
							),
						),
						'color'   => array(
							'label'      => __( 'Button Color', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => isset( $edd_options['checkout_color'] ) ? $edd_options['checkout_color'] : 'blue',
							'properties' => array(
								'options' => array(
									'white'     => __( 'White', self::$text_domain ),
									'gray'      => __( 'Gray', self::$text_domain ),
									'blue'      => __( 'Blue', self::$text_domain ),
									'red'       => __( 'Red', self::$text_domain ),
									'green'     => __( 'Green', self::$text_domain ),
									'yellow'    => __( 'Yellow', self::$text_domain ),
									'orange'    => __( 'Orange', self::$text_domain ),
									'dark gray' => __( 'Dark gray', self::$text_domain ),
									'inherit'   => __( 'Inherit', self::$text_domain ),
								),
							),
						),
						'sku'     => array(
							'label'       => __( 'SKU', self::$text_domain ),
							'description' => __( 'Get download by SKU (overrides download set above)', self::$text_domain ),
							'advanced'    => true,
						),
						'direct'  => array(
							'label'      => __( 'Direct Purchase', self::$text_domain ),
							'type'       => 'checkbox',
							'properties' => array(
								'label' => __( 'Send customer to directly to PayPal', self::$text_domain ),
							),
							'advanced'   => true,
						),
						'class'   => array(
							'label'    => __( 'CSS Class', self::$text_domain ),
							'default'  => 'edd-submit',
							'advanced' => true,
						),
						'form_id' => array(
							'label'    => __( 'Form ID', self::$text_domain ),
							'default'  => '',
							'advanced' => true,
						),
					),
					'render'      => array(
						'noStyle' => true,
					),
				),
				// Purchase Collection
				array(
					'code'        => 'purchase_collection',
					'function'    => 'edd_purchase_collection_shortcode',
					'title'       => __( 'Download Purchase Collection', self::$text_domain ),
					'description' => __( 'Displays a button which adds all products in a specific taxonomy term to the cart.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads purchase product buy button pay link checkout',
					'atts'        => array(
						'taxonomy' => array(
							'label'      => __( 'Taxonomy', self::$text_domain ),
							'type'       => 'selectbox',
							'required'   => true,
							'properties' => array(
								'options' => array(
									'download_category' => __( 'Category', self::$text_domain ),
									'download_tag'      => __( 'Tag', self::$text_domain ),
								),
							),
						),
						'terms'    => array(
							'label'       => __( 'Terms', self::$text_domain ),
							'required'    => true,
							'description' => __( 'Enter a comma separated list of terms for the selected taxonomy.', self::$text_domain ),
						),
						'text'     => array(
							'label'   => __( 'Link Text', self::$text_domain ),
							'default' => __( 'Purchase All Items', 'edd' ),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Style', self::$text_domain ),
						),
						'style'    => array(
							'label'      => __( 'Style', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'   => isset( $edd_options['button_style'] ) && $edd_options['button_style'] == 'plain',
								'values' => array(
									'button' => __( 'Button', self::$text_domain ),
									'plain'  => __( 'Text', self::$text_domain ),
								),
							),
						),
						'color'    => array(
							'label'      => __( 'Button Color', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => isset( $edd_options['checkout_color'] ) ? $edd_options['checkout_color'] : 'blue',
							'properties' => array(
								'options' => array(
									'gray'      => __( 'Gray', self::$text_domain ),
									'blue'      => __( 'Blue', self::$text_domain ),
									'green'     => __( 'Green', self::$text_domain ),
									'dark gray' => __( 'Dark gray', self::$text_domain ),
									'yellow'    => __( 'Yellow', self::$text_domain ),
								),
							),
						),
						'class'    => array(
							'label'    => __( 'CSS Class', self::$text_domain ),
							'default'  => 'edd-submit',
							'advanced' => true,
						),
					),
					'render'      => array(
						'noStyle' => true,
					),
				),
				// Downloads
				array(
					'code'        => 'downloads',
					'function'    => 'edd_downloads_query',
					'title'       => __( 'Downloads', self::$text_domain ),
					'description' => __( 'Outputs a list or grid of downloadable products.', self::$text_domain ),
					'tags'        => 'edd ecommerce downloads purchase product list',
					'atts'        => array(
						array(
							'type'  => 'section_break',
							'label' => __( 'Downloads', self::$text_domain ),
						),
						'category'         => render_sc_attr_template( 'terms_list', array(
							'label'      => __( 'Categories', self::$text_domain ),
							'properties' => array(
								'placeholder' => __( 'Download category', self::$text_domain ),
								'multi'       => true,
							),
						), array(
							'taxonomies' => array( 'download_category' ),
						) ),
						'tags'             => render_sc_attr_template( 'terms_list', array(
							'label'      => __( 'Tags', self::$text_domain ),
							'properties' => array(
								'placeholder' => __( 'Download tag', self::$text_domain ),
								'multi'       => true,
							),
						), array(
							'taxonomies' => array( 'download_tag' ),
						) ),
						'relation'         => array(
							'label'       => __( 'Relation', self::$text_domain ),
							'description' => __( 'Downloads must be in ALL categories / tags, or at least just one.', self::$text_domain ),
							'type'        => 'toggle',
							'properties'  => array(
								'values' => array(
									'AND' => __( 'All', self::$text_domain ) . '&nbsp;',
									// For spacing in the toggle switch
									'OR'  => __( 'One', self::$text_domain ),
								),
							),
						),
						'exclude_category' => render_sc_attr_template( 'terms_list', array(
							'label'      => __( 'Exclude Categories', self::$text_domain ),
							'properties' => array(
								'placeholder' => __( 'Download category', self::$text_domain ),
								'multi'       => true,
							),
						), array(
							'taxonomies' => array( 'download_category' ),
						) ),
						'exclude_tags'     => render_sc_attr_template( 'terms_list', array(
							'label'      => __( 'Exclude Tags', self::$text_domain ),
							'properties' => array(
								'placeholder' => __( 'Download tag', self::$text_domain ),
								'multi'       => true,
							),
						), array(
							'taxonomies' => array( 'download_tag' ),
						) ),
						'number'           => array(
							'label'      => __( 'Download Count', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 9,
							'properties' => array(
								'min' => 1,
								'max' => 50,
							),
						),
						'ids'              => render_sc_attr_template( 'post_list', array(
							'label'       => __( 'Downloads', self::$text_domain ),
							'description' => __( 'Enter one or more downloads to use ONLY these downloads.', self::$text_domain ),
							'properties'  => array(
								'no_options'  => __( 'No downloads available', self::$text_domain ),
								'placeholder' => __( 'Select a download', self::$text_domain ),
								'multi'       => true,
							),
						), array(
							'post_type' => 'download',
						) ),
						'orderby'          => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'post_date',
							'properties' => array(
								'options' => array(
									'price'     => __( 'Price', self::$text_domain ),
									'id'        => __( 'ID', self::$text_domain ),
									'random'    => __( 'Random', self::$text_domain ),
									'post_date' => __( 'Published date', self::$text_domain ),
									'title'     => __( 'Title', self::$text_domain ),
								),
							),
						),
						'order'            => array(
							'label'      => __( 'Order', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'DESC' => __( 'Descending', self::$text_domain ),
									'ASC'  => __( 'Ascending', self::$text_domain ),
								),
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Visibility', self::$text_domain ),
						),
						'price'            => array(
							'label'      => __( 'Price', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', self::$text_domain ),
									'yes' => __( 'Show', self::$text_domain ),
								),
							),
						),
						'excerpt'          => array(
							'label'      => __( 'Excerpt', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'          => true,
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', self::$text_domain ),
									'yes' => __( 'Show', self::$text_domain ),
								),
							),
						),
						'full_content'     => array(
							'label'      => __( 'Full Content', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', self::$text_domain ),
									'yes' => __( 'Show', self::$text_domain ),
								),
							),
						),
						'buy_button'       => array(
							'label'      => __( 'Buy Button', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'          => true,
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', self::$text_domain ),
									'yes' => __( 'Show', self::$text_domain ),
								),
							),
						),
						'thumbnails'       => array(
							'label'      => __( 'Thumbnails', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'          => true,
								'deselectStyle' => true,
								'values'        => array(
									'false' => __( 'Hide', self::$text_domain ),
									'true'  => __( 'Show', self::$text_domain ),
								),
							),
						),
						'columns'          => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 3,
							'properties' => array(
								'min' => 0,
								'max' => 6,
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					)
				),
			) as $shortcode
		) {

			$shortcode['category'] = 'ecommerce';
			$shortcode['source']   = 'Easy Digital Downloads';

			render_add_shortcode( $shortcode );
			render_add_shortcode_category( array(
				'id'    => 'ecommerce',
				'label' => __( 'Ecommerce', self::$text_domain ),
				'icon'  => 'dashicons-cart',
			) );
		}
	}

	/**
	 * Display a notice in the admin if EDD and Render are not both active.
	 *
	 * @since  0.1.0
	 * @access private
	 */
	public function _notice() {
		?>
		<div class="error">
			<p>
				<?php _e( 'Render Easy Digital Downloads is not active due to the following errors:', self::$text_domain ); ?>
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

$render_edd = new Render_EDD();

/**
 * TinyMCE callback for the EDD Login Form shortcode.
 *
 * Logs out the user before calling the original shortcode callback.
 *
 * @since  0.1.0
 * @access Private
 *
 * @param array  $atts    The attributes sent to the shortcode.
 * @param string $content The content inside the shortcode.
 * @return string Shortcode output,
 */
function edd_login_form_shortcode_tinymce( $atts = array(), $content = '' ) {

	// Log out for displaying this shortcode
	render_tinyme_log_out();

	$output = edd_login_form_shortcode( $atts, $content );

	return $output;
}

/**
 * TinyMCE callback for the EDD Register Form shortcode.
 *
 * Logs out the user before calling the original shortcode callback.
 *
 * @since  0.1.0
 *
 * @access Private
 *
 * @param array  $atts    The attributes sent to the shortcode.
 * @param string $content The content inside the shortcode.
 * @return string Shortcode output.
 */
function edd_register_form_shortcode_tinymce( $atts = array(), $content = '' ) {

	// Log out for displaying this shortcode
	render_tinyme_log_out();

	$output = edd_register_form_shortcode( $atts, $content );

	return $output;
}