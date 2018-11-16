<?php

interface TT_Import_Items_Group_Interface {

    /**
     * Gives the post type name of the item group (e.g. view-template for Content Templates)
     * @return string
     */
    public function getPostType();

    /**
     * Items which are available to import (by the export file)
     *
     * @return WP_Post[]|false
     */
    public function getItemsToImport();

    /**
     * Get all items
     *
     * @return WP_Post[]|false
     */
    public function getItemsPresent();

    /**
     * Get all modified items (modified by the user)
     *
     * @return WP_Post[]|false
     */
    public function getItemsModified();

    /**
     * Get items which require a user decision
     * (modified items, which are also present in import file)
     *
     * @return WP_Post[]|false
     */
    public function getItemsRequireUserDecision();

    /**
     * @return boolean
     */
    public function allowSkip();

    /**
     * @return boolean
     */
    public function allowDuplicate();

    /**
     * @return boolean
     */
    public function allowOverwrite();

    /**
     * Get all not modified items
     *
     * @return WP_Post[]|false
     */
    public function getItemsNotModified();
}