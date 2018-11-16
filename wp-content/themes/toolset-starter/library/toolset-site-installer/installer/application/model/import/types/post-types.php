<?php


class TT_Import_Types_Post_Types extends TT_Import_Items_Group_Abstract
{
    protected $allow_duplicate = false;

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
        return 'wpcf-custom-types';
    }

    /**
     * Translated Title
     * @return mixed|string|void
     */
    public function getTitle()
    {
        return __('Post Types', 'toolset-themes');
    }

    /**
     * Sets items to import
     */
    protected function fetchItemsToImport()
    {
	    if( ! $this->propertyExists( array( 'types', 'type' ), $this->import_data )
	        && ! is_array( $this->import_data->types->type ) ) {
		    return false;
	    }

	    $items = array();

        foreach ($this->import_data->types->type as $xml_cpt) {
            $cpt = wpcf_admin_import_export_simplexml2array($xml_cpt);

            // most items reflecting WP_POST, so we are using these entries on templates
            $cpt['post_name']  = $cpt['slug'];
            $cpt['post_title'] = $cpt['labels']['name'];

            $items[] = (object)$cpt;
        }

        return ! empty($items) ? $items : false;
    }

    /**
     * Fetch the currently stored items (stored in the database)
     *
     * @return WP_Post[]|false
     */
    protected function fetchItemsPresent()
    {
        $all_cpts = get_option($this->getPostType(), array());

        if (empty($all_cpts)) {
            return false;
        }

        $cpts = array();

        foreach ($all_cpts as $cpt) {
            // most items reflecting WP_POST, so we are using these entries on templates
            $cpt['post_name']  = $cpt['slug'];
            $cpt['post_title'] = $cpt['labels']['name'];

            $cpts[] = (object)$cpt;
        }

        return $cpts;
    }

    /**
     * We don't save any 'last edit' timestamp for fields.
     * Todo: compare with previous import file
     *
     * @return array|WP_Post[]|false
     */
    protected function fetchItemsModified()
    {
        $modified_cpts = array();

        if( ! $this->getItemsPresent() ) {
        	// no items
        	return $modified_cpts;
        }

        foreach( $this->getItemsPresent() as $cpt ) {
            if( property_exists( $cpt, '_toolset_edit_last' ) ) {
                $cpt->guid = $this->getItemEditLink( $cpt );
                $modified_cpts[] = $cpt;
            }
        };

        return $modified_cpts;
    }

    /**
     * Returns the edit link
     *
     * @param $post WP_Post
     *
     * @return string
     */
    protected function getItemEditLink( $post ) {
        return admin_url() . 'admin.php?page=wpcf-edit-type&wpcf-post-type=' . $post->{'wpcf-post-type'};
    }
}