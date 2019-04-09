<?php


interface TT_Import_Interface {
    /**
     * Title of the plugin
     * @return string
     */
    public function getTitle();

    /**
     * Slug of the plugin
     * @return string
     */
    public function getSlug();

    /**
     *
     * @return mixed
     */
    public function getItemsToImport();

    public function getItemsRequireUserDecision();

    public function setPathImportFile($path_import_file);

    /**
     * Import function
     *
     * @param array $user_choice
     *
     * @return mixed
     */
    public function import($user_choice);

	/**
	 * @return TT_Import_Process
	 */
	public function getProcessHandler();

	/**
	 * @param TT_Import_Process $process
	 */
	public function setProcessHandler( TT_Import_Process $process );

	public function processIncreaseTodo();

	public function processIncreaseTodoDone();
}