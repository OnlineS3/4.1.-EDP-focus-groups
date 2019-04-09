<?php


class TT_Import_Types_User_Fields extends TT_Import_Types_Post_Fields
{
    /**
     * Post Type
     * @return string
     */
    public function getPostType()
    {
        return 'wpcf-usermeta';
    }

    /**
     * Translated Title
     * @return mixed|string|void
     */
    public function getTitle()
    {
        return __('User Fields', 'toolset-themes');
    }

    /**
     * Sets items to import
     */
    protected function fetchItemsToImport()
    {
	    if( ! $this->propertyExists( array( 'user_fields', 'field' ), $this->import_data )
	        && ! is_array( $this->import_data->user_fields->field ) ) {
		    return false;
	    }

        $items = array();

	    foreach ($this->import_data->user_fields->field as $xml_field) {
            $field = wpcf_admin_import_export_simplexml2array($xml_field);

            // most items reflecting WP_POST, so we are using these entries on templates
            $field['post_name']  = $field['slug'];
            $field['post_title'] = $field['name'];

            $items[] = (object)$field;
        }

        return ! empty($items) ? $items : false;
    }

    /**
     * No edit link
     */
    protected function getItemEditLink( $post ) {
        return false;
    }
}