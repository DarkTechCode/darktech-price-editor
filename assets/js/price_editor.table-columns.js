/**
 * DataTables column configuration for the editor.
 */

class PriceEditorTableColumnsFactory {
  constructor(editor) {
    this.editor = editor;
  }

  /**
   * Returns the full DataTables columns definition.
   */
  build() {
    return [
      this.createRowNumberColumn(),
      this.createIdColumn(),
      this.createCategoryColumn(),
      this.createTitleColumn(),
      this.createSkuColumn(),
      this.createPriceColumn("regular_price", "columns.regularPrice", "Price"),
      this.createPriceColumn("sale_price", "columns.salePrice", "Sale price"),
      this.createOldPriceColumn(),
      this.createStockStatusColumn(),
      this.createTaxStatusColumn(),
      this.createTaxClassColumn(),
      this.createActionsColumn(),
    ];
  }

  /**
   * Creates the row number column.
   */
  createRowNumberColumn() {
    return {
      data: null,
      title: this.editor.getText("columns.rowNumber", "#"),
      className: "text-center",
      orderable: true,
      type: "num",
      render: (data, type, row, meta) => {
        const totalRecords = window.priceEditorTotalRecords || 0;

        if (totalRecords > 0) {
          return totalRecords - meta.row;
        }

        return meta.row + 1;
      },
    };
  }

  /**
   * Creates the product ID column.
   */
  createIdColumn() {
    return {
      data: "id",
      title: this.editor.getText("columns.id", "ID"),
      className: "text-center",
      render: (data, type, row) => {
        return `<a href="${this.editor.escapeAttribute(row.view_url)}" target="_blank" rel="noopener noreferrer" class="product-id-link" title="${this.editor.escapeAttribute(
          this.editor.getText("table.viewProduct", "Click to view the product"),
        )}">${data}</a>`;
      },
    };
  }

  /**
   * Creates the category column.
   */
  createCategoryColumn() {
    return {
      data: "categories",
      title: this.editor.getText("columns.category", "Category"),
      className: "category-column",
      render: (data, type, row) => {
        const categoryText =
          data && data.trim() !== ""
            ? data
            : this.editor.getUncategorizedLabel();
        const displayText = this.truncateText(categoryText, 50);

        return this.wrapEditableCell(
          "category-cell",
          "category",
          row.id,
          this.editor.editingModule.renderFieldDisplay(
            "category",
            row.id,
            row.category_id || 0,
            displayText,
            row.category_slug || "",
            categoryText,
          ),
        );
      },
    };
  }

  /**
   * Creates the title column.
   */
  createTitleColumn() {
    return {
      data: "title",
      title: this.editor.getText("columns.title", "Product title"),
      className: "title-column",
      render: (data, type, row) => {
        return this.wrapEditableCell(
          "title-cell",
          "title",
          row.id,
          this.editor.editingModule.renderFieldDisplay("title", row.id, data),
        );
      },
    };
  }

  /**
   * Creates the SKU column.
   */
  createSkuColumn() {
    return {
      data: "sku",
      title: this.editor.getText("columns.sku", "SKU"),
      className: "sku-column",
      render: (data, type, row) => {
        return this.wrapEditableCell(
          "sku-cell",
          "sku",
          row.id,
          this.editor.editingModule.renderFieldDisplay(
            "sku",
            row.id,
            data || "",
          ),
        );
      },
    };
  }

  /**
   * Creates a price input column.
   */
  createPriceColumn(field, titlePath, titleFallback) {
    return {
      data: field,
      title: this.editor.getText(titlePath, titleFallback),
      className: "price-column",
      type: "num",
      orderable: true,
      render: (data, type, row) => {
        if (type === "sort" || type === "type") {
          return data || 0;
        }

        return this.editor.editingModule.createPriceInput(row.id, field, data);
      },
    };
  }

  /**
   * Creates the derived old price column.
   */
  createOldPriceColumn() {
    return {
      data: "old_price",
      title: this.editor.getText("columns.oldPrice", "Was"),
      className: "price-column",
      type: "num",
      orderable: true,
      render: (data, type, row) => {
        if (type === "sort" || type === "type") {
          const regularPrice = parseFloat(row.regular_price) || 0;
          const salePrice = parseFloat(row.sale_price) || 0;

          return Math.max(regularPrice, salePrice);
        }

        return `<span class="price-display">${this.editor.escapeHtml(
          row.regular_price || "-",
        )} / ${this.editor.escapeHtml(row.sale_price || "-")}</span>`;
      },
    };
  }

  /**
   * Creates the stock status column.
   */
  createStockStatusColumn() {
    return {
      data: "stock_status",
      title: this.editor.getText("columns.stockStatus", "Stock status"),
      className: "stock-column",
      render: (data, type, row) => {
        const statusValue = data || "instock";
        const statusText =
          row.stock_status_label || this.editor.getStockStatusText(statusValue);

        return this.wrapEditableCell(
          "stock-cell",
          "stock_status",
          row.id,
          this.editor.editingModule.renderFieldDisplay(
            "stock_status",
            row.id,
            statusValue,
            statusText,
          ),
        );
      },
    };
  }

  /**
   * Creates the tax status column.
   */
  createTaxStatusColumn() {
    return {
      data: "tax_status",
      title: this.editor.getText("columns.taxStatus", "Tax status"),
      className: "tax-column",
      render: (data, type, row) => {
        const statusValue = data || "taxable";
        const statusText =
          row.tax_status_label || this.editor.getTaxStatusText(statusValue);

        return this.wrapEditableCell(
          "tax-status-cell",
          "tax_status",
          row.id,
          this.editor.editingModule.renderFieldDisplay(
            "tax_status",
            row.id,
            statusValue,
            statusText,
          ),
        );
      },
    };
  }

  /**
   * Creates the tax class column.
   */
  createTaxClassColumn() {
    return {
      data: "tax_class",
      title: this.editor.getText("columns.taxClass", "Tax class"),
      className: "tax-column",
      render: (data, type, row) => {
        const classValue = data || "";
        const classText =
          row.tax_class_label || this.editor.getTaxClassDisplayText(classValue);

        return this.wrapEditableCell(
          "tax-class-cell",
          "tax_class",
          row.id,
          this.editor.editingModule.renderFieldDisplay(
            "tax_class",
            row.id,
            classValue,
            classText,
          ),
        );
      },
    };
  }

  /**
   * Creates the actions column.
   */
  createActionsColumn() {
    return {
      data: null,
      title: this.editor.getText("columns.actions", "Actions"),
      className: "actions-column",
      orderable: false,
      render: (data, type, row) => {
        return `<a href="${this.editor.escapeAttribute(row.edit_url)}" target="_blank" rel="noopener noreferrer" class="edit-link">${this.editor.escapeHtml(
          this.editor.getText("table.edit", "Edit"),
        )}</a>`;
      },
    };
  }

  /**
   * Wraps editable content in a cell container.
   */
  wrapEditableCell(wrapperClass, field, productId, content) {
    return `
      <div class="${this.editor.escapeAttribute(wrapperClass)}" data-field="${this.editor.escapeAttribute(field)}" data-id="${this.editor.escapeAttribute(productId)}">
        ${content}
      </div>
    `;
  }

  /**
   * Truncates long text for compact table display.
   */
  truncateText(text, maxLength) {
    if (!text || text.length <= maxLength) {
      return text;
    }

    return `${text.substring(0, maxLength)}...`;
  }
}
