<?php

/**
 * Class TT_Step_Plugin_Installation
 *
 * Handles the installation of all Toolset plugin and also
 * some selected third party plugins.
 *
 */
class TT_Step_Plugin_Installation extends TT_Step_Abstract {

	protected $slug = 'plugin-installation';

	protected $show_only_downloaded_plugins = false;

	/**
	 * @var bool
	 */
	private $active = false;

	public function __construct( TT_Settings_Interface $settings ) {
		parent::__construct( $settings );

		if ( $this->settings->getPlugins() ) {
			$this->active = true;
		}
	}

	/**
	 * Call to only show the already downloaded plugins
	 */
	public function showOnlyDownloadedPlugins() {
		$this->show_only_downloaded_plugins = true;
	}

	/**
	 * We can disable this step if all available plugins
	 * are already installed AND activated
	 */
	private function noPluginsToActivateOrInstall() {
		foreach ( $this->settings->getPlugins() as $plugin ) {
			// if only one plugin is not active we can end the loop
			if ( $plugin->getStatus() != TT_Plugin::STATUS_ACTIVE ) {
				return false;
			}
		}

		// NO plugins left to install/activate
		return true;
	}

	private function allRequiredPluginsActive() {
		foreach ( $this->settings->getPlugins() as $plugin ) {
			if ( $plugin->isRequired() && $plugin->getStatus() != TT_Plugin::STATUS_ACTIVE ) {
				return false;
			}
		}

		// all required plugins active
		return true;
	}

	public function getPlugins() {
		if( ! $this->show_only_downloaded_plugins ) {
			// all plugins
			return $this->settings->getPlugins();
		}

		$downloaded_plugins = array();

		foreach( $this->settings->getPlugins() as $plugin_slug => $plugin ) {
			if( $plugin->getStatus() !== TT_Plugin::STATUS_NOT_INSTALLED ) {
				$downloaded_plugins[$plugin_slug] = $plugin;
			}
		}
		
		return $downloaded_plugins;

	}

	public function isActive() {
		if ( $this->settings->getProtocol()->isStepActive( $this->slug ) ) {
			// active, if it was once active in this installation session
			return true;
		}

		$context = $this->settings->getContext();
		if ( ( $this->isUpdate() || $context::ID == 'plugin' ) && $this->allRequiredPluginsActive() ) {
			// not active, when all required plugins are active and we're doing a theme update
			return false;
		}

		$this->settings->getProtocol()->setStepActive( $this->slug );

		return true;
	}

	public function allRequiredPluginsDownloaded() {
		foreach ( $this->settings->getPlugins() as $plugin ) {
			if ( $plugin->isRequired() && $plugin->getStatus() == TT_Plugin::STATUS_NOT_INSTALLED ) {
				return false;
			}
		}

		// all required plugins are at least downloaded
		return true;
	}

	public function requirementsDone() {
		if ( ! $this->allRequiredPluginsActive() ) {
			return false;
		}

		$this->setFinished();

		return true;
	}
}
