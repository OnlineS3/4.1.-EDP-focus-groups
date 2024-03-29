<?php

/**
 * Class WPDD_LayoutsPlugin
 * init Layouts Plugin
 * This class has the only purpose to solve the dependencies of the plugin
 * so it is meant to create dependencies in one single place to handle them at
 * the appropriate time since this control is lost with the auto-loader
 */
class WPDD_LayoutsPlugin{

	private static $instance = null;
	private $wpddl_wpml_support;
	private $cells_api = null;
	private $settings = null;

	private function __construct( WPDDL_WPML_Support $wpddl_wpml_support ) {
		$this->wpddl_wpml_support = $wpddl_wpml_support;
		$this->init_dependencies();
		$this->settings = $this->init_settings();
		$this->add_hooks();
	}

	private function add_hooks(){
		add_action( 'plugins_loaded', array( $this, 'init_registered_cells_api' ), 1 );
		add_action( 'init', array( $this, 'init_layouts_plugin' ), 9 );
		add_action( 'ddl-before_init_layouts_plugin', array('WPDDL_Templates_Settings', 'getInstance') );
	}

	/**
	 * let's make sure all the cells are loaded in the parser before any use
	 */
	public function init_registered_cells_api(){
		$this->cells_api = WPDD_RegisteredCellTypesFactory::build();
	}

	public function get_cells_api(){
		return $this->cells_api;
	}

	private function init_dependencies(){
		$this->init_utils();
		$this->init_WPML_hooks();
		$this->init_wpddl_framework();
	}

	private function init_WPML_hooks(){
		$this->wpddl_wpml_support->add_hooks();
	}

	private function init_utils(){
		WPDD_Utils::init();
	}

	private function init_wpddl_framework(){
		return WPDDL_Framework::getInstance();
	}

	private function init_settings(){
		return WPDDL_Settings::getInstance();
	}

	function init_layouts_plugin()
	{
		global $wpddlayout;
		$wpddlayout = WPDD_Layouts::getInstance();
	}

	public static function getInstance( WPDDL_WPML_Support $wpddl_wpml_support = null )
	{
		if (!self::$instance) {
			self::$instance = new self( $wpddl_wpml_support );
		}

		return self::$instance;
	}
}