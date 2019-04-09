<?php


class TT_Plugin_Access extends TT_Plugin {
	protected function fetchStatus( $plugins = false ) {
		if( ! function_exists( 'get_plugins' ) ) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$plugins = get_plugins();

		if( defined( 'TACCESS_VERSION' ) && defined( 'TACCESS_PLUGIN_PATH' ) ) {
			// Access is already active
			$entry_point = basename( TACCESS_PLUGIN_PATH ) . '/types-access.php';

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
			// check if any plugin is using 'types-access.php'
			if( strpos( $entry_point, 'types-access.php' ) !== false
			    && strpos( $plugin_data['Name'], '(for') == false ) {
				// could possible be Access, let's check for "toolset" dir
				if( file_exists( WP_PLUGIN_DIR . '/' . str_replace( '/types-access.php', '', $entry_point ) . '/toolset' ) ) {
					// we found Access inactive under a custom named folder
					$this->entry_point = $entry_point;
					return self::STATUS_INSTALLED;
				}
			}
		}

		// return parent::fetchStatus (checks for theme locked versions)
		return parent::fetchStatus( $plugins );
	}
}