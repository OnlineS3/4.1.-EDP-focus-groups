<?php


class TT_Import_Views_Content_Templates extends TT_Import_Items_Group_Abstract
{
    public function __construct($import_data)
    {
        $this->import_data = $import_data;
    }

    /**
     * Post Type
     * @return string
     */
    public function getPostType()
    {
        return 'view-template';
    }

    /**
     * Translated Title
     * @return mixed|string|void
     */
    public function getTitle()
    {
        return __('Content Templates', 'toolset-themes');
    }

    /**
     * Sets items to import
     */
    protected function fetchItemsToImport()
    {
        $import_data = wpv_admin_import_export_simplexml2array($this->import_data);

	    // normalise data (different structure for 1 item and multiple)
	    $import = is_numeric( key( $import_data['view-templates']['view-template'] ) )
		    ? $import_data['view-templates']['view-template']
		    : $import_data['view-templates'];

	    $items = array();
	    foreach( $import as $item ) {
            $items[] = (object) $item;
        }

        return ! empty($items) ? $items : false;
    }


    /**
     * Returns the edit link
     *
     * @param $post WP_Post
     *
     * @return string
     */
    protected function getItemEditLink( $post ) {
        return admin_url() . 'admin.php?page=ct-editor&ct_id=' . $post->ID;
    }
}