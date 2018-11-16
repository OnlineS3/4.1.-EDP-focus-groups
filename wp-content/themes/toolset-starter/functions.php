<?php
/**********************************************************************
 *          Define textdomain
 ********************************************************************/
load_theme_textdomain( 'toolset_starter', get_template_directory() );

/**********************************************************************
 *            Load Bootstrap functions and Theme Customization
 ********************************************************************/
require_once( get_template_directory() . '/functions/bootstrap-wordpress.php' );
require_once( get_template_directory() . '/functions/theme-customizer.php' );

/******************************************************************************************
 * Enqueue styles and scripts
 *****************************************************************************************/

// used in different places
define( 'THEME_CSS', get_template_directory_uri() . '/css/theme.css' );
define( 'THEME_CSS_WOO', get_template_directory_uri() . '/css/woocommerce.css' );
define( 'THEME_CSS_BOOTSTRAP', get_template_directory_uri() . '/bootstrap/css/bootstrap.min.css' );

if (!function_exists('ref_enqueue_main_stylesheet')) {
	function ref_enqueue_main_stylesheet()
	{
		if (!is_admin()) {
			wp_enqueue_style('main', get_template_directory_uri() . '/style.css', array(), null);
		}
	}

	add_action('wp_enqueue_scripts', 'ref_enqueue_main_stylesheet');
}

if ( ! function_exists( 'ref_register_scripts' ) ) {

	function ref_register_scripts() {
		if ( ! is_admin() ) {

			// Register  CSS
			wp_register_style( 'bootstrap_css', THEME_CSS_BOOTSTRAP , array(), null );
			wp_register_style( 'theme', THEME_CSS, array(), null );
			wp_register_style( 'ref_woocommerce', THEME_CSS_WOO, array(), null );
			wp_register_style( 'font_awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css', array(), null );

			if(get_theme_mod( 'ref_theme_styles',1) == 1 ) {
				wp_enqueue_style( 'theme' );
			} else {
				wp_enqueue_style( 'bootstrap_css' );
			}

			if(get_theme_mod( 'ref_wc_styles',1) == 1 ) {
				wp_enqueue_style( 'ref_woocommerce' );
			}

			wp_enqueue_style( 'font_awesome' );

			// Register  JS
			wp_register_script( 'wpbootstrap_bootstrap_js', get_template_directory_uri() . '/bootstrap/js/bootstrap.min.js', array( 'jquery' ), null, true );
			wp_register_script( 'theme_js', get_template_directory_uri() . '/js/theme.min.js', array( 'jquery' ), null, true );

			// Enqueue JS
			wp_enqueue_script( 'wpbootstrap_bootstrap_js' );
			wp_enqueue_script( 'theme_js' );


			if ( is_single() && comments_open() && get_option( 'thread_comments' ) ) {
				wp_enqueue_script( 'comment-reply' );
			}
		}
	}

	add_action( 'wp_enqueue_scripts', 'ref_register_scripts' );
}
/******************************************************************************************
 * Tell Toolset plugins that Toolset Starter is loading Bootstrap
 *****************************************************************************************/
if (!function_exists('ref_set_boostrap_option')) {
	add_filter( 'toolset_set_boostrap_option', 'ref_set_boostrap_option', 10, 1 );
	function ref_set_boostrap_option() {
		return '3';
	}
}

/******************************************************************************************
 * Theme support
 *****************************************************************************************/
add_theme_support( 'woocommerce' );
add_theme_support( "title-tag" );
add_theme_support( 'automatic-feed-links' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'nav-menus' );
register_nav_menus( array(
	'header-menu' => __( 'Header Menu', 'toolset_starter' ),
) );


add_theme_support( 'html5', array(
	'search-form',
	'comment-form',
	'comment-list',
	'gallery',
	'caption',
	'video'
) );

/**********************************************************
 * The Archive Title Filter
 ********************************************************/

if (!function_exists('ref_custom_archive_title')) {
	add_filter('get_the_archive_title', 'ref_custom_archive_title');

	function ref_custom_archive_title($title)
	{
		if (is_post_type_archive()) {

			$title = post_type_archive_title('', false);

		}
		return $title;
	}
}
/******************************************************************************************
 * Add Open Sans font variants for admin and front-end
 *****************************************************************************************/

if ( ! function_exists( 'replace_open_sans' ) ) {

	function replace_open_sans() {
		wp_deregister_style( 'open-sans' );
		wp_register_style( 'open-sans', '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800&subset=latin,latin-ext' );
		wp_enqueue_style( 'open-sans' );
	}

	add_action( 'wp_enqueue_scripts', 'replace_open_sans' );
}


/**********************************************************************
 *            Add image sizes
 ********************************************************************/

add_image_size( 'product-thumbnail', 260, 330, true );

/**********************************************************************
 *            Register sidebars
 ********************************************************************/

register_sidebar(array(
	'name' => __('Default Sidebar', 'toolset_starter'),
	'id' => 'sidebar-default',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h3 class="widget-title">',
	'after_title' => '</h3>'
));

if ( ! defined( 'WPDDL_VERSION' ) ) {
	if (!function_exists('wpbootstrap_register_widget_areas')) {
		function wpbootstrap_register_widget_areas()
		{
			register_sidebar(array(
					'name' => __('Widgets in Footer', 'toolset_starter'),
					'id' => 'sidebar-footer',
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget' => '</div>',
					'before_title' => '<h3 class="widget-title">',
					'after_title' => '</h3>'
			));

			register_sidebar(array(
					'name' => __('Widgets in Header', 'toolset_starter'),
					'id' => 'sidebar-header',
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget' => '</div>',
					'before_title' => '<h3 class="widget-title">',
					'after_title' => '</h3>'
			));
		}

		add_action('widgets_init', 'wpbootstrap_register_widget_areas');
	}
}

/**************************************************
 * Load custom cells types for Layouts plugin from the /dd-layouts-cells/ directory
 **************************************************/
if ( defined('WPDDL_VERSION') && !function_exists('include_ddl_layouts')) {

	function include_ddl_layouts( $tpls_dir = '' ) {
		$dir_str = dirname( __FILE__ ) . $tpls_dir;
		$dir     = opendir( $dir_str );

		while ( ( $currentFile = readdir( $dir ) ) !== false ) {
			if ( is_file($dir_str . $currentFile) ) {
				$info = pathinfo($dir_str . $currentFile);
				/**
				 * http://php.net/manual/en/function.pathinfo.php#refsect1-function.pathinfo-returnvalues
				 * It will only return 'extension' if the file has an extension
				 */
				if (isset($info['extension'])) {
					
					/** 
					 * This file has extension, validate
					 * Only allows PHP files.
					 */
					
					$the_extension= $info['extension'];
	                if( 'php' === $the_extension  ){
	                    include $dir_str . $currentFile;
	                }
				}
			}
		}
		closedir( $dir );
	}

	include_ddl_layouts( '/dd-layouts-cells/' );
}


/**************************************************
 * Allow to Import/Export Layouts
 **************************************************/
if (defined('WPDDL_VERSION')) {
	require_once WPDDL_ABSPATH . '/ddl-theme.php';
}

if (function_exists('ddl_import_layouts_from_theme_dir')) {
	function ref_import_layouts()
	{
		ddl_import_layouts_from_theme_dir();
	}

	add_action('init', 'ref_import_layouts', 99);
}
/**********************************************************************
 *            Layouts Integration
 ********************************************************************/

require_once 'functions/layouts/layouts-integrate.php';

/**********************************************************************
 *     Force setting to show message when no layout is assigned
 ********************************************************************/

if (defined('WPDDL_VERSION')) {
	add_filter( 'ddl-template_include_force_option', 'ref_force_show_message_when_no_layout', 999, 1);
	add_filter( 'ddl-template_include_force_user_option', 'ref_force_show_message_when_no_layout', 999, 1);
	function ref_force_show_message_when_no_layout( $option ) {
		return 1;
	}
	add_filter( 'ddl-default-template-disabled-radio', 'ref_force_show_message_disable_radio', 999, 2 );
	function ref_force_show_message_disable_radio( $disabled, $value ){
		return 'disabled';
	}
}

/**********************************************************************
 *            Page Slug Body Class
 ********************************************************************/

function add_slug_body_class( $classes ) {
	global $post;

	if ( isset( $post ) && !is_archive() ) {
		$classes[] = $post->post_type . '-' . $post->post_name;
	}

	return $classes;
}

add_filter( 'body_class', 'add_slug_body_class' );

/**********************************************************************
 *            Layout for attachments
 ********************************************************************/

add_filter('get_layout_id_for_render', 'toolset_base_theme_fix_attachment', 99, 2);
function toolset_base_theme_fix_attachment( $layout_id, $layout ){
	// if the page is rendering with layouts fix attachment if that's the case
	if( $layout_id !== 0 ){
		add_filter('the_content', 'prepend_attachment', 999, 1);
	}
	return $layout_id;
}


/** If has_header_image not exists */
if( ! function_exists( 'has_header_image' ) ) {
	function has_header_image() {
		return (bool) get_header_image();
	}
}


/**********************************************************************
 *            Toolset Site Installer
 ********************************************************************/
if( ! function_exists( 'toolset_themes_installer' ) ) {
	add_action( 'init', 'toolset_site_installer' );
	function toolset_site_installer() {
		if( defined( 'DISABLE_TOOLSET_SITE_INSTALLER' ) ) {
			return;
		}

		if( ( ! isset( $_GET['page'] ) || $_GET['page'] != 'toolset-site-installer' )
		    &&
		    ( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'tt_ajax' )
		) {
			// current request is not installer page && no installer ajax request
			$existing_layouts_views = get_posts(
				array(
					'post_type' =>
						array(
							'dd_layouts',
							'view',
							'view-template'
						),
					'numberposts' => 1
				)
			);

			if( ! empty( $existing_layouts_views ) ) {
				// abort if there is already a view / ct / wpa / layout
				return;
			}
		}

		// toolset installer init file
		$file = dirname( __FILE__ ) . '/library/toolset-site-installer/toolset-site-installer.php';
		if( ! file_exists( $file ) ) {
			throw new Exception( 'Required file not found. ' . $file );
		}

		require_once( $file );
		unset( $file );

		// init toolset site installer
		$toolset_site_installer = new Toolset_Site_Installer();

		if( ! function_exists( 'get_plugins' ) ) {
			// WP_Installer (our plugin) depends on get_plugins
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		// assign setting and export dir
		$toolset_site_installer
			->setSettingsFile( dirname( __FILE__ ) . '/imports/settings.json' )
			->setExportsDir( dirname( __FILE__ ) . '/imports' );

		// if init() works run the installer
		if( $toolset_site_installer->init( 'TT_Controller_Site_Installer' ) ) {
			$context = new TT_Context_Toolset_Starter();
			$context->setAssetsUrl( get_template_directory_uri() . '/library/toolset-site-installer/installer' );

			$toolset_site_installer
				->getSettings()
				->setRepository( new TT_Repository_OTGS() )
				->setContext( $context );

			$toolset_site_installer
				->run();

			$dissmised_user_notices = get_user_meta( get_current_user_id(), '_types_notice_dismiss_permanent', true );
			if( ! is_array( $dissmised_user_notices) || ! in_array( 'toolset-starter-dismiss-run-installer', $dissmised_user_notices ) ) {
				add_action( 'admin_notices', 'toolset_site_installer_notice' );
			}
		}
	}

	/**
	 * Admin Notices for starting the installer (just used by Tooslet Starter)
	 */
	function toolset_site_installer_notice() {
		$theme = wp_get_theme(); ?>

        <div class="notice notice-success toolset-notice is-dismissible"
             data-types-notice-dismiss-permanent="toolset-starter-dismiss-run-installer">
            <style scoped>
                .toolset-notice {
                    border-left-color: #f05a29 !important;
                }
                .toolset-notice .button-primary-toolset,
                .toolset-notice .button-primary-toolset:hover,
                .toolset-notice .button-primary-toolset:focus {
                    outline: 0 !important;
                    background: #f05a29 !important;
                    border-color: #da5628 !important;
                    color: #fff !important;
                    box-shadow: 0 1px 0 #C35428;
                    text-decoration: none;
                    text-shadow: 0 -1px 1px #da5628, 1px 0 1px #da5628, 0 1px 1px #da5628, -1px 0 1px #da5628;
                }

                .toolset-notice .button-primary-toolset:hover,
                .toolset-notice .button-primary-toolset:focus {
                    background: rgba(240, 90, 41, 0.95) !important;
                }

                .toolset-notice-list {
                    padding: 2px;
                    margin: -7px 0 0px 20px;
                    list-style: disc;
                }
            </style>

            <p>
                <b>
					<?php _e( 'Do you want to prepare this site for quick editing with Toolset?', 'toolset_starter' ); ?>
                </b>
            </p>

            <p>
				<?php _e( 'We will:', 'toolset_starter' ); ?>
            </p>

            <ul class="toolset-notice-list">
                <li>
					<?php printf(
						__( 'Automatically install the Toolset plugins that are needed for %s', 'toolset_starter' )
						, $theme->get('Name')
					); ?>
                </li>

                <li>
					<?php printf(
						__( 'Set up layouts, template, archives and other site elements for %s', 'toolset_starter' )
						, $theme->get('Name')
					); ?>
                </li>
            </ul>

            <p>
                <a href="<?php echo admin_url( 'index.php?page=toolset-site-installer' ); ?>"
                   class="button button-primary button-primary-toolset" style="margin: 0 0 5px;">
					<?php _e( 'Run Installer', 'toolset_starter' ); ?>
                </a>
            </p>
        </div>
		<?php
	}

	// INSTALLER IMPORT

	// we don't want to have a layout for the shop page
	add_action( 'tt_import_finished_layouts', 'ts_installer_unassign_layout_of_woocommerce_shop_page' );

	function ts_installer_unassign_layout_of_woocommerce_shop_page() {
		if( ! class_exists( 'WPDD_Utils' )
		    || ! method_exists( 'WPDD_Utils', 'remove_layout_assignment_to_post_object' ) ) {
			// abort, dependencies missing
			return;
		}

		$shop_page_id = get_option( 'woocommerce_shop_page_id' );

		if( ! $shop_page_id ) {
			// abort, no shop (no problem, perhaps WC is not installed)
			return;
		}

		// remove layout assignment of shop page
		WPDD_Utils::remove_layout_assignment_to_post_object( $shop_page_id );
	}

	// predefined options for WooCommerce Views
	add_action( 'tt_import_finished_layouts', 'ts_installer_set_options_woocommerce_views' );

	function ts_installer_set_options_woocommerce_views() {
		if( ! defined ( 'WOOCOMMERCE_VIEWS_PLUGIN_PATH' ) || ! class_exists( 'Class_WooCommerce_Views' ) ) {
			return;
		}

		$wc_views = new Class_WooCommerce_Views();

		// single product
		$template_path = dirname( __FILE__ ) . '/page.php';

		if( file_exists( $template_path ) ) {
			$wc_views->wcviews_save_php_template_settings($template_path);
		}

		// products listing
		$template_path = WOOCOMMERCE_VIEWS_PLUGIN_PATH .
		                 DIRECTORY_SEPARATOR . 'templates' .
		                 DIRECTORY_SEPARATOR . 'archive-product.php';

		if( file_exists( $template_path ) ) {
			$wc_views->wcviews_save_php_archivetemplate_settings($template_path);
		}
	}


	/**********************************************************************
	 *            WooCommerce Views
	 ********************************************************************/
	add_action( 'admin_init', 'ts_woocommerce_views_parametric_search_setup' );

	function ts_woocommerce_views_parametric_search_setup() {
		if( defined( 'DOING_AJAX' ) || ! class_exists( 'Class_WooCommerce_Views' ) ) {
			// Don't run on ajax calls or if WooCommerce Views not active
			return;
		}

		$option_name = 'toolset_starter_woocommerce_views_setup';
		$option = get_option( $option_name, false );

		if( $option !== false ) {
			// this setup already ran
			return;
		}

		/**@var Class_WooCommerce_Views $woo_views */
		$woo_views = new Class_WooCommerce_Views();

		if( ! method_exists( $woo_views, 'ajax_process_wc_views_batchprocessing' ) ) {
			// required method missing
			error_log( 'WooCommerce Views no longer supports function ajax_process_wc_views_batchprocessing()' );
			return;
		}

		// calcualte product fields to be used in parametric search
		$woo_views->ajax_process_wc_views_batchprocessing();

		// store
		add_option( $option_name, 'completed' );
	}
}