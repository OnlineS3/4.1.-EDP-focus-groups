<?php


abstract class TT_Import_Abstract implements TT_Import_Interface
{
    /**
     * @var string path to import file
     */
    protected $path_import_file;

    /**
     * @var TT_Import_Items_Group_Interface[]
     */
    protected $items_groups;

    /**
     * All items, which are currently present, of the managed $product_post_types
     * @var null|false|array|WP_Post[]
     */
    protected $items_present;

    /**
     * Items which are available to import
     * @var null|false|array|WP_Post[]
     */
    protected $items_import;

    /**
     * All new items (import items minus present items)
     * @var null|false|array|WP_Post[]
     */
    protected $items_new;

    /**
     * All modified items of the managed $product_item_keys
     * @var null|false|array|WP_Post[]
     */
    protected $items_modified;

    /**
     * All not modified items of the managed $product_item_keys
     * @var null|false|array|WP_Post[]
     */
    protected $items_not_modified;

    /**
     * Key for last modified option
     *
     * @var string
     */
    protected $key_option_last_modified = '_toolset_edit_last';

	/**
	 * @var TT_Import_Process
	 */
	protected $process_handler;

	/**
	 * @return TT_Import_Process
	 */
	public function getProcessHandler() {
		return $this->process_handler;
	}

	/**
	 * @param TT_Import_Process $process_handler
	 */
	public function setProcessHandler( TT_Import_Process $process_handler ) {
		$this->process_handler = $process_handler;
	}

	/**
     * @param string|array $path_import_file
     */
    public function setPathImportFile($path_import_file)
    {
        $this->path_import_file = $path_import_file;
    }

    /**
     * @return string
     */
    protected function getPathImportFile()
    {
        return $this->path_import_file;
    }

    /**
     * Import functions of Toolset plugins provide an option TO SKIP ITEMS by post name
     * This function must returns the name of the option.
     *
     * @return string
     */
    protected function getKeyForSkippingItem()
    {
        return 'force_skip_post_name';
    }

    /**
     * Import functions of Toolset plugins provide an option TO DUPLICATE ITEMS by post name
     * This function must returns the name of the option.
     *
     * @return mixed
     */
    protected function getKeyForDuplicateItem()
    {
        return 'force_duplicate_post_name';
    }

    /**
     * Import functions of Toolset plugins provide an option TO IMPORT(& OVERWRITE) ITEMS by post name
     * This function must returns the name of the option.
     *
     * @return mixed
     */
    protected function getKeyForImportItem()
    {
        return 'force_import_post_name';
    }


    /**
     * @return array|WP_Post[]|false
     */
    protected function fetchItemsToImport()
    {
    	if( ! is_array( $this->items_groups ) || empty( $this->items_groups ) ) {
    		return false;
	    }

        $all_items = array();

        foreach ($this->items_groups as $items_group) {
            if ($items = $items_group->getItemsToImport()) {
                foreach ($items as $item) {
                    $all_items[] = $item;
                }
            }
        }

        return ! empty($all_items) ? $all_items : false;
    }

    /**
     * Items which are available to import (by the export file)
     *
     * @return WP_Post[]|false
     */
    public function getItemsToImport()
    {
        if ($this->items_import === null) {
            $this->items_import = $this->fetchItemsToImport();
        }

        return $this->items_import;
    }

    /**
     * Fetch the currently stored items (stored in the database)
     *
     * @return WP_Post[]|false
     */
    protected function fetchItemsPresent()
    {
        $args = array(
            'posts_per_page'         => -1,                         // all items
            'post_type'              => $this->getPostTypes(),      // products post types
            'post_status'            => 'any',                      // all statuses
            'no_found_rows'          => false,                      // no post count needed
            'update_post_term_cache' => false,                      // no terms needed
            'update_post_meta_cache' => false,                      // no meta needed
            'cache_results'          => false,                      // no caching needed
            'suppress_filters'       => true,                       // no filters for this query
        );

        $query = new WP_Query($args);

        if (count($query->posts) === 0) {
            // no items
            return false;
        }

        return $query->posts;
    }

    protected function getPostTypes(){
        $post_types = array();
        foreach ($this->items_groups as $items_group) {
            $post_types[] = $items_group->getPostType();
        }

        return $post_types;
    }

    /**
     * Translate user choice keys to the related plugin arguments keys
     *
     * @param $user_choice
     *
     * @return array
     */
    protected function translateUserChoiceToImportArguments($user_choice)
    {
        if (! is_array($user_choice)) {
            return array();
        }

        $args = array();

        if (array_key_exists(TT_Controller_Ajax_Import::USER_CHOICE_SKIP, $user_choice)) {
            $args[$this->getKeyForSkippingItem()] = $user_choice[TT_Controller_Ajax_Import::USER_CHOICE_SKIP];
        }

        if (array_key_exists(TT_Controller_Ajax_Import::USER_CHOICE_DUPLICATE, $user_choice)) {
            $args[$this->getKeyForDuplicateItem()] = $user_choice[TT_Controller_Ajax_Import::USER_CHOICE_DUPLICATE];
        }

        if (array_key_exists(TT_Controller_Ajax_Import::USER_CHOICE_OVERWRITE, $user_choice)) {
            $args[$this->getKeyForImportItem()] = $user_choice[TT_Controller_Ajax_Import::USER_CHOICE_OVERWRITE];
        }

        $args = $this->skipItemsWithoutUserDecision($args);
        $args = $this->importNotModifiedAndNewItems($args);

        return $args;
    }

    /**
     * Get all items
     *
     * @return WP_Post[]|false
     */
    public function getItemsPresent()
    {
        if ($this->items_present === null) {
            $this->items_present = $this->fetchItemsPresent();
        }

        return $this->items_present;
    }

    /**
     * Get all modified items (modified by the user)
     *
     * @return WP_Post[]|false
     */
    public function getItemsNew()
    {
        if ($this->items_new === null) {
            $this->items_new = $this->fetchItemsNew();
        }

        return $this->items_new;
    }


    /**
     * Get all modified items (modified by the user)
     *
     * @return WP_Post[]|false
     */
    public function getItemsModified()
    {
        if ($this->items_modified === null) {
            $this->items_modified = $this->fetchItemsModified();
        }

        return $this->items_modified;
    }

    /**
     * Get items which require a user decision
     * (modified items, which are also present in import file)
     *
     * @return WP_Post[]|false
     */
    public function getItemsRequireUserDecision()
    {
        if( ! is_array( $this->items_groups ) || empty( $this->items_groups ) ) {
        	return false;
        }

	    $items = array();

        foreach ($this->items_groups as $items_group) {
            if ($items_group->getItemsRequireUserDecision()) {
                $items[$items_group->getPostType()]['title']              = $items_group->getTitle();
                $items[$items_group->getPostType()]['allowed_operations'] = array(
                    'skip'      => $items_group->allowSkip() ? 1 : 0,
                    'duplicate' => $items_group->allowDuplicate() ? 1 : 0,
                    'overwrite' => $items_group->allowOverwrite() ? 1 : 0
                );

                $items[$items_group->getPostType()]['items'] = $items_group->getItemsRequireUserDecision();
            }
        }

        if (empty($items)) {
            // no modified sitems
            return false;
        }

        return $items;
    }

    /**
     * Comparing twice posts
     *
     * @param WP_Post $item_a
     * @param WP_Post $item_b
     *
     * @return int if both posts are the same the return will be 0
     */
    protected function arrayDiffCompareItems($item_a, $item_b)
    {
        return strcmp($item_a->post_name, $item_b->post_name);
    }


    /**
     * Get all not modified items
     *
     * @return WP_Post[]|false
     */
    public function getItemsNotModified()
    {
        if ($this->items_not_modified === null) {
            $this->items_not_modified = $this->fetchItemsNotModified();
        }

        return $this->items_not_modified;
    }

    /**
     * Fetch all modified items
     *
     * @return array|WP_Post[]|false
     */
    protected function fetchItemsModified()
    {
        if ($this->getItemsPresent() === false) {
            // no items at all = no modified items
            return false;
        }

        $items_modified = array();

        foreach ($this->getItemsPresent() as $post) {
            // loop through all items and get the timestamp of last edit
            $last_modified_timestamp = get_post_meta($post->ID, $this->key_option_last_modified, true);

            if (! empty($last_modified_timestamp)) {
                // if last edit timestamp exists, we have modified post
                $items_modified[] = $post;
            }
        }

        // return modified items if exists, otherwise false
        return ! empty($items_modified) ? $items_modified : false;
    }


    /**
     * Fetch all not modified items
     *
     * @return array|WP_Post[]|false
     */
    protected function fetchItemsNotModified()
    {
        if ($this->getItemsPresent() === false) {
            // no items at all
            return false;
        }

        if ($this->getItemsModified() === false) {
            // no modified items, means all items are not modified
            return $this->getItemsPresent();
        }

        // return not modified items = difference between all and modified items
        return array_udiff($this->getItemsPresent(), $this->getItemsModified(),
            array($this, 'arrayDiffCompareItemsById'));
    }


    /**
     * Fetch all new items
     *
     * @return array|WP_Post[]|false
     */
    protected function fetchItemsNew()
    {
        if ($this->getItemsToImport() === false) {
            // no items at all
            return false;
        }

        if ($this->getItemsPresent() === false) {
            // no modified items, means all items are not modified
            return $this->getItemsToImport();
        }

        // return not modified items = difference between all import items and present items
        return array_udiff($this->getItemsToImport(), $this->getItemsPresent(),
            array($this, 'arrayDiffCompareItems'));
    }

    /**
     * Comparing twice posts
     *
     * @param $item_a
     * @param $item_b
     *
     * @return int if both posts are the same the return will be 0
     */
    protected function arrayDiffCompareItemsById($item_a, $item_b)
    {
        return $item_a->ID - $item_b->ID;
    }

    protected function importNotModifiedAndNewItems($items_actions)
    {
        $items_not_modified = $this->getItemsNotModified() ? $this->getItemsNotModified() : array();
        $items_new          = $this->getItemsNew() ? $this->getItemsNew() : array();

        $items = array_merge( $items_not_modified, $items_new );

        foreach ($items as $item) {
            $slug = property_exists($item, 'slug') && ! empty($item->slug)
                ? $item->slug
                : $item->post_name;

            $items_actions[$this->getKeyForImportItem()][] = $slug;
        }

        return $items_actions;
    }

    protected function skipItemsWithoutUserDecision($items_actions)
    {
        $required_choices = $this->getItemsRequireUserDecision();

        if (! $required_choices) {
            return $items_actions;
        }

        foreach ($required_choices as $key => $group) {
            foreach ($group['items'] as $item) {
                // beside groups we have 'slug', groups are registered as posts, so we have post_name there
                $slug = property_exists($item, 'slug') && ! empty($item->slug)
                    ? $item->slug
                    : $item->post_name;

                if ((! array_key_exists($this->getKeyForSkippingItem(), $items_actions)
                     || ! in_array($slug, $items_actions[$this->getKeyForSkippingItem()]))
                    && (! array_key_exists($this->getKeyForDuplicateItem(), $items_actions)
                        || ! in_array($slug, $items_actions[$this->getKeyForDuplicateItem()]))
                    && (! array_key_exists($this->getKeyForImportItem(), $items_actions)
                        || ! in_array($slug, $items_actions[$this->getKeyForImportItem()]))
                ) {
                    // keep users version if there is no user decision (should not happen, but safety first)
                    $items_actions[$this->getKeyForSkippingItem()][] = $slug;
                }
            }
        }

        return $items_actions;
    }

    public function processIncreaseTodo() {
    	if( ! $this->getProcessHandler() ) {
    		return;
	    }

	    $this->getProcessHandler()->increaseTodo();
    }


	public function processIncreaseTodoDone() {
		if( ! $this->getProcessHandler() ) {
			return;
		}

		$this->getProcessHandler()->increaseTodoDone();
	}
}