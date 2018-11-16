<?php


class TT_Updater_Plugin
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $latest_version;

	/**
	 * The entry point is the plugin folder name plus plugin root file.
	 * e.g. types/wpcf.php
	 *
	 * @var string
	 */
	private $entry_point;

	/**
	 * URL to update the plugin
	 * @var string
	 */
	private $url_update;


	/**
	 * URL to get the latest version
	 */
	private $url_latest_version;

	/**
	 * @var TT_Settings_Interface
	 */
	private $settings;

	/**
	 * Register hooks on construct
	 *
	 * @param TT_Settings_Interface $settings
	 * @param $plugin_id
	 */
	public function __construct( TT_Settings_Interface $settings, $plugin_id )
	{
		$this->settings = $settings;
		$this->id = $plugin_id;

		add_filter('pre_set_site_transient_update_plugins', array( $this, '_filterTransientUpdatePlugin' ));
		add_filter('plugins_api_result', array( $this, '_filterPluginDetailsDialog' ), 10, 3);
		add_filter('plugin_row_meta', array( $this, '_filterListRowMeta' ), 100, 2);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @param string $version
	 */
	public function setVersion($version)
	{
		$this->version = $version;
	}

	/**
	 * @return string
	 */
	public function getLatestVersion()
	{
		if ($this->latest_version === null) {
			$this->latest_version = $this->fetchLatestVersion();
		}

		return $this->latest_version;
	}

	/**
	 * Connect to repository to fetch latest version of plugin
	 * @return bool
	 */
	private function fetchLatestVersion()
	{
		$request = wp_remote_post($this->getUrlLatestVersion(), array(
			'body' => array(
				'information' => 'version'
			),
			'sslverify' => false,
		));

		if (is_wp_error($request)
		    || wp_remote_retrieve_response_code($request) !== 200
		) {
			return false;
		}

		return $request['body'];
	}

	/**
	 * @return string
	 */
	public function getEntryPoint()
	{
		return $this->entry_point;
	}

	/**
	 * @param string $entry_point
	 */
	public function setEntryPoint($entry_point)
	{
		$this->entry_point = $entry_point;
	}

	/**
	 * @return string
	 */
	public function getUrlLatestVersion()
	{
		return $this->url_latest_version;
	}

	/**
	 * @param string $url_latest_version
	 */
	public function setUrlLatestVersion($url_latest_version)
	{
		$this->url_latest_version = $url_latest_version;
	}

	/**
	 * @return string
	 */
	public function getUrlUpdate()
	{
		if( $this->url_update === null ) {
			$this->setUrlUpdate( $this->settings->getRepository()->getPluginSrc( $this->id ) );
		}

		return $this->url_update;
	}

	/**
	 * @param string $url_update
	 */
	public function setUrlUpdate($url_update)
	{
		$this->url_update = $url_update;
	}

	/**
	 * @return string
	 */
	public function getSlug()
	{
		return pathinfo($this->getEntryPoint(), PATHINFO_DIRNAME);
	}


	/**
	 * Check for plugin update
	 *
	 * @param $transient
	 *
	 * @return mixed
	 */
	public function _filterTransientUpdatePlugin($transient)
	{

		if (! $this->getLatestVersion()) {
			return $transient;
		}

		if (version_compare($this->getVersion(), $this->getLatestVersion(), '<')) {
			$plugin                 = new stdClass();
			$plugin->slug           = $this->getSlug();
			$plugin->new_version    = $this->getLatestVersion();
			$plugin->url            = $this->getUrlUpdate();
			$plugin->package        = $this->getUrlUpdate();

			$transient->response[$this->getEntryPoint()] = $plugin;
		}

		return $transient;
	}

	/**
	 * Content for plugin details dialog (only visible if update is available)
	 *
	 * @param $response
	 * @param $action
	 * @param $arg
	 *
	 * @return stdClass
	 */
	public function _filterPluginDetailsDialog($response, $action, $arg)
	{
		if ( property_exists( $arg, 'slug' ) && $arg->slug === $this->getSlug() ) {
			$plugin_details = new stdClass();
			$plugin_details->name = $this->getName();
			$plugin_details->slug = $this->getSlug();
			$plugin_details->download_link = $this->getUrlUpdate();
			$plugin_details->external = true;
			// $obj->last_updated = '2016-09-19';
			// $obj->sections = array( 'changelog' => '' );

			return $plugin_details;
		}

		return $response;
	}

	/**
	 * If plugin update is available, the "View details" link is back. This removes it.
	 *
	 * @param $plugin_meta
	 * @param $plugin_entry_point
	 *
	 * @return mixed
	 */
	public function _filterListRowMeta($plugin_meta, $plugin_entry_point)
	{
		if ($plugin_entry_point == $this->getEntryPoint()) {
			foreach ($plugin_meta as $key => $value) {
				if (strpos($value, 'open-plugin-details-modal') !== false) {
					unset($plugin_meta[ $key ]);
					break;
				}
			}
		}
		return $plugin_meta;
	}
}
