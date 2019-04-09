<?php
/**
 * Created by PhpStorm.
 * User: rogopag
 * Date: 28/01/16
 * Time: 22:47
 */

add_action( 'wpddl_theme_integration_support_ready', array( WPDDL_Integration_Toolset_Starter::getInstance(), 'begin_loading' ), 10, 2 );

class WPDDL_Integration_Toolset_Starter{

    private static $instance = null;

    public static function getInstance() {
        if( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function begin_loading(){
        require_once 'layouts-integrate-loader.php';

        // We need to manually setup plugin name, since it depends on the main file name.
        // @todo Update class name.
        $loader = WPDDL_Integration_Toolset_Starter_Loader::get_instance();
        $loader->set_plugin_base_name( basename( __FILE__ ) );
    }
}