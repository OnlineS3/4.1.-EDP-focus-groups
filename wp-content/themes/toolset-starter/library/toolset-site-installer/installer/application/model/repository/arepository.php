<?php


abstract class TT_Repository_Abstract implements TT_Repository_Interface {
	private $settings;

	/**
	 * @return TT_Settings_Interface
	 */
	protected function getSettings() {
		return $this->settings;
	}

	/**
	 * @param TT_Settings_Interface $settings
	 */
	public function setSettings( TT_Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * @return bool
	 */
	public function requireSiteKey() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function isSiteKeyValid( ) {
		if( ! $this->requireSiteKey() ) {
			return true;
		}

		return false;
	}

	/**
	 * use TBT Production / Development / Testing mechanism
	 * @return bool
	 */
	public function useHostAllowedMechanism() {
		return true;
	}
}