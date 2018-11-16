<?php


class TT_Import_Cred extends TT_Import_Abstract
{
    /**
     * @param string|bool $path_post_forms
     * @param string|bool $path_user_forms
     *
     * @internal param array|string $path
     */
    public function __construct(
        $path_post_forms = false,
        $path_user_forms = false
    ) {
        CRED_Loader::load('CLASS/XML_Processor');

        // default paths
        $path_post_forms = $path_post_forms ? $path_post_forms : TT_INSTALLER_EXPORTS_DIR;
	    $path_post_forms .= strpos( $path_post_forms, '.zip' ) === false ? '/cred-posts.zip' : '';
        $path_user_forms = $path_user_forms ? $path_user_forms : TT_INSTALLER_EXPORTS_DIR;
	    $path_user_forms .= strpos( $path_user_forms, '.zip' ) === false ? '/cred-users.zip' : '';

        // path to import file
        $this->setPathImportFile(array(
            'post-forms' => $path_post_forms,
            'user-forms' => $path_user_forms
        ));

        // load post forms
        if (file_exists($this->getPathImportFile('post-forms'))) {
            $zip      = new TT_Helper_Zip($this->getPathImportFile('post-forms'));
            $xml_file = $zip->fetchFileByExtension('.xml');

            if (! $xml = simplexml_load_string($xml_file)) {
                return false;
            }
            $this->items_groups[] = new TT_Import_Cred_Post_Forms($xml);
        }

        // load user forms
        if (file_exists($this->getPathImportFile('user-forms'))) {
            $zip      = new TT_Helper_Zip($this->getPathImportFile('user-forms'));
            $xml_file = $zip->fetchFileByExtension('.xml');

            if (! $xml = simplexml_load_string($xml_file)) {
                return false;
            }
            $this->items_groups[] = new TT_Import_Cred_User_Forms($xml);
        }
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'CRED';
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return 'cred';
    }

    /**
     * CRED has twice import files, one for post forms, one for user forms
     *
     * @param string $type 'post-forms' OR 'user-forms'
     *
     * @return mixed
     */
    protected function getPathImportFile($type = 'post-forms')
    {
        return $this->path_import_file[$type];
    }

    /**
     * Import function
     *
     * @param array $user_choice
     *  'force_overwrite_post_name' => array(),
     *  'force_skip_post_name       => array(),
     *  'force_duplicate_post_name  => array(),
     *
     * @return mixed
     */
    public function import($user_choice)
    {
        $args = $this->translateUserChoiceToImportArguments($user_choice);
        $args['toolset-themes'] = true;

        // load post forms
        if (file_exists($this->getPathImportFile('post-forms'))) {
            $zip      = new TT_Helper_Zip($this->getPathImportFile('post-forms'));
            $xml_file = $zip->fetchFileByExtension('.xml');

            $result = CRED_XML_Processor::importFromXMLString($xml_file, $args);

            if( is_wp_error( $result ) ) {
                return $result;
            }
        }

        // load user forms
        if (file_exists($this->getPathImportFile('user-forms'))) {
            $zip      = new TT_Helper_Zip($this->getPathImportFile('user-forms'));
            $xml_file = $zip->fetchFileByExtension('.xml');

            $result = CRED_XML_Processor::importUsersFromXMLString($xml_file, $args);

            if( is_wp_error( $result ) ) {
                return $result;
            }
        }

        return true;
    }

    /**
     * For CRED the overwrite key is 'force_overwrite_post_name' instead of 'force_import_post_name'
     *
     * @return mixed
     */
    protected function getKeyForImportItem()
    {
        return 'force_overwrite_post_name';
    }


}