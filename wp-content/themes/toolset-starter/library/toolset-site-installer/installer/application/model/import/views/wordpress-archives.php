<?php


class TT_Import_Views_Wordpress_Archives extends TT_Import_Items_Group_Abstract
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
        return 'wordpress-archives';
    }

    /**
     * Translated Title
     * @return mixed|string|void
     */
    public function getTitle()
    {
        return __('Wordpress Archives', 'toolset-themes');
    }

    /**
     * Fetch the currently stored items (stored in the database)
     *
     * @return WP_Post[]|false
     */
    protected function fetchItemsPresent()
    {
        if ( ! class_exists('WPV_Embedded_WPA_Item_Provider_Decorator')) {
            return false;
        }

        $collector = new WPV_Embedded_WPA_Item_Provider_Decorator();
        $items     = $collector->get_items( array() );

        if ( ! $items || empty($items)) {
            return false;
        }

        $views = array();
        foreach ($items as $item) {
            $views[] = $item->get_post();
        }

        if (empty($views)) {
            // no items
            return false;
        }

        return $views;
    }

    /**
     * Sets items to import
     */
    protected function fetchItemsToImport()
    {
        $import_data = wpv_admin_import_export_simplexml2array($this->import_data);

	    // normalise data (different structure for 1 item and multiple)
	    $import = is_numeric( key( $import_data['views']['view'] ) )
		    ? $import_data['views']['view']
		    : $import_data['views'];

	    $items = array();
	    foreach ($import as $item) {
            if ( array_key_exists( 'meta', $item ) && array_key_exists('_wpv_settings', $item['meta'] )
                 && ( $item['meta']['_wpv_settings']['view-query-mode'] == 'archive'
                      || $item['meta']['_wpv_settings']['view-query-mode'] == 'layouts-loop' )
            ) {
                $items[] = (object)$item;
            }
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
    protected function getItemEditLink($post)
    {
        return admin_url() . 'admin.php?page=views-editor&view_id=' . $post->ID;
    }
}