<?php


class TT_Plugin_Cred_Commerce extends TT_Plugin {


	public function __construct( $arrayPlugin, $settingsPlugins = array() ) {
		parent::__construct( $arrayPlugin );

		if( isset( $settingsPlugins['cred']['required'] )
		    &&  $settingsPlugins['cred']['required'] == 1
		    && class_exists( 'WooCommerce' ) ) {
			// views is required and WooCommerce is active
			$this->required = true;
			$this->extension = false;
		}
	}

	protected function fetchStatus( $plugins = false ) {
		if( ! function_exists( 'get_plugins' ) ) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$plugins = get_plugins();

		if( defined( 'CRED_COMMERCE_PLUGIN_PATH' ) ) {
			$entry_point = basename( CRED_COMMERCE_PLUGIN_PATH ) . '/plugin.php';

			if( array_key_exists( $entry_point, $plugins ) ) {
				$this->entry_point = $entry_point;
				return self::STATUS_ACTIVE;
			}
		}

		if( ! defined( 'WP_PLUGIN_DIR' ) ) {
			// end here if we don't have this WordPress constant
			return self::STATUS_NOT_INSTALLED;
		}

		foreach( $plugins as $entry_point => $plugin_data ) {
			// check if any plugin is using 'plugin.php' (yes, cred commerce is using "plugin.php" as entry file)
			if( strpos( $entry_point, 'plugin.php' ) !== false ) {
				// could possible be CRED Commerce, let's check also for /classes/CRED_Commerce.php
				if( file_exists( WP_PLUGIN_DIR . '/' . str_replace( '/plugin.php', '', $entry_point ) . '/classes/CRED_Commerce.php' ) ) {
					// we found CRED Commerce inactive under a custom named folder
					$this->entry_point = $entry_point;
					return self::STATUS_INSTALLED;
				}
			}
		}

		// not installed
		return self::STATUS_NOT_INSTALLED;
	}
}