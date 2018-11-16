<?php


/**
 * @refactoring !! This needs to use the potential association query, and the get_additional_wp_query_args() method.
 */
class Types_Ajax_Handler_Post_Reference_Field extends Toolset_Ajax_Handler_Abstract {
	/**
	 * @param array $arguments Original action arguments.
	 *
	 * @return void
	 */
	function process_call( $arguments ) {
		$this->get_am()
		     ->ajax_begin(
			     array(
				     'nonce' => $this->get_am()->get_action_js_name( Types_Ajax::CALLBACK_POST_REFERENCE_FIELD ),
				     'capability_needed' => 'edit_posts'
			     )
		     );

		// Read and validate input
		$action = sanitize_text_field( toolset_getpost( 'post_reference_field_action' ) );

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
			case 'json_post_reference_field_posts':
				return $this->json_posts();
		}
	}


	/**
	 * Function to get posts by search term
	 *
	 * This function exits the script (ajax response).
	 * @print json
	 */
	protected function json_posts() {
		$post_type      = sanitize_text_field( toolset_getpost( 'post_type' ) );
		$search         = sanitize_text_field( toolset_getpost( 'search' ) );
		$page           = sanitize_text_field( toolset_getpost( 'page' ) );
		$post_id        = sanitize_text_field( toolset_getpost( 'post_id' ) );
		$posts_per_page = Types_Field_Type_Post_View_Backend_Display::SELECT2_POSTS_PER_LOAD;

		global $wpdb;

		$prepare_values = array( $post_type );

		// SEARCH
		$search_where = " AND p.post_status IN ('publish', 'draft') ";

		if( $post_id ) {
			// don't display current post in list of assignable posts
			$search_where .= " AND p.ID != %d ";
			$prepare_values[] = $post_id;
		}

		if ( $search != '' ) {
			if ( method_exists( $wpdb, 'esc_like' ) ) {
				$search_term = '%' . $wpdb->esc_like( $search ) . '%';
			} else {
				$search_term = '%' . like_escape( esc_sql( $search ) ) . '%';
			}
			$search_where     .= " AND p.post_title LIKE %s ";
			$prepare_values[] = $search_term;
			$orderby = ' ORDER BY p.post_title ';
		} else {
			$orderby = ' ORDER BY p.post_date DESC ';
		}

		// PAGE
		if ( preg_match( '/^\d+$/', $page ) ) {
			$prepare_values[] = ( (int) $page - 1 ) * $posts_per_page;
		} else {
			$prepare_values[] = 0;
		}

		$prepare_values[] = $posts_per_page;

		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS
					p.ID as id, 
					p.post_title as text, 
					p.post_type as type, 
					p.post_status as status 
				FROM {$wpdb->posts} p
				WHERE p.post_type IN ('%s')
				{$search_where}
				{$orderby}
				LIMIT %d, %d",
				$prepare_values
			)
		);

		$posts_count = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		wp_send_json(
			array(
				'items'              => $posts,
				'total_count'        => $posts_count,
				'incomplete_results' => $posts_count > $posts_per_page,
				'posts_per_page'     => $posts_per_page,
			)
		);
		
	}
	
	private function get_additional_wp_query_args( $field_slug, $post_type ) {
		$query_arguments = new Toolset_Potential_Association_Query_Arguments();
		
		$query_arguments->addFilter( 
			new Types_Potential_Association_Query_Filter_Posts_Author_For_Post_Reference( $field_slug, $post_type ) 
		);

		// TODO create an equivalent of Types_Potential_Association_Query_Filter_Posts_Status and use it here.
		
		$additional_query_arguments = $query_arguments->get();
		
		return toolset_ensarr( toolset_getarr( $additional_query_arguments, 'wp_query_override' ) );
	}
	
}
