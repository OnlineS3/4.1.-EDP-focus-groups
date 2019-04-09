<?php


abstract class TT_Helper_Notice_Abstract
{
    protected $template;
	public function __construct()
	{
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		if( class_exists( 'Toolset_Admin_Notices_Manager' ) && class_exists( 'Toolset_Admin_Notice_Dismissible' ) ) {
			$notice = new Toolset_Admin_Notice_Dismissible( 'installation-not-complete' );
			$notice->set_content( $this->template );
			Toolset_Admin_Notices_Manager::add_notice( $notice );

			return;
		}

		add_action('admin_notices', array( $this, 'render' ));
	}

	public function render()
	{
		if ($this->template !== null && file_exists($this->template)) {
			include_once($this->template);
		}
	}
}
