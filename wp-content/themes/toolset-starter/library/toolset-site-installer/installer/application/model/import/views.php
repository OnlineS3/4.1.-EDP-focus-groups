<?php


class TT_Import_Views extends TT_Import_Abstract
{
    /**
     * @param string|bool $path
     */
    public function __construct($path = false)
    {
        // default path
        $path = $path ? $path : TT_INSTALLER_EXPORTS_DIR;
        $path .= strpos( $path, '.zip' ) === false ? '/views.zip' : '';

        $this->setPathImportFile($path);

        // load import file
        $zip      = new TT_Helper_Zip($this->getPathImportFile());
        $xml_file = $zip->fetchFileByExtension('.xml');

        if (! $xml = simplexml_load_string($xml_file)) {
            return false;
        }

        // initialize items groups
        $this->items_groups[] = new TT_Import_Views_Views($xml);
        $this->items_groups[] = new TT_Import_Views_Content_Templates($xml);
        $this->items_groups[] = new TT_Import_Views_Wordpress_Archives($xml);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Views';
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return 'views';
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
        $args                = $this->translateUserChoiceToImportArguments($user_choice);
        $args['import-file'] = $this->getPathImportFile();

        return wpv_api_import_from_file($args);
    }
}