=== DarkTech Price Editor ===
Contributors: darktechcode
Tags: woocommerce, bulk edit, price editor, product management, catalog
Requires at least: 6.2
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bulk editor for WooCommerce product prices and product fields with a fullscreen admin interface.

== Description ==

DarkTech Price Editor is a WordPress plugin for WooCommerce that helps store managers update product data in bulk through a fullscreen admin interface. It is designed for fast day-to-day work with large catalogs where prices, stock status, tax settings, and other product fields need to be edited without jumping between multiple product pages.

Repository: https://github.com/DarkTechCode/darktech-price-editor

Key features:

* Inline editing directly inside the product table
* Fullscreen workspace focused on bulk product management
* Search by product ID, title, or SKU
* Filters for publication status, category, stock status, and tax status
* Configurable column visibility
* Change history popup with the latest 100 saved actions
* Responsive interface for desktop and tablet use
* Local caching for categories and tax classes, with manual cache reset
* AJAX-based updates with nonce and capability checks
* Localized admin interface with 15 bundled translation packs

Supported languages:

* `ar` (Arabic)
* `de_DE` (German)
* `en_US` (English)
* `es_ES` (Spanish)
* `fr_FR` (French)
* `hi_IN` (Hindi)
* `id_ID` (Indonesian)
* `it_IT` (Italian)
* `ja` (Japanese)
* `ko_KR` (Korean)
* `nl_NL` (Dutch)
* `pt_BR` (Portuguese, Brazil)
* `ru_RU` (Russian)
* `tr_TR` (Turkish)
* `zh_CN` (Chinese, Simplified)

Editable fields:

* Product title
* SKU
* Category
* Regular price
* Sale price
* Stock status
* Tax status
* Tax class

Read-only columns include row number, product ID, old price, and action links.

The status bar at the bottom of the editor shows the latest action and opens a fullscreen change history popup when clicked. The popup displays the latest 100 saved changes and stores history in the WordPress database table `{wp_prefix}darktech_price_editor_logs`.

== Installation ==

1. Download the plugin.
2. Upload the `darktech-price-editor` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the "Plugins" menu in WordPress.
4. Make sure WooCommerce is installed and active.
5. Open "Price Editor" from the WordPress admin menu.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes. WooCommerce must be installed and active for the plugin to work.

= Which product fields can I edit? =

You can edit product title, SKU, category, regular price, sale price, stock status, tax status, and tax class.

= How does localization work? =

The plugin follows the current WordPress admin locale and loads translation files from the `languages` directory.

= Where can I report bugs or request features? =

Use the GitHub repository at https://github.com/DarkTechCode/darktech-price-editor or contact the team via https://darktech.ru

== Screenshots ==

1. Fullscreen workspace for bulk editing WooCommerce product data.
2. Product filters and column management tools for large catalogs.
3. Change history popup showing the latest saved actions.

== Changelog ==

= 1.0.0 =

* Initial public release.
* Fullscreen WooCommerce bulk editor with inline editing and filters.
* Change history popup with the latest 100 saved actions.
* Bundled translation packs for 15 locales.

== Upgrade Notice ==

= 1.0.0 =

Initial release.
