<?php
/**
 * Fullscreen template for the price editor page.
 */

if (! defined('ABSPATH')) {
    exit;
}

$darktech_pe_config = darktech_pe_get_frontend_config();
$darktech_pe_i18n = $darktech_pe_config['i18n'];
$darktech_pe_product_statuses = darktech_pe_get_product_status_labels();
$darktech_pe_tax_statuses = darktech_pe_get_tax_status_labels();
$darktech_pe_stock_statuses = darktech_pe_get_stock_status_labels();
$darktech_pe_column_labels = $darktech_pe_i18n['columns'];
$darktech_pe_column_order = [
    'rowNumber',
    'id',
    'category',
    'title',
    'sku',
    'regularPrice',
    'salePrice',
    'oldPrice',
    'stockStatus',
    'taxStatus',
    'taxClass',
    'actions',
];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <title><?php echo esc_html($darktech_pe_i18n['page']['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_print_styles(); ?>
    <?php wp_print_head_scripts(); ?>
</head>

<body class="price-editor-page">
    <div class="header-section">
        <h1><?php echo esc_html($darktech_pe_i18n['page']['heading']); ?></h1>
        <div class="header-actions">
            <button id="clear-cache-btn" class="clear-cache-btn" title="<?php echo esc_attr($darktech_pe_i18n['page']['clearCacheTitle']); ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 4v6h-6"></path>
                    <path d="M1 20v-6h6"></path>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
            </button>
        </div>
        <div class="breadcrumb-row">
            <div class="breadcrumb">
                <a href="<?php echo esc_url(admin_url()); ?>"><?php echo esc_html($darktech_pe_i18n['page']['breadcrumbAdmin']); ?></a> &gt;
                <span><?php echo esc_html($darktech_pe_i18n['page']['breadcrumbCurrent']); ?></span>
            </div>
            <span id="products-count" class="products-count" role="button" tabindex="0" title="<?php echo esc_attr($darktech_pe_i18n['page']['productsLimitPrompt']); ?>"></span>
        </div>
    </div>

    <div class="filters-section">
        <div class="column-filter-icon-wrapper">
            <button id="column-filter-toggle" class="column-filter-btn" title="<?php echo esc_attr($darktech_pe_i18n['columnManager']['toggleTitle']); ?>">
                <svg class="filter-icon" viewBox="0 0 24 24" width="20" height="20">
                    <rect x="3" y="3" width="7" height="7" fill="currentColor" />
                    <rect x="14" y="3" width="7" height="3" fill="currentColor" />
                    <rect x="14" y="8" width="7" height="2" fill="currentColor" />
                    <rect x="3" y="14" width="7" height="7" fill="currentColor" />
                    <rect x="14" y="14" width="7" height="3" fill="currentColor" />
                    <rect x="14" y="19" width="7" height="2" fill="currentColor" />
                </svg>
            </button>
        </div>

        <div class="filter-group">
            <label for="status-filter"><?php echo esc_html($darktech_pe_i18n['filters']['status']); ?></label>
            <select id="status-filter">
                <option value=""><?php echo esc_html($darktech_pe_i18n['filters']['allStatuses']); ?></option>
                <?php foreach ($darktech_pe_product_statuses as $darktech_pe_status_key => $darktech_pe_status_label) : ?>
                    <option value="<?php echo esc_attr($darktech_pe_status_key); ?>" <?php selected($darktech_pe_status_key, 'publish'); ?>>
                        <?php echo esc_html($darktech_pe_status_label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="category-filter"><?php echo esc_html($darktech_pe_i18n['filters']['category']); ?></label>
            <select id="category-filter">
                <option value=""><?php echo esc_html($darktech_pe_i18n['filters']['allCategories']); ?></option>
            </select>
        </div>

        <div class="filter-group search-group">
            <label for="search-input"><?php echo esc_html($darktech_pe_i18n['filters']['search']); ?></label>
            <input type="text" id="search-input" placeholder="<?php echo esc_attr($darktech_pe_i18n['filters']['searchPlaceholder']); ?>">
        </div>

        <div class="filter-group">
            <label for="tax-filter"><?php echo esc_html($darktech_pe_i18n['filters']['taxStatus']); ?></label>
            <select id="tax-filter">
                <option value=""><?php echo esc_html($darktech_pe_i18n['filters']['all']); ?></option>
                <?php foreach ($darktech_pe_tax_statuses as $darktech_pe_status_key => $darktech_pe_status_label) : ?>
                    <option value="<?php echo esc_attr($darktech_pe_status_key); ?>">
                        <?php echo esc_html($darktech_pe_status_label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="tax-class-filter"><?php echo esc_html($darktech_pe_i18n['filters']['taxClass']); ?></label>
            <select id="tax-class-filter">
                <option value=""><?php echo esc_html($darktech_pe_i18n['filters']['allTaxClasses']); ?></option>
            </select>
        </div>

        <div class="filter-group">
            <label for="stock-filter"><?php echo esc_html($darktech_pe_i18n['filters']['stockStatus']); ?></label>
            <select id="stock-filter">
                <option value=""><?php echo esc_html($darktech_pe_i18n['filters']['all']); ?></option>
                <?php foreach ($darktech_pe_stock_statuses as $darktech_pe_status_key => $darktech_pe_status_label) : ?>
                    <option value="<?php echo esc_attr($darktech_pe_status_key); ?>">
                        <?php echo esc_html($darktech_pe_status_label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="column-filter-modal" class="column-filter-modal" style="display: none;">
        <div class="column-filter-modal-content">
            <div class="column-filter-header">
                <h3><?php echo esc_html($darktech_pe_i18n['columnManager']['title']); ?></h3>
                <button id="column-filter-close" class="column-filter-close" title="<?php echo esc_attr($darktech_pe_i18n['columnManager']['close']); ?>">×</button>
            </div>
            <div class="column-list">
                <?php foreach ($darktech_pe_column_order as $darktech_pe_index => $darktech_pe_column_key) : ?>
                    <label class="column-item">
                        <input type="checkbox" data-column="<?php echo esc_attr((string) $darktech_pe_index); ?>" checked>
                        <span><?php echo esc_html($darktech_pe_column_labels[$darktech_pe_column_key]); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="column-filter-actions">
                <button id="show-all-columns" class="btn btn-small btn-primary"><?php echo esc_html($darktech_pe_i18n['columnManager']['showAll']); ?></button>
                <button id="hide-all-columns" class="btn btn-small btn-secondary"><?php echo esc_html($darktech_pe_i18n['columnManager']['hideAll']); ?></button>
                <button id="reset-columns" class="btn btn-small btn-secondary"><?php echo esc_html($darktech_pe_i18n['columnManager']['reset']); ?></button>
            </div>
        </div>
    </div>

    <div class="table-section">
        <table id="products-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <?php foreach ($darktech_pe_column_order as $darktech_pe_column_key) : ?>
                        <?php if ($darktech_pe_column_key === 'oldPrice') : ?>
                            <th class="old-price-column"><?php echo esc_html($darktech_pe_column_labels[$darktech_pe_column_key]); ?></th>
                        <?php elseif ($darktech_pe_column_key === 'taxClass') : ?>
                            <th class="tax-class-column"><?php echo esc_html($darktech_pe_column_labels[$darktech_pe_column_key]); ?></th>
                        <?php else : ?>
                            <th><?php echo esc_html($darktech_pe_column_labels[$darktech_pe_column_key]); ?></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div id="horizontal-scroll-bar" class="horizontal-scroll-bar">
            <div class="scroll-content">
                <div id="scroll-spacer" class="scroll-spacer"></div>
            </div>
        </div>

        <div class="developer-signature">
            <?php echo esc_html($darktech_pe_i18n['developer']['label']); ?>
            <a href="https://darktech.ru" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr($darktech_pe_i18n['developer']['linkTitle']); ?>">DarkTech</a>
            <?php echo esc_html($darktech_pe_i18n['developer']['year']); ?>
        </div>
    </div>

    <div class="errors-section" id="errors-section" style="display: none;">
        <div class="errors-header">
            <span class="errors-icon">⚠️</span>
            <span class="errors-title"><?php echo esc_html($darktech_pe_i18n['errors']['title']); ?></span>
            <button class="errors-close" id="errors-close" title="<?php echo esc_attr($darktech_pe_i18n['errors']['close']); ?>">✕</button>
        </div>
        <div class="errors-content" id="errors-content"></div>
    </div>

    <div
        class="tech-info-bar"
        id="tech-info-bar"
        role="button"
        tabindex="0"
        title="<?php echo esc_attr($darktech_pe_i18n['history']['openHint']); ?>"
        aria-label="<?php echo esc_attr($darktech_pe_i18n['history']['openHint']); ?>">
        <span class="info-icon">ℹ️</span>
        <span class="info-text" id="tech-info-text"><?php echo esc_html($darktech_pe_i18n['tech']['ready']); ?></span>
        <span class="info-time" id="tech-info-time"></span>
    </div>

    <div id="history-modal" class="history-modal" style="display: none;" aria-hidden="true">
        <div class="history-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="history-modal-title">
            <div class="history-modal-header">
                <h2 id="history-modal-title"><?php echo esc_html($darktech_pe_i18n['history']['title']); ?></h2>
                <button
                    type="button"
                    id="history-modal-close"
                    class="history-modal-close"
                    title="<?php echo esc_attr($darktech_pe_i18n['history']['close']); ?>"
                    aria-label="<?php echo esc_attr($darktech_pe_i18n['history']['close']); ?>">×</button>
            </div>

            <div class="history-modal-body">
                <div id="history-modal-feedback" class="history-modal-feedback" style="display: none;"></div>

                <div id="history-table-wrapper" class="history-table-wrapper">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html($darktech_pe_i18n['history']['dateColumn']); ?></th>
                                <th><?php echo esc_html($darktech_pe_i18n['history']['messageColumn']); ?></th>
                                <th><?php echo esc_html($darktech_pe_i18n['history']['userColumn']); ?></th>
                            </tr>
                        </thead>
                        <tbody id="history-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="confirm-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3><?php echo esc_html($darktech_pe_i18n['confirm']['title']); ?></h3>
            <p id="confirm-message"></p>
            <div class="modal-buttons">
                <button id="confirm-yes" class="btn btn-primary"><?php echo esc_html($darktech_pe_i18n['confirm']['yes']); ?></button>
                <button id="confirm-no" class="btn btn-secondary"><?php echo esc_html($darktech_pe_i18n['confirm']['no']); ?></button>
            </div>
        </div>
    </div>

    <?php wp_print_footer_scripts(); ?>
</body>

</html>
