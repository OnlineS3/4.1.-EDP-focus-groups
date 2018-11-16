<?php


interface TT_Repository_Interface {
	/**
	 * @param TT_Settings_Interface $settings
	 */
	public function setSettings( TT_Settings_Interface $settings );

	/**
	 * @return bool
	 */
	public function requireSiteKey();

	/**
	 * @return bool
	 */
	public function isSiteKeyValid();

	/**
	 * @param $plugin_slug
	 *
	 * @return string
	 */
	public function getPluginSrc( $plugin_slug );

	/**
	 * @return bool
	 */
	public function useHostAllowedMechanism();
}