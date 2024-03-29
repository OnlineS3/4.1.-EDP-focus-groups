<?php

/**
 * Class Types_Field_Type_File_View_Frontend
 *
 * Handles view specific tasks for field "File"
 *
 * @since 2.3
 */
class Types_Field_Type_File_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {
	/**
	 * Types_Field_Type_Single_Line_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_File $entity
	 * @param array $params
	 */
	public function __construct( Types_Field_Type_File $entity, $params = array() ) {
		$this->entity = $entity;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		if ( ( ! isset( $this->params['output'] ) || $this->params['output'] != 'raw' ) && ( ! isset( $this->params['raw'] ) || 'true' !== $this->params['raw'] ) ) {
			$this->add_decorator( new Types_View_Decorator_Link() );
		}

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
