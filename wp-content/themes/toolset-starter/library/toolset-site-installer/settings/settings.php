<?php


class TT_Settings implements TT_Settings_Interface
{
    /**
     * @var TT_Settings_Protocol_Interface
     */
    private $protocol;

    /**
     * @var TT_Settings_Files_Interface
     */
    private $files;

	/**
	 * @var TT_Context_Interface
	 */
	private $context;

	/**
	 * @var TT_Repository_Interface
	 */
	private $repository;

    /**
     * @var array
     */
    private $settings;

    /**
     * All plugins available in the settings file
     *
     * @var TT_Plugin[]
     */
    private $plugins = array();

    /**
     * All plugins available in the settings file, which are also active
     *
     * @var TT_Plugin[]
     */
    private $plugins_active;

    /**
     * Pass the path to the settings file
     *
     * @param $json_settings_file
     * @param TT_Settings_Protocol_Interface $memory
     *
     * @param TT_Settings_Files_Interface $files
     *
     * @throws Exception if file does not exists
     */
    public function __construct(
        $json_settings_file,
        TT_Settings_Protocol_Interface $memory,
        TT_Settings_Files_Interface $files
    ) {
        if (! file_exists($json_settings_file)) {
            throw new Exception('Settings file could not be found.');
        }

        $json = file_get_contents($json_settings_file);
        $this->settings = json_decode($json, true);

        $this->fetchPlugins();

        $this->protocol = $memory;
        $this->files    = $files;
        $this->files->setSettings( $this );
    }

    /**
     * @return TT_Settings_Protocol_Interface
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return TT_Settings_Files_Interface
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Load all plugins of settings file
     */
    private function fetchPlugins()
    {
        if (! isset($this->settings['plugins'])) {
            return;
        }

        foreach ($this->settings['plugins'] as $idPlugin => $arrayPlugin) {
            if (! array_key_exists('id', $arrayPlugin)) {
                $arrayPlugin['id'] = $idPlugin;
            }

	        switch( $idPlugin ) {
		        case 'layouts':
			        $objPlugin = new TT_Plugin_Layouts( $arrayPlugin );
			        break;
		        case 'views':
			        $objPlugin = new TT_Plugin_Views( $arrayPlugin );
			        break;
		        case 'types':
			        $objPlugin = new TT_Plugin_Types( $arrayPlugin );
			        break;
		        case 'cred':
			        $objPlugin = new TT_Plugin_Cred( $arrayPlugin );
			        break;
		        case 'access':
			        $objPlugin = new TT_Plugin_Access( $arrayPlugin );
			        break;
		        case 'maps':
			        $objPlugin = new TT_Plugin_Maps( $arrayPlugin );
			        break;
		        case 'woocommerce-views':
			        $objPlugin = new TT_Plugin_WooCommerce_Views( $arrayPlugin, $this->settings['plugins'] );
			        break;
		        case 'cred-commerce':
			        $objPlugin = new TT_Plugin_Cred_Commerce( $arrayPlugin, $this->settings['plugins'] );
			        break;
		        default:
			        $objPlugin = new TT_Plugin( $arrayPlugin );
			        break;
	        }

            if ($objPlugin->isValid()) {
                $this->plugins[$objPlugin->getId()] = $objPlugin;
            }
        }
    }

    /**
     * Get Plugin by Slug
     *
     * @param $slug
     *
     * @return false|TT_Plugin
     */
    public function getPlugin($slug)
    {
        if (! isset($this->plugins[$slug])) {
            return false;
        }

        return $this->plugins[$slug];
    }

    /**
     * Get all plugins
     *
     * @return false|TT_Plugin[]
     */
    public function getPlugins()
    {
        if (empty($this->plugins)) {
            return false;
        }

        return $this->plugins;
    }

    /**
     * Get active Plugin by Slug
     *
     * @param $slug
     *
     * @return false|TT_Plugin
     */
    public function getActivePlugin($slug)
    {
    	$plugins_active = $this->getActivePlugins();
        if ( ! $plugins_active || ! isset( $plugins_active[$slug] ) ) {
            return false;
        }

        return $plugins_active[$slug];
    }

    /**
     * Get all active plugins
     *
     * @return false|TT_Plugin[]
     */
    public function getActivePlugins()
    {
        // Caching: return result if this process already run before
        if ($this->plugins_active !== null) {
            return $this->plugins_active;
        }

        // no plugins available
        if (! $this->getPlugins()) {
            return false;
        }

        $this->plugins_active = array();

        // go through all plugins and search for active
        foreach ($this->getPlugins() as $plugin_slug => $plugin) {
            if ($plugin->getStatus() === TT_Plugin::STATUS_ACTIVE) {
                $this->plugins_active[$plugin_slug] = $plugin;
            }
        }

        // nothing found, set plugins_active to false to avoid running this process again
        if (empty($this->plugins_active)) {
            $this->plugins_active = false;
        }

        // return result
        return $this->plugins_active;
    }

    public function getThemeUpdateUrl()
    {
        if (! isset($this->settings['theme']) || ! isset($this->settings['theme']['update'])) {
            return false;
        }

        return $this->settings['theme']['update'];
    }

	public function getAuthorBaseUrl()
	{
		if (! array_key_exists( 'author_base_url', $this->settings ) ) {
			return false;
		}

		return $this->settings['author_base_url'];
	}

	public function getRepository() {
		return $this->repository;
	}

	public function setRepository( TT_Repository_Interface $repository ) {
    	$repository->setSettings( $this );
		$this->repository = $repository;
		return $this;
	}

	public function getContext() {
		return $this->context;
	}

	public function setContext( TT_Context_Interface $context ) {
    	$context->setSettings( $this );
		$this->context = $context;
		return $this;
	}
}
