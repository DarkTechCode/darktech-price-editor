<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns localized product status labels.
 *
 * @return array<string, string>
 */
function darktech_pe_get_product_status_labels(): array
{
    return [
        'publish' => __('Published', 'darktech-price-editor'),
        'draft' => __('Draft', 'darktech-price-editor'),
        'private' => __('Private', 'darktech-price-editor'),
    ];
}

/**
 * Returns localized stock status labels.
 *
 * @return array<string, string>
 */
function darktech_pe_get_stock_status_labels(): array
{
    return [
        'instock' => __('In stock', 'darktech-price-editor'),
        'outofstock' => __('Out of stock', 'darktech-price-editor'),
        'onbackorder' => __('On backorder', 'darktech-price-editor'),
    ];
}

/**
 * Returns localized tax status labels.
 *
 * @return array<string, string>
 */
function darktech_pe_get_tax_status_labels(): array
{
    return [
        'taxable' => __('Taxable', 'darktech-price-editor'),
        'shipping' => __('Shipping only', 'darktech-price-editor'),
        'none' => __('None', 'darktech-price-editor'),
    ];
}

/**
 * Returns the localized default tax class label.
 */
function darktech_pe_get_default_tax_class_label(): string
{
    return __('Standard', 'darktech-price-editor');
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
    return __('Uncategorized', 'darktech-price-editor');
}

/**
 * Returns DataTables localization.
 *
 * @return array<string, mixed>
 */
function darktech_pe_get_datatables_language(): array
{
    return [
        'emptyTable' => __('No data available in table', 'darktech-price-editor'),
        'info' => __('Showing _START_ to _END_ of _TOTAL_ entries', 'darktech-price-editor'),
        'infoEmpty' => __('Showing 0 to 0 of 0 entries', 'darktech-price-editor'),
        'infoFiltered' => __('(filtered from _MAX_ total entries)', 'darktech-price-editor'),
        'lengthMenu' => __('Show _MENU_ entries', 'darktech-price-editor'),
        'loadingRecords' => __('Loading...', 'darktech-price-editor'),
        'processing' => __('Processing...', 'darktech-price-editor'),
        'search' => __('Search:', 'darktech-price-editor'),
        'zeroRecords' => __('No matching records found', 'darktech-price-editor'),
        'paginate' => [
            'first' => __('First', 'darktech-price-editor'),
            'last' => __('Last', 'darktech-price-editor'),
            'next' => __('Next', 'darktech-price-editor'),
            'previous' => __('Previous', 'darktech-price-editor'),
        ],
        'aria' => [
            'sortAscending' => __(': activate to sort column ascending', 'darktech-price-editor'),
            'sortDescending' => __(': activate to sort column descending', 'darktech-price-editor'),
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
                'title' => __('[DarkTech] Product Price Editor', 'darktech-price-editor'),
                'heading' => __('Product Price Editor', 'darktech-price-editor'),
                'breadcrumbAdmin' => __('Dashboard', 'darktech-price-editor'),
                'breadcrumbCurrent' => __('Price Editor', 'darktech-price-editor'),
                'clearCacheTitle' => __('Clear cache and reload data (for example, after adding categories)', 'darktech-price-editor'),
                'productsShown' => __('Products shown: {count}', 'darktech-price-editor'),
                'productsLimitPrompt' => __('How many products to show at most?', 'darktech-price-editor'),
                'productsLimitSaved' => __('Product limit updated to {limit}', 'darktech-price-editor'),
                'productsLimitInvalid' => __('Please enter a valid number greater than 0.', 'darktech-price-editor'),
                'productsLimitSaveError' => __('Error saving the product limit: {message}', 'darktech-price-editor'),
            ],
            'filters' => [
                'status' => __('Product status:', 'darktech-price-editor'),
                'allStatuses' => __('All statuses', 'darktech-price-editor'),
                'category' => __('Category:', 'darktech-price-editor'),
                'allCategories' => __('All categories', 'darktech-price-editor'),
                'search' => __('Search:', 'darktech-price-editor'),
                'searchPlaceholder' => __('Title, SKU, ID...', 'darktech-price-editor'),
                'taxStatus' => __('Tax status:', 'darktech-price-editor'),
                'taxClass' => __('Tax class:', 'darktech-price-editor'),
                'allTaxClasses' => __('All tax classes', 'darktech-price-editor'),
                'all' => __('All', 'darktech-price-editor'),
                'stockStatus' => __('Stock status:', 'darktech-price-editor'),
            ],
            'columnManager' => [
                'title' => __('Manage columns', 'darktech-price-editor'),
                'close' => __('Close', 'darktech-price-editor'),
                'toggleTitle' => __('Manage table columns', 'darktech-price-editor'),
                'showAll' => __('Show all', 'darktech-price-editor'),
                'hideAll' => __('Hide all', 'darktech-price-editor'),
                'reset' => __('Reset', 'darktech-price-editor'),
                'shownMessage' => __('Column "{column}" shown', 'darktech-price-editor'),
                'hiddenMessage' => __('Column "{column}" hidden', 'darktech-price-editor'),
                'resetMessage' => __('Column settings reset', 'darktech-price-editor'),
                'showAllMessage' => __('All columns are visible', 'darktech-price-editor'),
                'hideAllMessage' => __('All columns hidden except the row number', 'darktech-price-editor'),
            ],
            'columns' => [
                'rowNumber' => __('#', 'darktech-price-editor'),
                'id' => __('ID', 'darktech-price-editor'),
                'category' => __('Category', 'darktech-price-editor'),
                'title' => __('Product title', 'darktech-price-editor'),
                'sku' => __('SKU', 'darktech-price-editor'),
                'regularPrice' => __('Price', 'darktech-price-editor'),
                'salePrice' => __('Sale price', 'darktech-price-editor'),
                'oldPrice' => __('Was', 'darktech-price-editor'),
                'stockStatus' => __('Stock status', 'darktech-price-editor'),
                'taxStatus' => __('Tax status', 'darktech-price-editor'),
                'taxClass' => __('Tax class', 'darktech-price-editor'),
                'actions' => __('Actions', 'darktech-price-editor'),
            ],
            'table' => [
                'viewProduct' => __('Click to view the product', 'darktech-price-editor'),
                'edit' => __('Edit', 'darktech-price-editor'),
            ],
            'errors' => [
                'title' => __('Data loading errors:', 'darktech-price-editor'),
                'close' => __('Close', 'darktech-price-editor'),
            ],
            'tech' => [
                'ready' => __('Ready to work', 'darktech-price-editor'),
                'loadingInitialData' => __('Loading data...', 'darktech-price-editor'),
                'savingChanges' => __('Saving changes...', 'darktech-price-editor'),
                'savingTitle' => __('Saving the product title...', 'darktech-price-editor'),
                'savingSku' => __('Saving the SKU...', 'darktech-price-editor'),
                'savingStockStatus' => __('Saving the stock status...', 'darktech-price-editor'),
                'savingTaxStatus' => __('Saving the tax status...', 'darktech-price-editor'),
                'savingTaxClass' => __('Saving the tax class...', 'darktech-price-editor'),
                'savingCategory' => __('Saving the category...', 'darktech-price-editor'),
                'filtersApplied' => __('New filters applied', 'darktech-price-editor'),
                'filtersReset' => __('Filters reset', 'darktech-price-editor'),
                'cacheReloading' => __('Clearing cache and reloading...', 'darktech-price-editor'),
                'cacheReloaded' => __('Cache cleared, reloading data...', 'darktech-price-editor'),
            ],
            'history' => [
                'title' => __('Change history', 'darktech-price-editor'),
                'openHint' => __('Click to view the latest 100 changes', 'darktech-price-editor'),
                'close' => __('Close', 'darktech-price-editor'),
                'loading' => __('Loading history...', 'darktech-price-editor'),
                'empty' => __('Change history is empty so far.', 'darktech-price-editor'),
                'dateColumn' => __('Date and time', 'darktech-price-editor'),
                'messageColumn' => __('What happened', 'darktech-price-editor'),
                'userColumn' => __('Author', 'darktech-price-editor'),
                'loadError' => __('Error loading history: {message}', 'darktech-price-editor'),
            ],
            'confirm' => [
                'title' => __('Confirmation', 'darktech-price-editor'),
                'yes' => __('Yes', 'darktech-price-editor'),
                'no' => __('No', 'darktech-price-editor'),
            ],
            'placeholders' => [
                'title' => __('Enter title', 'darktech-price-editor'),
                'sku' => __('Enter SKU', 'darktech-price-editor'),
            ],
            'editing' => [
                'clickToEditTitle' => __('Click to edit the product title', 'darktech-price-editor'),
                'clickToEditCategory' => __('Click to edit the category', 'darktech-price-editor'),
                'clickToEditStockStatus' => __('Click to edit the stock status', 'darktech-price-editor'),
                'clickToEditTaxStatus' => __('Click to edit the tax status', 'darktech-price-editor'),
                'clickToEditTaxClass' => __('Click to edit the tax class', 'darktech-price-editor'),
                'clickToEditSku' => __('Click to edit the SKU', 'darktech-price-editor'),
                'clickToAddSku' => __('Click to add a SKU', 'darktech-price-editor'),
            ],
            'notifications' => [
                'changesSaved' => __('Changes saved', 'darktech-price-editor'),
                'titleUpdated' => __('Title updated', 'darktech-price-editor'),
                'skuUpdated' => __('SKU updated', 'darktech-price-editor'),
                'stockStatusUpdated' => __('Stock status updated', 'darktech-price-editor'),
                'taxStatusUpdated' => __('Tax status updated', 'darktech-price-editor'),
                'taxClassUpdated' => __('Tax class updated', 'darktech-price-editor'),
                'categoryUpdated' => __('Category updated', 'darktech-price-editor'),
            ],
            'messages' => [
                'unknownError' => __('Unknown error', 'darktech-price-editor'),
                'dataLoadError' => __('Error loading data: {message}', 'darktech-price-editor'),
                'categoriesLoadError' => __('Error loading categories', 'darktech-price-editor'),
                'categoriesLoadErrorDetailed' => __('Error loading categories: {message}', 'darktech-price-editor'),
                'taxClassesLoadError' => __('Error loading tax classes', 'darktech-price-editor'),
                'taxClassesLoadErrorDetailed' => __('Error loading tax classes: {message}', 'darktech-price-editor'),
                'saveError' => __('Save error: {message}', 'darktech-price-editor'),
                'titleSaveError' => __('Error saving the title: {message}', 'darktech-price-editor'),
                'skuSaveError' => __('Error saving the SKU: {message}', 'darktech-price-editor'),
                'stockStatusSaveError' => __('Error saving the stock status: {message}', 'darktech-price-editor'),
                'taxStatusSaveError' => __('Error saving the tax status: {message}', 'darktech-price-editor'),
                'taxClassSaveError' => __('Error saving the tax class: {message}', 'darktech-price-editor'),
                'categorySaveError' => __('Error saving the category: {message}', 'darktech-price-editor'),
                'missingDataTables' => __('Error: DataTables library is not loaded', 'darktech-price-editor'),
                'missingJQuery' => __('jQuery is not loaded', 'darktech-price-editor'),
                'missingDataTablesConsole' => __('DataTables is not loaded', 'darktech-price-editor'),
                'fieldUpdatedMessage' => __('Updated {field} for product #{id}', 'darktech-price-editor'),
                'titleUpdatedMessage' => __('Updated product #{id} title: {old} -> {new}', 'darktech-price-editor'),
                'skuUpdatedMessage' => __('Updated product #{id} SKU: "{old}" -> "{new}"', 'darktech-price-editor'),
                'stockStatusUpdatedMessage' => __('Updated product #{id} stock status: "{old}" -> "{new}"', 'darktech-price-editor'),
                'taxStatusUpdatedMessage' => __('Updated product #{id} tax status: "{old}" -> "{new}"', 'darktech-price-editor'),
                'taxClassUpdatedMessage' => __('Updated product #{id} tax class: "{old}" -> "{new}"', 'darktech-price-editor'),
                'categoryUpdatedMessage' => __('Updated product #{id} category: "{old}" -> "{new}"', 'darktech-price-editor'),
            ],
            'fields' => [
                'regularPrice' => __('regular price', 'darktech-price-editor'),
                'salePrice' => __('sale price', 'darktech-price-editor'),
                'title' => __('product title', 'darktech-price-editor'),
                'taxStatus' => __('tax status', 'darktech-price-editor'),
                'taxClass' => __('tax class', 'darktech-price-editor'),
                'stockStatus' => __('stock status', 'darktech-price-editor'),
                'category' => __('category', 'darktech-price-editor'),
                'sku' => __('SKU', 'darktech-price-editor'),
            ],
            'mobile' => [
                'scrollIndicator' => __('Scroll horizontally', 'darktech-price-editor'),
                'rotateToPortrait' => __('Rotate the device to portrait mode for a better view', 'darktech-price-editor'),
            ],
            'developer' => [
                'label' => __('Developed by', 'darktech-price-editor'),
                'linkTitle' => __('Open in a new tab', 'darktech-price-editor'),
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
