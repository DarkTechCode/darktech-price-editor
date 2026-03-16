/**
 * UI helpers for notifications, modal confirmations and diagnostics.
 */

class PriceEditorUIModule {
  constructor(editor) {
    this.editor = editor;
  }

  /**
   * Updates technical info bar.
   */
  updateTechInfo(message, loading = false) {
    jQuery("#tech-info-text").text(message);
    jQuery("#tech-info-time").text(new Date().toLocaleTimeString(this.editor.locale));

    if (loading) {
      jQuery("#tech-info-text").prev(".spinner").remove();
      jQuery("#tech-info-text").before('<span class="spinner"></span>');
    } else {
      jQuery(".spinner").remove();
    }
  }

  /**
   * Shows a temporary notification.
   */
  showNotification(message, type = "info") {
    const $notification = jQuery("<div>", {
      class: `notification ${type}`,
      text: message,
    });

    jQuery("body").append($notification);

    setTimeout(() => $notification.addClass("show"), 100);

    setTimeout(() => {
      $notification.removeClass("show");
      setTimeout(() => $notification.remove(), 300);
    }, 3000);
  }

  /**
   * Resolves a pending confirmation action.
   */
  confirmAction(confirmed) {
    jQuery("#confirm-modal").hide();

    if (confirmed && this.pendingAction) {
      this.pendingAction();
    }

    this.pendingAction = null;
  }

  /**
   * Displays all collected errors.
   */
  showErrors() {
    if (this.editor.errors.length === 0) {
      return;
    }

    const $errorsSection = jQuery("#errors-section");
    const $errorsContent = jQuery("#errors-content");

    $errorsContent.empty();

    this.editor.errors.forEach((error) => {
      $errorsContent.append(`
        <div class="error-item" data-error-id="${error.id}">
          <span class="error-icon">⚠️</span>
          <span class="error-text">${this.editor.escapeHtml(error.message)}</span>
          <span class="error-time">${error.time}</span>
        </div>
      `);
    });

    $errorsSection.show();
    this.editor.errorsShown = true;

    setTimeout(() => {
      if (this.editor.errorsShown) {
        this.hideErrors();
      }
    }, 10000);
  }

  /**
   * Hides the error panel.
   */
  hideErrors() {
    jQuery("#errors-section").hide();
    this.editor.errorsShown = false;
  }

  /**
   * Adds an error to the internal log.
   */
  addError(errorMessage) {
    this.editor.errors.push({
      message: errorMessage,
      time: new Date().toLocaleTimeString(this.editor.locale),
      id: Date.now() + Math.random(),
    });
  }

  /**
   * Clears all collected errors.
   */
  clearErrors() {
    this.editor.errors = [];
    this.hideErrors();
  }

  /**
   * Returns current error count.
   */
  getErrorsCount() {
    return this.editor.errors.length;
  }
}
