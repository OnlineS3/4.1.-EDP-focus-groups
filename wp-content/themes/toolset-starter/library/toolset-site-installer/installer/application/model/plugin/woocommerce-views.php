<?php


class TT_Plugin_WooCommerce_Views extends TT_Plugin {


	public function __construct( $arrayPlugin, $settingsPlugins = array() ) {
		parent::__construct( $arrayPlugin );

		if( isset( $settingsPlugins['views']['required'] )
		    &&  $settingsPlugins['views']['required'] == 1
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

		if( defined( 'WC_VIEWS_VERSION' ) && defined( 'WOOCOMMERCE_VIEWS_PLUGIN_PATH' ) ) {
			$entry_point = basename( WOOCOMMERCE_VIEWS_PLUGIN_PATH ) . '/views-woocommerce.php';

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
			// check if any plugin is using 'views-woocommerce.php'
			if( strpos( $entry_point, 'views-woocommerce.php' ) !== false ) {
				// could possible be WooCommerceViews, let's check for Class_WooCommerce_Views.php
				if( file_exists( WP_PLUGIN_DIR . '/' . str_replace( '/views-woocommerce.php', '', $entry_point ) . '/Class_WooCommerce_Views.php' ) ) {
					// we found WooCommerceViews inactive under a custom named folder
					$this->entry_point = $entry_point;
					return self::STATUS_INSTALLED;
				}
			}
		}

		// not installed
		return self::STATUS_NOT_INSTALLED;
	}
}