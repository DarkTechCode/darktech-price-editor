/**
 * Field-specific save handlers.
 */

class PriceEditorSaveService extends PriceEditorBaseSaveService {
  /**
   * Saves title edits.
   */
  async saveTitleEdit($cell, newValue, oldValue, productId) {
    const normalizedOldValue = String(oldValue || "").trim();
    const normalizedNewValue = String(newValue || "").trim();

    if (!normalizedNewValue || normalizedNewValue === normalizedOldValue) {
      this.editor.editingModule.cancelTitleEdit(
        $cell,
        normalizedOldValue,
        productId,
      );
      return;
    }

    this.editor.editingModule.showPendingTextSave(
      $cell,
      "title",
      normalizedNewValue,
      productId,
    );

    await this.saveManagedEdit({
      $cell,
      field: "title",
      newValue: normalizedNewValue,
      productId,
      loadingTextPath: "tech.savingTitle",
      loadingFallback: "Saving the product title...",
      notificationPath: "notifications.titleUpdated",
      notificationFallback: "Title updated",
      successMessagePath: "messages.titleUpdatedMessage",
      successMessageFallback: `Updated product #${productId} title: ${normalizedOldValue} -> ${normalizedNewValue}`,
      buildMessageReplacements: () => ({
        id: productId,
        old: normalizedOldValue,
        new: normalizedNewValue,
      }),
      errorMessagePath: "messages.titleSaveError",
      errorFallbackPrefix: "Error saving the title: ",
      onSuccess: null,
      onError: () =>
        this.editor.editingModule.cancelTitleEdit(
          $cell,
          normalizedOldValue,
          productId,
        ),
    });
  }

  /**
   * Saves SKU edits.
   */
  async saveSkuEdit($cell, newValue, oldValue, productId) {
    if (newValue === oldValue) {
      this.editor.editingModule.cancelSkuEdit($cell, oldValue, productId);
      return;
    }

    this.editor.editingModule.showPendingTextSave(
      $cell,
      "sku",
      newValue,
      productId,
    );

    await this.saveManagedEdit({
      $cell,
      field: "sku",
      newValue,
      productId,
      loadingTextPath: "tech.savingSku",
      loadingFallback: "Saving the SKU...",
      notificationPath: "notifications.skuUpdated",
      notificationFallback: "SKU updated",
      successMessagePath: "messages.skuUpdatedMessage",
      successMessageFallback: `Updated product #${productId} SKU: "${oldValue}" -> "${newValue}"`,
      buildMessageReplacements: () => ({
        id: productId,
        old: oldValue,
        new: newValue,
      }),
      errorMessagePath: "messages.skuSaveError",
      errorFallbackPrefix: "Error saving the SKU: ",
      onSuccess: null,
      onError: () =>
        this.editor.editingModule.cancelSkuEdit($cell, oldValue, productId),
    });
  }

  /**
   * Saves stock status edits.
   */
  async saveStockStatusEdit($cell, newValue, oldValue, productId) {
    if (newValue === oldValue) {
      this.editor.editingModule.cancelStockStatusEdit($cell, oldValue, productId);
      return;
    }

    await this.saveManagedEdit({
      $cell,
      field: "stock_status",
      newValue,
      productId,
      loadingTextPath: "tech.savingStockStatus",
      loadingFallback: "Saving the stock status...",
      notificationPath: "notifications.stockStatusUpdated",
      notificationFallback: "Stock status updated",
      successMessagePath: "messages.stockStatusUpdatedMessage",
      successMessageFallback: `Updated product #${productId} stock status: "${this.editor.getStockStatusText(
        oldValue
      )}" -> "${this.editor.getStockStatusText(newValue)}"`,
      buildMessageReplacements: () => ({
        id: productId,
        old: this.editor.getStockStatusText(oldValue),
        new: this.editor.getStockStatusText(newValue),
      }),
      errorMessagePath: "messages.stockStatusSaveError",
      errorFallbackPrefix: "Error saving the stock status: ",
      onSuccess: () =>
        this.editor.editingModule.cancelStockStatusEdit(
          $cell,
          newValue,
          productId
        ),
      onError: () =>
        this.editor.editingModule.cancelStockStatusEdit(
          $cell,
          oldValue,
          productId
        ),
    });
  }

  /**
   * Saves tax status edits.
   */
  async saveTaxStatusEdit($cell, newValue, oldValue, productId) {
    if (newValue === oldValue) {
      this.editor.editingModule.cancelTaxStatusEdit($cell, oldValue, productId);
      return;
    }

    await this.saveManagedEdit({
      $cell,
      field: "tax_status",
      newValue,
      productId,
      loadingTextPath: "tech.savingTaxStatus",
      loadingFallback: "Saving the tax status...",
      notificationPath: "notifications.taxStatusUpdated",
      notificationFallback: "Tax status updated",
      successMessagePath: "messages.taxStatusUpdatedMessage",
      successMessageFallback: `Updated product #${productId} tax status: "${this.editor.getTaxStatusText(
        oldValue
      )}" -> "${this.editor.getTaxStatusText(newValue)}"`,
      buildMessageReplacements: () => ({
        id: productId,
        old: this.editor.getTaxStatusText(oldValue),
        new: this.editor.getTaxStatusText(newValue),
      }),
      errorMessagePath: "messages.taxStatusSaveError",
      errorFallbackPrefix: "Error saving the tax status: ",
      onSuccess: () =>
        this.editor.editingModule.cancelTaxStatusEdit(
          $cell,
          newValue,
          productId
        ),
      onError: () =>
        this.editor.editingModule.cancelTaxStatusEdit(
          $cell,
          oldValue,
          productId
        ),
    });
  }

  /**
   * Saves tax class edits.
   */
  async saveTaxClassEdit($cell, newValue, oldValue, oldDisplayText, productId) {
    if (newValue === oldValue) {
      this.editor.editingModule.cancelTaxClassEdit(
        $cell,
        oldValue,
        productId,
        oldDisplayText
      );
      return;
    }

    const newDisplayText = this.editor.getTaxClassDisplayText(newValue);

    await this.saveManagedEdit({
      $cell,
      field: "tax_class",
      newValue,
      productId,
      loadingTextPath: "tech.savingTaxClass",
      loadingFallback: "Saving the tax class...",
      notificationPath: "notifications.taxClassUpdated",
      notificationFallback: "Tax class updated",
      successMessagePath: "messages.taxClassUpdatedMessage",
      successMessageFallback: `Updated product #${productId} tax class: "${oldDisplayText}" -> "${newDisplayText}"`,
      buildMessageReplacements: () => ({
        id: productId,
        old: oldDisplayText,
        new: newDisplayText,
      }),
      errorMessagePath: "messages.taxClassSaveError",
      errorFallbackPrefix: "Error saving the tax class: ",
      onSuccess: () =>
        this.editor.editingModule.cancelTaxClassEdit(
          $cell,
          newValue,
          productId,
          newDisplayText
        ),
      onError: () =>
        this.editor.editingModule.cancelTaxClassEdit(
          $cell,
          oldValue,
          productId,
          oldDisplayText
        ),
    });
  }

  /**
   * Saves category edits.
   */
  async saveCategoryEdit($cell, newValue, oldValue, oldDisplayText, productId) {
    if (newValue === String(oldValue)) {
      this.editor.editingModule.cancelCategoryEdit(
        $cell,
        oldValue,
        productId,
        oldDisplayText
      );
      return;
    }

    const newCategoryId = parseInt(newValue, 10) || 0;
    const newCategory =
      this.editor.categories.find((item) => item.id === newCategoryId) || null;
    const newDisplayText = newCategory
      ? newCategory.name
      : this.editor.getUncategorizedLabel();

    await this.saveManagedEdit({
      $cell,
      field: "category",
      newValue,
      productId,
      loadingTextPath: "tech.savingCategory",
      loadingFallback: "Saving the category...",
      notificationPath: "notifications.categoryUpdated",
      notificationFallback: "Category updated",
      successMessagePath: "messages.categoryUpdatedMessage",
      successMessageFallback: `Updated product #${productId} category: "${oldDisplayText}" -> "${newDisplayText}"`,
      buildMessageReplacements: () => ({
        id: productId,
        old: oldDisplayText,
        new: newDisplayText,
      }),
      errorMessagePath: "messages.categorySaveError",
      errorFallbackPrefix: "Error saving the category: ",
      onSuccess: () =>
        this.editor.editingModule.cancelCategoryEdit(
          $cell,
          newCategoryId,
          productId,
          newDisplayText,
          newCategory?.slug || ""
        ),
      onError: () =>
        this.editor.editingModule.cancelCategoryEdit(
          $cell,
          oldValue,
          productId,
          oldDisplayText
        ),
    });
  }
}
