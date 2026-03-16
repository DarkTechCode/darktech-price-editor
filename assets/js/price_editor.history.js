/**
 * Fullscreen history popup for the status bar.
 */

class PriceEditorHistoryModule {
  constructor(editor) {
    this.editor = editor;
    this.lastFocusedElement = null;
    this.currentRequest = null;
  }

  /**
   * Binds the history popup controls.
   */
  bindEvents() {
    $("#tech-info-bar").on("click", () => {
      this.openModal();
    });

    $("#tech-info-bar").on("keydown", (event) => {
      if (event.key !== "Enter" && event.key !== " " && event.key !== "Spacebar") {
        return;
      }

      event.preventDefault();
      this.openModal();
    });

    $("#history-modal-close").on("click", () => this.closeModal());

    $("#history-modal").on("click", (event) => {
      if (event.target === event.currentTarget) {
        this.closeModal();
      }
    });

    $(document).on("keydown.priceEditorHistory", (event) => {
      if (event.key === "Escape" && this.isOpen()) {
        event.preventDefault();
        this.closeModal();
      }
    });
  }

  /**
   * Opens the fullscreen history popup and loads its content.
   */
  openModal() {
    this.lastFocusedElement =
      document.activeElement instanceof HTMLElement ? document.activeElement : null;

    $("body").addClass("history-modal-open");
    $("#history-modal").css("display", "flex").attr("aria-hidden", "false");
    $("#history-modal-close").trigger("focus");

    this.loadHistory();
  }

  /**
   * Closes the popup and restores focus.
   */
  closeModal() {
    if (this.currentRequest && typeof this.currentRequest.abort === "function") {
      this.currentRequest.abort();
    }

    $("#history-modal").hide().attr("aria-hidden", "true");
    $("body").removeClass("history-modal-open");

    if (this.lastFocusedElement && typeof this.lastFocusedElement.focus === "function") {
      this.lastFocusedElement.focus();
    }
  }

  /**
   * Returns whether the popup is open.
   */
  isOpen() {
    return $("#history-modal").is(":visible");
  }

  /**
   * Loads the latest history items from the server.
   */
  async loadHistory() {
    if (this.currentRequest && typeof this.currentRequest.abort === "function") {
      this.currentRequest.abort();
    }

    this.renderFeedback(this.editor.getText("history.loading", "Loading history..."));

    try {
      this.currentRequest = $.ajax({
        url: this.editor.config.ajax_url,
        method: "POST",
        data: {
          action: "darktech_pe_get_change_history",
          nonce: this.editor.config.nonce,
        },
      });

      const response = await this.currentRequest;
      if (!response?.success) {
        throw new Error(
          response?.data?.message ||
            this.editor.getText("messages.unknownError", "Unknown error")
        );
      }

      const items = Array.isArray(response?.data?.items) ? response.data.items : [];
      this.renderItems(items);
    } catch (error) {
      if (this.isAbortError(error)) {
        return;
      }

      const errorMessage =
        error?.message || this.editor.getText("messages.unknownError", "Unknown error");
      const formattedMessage = this.editor.formatText(
        "history.loadError",
        { message: errorMessage },
        `Error loading history: ${errorMessage}`
      );

      this.renderFeedback(formattedMessage, true);
      this.editor.addError(formattedMessage);
    } finally {
      this.currentRequest = null;
    }
  }

  /**
   * Renders the loaded items.
   */
  renderItems(items) {
    const $tbody = $("#history-table-body");
    $tbody.empty();

    if (!items.length) {
      this.renderFeedback(
        this.editor.getText("history.empty", "Change history is empty so far.")
      );
      return;
    }

    $("#history-modal-feedback").hide().removeClass("is-error").text("");
    $("#history-table-wrapper").show();

    items.forEach((item) => {
      const dateDisplay = this.editor.escapeHtml(item?.date_display || "");
      const message = this.editor.escapeHtml(item?.message || "");
      const userDisplayName = this.editor.escapeHtml(item?.user_display_name || "");

      $tbody.append(`
        <tr>
          <td class="history-col-date">${dateDisplay}</td>
          <td class="history-col-message">${message}</td>
          <td class="history-col-user">${userDisplayName}</td>
        </tr>
      `);
    });
  }

  /**
   * Shows a feedback message and hides the table.
   */
  renderFeedback(message, isError = false) {
    $("#history-table-wrapper").hide();
    $("#history-table-body").empty();
    $("#history-modal-feedback")
      .text(message)
      .toggleClass("is-error", isError)
      .show();
  }

  /**
   * Detects aborted AJAX requests.
   */
  isAbortError(error) {
    if (!error) {
      return false;
    }

    return (
      error === "abort" ||
      error?.statusText === "abort" ||
      error?.readyState === 0
    );
  }
}
