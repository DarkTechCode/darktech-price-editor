/**
 * Debounced save helpers and shared save service behavior.
 */

class PriceEditorDebouncedSaver {
  constructor(saveService) {
    this.saveService = saveService;
    this.timeouts = new Map();
    this.emptyAllowedFields = new Set([
      "regular_price",
      "sale_price",
      "tax_class",
      "stock_status",
    ]);
  }

  /**
   * Schedules a debounced save for inline controls.
   */
  schedule($editable, delay = 1500) {
    const key = this.getKey($editable);

    clearTimeout(this.timeouts.get(key));

    const timeout = setTimeout(() => {
      this.performSave($editable, key);
    }, delay);

    this.timeouts.set(key, timeout);
  }

  /**
   * Executes a debounced inline save.
   */
  async performSave($editable, key) {
    const productId = $editable.data("id");
    const field = $editable.data("field");
    const value = this.getEditableValue($editable);

    if (!value && !this.emptyAllowedFields.has(field)) {
      this.timeouts.delete(key);
      return;
    }

    try {
      const response = await this.saveService.requestCellUpdate({
        $cell: $editable,
        field,
        value,
        productId,
        loadingTextPath: "tech.savingChanges",
        loadingFallback: "Saving changes...",
      });

      this.saveService.handleInlineFieldSuccess(response, field, productId, value);
    } catch (error) {
      this.saveService.handleGenericSaveError(error, $editable);
    } finally {
      $editable.removeClass("saving");
      this.saveService.syncDisplaySavingIndicator($editable);
      this.timeouts.delete(key);
    }
  }

  /**
   * Clears all pending save timers.
   */
  clear() {
    this.timeouts.forEach((timeout) => clearTimeout(timeout));
    this.timeouts.clear();
  }

  getKey($editable) {
    return `${$editable.data("id")}-${$editable.data("field")}`;
  }

  getEditableValue($editable) {
    return $editable.find("input, select").first().val();
  }
}

class PriceEditorBaseSaveService {
  constructor(editor) {
    this.editor = editor;
    this.debouncedSaver = new PriceEditorDebouncedSaver(this);
  }

  syncDisplaySavingIndicator($cell) {
    if (this.editor?.editingModule?.syncDisplaySavingIndicator) {
      this.editor.editingModule.syncDisplaySavingIndicator($cell);
    }
  }

  /**
   * Triggers debounced autosave for inline inputs.
   */
  autoSaveField($editable) {
    const $input = $editable.find("input, select");
    if ($input.length && $editable.data("original-value") === undefined) {
      $editable.data("original-value", $input.val());
    }

    this.debouncedSaver.schedule($editable, 1500);
  }

  /**
   * Performs a managed save flow for cell editors.
   */
  async saveManagedEdit({
    $cell,
    field,
    newValue,
    productId,
    loadingTextPath,
    loadingFallback,
    notificationPath,
    notificationFallback,
    successMessagePath,
    successMessageFallback,
    buildMessageReplacements,
    errorMessagePath,
    errorFallbackPrefix,
    onSuccess,
    onError,
  }) {
    try {
      const response = await this.requestCellUpdate({
        $cell,
        field,
        value: newValue,
        productId,
        loadingTextPath,
        loadingFallback,
      });

      this.updateRowInTable(productId, field, newValue);

      this.editor.showNotification(
        this.editor.getText(notificationPath, notificationFallback),
        "success"
      );
      this.editor.updateTechInfo(
        response.data?.message ||
          this.editor.formatText(
            successMessagePath,
            buildMessageReplacements(),
            successMessageFallback
          )
      );

      if (typeof onSuccess === "function") {
        onSuccess(response);
      }
    } catch (error) {
      this.handleSaveError(error, errorMessagePath, errorFallbackPrefix);

      if (typeof onError === "function") {
        onError(error);
      }
    } finally {
      $cell.removeClass("saving");
      this.syncDisplaySavingIndicator($cell);
    }
  }

  /**
   * Sends the product update request.
   */
  async requestCellUpdate({
    $cell,
    field,
    value,
    productId,
    loadingTextPath,
    loadingFallback,
  }) {
    this.editor.updateTechInfo(
      this.editor.getText(loadingTextPath, loadingFallback),
      true
    );

    if ($cell) {
      $cell.addClass("saving");
      this.syncDisplaySavingIndicator($cell);
    }

    const response = await $.ajax({
      url: this.editor.config.ajax_url,
      method: "POST",
      data: {
        action: "darktech_pe_update_product",
        nonce: this.editor.config.nonce,
        product_id: productId,
        field,
        value,
      },
    });

    if (!response.success) {
      throw new Error(
        response.data?.message ||
          this.editor.getText("messages.unknownError", "Unknown error")
      );
    }

    return response;
  }

  /**
   * Handles successful generic inline field saves.
   */
  handleInlineFieldSuccess(response, field, productId, value) {
    this.updateRowInTable(productId, field, value);

    this.editor.showNotification(
      this.editor.getText("notifications.changesSaved", "Changes saved"),
      "success"
    );
    this.editor.updateTechInfo(
      response.data?.message ||
        this.editor.formatText(
          "messages.fieldUpdatedMessage",
          {
            field: this.getFieldLabel(field),
            id: productId,
          },
          `Updated ${this.getFieldLabel(field)} for product #${productId}`
        )
    );
  }

  /**
   * Handles save request failures for inline inputs.
   */
  handleGenericSaveError(error, $editable) {
    this.handleSaveError(error, "messages.saveError", "Save error: ");

    const originalValue = $editable.data("original-value");
    if (originalValue !== undefined) {
      $editable.find("input, select").val(originalValue);
    }
  }

  /**
   * Formats and records a save error message.
   */
  handleSaveError(error, messagePath, fallbackPrefix) {
    const errorMessage =
      error?.message || this.editor.getText("messages.unknownError", "Unknown error");

    this.editor.addError(
      this.editor.formatText(
        messagePath,
        { message: errorMessage },
        `${fallbackPrefix}${errorMessage}`
      )
    );
    this.editor.showErrors();
  }

  /**
   * Updates cached row data after a successful save.
   */
  updateRowInTable(productId, field, newValue) {
    if (!this.editor.table) {
      return;
    }

    const rowApi = this.editor.table.row((index, data) => {
      return Number(data?.id) === Number(productId);
    });
    const rowData = rowApi.data();

    if (!rowData) {
      return;
    }

    switch (field) {
      case "stock_status":
        rowData.stock_status = newValue;
        rowData.stock_status_label = this.editor.getStockStatusText(newValue);
        break;
      case "tax_status":
        rowData.tax_status = newValue;
        rowData.tax_status_label = this.editor.getTaxStatusText(newValue);
        break;
      case "tax_class":
        rowData.tax_class = newValue;
        rowData.tax_class_label = this.editor.getTaxClassDisplayText(newValue);
        break;
      case "category": {
        const categoryId = parseInt(newValue, 10) || 0;
        const category =
          this.editor.categories.find((item) => item.id === categoryId) || null;
        const categoryName = category
          ? category.name
          : this.editor.getUncategorizedLabel();

        rowData.category = categoryName;
        rowData.category_id = categoryId;
        rowData.category_slug = category ? category.slug : "";
        rowData.categories = categoryName;
        break;
      }
      default:
        rowData[field] = newValue;
        break;
    }

    rowApi.data(rowData);

    const rowNode = rowApi.node();
    if (!rowNode) {
      return;
    }

    $(rowNode).addClass("saved-success");
    setTimeout(() => {
      $(rowNode).removeClass("saved-success");
    }, 2000);
  }

  /**
   * Returns a localized field label for generic notifications.
   */
  getFieldLabel(field) {
    const labels = {
      regular_price: this.editor.getText("fields.regularPrice", "regular price"),
      sale_price: this.editor.getText("fields.salePrice", "sale price"),
      title: this.editor.getText("fields.title", "product title"),
      tax_status: this.editor.getText("fields.taxStatus", "tax status"),
      tax_class: this.editor.getText("fields.taxClass", "tax class"),
      stock_status: this.editor.getText("fields.stockStatus", "stock status"),
      category: this.editor.getText("fields.category", "category"),
      sku: this.editor.getText("fields.sku", "SKU"),
    };

    return labels[field] || field;
  }

}
