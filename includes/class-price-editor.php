<?php

declare(strict_types=1);

/**
 * Price editor AJAX controller.
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once DARKTECH_PE_PLUGIN_DIR . 'includes/class-price-editor-lookups.php';
require_once DARKTECH_PE_PLUGIN_DIR . 'includes/class-price-editor-catalog-service.php';
require_once DARKTECH_PE_PLUGIN_DIR . 'includes/class-price-editor-history-repository.php';
require_once DARKTECH_PE_PLUGIN_DIR . 'includes/class-price-editor-history-service.php';
require_once DARKTECH_PE_PLUGIN_DIR . 'includes/class-price-editor-product-updater.php';

class DarkTech_Price_Editor
{
    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @var DarkTech_Price_Editor_Catalog_Service
     */
    private $catalog_service;

    /**
     * @var DarkTech_Price_Editor_Product_Updater
     */
    private $product_updater;

    /**
     * @var DarkTech_Price_Editor_History_Service
     */
    private $history_service;

    /**
     * Returns singleton instance.
     */
    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registers AJAX handlers.
     */
    private function __construct()
    {
        $lookups = new DarkTech_Price_Editor_Lookups();
        $history_repository = new DarkTech_Price_Editor_History_Repository();
        $this->catalog_service = new DarkTech_Price_Editor_Catalog_Service($lookups);
        $this->product_updater = new DarkTech_Price_Editor_Product_Updater($lookups);
        $this->history_service = new DarkTech_Price_Editor_History_Service($history_repository, $lookups);

        add_action('wp_ajax_darktech_pe_get_categories', [$this, 'get_categories']);
        add_action('wp_ajax_darktech_pe_get_tax_classes', [$this, 'get_tax_classes']);
        add_action('wp_ajax_darktech_pe_get_products', [$this, 'get_products']);
        add_action('wp_ajax_darktech_pe_get_change_history', [$this, 'get_change_history']);
        add_action('wp_ajax_darktech_pe_update_product', [$this, 'update_product']);
        add_action('wp_ajax_darktech_pe_update_products_limit', [$this, 'update_products_limit']);
    }

    /**
     * Checks nonce and capabilities.
     */
    private function verify_request(): void
    {
        if (! check_ajax_referer('darktech_pe_nonce', 'nonce', false)) {
            wp_send_json_error([
                'message' => __('Security check failed.', 'darktech-price-editor'),
            ]);
        }

        if (! current_user_can('edit_products')) {
            wp_send_json_error([
                'message' => __('You do not have permission to edit products.', 'darktech-price-editor'),
            ]);
        }
    }

    /**
     * Returns unslashed POST data after verify_request() has run.
     */
    private function get_post_data(): array
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verify_request() validates the nonce and the getter methods sanitize individual values.
        $post_data = wp_unslash($_POST);

        return is_array($post_data) ? $post_data : [];
    }

    /**
     * Returns a sanitized text request value.
     */
    private function get_text_request_value(string $key): string
    {
        $post_data = $this->get_post_data();
        $value = $post_data[$key] ?? '';

        return is_string($value) ? sanitize_text_field($value) : '';
    }

    /**
     * Returns a slug-like request value while keeping percent-encoded chars.
     */
    private function get_slug_request_value(string $key): string
    {
        $post_data = $this->get_post_data();
        $value = $post_data[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }

    /**
     * Returns a positive integer request value.
     */
    private function get_int_request_value(string $key): int
    {
        $post_data = $this->get_post_data();
        $value = $post_data[$key] ?? 0;

        if (! is_scalar($value)) {
            return 0;
        }

        return absint((string) $value);
    }

    /**
     * Sends categories list.
     */
    public function get_categories(): void
    {
        $this->verify_request();

        wp_send_json_success($this->catalog_service->get_categories());
    }

    /**
     * Sends tax classes list.
     */
    public function get_tax_classes(): void
    {
        $this->verify_request();

        wp_send_json_success($this->catalog_service->get_tax_classes());
    }

    /**
     * Sends product list.
     */
    public function get_products(): void
    {
        $this->verify_request();

        wp_send_json_success($this->catalog_service->get_products([
            'status'       => $this->get_text_request_value('status'),
            'category'     => $this->get_slug_request_value('category'),
            'search'       => $this->get_text_request_value('search'),
            'tax_status'   => $this->get_text_request_value('tax_status'),
            'tax_class'    => $this->get_slug_request_value('tax_class'),
            'stock_status' => $this->get_text_request_value('stock_status'),
        ]));
    }

    /**
     * Sends the latest change history rows.
     */
    public function get_change_history(): void
    {
        $this->verify_request();

        wp_send_json_success([
            'items' => $this->history_service->get_change_history(),
        ]);
    }

    /**
     * Updates a product field.
     */
    public function update_product(): void
    {
        $this->verify_request();

        $product_id = $this->get_int_request_value('product_id');
        $field = $this->get_text_request_value('field');
        $value = $field === 'tax_class'
            ? $this->get_slug_request_value('value')
            : $this->get_text_request_value('value');

        $result = $this->product_updater->update_product($product_id, $field, $value);

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
            ]);
        }

        if (isset($result['_history']) && is_array($result['_history'])) {
            $this->history_service->log_product_change(
                $result['_history'],
                (string) ($result['message'] ?? '')
            );
            unset($result['_history']);
        }

        wp_send_json_success($result);
    }

    /**
     * Updates the products display limit.
     */
    public function update_products_limit(): void
    {
        $this->verify_request();

        $limit = $this->get_int_request_value('limit');

        if ($limit <= 0) {
            wp_send_json_error([
                'message' => __('Please enter a valid number greater than 0.', 'darktech-price-editor'),
            ]);
        }

        update_option('darktech_pe_products_limit', $limit);

        wp_send_json_success([
            'limit' => $limit,
        ]);
    }
}
