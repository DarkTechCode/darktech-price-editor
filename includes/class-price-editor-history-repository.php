<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Persists change history rows for the price editor.
 */
class DarkTech_Price_Editor_History_Repository
{
    public const MAX_ENTRIES = 100;

    /**
     * @var bool|null
     */
    private $table_exists = null;

    /**
     * Returns the journal table name.
     */
    public function get_table_name(): string
    {
        return darktech_pe_get_history_table_name();
    }

    /**
     * Returns whether the history table exists.
     */
    public function table_exists(): bool
    {
        if ($this->table_exists !== null) {
            return $this->table_exists;
        }

        global $wpdb;

        $table_name = $this->get_table_name();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Checks whether the plugin history table exists.
        $found_table = $wpdb->get_var(
            $wpdb->prepare('SHOW TABLES LIKE %s', $table_name)
        );

        $this->table_exists = (string) $found_table === $table_name;

        return $this->table_exists;
    }

    /**
     * Inserts a history row and trims old entries.
     *
     * @param array<string, mixed> $entry
     */
    public function insert(array $entry): bool
    {
        global $wpdb;

        if (! $this->table_exists()) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Writes a row into the plugin's custom history table.
        $result = $wpdb->insert(
            $this->get_table_name(),
            [
                'created_at_gmt' => (string) ($entry['created_at_gmt'] ?? current_time('mysql', true)),
                'user_id' => (int) ($entry['user_id'] ?? 0),
                'user_display_name' => (string) ($entry['user_display_name'] ?? ''),
                'event_type' => (string) ($entry['event_type'] ?? ''),
                'product_id' => isset($entry['product_id']) ? (int) $entry['product_id'] : null,
                'field_name' => isset($entry['field_name']) ? (string) $entry['field_name'] : null,
                'message_fallback' => (string) ($entry['message_fallback'] ?? ''),
                'payload_json' => isset($entry['payload_json']) ? (string) $entry['payload_json'] : null,
            ],
            [
                '%s',
                '%d',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
            ]
        );

        if ($result === false) {
            return false;
        }

        $this->trim_to_limit(self::MAX_ENTRIES);

        return true;
    }

    /**
     * Returns the latest history rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_latest(int $limit = self::MAX_ENTRIES): array
    {
        global $wpdb;

        if (! $this->table_exists()) {
            return [];
        }

        $limit = max(1, $limit);
        $query = $wpdb->prepare(
            'SELECT id, created_at_gmt, user_id, user_display_name, event_type, product_id, field_name, message_fallback, payload_json
            FROM %i
            ORDER BY id DESC
            LIMIT %d',
            $this->get_table_name(),
            $limit
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared -- Reads rows from the plugin's custom history table with a prepared query.
        $rows = $wpdb->get_results($query, ARRAY_A);

        return is_array($rows) ? $rows : [];
    }

    /**
     * Keeps only the latest configured number of rows.
     */
    public function trim_to_limit(int $limit): void
    {
        global $wpdb;

        if (! $this->table_exists()) {
            return;
        }

        $limit = max(1, $limit);
        $offset = max(0, $limit - 1);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Reads the cutoff row ID from the plugin's custom history table.
        $boundary_id = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id
                FROM %i
                ORDER BY id DESC
                LIMIT 1 OFFSET %d',
                $this->get_table_name(),
                $offset
            )
        );

        if ($boundary_id === null) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Trims old rows from the plugin's custom history table.
        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM %i WHERE id < %d',
                $this->get_table_name(),
                (int) $boundary_id
            )
        );
    }
}
