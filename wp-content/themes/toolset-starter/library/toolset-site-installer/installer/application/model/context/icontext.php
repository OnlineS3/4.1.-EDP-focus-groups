<?php

interface TT_Context_Interface {
	public function redirections();
	public function setSettings( TT_Settings_Interface $settings );
	public function onInstallerStart();
	public function getAssetsUrl();
	public function isUpdate();
	public function isStepWelcomeActive();

	public function strInstallerTitle();
	public function tplStepDemoImport( $default );

	public function showNoticeRunInstaller();
}