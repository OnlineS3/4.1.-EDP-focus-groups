<?php
class WPDDL_Integration_Setup_Toolset_Starter extends WPDDL_Theme_Integration_Setup_Abstract{


    protected function get_supported_theme_version(){
        return '1.3.5';
    }

    protected  function get_plugins_url( $relative_path ){
        return get_template_directory_uri() . '/functions/layouts';
    }

    protected function get_supported_templates() {
        return array(
            $this->getPageDefaultTemplate() => __( 'Page', 'toolset_starter' )
        );
    }

    public function run() {
        parent::run();
        $this->setPageDefaultTemplate('page.php');
        return true;
    }

    /**
     * @override
     */
    public function hook_enqueue_frontend_scripts(){
        return;
    }

    /**
     * @override
     */
    public function admin_enqueue() {
        $this->enqueue_post_edit_page_overrides_js();
    }
}
