<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Write-side product updates for the price editor.
 */
class DarkTech_Price_Editor_Product_Updater
{
    /**
     * @var DarkTech_Price_Editor_Lookups
     */
    private $lookups;

    /**
     * Allowed values for tax_status field.
     */
    private const ALLOWED_TAX_STATUSES = ['taxable', 'shipping', 'none'];

    /**
     * Allowed values for stock_status field.
     */
    private const ALLOWED_STOCK_STATUSES = ['instock', 'outofstock', 'onbackorder'];

    public function __construct(DarkTech_Price_Editor_Lookups $lookups)
    {
        $this->lookups = $lookups;
    }

    /**
     * Updates a single product field.
     *
     * @return array<string, mixed>|WP_Error
     */
    public function update_product(int $product_id, string $field, string $value)
    {
        if ($product_id <= 0 || $field === '') {
            return new WP_Error(
                'darktech_pe_invalid_request',
                __('Not enough data to update the product.', 'darktech-price-editor')
            );
        }

        $product = wc_get_product($product_id);
        if (! $product) {
            return new WP_Error(
                'darktech_pe_product_not_found',
                __('Product not found.', 'darktech-price-editor')
            );
        }

        switch ($field) {
            case 'title':
                return $this->update_title($product, $product_id, $value);

            case 'sku':
                return $this->update_sku($product, $product_id, $value);

            case 'regular_price':
                return $this->update_regular_price($product, $product_id, $value);

            case 'sale_price':
                return $this->update_sale_price($product, $product_id, $value);

            case 'tax_status':
                if (! in_array($value, self::ALLOWED_TAX_STATUSES, true)) {
                    return new WP_Error(
                        'darktech_pe_invalid_value',
                        __('Invalid tax status value.', 'darktech-price-editor')
                    );
                }
                return $this->update_tax_status($product, $product_id, $value);

            case 'tax_class':
                return $this->update_tax_class($product, $product_id, $value);

            case 'stock_status':
                if (! in_array($value, self::ALLOWED_STOCK_STATUSES, true)) {
                    return new WP_Error(
                        'darktech_pe_invalid_value',
                        __('Invalid stock status value.', 'darktech-price-editor')
                    );
                }
                return $this->update_stock_status($product, $product_id, $value);

            case 'category':
                return $this->update_category($product_id, $value);

            default:
                return new WP_Error(
                    'darktech_pe_unknown_field',
                    __('Unknown field for update.', 'darktech-price-editor')
                );
        }
    }

    /**
     * @param WC_Product $product
     * @return array<string, mixed>|WP_Error
     */
    private function update_title($product, int $product_id, string $value)
    {
        $old_value = (string) $product->get_name();
        $product->set_name($value);

        if (! $this->save_product($product)) {
            return $this->build_save_error();
        }

        return $this->build_success_response(
            sprintf(
                __('Updated product #%1$d title: %2$s -> %3$s', 'darktech-price-editor'),
                $product_id,
                $old_value,
                $value
            ),
            $old_value,
            $value,
            $this->build_history_context($product_id, 'title', $old_value, $value)
        );
    }

    /**
     * @param WC_Product $product
     * @return array<string, mixed>|WP_Error
     */
    private function update_sku($product, int $product_id, string $value)
    {
        $old_value = (string) $product->get_sku();
        $product->set_sku($value);

        if (! $this->save_product($product)) {
            return $this->build_save_error();
        }

        return $this->build_success_response(
            sprintf(
                __('Updated product #%1$d SKU: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                $product_id,
                $old_value,
                $value
            ),
            $old_value,
            $value,
            $this->build_history_context($product_id, 'sku', $old_value, $value)
        );
    }

    /**
     * @param WC_Product $product
     * @return array<string, mixed>|WP_Error
     */
    private function update_regular_price($product, int $product_id, string $value)
    {
        $old_value = (string) $product->get_regular_price();
        $product->set_regular_price($value);

        if (! $this->save_product($product)) {
            return $this->build_save_error();
        }

        return $this->build_success_response(
            sprintf(
                __('Updated product #%1$d regular price: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                $product_id,
                $old_value,
                $value
            ),
            $old_value,
            $value,
            $this->build_history_context($product_id, 'regular_price', $old_value, $value)
        );
    }

    /**
     * @param WC_Product $product
     * @return array<string, mixed>|WP_Error
     */
    private function update_sale_price($product, int $product_id, string $value)
    {
        $old_value = (string) $product->get_sale_price();
        $product->set_sale_price($value);

        if (! $this->save_product($product)) {
            return $this->build_save_error();
        }

        return $this->build_success_response(
            sprintf(
                __('Updated product #%1$d sale price: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                $product_id,
                $old_value,
                $value
            ),
            $old_value,
            $value,
            $this->build_history_context($product_id, 'sale_price', $old_value, $value)
        );
    }

    /**
     * @param WC_Product $product
     * @return array<string, mixed>|WP_Error
     */
    private function update_tax_status($product, int $product_id, string $value)
    {
        $old_value = (string) $product->get_tax_status();
        $old_label = $this->lookups->get_tax_status_label($old_value);
        $new_label = $this->lookups->get_tax_status_label($value);
        $product->set_tax_status($value);

        if (! $this->save_product($product)) {
            return $this->build_save_error();
        }

        return $this->build_success_response(
            sprintf(
                __('Updated product #%1$d tax status: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                $product_id,
                $old_label,
                $new_label
            ),
            $old_value,
            $value,
            $this->build_history_context($product_id, 'tax_status', $old_value, $value, $old_label, $new_label)
        );
    }

    /**
     * @param WC_Product $product
     * @return array<string, mixed>|WP_Error
     */
    private function update_tax_class($product, int $product_id, string $value)
    {
        $old_value = (string) $product->get_tax_class();
        $old_label = $this->lookups->get_tax_class_label($old_value);
        $new_label = $this->lookups->get_tax_class_label($value);
        $product->set_tax_class($value);

        if (! $this->save_product($product)) {
            return $this->build_save_error();
        }

        return $this->build_success_response(
            sprintf(
                __('Updated product #%1$d tax class: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                $product_id,
                $old_label,
                $new_label
            ),
            $old_value,
            $value,
            $this->build_history_context($product_id, 'tax_class', $old_value, $value, $old_label, $new_label)
        );
    }

    /**
     * @param WC_Product $product
     * @return array<string, mixed>|WP_Error
     */
    private function update_stock_status($product, int $product_id, string $value)
    {
        $old_value = (string) $product->get_stock_status();
        $old_label = $this->lookups->get_stock_status_label($old_value);
        $new_label = $this->lookups->get_stock_status_label($value);
        $product->set_stock_status($value);

        if (! $this->save_product($product)) {
            return $this->build_save_error();
        }

        return $this->build_success_response(
            sprintf(
                __('Updated product #%1$d stock status: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                $product_id,
                $old_label,
                $new_label
            ),
            $old_value,
            $value,
            $this->build_history_context($product_id, 'stock_status', $old_value, $value, $old_label, $new_label)
        );
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    private function update_category(int $product_id, string $value)
    {
        $old_category_ids = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
        $old_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
        $old_value = ! empty($old_categories)
            ? implode(', ', $old_categories)
            : $this->lookups->get_uncategorized_label();

        $category_id = (int) $value;
        if ($category_id > 0) {
            $result = wp_set_post_terms($product_id, [$category_id], 'product_cat');
            if (is_wp_error($result)) {
                return $result;
            }

            $new_category = get_term($category_id, 'product_cat');
            $new_value = $new_category && ! is_wp_error($new_category)
                ? (string) $new_category->name
                : $value;
        } else {
            $result = wp_set_post_terms($product_id, [], 'product_cat');
            if (is_wp_error($result)) {
                return $result;
            }

            $new_value = $this->lookups->get_uncategorized_label();
        }

        $old_ids_string = ! empty($old_category_ids) ? implode(',', $old_category_ids) : '0';

        return $this->build_success_response(
            sprintf(
                __('Updated product #%1$d category: "%2$s" -> "%3$s"', 'darktech-price-editor'),
                $product_id,
                $old_value,
                $new_value
            ),
            $old_value,
            $new_value,
            $this->build_history_context($product_id, 'category', $old_ids_string, (string) $category_id, $old_value, $new_value)
        );
    }

    /**
     * @param WC_Product $product
     */
    private function save_product($product): bool
    {
        return (bool) $product->save();
    }

    /**
     * @param array<string, mixed> $history_context
     * @return array<string, mixed>
     */
    private function build_success_response(
        string $message,
        string $old_value,
        string $new_value,
        array $history_context = []
    ): array
    {
        $response = [
            'message' => $message,
            'old_value' => $old_value,
            'new_value' => $new_value,
        ];

        if (! empty($history_context)) {
            $response['_history'] = $history_context;
        }

        return $response;
    }

    /**
     * Builds structured history metadata for a successful field update.
     *
     * @return array<string, mixed>
     */
    private function build_history_context(
        int $product_id,
        string $field_name,
        string $old_value,
        string $new_value,
        ?string $old_label = null,
        ?string $new_label = null
    ): array {
        return [
            'event_type' => 'product_field_updated',
            'product_id' => $product_id,
            'field_name' => $field_name,
            'payload' => [
                'product_id' => $product_id,
                'field' => $field_name,
                'old_value' => $old_value,
                'new_value' => $new_value,
                'old_label' => $old_label ?? $old_value,
                'new_label' => $new_label ?? $new_value,
            ],
        ];
    }

    private function build_save_error(): WP_Error
    {
        return new WP_Error(
            'darktech_pe_save_failed',
            __('Error saving changes.', 'darktech-price-editor')
        );
    }
}
