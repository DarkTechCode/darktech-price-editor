/**
 * Price editor core module.
 */

class PriceEditor {
  constructor() {
    this.config = window.darktech_pe || {};
    this.i18n = this.config.i18n || {};
    this.statuses = this.config.statuses || {};
    this.locale = this.config.locale || "en-US";
    this.table = null;
    this.currentFilters = {
      status: "publish",
      category: "",
      search: "",
      tax_status: "",
      tax_class: "",
      stock_status: "",
    };
    this.categories = [];
    this.taxClasses = [];
    this.errors = [];
    this.errorsShown = false;

    this.dataModule = new PriceEditorDataModule(this);
    this.editingModule = new PriceEditorEditingModule(this);
    this.uiModule = new PriceEditorUIModule(this);
    this.historyModule = new PriceEditorHistoryModule(this);
    this.filtersModule = new PriceEditorFiltersModule(this);
    this.columnsModule = new PriceEditorColumnsModule(this);
    this.mobileModule = new PriceEditorMobileModule(this);
    this.horizontalScrollModule = new PriceEditorHorizontalScrollModule(this);
    this.tableColumnsFactory = new PriceEditorTableColumnsFactory(this);

    this.init();
  }

  /**
   * Initializes the editor.
   */
  init() {
    window.priceEditor = this;

    this.bindEvents();
    this.uiModule.updateTechInfo(
      this.getText("tech.loadingInitialData", "Loading data..."),
      true
    );

    this.dataModule.loadInitialData().finally(() => {
      this.initDataTable();
      this.uiModule.updateTechInfo(this.getText("tech.ready", "Ready to work"));
    });
  }

  /**
   * Binds top-level UI events.
   */
  bindEvents() {
    this.filtersModule.bindFilterEvents();
    this.historyModule.bindEvents();

    jQuery("#confirm-yes").on("click", () => this.uiModule.confirmAction(true));
    jQuery("#confirm-no").on("click", () => this.uiModule.confirmAction(false));
    jQuery("#errors-close").on("click", () => this.uiModule.hideErrors());
    jQuery("#clear-cache-btn").on("click", () => this.clearCacheAndReload());
    jQuery("#products-count").on("click", () => this.promptProductsLimit());
  }

  /**
   * Clears cached data and reloads the interface.
   */
  async clearCacheAndReload() {
    this.dataModule.cache.clear();

    this.showNotification(
      this.getText("tech.cacheReloaded", "Cache cleared, reloading data..."),
      "info"
    );
    this.updateTechInfo(
      this.getText("tech.cacheReloading", "Clearing cache and reloading..."),
      true
    );

    await this.dataModule.loadInitialData();

    if (this.table) {
      this.loadProducts();
    }
  }

  /**
   * Initializes DataTables.
   */
  initDataTable() {
    if (this.table) {
      return;
    }

    if (!jQuery.fn.DataTable) {
      this.uiModule.addError(
        this.getText(
          "messages.missingDataTables",
          "Error: DataTables library is not loaded"
        )
      );
      return;
    }

    this.table = jQuery("#products-table").DataTable({
      processing: true,
      serverSide: false,
      ajax: this.buildAjaxConfig(),
      columns: this.tableColumnsFactory.build(),
      pageLength: -1,
      lengthChange: false,
      paging: false,
      language: {
        ...(this.config.datatables || {}),
        lengthMenu: "",
        search: "",
        searchPlaceholder: "",
        info: "",
        infoEmpty: "",
      },
      order: [[1, "desc"]],
      columnDefs: [
        { className: "text-center", targets: [0] },
        { className: "text-right", targets: [5, 6, 7] },
        { searchable: false, targets: "_all" },
      ],
      drawCallback: () => {
        this.editingModule.bindEditableEvents();
        this.horizontalScrollModule.refresh();
      },
      initComplete: () => {
        jQuery(".dataTables_length").hide();
        jQuery(".dataTables_filter").hide();
        jQuery(".dataTables_info").hide();
        jQuery(".dataTables_paginate").hide();

        this.columnsModule.applyColumnSettings();
        this.horizontalScrollModule.refresh();
      },
    });
  }

  /**
   * Builds the DataTables AJAX configuration.
   */
  buildAjaxConfig() {
    return {
      url: this.config.ajax_url,
      method: "POST",
      data: (request) => {
        request.action = "darktech_pe_get_products";
        request.nonce = this.config.nonce;
        request.status = this.currentFilters.status;
        request.category = this.currentFilters.category;
        request.search = this.currentFilters.search;
        request.tax_status = this.currentFilters.tax_status;
        request.tax_class = this.currentFilters.tax_class;
        request.stock_status = this.currentFilters.stock_status;
      },
      dataSrc: (json) => this.handleProductsResponse(json),
      error: (xhr, error, thrown) => this.handleProductsError(xhr, error, thrown),
    };
  }

  /**
   * Normalizes successful AJAX responses for DataTables.
   */
  handleProductsResponse(json) {
    if (json.success && json.data) {
      if (json.data.recordsTotal) {
        window.priceEditorTotalRecords = json.data.recordsTotal;
      }

      const products = json.data.products || [];
      this.updateProductsCount(products.length);
      return products;
    }

    this.updateProductsCount(0);
    return [];
  }

  /**
   * Normalizes AJAX errors for DataTables.
   */
  handleProductsError(xhr, error, thrown) {
    if (error === "abort") {
      return [];
    }

    let errorMessage = this.formatText(
      "messages.dataLoadError",
      {
        message: thrown || this.getText("messages.unknownError", "Unknown error"),
      },
      `Error loading data: ${
        thrown || this.getText("messages.unknownError", "Unknown error")
      }`
    );

    if (xhr.responseText) {
      try {
        const response = JSON.parse(xhr.responseText);
        if (response.data && response.data.message) {
          errorMessage = this.formatText(
            "messages.dataLoadError",
            { message: response.data.message },
            `Error loading data: ${response.data.message}`
          );
        }
      } catch (parseError) {
        // Ignore malformed JSON payloads from failed requests.
      }
    }

    this.uiModule.addError(errorMessage);
    return [];
  }

  /**
   * Reloads products table data.
   */
  loadProducts() {
    if (this.table) {
      this.table.ajax.reload();
    }
  }

  /**
   * Returns a localized string by dot path.
   */
  getText(path, fallback = "") {
    if (!path) {
      return fallback;
    }

    const value = path.split(".").reduce((current, key) => {
      if (
        current &&
        Object.prototype.hasOwnProperty.call(current, key) &&
        current[key] !== undefined
      ) {
        return current[key];
      }

      return undefined;
    }, this.i18n);

    return typeof value === "string" ? value : fallback;
  }

  /**
   * Formats a localized template with named replacements.
   */
  formatText(path, replacements = {}, fallback = "") {
    let template = this.getText(path, fallback);
    if (typeof template !== "string") {
      return fallback;
    }

    Object.entries(replacements).forEach(([key, value]) => {
      template = template.replaceAll(`{${key}}`, String(value));
    });

    return template;
  }

  /**
   * Returns a localized status label.
   */
  getStatusText(group, statusValue, fallback = "") {
    const labels = this.statuses[group] || {};
    return labels[statusValue] || fallback || statusValue || "";
  }

  /**
   * Returns a CSS class for stock status.
   */
  getStockStatusClass(statusValue) {
    const classMap = {
      instock: "stock-instock",
      outofstock: "stock-outofstock",
      onbackorder: "stock-onbackorder",
    };

    return classMap[statusValue] || "stock-status";
  }

  /**
   * Returns a CSS class for tax status.
   */
  getTaxStatusClass(statusValue) {
    const classMap = {
      taxable: "tax-taxable",
      shipping: "tax-shipping",
      none: "tax-none",
    };

    return classMap[statusValue] || "tax-status";
  }

  updateTechInfo(message, loading = false) {
    this.uiModule.updateTechInfo(message, loading);
  }

  showNotification(message, type = "info") {
    this.uiModule.showNotification(message, type);
  }

  addError(errorMessage) {
    this.uiModule.addError(errorMessage);
  }

  showErrors() {
    this.uiModule.showErrors();
  }

  hideErrors() {
    this.uiModule.hideErrors();
  }

  openHistoryModal() {
    this.historyModule.openModal();
  }

  closeHistoryModal() {
    this.historyModule.closeModal();
  }

  getProductStatusText(statusValue) {
    return this.getStatusText("product", statusValue, statusValue);
  }

  getStockStatusText(statusValue) {
    return this.filtersModule.getStockStatusText(statusValue);
  }

  getTaxStatusText(statusValue) {
    return this.filtersModule.getTaxStatusText(statusValue);
  }

  getTaxClassDisplayText(slug) {
    return this.filtersModule.getTaxClassDisplayText(slug);
  }

  getUncategorizedLabel() {
    return this.getText("defaults.uncategorized", "Uncategorized");
  }

  getDefaultTaxClassLabel() {
    return this.getText("defaults.defaultTaxClass", "Standard");
  }

  getDefaultTaxClassFilterValue() {
    return this.getText("defaults.defaultTaxClassFilterValue", "__default__");
  }

  escapeHtml(text) {
    return this.filtersModule.escapeHtml(text);
  }

  escapeAttribute(text) {
    return this.filtersModule.escapeAttribute(text);
  }

  getErrorsCount() {
    return this.uiModule.getErrorsCount();
  }

  getColumnsModule() {
    return this.columnsModule;
  }

  getHorizontalScrollModule() {
    return this.horizontalScrollModule;
  }

  refreshHorizontalScroll() {
    this.horizontalScrollModule.refresh();
  }

  hideHorizontalScrollBar() {
    this.horizontalScrollModule.hideScrollBar();
  }

  showHorizontalScrollBar() {
    this.horizontalScrollModule.updateScrollBar();
  }

  /**
   * Updates the products count display.
   */
  updateProductsCount(count) {
    const text = this.formatText(
      "page.productsShown",
      { count: count },
      `Products shown: ${count}`
    );

    jQuery("#products-count").text(text);
  }

  /**
   * Prompts the user to change the products limit.
   */
  promptProductsLimit() {
    const currentLimit = this.config.products_limit || 3000;
    const promptText = this.getText(
      "page.productsLimitPrompt",
      "How many products to show at most?"
    );

    const input = prompt(promptText, currentLimit);
    if (input === null) {
      return;
    }

    const newLimit = parseInt(input, 10);
    if (isNaN(newLimit) || newLimit <= 0) {
      this.showNotification(
        this.getText(
          "page.productsLimitInvalid",
          "Please enter a valid number greater than 0."
        ),
        "error"
      );
      return;
    }

    this.saveProductsLimit(newLimit);
  }

  /**
   * Saves the new products limit via AJAX and reloads the table.
   */
  async saveProductsLimit(limit) {
    try {
      const response = await jQuery.ajax({
        url: this.config.ajax_url,
        method: "POST",
        data: {
          action: "darktech_pe_update_products_limit",
          nonce: this.config.nonce,
          limit: limit,
        },
      });

      if (!response.success) {
        throw new Error(
          response.data?.message || "Unknown error"
        );
      }

      this.config.products_limit = limit;

      this.showNotification(
        this.formatText(
          "page.productsLimitSaved",
          { limit: limit },
          `Product limit updated to ${limit}`
        ),
        "info"
      );

      this.loadProducts();
    } catch (error) {
      this.showNotification(
        this.formatText(
          "page.productsLimitSaveError",
          { message: error?.message || "Unknown error" },
          `Error saving the product limit: ${error?.message || "Unknown error"}`
        ),
        "error"
      );
    }
  }
}

window.priceEditor = null;

window.testColumnFilters = {
  showAll: () => window.priceEditor?.getColumnsModule()?.showAllColumns(),
  hideAll: () => window.priceEditor?.getColumnsModule()?.hideAllColumns(),
  reset: () => window.priceEditor?.getColumnsModule()?.resetToDefault(),
  getSettings: () =>
    window.priceEditor?.getColumnsModule()?.getCurrentSettings(),
};
