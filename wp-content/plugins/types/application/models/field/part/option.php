<?php

/**
 * Class Types_Field_Part_Option
 *
 * @since 2.3
 */
class Types_Field_Part_Option implements Types_Field_Part_Interface, Types_Interface_Value {
	/**
	 * @var Types_Field_Interface
	 */
	private $field;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $store_value;

	/**
	 * The user defined value, which should be shown if the checkbox is checked
	 * @var string
	 */
	private $display_value_checked;

	/**
	 * The user defined value, which should be shown if the checkbos is unchecked
	 * @var string
	 */
	private $display_value_unchecked;

	/**
	 * Value which is stored in the database
	 * @var string|bool
	 */
	private $checked;

	/**
	 * Field data
	 *
	 * @var array
	 * @since 3.0
	 */
	private $data;

	/**
	 * Types_Field_Part_Option constructor.
	 *
	 * @param Types_Field_Interface $field
	 * @param $data
	 */
	public function __construct( Types_Field_Interface $field, $data ) {
		$this->field = $field;

		$this->set_id( $data );
		$this->set_title( $data );
		$this->set_stored_value( $data );
		$this->set_display_value_checked( $data );
		$this->set_display_value_unchecked( $data );
		$this->set_checked( $data );
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}
	
	/**
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Gets the option value
	 *
	 * @return string
	 */
	public function get_value() {
		if ( $this->is_active() || $this->field instanceof Types_Field_Type_Checkboxes || $this->field instanceof Types_Field_Type_Checkbox ) {

			if ( $this->field instanceof Types_Field_Type_Checkbox || $this->field instanceof Types_Field_Type_Checkboxes ) {
				$display_mode = $this->field instanceof Types_Field_Type_Checkbox
					? $this->field->get_display_mode()
					: ( isset( $this->data['display'] ) ? $this->data['display'] : Types_Field_Abstract::DISPLAY_MODE_DB );
				if ( $display_mode === Types_Field_Abstract::DISPLAY_MODE_DB ) {
					return $this->checked;
				} else {
					return $this->is_active()
						? $this->display_value_checked
						: $this->display_value_unchecked;
				}
			}

			if ( $this->field instanceof Types_Field_Type_Radio
					&& $this->field->get_display_mode() === Types_Field_Abstract::DISPLAY_MODE_DB ) {
				return $this->title;
			}

			return ! empty( $this->display_value_checked )
				? $this->display_value_checked
				: $this->store_value;
		}

		if ( ! empty( $this->display_value_unchecked ) ) {
			return $this->display_value_unchecked;
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function get_value_raw() {
		return $this->checked;
	}

	/**
	 * @return bool
	 */
	public function is_active() {
		return $this->checked ? true : false;
	}

	/**
	 * @param $data
	 */
	private function set_id( $data ) {
		if ( ! isset( $data['id'] ) ) {
			throw new InvalidArgumentException( 'Types_Field_Part_Option requires "id".' );
		}

		$this->id = $data['id'];
	}

	/**
	 * @param $data
	 */
	private function set_checked( $data ) {
		if ( ! isset( $data['checked'] ) ) {
			$this->checked = false;

			return;
		}

		while ( is_array( $data['checked'] ) ) {
			$data['checked'] = array_shift( $data['checked'] );
		}

		$this->checked = $data['checked'];
	}

	/**
	 * @param $data
	 */
	private function set_title( $data ) {
		$this->title = isset( $data['title'] )
			? stripslashes( $data['title'] )
			: '';
	}

	/**
	 * @param $params
	 *
	 * @return mixed
	 */
	public function get_value_filtered( $params ) {
		$filtered = $this->field->get_value_filtered( $params, $this );

		return is_array( $filtered )
			? array_shift( $filtered )
			: $filtered;
	}

	/**
	 * @param $data
	 */
	private function set_stored_value( $data ) {
		$this->store_value = isset( $data['store_value'] )
			? $data['store_value']
			: '';
	}

	/**
	 * @param $data
	 */
	private function set_display_value_checked( $data ) {
		$this->display_value_checked = isset( $data['display_value_checked'] )
			? stripslashes( $data['display_value_checked'] )
			: '';

		return $data;
	}

	/**
	 * @param $data
	 */
	private function set_display_value_unchecked( $data ) {
		$this->display_value_unchecked = isset( $data['display_value_unchecked'] )
			? stripslashes( $data['display_value_unchecked'] )
			: '';

		return $data;
	}
}
