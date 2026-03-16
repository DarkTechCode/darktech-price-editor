/**
 * Inline editing controllers and shared editing behavior.
 */

class PriceEditorEditingModule {
  constructor(editor) {
    this.editor = editor;
    this.markupFactory = new PriceEditorEditingMarkupFactory(editor);
    this.activeEdits = [];
    this.fieldConfigs = this.buildFieldConfigs();
  }

  /**
   * Proxies price input rendering for the table columns factory.
   */
  createPriceInput(productId, field, value) {
    return this.markupFactory.createPriceInput(productId, field, value);
  }

  /**
   * Renders a field display using the markup factory.
   */
  renderFieldDisplay(field, ...args) {
    const renderMethodMap = {
      title: "renderTitleDisplay",
      sku: "renderSkuDisplay",
      stock_status: "renderStockStatusDisplay",
      tax_status: "renderTaxStatusDisplay",
      tax_class: "renderTaxClassDisplay",
      category: "renderCategoryDisplay",
    };
    const method = renderMethodMap[field];

    if (!method || typeof this.markupFactory[method] !== "function") {
      return "";
    }

    return this.markupFactory[method](...args);
  }

  /**
   * Binds delegated handlers for editable content.
   */
  bindEditableEvents() {
    const namespace = ".price-editor-editable";

    jQuery(document).off(namespace);

    jQuery(document).on(`click${namespace}`, ".editable-text", (event) => {
      event.stopPropagation();

      const $element = jQuery(event.currentTarget);
      const field = String($element.data("field") || "");
      const $cell = $element.closest("td");

      this.startFieldEdit(field, $cell);
    });

    jQuery(document).on(`input${namespace}`, ".price-input", (event) => {
      const $editable = jQuery(event.currentTarget).closest(".editable");
      this.editor.dataModule.autoSaveField($editable);
    });
  }

  /**
   * Returns field-specific editor configuration.
   */
  buildFieldConfigs() {
    return {
      title: {
        wrapperSelector: ".title-cell",
        controlSelector: "input",
        getState: ($editableText) => ({
          productId: $editableText.data("id"),
          value: String($editableText.data("value") || "").trim(),
        }),
        createMarkup: (state) =>
          this.markupFactory.createTitleInput(state.productId, state.value),
        onFinish: ($cell, $control, state, options = {}) =>
          this.finishTextFieldEdit("title", $cell, $control, state, options),
        onCancel: ($cell, state) =>
          this.cancelTitleEdit($cell, state.value, state.productId),
        onReady: ($control) => $control.select(),
      },
      sku: {
        wrapperSelector: ".sku-cell",
        controlSelector: "input",
        getState: ($editableText) => {
          const currentText = $editableText.text().trim();
          const emptySkuLabel = this.editor.getText("defaults.emptySku", "-");

          return {
            productId: $editableText.data("id"),
            value: currentText === emptySkuLabel ? "" : currentText,
          };
        },
        createMarkup: (state) =>
          this.markupFactory.createSkuInput(state.productId, state.value),
        onFinish: ($cell, $control, state, options = {}) =>
          this.finishTextFieldEdit("sku", $cell, $control, state, options),
        onCancel: ($cell, state) =>
          this.cancelSkuEdit($cell, state.value, state.productId),
        onReady: ($control) => $control.select(),
      },
      stock_status: {
        wrapperSelector: ".stock-cell",
        controlSelector: "select",
        eventName: "change",
        getState: ($editableText) => ({
          productId: $editableText.data("id"),
          value: $editableText.data("value") || "instock",
        }),
        createMarkup: (state) =>
          this.markupFactory.createStockStatusSelect(
            state.productId,
            state.value,
          ),
        onCommit: ($cell, $control, state) => {
          this.editor.dataModule.saveStockStatusEdit(
            $cell,
            $control.val(),
            state.value,
            state.productId,
          );
        },
        onCancel: ($cell, state) =>
          this.cancelStockStatusEdit($cell, state.value, state.productId),
      },
      tax_status: {
        wrapperSelector: ".tax-status-cell",
        controlSelector: "select",
        eventName: "change",
        getState: ($editableText) => ({
          productId: $editableText.data("id"),
          value: $editableText.data("value") || "taxable",
        }),
        createMarkup: (state) =>
          this.markupFactory.createTaxStatusSelect(
            state.productId,
            state.value,
          ),
        onCommit: ($cell, $control, state) => {
          this.editor.dataModule.saveTaxStatusEdit(
            $cell,
            $control.val(),
            state.value,
            state.productId,
          );
        },
        onCancel: ($cell, state) =>
          this.cancelTaxStatusEdit($cell, state.value, state.productId),
      },
      tax_class: {
        wrapperSelector: ".tax-class-cell",
        controlSelector: "select",
        eventName: "change",
        getState: ($editableText) => ({
          productId: $editableText.data("id"),
          value: $editableText.data("value") || "",
          text: $editableText.text().trim(),
        }),
        createMarkup: (state) =>
          this.markupFactory.createTaxClassSelect(state.productId, state.value),
        onCommit: ($cell, $control, state) => {
          this.editor.dataModule.saveTaxClassEdit(
            $cell,
            $control.val(),
            state.value,
            state.text,
            state.productId,
          );
        },
        onCancel: ($cell, state) =>
          this.cancelTaxClassEdit(
            $cell,
            state.value,
            state.productId,
            state.text,
          ),
      },
      category: {
        wrapperSelector: ".category-cell",
        controlSelector: "select",
        eventName: "change",
        getState: ($editableText) => ({
          productId: $editableText.data("id"),
          value: parseInt($editableText.data("categoryId"), 10) || 0,
          text: $editableText.text().trim(),
          slug: $editableText.data("categorySlug") || "",
        }),
        createMarkup: (state) =>
          this.markupFactory.createCategorySelect(state.productId, state.value),
        onCommit: ($cell, $control, state) => {
          this.editor.dataModule.saveCategoryEdit(
            $cell,
            $control.val(),
            state.value,
            state.text,
            state.productId,
          );
        },
        onCancel: ($cell, state) =>
          this.cancelCategoryEdit(
            $cell,
            state.value,
            state.productId,
            state.text,
            state.slug,
          ),
      },
    };
  }

  /**
   * Starts field editing based on its configuration.
   */
  startFieldEdit(field, $cell) {
    const config = this.fieldConfigs[field];
    if (!config) {
      return;
    }

    if (this.activeEdits.length > 0) {
      this.cancelAllEdits("outside");
    }

    const state = config.getState($cell.find(".editable-text"));

    this.activateFieldEdit({
      $cell,
      wrapperSelector: config.wrapperSelector,
      controlMarkup: config.createMarkup(state),
      controlSelector: config.controlSelector,
      eventName: config.eventName,
      onCommit: config.onCommit
        ? ($control) => config.onCommit($cell, $control, state)
        : null,
      onFinish: config.onFinish
        ? ($control, options = {}) =>
            config.onFinish($cell, $control, state, options)
        : null,
      onCancel: () => config.onCancel($cell, state),
      onReady: config.onReady,
    });
  }

  /**
   * Cancels all active edits.
   */
  cancelAllEdits(reason = "cancel") {
    [...this.activeEdits].forEach((editInfo) => {
      if (
        reason === "outside" &&
        typeof editInfo.outsideClickCallback === "function"
      ) {
        editInfo.outsideClickCallback();
        return;
      }

      editInfo.cancelCallback();
    });

    this.activeEdits = [];
    jQuery(document).off("click.editing-outside");
  }

  /**
   * Restores title cell view mode.
   */
  cancelTitleEdit($cell, originalText, productId) {
    this.finishEdit(
      $cell,
      ".title-cell",
      this.renderFieldDisplay("title", productId, originalText),
    );
  }

  /**
   * Restores SKU cell view mode.
   */
  cancelSkuEdit($cell, originalText, productId) {
    this.finishEdit(
      $cell,
      ".sku-cell",
      this.renderFieldDisplay("sku", productId, originalText),
    );
  }

  /**
   * Switches a text field back to display mode immediately and keeps the saving state visible.
   */
  showPendingTextSave($cell, field, nextText, productId) {
    const wrapperSelectorMap = {
      title: ".title-cell",
      sku: ".sku-cell",
    };
    const wrapperSelector = wrapperSelectorMap[field];

    if (!wrapperSelector) {
      return;
    }

    $cell.addClass("saving");
    this.finishEdit(
      $cell,
      wrapperSelector,
      this.renderFieldDisplay(field, productId, nextText),
    );
  }

  /**
   * Saves a text field and optionally moves focus to the next row in the same column.
   */
  finishTextFieldEdit(field, $cell, $control, state, options = {}) {
    const { moveDirection = null } = options;
    const $nextCell =
      moveDirection !== null
        ? this.findAdjacentEditableCell($cell, field, moveDirection)
        : jQuery();

    const saveMethodMap = {
      title: "saveTitleEdit",
      sku: "saveSkuEdit",
    };
    const saveMethod = saveMethodMap[field];

    if (saveMethod && typeof this.editor.dataModule[saveMethod] === "function") {
      void this.editor.dataModule[saveMethod](
        $cell,
        $control.val(),
        state.value,
        state.productId,
      );
    }

    if ($nextCell.length > 0) {
      this.startFieldEdit(field, $nextCell);
    }
  }

  /**
   * Finds the adjacent visible editable cell for the same field.
   */
  findAdjacentEditableCell($cell, field, direction = "next") {
    const wrapperSelector = this.fieldConfigs[field]?.wrapperSelector;
    if (!wrapperSelector) {
      return jQuery();
    }

    const $rows = $cell.closest("tbody").children("tr");
    const currentRowIndex = $rows.index($cell.closest("tr"));
    const step = direction === "previous" ? -1 : 1;

    for (
      let index = currentRowIndex + step;
      index >= 0 && index < $rows.length;
      index += step
    ) {
      const $candidateCell = jQuery($rows[index])
        .find(wrapperSelector)
        .closest("td")
        .first();

      if ($candidateCell.length > 0) {
        return $candidateCell;
      }
    }

    return jQuery();
  }

  /**
   * Restores stock status cell view mode.
   */
  cancelStockStatusEdit($cell, originalValue, productId, originalText = null) {
    this.finishEdit(
      $cell,
      ".stock-cell",
      this.renderFieldDisplay(
        "stock_status",
        productId,
        originalValue,
        originalText,
      ),
    );
  }

  /**
   * Restores tax status cell view mode.
   */
  cancelTaxStatusEdit($cell, originalValue, productId, originalText = null) {
    this.finishEdit(
      $cell,
      ".tax-status-cell",
      this.renderFieldDisplay(
        "tax_status",
        productId,
        originalValue,
        originalText,
      ),
    );
  }

  /**
   * Restores tax class cell view mode.
   */
  cancelTaxClassEdit($cell, originalValue, productId, originalText = null) {
    this.finishEdit(
      $cell,
      ".tax-class-cell",
      this.renderFieldDisplay(
        "tax_class",
        productId,
        originalValue,
        originalText,
      ),
    );
  }

  /**
   * Restores category cell view mode.
   */
  cancelCategoryEdit(
    $cell,
    originalCategoryId,
    productId,
    originalCategoryText = null,
    originalCategorySlug = "",
  ) {
    this.finishEdit(
      $cell,
      ".category-cell",
      this.renderFieldDisplay(
        "category",
        productId,
        originalCategoryId,
        originalCategoryText,
        originalCategorySlug,
      ),
    );
  }

  /**
   * Inserts an editor into a cell based on control configuration.
   */
  activateFieldEdit({
    $cell,
    wrapperSelector,
    controlMarkup,
    controlSelector,
    eventName,
    onCommit,
    onFinish,
    onCancel,
    onReady,
  }) {
    const $control = jQuery(controlMarkup).find(controlSelector);

    const $wrapper = $cell.find(wrapperSelector);

    $wrapper.removeClass("display-saving").html($control);
    $control.focus();

    if (typeof onReady === "function") {
      onReady($control);
    }

    this.addOutsideClickHandler(
      $cell,
      onCancel,
      onFinish ? () => onFinish($control) : null,
    );

    if (eventName && typeof onCommit === "function") {
      $control.on(eventName, () => onCommit($control));
    }

    $control.on("keydown", (event) => {
      if (event.key === "Escape") {
        event.preventDefault();
        onCancel();
        return;
      }

      if (typeof onFinish !== "function") {
        return;
      }

      if (event.key === "Enter") {
        event.preventDefault();
        onFinish($control);
        return;
      }

      if (event.key === "Tab") {
        event.preventDefault();
        onFinish($control, {
          moveDirection: event.shiftKey ? "previous" : "next",
        });
      }
    });
  }

  /**
   * Re-renders a cell and unregisters it from outside click handling.
   */
  finishEdit($cell, wrapperSelector, markup) {
    this.removeActiveEdit($cell);
    $cell.find(wrapperSelector).html(markup);
    this.syncDisplaySavingIndicator($cell);
    this.syncOutsideClickHandler();
  }

  /**
   * Toggles the display-mode saving indicator for text-based cells.
   */
  syncDisplaySavingIndicator($cell) {
    [".title-cell", ".sku-cell"].forEach((wrapperSelector) => {
      const $wrapper = $cell.find(wrapperSelector);
      if (!$wrapper.length) {
        return;
      }

      const shouldShowIndicator =
        $cell.hasClass("saving") && $wrapper.find(".editable-text").length > 0;

      $wrapper.toggleClass("display-saving", shouldShowIndicator);
    });
  }

  /**
   * Registers outside click handling for an active edit.
   */
  addOutsideClickHandler($cell, cancelCallback, outsideClickCallback = null) {
    this.removeActiveEdit($cell);
    this.activeEdits.push({
      cell: $cell,
      cancelCallback,
      outsideClickCallback,
      cellElement: $cell[0],
    });
    this.syncOutsideClickHandler();
  }

  /**
   * Removes an active edit registration for a cell.
   */
  removeActiveEdit($cell) {
    this.activeEdits = this.activeEdits.filter(
      (editInfo) => !editInfo.cell.is($cell),
    );
  }

  /**
   * Rebinds outside click handling based on current active edits.
   */
  syncOutsideClickHandler() {
    jQuery(document).off("click.editing-outside");

    if (this.activeEdits.length === 0) {
      return;
    }

    jQuery(document).on("click.editing-outside", (event) => {
      const clickedOnEditCell = this.activeEdits.some((editInfo) => {
        const $activeCell = jQuery(editInfo.cellElement);
        return (
          $activeCell.is(event.target) ||
          $activeCell.find(event.target).length > 0
        );
      });

      if (!clickedOnEditCell) {
        this.cancelAllEdits("outside");
      }
    });
  }
}
