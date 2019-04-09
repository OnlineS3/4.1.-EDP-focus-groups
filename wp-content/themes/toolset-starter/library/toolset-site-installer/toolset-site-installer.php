<?php
if ( defined( 'TOOLSET_SITE_INSTALLER_VERSION' ) ) {
	// seems there already is an instance running
	return;
}

define( 'TOOLSET_SITE_INSTALLER_VERSION', '0.1' );

class Toolset_Site_Installer {
	/**
	 * @var TT_Controller_Abstract
	 */
	private $controller;

	/**
	 * @var string
	 */
	private $controller_as_string;

	/**
	 * @var string
	 */
	private $settings_file;

	/**
	 * @var string
	 */
	private $exports_dir;

	/**
	 * @var TT_Settings_Interface
	 */
	private $settings;

	public function __construct() {
		remove_action( 'init', 'toolset_themes_installer' );
		$this->checkRequirements();
	}

	/**
	 * @param $controller_as_string
	 *
	 * @return bool
	 */
	public function init( $controller_as_string ) {
		if ( ! $this->settings_file || ! file_exists( $this->settings_file )
		     || ! $this->exports_dir || ! is_dir( $this->exports_dir )
		     || ! current_user_can( 'manage_options' )
		) {
			return false;
		}

		$this->registerAutoloader();
		$this->registerGlobals();

		$this->controller_as_string = $controller_as_string;

		$this->settings = new TT_Settings(
			$this->settings_file,
			new TT_Settings_Protocol(),
			new TT_Settings_Files( $this->exports_dir )
		);

		return true;
	}

	public function run() {
		$this->controller = new $this->controller_as_string( $this->settings );
	}

	/**
	 * @return TT_Settings_Interface
	 */
	public function getSettings() {
		return $this->settings;
	}

	private function checkRequirements() {
		if( ! defined( 'ABSPATH' ) ) {
			throw new Exception( 'Not working without WordPress' );
		}

		if( ! function_exists( 'current_user_can' ) ) {
			throw new Exception( 'Installer shouldn\'t be called before admin_init hook.' );
		}
	}

	private function registerGlobals() {
		if ( ! defined( 'TT_INSTALLER_DIR' ) ) {
			define( 'TT_INSTALLER_DIR', dirname( __FILE__ ) . '/installer' );
		}

		if ( ! defined( 'TT_INSTALLER_EXPORTS_URI' ) ) {
			define( 'TT_INSTALLER_EXPORTS_URI', get_template_directory_uri() . '/library/toolset/settings/exports' );
		}

		if ( ! defined( 'TT_THEME_SLUG' ) ) {
			define( 'TT_THEME_SLUG', get_template() );
		}

		if ( ! defined( 'TT_THEME_VERSION' ) ) {
			define( 'TT_THEME_VERSION', wp_get_theme()->get( 'Version' ) );
		}
	}

	private function registerAutoloader() {
		$file = dirname( __FILE__ ) . '/autoloader.php';
		if ( ! file_exists( $file ) ) {
			throw new Exception( 'Toolset Site Installer missing required file: ' . $file );
		}
		require_once( $file );
		new TT_Autoloader( dirname( __FILE__ ) . '/autoload_classmap.php' );
	}

	/**
	 * @param string $exports_dir
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function setExportsDir( $exports_dir ) {
		// normalise path
		$exports_dir = rtrim( $exports_dir, '/' );

		if( ! is_dir( $exports_dir ) ) {
			throw new Exception( 'Dir could not be found: ' . $exports_dir );
		}

		$this->exports_dir = $exports_dir;

		return $this;
	}

	/**
	 * @param $settings_file
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function setSettingsFile( $settings_file ) {
		if( ! file_exists( $settings_file ) ) {
			throw new Exception( 'File could not be found: ' . $settings_file );
		}
		$this->settings_file = $settings_file;
		return $this;
	}
}

/**
 * TBT Installer
 */
add_action( 'after_setup_theme', 'toolset_themes_installer' );
function toolset_themes_installer() {
	// init toolset site installer
	$toolset_site_installer = new Toolset_Site_Installer();

	// settings file
	$settings_file = file_exists( dirname( __FILE__ ) . '/settings/settings-local.json' )
		? dirname( __FILE__ ) . '/settings/settings-local.json'
		: dirname( __FILE__ ) . '/settings/settings.json';

	// assign setting and export dir
	$toolset_site_installer
		->setSettingsFile( $settings_file )
		->setExportsDir( dirname( __FILE__ ) . '/settings/exports' );

	// if init() works run the installer
	if( $toolset_site_installer->init( 'TT_Controller_Site_Installer' ) ) {
		$toolset_site_installer
			->getSettings()
			->setRepository( new TT_Repository_TBT() )
			->setContext( new TT_Context_Theme() );

		$toolset_site_installer
			->run();

		// run updater for theme and plugins
		if( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'tt_ajax' ) {
			$settings = $toolset_site_installer->getSettings();
			require_once( dirname( __FILE__ ) . '/updater/updater.php' );
		}
	}
}

