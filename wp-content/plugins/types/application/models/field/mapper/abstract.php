<?php

/**
 * Class Types_Field_Mapper_Abstract
 *
 * @since 2.3
 */
abstract class Types_Field_Mapper_Abstract implements Types_Field_Mapper_Interface {

	/**
	 * @var Types_Field_Factory_Interface
	 */
	protected $field_factory;


	/**
	 * @var Types_Field_Gateway_Interface
	 */
	protected $gateway;

	/**
	 * Types_Field_Mapper_Abstract constructor.
	 *
	 * @param Types_Field_Factory_Interface $factory
	 * @param Types_Field_Gateway_Interface $gateway
	 */
	public function __construct( Types_Field_Factory_Interface $factory, Types_Field_Gateway_Interface $gateway ) {
		$this->field_factory = $factory;
		$this->gateway = $gateway;
	}

	/**
	 * @param $id
	 *
	 * @return null|array
	 */
	protected function database_get_field_by_id( $id ) {
		return $this->gateway->get_field_by_id( $id );
	}

	/**
	 * @param array $field
	 *
	 * @return array
	 */
	protected function map_common_field_properties( $field ) {
		if( isset( $field['name'] ) ) {
			$field['title'] = $field['name'];
		}

		if( ! isset( $field['data'] ) ) {
			// no data by the legacy structure
			return $field;
		}

		// reorder legacy structure to new
		if( isset( $field['data']['user_default_value'] ) ) {
			$field['default_value'] = $field['data']['user_default_value'];
		}

		if( isset( $field['data']['placeholder'] ) ) {
			$field['placeholder'] = $field['data']['placeholder'];
		}

		$field['repeatable'] = isset( $field['data']['repetitive'] ) && $field['data']['repetitive']
			? true
			: false;

		return $field;
	}

	/**
	 * @param $id
	 * @param $field_slug
	 *
	 * @param bool $repeatable
	 *
	 * @return array
	 */
	protected function get_user_value( $id, $field_slug, $repeatable = false, $controlled = false ) {
		return $this->gateway->get_field_user_value( $id, $field_slug, $repeatable, $controlled );
	}

	/**
	 * Returns if the field is controlled by Types
	 *
	 * @param array $field Field data.
	 * @return boolean
	 * @since 3.0
	 */
	protected function is_controlled_by_types( $field ) {
		return isset( $field['data']['controlled'] ) && $field['data']['controlled'];
	}

}
