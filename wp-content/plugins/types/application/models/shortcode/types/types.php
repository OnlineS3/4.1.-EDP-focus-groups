<?php

/**
 * Class Types_Shortcode_Types
 *
 * @since 2.3
 */
class Types_Shortcode_Types implements Types_Shortcode_Interface {

	const SHORTCODE_NAME = 'types';

	// quite long as all fields are covered by one shortcode
	private $shortcode_atts = array(
		'field'        => null, // field slug for post fields
		'termmeta'     => null, // field slug for term fields
		'usermeta'     => null, // field slug for user fields
		'item'         => null, // post
		'id'           => null, // synonym for 'item'
		'post_id'      => null, // synonym for 'item'
		'output'       => null, // all fields (raw output)
		'raw'          => null, // @deprecated see 'output'
		'separator'    => null, // all repeatable fields
		'style'        => null, // date
		'format'       => null, // date
		'title'        => null, // image
		'loop'         => null, // video / audio
		'autoplay'     => null, // video / audio
		'preload'      => null, // video / audio
		'option'       => null, // checkbox
		'state'        => null, // checkbox
		'width'        => null, // image / video
		'height'       => null, // image / video
		'alt'          => null, // image
		'size'         => null, // image
		'resize'       => null, // image
		'align'        => null, // image
		'button_style' => null, // skype
		'no_protocol'  => null, // url
		'poster'       => null, // video
		'url'          => null, // image
		'class'        => null, // image
		'onload'       => null, // image
		'state'        => null, // checkbox
		'show_name'    => null, // checkbox
		'target'       => null, // url
		'user_id'      => null, // usermeta option
		'user_name'    => null, // usermeta option
		'user_current' => null, // usermeta option
		'current_user' => null, // usermeta option
		'term_id'      => null, // termmeta option
		'index'        => null, // zero-based index for repeating fields
	);


	private $user_content;
	private $user_atts;

	/**
	 * @var Toolset_Shortcode_Attr_Interface
	 */
	private $field;

	/**
	 * @var Toolset_Shortcode_Attr_Interface
	 */
	private $item;

	/**
	 * Types_Shortcode_Types constructor.
	 *
	 * @param Toolset_Shortcode_Attr_Interface $field
	 * @param Toolset_Shortcode_Attr_Interface $item
	 */
	public function __construct(
		Toolset_Shortcode_Attr_Interface $field,
		Toolset_Shortcode_Attr_Interface $item
	) {
		$this->field = $field;
		$this->item  = $item;
	}

	public function get_value( $atts, $content = null ) {
		$this->user_atts    = shortcode_atts( $this->shortcode_atts, $atts );
		$this->user_content = $content;

		if ( ! $item_id = $this->item->get( $this->user_atts ) ) {
			// no valid item
			throw new Exception_Invalid_Shortcode_Attr_Item();
		}

		if ( ! $field = $this->field->get( $this->user_atts ) ) {
			// no valid field
			throw new Exception_Invalid_Shortcode_Attr_Field();
		}

		$this->user_atts['item'] = $item_id;

		return types_render_field( $field, $this->user_atts, $this->user_content );
	}
}
