<?php

/**
 * Class Types_Field_Type_Image_View_Frontend
 *
 * Handles view specific tasks for field "Image"
 *
 * @since 2.3
 */
class Types_Field_Type_Image_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {

	/**
	 * @var Types_Wordpress_Media_Interface
	 */
	private $wordpress_media;

	/**
	 * @var Types_Media_Service
	 */
	private $media_service;

	/**
	 * @var Types_Site_Domain
	 */
	private $site_domain;

	/**
	 * @var Types_View_Placeholder_Interface
	 */
	private $view_placeholder;

	/**
	 * @var Types_View_Decorator_Image
	 */
	private $decorator_image;

	/**
	 * Types_Field_Type_Single_Line_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Image $entity
	 * @param Types_Wordpress_Media $wordpress_media
	 * @param Types_Media_Service $media_service
	 * @param Types_Site_Domain $site_domain
	 * @param Types_View_Placeholder_Media $view_placeholder_media
	 * @param Types_View_Decorator_Image $decorator_image
	 * @param array $params
	 */
	public function __construct(
		Types_Field_Type_Image $entity,
		Types_Wordpress_Media $wordpress_media,
		Types_Media_Service $media_service,
		Types_Site_Domain $site_domain,
		Types_View_Placeholder_Media $view_placeholder_media,
		Types_View_Decorator_Image $decorator_image,
		$params = array()
	) {
		$this->entity           = $entity;
		$this->wordpress_media  = $wordpress_media;
		$this->media_service    = $media_service;
		$this->site_domain      = $site_domain;
		$this->view_placeholder = $view_placeholder_media;
		$this->decorator_image  = $decorator_image;

		$this->prepare_params( $params );
	}

	/**
	 * @return string
	 */
	public function get_value() {
		if ( ( isset( $this->params['output'] ) && 'raw' === $this->params['output'] ) || ( isset( $this->params['raw'] ) && 'true' === $this->params['raw'] ) ) {
			// raw value
			$rendered_value = $this->entity->get_value_filtered( $this->params );
		} else {
			$is_filter_used = serialize( $this->entity->get_value() ) != serialize( $this->entity->get_value_filtered( $this->params ) );

			if ( $is_filter_used ) {
				foreach ( (array) $this->entity->get_value_filtered( $this->params ) as $value ) {
					$rendered_value[] = $this->filter_field_value_after_decorators( $value, $value );
				}
			} else {
				$rendered_value = $this->get_images();
			}
		}

		if ( empty( $rendered_value ) ) {
			return '';
		}

		if ( $this->entity->is_repeatable() ) {
			if ( null === toolset_getarr( $this->params, 'index' )
				|| '' === toolset_getarr( $this->params, 'index' ) ) {
			$decorator_separator = new Types_View_Decorator_Separator();
			$rendered_value      = $decorator_separator->get_value( $rendered_value, $this->params );
			} else {
				$decorator_index = new Types_View_Decorator_Index();
				$rendered_value = $decorator_index->get_value( $rendered_value, $this->params );
			}
		}

		return implode( '', (array) $rendered_value );
	}


	/**
	 * @return array
	 */
	private function get_images() {
		$rendered_value = array();

		foreach ( (array) $this->entity->get_value() as $value ) {
			$media = $this->media_service->find_by_url( $value );

			$view_params          = $this->params;
			$view_params['title'] = $this->view_placeholder->replace( $this->params['title'], $media );
			$view_params['alt']   = $this->view_placeholder->replace( $this->params['alt'], $media );
			$view_params['class'] = implode( ' ', (array) $this->params['class'] );
			$view_params['style'] = implode( ' ', (array) $this->params['style'] );

			if ( $modified_url = $this->handle_registered_image( $media ) ) {
				$rendered = $this->decorator_image->get_value( $modified_url, $view_params );
			} else if ( $modified_url = $this->handle_resized_image( $media ) ) {
				$rendered = $this->decorator_image->get_value( $modified_url, $view_params );
			} else {
				$rendered = $this->decorator_image->get_value( $media->get_url(), $view_params );
			}

			$rendered_value[] = $this->filter_field_value_after_decorators( $rendered, $value );
		}

		return $rendered_value;
	}

	/**
	 * Internal means the image is registered in the database
	 *
	 * @param Types_Interface_Media $media
	 *
	 * @return false|string
	 */
	private function handle_registered_image( Types_Interface_Media $media ) {
		if ( ! $media->get_id() ) {
			return false;
		}

		if ( $this->params['url'] == 'true' ) {
			// user wants blank url
			if ( ! empty( $this->params['size'] )
			     && $image = wp_get_attachment_image_src( $media->get_id(), $this->params['size'] )
			) {
				// specific size wanted and available
				return $image[0];
			}

			return $media->get_url();
		}

		if ( ! empty( $params['size'] ) && $params['resize'] == 'crop' ) {
			return wp_get_attachment_image(
				$media->get_id(),
				$this->params['size'],
				false,
				array(
					'title' => $this->view_placeholder->replace( $this->params['title'], $media ),
					'alt'   => $this->view_placeholder->replace( $this->params['alt'], $media ),
					'class' => implode( ' ', (array) $this->params['class'] ),
					'style' => implode( ' ', (array) $this->params['style'] ),
				)
			);
		}

		return false;
	}

	/**
	 * @param Types_Interface_Media $media
	 *
	 * @return bool
	 */
	private function handle_resized_image( Types_Interface_Media $media ) {
		if ( ! empty( $this->params['width'] ) || ! empty( $this->params['height'] ) ) {
			$args = array(
				'resize'        => $this->params['resize'],
				'padding_color' => $this->params['padding_color'],
				'width'         => $this->params['width'],
				'height'        => $this->params['height'],
			);

			return $this->media_service->resize_image( $media, $args, $this->site_domain->contains( $media ) );
		}

		return false;
	}


	/**
	 * Fill user params with default params
	 *
	 * @param $params
	 */
	private function prepare_params( $params ) {
		$this->params = array_merge( array(
			'alt'           => false,
			'title'         => false,
			'style'         => '',
			'size'          => false,
			'url'           => false,
			'onload'        => false,
			'padding_color' => '#fff'
		), $params );

		// class
		$this->params['class'] = array();

		if ( isset( $params['size'] ) && ! empty( $params['size'] ) ) {
			$this->params['class'][] = 'attachment-' . $params['size'];
		}

		if ( isset( $params['align'] ) && ! empty( $params['align'] ) && $params['align'] != 'none' ) {
			$this->params['class'][] = 'align' . $params['align'];
		}

		if ( isset( $params['class'] ) && ! empty( $params['class'] ) ) {
			$this->params['class'][] = $params['class'];
		}

		// 'proportional' is just for backwards compatibility (was replaced by 'resize')
		$proportional = isset( $params['proportional'] ) && $params['proportional'] == 'true'
			? 'proportional'
			: 'crop';

		$this->params['resize'] = isset( $params['resize'] ) ? $params['resize'] : $proportional;

		// width and height
		$this->params['width']  = isset( $params['width'] ) ? $params['width'] : false;
		$this->params['height'] = isset( $params['height'] ) ? $params['height'] : false;

		if ( ! $this->params['width'] && ! $this->params['height'] && $this->params['size'] ) {
			switch ( $this->params['size'] ) {
				case 'thumbnail':
					$width  = get_option( 'thumbnail_size_w' );
					$height = get_option( 'thumbnail_size_h' );
					break;

				case 'medium':
					$width  = get_option( 'medium_size_w' );
					$height = get_option( 'medium_size_h' );
					break;

				case 'large':
					$width  = get_option( 'large_size_w' );
					$height = get_option( 'large_size_h' );
					break;

				default:
					if ( $size = $this->wordpress_media->get_addtional_image_sizes( $params['size'] ) ) {
						$width  = $size['width'];
						$height = $size['height'];
					}

			}
		}
		if ( isset( $width ) && $width ) {
			$this->params['width'] = $width;
		}

		if ( isset( $height ) && $height ) {
			$this->params['height'] = $height;
		}
	}
}
