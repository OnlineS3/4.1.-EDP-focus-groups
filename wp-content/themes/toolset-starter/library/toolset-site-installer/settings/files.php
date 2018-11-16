<?php


class TT_Settings_Files implements TT_Settings_Files_Interface
{
	/**
	 * Dir of export files
	 * @var string
	 */
	private $export_path;

	/**
	 * URI of export files
	 * @var string
	 */
	private $export_path_uri;

	public function __construct( $export_path = null, $export_path_uri = null ) {
		if( $export_path === null && ! defined( 'TT_INSTALLER_EXPORTS_DIR' ) ) {
			throw new Exception( 'No export path defined.' );
		}

		$export_path = $export_path !== null
			? rtrim( $export_path, '/' )
			: rtrim( TT_INSTALLER_EXPORTS_DIR, '/' );

		if( $export_path && ! is_dir( $export_path ) ) {
			throw new Exception( 'Export path is not valid. ' . $export_path );
		}

		$this->export_path = $export_path;

		if( $export_path_uri === null && ! defined( 'TT_INSTALLER_EXPORTS_URI' ) ) {
			throw new Exception( 'No export path uri defined.' );
		}

		$this->export_path_uri = $export_path_uri !== null
			? rtrim( $export_path_uri, '/' )
			: rtrim( TT_INSTALLER_EXPORTS_URI, '/' );
	}
	
	public function getExportPath(){
		return $this->export_path;
	}

    /**
     * Supported Imports
     *
     * [Key = Import file name] => [Value = Plugin Slug]
     *
     * We need that to prove if the relevant plugin is active.
     */
    private $supported_imports = array(
	    'types.zip'         => 'types',
	    'wordpress.xml'     => 'wordpress',
	    'toolset-extra.zip' => 'toolset-extra',
	    'views.zip'         => 'views',
	    'cred-posts.zip'    => 'cred',
	    'cred-users.zip'    => 'cred',
	    'layouts.zip'       => 'layouts',
	    'access.zip'        => 'access',
    );

    /**
     * @var TT_Settings_Interface
     */
    private $settings;

    /**
     * Collection of relevant imports
     * @var null|false|array
     */
    private $imports_unfinished;

    /**
     * @param TT_Settings_Interface $settings
     */
    public function setSettings(TT_Settings_Interface $settings)
    {
        $this->settings = $settings;
    }

	/**
	 * @return TT_Settings_Interface
	 */
	public function getSettings()
	{
		return $this->settings;
	}

    /**
     * Detect if there any imports by plugins
     *
     * @return bool
     */
    public function hasPluginImports()
    {
        if ($this->getImportsUnfinished() === false) {
            // no imports at all
            return false;
        }

        if (count($this->getImportsUnfinished()) === 1
            && array_key_exists('wordpress', $this->getImportsUnfinished())
        ) {
            // only one item in imports and that is 'wordpress'
            return false;
        }

        return true;
    }

    /**
     * Detect if there is a wordpress import available
     * @return bool
     */
    public function hasWordpressImport()
    {
        if ($this->getImportsUnfinished() === false
            || ! array_key_exists('wordpress', $this->getImportsUnfinished())
        ) {
            // no imports at all or no wordpress in imports
            return false;
        }

        return true;
    }

    /**
     * @return array|false
     */
    private function getImportsUnfinished()
    {
        if ($this->imports_unfinished === null) {
            $this->fetchImportsUnfinished();
        }

        return $this->imports_unfinished;
    }

    /**
     * Detects import files which are ready to install
     * Means wordpress import file or import file of an active plugin
     */
    private function fetchImportsUnfinished()
    {
        foreach ($this->supported_imports as $filename => $plugin_slug) {
            if ($this->settings->getProtocol()->isImportFinished($plugin_slug)
                || (
                	$plugin_slug !== 'wordpress'
	                && $plugin_slug !== 'toolset-extra'
	                && ! $this->settings->getActivePlugin($plugin_slug)
                )
            ) {
                // is already done or not wordpress and not an active plugin
                continue;
            }

            $import_file_path = $this->export_path . '/' . $filename;

            if (file_exists($import_file_path)) {
                $this->imports_unfinished[$plugin_slug] = $import_file_path;
            }
        }

        if ($this->imports_unfinished === null) {
            $this->imports_unfinished = false;
        }
    }

    /**
     * This function checks if the import file is provided
     *
     * @param $include_wordpress
     * @param $import_file
     * @param $import_slug
     *
     * @return bool
     */
    private function importFileProvided( $include_wordpress, $import_file, $import_slug ) {
        if(
            ! array_key_exists($import_slug, $this->imports_unfinished)  // import file is not provided by theme author
            || ($import_slug === 'wordpress' && ! $include_wordpress) // import is wordpress but user don't want
            || ($import_slug !== 'wordpress' && ! $this->hasPluginImports()) // no plugin import available
            || strpos($this->imports_unfinished[$import_slug], $import_file) === false  // check if file name match path
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param $import_slug
     *
     * @return TT_Import_Interface
     * @throws Exception
     */
    private function getImportObjectBySlug($import_slug) {
        switch ($import_slug) {
            case 'views':
                return new TT_Import_Views( $this->settings->getFiles()->getExportPath() );
            case 'types':
                return new TT_Import_Types( $this->settings->getFiles()->getExportPath() );
            case 'cred':
                return new TT_Import_Cred(
                	$this->settings->getFiles()->getExportPath(),
                	$this->settings->getFiles()->getExportPath()
                );
            case 'layouts':
                return new TT_Import_Layouts( $this->settings->getFiles()->getExportPath() );
            case 'wordpress':
            	$this->initUploadsPackagedWithTheme();
                return new TT_Import_Wordpress( $this->settings->getFiles()->getExportPath() );
	        case 'toolset-extra':
		        return new TT_Import_Toolset_Extra( $this->settings->getFiles()->getExportPath() );
        }

        throw new Exception( 'No object found for import slug: ' . $import_slug );
    }

    /**
     * Returns array of all import objects, which are left to import
     *
     * @param bool $include_wordpress
     *
     * @return TT_Import_Interface[]|false
     */
    public function getAllImports($include_wordpress = true) {
        $imports = array();

        foreach ($this->supported_imports as $import_file => $import_slug) {
            if ( ! $this->importFileProvided($include_wordpress, $import_file, $import_slug)
            ) {
                continue;
            }

            $imports[] = $this->getImportObjectBySlug($import_slug);
        }

        return ! empty( $imports ) ? $imports : false;
    }

    /**
     * Returns the next import class
     *
     * @param bool $include_wordpress
     *
     * @return TT_Import_Interface|false
     */
    public function getNextImport($include_wordpress = true)
    {
        // following the order
        foreach ($this->supported_imports as $import_file => $import_slug) {
            if ( ! $this->importFileProvided($include_wordpress, $import_file, $import_slug)
            ) {
                continue;
            }

            return $this->getImportObjectBySlug( $import_slug );
        }

        return false;
    }

	/**
	 * Check if uploads folder exists (author used upload option for media files)
	 * @return bool
	 */
	private function initUploadsPackagedWithTheme() {
		if ( defined( 'TT_THEME_UPLOADS_DIR' ) && defined( 'TT_THEME_UPLOADS_URI' ) ) {
			return true;
		}

		if ( is_dir( $this->export_path . '/uploads' ) ) {
			define( 'TT_THEME_UPLOADS_DIR', $this->export_path . '/uploads' );
			define( 'TT_THEME_UPLOADS_URI', $this->export_path_uri . '/uploads' );

			return true;
		}

		return false;
	}
}
