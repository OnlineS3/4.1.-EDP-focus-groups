<?php

/**
 * Class Types_Field_Type_Multiple_Lines_View_Frontend
 *
 * Handles view specific tasks for field "Multilines"
 *
 * @since 2.3
 */
class Types_Field_Type_Multiple_Lines_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {

	/**
	 * Types_Field_Type_Multiple_Lines_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Multiple_Lines $entity
	 * @param $params
	 */
	public function __construct( Types_Field_Type_Multiple_Lines $entity, $params = array() ) {
		$this->entity = $entity;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		$rendered_value = array();
		$is_filter_used = serialize( $this->entity->get_value() ) != serialize( $this->entity->get_value_filtered( $this->params ) );

		foreach( (array) $this->entity->get_value_filtered( $this->params ) as $value ) {
			$rendered = $is_filter_used || ( ( isset( $this->params['output'] ) && $this->params['output'] == 'raw' ) || ( isset( $this->params['raw'] ) && 'true' !== $this->params['raw'] ) )
				? $value
				: wpautop( $value );

			$rendered_value[] = $this->filter_field_value_after_decorators( $rendered, $value );
		}

		return $this->to_string( $rendered_value );
	}
}
