<?php
define("DDL_CREATE", "ddl_create_layout" );
define("DDL_ASSIGN", "ddl_assign_layout_to_content" );
define("DDL_EDIT", "ddl_edit_layout" );
define("DDL_DELETE", "ddl_delete_layout" );

class WPDD_Layouts_Users_Profiles{

    private static $instance = null;
    const USER_OPTIONS = 'users_options';
    private $ddl_users_settings;

    protected static $perms_to_pages = array(
        'admin.php?page=dd_layouts&amp;new_layout=true' => DDL_CREATE,
        'dd_layouts_edit' => DDL_EDIT,
        'dd_layouts' => DDL_EDIT,
        'dd_layouts_debug' => DDL_EDIT,
        'dd_tutorial_videos' => DDL_EDIT,
        'dd_layouts_troubleshoot' => DDL_EDIT,
        'toolset-settings' => DDL_EDIT
    );

    private function __construct(){
        $this->ddl_users_settings = new WPDDL_Options_Manager( self::USER_OPTIONS );

        add_filter('wpcf_access_custom_capabilities', array( &$this, 'wpddl_layouts_capabilities'), 12, 1);
        // clean up the database when deactivate plugin
        //register_activation_hook( WPDDL_ABSPATH . DIRECTORY_SEPARATOR . 'dd-layouts.php', array(&$this, 'add_caps') );
        add_action('init', array(&$this, 'add_caps'), 99 );
        register_deactivation_hook( WPDDL_ABSPATH . DIRECTORY_SEPARATOR . 'dd-layouts.php', array(&$this, 'disable_all_caps') );
		
        add_action( 'profile_update', array( $this, 'clean_the_mess_in_nonadmin_user_caps' ), 10, 1 );

    }

    function wpddl_layouts_capabilities ($data){
        if ( class_exists('WPDD_Layouts_Users_Profiles') ){
            $wp_roles['label'] = __('Layouts capabilities', 'wpcf_access');
            $wp_roles['capabilities'] = self::ddl_get_capabilities();
            $data[] = $wp_roles;
        }
        return $data;
    }

    public static final function ddl_get_capabilities(){
            return array(
                DDL_CREATE => "Create layouts",
                DDL_ASSIGN => "Assign layouts to content",
                DDL_EDIT => "Edit layouts",
                DDL_DELETE => "Delete layouts"
            );
    }


    public static function get_cap_for_page( $page ){
        return self::$perms_to_pages[$page] ? self::$perms_to_pages[$page] : DDL_EDIT;
    }

    public function add_caps(){
        global $wp_roles;

        if( $this->ddl_users_settings->get_options('updated_profiles') === true ){
            return;
        }

        if ( ! isset( $wp_roles ) || ! is_object( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        $ddl_capabilities = array_keys( self::ddl_get_capabilities() );

        $roles = $wp_roles->get_names();
        foreach ( $roles as $current_role => $role_name ) {
            $capability_can = apply_filters( 'ddl_capability_can', 'manage_options' );
            if ( isset( $wp_roles->roles[ $current_role ][ 'capabilities' ][ $capability_can ] ) ) {
                $role = get_role( $current_role );
                if ( isset( $role ) && is_object( $role ) ) {
                    for ( $i = 0, $caps_limit = count( $ddl_capabilities ); $i < $caps_limit; $i ++ ) {

                        if ( ! isset( $wp_roles->roles[ $current_role ][ 'capabilities' ][ $ddl_capabilities[ $i ] ] ) ) {
                            $role->add_cap( $ddl_capabilities[ $i ] );

                        }
                    }
                }

            }
        }

        // Set new caps for all Super Admins
        // Note that on non-multisite, get_super_admins might return false positives:
        // https://developer.wordpress.org/reference/functions/get_super_admins/
        if ( is_multisite() ) {
            $super_admins = get_super_admins();
            foreach ( $super_admins as $admin ) {
                $updated_current_user = new WP_User( $admin );
                for ( $i = 0, $caps_limit = count( $ddl_capabilities ); $i < $caps_limit; $i ++ ) {
                    $updated_current_user->add_cap( $ddl_capabilities[ $i ] );
                }
            }
        }
        
        // We need to refresh $current_user caps to display the entire Layouts menu
                
        // If $current_user has not been updated yet with the new capabilities,
        global $current_user;
        if ( isset( $current_user ) && isset( $current_user->ID ) ) {
            
            // Insert the capabilities for the current execution
            $updated_current_user = new WP_User( $current_user->ID );

            for ( $i = 0, $caps_limit = count( $ddl_capabilities ); $i < $caps_limit; $i ++ ) {
                if ( $updated_current_user->has_cap($ddl_capabilities[$i]) ) {
                    $current_user->add_cap($ddl_capabilities[$i]);
                }
            }
            
            // Refresh $current_user->allcaps
            $current_user->get_role_caps();
        }


        $this->ddl_users_settings->update_options( 'updated_profiles', true, true );
    }
	
	/**
	 * In WPDD_Layouts_Users_Profiles::add_caps() we're adding extra capabilities to superadmins.
	 *
	 * When the superadmin status is revoked, we need to take those caps back, otherwise we might create a security
	 * issue.
	 *
	 * This is a temporary workaround for toolsetcommon-248 inspired by types-768 until a better solution is provided.
	 *
	 * @param int|WP_User $user ID of the user or a WP_User instance that is currently being edited.
	 * @since 2.0.3
	 */
	public function clean_the_mess_in_nonadmin_user_caps( $user ) {
		
		if( ! $user instanceof WP_User ) {
			$user = new WP_User( $user );
			if( ! $user->exists() ) {
				return;
			}
		}

		// True if the user is network (super) admin. Also returns True if network mode is disabled and the user is an admin.
		$is_superadmin = is_super_admin( $user->ID );

		if( ! $is_superadmin ) {
			// We'll remove the extra Types capabilities. If the user has a role that adds those capabilities, nothing
			// should change for them.
			$ddl_get_capabilities = array_keys( self::ddl_get_capabilities() );
			foreach( $ddl_get_capabilities as $capability ) {
				$user->remove_cap( $capability );
			}
		}

	}

    public function disable_all_caps(){
        global $wp_roles;

        if ( ! isset( $wp_roles ) || ! is_object( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        $ddl_capabilities = array_keys( self::ddl_get_capabilities() );

        foreach ( $ddl_capabilities as $cap ) {
            foreach (array_keys($wp_roles->roles) as $role) {
                $wp_roles->remove_cap($role, $cap);
            }
        }

        // Remove caps for all Super Admins
        // Note that on non-multisite, get_super_admins might return false positives:
        // https://developer.wordpress.org/reference/functions/get_super_admins/
        if ( is_multisite() ) {
            $super_admins = get_super_admins();
            foreach ( $super_admins as $admin ) {
                $user = new WP_User( $admin );
                for ( $i = 0, $caps_limit = count( $ddl_capabilities ); $i < $caps_limit; $i ++ ) {
                    $user->remove_cap( $ddl_capabilities[ $i ] );
                }
            }
        }

        $this->ddl_users_settings->update_options( 'updated_profiles', false, true );

    }

    public static function user_can_create(){
        return current_user_can( DDL_CREATE );
    }

    public static function user_can_assign(){
        return current_user_can( DDL_ASSIGN );
    }

    public static function user_can_edit(){
        return current_user_can( DDL_EDIT );
    }
	public static function user_can_edit_content_layout($layout_id){

		$cap = DDL_EDIT;
		$is_private = WPDD_Utils::is_private( $layout_id );
		if( true === $is_private ){
			$cap = 'edit_others_pages'; // allow content layout editing for user with edit_others_pages cap
		}

		return current_user_can( $cap );
	}

    public static function user_can_delete(){
        return current_user_can( DDL_DELETE );
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new WPDD_Layouts_Users_Profiles();
        }

        return self::$instance;
    }
}