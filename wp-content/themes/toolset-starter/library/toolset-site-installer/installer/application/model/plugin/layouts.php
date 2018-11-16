<?php


class TT_Plugin_Layouts extends TT_Plugin {
	protected function fetchStatus( $plugins = false ) {
		if( ! function_exists( 'get_plugins' ) ) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$plugins = get_plugins();

		if( defined( 'WPDDL_VERSION' ) && defined( 'WPDDL_ABSPATH' ) ) {
			// Layouts is already active
			$entry_point = basename( WPDDL_ABSPATH ) . '/dd-layouts.php';

			if( array_key_exists( $entry_point, $plugins ) ) {
				// layouts active
				$this->entry_point = $entry_point;
				return self::STATUS_ACTIVE;
			}
		}

		if( ! defined( 'WP_PLUGIN_DIR' ) ) {
			// end here if we don't have this WordPress constant
			return self::STATUS_NOT_INSTALLED;
		}

		foreach( $plugins as $entry_point => $plugin_data ) {
			// check if any plugin is using 'dd-layouts.php'
			if( strpos( $entry_point, 'dd-layouts.php' ) !== false
			    && strpos( $plugin_data['Name'], '(for') == false ) {
				// could possible be Layouts, let's check for wpml-config.xml
				if( ! file_exists( WP_PLUGIN_DIR . '/' . str_replace( '/dd-layouts.php', '', $entry_point ) . '/wpml-config.xml' ) ) {
					// wpml-config.xml not found = no Layouts
					return self::STATUS_NOT_INSTALLED;
				}

				// we found Layouts inactive under a custom named folder
				$this->entry_point = $entry_point;
				return self::STATUS_INSTALLED;
			}
		}

		// return parent::fetchStatus (checks for theme locked versions)
		return parent::fetchStatus( $plugins );
	}
}