<?php


class TT_Import_Types_Post_Fields extends TT_Import_Items_Group_Abstract
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
        return 'wpcf-fields';
    }

    /**
     * Translated Title
     * @return mixed|string|void
     */
    public function getTitle()
    {
        return __('Post Fields', 'toolset-themes');
    }

    /**
     * Sets items to import
     */
    protected function fetchItemsToImport()
    {
	    if( ! $this->propertyExists( array( 'fields', 'field' ), $this->import_data )
	        && ! is_array( $this->import_data->fields->field ) ) {
		    return false;
	    }

	    $items = array();

        foreach ($this->import_data->fields->field as $xml_field) {
            $field = wpcf_admin_import_export_simplexml2array($xml_field);

            // most items reflecting WP_POST, so we are using these entries on templates
            $field['post_name']  = $field['slug'];
            $field['post_title'] = $field['name'];

            $items[] = (object)$field;
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
        $all_fields = get_option($this->getPostType(), array());

        if (empty($all_fields)) {
            return false;
        }

        $fields = array();

        foreach ($all_fields as $field) {
            // most items reflecting WP_POST, so we are using these entries on templates
            $field['post_name']  = $field['slug'];
            $field['post_title'] = $field['name'];

            $fields[] = (object)$field;
        }

        return $fields;
    }

    protected function arrayDiffCompareItems($item_a, $item_b)
    {
        return strcmp($item_a->slug, $item_b->slug);
    }


    /**
     * We don't save any 'last edit' timestamp for fields.
     * Todo: compare with previous import file
     *
     * @return array|WP_Post[]|false
     */
    protected function fetchItemsModified()
    {
        $modified_fields = array();

	    if( ! $this->getItemsPresent() ) {
		    // no items
		    return $modified_fields;
	    }

	    foreach( $this->getItemsPresent() as $field ) {
            if( property_exists( $field, '_toolset_edit_last' ) ) {
                $field->guid = $this->getItemEditLink( $field );
                $modified_fields[] = $field;
            }
        };

        return $modified_fields;
    }

    /**
     * No edit link
     */
    protected function getItemEditLink( $post ) {
        return false;
    }
}