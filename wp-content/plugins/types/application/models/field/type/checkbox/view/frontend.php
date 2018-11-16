<?php

/**
 * Class Types_Field_Type_Checkbox_View_Frontend
 *
 * Handles view specific tasks for field "Single Line"
 *
 * @since 2.3
 */
class Types_Field_Type_Checkbox_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {

	/**
	 * Types_Field_Type_Single_Line_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Checkbox $entity
	 * @param array $params
	 */
	public function __construct( Types_Field_Type_Checkbox $entity, $params = array() ) {
		$this->entity = $entity;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		if ( ( isset( $this->params['output'] ) && 'raw' === $this->params['output'] ) || ( isset( $this->params['raw'] ) && 'true' === $this->params['raw'] ) ) {
			$value = $this->entity->get_option()->get_value_raw();
		} else if( isset( $this->params['state'] ) ) {
			$decorator = new Types_View_Decorator_Option_State( $this->entity->get_option(), $this->params );
			$value = $decorator->get_value();
		} else {
			$value = $this->entity->get_option()->get_value_filtered( $this->params );
		}

		if( isset( $this->params['show_name'] ) ) {
			$placeholder_field = new Types_View_Placeholder_Field();
			$value = $placeholder_field->replace( 'FIELD_NAME: FIELD_VALUE', $this->entity, $value );
		}

		return $this->filter_field_value_after_decorators( $value );
	}
}
