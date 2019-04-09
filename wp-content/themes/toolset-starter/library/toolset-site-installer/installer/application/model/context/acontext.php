<?php

abstract class TT_Context_Abstract implements TT_Context_Interface {
	/**
	 * @var TT_Settings_Interface
	 */
	protected $settings;

	/**
	 * TT_Context_Abstract constructor.
	 *
	 * @param TT_Settings_Interface $settings
	 */
	public function setSettings( TT_Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Redirections
	 * @return bool
	 */
	public function redirections() {
		// no redirections by default
		return;
	}

	/**
	 * Fires on installer start
	 */
	public function onInstallerStart() {
		return;
	}

	/**
	 * @return bool
	 */
	public function isStepWelcomeActive() {
		return true;
	}

	public function tplStepDemoImport( $default ) {
		return $default;
	}

	public function showNoticeRunInstaller() {
		return true;
	}
}