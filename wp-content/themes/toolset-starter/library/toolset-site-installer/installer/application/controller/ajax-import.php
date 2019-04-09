<?php


class TT_Controller_Ajax_Import extends TT_Controller_Abstract
{
    const COMMAND = 'import';

    // these constants are defining the choices a user has on each modified item.
    const USER_CHOICE_SKIP = 'skip';
    const USER_CHOICE_DUPLICATE = 'duplicate';
    const USER_CHOICE_OVERWRITE = 'overwrite';
    const USER_CHOICE_DECIDE_PER_ITEM = 'decide_per_item';

    public function handleAjaxRequest()
    {
        $export = $this->settings->getFiles();

        // for wordpress import the user has the choice to install or not
        $user_choice_wordpress = $_REQUEST['tt_user_choice_import_wordpress'] ? true : false;


        if ( ! $export->hasPluginImports()
             && ( ! $export->hasWordpressImport() || ! $user_choice_wordpress)
        ) {
            // $this->response_ajax->setMessage(__('Import was sucessfully completed.', 'toolset-themes'));

            return $this->response_ajax->success()->response();
        }

        return $this->ajaxImportAllAtOnce($export, $user_choice_wordpress);
        // $this->ajaxImportPerPlugin( $export, $user_choice_wordpress );
    }

    /**
     * Get user decisions for modified items by POST/GET
     *
     * @return array|bool
     */
    private function getUserDecisionsForModifiedItems()
    {
        $user_decisions_modified_items = array();
        if ($_REQUEST['tt_modified_items'] != '0') {
            foreach ($_REQUEST['tt_modified_items'] as $key => $item) {
                $user_decisions_modified_items[$item['value']][] = $item['name'];
            }
        }

        return ! empty($user_decisions_modified_items) ? $user_decisions_modified_items : false;
    }

    /**
     * Get user decisions for modified items by POST/GET
     *
     * @return array|bool
     */
    private function getUserDecisionsForModifiedItemsMaster()
    {
        switch( $_REQUEST['tt_master_decision'] ) {
            case self::USER_CHOICE_DECIDE_PER_ITEM:
            case self::USER_CHOICE_OVERWRITE:
            case self::USER_CHOICE_SKIP:
                return $_REQUEST['tt_master_decision'];
        }

        return false;
    }

    private function finishStep(TT_Import_Interface $next_import)
    {
	    // used by integration plugin to remove layout from "Shop" page
	    do_action( 'tt_import_finished_' . $next_import->getSlug() );

        $this->settings
            ->getProtocol()
            ->setImportFinished($next_import->getSlug());
    }

    /**
     * @param $export TT_Settings_Files_Interface
     * @param $user_choice_wordpress bool
     */
    private function ajaxImportAllAtOnce(TT_Settings_Files_Interface $export, $user_choice_wordpress)
    {
        $all_imports = $export->getAllImports($user_choice_wordpress);

        $items_require_user_decision = array();
        foreach ($all_imports as $import) {
        	if( ! $additional_user_decisions = $import->getItemsRequireUserDecision() ) {
        		// no items for this import require an user decision
        	    continue;
	        }

            $items_require_user_decision = array_merge(
                $items_require_user_decision,
	            $additional_user_decisions
            );
        }

        if ( ! empty($items_require_user_decision)) {
            // we have modified items...
            return $this->ajaxImportAllAtOnceWithModifiedItems($all_imports, $items_require_user_decision);
        }

        // we have items, but nothing requires a user decision, import all...
        $this->ajaxImportAllAtOnceRunImport($all_imports);
    }

    /**
     * Run the import for all available imports
     * NOTE: This function aborts on error and sends a response message
     *
     * @param $all_imports TT_Import_Interface[]
     * @param bool|array $user_decisions
     */
    private function ajaxImportAllAtOnceRunImport($all_imports, $user_decisions = false)
    {
	    $process_handler  = new TT_Import_Process();
		$process_handler->clearProcess();

        foreach ($all_imports as $import) {
            $items_to_import = $user_decisions !== false
                ? $user_decisions
                : $import->getItemsToImport();

	        $import->setProcessHandler( $process_handler );
            $result = $import->import($items_to_import);

            if (is_wp_error($result)) {
                // Error
                return $this->response_ajax->failed()->setMessage($this->wordpressErrorMessage($result))->response();
            }

            // store that this import is done
            $this->finishStep($import);
        }

        // update url if author base url is available
	    if( $this->settings->getAuthorBaseUrl() ) {
			$this->updateSiteURLs( $this->settings->getAuthorBaseUrl() );
	    }

	    // all imports are done
	    $process_handler->clearProcess();
	    $this->response_ajax->setMessage( __('Import was completed.', 'toolset-themes') )->response();
    }

    /**
     * @param $all_imports TT_Import_Interface[]
     * @param $items_require_user_decision
     */
    private function ajaxImportAllAtOnceWithModifiedItems($all_imports, $items_require_user_decision)
    {
        // master decision
        $decision_master = $this->getUserDecisionsForModifiedItemsMaster();

        if( ! $decision_master ) {
            return $this->response_ajax->setMessage(
                __('What to do with modified items?', 'toolset-themes')
            )->response(array(
                'modified_items_master_decision' => 'required'
            ));
        }

        // master decision: keep own changes
        if( $decision_master == self::USER_CHOICE_SKIP ) {
            $decision_per_item = array();

            foreach( $all_imports as $import_plugin ) {
                foreach( $import_plugin->getItemsModified() as $post ) {
                    $decision_per_item[self::USER_CHOICE_SKIP][] = $post->post_name;
                }
            }

            return $this->ajaxImportAllAtOnceRunImport($all_imports, $decision_per_item);
        }

        // master decision: overwrite own decisions
        if( $decision_master == self::USER_CHOICE_OVERWRITE ) {
            $decision_per_item = array();

            foreach( $all_imports as $import_plugin ) {
                foreach( $import_plugin->getItemsToImport() as $post ) {
                    $decision_per_item[self::USER_CHOICE_OVERWRITE][] = $post->post_name;
                }
            }

            return $this->ajaxImportAllAtOnceRunImport($all_imports, $decision_per_item);
        }

        // master decision: decision per item
        $decision_per_item = $this->getUserDecisionsForModifiedItems();

        if ( ! $decision_per_item) {
            return $this->response_ajax->setMessage(
                __('Here you have a list of your modified items, please decide what to do.', 'toolset-themes')
            )->response(array(
                'plugin'         => '',
                'modified_items' => $items_require_user_decision
            ));
        }

        $this->ajaxImportAllAtOnceRunImport($all_imports, $decision_per_item);
    }

    /**
     * This will ask for user decisions per plugin and not all in one dialog
     *
     * @param $export TT_Settings_Files_Interface
     * @param $user_choice_wordpress
     *
     * @deprecated We decided to show all items of all plugins in one dialog
     *
     * @return mixed
     */
    private function ajaxImportPerPlugin(TT_Settings_Files_Interface $export, $user_choice_wordpress)
    {
        $next_import                 = $export->getNextImport($user_choice_wordpress);
        $items_require_user_decision = $next_import->getItemsRequireUserDecision();

        if ($items_require_user_decision) {
            // we have modified items...
            return $this->ajaxImportPerPluginWithModifiedItems($next_import, $items_require_user_decision);
        }

        // we have items, but nothing requires a user decision, import all...
        $result = $next_import->import($next_import->getItemsToImport());

        if (is_wp_error($result)) {
            // Error
            return $this->response_ajax->failed()->setMessage($this->wordpressErrorMessage($result))->response();
        }

        // success
        $this->finishStep($next_import);
        $this->response_ajax->setMessage(sprintf(
            __('Import for %s was sucessfully completed.', 'toolset-themes'),
            ucfirst($next_import->getSlug())))->response();

        echo json_encode($this->response_ajax);
        die(1);
    }

    /**
     * @param TT_Import_Interface $next_import
     * @param $items_require_user_decision
     *
     * @deprecated This is only used by ajaxImportPerPlugin()
     *
     * @return mixed
     */
    private function ajaxImportPerPluginWithModifiedItems(
        TT_Import_Interface $next_import,
        $items_require_user_decision
    ) {
        $user_decisions_modified_items = $this->getUserDecisionsForModifiedItems();

        if ( ! $user_decisions_modified_items) {
            return $this->response_ajax->setMessage(
                sprintf(
                    __('%s wants to import items, which you have modified. What should %s do?', 'toolset-themes'),
                    ucfirst($next_import->getSlug()), ucfirst($next_import->getSlug()))
            )->response(array(
                'plugin'         => $next_import->getTitle(),
                'modified_items' => $items_require_user_decision
            ));
        }

        $result = $next_import->import($user_decisions_modified_items);

        if (is_wp_error($result)) {
            // Error
            return $this->response_ajax->failed()->setMessage($this->wordpressErrorMessage($result))->response();
        }

        // success
        $this->finishStep($next_import);
        $this->response_ajax->setMessage(sprintf(
            __('Import for %s was sucessfully completed.', 'toolset-themes'),
            ucfirst($next_import->getSlug())))->response();
    }

    private function updateSiteURLs( $old_url ) {
    	global $wpdb;

    	$new_url = site_url();

    	// make sure old and new url not having trailing slash
	    $new_url = rtrim( $new_url, '/' );
	    $old_url = rtrim( $old_url, '/' );

	    if( $wpdb ) {
		    $wpdb->query( "UPDATE {$wpdb->posts} SET post_content=(REPLACE (post_content, '" . $old_url . "', '" . $new_url . "' ) );" );
		    $wpdb->query( "UPDATE {$wpdb->options} SET option_value=(REPLACE (option_value, '" . $old_url . "', '" . $new_url . "' ) );" );
		    $wpdb->query( "UPDATE {$wpdb->postmeta} SET meta_value=(REPLACE (meta_value, '" . $old_url . "', '" . $new_url . "' ) );" );
	    }
    }
}