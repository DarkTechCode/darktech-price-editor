<?php

declare(strict_types=1);

/**
 * Plugin Name: DarkTech Price Editor
 * Plugin URI: https://github.com/DarkTechCode/darktech-price-editor
 * Description: Bulk editor for WooCommerce product prices.
 * Version: 1.0.0
 * Author: Dark Wizard
 * Author URI: https://darktech.ru
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: darktech-price-editor
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Запрет прямого доступа
if (! defined('ABSPATH')) {
    exit;
}

// Константы плагина
define('DARKTECH_PE_VERSION', '1.0.0');
define('DARKTECH_PE_DB_VERSION', '1.1.1');
define('DARKTECH_PE_PLUGIN_FILE', __FILE__);
define('DARKTECH_PE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('DARKTECH_PE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DARKTECH_PE_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once DARKTECH_PE_PLUGIN_DIR . 'includes/i18n.php';

/**
 * Returns the change history table name.
 */
function darktech_pe_get_history_table_name(): string
{
    global $wpdb;

    return $wpdb->prefix . 'darktech_price_editor_logs';
}

/**
 * Creates or updates plugin database tables.
 */
function darktech_pe_install_or_update_database(): void
{
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    global $wpdb;

    $table_name = darktech_pe_get_history_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        created_at_gmt datetime NOT NULL,
        user_id bigint(20) unsigned NOT NULL DEFAULT 0,
        user_display_name varchar(191) NOT NULL DEFAULT '',
        event_type varchar(64) NOT NULL DEFAULT '',
        product_id bigint(20) unsigned DEFAULT NULL,
        field_name varchar(64) DEFAULT NULL,
        message_fallback text NOT NULL,
        payload_json longtext DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY created_at_gmt (created_at_gmt),
        KEY user_id (user_id),
        KEY product_id (product_id),
        KEY event_type (event_type)
    ) {$charset_collate};";

    dbDelta($sql);

    update_option('darktech_pe_db_version', DARKTECH_PE_DB_VERSION);
}

/**
 * Enqueues styles and scripts for the fullscreen editor page.
 */
function darktech_pe_enqueue_fullscreen_assets(): void
{
    $darktech_pe_config = darktech_pe_get_frontend_config();
    $darktech_pe_style_handles = [
        'darktech-pe-datatables' => 'assets/css/jquery.dataTables.min.css',
        'darktech-pe-theme-light' => 'assets/css/themes/light.css',
        'darktech-pe-theme-dark' => 'assets/css/themes/dark.css',
        'darktech-pe-base' => 'assets/css/base.css',
        'darktech-pe-header' => 'assets/css/header.css',
        'darktech-pe-filters' => 'assets/css/filters.css',
        'darktech-pe-column-manager' => 'assets/css/column-manager.css',
        'darktech-pe-table' => 'assets/css/table.css',
        'darktech-pe-editing' => 'assets/css/editing.css',
        'darktech-pe-statuses' => 'assets/css/statuses.css',
        'darktech-pe-links-buttons' => 'assets/css/links-buttons.css',
        'darktech-pe-system' => 'assets/css/system.css',
        'darktech-pe-errors' => 'assets/css/errors.css',
        'darktech-pe-indicators' => 'assets/css/indicators.css',
        'darktech-pe-datatables-custom' => 'assets/css/datatables-custom.css',
        'darktech-pe-responsive' => 'assets/css/responsive.css',
    ];

    foreach ($darktech_pe_style_handles as $darktech_pe_handle => $darktech_pe_relative_path) {
        wp_enqueue_style(
            $darktech_pe_handle,
            DARKTECH_PE_PLUGIN_URL . $darktech_pe_relative_path,
            [],
            DARKTECH_PE_VERSION
        );
    }

    wp_enqueue_script(
        'darktech-pe-datatables',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/jquery.dataTables.min.js',
        ['jquery'],
        '1.13.7',
        true
    );

    wp_enqueue_script(
        'darktech-pe-ui-module',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.ui.js',
        ['jquery'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-history-module',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.history.js',
        ['jquery'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-filters-module',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.filters.js',
        ['jquery'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-columns-module',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.columns.js',
        ['jquery'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-editing-markup',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.editing.markup.js',
        [],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-save-base',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.data.save-base.js',
        ['jquery'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-save-service',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.data.saves.js',
        ['darktech-pe-save-base'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-data-module',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.data.js',
        ['jquery', 'darktech-pe-save-service'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-editing-module',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.editing.js',
        ['jquery', 'darktech-pe-editing-markup'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-mobile-module',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.mobile.js',
        ['jquery'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-horizontal-scroll-module',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.horizontal-scroll.js',
        ['jquery'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-table-columns',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.table-columns.js',
        [],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-core',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.core.js',
        [
            'jquery',
            'darktech-pe-datatables',
            'darktech-pe-data-module',
            'darktech-pe-editing-module',
            'darktech-pe-ui-module',
            'darktech-pe-history-module',
            'darktech-pe-filters-module',
            'darktech-pe-columns-module',
            'darktech-pe-mobile-module',
            'darktech-pe-horizontal-scroll-module',
            'darktech-pe-table-columns',
        ],
        DARKTECH_PE_VERSION,
        true
    );

    wp_enqueue_script(
        'darktech-pe-main',
        DARKTECH_PE_PLUGIN_URL . 'assets/js/price_editor.js',
        ['jquery', 'darktech-pe-core'],
        DARKTECH_PE_VERSION,
        true
    );

    wp_add_inline_script(
        'darktech-pe-main',
        'window.darktech_pe = ' . wp_json_encode($darktech_pe_config) . ';',
        'before'
    );
}

/**
 * Runs database setup on plugin activation.
 */
function darktech_pe_activate(): void
{
    darktech_pe_install_or_update_database();
}

/**
 * Applies database upgrades when the schema version changes.
 */
function darktech_pe_maybe_upgrade_database(): void
{
    $installed_version = (string) get_option('darktech_pe_db_version', '');

    if ($installed_version === DARKTECH_PE_DB_VERSION) {
        return;
    }

    darktech_pe_install_or_update_database();
}

/**
 * Declares WooCommerce feature compatibility.
 */
function darktech_pe_declare_woocommerce_compatibility(): void
{
    if (! class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        return;
    }

    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
        'custom_order_tables',
        DARKTECH_PE_PLUGIN_FILE,
        true
    );
}
add_action('before_woocommerce_init', 'darktech_pe_declare_woocommerce_compatibility');
register_activation_hook(DARKTECH_PE_PLUGIN_FILE, 'darktech_pe_activate');

/**
 * Проверка наличия WooCommerce
 */
function darktech_pe_is_woocommerce_available(): bool
{
    return class_exists('WooCommerce');
}

/**
 * Уведомление об отсутствии WooCommerce
 */
function darktech_pe_render_woocommerce_notice(): void
{
    echo sprintf(
        '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
        esc_html__('[DarkTech] Price Editor:', 'darktech-price-editor'),
        esc_html__('WooCommerce is required for the plugin to work.', 'darktech-price-editor')
    );
}

/**
 * Инициализация плагина
 */
function darktech_pe_init(): void
{
    if (! darktech_pe_is_woocommerce_available()) {
        if (is_admin()) {
            add_action('admin_notices', 'darktech_pe_render_woocommerce_notice');
        }

        return;
    }

    add_action('admin_menu', 'darktech_pe_add_admin_menu');
    add_action('admin_init', 'darktech_pe_intercept_fullscreen', 1);

    // Подключаем класс плагина
    require_once DARKTECH_PE_PLUGIN_DIR . 'includes/class-price-editor.php';

    // Инициализируем
    DarkTech_Price_Editor::get_instance();
}
add_action('plugins_loaded', 'darktech_pe_maybe_upgrade_database', 5);
add_action('plugins_loaded', 'darktech_pe_init');

/**
 * Добавление пункта меню в админку
 */
function darktech_pe_add_admin_menu(): void
{
    add_menu_page(
        __('Price Editor', 'darktech-price-editor'), // Заголовок страницы
        __('Price Editor', 'darktech-price-editor'), // Название в меню
        'edit_products',                      // Права доступа (редактирование товаров)
        'darktech-price-editor',              // Slug меню
        '__return_null',                      // Пустой callback - страница выводится через admin_init
        'dashicons-money-alt',                // Иконка
        56                                    // Позиция в меню
    );
}

/**
 * Перехват запроса для полноэкранного режима
 * Выполняется до отрисовки WordPress admin wrapper
 */
function darktech_pe_intercept_fullscreen(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading the current admin page slug does not change state.
    $darktech_pe_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

    // Проверяем, что это наша страница
    if ($darktech_pe_page !== 'darktech-price-editor') {
        return;
    }

    // Проверяем права доступа
    if (! current_user_can('edit_products')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'darktech-price-editor'));
    }

    darktech_pe_enqueue_fullscreen_assets();

    // Выводим полноэкранную страницу и завершаем выполнение
    include DARKTECH_PE_PLUGIN_DIR . 'templates/fullscreen-page.php';
    exit;
}
