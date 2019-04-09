<?php


class TT_Import_Types_Post_Groups extends TT_Import_Items_Group_Abstract
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
        return 'wp-types-group';
    }

    /**
     * Translated Title
     * @return mixed|string|void
     */
    public function getTitle()
    {
        return __('Post Field Groups', 'toolset-themes');
    }

    /**
     * Sets items to import
     */
    protected function fetchItemsToImport()
    {
	    if( ! $this->propertyExists( array( 'groups', 'group' ), $this->import_data )
	        && ! is_array( $this->import_data->groups->group ) ) {
		    return false;
	    }

	    $items = array();

        foreach ($this->import_data->groups->group as $xml_group) {
            $group   = wpcf_admin_import_export_simplexml2array($xml_group);
            $items[] = (object)$group;
        }

        return ! empty($items) ? $items : false;
    }

    protected function arrayDiffCompareItems($item_a, $item_b)
    {
        $name_a = property_exists($item_a, 'post_name') ? $item_a->post_name : $item_a->__types_id;
        $name_b = property_exists($item_b, 'post_name') ? $item_b->post_name : $item_b->__types_id;

        return strcmp($name_a, $name_b);
    }

    /**
     * Returns the edit link
     *
     * @param $post WP_Post
     *
     * @return string
     */
    protected function getItemEditLink( $post ) {
        return admin_url() . 'admin.php?page=wpcf-edit&group_id=' . $post->ID;
    }
}