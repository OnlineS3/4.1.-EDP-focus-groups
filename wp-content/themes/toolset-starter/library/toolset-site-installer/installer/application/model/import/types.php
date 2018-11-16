<?php


class TT_Import_Types extends TT_Import_Abstract
{
    /**
     * @param string|bool $path
     */
    public function __construct( $path = false )
    {
        require_once WPCF_INC_ABSPATH . '/fields.php';
        require_once WPCF_INC_ABSPATH . '/import-export.php';

        // set default path if none set
        $path = $path ? $path : TT_INSTALLER_EXPORTS_DIR;
	    $path .= strpos( $path, '.zip' ) === false ? '/types.zip' : '';

        // path to import file
        $this->setPathImportFile($path);

        // load import file
        $zip      = new TT_Helper_Zip($this->getPathImportFile());
        $xml_file = $zip->fetchFileByExtension('.xml');

        if (! $xml = simplexml_load_string($xml_file)) {
            return false;
        }

        // initialize items groups
        $this->items_groups[] = new TT_Import_Types_Post_Types($xml);
        $this->items_groups[] = new TT_Import_Types_Taxonomies($xml);
        $this->items_groups[] = new TT_Import_Types_Post_Groups($xml);
        $this->items_groups[] = new TT_Import_Types_Post_Fields($xml);
        $this->items_groups[] = new TT_Import_Types_User_Groups($xml);
        $this->items_groups[] = new TT_Import_Types_User_Fields($xml);
        $this->items_groups[] = new TT_Import_Types_Term_Groups($xml);
        $this->items_groups[] = new TT_Import_Types_Term_Fields($xml);
    }

    public function getTitle()
    {
        return 'Types';
    }

    public function getSlug()
    {
        return 'types';
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
        $args = $this->translateUserChoiceToImportArguments($user_choice);
        // $args['import-file'] = $this->getPathImportFile();

        $zip      = new TT_Helper_Zip($this->getPathImportFile());
        $xml_file = $zip->fetchFileByExtension('.xml');

        return wpcf_admin_import_data($xml_file, $redirect = false, 'toolset-themes', $args);
    }

    protected function importNotModifiedAndNewItems($items_actions)
    {
        $items_not_modified = $this->getItemsNotModified() ? $this->getItemsNotModified() : array();
        $items_new          = $this->getItemsNew() ? $this->getItemsNew() : array();

        $items = array_merge( $items_not_modified, $items_new );

        foreach ($items as $item) {
            $slug = property_exists($item, '__types_id') && ! empty($item->__types_id)
                ? $item->__types_id
                : $item->post_name;

            $items_actions[$this->getKeyForImportItem()][] = $slug;
        }

        return $items_actions;
    }

    protected function arrayDiffCompareItems($item_a, $item_b)
    {
        return strcmp($item_a->__types_id, $item_b->__types_id);
    }

}