<?php

/**
 * Related Posts. Elements related to a specific post.
 *
 * @since m2m
 */
class Types_Viewmodel_Related_Content_Post extends Types_Viewmodel_Related_Content {


	/**
	 * The number of rows found
	 *
	 * @var int
	 * @since m2m
	 */
	private $rows_found;


	/**
	 * Stores the arguments of get_related_content() in order to use them in the filters
	 *
	 * @var array
	 * @see get_related_content
	 * @since m2m
	 */
	private $get_related_content_arguments;


	/**
	 * Returns the related posts
	 *
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post type.
	 * @param int    $page_number Page number.
	 * @param int    $items_per_page Limit.
	 * @param string $role_name Needed for subqueries.
	 * @param string $sort ASC or DESC.
	 * @param string $sort_by The field name or 'displayName'.
	 * @param string $sort_origin The origin of the field: post field, relationship field or post_title.
	 *
	 * @return array Related posts.
	 * @throws InvalidArgumentException If arguments are not passed.
	 * @since m2m
	 */
	public function get_related_content(
		$post_id = null,
		$post_type = '',
		$page_number = 1,
		$items_per_page = 0,
		$role_name = null,
		$sort = null,
		$sort_by = null,
		$sort_origin = null
	) {
		if ( empty( $post_id ) || empty( $post_type ) ) {
			throw new InvalidArgumentException( 'Invalid post id or type.' );
		}

		$role = Toolset_Relationship_Role::parent_or_child_from_name( $role_name );

		if ( ! $items_per_page ) {
			$items_per_page = Types_Page_Extension_Meta_Box_Related_Content::SCREEN_OPTION_PER_PAGE_DEFAULT_VALUE;
		}

		$query = $this->get_association_query();
		$query->add( $query->relationship( $this->relationship ) )
			->need_found_rows()
			->offset( ( $page_number - 1 ) * $items_per_page )
			->add( $query->element_id_and_domain(
				$post_id, Toolset_Element_Domain::POSTS, $role
			) )
			->limit( $items_per_page );

		if ( $sort ) {
			$query->order( $sort );
		}

		// Sorting.
		if ( $sort_origin ) {
			$field_definition_factory = Toolset_Field_Definition_Factory_Post::get_instance();
			switch ( $sort_origin ) {
				case 'post_title':
					$query->order_by_title( $role->other() );
					break;
				case 'post':
					$query->order_by_field_value(
						$field_definition_factory->load_field_definition( $sort_by ), $role->other()
					);
					break;
				case 'relationship':
					$query->order_by_field_value(
						$field_definition_factory->load_field_definition( $sort_by ),
						new Toolset_Relationship_Role_Intermediary()
					);
					break;
			}
		}

		$associations = $query->get_results();
		$this->rows_found = $query->get_found_rows();

		$result = $this->get_related_content_data( $associations );
		return $result;
	}


	/**
	 * Get related posts data
	 *
	 * @param IToolset_Association[] $associations Array of related content.
	 * @return array
	 * @since m2m
	 */
	private function get_related_content_data( $associations ) {
		$related_posts = array();
		foreach ( $associations as $association ) {
			// The related post.
			try {
				$post = $association->get_element( Toolset_Relationship_Role::role_from_name( $this->related_element_role ) );
				$fields = $association->get_fields();
				$uid = $association->get_uid();
			} catch ( Toolset_Element_Exception_Element_Doesnt_Exist $e ) {
				// An element was supposed to be in the database but it's missing. We're going to
				// report a data integrity issue and skip it.
				do_action(
					'toolset_report_m2m_integrity_issue',
					new Toolset_Relationship_Database_Issue_Missing_Element(
						$e->get_domain(),
						$e->get_element_id()
					)
				);

				continue;
			}
			$association_query = $this->get_association_query();
			$association_is_enabled = new Types_Page_Extension_Related_Content_Direct_Edit_Status( $uid, null, $association_query );
			$intermediary_id = $association->get_intermediary_id();
			$related_posts[] = array(
				'uid' => $uid,
				'enable_post_fields_editing' => $association_is_enabled->get(),
				'role' => $this->related_element_role,
				'post' => $post,
				'fields' => $fields,
				'relatedPosts' => $this->get_related_posts( $post ),
				'has_intermediary_fields' => ( $fields && count( $fields ) > 0 ),
				'flag' => $this->get_language_flag( $post->get_id() ),
				'fieldsFlag' => $intermediary_id? $this->get_language_flag( $intermediary_id ) : '',
			);
		}
		return $related_posts;
	}


	/**
	 * Returns the number of rows found
	 *
	 * @return integer
	 * @since m2m
	 */
	public function get_rows_found() {
		return $this->rows_found;
	}


	/**
	 * Gets a related posts using its UID
	 *
	 * @param int $association_uid Used to get only a specific related content.
	 * @return array Related post.
	 * @since m2m
	 */
	public function get_related_content_from_uid( $association_uid ) {
		$association_query = $this->get_association_query();
		$association_query->add( $association_query->association_id( $association_uid ) );
		$association = $association_query->get_results();
		return $this->get_related_content_data( $association );
	}


	/**
	 * Gets a related posts using its UID
	 *
	 * @param int $association_uid Used to get only a specific related content.
	 * @return array Related posts.
	 * @since m2m
	 */
	public function get_related_content_from_uid_array( $association_uid ) {
		$associations = $this->get_related_content_from_uid( $association_uid );
		return $this->format_related_content_array( $associations );
	}


	/**
	 * Gets the related posts as an array for using in the admin frontend
	 *
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post type.
	 * @param int    $page_number Page number.
	 * @param int    $items_per_page Limit.
	 * @param string $role Needed for subqueries.
	 * @param string $sort ASC or DESC.
	 * @param string $sort_by The field name or 'displayName'.
	 * @param string $sort_origin The origin of the field: post field, relationship field or post_title.
	 * @return array
	 * @since m2m
	 */
	public function get_related_content_array( $post_id = null, $post_type = '', $page_number = 1, $items_per_page = 0, $role = null, $sort = 'ASC', $sort_by = 'displayName', $sort_origin = 'post_title' ) {
		// Data represents the items and columns the info for the table.
		$related_posts_array = array(
			'data' => array(),
			'columns' => $this->get_fields(),
			'fieldsListing' => $this->get_fields_listing( $role ),
		);
		$related_posts = $this->get_related_content( $post_id, $post_type, $page_number, $items_per_page, $role, $sort, $sort_by, $sort_origin );
		$related_posts_array['data'] = $this->format_related_content_array( $related_posts );
		return $related_posts_array;
	}


	/**
	 * Formats an array of related posts
	 *
	 * @param array $related_posts Array of associations.
	 * @return array
	 * @since m2m
	 */
	private function format_related_content_array( $related_posts ) {
		$items = array();
		foreach ( $related_posts as $related_post ) {
			$item = array();
			$item['association_uid'] = $related_post['uid'];
			// Not needed when updating fields.
			$item['enable_post_fields_editing'] = isset( $related_post['enable_post_fields_editing'] )
				? $related_post['enable_post_fields_editing']
				: false;
			$item['role'] = $related_post['role'];
			/** @var IToolset_Element $related_post_object */
			$related_post_object = $related_post['post'];
			$post_id = $related_post_object->get_id();
			$post = get_post( $post_id );
			$item['post_id'] = $post_id;
			$item['displayName'] = $post->post_title;
			$item['editPage'] = get_edit_post_link( $post_id, false );
			$item['strings'] = $this->get_js_strings( $related_post );
			// Post fields. It gets posts fields and relationship fields.
			$item['fields'] = array(
				'post' => $related_post_object->get_fields(),
				'relationship' => $related_post['fields'],
			);
			$item['relatedPosts'] = $related_post['relatedPosts'];
			$item['has_intermediary_fields'] = $related_post['has_intermediary_fields'];
			$item['flag'] = $related_post['flag'];
			$item['fieldsFlag'] = $related_post['fieldsFlag'];

			$items[] = $item;
		}
		return $items;
	}

	/**
	 * Returns the strings for the knockout
	 *
	 * @param array $association The related content.
	 * @return array
	 * @since m2m
	 */
	private function get_js_strings( $association ) {
		$strings = array();
		$post_type = $association['post']->get_type();
		$post_type_object = get_post_type_object( $post_type );
		$strings['titles'] = array();
		// translators: Post type singular label.
		$strings['titles']['postHeading'] = sprintf( __( '%s fields', 'wpcf' ), $post_type_object->labels->singular_name );
		$strings['titles']['postTitleLabel'] = $post_type_object->labels->singular_name;
		return $strings;
	}


	/**
	 * Gets the columns data from the fields
	 *
	 * @return array The fields definition data for table columns.
	 * @since m2m
	 */
	private function get_fields() {
		$columns = array(
			'post' => array(),
			'relationship' => array(),
			'relatedPosts' => array(),
		);
		// Post Fields.
		$element_type = $this->relationship->get_element_type( $this->related_element_role );
		$post_types = $element_type->get_types();
		if ( $this->constants->constant( 'Toolset_Field_Utils::DOMAIN_POSTS' ) === $element_type->get_domain() ) {
			$field_group_post_factory = Toolset_Field_Group_Post_Factory::get_instance();
			$field_groups = $field_group_post_factory->get_groups_by_post_type( $post_types[0] );
			foreach ( $field_groups as $field_group ) {
				$definitions = $field_group->get_field_definitions();
				foreach ( $definitions as $definition ) {
					$columns['post'][] = array(
						'slug' => $definition->get_slug(),
						'displayName' => $definition->get_name(),
						'fieldType' => $definition->get_type()->get_slug(),
					);
				}
			}
		}
		// Relationship fields.
		$fields = $this->relationship->get_association_field_definitions();
		foreach ( $fields as $field ) {
			$columns['relationship'][] = array(
				'slug' => $field->get_slug(),
				'displayName' => $field->get_name(),
				'fieldType' => $field->get_type()->get_slug(),
			);
		}
		// Related posts.
		$actual_element_type = $this->relationship->get_element_type( $this->role );
		$actual_post_types = $actual_element_type->get_types();
		$relationship_query = $this->get_relationship_query();
		$cardinality = $relationship_query->cardinality();
		$relationship_query->add( $relationship_query->has_domain_and_type( $post_types[0], 'posts', new Toolset_Relationship_Role_Child() ) )
			->add( $relationship_query->exclude_relationship( $this->relationship ) )
			->add( $relationship_query->do_or(
				$relationship_query->has_cardinality( $cardinality->one_to_many() ),
				$relationship_query->has_cardinality( $cardinality->one_to_one() )
			) )
			->add( $relationship_query->exclude_type( $actual_post_types[0] ) );
		// Used to avoid post types duplications.
		$used_post_types = array();
		foreach ( $relationship_query->get_results() as $relationship ) {
			$parent_types = $relationship->get_element_type( new Toolset_Relationship_Role_Parent() )->get_types();
			foreach ( $parent_types as $parent_type ) {
				if ( in_array( $parent_type, $used_post_types, true ) ) {
					continue;
				}
				$parent_type_object = get_post_type_object( $parent_type );
				$columns['relatedPosts'][] = array(
					'slug' => $parent_type,
					'displayName' => $parent_type_object->labels->singular_name,
					'fieldType' => 'relatedPost',
				);
				$used_post_types[] = $parent_type;
			}
		}
		return $columns;
	}


	/**
	 * Get post WPML language flag <img>
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 * @since m2m
	 */
	private function get_language_flag( $post_id ) {
		if ( ! Toolset_Wpml_Utils::is_post_type_translatable( get_post_type( $post_id ) ) ) {
			return '';
		}
		$flag_url = apply_filters( 'wpml_post_language_flag_url', '', $post_id );
		if ( $flag_url ) {
			return '<img src="' . esc_attr( $flag_url ) . '" /> ';
		}
		return '';
	}


	/**
	 * Returns the list of fields to be displayed
	 *
	 * @param string $role_name Role name.
	 * @return array
	 * @since m2m
	 */
	private function get_fields_listing( $role_name ) {
		$role = Toolset_Relationship_Role::parent_or_child_from_name( $role_name );
		$post_type = $this->relationship->get_element_type( $role->other() )->get_types();
		$post_type = reset( $post_type );
		$ipt = $this->relationship->get_intermediary_post_type();

		$post_fields_selected = new Types_Post_Type_Relationship_Settings( $post_type, $this->relationship );
		$relationship_fields_selected = new Types_Post_Type_Relationship_Settings( $ipt, $this->relationship );
		$related_posts_columns_selected = new Types_Post_Type_Relationship_Related_Posts_Settings( $post_type, $this->relationship );

		return array(
			'post' => $post_fields_selected->get_fields_list_related_content(),
			'relationship' => $relationship_fields_selected->get_fields_list_related_content(),
			'relatedPosts' => $related_posts_columns_selected->get_fields_list_related_content(),
		);
	}


	/**
	 * Returns related posts associated to a post only if post is a child of a 1-to-many or 1-to-1 relationship, or it has a RPF
	 *
	 * @param IToolset_Element $post Toolset Post object.
	 * @return array
	 * @since m2m
	 */
	private function get_related_posts( $post ) {
		$query = $this->get_association_query();
		$query->add( new Toolset_Association_Query_Condition_Exclude_Relationship( $this->relationship->get_row_id() ) )
			->add(
				$query->do_or(
					$query->do_and(
						$query->element_id( $post->get_id(), new Toolset_Relationship_Role_Child() ),
						$query->has_origin( Toolset_Relationship_Origin_Wizard::ORIGIN_KEYWORD )
					),
					$query->do_and(
						$query->element_id( $post->get_id(), new Toolset_Relationship_Role_Parent() ),
						$query->has_origin( Toolset_Relationship_Origin_Post_Reference_Field::ORIGIN_KEYWORD )
					)
				)
			)
			->limit( 100 ); // Not best solution.
		$results = $query->get_results();
		// Getting data.
		$related_posts = array();
		foreach ( $results as $association ) {
			$parent_post_id = $association->get_element_id( new Toolset_Relationship_Role_Parent() );
			$parent_post = get_post( $parent_post_id );
			$related_posts[  $parent_post->post_type ] = array(
				'post_id' => $parent_post_id,
				'displayName' => $parent_post->post_title,
				'editPage' => get_edit_post_link( $parent_post_id, false ),
			);
		}
		return $related_posts;
	}
}
