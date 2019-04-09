<?php

/**
 * Class Types_Field_Type_Legacy_View_Frontend
 *
 * @since 2.3
 */
class Types_Field_Type_Legacy_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {
	/**
	 * @var Types_Field_Type_Legacy
	 */
	protected $entity;

	/**
	 * Types_Field_Type_Legacy_View_Frontend constructor.
	 *
	 * @param Types_Field_Interface $entity
	 * @param array $params
	 */
	public function __construct( Types_Field_Interface $entity, $params = array() ) {
		$this->entity = $entity;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		$output = '';
		$_view_func = 'wpcf_fields_' . strtolower( $this->entity->get_type() ) . '_view';
		$field_data = array_merge( $this->entity->get_data_raw(), $this->params );

		if ( is_callable( $_view_func ) ) {
			foreach( (array) $this->entity->get_value_filtered() as $value ) {
				$field_data['field_value'] = $value;
				$return_of_external_function = call_user_func( $_view_func, $field_data );

				if( ! is_array( $return_of_external_function )
				    && $return_of_external_function != '__wpcf_skip_empty' ) {
					$output .= $return_of_external_function;
				}
			}

			$output = strval( $output );
		}

		return $this->filter_field_value_after_decorators( $output );
	}
}