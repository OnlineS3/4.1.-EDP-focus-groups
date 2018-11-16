<?php


interface TT_Settings_Files_Interface
{
    /**
     * Detect if there any imports by plugins
     *
     * @return bool
     */
    public function hasPluginImports();

    /**
     * Detect if there is a wordpress import available
     * @return bool
     */
    public function hasWordpressImport();

    /**
     * Returns the next import class
     *
     * @param bool $include_wordpress
     *
     * @return bool|null|TT_Import_Interface
     */
    public function getNextImport($include_wordpress = true);

    /**
     * Returns array of all import objects, which are left to import
     *
     * @param bool $include_wordpress
     *
     * @return TT_Import_Interface[]|false
     */
    public function getAllImports($include_wordpress = true);

    /**
     * @param TT_Settings_Interface $settings
     */
    public function setSettings(TT_Settings_Interface $settings);

	/**
	 * @return TT_Settings_Interface
	 */
	public function getSettings();

	/**
	 * @return string
	 */
	public function getExportPath();
}