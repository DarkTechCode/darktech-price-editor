/**
 * Filters and lookup helpers.
 */

class PriceEditorFiltersModule {
  constructor(editor) {
    this.editor = editor;
  }

  /**
   * Binds filter inputs.
   */
  bindFilterEvents() {
    jQuery("#status-filter").on("change", () => this.applyFilters());
    jQuery("#category-filter").on("change", () => this.applyFilters());
    jQuery("#tax-filter").on("change", () => this.applyFilters());
    jQuery("#tax-class-filter").on("change", () => this.applyFilters());
    jQuery("#stock-filter").on("change", () => this.applyFilters());

    let searchTimeout;
    jQuery("#search-input").on("input", () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => this.applyFilters(), 300);
    });
  }

  /**
   * Applies current filters.
   */
  applyFilters() {
    this.editor.currentFilters = this.getCurrentFilters();

    if (this.editor.table) {
      this.editor.table.ajax.reload();
      this.editor.uiModule.updateTechInfo(
        this.editor.getText("tech.filtersApplied", "New filters applied")
      );
    }
  }

  /**
   * Returns current filter values.
   */
  getCurrentFilters() {
    return {
      status: jQuery("#status-filter").val() || "",
      category: jQuery("#category-filter").val() || "",
      search: jQuery("#search-input").val() || "",
      tax_status: jQuery("#tax-filter").val() || "",
      tax_class: jQuery("#tax-class-filter").val() || "",
      stock_status: jQuery("#stock-filter").val() || "",
    };
  }

  /**
   * Resets all filters to their default state.
   */
  resetFilters() {
    jQuery("#status-filter").val("publish");
    jQuery("#category-filter").val("");
    jQuery("#search-input").val("");
    jQuery("#tax-filter").val("");
    jQuery("#tax-class-filter").val("");
    jQuery("#stock-filter").val("");

    this.applyFilters();
    this.editor.uiModule.updateTechInfo(
      this.editor.getText("tech.filtersReset", "Filters reset")
    );
  }

  /**
   * Returns a localized tax status label.
   */
  getTaxStatusText(statusValue) {
    return this.editor.getStatusText("tax", statusValue, "Taxable");
  }

  /**
   * Returns a display name for tax class slug.
   */
  getTaxClassDisplayText(slug) {
    const taxClass = this.editor.taxClasses.find((item) => item.slug === slug);
    return taxClass
      ? taxClass.name
      : slug || this.editor.getDefaultTaxClassLabel();
  }

  /**
   * Returns a localized stock status label.
   */
  getStockStatusText(statusValue) {
    return this.editor.getStatusText("stock", statusValue, "In stock");
  }

  /**
   * Escapes HTML special characters.
   */
  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text ?? "";
    return div.innerHTML;
  }

  /**
   * Escapes HTML attribute values.
   */
  escapeAttribute(text) {
    return String(text ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll('"', "&quot;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll("'", "&#39;");
  }
}
