<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode class.
 *
 * @since 1.0.0
 */
class OMAPI_Shortcode {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set our object.
		$this->set();

		// Load actions and filters.
		add_shortcode( 'optin-monster', array( $this, 'shortcode' ) );
		add_shortcode( 'optin-monster-shortcode', array( $this, 'shortcode_v1' ) );
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode' );

	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.0.0
	 */
	public function set() {

		self::$instance = $this;
		$this->base     = OMAPI::get_instance();

	}

	/**
	 * Creates the shortcode for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @global object $post The current post object.
	 *
	 * @param array $atts Array of shortcode attributes.
	 * @return string     The optin output.
	 */
	public function shortcode( $atts ) {

		// Checking if AMP is enabled.
		if ( OMAPI_Utils::is_amp_enabled() ) {
			return;
		}

		global $post;

		// Merge default attributes with passed attributes.
		$atts = shortcode_atts(
			array(
				'slug'        => '',
				'followrules' => 'false',
				// id attribute is deprecated.
				'id'          => '',
			),
			$atts,
			'optin-monster'
		);

		$identifier = false;

		if ( ! empty( $atts['slug'] ) ) {
			$identifier = $atts['slug'];
		}

		if ( ! empty( $atts['id'] ) ) {
			$identifier = $atts['id'];
		}

		if ( empty( $identifier ) ) {
			// A custom attribute must have been passed. Allow it to be filtered to grab the optin ID from a custom source.
			$identifier = apply_filters( 'optin_monster_api_custom_optin_id', false, $atts, $post );
		}

		// Allow the optin ID to be filtered before it is stored and used to create the optin output.
		$identifier = apply_filters( 'optin_monster_api_pre_optin_id', $identifier, $atts, $post );

		// If there is no identifier, do nothing.
		if ( empty( $identifier ) ) {
			return false;
		}

		$optin = ctype_digit( (string) $identifier )
			? $this->base->get_optin( absint( $identifier ) )
			: $this->base->get_optin_by_slug( sanitize_text_field( $identifier ) );

		// If there is no identifier, do nothing.
		if ( empty( $optin ) ) {
			return false;
		}

		// Try to grab the stored HTML.
		$html = $this->base->output->prepare_campaign( $optin );
		if ( ! $html ) {
			return false;
		}

		if (
			wp_validate_boolean( $atts['followrules'] )
			// Do OMAPI Output rules check.
			&& ! OMAPI_Rules::check_shortcode( $optin, $post->ID )
		) {
			return false;
		}

		// Make sure to apply shortcode filtering.
		$this->base->output->set_slug( $optin );

		// Return the HTML.
		return $html;
	}

	/**
	 * Backwards compat shortcode for v1.
	 *
	 * @since 1.0.0
	 *
	 * @global object $post The current post object.
	 *
	 * @param array $atts Array of shortcode attributes.
	 * @return string     The optin output.
	 */
	public function shortcode_v1( $atts ) {

		// Run the v2 implementation.
		if ( ! empty( $atts['id'] ) ) {
			$atts['slug'] = $atts['id'];
			unset( $atts['id'] );
		}

		return $this->shortcode( $atts );
	}

}
