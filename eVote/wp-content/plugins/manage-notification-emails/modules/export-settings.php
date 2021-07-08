<?php
/**
 * Manage notification emails settings page class
 * *
 * This file is part of the Manage Notification Emails plugin
 * You can find out more about this plugin at https://www.freeamigos.nl
 * Copyright (c) 2006-2015  Virgial Berveling
 *
 * @package WordPress
 * @author Virgial Berveling
 * @copyright 2006-2020
 *
 * since: 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load module famne_custom_recipients
 *
 * @return void
 */
function load_mod_famne_export_settings() {
	FAMNE::AddModule(
		'export_settings',
		array(
			'name'      => 'Export and import settings',
			'version'   => '1.0.0',
			'option_id' => array( 'export_settings' ),
			'card'      => 'card_famne_export_settings',
		)
	);

	/**
	 * Card_famne_export_settings
	 *
	 * @return void
	 */
	function card_famne_export_settings() {
		global $pagenow;
		if ( ! FAMNESettingsPage::is_famne_settings_page() && ! FAMNESettingsPage::is_famne_network_settings_page() ) {
			return;
		}

		$downloadlink = $pagenow . '?' . 'page=' . sanitize_text_field( $_GET['page'] ) . '&export-settings=famne';

		?>
	<div class="card">
		<h2 class="title"><?php _e( 'Export and import settings', 'manage-notification-emails' ); ?></h2>
		<?php _e( 'Here you can export or import your Manage notification e-mails settings.' ); ?>
		<br/><br/><strong>Export your settings in JSON format</strong><br/>
		<a href="<?php echo $downloadlink; ?>" class="button" target="_blank" rel="noopener">Export settings file</a>
		<br/><br/><strong>Import setting in JSON format</strong><br/>
		<input type="file" id="settings-upload-file" name="settings-upload-file" accept="application/JSON" data-message="<?php _e( 'Do you want to overwrite your current settings?', 'manage-notification-emails' );?>"/>
		<input type="submit" class="button button-primary" id="settings-upload-submit" disabled="disabled" value="<?php _e( 'Start import', 'manage-notification-emails' );?>" />
		<div class="spacer"></div>
	</div>
		<?php
	}

	if ( ! empty( $_GET['export-settings'] ) && 'famne' === $_GET['export-settings'] ) :
		if ( ! headers_sent() ) {
			global $famne_options;

			header( 'Content-Disposition: attachment; filename="famne-settings-' . gmdate( 'Y-m-d-Hs' ) . '.json"' );
			header( 'Cache-Control: no-cache, must-revalidate' );
			header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
			if ( is_array( $famne_options ) ) {
				$famne_options['version'] = FA_MNE_VERSION;
			}
				echo wp_json_encode( $famne_options );
			die;
		} else {
			wp_die( 'Unexpected headers found.' );
		}
	endif;

	add_filter( 'famne_sanitize_settings_page', 'card_famne_export_settings_sanitize', 99, 3 );

	function card_famne_export_settings_sanitize( $input ) {

		if ( ! empty( $_FILES['settings-upload-file'] ) && isset( $_FILES['settings-upload-file']['size'] ) ) :
			$file = $_FILES['settings-upload-file'];

			if ( 4 === $file['error'] || 0 === $file['size'] ) :
				return $input;
			endif;

			// Get file data.


			// Check errors.
			if ( $file['error'] ) {
				wp_die( __( 'Error uploading file. Please try again.', 'manage-notification-emails' ) );
			}

			// Check file type.
			if ( pathinfo( $file['name'], PATHINFO_EXTENSION ) !== 'json' ) {
				wp_die( __( 'Incorrect file type.', 'manage-notification-emails' ) );
			}


			// Check file size.
			if ( $file['size'] > 100000 ) {
				wp_die( __( 'File size exceeds maximum upload limit.', 'manage-notification-emails' ) );
			}

			// Read JSON.
			try {
				$json = null;
				if ( function_exists( 'file_get_contents' ) ) :
					$json = file_get_contents( $file['tmp_name'] );
					$json = json_decode( $json, true );
				endif;
				// Check if empty.
				if ( ! $json || ! is_array( $json ) ) {
					wp_die( __( 'Import file empty.', 'manage-notification-emails' ) );
				}
			} catch ( RuntimeException $e ) {
				wp_die( 'Invalid parameters or file is corrupted.' );
			}

			// Check file size.
			if ( empty( $json['version'] ) || FA_MNE_VERSION !== $json['version'] ) {
				wp_die( __( 'The import file has a different plugin version.', 'manage-notification-emails' ) );
			}

			// Get all available options.
			$available_options = array();
			foreach ( FAMNE::default_options() as $key=>$o ) :
				$available_options[] = $key;
			endforeach;
			foreach ( FAMNE::getModules() as $mod ) :
				if ( ! empty( $mod->option_id ) && is_array( $mod->option_id ) ) :
					foreach ( $mod->option_id as $m ) :
							$available_options[] = $m;
						endforeach;
				endif;
			endforeach;

			$input = array();
			foreach ( $json as $key => $val ) :
				if ( in_array( $key, $available_options, true ) ) {
					$input[ $key ] = sanitize_text_field( $val );
				}
			endforeach;
		endif;

		return $input;
	}

}

add_action( 'fa_mne_modules', 'load_mod_famne_export_settings' );
