<?php


class TT_Controller_Ajax_Import_Process extends TT_Controller_Abstract {
	const COMMAND = 'import-process';

	const KEY_NO_PROCESS = 'no-import-process';

	/**
	 * Currently we only use attachments for the process status
	 */
	public function handleAjaxRequest() {
		$process_handler  = new TT_Import_Process();

		if ( ! $percentage = $process_handler->getProcessInPercentage() ) {
			wp_send_json( array( 'response' => self::KEY_NO_PROCESS ) );
		}

		if( $percentage >= 100 ) {
			// >100 CAN happen if we have meta sizes with same name
			// limit to 99 as 100 will be used if everything is ready
			$percentage = 99;
		}

		wp_send_json( array( 'response' => str_pad( $percentage, 3, ' ', STR_PAD_LEFT ) ) );
	}
}