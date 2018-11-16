<?php


class TT_Context_Plugin extends TT_Context_Abstract {
	const ID = 'plugin';

	public function getAssetsUrl() {
		$plugin_url = rtrim( plugin_dir_url( __FILE__ ), '/' );

		return $plugin_url . '/../../..';
	}

	public function isUpdate() {
		return false;
	}

	public function isStepWelcomeActive() {
		return false;
	}

	public function strInstallerTitle() {
		return sprintf( __( 'Toolset Setup for %s', 'toolset-themes' ), wp_get_theme()->get( 'Name' ) );
	}

	public function tplStepDemoImport( $default ) {
		return TT_INSTALLER_DIR . '/application/view/plugin/installer/demo-import.phtml';
	}

	public function showNoticeRunInstaller(){
		// the plugin handles the notice
		return false;
	}
}