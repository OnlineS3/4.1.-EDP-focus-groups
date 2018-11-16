<?php

/**
 * Class Types_View_Decorator_Separator
 *
 * @since 2.3
 */
class Types_View_Decorator_Separator implements Types_Interface_Value {

	/**
	 * @param string $value
	 * @param array $params
	 *
	 * @return string
	 */
	public function get_value( $value = '', $params = array() ) {
		if( ! is_array( $value ) ) {
			return $value;
		}

		return implode( $this->get_separator( $params ), $value );
	}

	/**
	 * @param $params
	 *
	 * @return string
	 */
	private function get_separator( $params ) {
		if( ! isset( $params['separator'] ) ) {
			return ' ';
		}

		return $params['separator'];
	}
}