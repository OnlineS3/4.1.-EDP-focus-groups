<?php


class TT_Plugin_Maps extends TT_Plugin {
	protected function fetchStatus( $plugins = false ) {
		if( ! function_exists( 'get_plugins' ) ) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$plugins = get_plugins();


		if( defined( 'TOOLSET_ADDON_MAPS_VERSION' ) && defined( 'TOOLSET_ADDON_MAPS_PATH' ) ) {
			// Toolset Maps is already active
			$entry_point = basename( TOOLSET_ADDON_MAPS_PATH ) . '/toolset-maps-loader.php';

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
			// check if any plugin is using 'toolset-maps-loader.php'
			if( strpos( $entry_point, 'toolset-maps-loader.php' ) !== false
			    && strpos( $plugin_data['Name'], '(for') == false ) {
				// we found Toolset Maps inactive under a custom named folder
				$this->entry_point = $entry_point;
				return self::STATUS_INSTALLED;
			}
		}

		// return parent::fetchStatus (checks for theme locked versions)
		return parent::fetchStatus( $plugins );
	}
}