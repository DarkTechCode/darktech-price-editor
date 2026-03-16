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
$darktech_pe_table_name = $wpdb->prefix . 'darktech_price_editor_logs';

if (preg_match('/^[A-Za-z0-9_]+$/', $darktech_pe_table_name) === 1) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uninstall needs to drop the plugin's own table after validating the identifier.
    $wpdb->query("DROP TABLE IF EXISTS `{$darktech_pe_table_name}`");
}

// Remove plugin options.
delete_option('darktech_pe_db_version');
delete_option('darktech_pe_products_limit');
