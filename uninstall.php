<?php

/**
 * Uninstall handler for DarkTech Price Editor.
 *
 * Removes plugin data from the database when the plugin is deleted
 * through the WordPress admin interface.
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Remove the change history table.
$table_name = $wpdb->prefix . 'darktech_price_editor_logs';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// Remove plugin options.
delete_option('darktech_pe_db_version');
delete_option('darktech_pe_products_limit');
