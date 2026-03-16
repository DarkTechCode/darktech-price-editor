/**
 * Main entry point for the price editor.
 */

function initPriceEditor() {
  const messages = window.darktech_pe?.i18n?.messages || {};
  const jQueryInstance = window.jQuery;

  if (typeof jQueryInstance !== "function") {
    console.error(messages.missingJQuery || "jQuery is not loaded");
    return;
  }

  if (typeof jQueryInstance.fn?.DataTable === "undefined") {
    console.error(messages.missingDataTablesConsole || "DataTables is not loaded");
    return;
  }

  window.priceEditor = new PriceEditor();
}

if (typeof document !== "undefined") {
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initPriceEditor);
  } else {
    initPriceEditor();
  }
}

window.showNotification = function (message, type) {
  window.priceEditor?.showNotification(message, type);
};

window.updateTechInfo = function (message, loading) {
  window.priceEditor?.updateTechInfo(message, loading);
};

window.showErrors = function () {
  window.priceEditor?.showErrors();
};

window.hideErrors = function () {
  window.priceEditor?.hideErrors();
};

if (typeof module !== "undefined" && module.exports) {
  module.exports = {
    PriceEditor,
    PriceEditorCore: PriceEditor,
    PriceEditorDataModule,
    PriceEditorEditingModule,
    PriceEditorUIModule,
    PriceEditorHistoryModule,
    PriceEditorFiltersModule,
  };
}
