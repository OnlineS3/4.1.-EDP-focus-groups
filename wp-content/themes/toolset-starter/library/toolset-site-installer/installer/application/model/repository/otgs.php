<?php

/**
 * Class TT_Repository_OTGS_Handler
 * This is a lightweight clone of our OTGS Installer (WP_Installer())
 */
class TT_Repository_OTGS extends TT_Repository_Abstract {

	const REP_KEY_ID = 'id';
	const REP_KEY_API_URL = 'apiurl';
	const REP_KEY_PRODUCTS_LIST_URL = 'products';

	/**
	 * We have different domains for our different product lines (Toolset, WPML)
	 * @var array
	 */
	private $domains;

	/**
	 * Currently active domain
	 * @var string
	 */
	private $domain_active;

	/**
	 * @var string
	 */
	private $site_key;

	/**
	 * All data of our products (fetched from domain)
	 * @var array
	 */
	private $products;

	/**
	 * OTGS Installer Class
	 * @var WP_Installer
	 */
	private $otgs_installer;

	/**
	 * Subscription Info
	 */
	private $subscription_info;

	/**
	 * TT_Repository_OTGS constructor.
	 *
	 * @param $config_file
	 */
	public function __construct( $config_file = false ) {
		$config_file = $config_file
			? $config_file
			: dirname( __FILE__ ) . '/../../../../library/otgs/installer/repositories.xml';

		$this->parseConfig( $config_file );

		$this->loadDependencies();

		// default domain is 'toolset' (currently we have 'tooslet' and 'wpml')
		$this->setDomain( 'toolset' );
	}

	/**
	 * We require WP_Installer here
	 * (nothing "core" as the name suggests - it's our OTGS Installer)
	 *
	 * @throws Exception
	 */
	private function loadDependencies() {
		if( ! class_exists( 'WP_Installer' ) ) {
			$file_installer = dirname( __FILE__ ) . '/../../../../library/otgs/installer/installer.php';
			$file_loader = dirname( __FILE__ ) . '/../../../../library/otgs/installer/loader.php';
			if( ! file_exists( $file_installer ) || ! file_exists( $file_loader ) ) {
				throw new Exception( 'Dependencies for TT_Repository_OTGS missing.' );
			}

			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			require_once( $file_loader );
			require_once( $file_installer );
		}

		if( ! class_exists( 'WP_Installer' ) || ! method_exists( 'WP_Installer', 'instance' ) ) {
			// change in the file structure of OTGS Installer
			throw new Exception( 'Dependencies for TT_Repository_OTGS missing. Code 2.' );
		}

		$this->otgs_installer = WP_Installer::instance();
	}

	/**
	 * Change domain
	 *
	 * @param $domain
	 *
	 * @throws Exception
	 */
	public function setDomain( $domain ) {
		if( ! array_key_exists( $domain, $this->domains ) ) {
			throw new Exception( 'Domain '. $domain .' does not exist' );
		}

		$this->domain_active = $domain;
	}

	/**
	 * OTGS Installer is based on a valid Site Key
	 * @return bool
	 */
	public function requireSiteKey() {
		return true;
	}

	/**
	 * Get current site key
	 *
	 * @return bool|mixed|string
	 */
	public function getSiteKey() {
		if( $this->site_key === null || $this->site_key != $this->getSettings()->getProtocol()->getSiteKey() ) {
			$this->site_key = $this->fetchSiteKey();
		}

		return $this->site_key;
	}

	/**
	 * @param $plugin_slug
	 *
	 * @return bool|mixed
	 */
	public function getPluginSrc( $plugin_slug ) {
		if( ! $src = $this->getProductDownloadUrl( $plugin_slug ) ) {
			return false;
		}

		$src = $this->otgs_installer->append_site_key_to_download_url( $src, $this->getSiteKey(), $this->domain_active );
		return $src;
	}

	/**
	 * @param $slug
	 *
	 * @return bool|mixed
	 */
	private function getProductDownloadUrl( $slug ) {
		if( ! $this->getSiteKey() ) {
			return false;
		}

		$slug = $this->normaliseSlug( $slug );

		if( $this->products === null ) {
			$this->products = $this->fetchProducts();
		}

		if( is_array( $this->products ) && isset( $this->products[$slug]['url'] ) ) {
			return $this->products[$slug]['url'];
		}

		return false;
	}

	/**
	 * Little helper which normalise our slug plugin slugs to match other common uses
	 *
	 * @param $slug
	 *
	 * @return string
	 */
	private function normaliseSlug( $slug ) {
		$slug = strtolower( $slug );
		switch( $slug ){
			case 'wp-types':
				return 'types';
			case 'cred':
				return 'cred-frontend-editor';
			case 'wp-layouts':
				return 'layouts';
			case 'views':
				return 'wp-views';
			case 'access':
				return 'types-access';
			case 'maps':
				return 'toolset-maps';
			default:
				return $slug;
		}
	}

	/**
	 *
	 * @return array|bool|mixed|object
	 */
	private function fetchProducts() {
		$products_url = $this->domains[$this->domain_active][self::REP_KEY_PRODUCTS_LIST_URL];
		$response = wp_remote_get( $products_url );

		if ( is_wp_error( $response ) ) {
			// http fallback
			$products_url = preg_replace( "@^https://@", 'http://', $products_url );
			$response     = wp_remote_get( $products_url );
		}

		if ( is_wp_error( $response ) ) {
			// todo create a user response here
			error_log( 'ERROR ' . $response->get_error_message() );
			return false;
		}

		if ( $response && isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) {
			$body = wp_remote_retrieve_body( $response );
			if ( $body ) {
				$products = json_decode( $body, true );

				if ( is_array( $products ) and isset( $products['downloads']['plugins'] ) ) {
					return $products['downloads']['plugins'];
				}
			}
		}

		return false;
	}

	/**
	 * Checks database for site key.
	 *
	 * @return bool|mixed
	 */
	private function fetchSiteKey() {
		if( $site_key = $this->getSettings()->getProtocol()->getSiteKey() ) {
			try {
				$this->otgs_installer->save_site_key( array(
						'repository_id' => $this->domain_active,
						'nonce' => wp_create_nonce('save_site_key_' . $this->domain_active ),
						'site_key' => $site_key,
						'return' => true
					)
				);
			} catch( Exception $e ) {
				error_log( 'message ' . print_r( $e->getMessage(), true ) );
			}

			return $site_key;
		}

		if( $site_key = $this->otgs_installer->get_repository_site_key( $this->domain_active ) ) {
			return $site_key;
		};

		// no site key yet
		return false;
	}

	/**
	 * Get the subcription info of the current site
	 */
	public function getSubscriptionInfo() {
		if( $this->subscription_info == null ) {
			$this->subscription_info = $this->fetchSubscriptionInfo();
		}

		return $this->subscription_info;
	}

	/**
	 * Load config file of OTGS installer (repositories.xml)
	 *
	 * @param $config_file
	 *
	 * @throws Exception
	 */
	private function parseConfig( $config_file ) {
		if( ! file_exists( $config_file ) ) {
			throw new Exception( 'Config file does not exists: ' . $config_file );
		}

		$repos = simplexml_load_file( $config_file );

		if( $repos ) {
			foreach ( $repos as $repo ) {
				if( ! property_exists( $repo, self::REP_KEY_ID )
					&& ! property_exists( $repo, self::REP_KEY_API_URL )
					&& ! property_exists( $repo, self::REP_KEY_PRODUCTS_LIST_URL )
				) {
					// no valid repo
					continue;
				}

				$id = strval( $repo->id );

				$data['apiurl']  = strval( $repo->apiurl );
				$data['products'] = strval( $repo->products );

				$this->domains[ $id ] = $data;
			}
		}

		if( empty( $this->domains ) ) {
			// no valid repo at all
			throw new Exception( 'No valid repo for Installer' );
		}
	}

	/**
	 *
	 * @return bool
	 */
	public function isSiteKeyValid() {
		if( ! $this->getSubscriptionInfo() ) {
			return false;
		}
		return true;
	}

	private function fetchSubscriptionInfo() {
		if( ! $site_key = $this->getSiteKey() ) {
			return false;
		}

		return $this->otgs_installer->fetch_subscription_data( $this->domain_active, $site_key );
	}

	/**
	 * DO NOT use TBT Production / Development / Testing mechanism
	 * @return bool
	 */
	public function useHostAllowedMechanism() {
		return false;
	}
}