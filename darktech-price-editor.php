<?php

declare(strict_types=1);

/**
 * Plugin Name: DarkTech Price Editor
 * Plugin URI: https://darktech.ru
 * Description: Bulk editor for WooCommerce product prices.
 * Version: 1.0.0
 * Author: DarkTech
 * Author URI: https://darktech.ru
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: darktech-price-editor
 * Domain Path: /languages
 * Requires at least: 5.0
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
add_action('init', 'darktech_pe_load_textdomain');
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
    // Проверяем, что это наша страница
    if (! isset($_GET['page']) || $_GET['page'] !== 'darktech-price-editor') {
        return;
    }

    // Проверяем права доступа
    if (! current_user_can('edit_products')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'darktech-price-editor'));
    }

    // Выводим полноэкранную страницу и завершаем выполнение
    include DARKTECH_PE_PLUGIN_DIR . 'templates/fullscreen-page.php';
    exit;
}
