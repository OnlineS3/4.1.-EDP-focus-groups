<?php


class TT_Import_Layouts_Layouts extends TT_Import_Items_Group_Abstract
{
    /**
     * Import data, zip object
     * @var TT_Helper_Zip
     */
    protected $import_data;

    /**
     * TT_Import_Layouts_Layouts constructor.
     *
     * @param TT_Helper_Zip $import_data
     */
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
        return 'dd_layouts';
    }

    /**
     * Translated Title
     * @return mixed|string|void
     */
    public function getTitle()
    {
        return __('Layouts', 'toolset-themes');
    }

    /**
     * Sets items to import
     */
    protected function fetchItemsToImport()
    {
        if( ! $layouts_json = $this->import_data->fetchFilesByExtension('.ddl') ) {
            return false;
        }

        $layouts = array();

        foreach ($layouts_json as $layout_json) {
	        $layout_stdclass = json_decode(str_replace('\\\"', '\"', $layout_json));
        	if( ! is_object( $layout_stdclass ) || ! property_exists( $layout_stdclass, 'slug' ) ) {
        		continue;
	        }

            $layout_stdclass->post_name = $layout_stdclass->slug;
            $layouts[] = $layout_stdclass;
        }

        return ! empty($layouts) ? $layouts : false;
    }


    /**
     * Returns the edit link
     *
     * @param $post WP_Post
     *
     * @return string
     */
    protected function getItemEditLink( $post ) {
        return admin_url() . 'admin.php?page=dd_layouts_edit&layout_id=' . $post->ID . '&action=edit';
    }
}