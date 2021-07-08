# Pluginbazar SDK

This tool will allow your plugin development more awesome.

## FEATURES

Here is a list of features of this SDK.

### 1. INSIGHTS

This SDK will send some non-confidential data from users website to server. This way, product author will get more understanding about their users. The sending part will happen upon getting permission from the users.

_This feature is still under development and doesn't added yet to the repository!_

### 2. NOTIFICATIONS

This SDK will receive notifications and display as admin notices from server. The notifications can be sent based on **version number** and/or **specific product**.

### 3. ONE-CLICK-DOWNLOAD

This SDK will allow users to download the latest version without leaving WordPress interface. Once they activate the plugin with their license key, then they will able to receive new version warning as like other plugins do.

### 4. LICENSE MANAGEMENT

The SDK will add a custom license page, from where they can **activate** or **deactivate** the plugin.

## USAGES

You can use the following code to use the sdk in your project.

```
function pb_sdk_init_woc_open_close_pro() {

	if ( ! class_exists( 'Pluginbazar\Client' ) ) {
		require_once( plugin_dir_path( __FILE__ ) . 'includes/sdk/class-client.php' );
	}

	global $wooopenclose_pro_sdk;

	$wooopenclose_pro_sdk = new Pluginbazar\Client( esc_html( 'WooCommerce Open Close - Pro' ), 'woc-open-close-pro', 15, '1.2.5' );
	$wooopenclose_pro_sdk->license()->add_settings_page( array( 'parent_slug' => 'edit.php?post_type=woc_hour' ) );
	$wooopenclose_pro_sdk->updater();
}

/**
 * @global \Pluginbazar\Client $wooopenclose_pro_sdk
 */
global $wooopenclose_pro_sdk;

pb_sdk_init_woc_open_close_pro();
```

To check the license status, you can use the following code snippet.

```
global $wooopenclose_pro_sdk;


if ( $wooopenclose_pro_sdk->license()->is_valid() ) {
	// License key is activated and valid
}
```

## SUPPORT

For any issues, please create an issue here or email us at support@pluginbazar.com