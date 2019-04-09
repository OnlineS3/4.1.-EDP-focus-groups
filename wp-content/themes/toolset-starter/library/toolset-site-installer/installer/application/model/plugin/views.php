<?php


class TT_Plugin_Views extends TT_Plugin {
	protected function fetchStatus( $plugins = false ) {
		if( ! function_exists( 'get_plugins' ) ) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$plugins = get_plugins();

		// views is already active
		if( defined( 'WPV_VERSION' ) && defined( 'WPV_PATH' ) ) {
			$entry_point = basename( WPV_PATH ) . '/wp-views.php';

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
			// check if any plugin is using 'wp-views.php'
			if( strpos( $entry_point, 'wp-views.php' ) !== false
			    && strpos( $plugin_data['Name'], '(for') == false ) {
				// could possible be Views, let's check for wpml-config.xml
				if( file_exists( WP_PLUGIN_DIR . '/' . str_replace( '/wp-views.php', '', $entry_point ) . '/wpml-config.xml' ) ) {
					// we found Views inactive under a custom named folder
					$this->entry_point = $entry_point;
					return self::STATUS_INSTALLED;
				}
			}
		}

		// return parent::fetchStatus (checks for theme locked versions)
		return parent::fetchStatus( $plugins );
	}
}