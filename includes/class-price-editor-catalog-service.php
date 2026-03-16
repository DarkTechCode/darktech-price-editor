<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Read-side catalog queries for the price editor.
 */
class DarkTech_Price_Editor_Catalog_Service
{
    /**
     * @var DarkTech_Price_Editor_Lookups
     */
    private $lookups;

    public function __construct(DarkTech_Price_Editor_Lookups $lookups)
    {
        $this->lookups = $lookups;
    }

    /**
     * Returns the categories payload for the editor.
     *
     * @return array<string, mixed>
     */
    public function get_categories(): array
    {
        global $wpdb;

        $sql = "SELECT t.term_id, t.name, t.slug, tt.count, tt.parent
                FROM {$wpdb->terms} t
                INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                WHERE tt.taxonomy = 'product_cat'
                ORDER BY t.name ASC";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Reads core taxonomy tables for the admin editor screen.
        $results = $wpdb->get_results($sql);
        $category_map = $this->build_category_map($results);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reads a single aggregate value from the posts table for the admin editor screen.
        $total_count = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status IN ('publish', 'draft', 'pending', 'private')"
        );

        return [
            'categories' => $this->build_category_tree($category_map, 0),
            'total_count' => $total_count,
        ];
    }

    /**
     * Returns available tax classes.
     *
     * @return array<int, array<string, string>>
     */
    public function get_tax_classes(): array
    {
        return $this->lookups->get_tax_classes_list();
    }

    /**
     * Returns the products payload for the editor.
     *
     * @param array<string, mixed> $request_data
     * @return array<string, mixed>
     */
    public function get_products(array $request_data): array
    {
        global $wpdb;

        $status = $this->read_text_field($request_data, 'status');
        $category = $this->read_slug_field($request_data, 'category');
        $search = $this->read_text_field($request_data, 'search');
        $tax_status = $this->read_text_field($request_data, 'tax_status');
        $tax_class = $this->read_slug_field($request_data, 'tax_class');
        $stock_status = $this->read_text_field($request_data, 'stock_status');

        $where_conditions = ["p.post_type = 'product'", "p.post_status IN ('publish', 'draft', 'private')"];
        $params = [];

        if ($status !== '') {
            $where_conditions[] = 'p.post_status = %s';
            $params[] = $status;
        }

        if ($tax_status !== '') {
            $where_conditions[] = 'pm_tax_status.meta_value = %s';
            $params[] = $tax_status;
        }

        if ($tax_class !== '') {
            if ($tax_class === darktech_pe_get_default_tax_class_filter_value()) {
                $where_conditions[] = "(pm_tax_class.meta_value = '' OR pm_tax_class.meta_value IS NULL)";
            } else {
                $where_conditions[] = 'pm_tax_class.meta_value = %s';
                $params[] = $tax_class;
            }
        }

        if ($stock_status !== '') {
            $where_conditions[] = 'pm_stock.meta_value = %s';
            $params[] = $stock_status;
        }

        if ($search !== '') {
            $where_conditions[] = '(p.post_title LIKE %s OR pm_sku.meta_value LIKE %s OR p.ID = %d)';
            $search_term = '%' . $search . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = (int) $search;
        }

        if ($category !== '') {
            $where_conditions[] = 'c.slug = %s';
            $params[] = $category;
        }

        $where_clause = implode(' AND ', $where_conditions);
        $total = $this->get_filtered_total($wpdb, $where_clause, $params, $search !== '');

        $sql = "SELECT p.ID, p.post_title, p.post_status,
                       pm_sku.meta_value as sku,
                       pm_price.meta_value as regular_price,
                       pm_sale_price.meta_value as sale_price,
                       pm_old_price.meta_value as _old_price,
                       pm_stock.meta_value as stock_status,
                       pm_tax_status.meta_value as tax_status,
                       pm_tax_class.meta_value as tax_class,
                       GROUP_CONCAT(c.name SEPARATOR ', ') as categories,
                       MIN(c.term_id) as primary_category_id,
                       MIN(c.name) as primary_category_name,
                       MIN(c.slug) as primary_category_slug
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm_sku ON p.ID = pm_sku.post_id AND pm_sku.meta_key = '_sku'
                LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_regular_price'
                LEFT JOIN {$wpdb->postmeta} pm_sale_price ON p.ID = pm_sale_price.post_id AND pm_sale_price.meta_key = '_sale_price'
                LEFT JOIN {$wpdb->postmeta} pm_old_price ON p.ID = pm_old_price.post_id AND pm_old_price.meta_key = '_old_price'
                LEFT JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'
                LEFT JOIN {$wpdb->postmeta} pm_tax_status ON p.ID = pm_tax_status.post_id AND pm_tax_status.meta_key = '_tax_status'
                LEFT JOIN {$wpdb->postmeta} pm_tax_class ON p.ID = pm_tax_class.post_id AND pm_tax_class.meta_key = '_tax_class'
                LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'product_cat'
                LEFT JOIN {$wpdb->terms} c ON tt.term_id = c.term_id
                WHERE {$where_clause}
                GROUP BY p.ID
                ORDER BY p.ID DESC
                LIMIT %d";

        $limit = $this->get_products_limit();
        $params[] = $limit;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query uses placeholders and trusted core table names.
        $sql = $wpdb->prepare($sql, ...$params);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared -- Prepared query against core tables for the admin bulk editor.
        $results = $wpdb->get_results($sql);

        return [
            'products' => $this->map_products($results),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'total' => $total,
            'page' => 1,
            'per_page' => $limit,
        ];
    }

    /**
     * @param array<string, mixed> $request_data
     */
    private function read_text_field(array $request_data, string $key): string
    {
        $value = $request_data[$key] ?? '';

        return is_string($value) ? sanitize_text_field(wp_unslash($value)) : '';
    }

    /**
     * Reads a slug-like request field without stripping percent-encoded chars.
     */
    private function read_slug_field(array $request_data, string $key): string
    {
        $value = $request_data[$key] ?? '';

        if (! is_string($value)) {
            return '';
        }

        // Keep percent-encoded WooCommerce slugs intact (for example Cyrillic
        // categories), because sanitize_text_field() strips %xx sequences.
        return trim(wp_unslash($value));
    }

    /**
     * Returns the configured products limit.
     */
    private function get_products_limit(): int
    {
        $limit = (int) get_option('darktech_pe_products_limit', 3000);

        return max(1, $limit);
    }

    /**
     * @param array<int, object> $results
     * @return array<int, array<string, int|string>>
     */
    private function build_category_map(array $results): array
    {
        $category_map = [];

        foreach ($results as $row) {
            $category_map[(int) $row->term_id] = [
                'id' => (int) $row->term_id,
                'name' => (string) $row->name,
                'slug' => (string) $row->slug,
                'count' => (int) $row->count,
                'parent' => (int) $row->parent,
                'depth' => 0,
            ];
        }

        foreach ($category_map as &$category) {
            $depth = 0;
            $parent_id = (int) $category['parent'];

            while ($parent_id > 0 && isset($category_map[$parent_id])) {
                $depth++;
                $parent_id = (int) $category_map[$parent_id]['parent'];
            }

            $category['depth'] = $depth;
        }
        unset($category);

        return $category_map;
    }

    /**
     * @param array<int, array<string, int|string>> $category_map
     * @return array<int, array<string, int|string>>
     */
    private function build_category_tree(array $category_map, int $parent_id): array
    {
        $tree = [];

        foreach ($category_map as $category) {
            if ((int) $category['parent'] !== $parent_id) {
                continue;
            }

            $tree[] = $category;
            $tree = array_merge($tree, $this->build_category_tree($category_map, (int) $category['id']));
        }

        return $tree;
    }

    /**
     * @param wpdb $wpdb
     * @param array<int, mixed> $params
     */
    private function get_filtered_total(wpdb $wpdb, string $where_clause, array $params, bool $has_search): int
    {
        $count_sql = "SELECT COUNT(DISTINCT p.ID)
                      FROM {$wpdb->posts} p
                      LEFT JOIN {$wpdb->postmeta} pm_tax_status ON p.ID = pm_tax_status.post_id AND pm_tax_status.meta_key = '_tax_status'
                      LEFT JOIN {$wpdb->postmeta} pm_tax_class ON p.ID = pm_tax_class.post_id AND pm_tax_class.meta_key = '_tax_class'
                      LEFT JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'
                      LEFT JOIN {$wpdb->postmeta} pm_sku ON p.ID = pm_sku.post_id AND pm_sku.meta_key = '_sku'
                      LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                      LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'product_cat'
                      LEFT JOIN {$wpdb->terms} c ON tt.term_id = c.term_id
                      WHERE {$where_clause}";

        if ($has_search) {
            $count_sql .= ' GROUP BY p.ID';
        }

        if (! empty($params)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query uses placeholders and trusted core table names.
            $count_sql = $wpdb->prepare($count_sql, ...$params);
        }

        if ($has_search) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Wraps a prepared grouped query to count filtered rows.
            return (int) $wpdb->get_var("SELECT COUNT(*) FROM ({$count_sql}) as filtered_results");
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared -- Query is prepared above when parameters are present and only reads core tables.
        return (int) $wpdb->get_var($count_sql);
    }

    /**
     * @param array<int, object> $results
     * @return array<int, array<string, mixed>>
     */
    private function map_products(array $results): array
    {
        $uncategorized = $this->lookups->get_uncategorized_label();
        $products = [];

        foreach ($results as $row) {
            $primary_category = $row->primary_category_name !== null && $row->primary_category_name !== ''
                ? (string) $row->primary_category_name
                : $uncategorized;
            $all_categories = $row->categories !== null && $row->categories !== ''
                ? (string) $row->categories
                : $uncategorized;
            $category_id = isset($row->primary_category_id) ? (int) $row->primary_category_id : 0;
            $tax_class_slug = $row->tax_class ? (string) $row->tax_class : '';
            $stock_status_slug = $row->stock_status ? (string) $row->stock_status : 'instock';
            $tax_status_slug = $row->tax_status ? (string) $row->tax_status : 'taxable';

            $products[] = [
                'id' => (int) $row->ID,
                'title' => (string) $row->post_title,
                'status' => (string) $row->post_status,
                'sku' => $row->sku ? (string) $row->sku : '',
                'regular_price' => $row->regular_price ? (string) $row->regular_price : '',
                'sale_price' => $row->sale_price ? (string) $row->sale_price : '',
                'old_price' => $row->_old_price ? (string) $row->_old_price : '',
                'stock_status' => $stock_status_slug,
                'stock_status_label' => $this->lookups->get_stock_status_label($stock_status_slug),
                'tax_status' => $tax_status_slug,
                'tax_status_label' => $this->lookups->get_tax_status_label($tax_status_slug),
                'tax_class' => $tax_class_slug,
                'tax_class_label' => $this->lookups->get_tax_class_label($tax_class_slug),
                'category' => $primary_category,
                'category_id' => $category_id,
                'category_slug' => $row->primary_category_slug ? (string) $row->primary_category_slug : '',
                'categories' => $all_categories,
                'edit_url' => admin_url('post.php?post=' . $row->ID . '&action=edit'),
                'view_url' => get_permalink((int) $row->ID) ?: '',
            ];
        }

        return $products;
    }
}
