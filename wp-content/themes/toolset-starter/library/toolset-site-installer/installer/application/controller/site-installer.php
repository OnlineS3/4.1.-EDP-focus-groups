<?php

class TT_Controller_Site_Installer extends TT_Controller_Abstract
{
    const PAGE          = 'toolset-site-installer';
    const AJAX_ENTRY    = 'tt_ajax';

    /**
     * @var array
     */
    private $response = array(
        'message'        => '',
        'success'        => 1,
        'failed'         => 0,
        'modified_items' => 0,
    );

    /**
     * @var TT_Step_Abstract[]
     */
    private $steps = array();

    /**
     * @var TT_Step_Abstract[]
     */
    private $steps_active;

    public function __construct(TT_Settings_Interface $settings)
    {
        parent::__construct($settings);
	    
        // possible redirections (theme activation / update)
        $this->settings->getContext()->redirections();

        // ajax actions
        add_action('wp_ajax_' . self::AJAX_ENTRY, array($this, 'ajax'));

        // disable Beaver Builder Lite redirect on activation
        $this->disableBeaverBuilderRedirect();

        // full screen installer
        $this->pageFullScreenInstaller();

        if( $this->settings->getContext()->showNoticeRunInstaller() ) {
	        // possible notices
	        $this->notices();
        }
    }

    public function ajax()
    {
        if (! check_ajax_referer('tt-theme-setup', 'request_key', false) || ! current_user_can('manage_options')) {
            // todo make an ajax response here
            throw new Exception('You are not allowed todo this command.');
        }

        if (! array_key_exists('command', $_REQUEST)) {
            // todo make an ajax response here
            throw new Exception('Command must be declared');
        }

        switch ($_REQUEST['command']) {
            case 'load_step':
                return $this->ajaxLoadStep();
            case 'step_requirements_done':
                return $this->ajaxRequirementsDone();
	        case TT_Controller_Ajax_Import_Process::COMMAND:
		        $controller = new TT_Controller_Ajax_Import_Process($this->settings);
		        break;
            case TT_Controller_Ajax_Import::COMMAND:
                $controller = new TT_Controller_Ajax_Import($this->settings);
                break;
            case TT_Controller_Ajax_Plugins::COMMAND:
                $controller = new TT_Controller_Ajax_Plugins($this->settings);
                break;
	        case TT_Controller_Ajax_Host_Allowed::COMMAND:
	        	$controller = new TT_Controller_Ajax_Host_Allowed($this->settings);
	        	break;
	        case TT_Controller_Ajax_Site_Key::COMMAND:
		        $controller = new TT_Controller_Ajax_Site_Key($this->settings);
		        break;
            default:
                throw new Exception('Invalid command');
        }

        $controller->handleAjaxRequest();
    }

	private function notices() {
		if ( ! $this->getUnfinishedStep() ) {
			// no notices when there is no unfinished step
			return;
		}


		if ( $this->themeUpdated() ) {
			// notice: update not finished
			return new TT_Helper_Notice_Update_Incomplete();
		}

		// notice: installation not finished
		return new TT_Helper_Notice_Installation_Incomplete();
	}

    private function pageFullScreenInstaller()
    {
        if (isset($_GET['page'])
            && $_GET['page'] == self::PAGE
        ) {
	        $this->settings->getContext()->onInstallerStart();
            $this->initSteps();
            add_action('admin_init', array($this, 'actionScriptsAndStyles'));
            add_action('admin_init', array($this, 'actionRunInstaller'));
            add_action('admin_menu', array($this, 'actionAdminMenu'));
        } elseif( ! isset( $_REQUEST['action'] ) ) {
        	$this->settings->getProtocol()->resetActiveSteps();
        }
    }

	public function ajaxLoadStep()
	{
		$id = array_key_exists('tt_step_id', $_REQUEST)
			? $_REQUEST['tt_step_id']
			: 0;

		$this->initSteps();

		$return = array();

		if (isset($this->steps[$id])) {
			ob_start();
			$this->steps[$id]->render();
			$return['html'] = ob_get_contents();
			ob_end_clean();
		}

		if ($this->steps[$id]->isFinished()) {
			$return['finished'] = 1;
		}

		echo json_encode($return);
		die(1);
	}

    public function getUnfinishedStep()
    {
        $this->initSteps();

        foreach ($this->steps as $step) {
            if ($step->isRequired() && ! $step->isFinished()) {
                return $step;
            }
        }

        return false;
    }

    public function ajaxRequirementsDone()
    {
        $id = array_key_exists('tt_step_id', $_REQUEST)
            ? $_REQUEST['tt_step_id']
            : 0;

        $this->initSteps();

        if (isset($this->steps[$id])) {
            echo $this->steps[$id]->requirementsDone();
        }

        die(1);
    }


    /**
     * Default way for ajax response
     */
    private function ajaxResponse()
    {
        echo json_encode($this->response);
        die(1);
    }

    protected function initSteps()
    {
        if (! empty($this->steps)) {
            return;
        }

        // PHP < 5.3
        if( version_compare( phpversion(), '5.3', '<' ) ) {
	        $step = new TT_Step_Welcome($this->settings);
	        $step->setTitle(__('Welcome!', 'toolset-themes'));
	        $step->setTemplate(TT_INSTALLER_DIR . '/application/view/theme/installer/welcome.phtml');
	        $this->addStep($step);

	        $step = new TT_Step_PHP_Version_Incompatible( $this->settings );
	        $step->setTitle(__('Requirements not met.', 'toolset-themes'));
	        $step->setTemplate(TT_INSTALLER_DIR . '/application/view/theme/installer/php-version-incompatible.phtml');
	        $this->addStep($step);
	        return;
        }

        // welcome
	    if( $this->settings->getContext()->isStepWelcomeActive() ) {
		    $step = new TT_Step_Welcome($this->settings);
		    if( ! $step->isUpdate() ) {
			    $step->setTitle(__('Welcome!', 'toolset-themes'));
		    } else {
			    $step->setTitle(__('Hi again!', 'toolset-themes'));
		    }

		    $step->setTemplate(TT_INSTALLER_DIR . '/application/view/theme/installer/welcome.phtml');
		    $this->addStep($step);
	    }


	    // init step plugin installation
	    $step_plugin = new TT_Step_Plugin_Installation($this->settings);

        // site key
	    if ( $this->settings->getRepository()->requireSiteKey() ) {
		    $step_site_key = new TT_Step_Site_Key( $this->settings );
		    $step_site_key->setTitle( __( 'Site Key', 'toolset-themes' ) );
		    $step_site_key->setTemplate( TT_INSTALLER_DIR . '/application/view/theme/installer/site-key.phtml' );
		    if( $this->settings->getProtocol()->isStepActive( $step_site_key->getSlug() ) || ! $step_plugin->allRequiredPluginsDownloaded() ) {
		    	// not all required plugins are downloaded yet
			    $this->addStep( $step_site_key );
		    } elseif( ! $step_site_key->requirementsDone() ) {
		    	// we have all required plugins, without a valid site key
			    // in this case we only show already downloaded plugins in the plugin installation step
		    	$step_plugin->showOnlyDownloadedPlugins();
		    }
	    }

	    // add step plugin installation
	    $step_plugin->setTitle(__('Plugin Installation', 'toolset-themes'));
	    $step_plugin->setTemplate(TT_INSTALLER_DIR . '/application/view/theme/installer/plugin-installation.phtml');
	    $this->addStep($step_plugin);

        // import settings and content
        $step = new TT_Step_Demo_Import($this->settings);
        $step->setTitle(__('Demo Design', 'toolset-themes'));
        $step->setTemplate( $this->getContext()->tplStepDemoImport( TT_INSTALLER_DIR . '/application/view/theme/installer/demo-import.phtml' ) );
        $this->addStep($step);

        // finish
        $step = new TT_Step_Finish($this->settings);
        $step->setTitle(__('Finish', 'toolset-themes'));
        $step->setTemplate(TT_INSTALLER_DIR . '/application/view/theme/installer/finish.phtml');
        $this->addStep($step);
    }

    /*
	function toolset_get_plugin() {
		require_once( TT_INSTALLER_DIR . '/library/plugin-installer-ajax.php' );
	}
	*/
    public function actionScriptsAndStyles()
    {
        wp_register_style(
            'tt-installer-css',
	        $this->settings->getContext()->getAssetsUrl() . '/public/css/installer.css',
            array('dashicons'),
	        TOOLSET_SITE_INSTALLER_VERSION
        );

        wp_register_script(
            'tt-installer-js',
            $this->settings->getContext()->getAssetsUrl() . '/public/js/installer.js',
            array('jquery-ui-dialog', 'backbone'),
	        TOOLSET_SITE_INSTALLER_VERSION,
            true
        );

        wp_localize_script('tt-installer-js', 'toolsetinstaller', array(
            'ajax_url'              => admin_url('admin-ajax.php'),
            'request_key'           => wp_create_nonce('tt-theme-setup'),
            // wp ajaxurl is not available on our fullscreen installer
            'user_choice_skip'      => TT_Controller_Ajax_Import::USER_CHOICE_SKIP,
            'user_choice_duplicate' => TT_Controller_Ajax_Import::USER_CHOICE_DUPLICATE,
            'user_choice_overwrite' => TT_Controller_Ajax_Import::USER_CHOICE_OVERWRITE,
            'user_choice_decide_per_item' => TT_Controller_Ajax_Import::USER_CHOICE_DECIDE_PER_ITEM,
            'key_no_import_process' => TT_Controller_Ajax_Import_Process::KEY_NO_PROCESS,
	        'l10n' => array(
	        	'continue'              => __( 'Continue', 'toolset-themes' ),
		        'close'                 => __( 'Close', 'toolset-themes' ),
		        'exit_installer'        => __( 'Exit Installer', 'toolset-themes' ),
		        'requirements_not_done' => __( 'Not all requirements are done.', 'toolset-themes' ),
		        'msg_exit_installer'    => __( 'Are you sure? Your site may not have all the necessary Toolset plugins 
		                                        and design, which is needed to display content on the front-end.',
			                                    'toolset-themes'),
		        'return_to_setup'       => __( 'Return to Setup', 'toolset-themes'),
		        'abort'                 => __( 'Abort', 'toolset-themes'),
		        'cancel_setup'          => __( 'Cancel setup', 'toolset-themes' ),
	        )
        ));
    }
    public function actionRunInstaller()
    {
        $template = TT_INSTALLER_DIR . '/application/view/theme/installer.phtml';
        if (! file_exists($template)) {
            die('Installer broken. Template file missing.');
        }

        include_once($template);

        // we don't want to have all resources of Wordpress in the installer
        die();
    }

    public function getSteps()
    {
        if ($this->steps_active !== null) {
            return $this->steps_active;
        }

        $this->steps_active = array();

        foreach ($this->steps as $step) {
            if ($step->isActive()) {
                $this->steps_active[] = $step;
            }
        }

        return $this->steps_active;
    }

    public function addStep(TT_Step_Abstract $step)
    {
        if ($step->isActive()) {
            $this->steps[] = $step;
        }
    }

    public function actionAdminMenu()
    {
        add_dashboard_page('', '', 'manage_options', self::PAGE, '');
    }

    public function themeUpdated()
    {
        if ($this->settings->getProtocol()->getFirstInstalledThemeVersion() == TT_THEME_VERSION) {
            return false;
        }

        if (! $this->settings->getProtocol()->getLastInstalledThemeVersion()
            || version_compare($this->settings->getProtocol()->getLastInstalledThemeVersion(), TT_THEME_VERSION, '>=')
        ) {
            return false;
        }

        return true;
    }

    /**
     * This removes the hook Beaver Builder adds to perform a redirect after activation
     * This is required if the plugin is installed throught our Installer
     */
    public function disableBeaverBuilderRedirect()
    {
        remove_action( 'admin_init', array( 'FLBuilderAdmin', 'show_activate_notice' ) );
    }
}
