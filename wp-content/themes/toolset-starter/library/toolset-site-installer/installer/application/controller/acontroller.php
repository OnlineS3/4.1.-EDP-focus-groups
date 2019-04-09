<?php

abstract class TT_Controller_Abstract {

	/**
	 * @var TT_Settings_Interface
	 */
	protected $settings;

	/**
	 * @var TT_Response_Interface
	 */
	protected $response_ajax;

	/**
	 * TT_Controller_Abstract constructor.
	 *
	 * @param TT_Settings_Interface $settings
	 * @param TT_Response_Interface $response_ajax
	 */
	public function __construct( TT_Settings_Interface $settings, TT_Response_Interface $response_ajax = null ) {
		$this->settings = $settings;

		if ( $response_ajax === null ) {
			$this->response_ajax = new TT_Response_Wp_Ajax();
		}
	}

	/**
	 * When we respone a WP_Error message, we do it through this function
	 *
	 * @param WP_Error $error
	 *
	 * @return string
	 */
	protected function wordpressErrorMessage( WP_Error $error ) {
		return $error->get_error_message()
		       . sprintf(
			       __( 'This may be a temporary network problem. Please try again in a few minutes. If the problem 
                       continues, contact %s author for support.', 'toolset-themes' )
			       , wp_get_theme()->get( 'Name' )
		       );
	}

	/**
	 * @return TT_Context_Interface
	 */
	public function getContext() {
		return $this->settings->getContext();
	}
}