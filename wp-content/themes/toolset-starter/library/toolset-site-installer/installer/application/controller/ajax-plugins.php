<?php


class TT_Controller_Ajax_Plugins extends TT_Controller_Abstract
{
    const COMMAND = 'install_plugin';

    public function handleAjaxRequest()
    {
        if (! array_key_exists('plugin', $_REQUEST)
            || ! $plugin = $this->settings->getPlugin($_REQUEST['plugin'])
        ) {
        	// the message here is not what really happens - we use it as "universal" message for the client
	        // in real the requested plugin does not exists in the settings file (can only happen if DOM is modified)
            die(sprintf(__('There was a problem downloading the plugin. This may be a temporary network problem. 
            Please try again in a few minutes. If the problem continues, contact %s author for support.',
                'toolset-themes'), wp_get_theme()->get('Name')));
        }

	    // make sure WooCommerce is not running the installer
	    add_filter( 'woocommerce_enable_setup_wizard', '__return_false' );

        // allow repository to not use the settings src
        $repository_plugin_src = $this->settings->getRepository()->getPluginSrc( $plugin->getId() );
        if( $repository_plugin_src ) {
        	$plugin->setSrc( $repository_plugin_src );
        }

        require_once TT_INSTALLER_DIR . '/application/model/helper/upgrader-skin/ajax.php';
        $upgrader_step = new TT_Upgrader_Skin_Ajax();

        $status = $plugin->install($upgrader_step);

        if (is_wp_error($status)) {
            die($this->wordpressErrorMessage($status));
        }

        $status = $plugin->activate();

        if (is_wp_error($status)) {
            die($this->wordpressErrorMessage($status));
        }

        die();
    }
}