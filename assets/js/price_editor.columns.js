/**
 * Column visibility controls.
 */

class PriceEditorColumnsModule {
  constructor(editor) {
    this.editor = editor;
    this.columnSettingsKey = "priceEditorColumnSettings";
    this.modalVisible = false;

    this.init();
  }

  /**
   * Initializes the module.
   */
  init() {
    this.bindEvents();
    this.loadColumnSettings();
  }

  /**
   * Binds column manager UI events.
   */
  bindEvents() {
    $("#column-filter-toggle").on("click", (event) => {
      event.preventDefault();
      this.showModal();
    });

    $("#column-filter-close").on("click", () => {
      this.hideModal();
    });

    $("#column-filter-modal").on("click", (event) => {
      if (event.target === event.currentTarget) {
        this.hideModal();
      }
    });

    $(document).on(
      "change",
      "#column-filter-modal input[type='checkbox']",
      (event) => {
        const columnIndex = parseInt($(event.target).data("column"), 10);
        const visible = $(event.target).is(":checked");
        this.toggleColumn(columnIndex, visible);
      }
    );

    $("#show-all-columns").on("click", () => this.showAllColumns());
    $("#hide-all-columns").on("click", () => this.hideAllColumns());
    $("#reset-columns").on("click", () => this.resetToDefault());

    $(document).on("keydown", (event) => {
      if (event.key === "Escape" && this.modalVisible) {
        this.hideModal();
      }
    });
  }

  /**
   * Shows the column manager modal.
   */
  showModal() {
    $("#column-filter-modal").show();
    $("#column-filter-toggle").addClass("active");
    this.modalVisible = true;
  }

  /**
   * Hides the column manager modal.
   */
  hideModal() {
    $("#column-filter-modal").hide();
    $("#column-filter-toggle").removeClass("active");
    this.modalVisible = false;
  }

  /**
   * Toggles one column visibility.
   */
  toggleColumn(columnIndex, visible) {
    if (!this.editor.table) {
      return;
    }

    this.editor.table.column(columnIndex).visible(visible);
    this.saveColumnSettings();

    const columnName = $("#column-filter-modal .column-item")
      .eq(columnIndex)
      .find("span")
      .text();
    const message = visible
      ? this.editor.formatText(
          "columnManager.shownMessage",
          { column: columnName },
          `Column "${columnName}" shown`
        )
      : this.editor.formatText(
          "columnManager.hiddenMessage",
          { column: columnName },
          `Column "${columnName}" hidden`
        );

    this.editor.uiModule.updateTechInfo(message);
  }

  /**
   * Saves visibility settings to localStorage.
   */
  saveColumnSettings() {
    const settings = {};

    $("#column-filter-modal input[type='checkbox']").each((index, element) => {
      const columnIndex = $(element).data("column");
      settings[columnIndex] = $(element).is(":checked");
    });

    try {
      localStorage.setItem(this.columnSettingsKey, JSON.stringify(settings));
    } catch (error) {
      console.warn("Could not save column settings", error);
    }
  }

  /**
   * Loads saved settings from localStorage.
   */
  loadColumnSettings() {
    try {
      const savedSettings = localStorage.getItem(this.columnSettingsKey);
      if (savedSettings) {
        this.applySavedSettings(JSON.parse(savedSettings));
      }
    } catch (error) {
      console.warn("Could not load column settings", error);
    }
  }

  /**
   * Applies saved checkbox states to the modal UI.
   */
  applySavedSettings(settings) {
    Object.keys(settings).forEach((columnIndex) => {
      const index = parseInt(columnIndex, 10);
      const visible = settings[columnIndex];
      const $checkbox = $(
        `#column-filter-modal input[data-column="${index}"]`
      );

      if ($checkbox.length) {
        $checkbox.prop("checked", visible);
      }
    });
  }

  /**
   * Applies current checkbox visibility settings to the table.
   */
  applyColumnSettings(delay = 0) {
    const applySettings = () => {
      if (!this.editor.table) {
        return;
      }

      $("#column-filter-modal input[type='checkbox']").each((index, element) => {
        const columnIndex = parseInt($(element).data("column"), 10);
        const visible = $(element).is(":checked");
        this.editor.table.column(columnIndex).visible(visible);
      });
    };

    if (delay > 0) {
      setTimeout(applySettings, delay);
    } else {
      applySettings();
    }
  }

  /**
   * Resets all columns to visible.
   */
  resetToDefault() {
    $("#column-filter-modal input[type='checkbox']").prop("checked", true);
    this.applyColumnSettings();
    this.saveColumnSettings();
    this.editor.uiModule.updateTechInfo(
      this.editor.getText(
        "columnManager.resetMessage",
        "Column settings reset"
      )
    );
  }

  /**
   * Makes all columns visible.
   */
  showAllColumns() {
    $("#column-filter-modal input[type='checkbox']").prop("checked", true);
    this.applyColumnSettings();
    this.saveColumnSettings();
    this.editor.uiModule.updateTechInfo(
      this.editor.getText("columnManager.showAllMessage", "All columns are visible")
    );
  }

  /**
   * Hides all columns except the row number.
   */
  hideAllColumns() {
    $("#column-filter-modal input[type='checkbox']").prop("checked", false);
    $("#column-filter-modal input[data-column='0']").prop("checked", true);
    this.applyColumnSettings();
    this.saveColumnSettings();
    this.editor.uiModule.updateTechInfo(
      this.editor.getText(
        "columnManager.hideAllMessage",
        "All columns hidden except the row number"
      )
    );
  }

  /**
   * Returns current visibility settings.
   */
  getCurrentSettings() {
    const settings = {};

    $("#column-filter-modal input[type='checkbox']").each((index, element) => {
      const columnIndex = $(element).data("column");
      settings[columnIndex] = $(element).is(":checked");
    });

    return settings;
  }
}
