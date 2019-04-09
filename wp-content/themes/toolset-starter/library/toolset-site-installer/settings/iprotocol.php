<?php


interface TT_Settings_Protocol_Interface
{
    /**
     * Store that a step is finished
     *
     * @param TT_Step_Abstract $step
     */
    public function setStepFinished(TT_Step_Abstract $step);

    /**
     * Use this function to determine if a step is finished or not
     *
     * @param TT_Step_Abstract $step
     *
     * @return bool
     */
    public function isStepFinished(TT_Step_Abstract $step);


    /**
     * Stores the current theme version as first installed version
     */
    public function setFirstInstalledThemeVersion();

    /**
     * Returns the first installed theme version
     *
     * @return string|false
     */
    public function getFirstInstalledThemeVersion();


    /**
     * Stores the current theme version as last installed version
     */
    public function setLastInstalledThemeVersion();

    /**
     * Returns the last installed theme version
     *
     * @return string|false
     */
    public function getLastInstalledThemeVersion();

    /**
     * Store that a import is finished
     *
     * @param $import_file
     *
     * @return string
     */
    public function setImportFinished($import_file);

    /**
     * Returns if a import is finished or not
     *
     * @param $import_file
     *
     * @return bool
     */
    public function isImportFinished($import_file);

	/**
	 * @return bool
	 */
	public function isThemeUpdate();

	/**
	 * Store OTGS Site Key
	 *
	 * @param string $site_key
	 */
	public function setSiteKey( $site_key );

	/**
	 * Get OTGS Site Key
	 *
	 * @return bool|string
	 */
	public function getSiteKey();


	/**
	 * Check if Site Key is valid
	 *
	 * @param TT_Repository_Interface $repository
	 *
	 * @return bool
	 */
	public function isSiteKeyValid( TT_Repository_Interface $repository );

	/**
	 * Set a step to be active
	 *
	 * @param $step_id
	 */
	public function setStepActive( $step_id );

	/**
	 * Check if a step is active
	 *
	 * @param $step_id
	 *
	 * @return bool
	 */
	public function isStepActive( $step_id );

	/**
	 * Reset all active Steps
	 */
	public function resetActiveSteps();
}
