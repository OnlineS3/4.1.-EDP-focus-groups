<?php

class TT_Plugin
{
	const STATUS_ACTIVE         = 'active';
	const STATUS_INSTALLED      = 'installed';
	const STATUS_NOT_INSTALLED  = 'not-installed';

	private $id;
	private $name;
	private $src;
	private $update_url;
	protected $entry_point;
	private $full_plugin;
	protected $required = false;
	protected $extension = false;


	private $status;

	public function __construct($arrayPlugin)
	{
		if (array_key_exists('name', $arrayPlugin)) {
			$this->name = $arrayPlugin['name'];
		}

		if (array_key_exists('id', $arrayPlugin)) {
			$this->id = $arrayPlugin['id'];
		}

		if (array_key_exists('src', $arrayPlugin)) {
			$this->src = $arrayPlugin['src'];
		}

		if (array_key_exists('update', $arrayPlugin)) {
			$this->update_url = $arrayPlugin['update'];
		}

		if (array_key_exists('entry_point', $arrayPlugin)) {
			$this->entry_point = $arrayPlugin['entry_point'];
		}

		if (array_key_exists('full_plugin', $arrayPlugin)) {
			$this->full_plugin = $arrayPlugin['full_plugin'];
		}

		if (array_key_exists('required', $arrayPlugin)) {
			$this->required = $arrayPlugin['required'] ? true : false;
		}

		if (array_key_exists('extension', $arrayPlugin)) {
			$this->extension = $arrayPlugin['extension'] ? true : false;
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setSrc( $src )
	{
		$this->src = $src;
	}

	public function getSrc()
	{
		return $this->src;
	}

	public function getUpdateUrl()
	{
		return $this->update_url;
	}

	public function getEntryPoint()
	{
		return $this->entry_point;
	}

	public function isRequired()
	{
		return $this->required;
	}

	public function getStatus()
	{
		if ($this->status === null) {
			$this->status = $this->fetchStatus();
		}

		return $this->status;
	}

	public function isExtension()
	{
		return $this->extension;
	}

	public function isValid()
	{
		if ($this->name === null
		    || $this->id === null
		    || ! $this->validateEntryPoint()
		) {
			return false;
		}

		return true;
	}

	// todo validate src...
	private function validateSrc()
	{
		if ($this->src === null) {
			return false;
		}

		return true;
	}

	// todo validate entry point
	private function validateEntryPoint()
	{
		if ($this->entry_point === null) {
			return false;
		}

		return true;
	}

	protected function fetchStatus( $plugins = false )
	{
		if( ! $plugins ) {
			if( ! function_exists( 'get_plugins' ) ) {
				require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}

			$plugins = get_plugins();
		}

		// if full plugin exists (e.g. full views) we will use that
		if ($this->full_plugin !== null
		    && array_key_exists($this->full_plugin, $plugins)
		) {
			$this->entry_point = $this->full_plugin;
		}

		if (array_key_exists($this->getEntryPoint(), $plugins)) {
			if (is_plugin_active($this->getEntryPoint())) {
				return self::STATUS_ACTIVE;
			}

			return self::STATUS_INSTALLED;
		}

		return self::STATUS_NOT_INSTALLED;
	}

	public function install($upgrader_skin = false)
	{
		// no install without being valid
		if (! $this->isValid()) {
			return false;
		}

		// make sure we have the last status
		$this->status = $this->fetchStatus();

		// abort if already installed / active
		if ($this->getStatus() != self::STATUS_NOT_INSTALLED) {
			return true;
		}

		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$installer = ( $upgrader_skin instanceof WP_Upgrader_Skin )
			? new Plugin_Upgrader($upgrader_skin)
			: new Plugin_Upgrader();

		$installation = $installer->install($this->getSrc());

		if (is_wp_error($installation)) {
			return $installation;
		}

		$this->entry_point = $installer->plugin_info();
		return true;
	}

	public function activate()
	{
		if ($this->getStatus() == self::STATUS_ACTIVE) {
			return true;
		}

		$status = activate_plugin($this->entry_point);

		if (! is_wp_error($status)) {
			return true;
		}

		// another round (we have some third party plugins which produces output and that will throw an wp_error, but
		// on a second activation it's just fine, as the output is produced on the activation hook)
		$status = activate_plugin( $this->entry_point );

		if (! is_wp_error($status)) {
			return true;
		}

		return $status;
	}
}
