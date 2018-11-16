<?php
final class WPDDL_Integration_Toolset_Starter_Loader extends WPDDL_Theme_Integration_Abstract {


    /**
     * Theme-specific initialization.
     *
     * @return bool|WP_Error True when the integration was successful or a WP_Error with a sensible message
     *     (which can be displayed to the user directly).
     */
    protected function initialize() {

        // Setup the autoloader
        $autoloader = WPDDL_Theme_Integration_Autoloader::getInstance();
        $autoloader->addPath( dirname( __FILE__ ) . '/application' );
        $autoloader->addPath( dirname( __FILE__ ) . '/library' );

        // Run the integration setup
        /** @noinspection PhpUndefinedClassInspection */
        $integration = WPDDL_Integration_Setup_Toolset_Starter::get_instance();
        $result = $integration->run();

        return $result;
    }


    /**
     * Determine whether the expected theme is active and the integration can begin.
     *
     * @return bool
     *
     */
    protected function is_theme_active() {
        return true;
    }


    /**
     * Supported theme name (as would wp_get_theme() return).
     *
     * @return string
     *
     */
    protected function get_theme_name() {
        $theme = wp_get_theme( );
        return $theme->Name;
    }


}
// @todo Update the class name.
WPDDL_Integration_Toolset_Starter_Loader::get_instance();