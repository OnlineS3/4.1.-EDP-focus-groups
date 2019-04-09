<?php


class TT_Controller_Ajax_Site_Key extends TT_Controller_Abstract
{
    const COMMAND = 'site_key';

    public function handleAjaxRequest()
    {
        if(! array_key_exists('site_key', $_REQUEST) ) {
        	// the message here is not what really happens - we use it as "universal" message for the client
	        // (can only happen if DOM is modified)
            die(sprintf(__('There was a problem validating the site key. This may be a temporary network problem. 
            Please try again in a few minutes. If the problem continues, contact %s author for support.',
                'toolset-themes'), wp_get_theme()->get('Name')));
        }

        if( empty( $_REQUEST['site_key'] ) ) {
	        die( __( 'Site Key is required.', 'toolset-themes' ) );
        }

        $this->settings->getProtocol()->setSiteKey( $_REQUEST['site_key'] );

		if( $this->settings->getProtocol()->isSiteKeyValid( $this->settings->getRepository() ) ) {
			die( 'success' );
		}

	    die( __( 'The Site Key that you entered doesn\'t fit this domain. Check that you copy/paste exactly the 
	    Site Key, without adding or omitting characters. If you generated this Site Key for a different domain, 
	    you should create a separate Site Key for this domain.', 'toolset-themes' ) );
    }
}