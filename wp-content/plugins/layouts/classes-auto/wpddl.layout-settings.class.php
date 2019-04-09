<?php

class WPDDL_Layout_Settings {

	private $post_id;
	/** @var  WPDDL_Cache */
	private $raw_cache;
	/** @var  WPDDL_Cache */
	private $decoded_cache;

	public function __construct( $post_id, WPDDL_Cache $raw_cache = null, WPDDL_Cache $decoded_cache = null ) {
		$this->post_id       = $post_id;
		if ( $raw_cache ) {
			$this->raw_cache = $raw_cache;
		} else {
			$this->raw_cache = new WPDDL_Cache( 'temp_raw');
		}
		if ( $decoded_cache ) {
			$this->decoded_cache = $decoded_cache;
		} else {
			$this->decoded_cache = new WPDDL_Cache( 'temp_decoded' );
		}
	}

	public function get( $as_array = false, $clear_cache = false ) {

		if ( ! $this->raw_cache->has( $this->post_id ) || $clear_cache ) {

			$this->raw_cache->set( $this->post_id, get_post_meta( $this->post_id, WPDDL_LAYOUTS_SETTINGS, true ) );

		}

		if ( $as_array && ( ! $this->decoded_cache->has( $this->post_id ) || $clear_cache ) ) {

			if ( $this->raw_cache->has( $this->post_id ) ) {
				$this->decoded_cache->set( $this->post_id, json_decode( $this->raw_cache->get( $this->post_id ) ) );
			} else {
				$this->decoded_cache->set( $this->post_id, null );
			}
		}

		if ( $as_array ) {
			return $this->decoded_cache->get( $this->post_id );
		} else {
			return $this->raw_cache->get( $this->post_id );
		}
	}

	public function update( $settings, $original = '' ) {
		$meta_key = WPDDL_LAYOUTS_SETTINGS;

		if ( is_string( $settings ) ) {
			$settings = wp_json_encode( json_decode( $settings, true ) );
		} else if ( is_array( $settings ) || is_object( $settings ) ) {
			$settings = wp_json_encode( (array) $settings );
		}

		$result = update_post_meta( $this->post_id, $meta_key, addslashes( $settings ), $original );

		$this->clear_cache();

		return $result;
	}

	public function clear_cache() {
		$this->raw_cache->clear( $this->post_id );
		$this->decoded_cache->clear( $this->post_id );
	}
}
