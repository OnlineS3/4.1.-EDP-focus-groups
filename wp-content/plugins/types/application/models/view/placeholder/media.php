<?php

/**
 * Class Types_View_Placeholder_Media
 *
 * @since 2.3
 */
class Types_View_Placeholder_Media implements Types_View_Placeholder_Interface {

	/**
	 * @param string|array $string
	 * @param null|Types_Interface_Media $media
	 *
	 * @return mixed
	 */
	public function replace( $string, $media = null ) {
		if ( ( ! is_string( $string ) && ! is_array( $string ) )
		     || ! $media instanceof Types_Interface_Media ) {
			return $string;
		}

		$supported_replacements = array(
			'%%TITLE%%'       => $media->get_title(),
			'%%CAPTION%%'     => $media->get_caption(),
			'%%ALT%%'         => $media->get_alt(),
			'%%DESCRIPTION%%' => $media->get_description(),
		);

		return str_replace(
			array_keys( $supported_replacements ),
			array_values( $supported_replacements ),
			$string
		);
	}
}