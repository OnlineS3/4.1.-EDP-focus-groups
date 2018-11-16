<?php

/**
 * Class Types_Field_Type_Skype_View_Frontend
 *
 * Handles view specific tasks for field "Single Line"
 *
 * @since 2.3
 */
class Types_Field_Type_Skype_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {
	/**
	 * Types_Field_Type_Skype_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Skype $entity
	 * @param array $params
	 */
	public function __construct( Types_Field_Type_Skype $entity, $params = array() ) {
		$this->entity = $entity;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		if ( ( ! isset( $this->params['output'] ) || $this->params['output'] != 'raw' ) && ( ! isset( $this->params['raw'] ) || 'true' !== $this->params['raw'] ) ) {
			$decorator_skype = ! isset( $this->params['action'] ) && isset( $this->params['button_style'] )
				? new Types_View_Decorator_Skype_Legacy()
				: new Types_View_Decorator_Skype();

			$this->add_decorator( $decorator_skype );
		}

		$rendered_value = array();
		foreach( (array) $this->entity->get_value_filtered( $this->params ) as $value ) {
			$value = is_array( $value ) && array_key_exists( 'skypename', $value )
				? $value['skypename']
				: $value;

			$rendered_value[] = $this->filter_field_value_after_decorators(
				$this->get_decorated_value( $value ),
				$value
			);
		}

		return $this->to_string( $rendered_value );
	}
}
