/**
 * Main entry point for the price editor.
 */

$(document).ready(function () {
  const messages = window.darktech_pe?.i18n?.messages || {};

  if (typeof $ === "undefined") {
    console.error(messages.missingJQuery || "jQuery is not loaded");
    return;
  }

  if (typeof $.fn.DataTable === "undefined") {
    console.error(messages.missingDataTablesConsole || "DataTables is not loaded");
    return;
  }

  window.priceEditor = new PriceEditor();
});

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
