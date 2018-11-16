<?php


class TT_Context_Theme extends TT_Context_Abstract {
	const ID   = 'theme';

	protected $assetsUrl;

	/**
	 * @param mixed $assetsUrl
	 */
	public function setAssetsUrl( $assetsUrl ) {
		$this->assetsUrl = $assetsUrl;
	}

	public function redirections() {
		// redirect to full screen installer on theme activation
		$this->redirectOnThemeActivation();

		// redirect to full screen installer on theme update
		$this->redirectOnThemeUpdate();
	}


	/**
	 * First time the theme gets activated the installer will automatically run
	 */
	private function redirectOnThemeActivation()
	{
		global $pagenow;

		// redirect, if...
		if ('themes.php' == $pagenow                                             // on themes overview page
		    && isset($_GET['activated'])                                         // theme gets activated
		    && ! $this->settings->getProtocol()->getFirstInstalledThemeVersion() // installer did not run before
		) {
			// time to store the first installed theme version
			$this->settings->getProtocol()->setFirstInstalledThemeVersion();
			wp_redirect(admin_url('index.php?page=' . TT_Controller_Site_Installer::PAGE));
		}
	}

	/**
	 * Run theme installer on theme update
	 */
	private function redirectOnThemeUpdate()
	{
		if ( $this->settings->getProtocol()->getLastInstalledThemeVersion() != TT_THEME_VERSION
		     && ( ! array_key_exists( 'page', $_GET ) || $_GET['page'] != TT_Controller_Site_Installer::PAGE )
		) {
			// redirect
			wp_redirect(admin_url('index.php?page=' . TT_Controller_Site_Installer::PAGE));
		}
	}

	/**
	 * On Installer Start
	 */
	public function onInstallerStart() {
		// set last installed theme version
		$this->settings->getProtocol()->setLastInstalledThemeVersion();
	}

	/**
	 * @return string
	 */
	public function getAssetsUrl() {
		if( $this->assetsUrl == null ) {
			// if no url set yet use path of Toolset-based themes.
			$this->assetsUrl = is_dir( get_stylesheet_directory() . '/library/toolset/installer' )
				? get_stylesheet_directory_uri() . '/library/toolset/installer'
				: get_template_directory_uri() . '/library/toolset/installer';
		}

		return $this->assetsUrl;
	}

	public function isUpdate() {
		return $this->settings->getProtocol()->isThemeUpdate();
	}

	public function strInstallerTitle() {
		return sprintf( __('%s Installer', 'toolset-themes'), wp_get_theme()->get('Name') );
	}
}