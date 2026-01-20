<?php

namespace SIT\Search\Services;

abstract class Module
{
    protected static $module = '';

    /**
     * Synchronize data from an external source.
     *
     * @param int $page The current page for pagination.
     * @param int $per_page The number of items per page.
     * @param int $limit  The number of items to sync.
     * @return void
     */
    abstract public static function sync(int $page = 1, int $per_page = 1, int $limit = 1);

    /**
     * Retrieve the fields associated with the module.
     *
     * @return array
     */
    public static function get_fields()
    {
        $zoho = new Zoho();
        return $zoho->get_fields(static::$module);
    }

    /**
     * Retrieve items from the external source.
     *
     * @param int $page The current page for pagination.
     * @param int $per_page The number of items per page.
     * @return array
     */
    public static function get_items($page = 1, $per_page = 10)
    {
        $zoho = new Zoho();
        return $zoho->request(static::$module . '?page=' . $page . '&per_page=' . $per_page);
    }

    /**
     * Create a new item in the local system.
     *
     * @param array $data The data for the new item.
     * @return int The ID of the created item.
     */
    abstract public static function create_item($data);

    /**
     * Retrieve a specific item by ID.
     *
     * @param int $post_id The ID of the item.
     * @param string $type The type of item (default: 'post').
     * @return array|\WP_Post|null
     */
    abstract public static function get_item($post_id, $type = 'post');
}
