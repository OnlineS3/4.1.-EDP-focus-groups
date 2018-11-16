<?php
/**
 * Option 'toolset_theme_installer_for_' . TT_THEME_SLUG
 *
 * This is the one and only option for toolset-based theme
 * It contains
 *  'first-installed-version' => [first installed theme version no]
 *  'last-installed-version'  => [curr theme version no]
 *  'installation-steps' => array(
 *      'slug-of-step-1' => [curr theme version no]   // if step is finished
 *      'slug-of-step-2' => [curr theme version no]   // if step is finished
 *      ...
 *   ),
 *  'imports' => array(
 *      'types'     => [curr theme version no]   // if step is finished
 *      'wordpress' => [curr theme version no]   // if step is finished
 *      ...
 *   )
 */
class TT_Settings_Protocol implements TT_Settings_Protocol_Interface
{
    private $id;

    const KEY_FIRST_THEME_VER = 'first_installed_theme_version';
    const KEY_LAST_THEME_VER  = 'last_installed_theme_version';
    const KEY_STEPS           = 'installation_steps';
    const KEY_IMPORTS         = 'imports';
    const KEY_SITE_KEY        = 'site_key';
    const KEY_ACTIVE_STEPS    = 'active_steps';

    private $memory;

    public function __construct()
    {
        $this->id = 'toolset_theme_installer_for_' . TT_THEME_SLUG;
        $this->fetchOption();
    }

    /**
     * Fetch value of option and stores it in $this->option
     *
     * @return mixed
     */
    private function fetchOption()
    {
        if ($this->memory !== null) {
            return $this->memory;
        }

        $this->memory = get_option($this->id, '');
        $this->memory = json_decode($this->memory, true);
    }

    /**
     * Update option in database
     */
    private function store()
    {
        update_option($this->id, json_encode($this->memory));
    }

    /**
     * Store that a step is finished
     *
     * @param TT_Step_Abstract $step
     */
    public function setStepFinished(TT_Step_Abstract $step)
    {
        $this->memory[self::KEY_STEPS][$step->getSlug()] = TT_THEME_VERSION;
        $this->store();
    }

	/**
	 * Stores site_key
	 *
	 * @param string $site_key
	 */
	public function setSiteKey( $site_key )
	{
		$site_key = preg_replace("/[^A-Za-z0-9]/", '', $site_key);
		$this->memory[self::KEY_SITE_KEY] = $site_key;
		$this->store();
	}

	/**
	 * Returns site_key
	 *
	 * @return string|false
	 */
	public function getSiteKey()
	{
		if (! isset($this->memory[self::KEY_SITE_KEY])) {
			return false;
		}

		return $this->memory[self::KEY_SITE_KEY];
	}

	/**
	 * @param TT_Repository_Interface $repository
	 *
	 * @return bool
	 */
	public function isSiteKeyValid( TT_Repository_Interface $repository ) {
		if( ! $site_key = $this->getSiteKey() ) {
			// no site key set
			return false;
		}

		return $repository->isSiteKeyValid();
	}

	/**
     * Use this function to determine if a step is finished or not
     *
     * @param TT_Step_Abstract $step
     *
     * @return bool
     */
    public function isStepFinished(TT_Step_Abstract $step)
    {
        return $this->isFinished(self::KEY_STEPS,$step->getSlug());
    }

    /**
     * Stores the current theme version as first installed version
     */
    public function setFirstInstalledThemeVersion()
    {
        if (isset($this->memory[self::KEY_FIRST_THEME_VER])) {
            return;
        }

        $this->memory[self::KEY_FIRST_THEME_VER] = TT_THEME_VERSION;
        $this->store();
    }

    /**
     * Returns the first installed theme version
     *
     * @return string|false
     */
    public function getFirstInstalledThemeVersion()
    {
        if (! isset($this->memory[self::KEY_FIRST_THEME_VER])) {
            return false;
        }

        return $this->memory[self::KEY_FIRST_THEME_VER];
    }

    /**
     * Stores the current theme version as last installed version
     */
    public function setLastInstalledThemeVersion()
    {
        // there was already a newer version installed in the past
        if (isset($this->memory[self::KEY_LAST_THEME_VER])
            && version_compare($this->memory[self::KEY_LAST_THEME_VER], TT_THEME_VERSION) !== -1  ) {
            return;
        }

        $this->memory[self::KEY_LAST_THEME_VER] = TT_THEME_VERSION;
        $this->store();
    }

    /**
     * Returns the last installed theme version
     *
     * @return string|false
     */
    public function getLastInstalledThemeVersion()
    {
        if (! isset($this->memory[self::KEY_LAST_THEME_VER])) {
            return false;
        }

        return $this->memory[self::KEY_LAST_THEME_VER];
    }


    /**
     * Store that a import is finished
     *
     * @param $import_slug
     *
     * @return string
     */
    public function setImportFinished($import_slug) {
        $this->memory[self::KEY_IMPORTS][$import_slug] = TT_THEME_VERSION;
        $this->store();
    }

    /**
     * Returns if a import is finished or not
     *
     * @param $import_file
     *
     * @return bool
     */
    public function isImportFinished($import_file){
        return $this->isFinished(self::KEY_IMPORTS,$import_file);
    }

    /**
     * Check if in $category $key is finished or not
     *
     * @param $category
     * @param $key
     *
     * @return bool
     */
    private function isFinished($category,$key)
    {
        // import not registered yet = import not finished
        if (! isset($this->memory[$category][$key])) {
            return false;
        }

        // last import update was before current theme version
        if (version_compare($this->memory[$category][$key], TT_THEME_VERSION) == -1) {
            return false;
        }

        // on every other case the import is finished
        return true;
    }

	/**
	 * @return bool
	 */
	public function isThemeUpdate() {
		if( ! $this->getFirstInstalledThemeVersion() ) {
			// no theme of this name installed yet
			return false;
		}

		if( $this->getFirstInstalledThemeVersion() == TT_THEME_VERSION ) {
			// current theme has same version as first installed
			return false;
		}

		// update
		return true;
	}

	/**
	 * Set a step to be active
	 *
	 * @param $step_id
	 */
	public function setStepActive( $step_id ) {
		$this->memory[self::KEY_ACTIVE_STEPS][] = $step_id;
		$this->store();
	}

	/**
	 * Check if a step is active
	 *
	 * @param $step_id
	 *
	 * @return bool
	 */
	public function isStepActive( $step_id ) {
		if( ! isset( $this->memory[self::KEY_ACTIVE_STEPS] )
		    || ! in_array( $step_id, $this->memory[self::KEY_ACTIVE_STEPS] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Reset all active Steps
	 */
	public function resetActiveSteps() {
		if( ! isset( $this->memory[self::KEY_ACTIVE_STEPS] ) ) {
			return;
		}

		unset( $this->memory[self::KEY_ACTIVE_STEPS] );
		$this->store();
	}
}
