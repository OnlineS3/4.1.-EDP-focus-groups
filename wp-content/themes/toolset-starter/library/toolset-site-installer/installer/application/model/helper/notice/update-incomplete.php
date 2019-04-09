<?php


class TT_Helper_Notice_Update_Incomplete extends TT_Helper_Notice_Abstract
{
    protected $template;

	public function init() {
		$this->template = TT_INSTALLER_DIR . '/application/view/theme/admin/notice/theme-update-not-complete.phtml';

		if( class_exists( 'Toolset_Admin_Notices_Manager' ) && class_exists( 'Toolset_Admin_Notice_Dismissible' ) ) {
			$this->template = TT_INSTALLER_DIR . '/application/view/theme/admin/notice/toolset/theme-update-not-complete.phtml';
		}

		parent::init();
	}
}
