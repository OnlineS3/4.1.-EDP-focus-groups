<?php

/**
 * Main front-end controller for Types.
 * 
 * @since 2.0
 */
final class Types_Frontend {

	public static function initialize() {
		// shortcode [types]
		$factory = new Types_Shortcode_Factory();

		if( $shortcode = $factory->get_shortcode( 'types' ) ) {
			add_shortcode( 'types', array( $shortcode, 'render' ) );
		};
	}

}