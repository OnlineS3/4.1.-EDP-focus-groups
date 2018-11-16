<?php

/**
 * Class Types_Field_Type_Number_View_Frontend
 *
 * Handles view specific tasks for field "Number"
 *
 * @since 2.3
 */
class Types_Field_Type_Number_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {
	/**
	 * Types_Field_Type_Number_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Number $entity
	 * @param array $params
	 */
	public function __construct( Types_Field_Type_Number $entity, $params = array() ) {
		$this->entity = $params['field'] = $entity;

		$this->params = $params;

		if( ! isset( $params['format'] ) || empty( $params['format'] ) ) {
			$this->params['format'] = 'FIELD_VALUE';
		}
	}

	/**
	 * @return string
	 */
	public function get_value() {
		if ( ( ! isset( $this->params['output'] ) || $this->params['output'] != 'raw' ) && ( ! isset( $this->params['raw'] ) || 'true' !== $this->params['raw'] ) ) {
			$placeholder_field = new Types_View_Placeholder_Field();
		}

		$rendered_value = array();
		foreach( (array) $this->entity->get_value_filtered( $this->params ) as $value ) {
			$rendered = isset( $placeholder_field )
				? $placeholder_field->replace( $this->params['format'], $this->entity, $value )
				: $value;

			$rendered_value[] = $this->filter_field_value_after_decorators( $rendered, $value );
		}


		return $this->to_string( $rendered_value );
	}
}
