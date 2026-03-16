<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Shared localized labels and lookup helpers.
 */
class DarkTech_Price_Editor_Lookups
{
    /**
     * Returns available tax classes with labels.
     *
     * @return array<int, array<string, string>>
     */
    public function get_tax_classes_list(): array
    {
        $tax_classes = [
            [
                'slug' => '',
                'name' => darktech_pe_get_default_tax_class_label(),
            ],
        ];

        if (class_exists('WC_Tax')) {
            foreach (WC_Tax::get_tax_classes() as $class_name) {
                $tax_classes[] = [
                    'slug' => sanitize_title($class_name),
                    'name' => $class_name,
                ];
            }
        }

        return $tax_classes;
    }

    /**
     * Returns a localized tax class label by slug.
     */
    public function get_tax_class_label(string $slug): string
    {
        foreach ($this->get_tax_classes_list() as $tax_class) {
            if (($tax_class['slug'] ?? '') === $slug) {
                return $tax_class['name'];
            }
        }

        return $slug !== '' ? $slug : darktech_pe_get_default_tax_class_label();
    }

    /**
     * Returns a localized stock status label by slug.
     */
    public function get_stock_status_label(string $status): string
    {
        $labels = darktech_pe_get_stock_status_labels();

        return $labels[$status] ?? $status;
    }

    /**
     * Returns a localized tax status label by slug.
     */
    public function get_tax_status_label(string $status): string
    {
        $labels = darktech_pe_get_tax_status_labels();

        return $labels[$status] ?? $status;
    }

    /**
     * Returns a localized category fallback label.
     */
    public function get_uncategorized_label(): string
    {
        return darktech_pe_get_uncategorized_label();
    }
}
