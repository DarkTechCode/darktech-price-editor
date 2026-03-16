/**
 * Markup helpers for inline editing controls and display cells.
 */

class PriceEditorEditingMarkupFactory {
  constructor(editor) {
    this.editor = editor;
  }

  /**
   * Creates a price input.
   */
  createPriceInput(productId, field, value) {
    return this.createWrappedControl({
      field,
      productId,
      controlMarkup: `<input type="text" class="price-input" value="${this.editor.escapeAttribute(
        value || "",
      )}">`,
    });
  }

  /**
   * Creates a title input.
   */
  createTitleInput(productId, value) {
    return this.createWrappedControl({
      field: "title",
      productId,
      controlMarkup: `<input type="text" class="title-input" value="${this.editor.escapeAttribute(
        (value || "").trim(),
      )}" placeholder="${this.editor.escapeAttribute(
        this.editor.getText("placeholders.title", "Enter title"),
      )}">`,
    });
  }

  /**
   * Creates a SKU input.
   */
  createSkuInput(productId, value) {
    return this.createWrappedControl({
      field: "sku",
      productId,
      controlMarkup: `<input type="text" class="sku-input" value="${this.editor.escapeAttribute(
        (value || "").trim(),
      )}" placeholder="${this.editor.escapeAttribute(
        this.editor.getText("placeholders.sku", "Enter SKU"),
      )}">`,
    });
  }

  /**
   * Creates a stock status select.
   */
  createStockStatusSelect(productId, currentValue) {
    return this.createSelectControl({
      field: "stock_status",
      productId,
      selectClass: "select-stock-status",
      currentValue,
      options: Object.entries(this.editor.statuses.stock || {}).map(
        ([value, label]) => ({ value, label }),
      ),
    });
  }

  /**
   * Creates a tax status select.
   */
  createTaxStatusSelect(productId, currentValue) {
    return this.createSelectControl({
      field: "tax_status",
      productId,
      selectClass: "select-tax-status",
      currentValue,
      options: Object.entries(this.editor.statuses.tax || {}).map(
        ([value, label]) => ({ value, label }),
      ),
    });
  }

  /**
   * Creates a tax class select.
   */
  createTaxClassSelect(productId, currentValue) {
    return this.createSelectControl({
      field: "tax_class",
      productId,
      selectClass: "select-tax-class",
      currentValue: currentValue || "",
      options: this.editor.taxClasses.map((taxClass) => ({
        value: taxClass.slug || "",
        label: taxClass.name,
      })),
    });
  }

  /**
   * Creates a category select.
   */
  createCategorySelect(productId, currentCategoryId) {
    const uncategorized = this.editor.getUncategorizedLabel();

    return this.createSelectControl({
      field: "category",
      productId,
      selectClass: "select-category",
      currentValue: String(currentCategoryId || 0),
      options: [
        { value: "0", label: uncategorized },
        ...this.editor.categories.map((category) => {
          const prefix =
            (category.depth || 0) > 0 ? `${"- ".repeat(category.depth)}` : "";

          return {
            value: String(category.id),
            label: `${prefix}${category.name}`,
          };
        }),
      ],
    });
  }

  /**
   * Renders title view mode.
   */
  renderTitleDisplay(productId, text) {
    return this.renderEditableText({
      className: "title-text",
      field: "title",
      productId,
      text,
      title: this.editor.getText(
        "editing.clickToEditTitle",
        "Click to edit the product title",
      ),
    });
  }

  /**
   * Renders SKU view mode.
   */
  renderSkuDisplay(productId, text) {
    const actualValue = text || "";
    const displayText =
      actualValue || this.editor.getText("defaults.emptySku", "-");
    const clickText = actualValue
      ? this.editor.getText("editing.clickToEditSku", "Click to edit the SKU")
      : this.editor.getText("editing.clickToAddSku", "Click to add a SKU");

    return this.renderEditableText({
      className: "sku-text",
      field: "sku",
      productId,
      text: displayText,
      title: actualValue ? `${actualValue} - ${clickText}` : clickText,
    });
  }

  /**
   * Renders stock status view mode.
   */
  renderStockStatusDisplay(productId, value, text = null) {
    return this.renderEditableText({
      className: `stock-status ${this.editor.getStockStatusClass(value)}`,
      field: "stock_status",
      productId,
      text: text || this.editor.getStockStatusText(value),
      title: this.editor.getText(
        "editing.clickToEditStockStatus",
        "Click to edit the stock status",
      ),
      dataAttributes: {
        value,
      },
    });
  }

  /**
   * Renders tax status view mode.
   */
  renderTaxStatusDisplay(productId, value, text = null) {
    return this.renderEditableText({
      className: `tax-status ${this.editor.getTaxStatusClass(value)}`,
      field: "tax_status",
      productId,
      text: text || this.editor.getTaxStatusText(value),
      title: this.editor.getText(
        "editing.clickToEditTaxStatus",
        "Click to edit the tax status",
      ),
      dataAttributes: {
        value,
      },
    });
  }

  /**
   * Renders tax class view mode.
   */
  renderTaxClassDisplay(productId, value, text = null) {
    return this.renderEditableText({
      className: "tax-class",
      field: "tax_class",
      productId,
      text: text || this.editor.getTaxClassDisplayText(value),
      title: this.editor.getText(
        "editing.clickToEditTaxClass",
        "Click to edit the tax class",
      ),
      dataAttributes: {
        value,
      },
    });
  }

  /**
   * Renders category view mode.
   */
  renderCategoryDisplay(
    productId,
    categoryId,
    text = null,
    categorySlug = "",
    tooltipText = null,
  ) {
    const category =
      this.editor.categories.find(
        (item) => Number(item.id) === Number(categoryId),
      ) || null;
    const displayText =
      text || category?.name || this.editor.getUncategorizedLabel();
    const fullTooltipText = tooltipText || displayText;
    const resolvedCategorySlug = category?.slug || categorySlug || "";
    const clickText = this.editor.getText(
      "editing.clickToEditCategory",
      "Click to edit the category",
    );

    return this.renderEditableText({
      className: "category-names",
      field: "category",
      productId,
      text: displayText,
      title:
        fullTooltipText === this.editor.getUncategorizedLabel()
          ? clickText
          : `${fullTooltipText} - ${clickText}`,
      dataAttributes: {
        "category-id": categoryId,
        "category-slug": resolvedCategorySlug,
      },
    });
  }

  createWrappedControl({ field, productId, controlMarkup }) {
    return `
      <div class="editable" data-field="${this.editor.escapeAttribute(field)}" data-id="${this.editor.escapeAttribute(productId)}">
        ${controlMarkup}
      </div>
    `;
  }

  /**
   * Creates a select control wrapper.
   */
  createSelectControl({
    field,
    productId,
    selectClass,
    currentValue,
    options,
  }) {
    return this.createWrappedControl({
      field,
      productId,
      controlMarkup: `<select class="${this.editor.escapeAttribute(selectClass)}">
        ${options
          .map(({ value, label }) => {
            const normalizedValue = String(value ?? "");
            const selected =
              String(currentValue ?? "") === normalizedValue ? "selected" : "";

            return `<option value="${this.editor.escapeAttribute(
              normalizedValue,
            )}" ${selected}>${this.editor.escapeHtml(label)}</option>`;
          })
          .join("")}
      </select>`,
    });
  }

  /**
   * Renders a clickable editable span.
   */
  renderEditableText({
    className,
    field,
    productId,
    text,
    title,
    dataAttributes = {},
  }) {
    return `
      <span class="${this.editor.escapeAttribute(className)} editable-text" title="${this.editor.escapeAttribute(
        title,
      )}" data-field="${this.editor.escapeAttribute(field)}" data-id="${this.editor.escapeAttribute(
        String(productId),
      )}"${this.renderDataAttributes(dataAttributes)}>
        ${this.editor.escapeHtml(text ?? "")}
      </span>
    `;
  }

  renderDataAttributes(dataAttributes) {
    return Object.entries(dataAttributes)
      .map(([name, value]) => {
        if (value === undefined || value === null) {
          return "";
        }

        return ` data-${this.editor.escapeAttribute(name)}="${this.editor.escapeAttribute(String(value))}"`;
      })
      .join("");
  }
}
