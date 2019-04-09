<?php


interface TT_Settings_Interface
{

    /**
     * @return TT_Settings_Protocol_Interface
     */
    public function getProtocol();

    /**
     * @return TT_Settings_Files_Interface
     */
    public function getFiles();

    /**
     * @param $slug
     *
     * @return TT_Plugin
     */
    public function getPlugin($slug);

    /**
     * @return TT_Plugin[]
     */
    public function getPlugins();

    /**
     * @param $slug
     *
     * @return mixed
     */
    public function getActivePlugin($slug);

    /**
     * @return TT_Plugin[]
     */
    public function getActivePlugins();

    /**
     * @return string
     */
    public function getThemeUpdateUrl();

	/**
	 * @return string
	 */
    public function getAuthorBaseUrl();

	/**
	 * @return TT_Repository_Interface
	 */
    public function getRepository();

	/**
	 * @param TT_Repository_Interface $repository
	 *
	 * @return $this
	 */
	public function setRepository( TT_Repository_Interface $repository );

	/**
	 * @return TT_Context_Interface
	 */
	public function getContext();

	/**
	 * @param TT_Context_Interface $context
	 *
	 * @return $this
	 */
	public function setContext( TT_Context_Interface $context );
}
