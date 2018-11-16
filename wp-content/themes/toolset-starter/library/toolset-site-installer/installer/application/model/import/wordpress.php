<?php


class TT_Import_Wordpress extends TT_Import_Abstract {


	const FILE_WORDPRESS_XML   = 'toolset-based-theme-wordpress.xml';

	private $is_path_default = true;

	private $attachments = array();

	/**
	 * @param string|bool $path
	 */
	public function __construct( $path = false ) {
		if ( ! $path ) {
			// default path
			$this->path_import_file = TT_INSTALLER_EXPORTS_DIR . '/wordpress.xml';
		} else {
			$this->setPathImportFile( $path );
		}
	}

	public function getTitle() {
		return 'WordPress';
	}

	public function getSlug() {
		return 'wordpress';
	}

	/**
	 * Import function
	 *
	 * @param array $user_choice
	 *  'force_import_post_name'    => array(),
	 *  'force_skip_post_name       => array(),
	 *  'force_duplicate_post_name  => array(),
	 *
	 * @return mixed
	 */
	public function import( $user_choice ) {

		ob_start();
		define( 'WP_LOAD_IMPORTERS', true );
		require_once( ABSPATH . 'wp-admin/includes/post.php' );
		require_once( ABSPATH . 'wp-admin/includes/comment.php' );
		require_once( ABSPATH . 'wp-admin/includes/taxonomy.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
		require_once( TT_INSTALLER_DIR . '/library/wordpress-importer/wordpress-importer.php' );
		$import                    = new WP_Import();
		$import->fetch_attachments = true;
		add_filter( 'wp_unique_filename', array( $this, 'processIncreaseTodoDoneOnFilter' ) );
		@$import->import( $this->getPathImportFile() );
		remove_filter( 'wp_unique_filename', array( $this, 'processIncreaseTodoDoneOnFilter' ) );
		$wordpress_import_log = ob_get_contents();
		ob_end_clean();

		$wp_upload_dir        = wp_upload_dir();
		$wp_upload_dir        = trailingslashit( $wp_upload_dir['basedir'] );
		unlink( $wp_upload_dir . self::FILE_WORDPRESS_XML );

		return ! empty( $wordpress_import_log ) ? $this->importLogGetFailed( $wordpress_import_log ) : true;
	}

	public function processIncreaseTodoDoneOnFilter( $filename ) {
		$this->processIncreaseTodoDone();
		return $filename;
	}

	protected function getPathImportFile() {
		$this->adjustImportFileToUseThemeUploads();

		return $this->path_import_file;
	}

	private function adjustImportFileToUseThemeUploads() {
		if ( ! defined( 'TT_THEME_UPLOADS_URI' ) || ! defined( 'TT_THEME_UPLOADS_DIR' ) ) {
			return;
		}

		// author used the option to upload media files
		$wp_upload_dir        = wp_upload_dir();
		$wp_upload_dir        = trailingslashit( $wp_upload_dir['basedir'] );
		$file_wordpress_xml   = $wp_upload_dir . self::FILE_WORDPRESS_XML;

		if ( ! wp_mkdir_p( dirname( $file_wordpress_xml ) )
		     || ! file_put_contents( $file_wordpress_xml, 'Write test' )
		) {
			// check if uploads is writeable
			throw new Exception( 'Filesystem-Error: make sure wp-content is writeable.' );
		}

		if ( ! $original_import_file = file_get_contents( $this->path_import_file ) ) {
			throw new Exception( 'Original File could not be found.' );
		}

		// <wp:attachment_url><![CDATA[http://tbt-1-end-user.dev/wp-content/uploads/2016/11/Bildschirmfoto-2016-11-27-um-10.54.46.png]]></wp:attachment_url>

		$search = '#(\<wp\:attachment\_url\>\<\!\[CDATA\[)' .            // $1 <wp:attachment_url><![CDATA[
		          '(?:https?://)?(?:[-\w]+\.[-\w\.]+)+\w(?::\d+)?' .     // $2 domain with possible port
		          '(\/wp\-content\/uploads(/([-\w/_\.]*(\?\S+)?)?)*)#i'; // $3 path without domain

		$string = preg_replace_callback(
			$search,
			array( $this, 'callbackChangeAttachmentUrlToThemeNestedUploads' ),
			$original_import_file
		);

		if ( file_put_contents( $file_wordpress_xml, $string ) ) {
			$this->setPathImportFile( $file_wordpress_xml );
		};
	}

	/**
	 * Replace domain by url to theme nested uploads folder
	 *
	 * Make sure that TT_THEME_UPLOADS_DIR and TT_THEME_UPLOADS_URI is set before running
	 * this callback function.
	 *
	 * @param $matches
	 *
	 * @return string URL to attachment
	 */
	private function callbackChangeAttachmentUrlToThemeNestedUploads( $matches ) {
		$original            = $matches[0];
		$before_domain       = $matches[1];
		$path_without_domain = $matches[3];

		if ( ! file_exists( TT_THEME_UPLOADS_DIR . $path_without_domain ) ) {
			return $original;
		}

		$this->processIncreaseTodo();
		$this->attachments[] = $path_without_domain;

		return $before_domain . TT_THEME_UPLOADS_URI . $path_without_domain;
	}

	public function setPathImportFile( $path_import_file ) {
		$this->is_path_default  = false;
		$this->path_import_file = $path_import_file;
		$this->path_import_file .= strpos( $this->path_import_file, '.xml' ) === false ? '/wordpress.xml' : '';
	}


	protected function importNotModifiedAndNewItems( $items_actions ) {
		$items_not_modified = $this->getItemsNotModified() ? $this->getItemsNotModified() : array();
		$items_new          = $this->getItemsNew() ? $this->getItemsNew() : array();

		$items = array_merge( $items_not_modified, $items_new );

		foreach ( $items as $item ) {
			$slug = property_exists( $item, '__types_id' ) && ! empty( $item->__types_id )
				? $item->__types_id
				: $item->post_name;

			$items_actions[ $this->getKeyForImportItem() ][] = $slug;
		}

		return $items_actions;
	}

	protected function arrayDiffCompareItems( $item_a, $item_b ) {
		return strcmp( $item_a->__types_id, $item_b->__types_id );
	}

	/**
	 * Get the failed imports
	 *
	 * @param string $log
	 *
	 * @return bool
	 */
	private function importLogGetFailed( $log ) {
		// todo we should consider modifing wordpress-importer instead of this...
		preg_match( '#(.*?)<p>#', $log, $matches );
		if ( empty( $matches ) || ! array_key_exists( 1, $matches ) ) {
			return true;
		}

		return $matches[1];
	}

}