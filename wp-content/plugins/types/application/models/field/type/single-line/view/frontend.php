<?php

/**
 * Class Types_Field_Type_Single_Line_View_Frontend
 *
 * Handles view specific tasks for field "Single Line"
 *
 * @since 2.3
 */
class Types_Field_Type_Single_Line_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {
	/**
	 * Types_Field_Type_Single_Line_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Single_Line $entity
	 * @param array $params
	 */
	public function __construct( Types_Field_Type_Single_Line $entity, $params = array() ) {
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

		if( empty( $rendered_value ) ) {
			return '';
		}

		if( $this->entity->is_repeatable() ) {
			if ( null === toolset_getarr( $this->params, 'index' ) 
				|| '' === toolset_getarr( $this->params, 'index' ) ) {
				$decorator_separator = new Types_View_Decorator_Separator();
				$rendered_value = $decorator_separator->get_value( $rendered_value, $this->params );
			} else {
				$decorator_index = new Types_View_Decorator_Index();
				$rendered_value = $decorator_index->get_value( $rendered_value, $this->params );
			}
		}

		while( is_array( $rendered_value ) ) {
			$rendered_value = array_shift( $rendered_value );
		}

		return $rendered_value;
	}
}