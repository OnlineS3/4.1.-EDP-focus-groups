<?php


class TT_Import_Cred_Post_Forms extends TT_Import_Items_Group_Abstract
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
        return 'cred-form';
    }

    /**
     * Translated Title
     * @return mixed|string|void
     */
    public function getTitle()
    {
        return __('Post Forms', 'toolset-themes');
    }

    /**
     * Sets items to import
     */
    protected function fetchItemsToImport()
    {
	    if( ! $this->propertyExists( array( 'form' ), $this->import_data )
	        && ! is_array( $this->import_data->form ) ) {
		    return false;
	    }

        $items = array();

        foreach ($this->import_data->form as $xml_field) {
            // trick 17 to convert SimpleXMLClass to StdClass
            $items[] = json_decode(json_encode($xml_field));

        }

        return ! empty($items) ? $items : false;
    }
}