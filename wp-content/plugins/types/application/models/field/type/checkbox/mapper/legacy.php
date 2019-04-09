<?php

/**
 * Class Types_Field_Type_Checkbox_Mapper_Legacy
 *
 * Mapper for "Checkbox" field
 *
 * @since 2.3
 */
class Types_Field_Type_Checkbox_Mapper_Legacy extends Types_Field_Mapper_Abstract {

	/**
	 * @var Types_Field_Type_Checkbox_Factory
	 */
	protected $field_factory;

	public function find_by_id( $id, $id_post ) {
		if( ! $field = $this->database_get_field_by_id( $id ) ) {
			return null;
		};

		if( $field['type'] !== 'checkbox' ) {
			throw new Exception( 'Types_Field_Type_Checkbox_Mapper_Legacy can not map type: ' . $field['type'] );
		}

		$field = $this->map_common_field_properties( $field );
		$entity = $this->field_factory->get_field( $field );

		if( isset( $field['id'] )
	        && isset( $field['name'] )
			&& isset( $field['data']['set_value'] )
			&& isset( $field['data']['display_value_selected'] )
			&& isset( $field['data']['display_value_not_selected'] )
		) {
			$controlled = $this->is_controlled_by_types( $field );
			$option = new Types_Field_Part_Option( $entity, array(
				'id' => $field['id'],
				'title' => $field['name'],
				'checked' => $this->get_user_value( $id_post, $field['slug'], $field['repeatable'], $controlled ),
				'store_value' => $field['data']['set_value'],
				'display_value_checked' => $field['data']['display_value_selected'],
				'display_value_unchecked' => $field['data']['display_value_not_selected']
			) );

			$entity->set_option( $option );
		}

		return $entity;
	}
}
