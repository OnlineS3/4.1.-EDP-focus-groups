<?php

/**
 * Class Types_Field_Type_Audio_View_Frontend
 *
 * Handles view specific tasks for field "Audio"
 *
 * @since 2.3
 */
class Types_Field_Type_Audio_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {
	/**
	 * @var Types_Interface_Value
	 */
	private $decorator_audio;

	/**
	 * Types_Field_Type_Audio_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Audio $entity
	 * @param $params
	 */
	public function __construct( Types_Field_Type_Audio $entity, $params = array() ) {
		$this->entity = $entity;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		if( ( ! isset( $this->params['output'] ) || $this->params['output'] != 'raw' ) && ( ! isset( $this->params['raw'] ) || 'true' !== $this->params['raw'] ) ) {
			$this->add_decorator( $this->get_decorator_audio() );
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

	/**
	 * @param Types_Interface_Value $decorator
	 */
	public function set_decorator_audio( Types_Interface_Value $decorator ) {
		$this->decorator_audio = $decorator;
	}

	/**
	 * Default class for audio decorator
	 *
	 * @return Types_Interface_Value|Types_View_Decorator_Audio
	 */
	private function get_decorator_audio() {
		if( $this->decorator_audio !== null ) {
			return $this->decorator_audio;
		}

		return $this->decorator_audio = new Types_View_Decorator_Audio();
	}
}
