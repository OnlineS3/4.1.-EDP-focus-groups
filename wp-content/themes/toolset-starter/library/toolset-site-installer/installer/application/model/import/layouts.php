<?php


class TT_Import_Layouts extends TT_Import_Abstract
{
    /**
     * @param string|bool $path
     */
    public function __construct( $path = false )
    {
        // default path
        $path = $path ? $path : TT_INSTALLER_EXPORTS_DIR;
	    $path .= strpos( $path, '.zip' ) === false ? '/layouts.zip' : '';

        // path to import file
        $this->setPathImportFile($path);

        // load import file
        $zip      = new TT_Helper_Zip($this->getPathImportFile());

        // initialize items groups
        $this->items_groups[] = new TT_Import_Layouts_Layouts($zip);
    }

    public function getTitle()
    {
        return 'Layouts';
    }

    public function getSlug()
    {
        return 'layouts';
    }

    /**
     * Import functions of Toolset plugins provide an option TO SKIP ITEMS by post name
     * This function must returns the name of the option.
     *
     * @return string
     */
    protected function getKeyForSkippingItem()
    {
        return 'skip';
    }

    /**
     * Import functions of Toolset plugins provide an option TO DUPLICATE ITEMS by post name
     * This function must returns the name of the option.
     *
     * @return mixed
     */
    protected function getKeyForDuplicateItem()
    {
        return 'duplicate';
    }

    /**
     * Import functions of Toolset plugins provide an option TO IMPORT(& OVERWRITE) ITEMS by post name
     * This function must returns the name of the option.
     *
     * @return mixed
     */
    protected function getKeyForImportItem()
    {
        return 'overwrite';
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
	    $args['toolset-themes'] = true;

	    // set overwrite assignments to true if there are no layouts created yet
	    $existing_layouts = get_posts( array( 'post_type' => 'dd_layouts', 'numberposts' => 1 ) );
	    $args['toolset-themes-overwrite-assignments'] = empty( $existing_layouts ) ? true : false;

        require_once( WPDDL_ABSPATH . '/ddl-theme.php' );

        return @ddl_update_theme_layouts( $this->getPathImportFile(), $args );
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

}