<?php


class TT_Import_Toolset_Extra extends TT_Import_Abstract
{
    /**
     * @param string|bool $path
     */
    public function __construct( $path = false )
    {
        // default path
        $path = $path ? $path : TT_INSTALLER_EXPORTS_DIR;
	    $path .= strpos( $path, '.zip' ) === false ? '/toolset-extra.zip' : '';

        $this->setPathImportFile($path);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Toolset Advanced Export';
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return 'toolset-extra';
    }


    /**
     * Import function
     *
     * @param array $user_choice
     *  'force_import_post_name'    => array(),
     *  'force_skip_post_name       => array(),
     *  'force_duplicate_post_name  => array(),
     *
     * @return mixed
     */
    public function import($user_choice)
    {
	    try{
		    require_once( TT_INSTALLER_DIR . '/library/toolset-advanced-export/bootstrap.php' );
		    ToolsetAdvancedExport\Api::initialize();
		    $results = apply_filters(
			    'toolset_import_extra_wordpress_data_zip',
			    null,
		        $this->getPathImportFile(),
			    [ 'all' ]
		    );
	    } catch( \Exception $e ) {
		    error_log( 'error ' . print_r( $e->getMessage(), true ) );
	    }

        return true;
    }
}