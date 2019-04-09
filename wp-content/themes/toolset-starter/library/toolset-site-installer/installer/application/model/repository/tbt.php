<?php

/**
 * Class TT_Repository_OTGS_Handler
 * This is a lightweight clone of our OTGS Installer (WP_Installer())
 */
class TT_Repository_TBT extends TT_Repository_Abstract {

	/**
	 * @param $plugin_slug
	 *
	 * @return bool|mixed
	 */
	public function getPluginSrc( $plugin_slug ) {
		// use settings src
		$plugin = $this->getSettings()->getPlugin( $plugin_slug );

		$request = wp_remote_post( $plugin->getSrc() . '/latest/1', array(
			'sslverify' => false,
		));

		if (is_wp_error($request)
			|| wp_remote_retrieve_response_code($request) !== 200
		) {
			return $plugin->getSrc();
		}

		if( filter_var( $request['body'], FILTER_VALIDATE_URL ) === false ) {
			return $plugin->getSrc();
		}

		return $request['body'];
	}

	/**
	 * Use TBT Production / Development / Testing mechanism
	 * @return bool
	 */
	public function useHostAllowedMechanism() {
		return true;
	}
}