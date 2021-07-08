<?php
/**
 * Pluginbazar SDK Client
 *
 * @version 1.0.6
 * @author Pluginbazar
 */

namespace Pluginbazar;

/**
 * Class Client
 *
 * @package Pluginbazar
 */
class Client {

	public static $_integration_server = 'https://c.pluginbazar.com';
	public static $_notices_prefix = 'pb_notices_';

	public $plugin_name = null;
	public $text_domain = null;
	public $plugin_reference = null;
	public $plugin_version = null;


	/**
	 * @var \Pluginbazar\Notifications
	 */
	protected $license;


	/**
	 * @var \Pluginbazar\Notifications
	 */
	protected $notifications;


	/**
	 * @var \Pluginbazar\Updater
	 */
	protected $updater;


	/**
	 * Client constructor.
	 *
	 * @param $plugin_name
	 * @param $text_domain
	 * @param $plugin_reference
	 * @param $plugin_version
	 */
	function __construct( $plugin_name, $text_domain, $plugin_reference, $plugin_version ) {

		// Initialize variables
		$this->plugin_name      = $plugin_name;
		$this->text_domain      = $text_domain;
		$this->plugin_reference = $plugin_reference;
		$this->plugin_version   = $plugin_version;

		// Loading notifications
		$this->notifications();

		add_action( 'admin_init', array( $this, 'manage_permanent_dismissible' ) );
	}


	/**
	 * Return Updater class
	 *
	 * @return \Pluginbazar\Updater
	 */
	public function updater() {
		if ( ! class_exists( __NAMESPACE__ . '\Updater' ) ) {
			require_once __DIR__ . '/class-updater.php';
		}

		if ( ! $this->updater ) {
			$this->updater = new Updater( $this );
		}

		return $this->updater;
	}


	/**
	 * Return License class
	 *
	 * @return \Pluginbazar\License
	 */
	public function license() {
		if ( ! class_exists( __NAMESPACE__ . '\License' ) ) {
			require_once __DIR__ . '/class-license.php';
		}

		if ( ! $this->license ) {
			$this->license = new License( $this );
		}

		return $this->license;
	}


	/**
	 * Return Notifications class
	 *
	 * @return \Pluginbazar\Notifications
	 */
	public function notifications() {

		if ( ! class_exists( __NAMESPACE__ . '\Notifications' ) ) {
			require_once __DIR__ . '/class-notifications.php';
		}

		if ( ! $this->notifications ) {
			$this->notifications = new Notifications( $this );
		}

		return $this->notifications;
	}


	/**
	 * Manage permanent dismissible of any notice
	 */
	function manage_permanent_dismissible() {

		$query_args = wp_unslash( $_GET );

		if ( self::get_args_option( 'pb_action', $query_args ) == 'permanent_dismissible' && ! empty( $id = self::get_args_option( 'id', $query_args ) ) ) {

			// update value
			update_option( self::get_notices_id( $id ), time() );

			// Removing query args
			unset( $query_args['pb_action'] );
			unset( $query_args['id'] );

			$redirect = parse_url( esc_url_raw( add_query_arg( $query_args, site_url( $_SERVER['REQUEST_URI'] ) ) ) );

			// Redirect
			wp_safe_redirect( esc_url_raw( add_query_arg( $query_args, site_url( $redirect['path'] ) ) ) );
			exit;
		}
	}


	/**
	 * Send request to remote endpoint
	 *
	 * @param $route
	 * @param array $params
	 * @param false $is_post
	 * @param false $blocking
	 *
	 * @return array|mixed|\WP_Error
	 */
	public function send_request( $route, $params = array(), $is_post = false, $blocking = false ) {

		$url = trailingslashit( self::$_integration_server ) . 'wp-json/data/' . $route;

		if ( $is_post ) {
			$response = wp_remote_post( $url, array(
				'timeout'     => 30,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => $blocking,
				'headers'     => array(
					'user-agent' => 'Pluginbazar/' . md5( esc_url( site_url() ) ) . ';',
					'Accept'     => 'application/json',
				),
				'body'        => array_merge( $params, array( 'version' => $this->plugin_version ) ),
				'cookies'     => array(),
				'sslverify'   => false,
			) );
		} else {
			$response = wp_remote_get( $url, array( 'timeout' => 30, 'sslverify' => false ) );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}


	/**
	 * Print notices
	 *
	 * @param string $message
	 * @param string $type
	 * @param bool $is_dismissible
	 * @param bool $permanent_dismiss
	 */
	public function print_notice( $message = '', $type = 'success', $is_dismissible = true, $permanent_dismiss = false ) {

		if ( $permanent_dismiss && ! empty( get_option( self::get_notices_id( $permanent_dismiss ) ) ) ) {
			return;
		}

		$is_dismissible = $is_dismissible ? 'is-dismissible' : '';
		$pb_dismissible = '';

		// Manage permanent dismissible
		if ( $permanent_dismiss ) {
			$is_dismissible = 'pb-is-dismissible';
			$pb_dismissible = sprintf( '<a href="%s" class="notice-dismiss"><span class="screen-reader-text">%s</span></a>',
				esc_url_raw( add_query_arg( array( 'pb_action' => 'permanent_dismissible', 'id' => $permanent_dismiss ), site_url( $_SERVER['REQUEST_URI'] ) ) ),
				esc_html__( 'Dismiss', $this->text_domain )
			);
		}

		if ( ! empty( $message ) ) {
			printf( '<div class="notice notice-%s %s">%s%s</div>', $type, $is_dismissible, $message, $pb_dismissible );
			?>
            <style>
                .pb-is-dismissible {
                    position: relative;
                }

                .notice-dismiss, .notice-dismiss:active, .notice-dismiss:focus {
                    top: 50%;
                    transform: translateY(-50%);
                    text-decoration: none;
                    outline: none;
                    box-shadow: none;
                }
            </style>
			<?php
		}
	}


	/**
	 * Return Arguments Value
	 *
	 * @param string $key
	 * @param string $default
	 * @param array $args
	 *
	 * @return mixed|string
	 */
	public static function get_args_option( $key = '', $args = array(), $default = '' ) {

		$default = is_array( $default ) && empty( $default ) ? array() : $default;
		$default = ! is_array( $default ) && empty( $default ) ? '' : $default;
		$key     = empty( $key ) ? '' : $key;

		if ( isset( $args[ $key ] ) && ! empty( $args[ $key ] ) ) {
			return $args[ $key ];
		}

		return $default;
	}


	/**
	 * Return notices id with prefix
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public static function get_notices_id( $id ) {
		return self::$_notices_prefix . $id;
	}


	/**
	 * Parsed string
	 *
	 * @param $string
	 *
	 * @return mixed|string
	 */
	public static function get_parsed_string( $string ) {

		preg_match_all( '#\{(.*?)\}#', $string, $matches, PREG_SET_ORDER, 0 );

		foreach ( $matches as $match ) {

			$match_object = explode( '.', $match[1] );

			if ( isset( $match_object[0] ) ) {
				switch ( $match_object[0] ) {
					case 'user':
						global $current_user;
						$string = str_replace( $match[0], $current_user->{$match_object[1]}, $string );
						break;
				}
			}
		}

		return $string;
	}


	/**
	 * Return url of client website
	 *
	 * @return mixed|string
	 */
	public static function get_website_url() {

//			if ( is_multisite() ) {
//				return site_url();
//			}

		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			return $_SERVER['SERVER_NAME'];
		}

		return site_url();
	}


	/**
	 * Translate function _e()
	 */
	public function _etrans( $text ) {
		call_user_func( '_e', $text, $this->text_domain );
	}


	/**
	 * Translate function __()
	 */
	public function __trans( $text ) {
		return call_user_func( '__', $text, $this->text_domain );
	}


	/**
	 * Return Plugin Basename
	 *
	 * @return string
	 */
	public function basename() {
		return sprintf( '%1$s/%1$s.php', $this->text_domain );
	}
}