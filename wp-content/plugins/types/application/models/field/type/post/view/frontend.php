<?php

/**
 * Class Types_Field_Type_Post_View_Frontend
 *
 * Handles view specific tasks for field "Post"
 *
 * @since 2.3
 */
class Types_Field_Type_Post_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {

	/**
	 * Types_Field_Type_Post_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Post $entity
	 * @param array $params
	 */
	public function __construct( Types_Field_Type_Post $entity, $params = array() ) {
		$this->entity = $entity;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		$rendered_value = array();
		foreach( (array) $this->entity->get_value_filtered( $this->params ) as $value ) {
			$rendered_value[] = $value;
		}

		return $this->to_string( $rendered_value );
	}
}