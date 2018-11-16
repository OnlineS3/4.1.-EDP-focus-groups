<?php


class TT_Plugin_Cred extends TT_Plugin {
	protected function fetchStatus( $plugins = false ) {
		if( ! function_exists( 'get_plugins' ) ) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$plugins = get_plugins();

		if( defined( 'CRED_FE_VERSION' ) && defined( 'CRED_ROOT_PLUGIN_PATH' ) ) {
			// CRED is already active
			$entry_point = basename( CRED_ROOT_PLUGIN_PATH ) . '/plugin.php';

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
			// check if any plugin is using 'plugin.php'
			if( strpos( $entry_point, 'plugin.php' ) !== false
			    && strpos( $plugin_data['Name'], '(for') == false ) {
				// could possible be CRED, let's check for wpml-config.xml
				if( file_exists( WP_PLUGIN_DIR . '/' . str_replace( '/plugin.php', '', $entry_point ) . '/embedded/wpml-config.xml' ) ) {
					// we found CRED inactive under a custom named folder
					$this->entry_point = $entry_point;
					return self::STATUS_INSTALLED;
				}
			}
		}

		// return parent::fetchStatus (checks for theme locked versions)
		return parent::fetchStatus( $plugins );
	}
}