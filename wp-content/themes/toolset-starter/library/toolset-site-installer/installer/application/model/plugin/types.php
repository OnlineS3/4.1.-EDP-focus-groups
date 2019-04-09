<?php


class TT_Plugin_Types extends TT_Plugin {
	protected function fetchStatus( $plugins = false ) {
		if( ! function_exists( 'get_plugins' ) ) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$plugins = get_plugins();

		// views is already active
		if( defined( 'TYPES_VERSION' ) && defined( 'TYPES_ABSPATH' ) ) {
			$entry_point = basename( TYPES_ABSPATH ) . '/wpcf.php';

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
			// check if any plugin is using 'wpcf.php'
			if( strpos( $entry_point, 'wpcf.php' ) !== false ) {
				// could possible be Types, let's check for wpml-config.xml
				if( file_exists( WP_PLUGIN_DIR . '/' . str_replace( '/wpcf.php', '', $entry_point ) . '/wpml-config.xml' ) ) {
					// we found Types inactive under a custom named folder
					$this->entry_point = $entry_point;
					return self::STATUS_INSTALLED;
				}
			}
		}

		// not installed
		return self::STATUS_NOT_INSTALLED;
	}
}