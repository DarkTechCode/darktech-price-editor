<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns the plugin text domain.
 */
function darktech_pe_get_text_domain(): string
{
    return 'darktech-price-editor';
}

/**
 * Loads plugin translations.
 */
function darktech_pe_load_textdomain(): void
{
    load_plugin_textdomain(
        darktech_pe_get_text_domain(),
        false,
        dirname(DARKTECH_PE_PLUGIN_BASENAME) . '/languages'
    );
}

/**
 * Returns localized product status labels.
 *
 * @return array<string, string>
 */
function darktech_pe_get_product_status_labels(): array
{
    $text_domain = darktech_pe_get_text_domain();

    return [
        'publish' => __('Published', $text_domain),
        'draft' => __('Draft', $text_domain),
        'private' => __('Private', $text_domain),
    ];
}

/**
 * Returns localized stock status labels.
 *
 * @return array<string, string>
 */
function darktech_pe_get_stock_status_labels(): array
{
    $text_domain = darktech_pe_get_text_domain();

    return [
        'instock' => __('In stock', $text_domain),
        'outofstock' => __('Out of stock', $text_domain),
        'onbackorder' => __('On backorder', $text_domain),
    ];
}

/**
 * Returns localized tax status labels.
 *
 * @return array<string, string>
 */
function darktech_pe_get_tax_status_labels(): array
{
    $text_domain = darktech_pe_get_text_domain();

    return [
        'taxable' => __('Taxable', $text_domain),
        'shipping' => __('Shipping only', $text_domain),
        'none' => __('None', $text_domain),
    ];
}

/**
 * Returns the localized default tax class label.
 */
function darktech_pe_get_default_tax_class_label(): string
{
    return __('Standard', darktech_pe_get_text_domain());
}

/**
 * Returns the internal filter value for the default tax class.
 */
function darktech_pe_get_default_tax_class_filter_value(): string
{
    return '__default__';
}

/**
 * Returns the localized "uncategorized" label.
 */
function darktech_pe_get_uncategorized_label(): string
{
    return __('Uncategorized', darktech_pe_get_text_domain());
}

/**
 * Returns DataTables localization.
 *
 * @return array<string, mixed>
 */
function darktech_pe_get_datatables_language(): array
{
    $text_domain = darktech_pe_get_text_domain();

    return [
        'emptyTable' => __('No data available in table', $text_domain),
        'info' => __('Showing _START_ to _END_ of _TOTAL_ entries', $text_domain),
        'infoEmpty' => __('Showing 0 to 0 of 0 entries', $text_domain),
        'infoFiltered' => __('(filtered from _MAX_ total entries)', $text_domain),
        'lengthMenu' => __('Show _MENU_ entries', $text_domain),
        'loadingRecords' => __('Loading...', $text_domain),
        'processing' => __('Processing...', $text_domain),
        'search' => __('Search:', $text_domain),
        'zeroRecords' => __('No matching records found', $text_domain),
        'paginate' => [
            'first' => __('First', $text_domain),
            'last' => __('Last', $text_domain),
            'next' => __('Next', $text_domain),
            'previous' => __('Previous', $text_domain),
        ],
        'aria' => [
            'sortAscending' => __(': activate to sort column ascending', $text_domain),
            'sortDescending' => __(': activate to sort column descending', $text_domain),
        ],
    ];
}

/**
 * Returns frontend translations and configuration.
 *
 * @return array<string, mixed>
 */
function darktech_pe_get_frontend_config(): array
{
    $text_domain = darktech_pe_get_text_domain();

    return [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('darktech_pe_nonce'),
        'plugin_url' => DARKTECH_PE_PLUGIN_URL,
        'locale' => str_replace('_', '-', get_user_locale()),
        'products_limit' => (int) get_option('darktech_pe_products_limit', 3000),
        'statuses' => [
            'product' => darktech_pe_get_product_status_labels(),
            'stock' => darktech_pe_get_stock_status_labels(),
            'tax' => darktech_pe_get_tax_status_labels(),
        ],
        'datatables' => darktech_pe_get_datatables_language(),
        'i18n' => [
            'page' => [
                'title' => __('[DarkTech] Product Price Editor', $text_domain),
                'heading' => __('Product Price Editor', $text_domain),
                'breadcrumbAdmin' => __('Dashboard', $text_domain),
                'breadcrumbCurrent' => __('Price Editor', $text_domain),
                'clearCacheTitle' => __('Clear cache and reload data (for example, after adding categories)', $text_domain),
                'productsShown' => __('Products shown: {count}', $text_domain),
                'productsLimitPrompt' => __('How many products to show at most?', $text_domain),
                'productsLimitSaved' => __('Product limit updated to {limit}', $text_domain),
                'productsLimitInvalid' => __('Please enter a valid number greater than 0.', $text_domain),
                'productsLimitSaveError' => __('Error saving the product limit: {message}', $text_domain),
            ],
            'filters' => [
                'status' => __('Product status:', $text_domain),
                'allStatuses' => __('All statuses', $text_domain),
                'category' => __('Category:', $text_domain),
                'allCategories' => __('All categories', $text_domain),
                'search' => __('Search:', $text_domain),
                'searchPlaceholder' => __('Title, SKU, ID...', $text_domain),
                'taxStatus' => __('Tax status:', $text_domain),
                'taxClass' => __('Tax class:', $text_domain),
                'allTaxClasses' => __('All tax classes', $text_domain),
                'all' => __('All', $text_domain),
                'stockStatus' => __('Stock status:', $text_domain),
            ],
            'columnManager' => [
                'title' => __('Manage columns', $text_domain),
                'close' => __('Close', $text_domain),
                'toggleTitle' => __('Manage table columns', $text_domain),
                'showAll' => __('Show all', $text_domain),
                'hideAll' => __('Hide all', $text_domain),
                'reset' => __('Reset', $text_domain),
                'shownMessage' => __('Column "{column}" shown', $text_domain),
                'hiddenMessage' => __('Column "{column}" hidden', $text_domain),
                'resetMessage' => __('Column settings reset', $text_domain),
                'showAllMessage' => __('All columns are visible', $text_domain),
                'hideAllMessage' => __('All columns hidden except the row number', $text_domain),
            ],
            'columns' => [
                'rowNumber' => __('#', $text_domain),
                'id' => __('ID', $text_domain),
                'category' => __('Category', $text_domain),
                'title' => __('Product title', $text_domain),
                'sku' => __('SKU', $text_domain),
                'regularPrice' => __('Price', $text_domain),
                'salePrice' => __('Sale price', $text_domain),
                'oldPrice' => __('Was', $text_domain),
                'stockStatus' => __('Stock status', $text_domain),
                'taxStatus' => __('Tax status', $text_domain),
                'taxClass' => __('Tax class', $text_domain),
                'actions' => __('Actions', $text_domain),
            ],
            'table' => [
                'viewProduct' => __('Click to view the product', $text_domain),
                'edit' => __('Edit', $text_domain),
            ],
            'errors' => [
                'title' => __('Data loading errors:', $text_domain),
                'close' => __('Close', $text_domain),
            ],
            'tech' => [
                'ready' => __('Ready to work', $text_domain),
                'loadingInitialData' => __('Loading data...', $text_domain),
                'savingChanges' => __('Saving changes...', $text_domain),
                'savingTitle' => __('Saving the product title...', $text_domain),
                'savingSku' => __('Saving the SKU...', $text_domain),
                'savingStockStatus' => __('Saving the stock status...', $text_domain),
                'savingTaxStatus' => __('Saving the tax status...', $text_domain),
                'savingTaxClass' => __('Saving the tax class...', $text_domain),
                'savingCategory' => __('Saving the category...', $text_domain),
                'filtersApplied' => __('New filters applied', $text_domain),
                'filtersReset' => __('Filters reset', $text_domain),
                'cacheReloading' => __('Clearing cache and reloading...', $text_domain),
                'cacheReloaded' => __('Cache cleared, reloading data...', $text_domain),
            ],
            'history' => [
                'title' => __('Change history', $text_domain),
                'openHint' => __('Click to view the latest 100 changes', $text_domain),
                'close' => __('Close', $text_domain),
                'loading' => __('Loading history...', $text_domain),
                'empty' => __('Change history is empty so far.', $text_domain),
                'dateColumn' => __('Date and time', $text_domain),
                'messageColumn' => __('What happened', $text_domain),
                'userColumn' => __('Author', $text_domain),
                'loadError' => __('Error loading history: {message}', $text_domain),
            ],
            'confirm' => [
                'title' => __('Confirmation', $text_domain),
                'yes' => __('Yes', $text_domain),
                'no' => __('No', $text_domain),
            ],
            'placeholders' => [
                'title' => __('Enter title', $text_domain),
                'sku' => __('Enter SKU', $text_domain),
            ],
            'editing' => [
                'clickToEditTitle' => __('Click to edit the product title', $text_domain),
                'clickToEditCategory' => __('Click to edit the category', $text_domain),
                'clickToEditStockStatus' => __('Click to edit the stock status', $text_domain),
                'clickToEditTaxStatus' => __('Click to edit the tax status', $text_domain),
                'clickToEditTaxClass' => __('Click to edit the tax class', $text_domain),
                'clickToEditSku' => __('Click to edit the SKU', $text_domain),
                'clickToAddSku' => __('Click to add a SKU', $text_domain),
            ],
            'notifications' => [
                'changesSaved' => __('Changes saved', $text_domain),
                'titleUpdated' => __('Title updated', $text_domain),
                'skuUpdated' => __('SKU updated', $text_domain),
                'stockStatusUpdated' => __('Stock status updated', $text_domain),
                'taxStatusUpdated' => __('Tax status updated', $text_domain),
                'taxClassUpdated' => __('Tax class updated', $text_domain),
                'categoryUpdated' => __('Category updated', $text_domain),
            ],
            'messages' => [
                'unknownError' => __('Unknown error', $text_domain),
                'dataLoadError' => __('Error loading data: {message}', $text_domain),
                'categoriesLoadError' => __('Error loading categories', $text_domain),
                'categoriesLoadErrorDetailed' => __('Error loading categories: {message}', $text_domain),
                'taxClassesLoadError' => __('Error loading tax classes', $text_domain),
                'taxClassesLoadErrorDetailed' => __('Error loading tax classes: {message}', $text_domain),
                'saveError' => __('Save error: {message}', $text_domain),
                'titleSaveError' => __('Error saving the title: {message}', $text_domain),
                'skuSaveError' => __('Error saving the SKU: {message}', $text_domain),
                'stockStatusSaveError' => __('Error saving the stock status: {message}', $text_domain),
                'taxStatusSaveError' => __('Error saving the tax status: {message}', $text_domain),
                'taxClassSaveError' => __('Error saving the tax class: {message}', $text_domain),
                'categorySaveError' => __('Error saving the category: {message}', $text_domain),
                'missingDataTables' => __('Error: DataTables library is not loaded', $text_domain),
                'missingJQuery' => __('jQuery is not loaded', $text_domain),
                'missingDataTablesConsole' => __('DataTables is not loaded', $text_domain),
                'fieldUpdatedMessage' => __('Updated {field} for product #{id}', $text_domain),
                'titleUpdatedMessage' => __('Updated product #{id} title: {old} -> {new}', $text_domain),
                'skuUpdatedMessage' => __('Updated product #{id} SKU: "{old}" -> "{new}"', $text_domain),
                'stockStatusUpdatedMessage' => __('Updated product #{id} stock status: "{old}" -> "{new}"', $text_domain),
                'taxStatusUpdatedMessage' => __('Updated product #{id} tax status: "{old}" -> "{new}"', $text_domain),
                'taxClassUpdatedMessage' => __('Updated product #{id} tax class: "{old}" -> "{new}"', $text_domain),
                'categoryUpdatedMessage' => __('Updated product #{id} category: "{old}" -> "{new}"', $text_domain),
            ],
            'fields' => [
                'regularPrice' => __('regular price', $text_domain),
                'salePrice' => __('sale price', $text_domain),
                'title' => __('product title', $text_domain),
                'taxStatus' => __('tax status', $text_domain),
                'taxClass' => __('tax class', $text_domain),
                'stockStatus' => __('stock status', $text_domain),
                'category' => __('category', $text_domain),
                'sku' => __('SKU', $text_domain),
            ],
            'mobile' => [
                'scrollIndicator' => __('Scroll horizontally', $text_domain),
                'rotateToPortrait' => __('Rotate the device to portrait mode for a better view', $text_domain),
            ],
            'developer' => [
                'label' => __('Developed by', $text_domain),
                'linkTitle' => __('Open in a new tab', $text_domain),
                'year' => '2025',
            ],
            'defaults' => [
                'uncategorized' => darktech_pe_get_uncategorized_label(),
                'defaultTaxClass' => darktech_pe_get_default_tax_class_label(),
                'defaultTaxClassFilterValue' => darktech_pe_get_default_tax_class_filter_value(),
                'emptySku' => '-',
            ],
        ],
    ];
}
