<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Formats and records price editor change history.
 */
class DarkTech_Price_Editor_History_Service
{
    /**
     * @var DarkTech_Price_Editor_History_Repository
     */
    private $repository;

    /**
     * @var DarkTech_Price_Editor_Lookups
     */
    private $lookups;

    public function __construct(
        DarkTech_Price_Editor_History_Repository $repository,
        DarkTech_Price_Editor_Lookups $lookups
    )
    {
        $this->repository = $repository;
        $this->lookups = $lookups;
    }

    /**
     * Persists a successful product change.
     *
     * @param array<string, mixed> $history_context
     */
    public function log_product_change(array $history_context, string $message_fallback): void
    {
        $user = wp_get_current_user();
        $payload = $history_context['payload'] ?? [];

        $this->repository->insert([
            'created_at_gmt' => current_time('mysql', true),
            'user_id' => get_current_user_id(),
            'user_display_name' => $user instanceof WP_User && $user->exists()
                ? $user->display_name
                : $this->get_unknown_user_label(),
            'event_type' => sanitize_key((string) ($history_context['event_type'] ?? 'product_field_updated')),
            'product_id' => isset($history_context['product_id']) ? (int) $history_context['product_id'] : null,
            'field_name' => isset($history_context['field_name']) ? sanitize_key((string) $history_context['field_name']) : null,
            'message_fallback' => $message_fallback,
            'payload_json' => is_array($payload) && ! empty($payload)
                ? wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
        ]);
    }

    /**
     * Returns the latest history items for the UI.
     *
     * @return array<int, array<string, string>>
     */
    public function get_change_history(int $limit = DarkTech_Price_Editor_History_Repository::MAX_ENTRIES): array
    {
        $rows = $this->repository->get_latest($limit);

        return array_map([$this, 'map_row_to_item'], $rows);
    }

    /**
     * Maps a database row to a frontend payload.
     *
     * @param array<string, mixed> $row
     * @return array<string, string>
     */
    private function map_row_to_item(array $row): array
    {
        $payload = $this->decode_payload($row['payload_json'] ?? null);
        $message_fallback = (string) ($row['message_fallback'] ?? '');

        return [
            'date_gmt' => (string) ($row['created_at_gmt'] ?? ''),
            'date_display' => $this->format_date_display((string) ($row['created_at_gmt'] ?? '')),
            'message' => $this->format_message(
                (string) ($row['event_type'] ?? ''),
                (string) ($row['field_name'] ?? ''),
                $payload,
                $message_fallback
            ),
            'user_display_name' => (string) ($row['user_display_name'] ?: $this->get_unknown_user_label()),
        ];
    }

    /**
     * Decodes a JSON payload.
     *
     * @return array<string, mixed>
     */
    private function decode_payload($raw_payload): array
    {
        if (! is_string($raw_payload) || $raw_payload === '') {
            return [];
        }

        $decoded = json_decode($raw_payload, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Formats a stored GMT date for display.
     */
    private function format_date_display(string $created_at_gmt): string
    {
        if ($created_at_gmt === '') {
            return '';
        }

        $timestamp = strtotime($created_at_gmt . ' UTC');
        if ($timestamp === false) {
            return $created_at_gmt;
        }

        return wp_date(
            get_option('date_format') . ' ' . get_option('time_format'),
            $timestamp
        ) ?: $created_at_gmt;
    }

    /**
     * Formats a localized message for a history row.
     *
     * @param array<string, mixed> $payload
     */
    private function format_message(
        string $event_type,
        string $field_name,
        array $payload,
        string $message_fallback
    ): string {
        if ($event_type !== 'product_field_updated') {
            return $message_fallback;
        }

        $product_id = isset($payload['product_id']) ? (int) $payload['product_id'] : 0;
        if ($product_id <= 0) {
            return $message_fallback;
        }

        [$old_value, $new_value] = $this->get_display_values($field_name, $payload);

        switch ($field_name) {
            case 'title':
                return sprintf(
                    /* translators: 1: Product ID, 2: previous title, 3: new title. */
                    __('Updated product #%1$d title: %2$s -> %3$s', 'darktech-price-editor'),
                    $product_id,
                    $old_value,
                    $new_value
                );

            case 'sku':
                return sprintf(
                    /* translators: 1: Product ID, 2: previous SKU, 3: new SKU. */
                    __('Updated product #%1$d SKU: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                    $product_id,
                    $old_value,
                    $new_value
                );

            case 'regular_price':
                return sprintf(
                    /* translators: 1: Product ID, 2: previous regular price, 3: new regular price. */
                    __('Updated product #%1$d regular price: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                    $product_id,
                    $old_value,
                    $new_value
                );

            case 'sale_price':
                return sprintf(
                    /* translators: 1: Product ID, 2: previous sale price, 3: new sale price. */
                    __('Updated product #%1$d sale price: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                    $product_id,
                    $old_value,
                    $new_value
                );

            case 'tax_status':
                return sprintf(
                    /* translators: 1: Product ID, 2: previous tax status label, 3: new tax status label. */
                    __('Updated product #%1$d tax status: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                    $product_id,
                    $old_value,
                    $new_value
                );

            case 'tax_class':
                return sprintf(
                    /* translators: 1: Product ID, 2: previous tax class label, 3: new tax class label. */
                    __('Updated product #%1$d tax class: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                    $product_id,
                    $old_value,
                    $new_value
                );

            case 'stock_status':
                return sprintf(
                    /* translators: 1: Product ID, 2: previous stock status label, 3: new stock status label. */
                    __('Updated product #%1$d stock status: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                    $product_id,
                    $old_value,
                    $new_value
                );

            case 'category':
                return sprintf(
                    /* translators: 1: Product ID, 2: previous category name, 3: new category name. */
                    __('Updated product #%1$d category: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                    $product_id,
                    $old_value,
                    $new_value
                );
        }

        return $message_fallback;
    }

    /**
     * Returns a text value from the payload with fallback keys.
     *
     * @param array<string, mixed> $payload
     */
    private function get_payload_text(array $payload, string $preferred_key, string $fallback_key): string
    {
        if (isset($payload[$preferred_key])) {
            return (string) $payload[$preferred_key];
        }

        return isset($payload[$fallback_key]) ? (string) $payload[$fallback_key] : '';
    }

    /**
     * Returns display values for a stored change in the current locale.
     *
     * @param array<string, mixed> $payload
     * @return array{0:string,1:string}
     */
    private function get_display_values(string $field_name, array $payload): array
    {
        $old_raw_value = $this->get_payload_text($payload, 'old_value', 'old_value');
        $new_raw_value = $this->get_payload_text($payload, 'new_value', 'new_value');

        switch ($field_name) {
            case 'tax_status':
                return [
                    $this->lookups->get_tax_status_label($old_raw_value),
                    $this->lookups->get_tax_status_label($new_raw_value),
                ];

            case 'tax_class':
                return [
                    $this->lookups->get_tax_class_label($old_raw_value),
                    $this->lookups->get_tax_class_label($new_raw_value),
                ];

            case 'stock_status':
                return [
                    $this->lookups->get_stock_status_label($old_raw_value),
                    $this->lookups->get_stock_status_label($new_raw_value),
                ];

            default:
                return [
                    $this->get_payload_text($payload, 'old_label', 'old_value'),
                    $this->get_payload_text($payload, 'new_label', 'new_value'),
                ];
        }
    }

    /**
     * Returns a fallback label for unknown users.
     */
    private function get_unknown_user_label(): string
    {
        return __('Unknown user', 'darktech-price-editor');
    }
}
