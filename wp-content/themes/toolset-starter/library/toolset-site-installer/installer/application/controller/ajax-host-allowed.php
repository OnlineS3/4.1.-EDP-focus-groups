<?php


class TT_Controller_Ajax_Host_Allowed extends TT_Controller_Abstract {
	const COMMAND = 'host_allowed';

	public function handleAjaxRequest()
	{
		if( ! $this->settings->getRepository()->useHostAllowedMechanism() ) {
			// no check required on this current repository
			die( 'success' );
		};

		$url = parse_url( $this->settings->getThemeUpdateUrl() );

		// todo better provide the url through install settings than generating it this way
		$host_allowed = $url['scheme'] . '://' . $url['host'] . '/theme/host/allowed/' . substr( $url['path'], -32 );

		$response = wp_remote_get( $host_allowed , array( 'sslverify' => false ) );

		if( is_wp_error( $response ) ) {
			die( $response->get_error_message() );
		}

		die( $response['body'] );
	}
}