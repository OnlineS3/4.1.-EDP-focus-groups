<?php

/**
 * Class Types_Ajax_Handler_Intermediary_Parent_Child
 *
 * @since 3.0
 */
class Types_Ajax_Handler_Intermediary_Parent_Child extends Types_Ajax_Handler_Post_Reference_Field {
	/**
	 * @param array $arguments Original action arguments.
	 *
	 * @return mixed
	 */
	function process_call( $arguments ) {
		$this->get_am()
		     ->ajax_begin(
			     array(
				     'nonce' => $this->get_am()->get_action_js_name( Types_Ajax::CALLBACK_INTERMEDIARY_PARENT_CHILD ),
				     'capability_needed' => 'edit_posts'
			     )
		     );

		// Read and validate input
		$action = sanitize_text_field( toolset_getpost( 'intermediary_action' ) );

		// route action
		return $this->route( $action );
	}

	/**
	 * Route ajax calls
	 *
	 * @param $action
	 *
	 * @return array
	 */
	protected function route( $action ) {
		switch ( $action ) {
			case 'json_intermediary_parent_child_posts':
				return $this->json_posts();
			case 'json_save_association':
				return $this->json_save_association();
		}
	}

	/**
	 * Types_Ajax_Handler_Post_Reference_Field::json_posts()
	 * Just added here for better class overview.
	 */
	protected function json_posts() {
		parent::json_posts();
	}

	/**
	 *
	 */
	private function json_save_association() {
		try {
			// get user data
			$intermediary_id   = sanitize_text_field( toolset_getpost( 'intermediary_id' ) );
			$parent_id         = sanitize_text_field( toolset_getpost( 'parent_id' ) );
			$child_id          = sanitize_text_field( toolset_getpost( 'child_id' ) );

			// response handler
			$response_handler = new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseHandler();
			$response_handler->addResponse(
				new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseAssociationDelete(
					new Toolset_Association_Persistence()
				)
			);
			$response_handler->addResponse(
				new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseAssociationMissingData()
			);
			$response_handler->addResponse(
				new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseAssociationConflict()
			);
			$response_handler->addResponse(
				new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseAssociationSave(
					new Toolset_Association_Persistence(),
					new Toolset_Association_Factory()
				)
			);

			/** @var \OTGS\Toolset\Types\Model\Post\Intermediary\Request $request */
			$request = new \OTGS\Toolset\Types\Model\Post\Intermediary\Request(
				new Toolset_Element_Factory(),
				Toolset_Post_Type_Repository::get_instance(),
				new Toolset_Association_Query_V2(),
				new Toolset_Relationship_Query_V2(),
				new Toolset_Relationship_Role_Parent(),
				new Toolset_Relationship_Role_Child(),
				new Toolset_Relationship_Role_Intermediary()
			);

			$request->setIntermediaryId( $intermediary_id );
			$request->setParentId( $parent_id );
			$request->setChildId( $child_id );

			$result  = new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result();

			wp_send_json( $response_handler->response( $request, $result ) );

		} catch ( Toolset_Element_Exception_Element_Doesnt_Exist $e ) {
			// some element could not be loaded, probably DOM invalid
			wp_send_json( new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result(
				'', \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result::RESULT_DOM_ERROR
			) );

		} catch ( Exception $e ) {
			if( defined( 'WP_DEBUG') && WP_DEBUG ) {
				error_log( $e->getMessage() );
			}

			wp_send_json( new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result(
				'', \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result::RESULT_SYSTEM_ERROR
			) );
		}
	}
}