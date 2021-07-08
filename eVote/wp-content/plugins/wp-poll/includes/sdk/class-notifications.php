<?php
/**
 * Pluginbazar SDK Client
 */

namespace Pluginbazar;

/**
 * Class Notifications
 *
 * @package Pluginbazar
 */
class Notifications {

	protected $cache_key;

	/**
	 * @var Client null
	 */
	protected $client = null;

	/**
	 * Notifications constructor.
	 */
	function __construct( Client $client ) {

		$this->client    = $client;
		$this->cache_key = sprintf( '_%s_notifications_data', md5( $this->client->text_domain ) );

		add_action( 'init', array( $this, 'force_check_notifications' ) );
		add_action( 'admin_notices', array( $this, 'render_admin_notices' ) );
	}


	/**
	 * Render notification as notices
	 */
	function render_admin_notices() {

		$data = $this->get_notification_data();
		$this->client->print_notice( $this->get_message( $data ), 'info', false, $this->get_id( $data ) );
	}


	/**
	 * Force check notifications
	 */
	function force_check_notifications() {
		if ( Client::get_args_option( 'pb-force-check', wp_unslash( $_GET ) ) === 'yes' ) {
			$this->set_cached_notification_data( $this->get_latest_notification_data() );
		}
	}


	/**
	 * Return notification content
	 *
	 * @return mixed|string
	 */
	private function get_message( $data ) {
		return Client::get_parsed_string( Client::get_args_option( 'message', $data ) );
	}


	/**
	 * Return notification unique ID
	 *
	 * @return array|mixed|string
	 */
	private function get_id( $data ) {
		return Client::get_args_option( 'id', $data );
	}


	/**
	 * Get version information
	 */
	private function get_notification_data() {

		$notification_data = $this->get_cached_notification_data();

		if ( false === $notification_data ) {
			$notification_data = $this->get_latest_notification_data();
			$this->set_cached_notification_data( $notification_data );
		}

		if (
			( isset( $notification_data['version'] ) && empty( $notification_data['version'] ) ) ||
			( isset( $notification_data['version'] ) && version_compare( $this->client->plugin_version, $notification_data['version'], '=' ) )
		) {
			return $notification_data;
		}

		return array();
	}


	/**
	 * Get new data from server
	 *
	 * @return false|mixed
	 */
	private function get_latest_notification_data() {

		if ( ! is_wp_error( $data = $this->client->send_request( 'notifications/' . $this->client->text_domain ) ) ) {
			return $data;
		}

		return false;
	}


	/**
	 * Set cached data
	 *
	 * @param $value
	 */
	private function set_cached_notification_data( $value ) {
		if ( $value ) {
			// check notifications in every 5 days
			set_transient( $this->cache_key, $value, 5 * 24 * HOUR_IN_SECONDS );
		}
	}


	/**
	 * Get cached data
	 *
	 * @return mixed
	 */
	private function get_cached_notification_data() {
		return get_transient( $this->cache_key );
	}
}