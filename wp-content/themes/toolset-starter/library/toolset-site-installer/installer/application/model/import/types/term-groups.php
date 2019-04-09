<?php


class TT_Import_Types_Term_Groups extends TT_Import_Types_Post_Groups
{
    /**
     * Post Type
     * @return string
     */
    public function getPostType()
    {
        return 'wp-types-term-group';
    }

    /**
     * Translated Title
     * @return mixed|string|void
     */
    public function getTitle()
    {
        return __('Term Field Groups', 'toolset-themes');
    }

    /**
     * Sets items to import
     */
    protected function fetchItemsToImport()
    {
	    if( ! $this->propertyExists( array( 'term_groups', 'group' ), $this->import_data )
	        && ! is_array( $this->import_data->term_groups->group ) ) {
		    return false;
	    }

	    $items = array();

        foreach ($this->import_data->term_groups->group as $xml_group) {
            $group   = wpcf_admin_import_export_simplexml2array($xml_group);
            $items[] = (object)$group;
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
        return admin_url() . 'admin.php?page=wpcf-termmeta-edit&group_id=' . $post->ID;
    }
}