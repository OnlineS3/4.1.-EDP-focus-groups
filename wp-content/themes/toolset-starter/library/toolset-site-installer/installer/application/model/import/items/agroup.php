<?php


abstract class TT_Import_Items_Group_Abstract implements TT_Import_Items_Group_Interface
{

    protected $allow_skip      = true;
    protected $allow_duplicate = true;
    protected $allow_overwrite = true;

    /**
     * Import data, can be xml string / zip path depending on class
     * @var string
     */
    protected $import_data;

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

    abstract public function __construct( $import_data );

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

    abstract protected function fetchItemsToImport();

    /**
     * Fetch the currently stored items (stored in the database)
     *
     * @return WP_Post[]|false
     */
    protected function fetchItemsPresent()
    {
        $args = array(
            'posts_per_page'         => -1,                         // all items
            'post_type'              => $this->getPostType(),      // products post types
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
        $items_import_file = $this->getItemsToImport();
        $items_modified    = $this->getItemsModified();

        if (! $items_import_file || ! $items_modified) {
            // no modified sitems
            return false;
        }

        // subtract the "MODIFIED by user"-items from all items in the import file
        $modified = array_uintersect($items_modified, $items_import_file, array($this, 'arrayDiffCompareItems'));

        return ! empty($modified) ? $modified : false;
    }

    /**
     * @return boolean
     */
    public function allowSkip()
    {
        return $this->allow_skip;
    }

    /**
     * @return boolean
     */
    public function allowDuplicate()
    {
        return $this->allow_duplicate;
    }

    /**
     * @return boolean
     */
    public function allowOverwrite()
    {
        return $this->allow_overwrite;
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
                $post->guid = $this->getItemEditLink( $post );
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


    /**
     * Returns the edit link
     *
     * @param $post WP_Post
     *
     * @return string
     */
    protected function getItemEditLink( $post ) {
        return get_edit_post_link( $post->ID );
    }

	/**
	 * This normalise the PHP function property_exists() and extends it to check
	 * for nested properties. To find nested properties use an array
	 * e.g. propertyExists( array( 'top-level', 'nested-in-top', 'nested-in-nested'), $obj );
	 *
	 * @param string|array $find
	 * @param object $obj
	 *
	 * @return bool
	 */
	protected function propertyExists( $find, $obj ) {
		if ( ! is_array( $find ) ) {
			return property_exists( $obj, $find );
		}

		$obj_nested = $obj;
		foreach ( $find as $property ) {
			if ( ! self::propertyExists( $property, $obj_nested ) ) {
				return false;
			}

			$obj_nested = $obj_nested->{$property};
		}

		return true;
	}

	/**
	 * Array Key exists with the possibility to check nested keys
	 * e.g. arrayKeyExists( array( 'top-level', 'nested-in-top', 'nested-in-nested'), $array );
	 *
	 * @param string|array $find
	 * @param object $array
	 *
	 * @return bool
	 */
	protected function arrayKeyExists( $find, $array ) {
		if ( ! is_array( $find ) ) {
			return array_key_exists( $find, $array );
		}

		$array_nested = $array;
		foreach ( $find as $nested ) {
			if ( ! self::arrayKeyExists( $nested, $array_nested ) ) {
				return false;
			}

			$array_nested = $array_nested[$nested];
		}

		return true;
	}
}