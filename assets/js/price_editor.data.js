/**
 * Lookup loading, caching, filter population and save entry points.
 */

class PriceEditorLocalCache {
  constructor(editor) {
    this.editor = editor;
    this.categoriesKey = `price_editor_categories_${editor.locale}`;
    this.taxClassesKey = `price_editor_tax_classes_${editor.locale}`;
    this.cacheTimeout = 24 * 60 * 60 * 1000;
  }

  /**
   * Reads a cache bucket if it is still fresh.
   */
  readBucket(key) {
    try {
      const cached = localStorage.getItem(key);
      if (!cached) {
        return null;
      }

      const parsed = JSON.parse(cached);
      if (!parsed || typeof parsed !== "object") {
        return null;
      }

      if (Date.now() - (parsed.timestamp || 0) >= this.cacheTimeout) {
        return null;
      }

      return parsed.data ?? null;
    } catch (error) {
      return null;
    }
  }

  /**
   * Persists a cache bucket.
   */
  writeBucket(key, data) {
    try {
      localStorage.setItem(
        key,
        JSON.stringify({
          data,
          timestamp: Date.now(),
        })
      );
    } catch (error) {
      // Ignore storage failures.
    }
  }

  getCategories() {
    return this.readBucket(this.categoriesKey);
  }

  setCategories(data) {
    this.writeBucket(this.categoriesKey, data);
  }

  getTaxClasses() {
    return this.readBucket(this.taxClassesKey);
  }

  setTaxClasses(data) {
    this.writeBucket(this.taxClassesKey, data);
  }

  clear() {
    localStorage.removeItem(this.categoriesKey);
    localStorage.removeItem(this.taxClassesKey);
  }
}

class PriceEditorDataModule {
  constructor(editor) {
    this.editor = editor;
    this.cache = new PriceEditorLocalCache(editor);
    this.saveService = new PriceEditorSaveService(editor);
  }

  /**
   * Loads all initial lookup data.
   */
  async loadInitialData() {
    await Promise.all([this.loadCategories(), this.loadTaxClasses()]);
  }

  /**
   * Loads product categories.
   */
  async loadCategories() {
    try {
      const cached = this.cache.getCategories();
      if (cached && cached.total_count !== undefined) {
        this.editor.categories = cached.categories || [];
        this.editor.totalProductCount = cached.total_count || 0;
        this.populateCategoryFilter();
        return;
      }

      const response = await $.ajax({
        url: this.editor.config.ajax_url,
        method: "POST",
        data: {
          action: "darktech_pe_get_categories",
          nonce: this.editor.config.nonce,
        },
      });

      if (!response.success) {
        throw new Error(
          response.data?.message ||
            this.editor.getText(
              "messages.categoriesLoadError",
              "Error loading categories"
            )
        );
      }

      this.editor.categories = response.data.categories || [];
      this.editor.totalProductCount = response.data.total_count || 0;
      this.cache.setCategories(response.data);
      this.populateCategoryFilter();
    } catch (error) {
      this.reportLookupError(
        error,
        "messages.categoriesLoadError",
        "messages.categoriesLoadErrorDetailed",
        "Error loading categories"
      );
    }
  }

  /**
   * Loads tax classes.
   */
  async loadTaxClasses() {
    try {
      const cached = this.cache.getTaxClasses();
      if (cached) {
        this.editor.taxClasses = cached;
        this.populateTaxClassFilter();
        return;
      }

      const response = await $.ajax({
        url: this.editor.config.ajax_url,
        method: "POST",
        data: {
          action: "darktech_pe_get_tax_classes",
          nonce: this.editor.config.nonce,
        },
      });

      if (!response.success) {
        throw new Error(
          response.data?.message ||
            this.editor.getText(
              "messages.taxClassesLoadError",
              "Error loading tax classes"
            )
        );
      }

      this.editor.taxClasses = response.data || [];
      this.cache.setTaxClasses(this.editor.taxClasses);
      this.populateTaxClassFilter();
    } catch (error) {
      this.reportLookupError(
        error,
        "messages.taxClassesLoadError",
        "messages.taxClassesLoadErrorDetailed",
        "Error loading tax classes"
      );
    }
  }

  /**
   * Fills the tax class filter select.
   */
  populateTaxClassFilter() {
    const $filter = $("#tax-class-filter");
    if ($filter.length === 0) {
      return;
    }

    const allTaxClassesLabel = this.editor.getText(
      "filters.allTaxClasses",
      "All tax classes"
    );
    const defaultFilterValue = this.editor.getDefaultTaxClassFilterValue();

    $filter.find("option:first").text(allTaxClassesLabel);
    $filter.find("option:not(:first)").remove();

    this.editor.taxClasses.forEach((taxClass) => {
      const optionValue =
        (taxClass.slug || "") === "" ? defaultFilterValue : taxClass.slug;

      $filter.append(
        `<option value="${this.editor.escapeAttribute(optionValue)}">${this.editor.escapeHtml(
          taxClass.name
        )}</option>`
      );
    });
  }

  /**
   * Fills the category filter select.
   */
  populateCategoryFilter() {
    const $filter = $("#category-filter");
    const totalCount = this.editor.totalProductCount || 0;
    const allCategoriesLabel = this.editor.getText(
      "filters.allCategories",
      "All categories"
    );

    $filter.find("option:first").text(`${allCategoriesLabel} (${totalCount})`);
    $filter.find("option:not(:first)").remove();

    this.editor.categories.forEach((category) => {
      const depth = category.depth || 0;
      const prefix = depth > 0 ? `${"- ".repeat(depth)}` : "";

      $filter.append(
        `<option value="${this.editor.escapeAttribute(category.slug)}">${this.editor.escapeHtml(
          `${prefix}${category.name} (${category.count})`
        )}</option>`
      );
    });
  }

  autoSaveField($editable) {
    this.saveService.autoSaveField($editable);
  }

  async saveTitleEdit($cell, newValue, oldValue, productId) {
    await this.saveService.saveTitleEdit($cell, newValue, oldValue, productId);
  }

  async saveSkuEdit($cell, newValue, oldValue, productId) {
    await this.saveService.saveSkuEdit($cell, newValue, oldValue, productId);
  }

  async saveStockStatusEdit($cell, newValue, oldValue, productId) {
    await this.saveService.saveStockStatusEdit(
      $cell,
      newValue,
      oldValue,
      productId
    );
  }

  async saveTaxStatusEdit($cell, newValue, oldValue, productId) {
    await this.saveService.saveTaxStatusEdit($cell, newValue, oldValue, productId);
  }

  async saveTaxClassEdit($cell, newValue, oldValue, oldDisplayText, productId) {
    await this.saveService.saveTaxClassEdit(
      $cell,
      newValue,
      oldValue,
      oldDisplayText,
      productId
    );
  }

  async saveCategoryEdit($cell, newValue, oldValue, oldDisplayText, productId) {
    await this.saveService.saveCategoryEdit(
      $cell,
      newValue,
      oldValue,
      oldDisplayText,
      productId
    );
  }

  /**
   * Adds a localized lookup loading error.
   */
  reportLookupError(error, titlePath, detailedPath, titleFallback) {
    const errorMessage =
      error?.message || this.editor.getText(titlePath, titleFallback);

    this.editor.addError(
      this.editor.formatText(
        detailedPath,
        { message: errorMessage },
        `${titleFallback}: ${errorMessage}`
      )
    );
  }
}
