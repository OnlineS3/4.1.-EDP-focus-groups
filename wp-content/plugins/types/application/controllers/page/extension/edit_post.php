<?php

/**
 * This controller extends all post edit pages
 *
 * @since 2.0
 */
final class Types_Page_Extension_Edit_Post {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$post = wpcf_admin_get_edited_post();
		$post_type = wpcf_admin_get_edited_post_type( $post );

		// if no post or no page
		if ( $post_type != 'post' && $post_type != 'page' ) {
			$post_type_option = new Types_Utils_Post_Type_Option();
			$custom_types = $post_type_option->get_post_types();

			// abort if also no custom post type of types
			if ( ! array_key_exists( $post_type, $custom_types ) ) {
				return false;
			}
		}

		$this->prepare();
	}

	private function __clone() { }


	public function prepare() {
		// documentation urls
		Types_Helper_Url::load_documentation_urls();

		// set analytics medium
		Types_Helper_Url::set_medium( 'post_editor' );

		// storage of repeatable groups
		$this->repeatable_group_save();

		// storage of post fields
		add_action( 'wpcf_fields_type_post_save', array( $this, 'post_reference_save' ), 10, 3 );
	}

	/**
	 * This method handles possible repeatable field groups when post is saved
	 */
	private function repeatable_group_save() {
		if ( ! isset( $_POST['types-repeatable-group'] ) ) {
			// no repeatable groups set for this post
			return;
		}

		$wpcf_field = new WPCF_Field();
		$temp_post_data = $_POST;
		$updated_posts = array();

		foreach ( $_POST['types-repeatable-group'] as $post_id => $fields ) {
			$_POST = array();

			// handle all fields except checkboxes
			$_POST['post_ID'] = $post_id;
			foreach ( $fields as $slug => $value ) {
				// restructure $_POST so it can be handled by our legacy post save hook
				$_POST['wpcf'][ $wpcf_field->get_slug_no_prefix( $slug ) ] = $_POST['wpcf'][ $slug ] = $value;
			}

			// handle checkboxes
			if ( isset( $temp_post_data['_wptoolset_checkbox'] ) ) {
				foreach ( $temp_post_data['_wptoolset_checkbox'] as $checkbox_slug => $value ) {
					$current_data = get_post_meta( $post_id, $checkbox_slug );
					$post_data = isset( $_POST['wpcf'][ $checkbox_slug ] )
						? $_POST['wpcf'][ $checkbox_slug ]
						: '';

					if ( empty( $current_data ) && empty( $post_data ) ) {
						// no checkbox set and previous data left in the database
						continue;
					}

					wpcf_fields_checkbox_update_one(
						$post_id,
						$checkbox_slug,
						$temp_post_data['types-repeatable-group'][ $post_id ]
					);
				}
			}


			// make legacy hook handle this like a child post
			add_filter( 'types_updating_child_post', '__return_true' );

			$is_wpml_tm_save_post_action_active = has_action( 'wpml_tm_save_post', 'wpml_tm_save_post' );
			// disable wpml translation job update for RFG item (it will be updated by the parent post)
			if( $is_wpml_tm_save_post_action_active ) {
				remove_action( 'wpml_tm_save_post', 'wpml_tm_save_post', 10, 3 ); // prevent creating a translation job
			}

			// run wp_update_post
			$updated_posts[] = wp_update_post( array( 'ID' => $post_id ) );

			/*
			 * Action 'toolset_post_update'
			 *
			 * @var WP_Post $affected_post
			 *
			 * @since 3.0
			 */
			$affected_post = get_post( $post_id );
			do_action( 'toolset_post_update', $affected_post );

			// re-apply temp hook removements
			if( $is_wpml_tm_save_post_action_active ) {
				add_action( 'wpml_tm_save_post', 'wpml_tm_save_post', 10, 3 );
			}

			remove_filter( 'types_updating_child_post', '__return_true' );
		}

		// re-apply original $_POST data
		$_POST = $temp_post_data;

		// Store the order of the items
		$sortorder = 1;
		foreach ( $updated_posts as $post_id ) {
			update_post_meta( $post_id, Toolset_Post::SORTORDER_META_KEY, $sortorder );
			$sortorder ++;
		}
	}

	/**
	 * This function saves the association created with post reference field
	 *
	 * @wp_action wpcf_fields_type_post_save
	 *
	 * @param $value
	 * @param $field
	 * @param WPCF_Field $wpcf_field
	 *
	 * @return mixed
	 */
	public function post_reference_save( $value, $field, $wpcf_field ) {
		$new_parent_id = $value;
		$child_id = $wpcf_field->post->ID;
		$relationship_slug = $field['data']['relationship_slug'];

		// delete previous association
		do_action( 'toolset_do_m2m_full_init' );
		$repository = Toolset_Relationship_Definition_Repository::get_instance();

		if ( ! $definition = $repository->get_definition( $relationship_slug ) ) {
			// definition could not be found, should not happen...
			return $value;
		}

		// initalize relationship
		Toolset_Relationship_Controller::get_instance()->initialize_full();

		// get association
		$query = new Toolset_Association_Query(
			array(
				Toolset_Association_Query::QUERY_RELATIONSHIP_ID => $definition->get_row_id(),
				Toolset_Association_Query::QUERY_CHILD_ID => $child_id,
				Toolset_Association_Query::OPTION_RETURN => Toolset_Association_Query::RETURN_ASSOCIATIONS
			)
		);
		$associations = $query->get_results();
		$association = array_shift( $associations );

		// if no assocation stored so far...
		if ( empty( $association ) ) {
			if ( ! empty( $new_parent_id ) ) {
				// user has set a new parent, store it
				$definition->create_association(
					get_post( $new_parent_id ),
					$wpcf_field->post
				);
			}

			return $value;
		}

		// ...there is a stored association
		$is_current_association_different_to_stored =
			$association->get_element( Toolset_Relationship_Role::PARENT )->get_id() != $new_parent_id;

		if ( $is_current_association_different_to_stored ) {
			// associated post has changed, delete previous
			$association->get_driver()->delete_association( $association );

			if ( ! empty( $new_parent_id ) ) {
				// a new post was selected
				$definition->create_association(
					get_post( $new_parent_id ),
					$wpcf_field->post
				);
			}
		}

		return $value;

	}
}