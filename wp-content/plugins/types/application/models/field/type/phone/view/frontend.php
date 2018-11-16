<?php

/**
 * Class Types_Field_Type_Phone_View_Frontend
 *
 * Handles view specific tasks for field "Phone"
 *
 * @since 2.3
 */
class Types_Field_Type_Phone_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {
	/**
	 * Types_Field_Type_Phone_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Phone $entity
	 * @param array $params
	 */
	public function __construct( Types_Field_Type_Phone $entity, $params = array() ) {
		$this->entity = $entity;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		$rendered_value = array();
		foreach( (array) $this->entity->get_value_filtered( $this->params ) as $value ) {
			$rendered_value[] = $this->filter_field_value_after_decorators(
				$this->get_decorated_value( $value ),
				$value
			);
		}

		return $this->to_string( $rendered_value );
	}
}