<?php

/**
 * Class Types_Field_Gateway_Wordpress_User
 *
 * @since 2.3
 */
class Types_Field_Gateway_Wordpress_User extends Types_Field_Gateway_Abstract {
	/**
	 * Returns all defined fields
	 * @return array
	 */
	public function get_fields() {
		return get_option( 'wpcf-usermeta', array() );
	}

	/**
	 * @param $id
	 * @param $field_slug
	 * @param bool $repeatable
	 * @param bool $controlled If it is conrtolled by Types.
	 *
	 * @return array|void
	 */
	public function get_field_user_value( $id, $field_slug, $repeatable = false, $controlled = false ) {
		$prefix = ! $controlled ? 'wpcf-' : '';
		$user_value = get_user_meta( $id, $prefix . $field_slug );

		if( $repeatable ) {
			return $user_value;
		}

		if( is_array( $user_value ) ) {
			return array_unique( $user_value, SORT_REGULAR );
		}

		return $user_value;
	}
}
