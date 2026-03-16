/**
 * Mobile-specific enhancements for the price editor.
 */

class PriceEditorMobileModule {
  constructor(priceEditor) {
    this.priceEditor = priceEditor;
    this.isMobile = this.checkIfMobile();
    this.touchStartX = 0;
    this.touchStartY = 0;
    this.touchEndX = 0;
    this.touchEndY = 0;

    this.init();
  }

  /**
   * Initializes mobile behavior.
   */
  init() {
    if (this.isMobile) {
      this.setupTouchGestures();
      this.optimizeForMobile();
      this.setupOrientationChange();
      this.setupScrollEnhancements();
    }
  }

  /**
   * Detects mobile/touch devices.
   */
  checkIfMobile() {
    return (
      window.innerWidth <= 768 ||
      "ontouchstart" in window ||
      navigator.maxTouchPoints > 0
    );
  }

  /**
   * Binds touch gestures for horizontal scrolling.
   */
  setupTouchGestures() {
    const tableSection = document.querySelector(".table-section");
    if (!tableSection) {
      return;
    }

    tableSection.addEventListener(
      "touchstart",
      (event) => {
        this.touchStartX = event.changedTouches[0].screenX;
        this.touchStartY = event.changedTouches[0].screenY;
      },
      { passive: true }
    );

    tableSection.addEventListener(
      "touchend",
      (event) => {
        this.handleSwipe(event);
      },
      { passive: true }
    );

    let lastTouchEnd = 0;
    tableSection.addEventListener(
      "touchend",
      (event) => {
        const now = Date.now();
        if (now - lastTouchEnd <= 300) {
          event.preventDefault();
        }
        lastTouchEnd = now;
      },
      { passive: false }
    );
  }

  /**
   * Handles swipe direction and threshold.
   */
  handleSwipe(event) {
    this.touchEndX = event.changedTouches[0].screenX;
    this.touchEndY = event.changedTouches[0].screenY;

    const deltaX = this.touchEndX - this.touchStartX;
    const deltaY = this.touchEndY - this.touchStartY;

    if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
      this.performHorizontalScroll(deltaX);
    }
  }

  /**
   * Performs horizontal scrolling.
   */
  performHorizontalScroll(deltaX) {
    const tableSection = document.querySelector(".table-section");
    if (!tableSection) {
      return;
    }

    const currentScroll = tableSection.scrollLeft;
    const scrollAmount = Math.abs(deltaX) * 0.8;
    const newScrollLeft =
      deltaX > 0 ? currentScroll - scrollAmount : currentScroll + scrollAmount;

    tableSection.scrollTo({
      left: Math.max(0, newScrollLeft),
      behavior: "smooth",
    });
  }

  /**
   * Applies mobile-specific optimizations.
   */
  optimizeForMobile() {
    this.addMobileClasses();
    this.optimizeTableScrolling();
    this.enhanceTouchTargets();
  }

  /**
   * Adds CSS classes for device sizes.
   */
  addMobileClasses() {
    document.body.classList.add("mobile-device");

    if (window.innerWidth <= 480) {
      document.body.classList.add("small-mobile");
    }

    if (window.innerWidth <= 360) {
      document.body.classList.add("tiny-mobile");
    }
  }

  /**
   * Adds scroll indicator and scroll behavior.
   */
  optimizeTableScrolling() {
    const tableSection = document.querySelector(".table-section");
    if (!tableSection) {
      return;
    }

    this.addScrollIndicator();

    let scrollTimeout;
    tableSection.addEventListener(
      "scroll",
      () => {
        this.updateScrollIndicator();

        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
          this.hideScrollIndicatorTemporarily();
        }, 1000);
      },
      { passive: true }
    );
  }

  /**
   * Adds horizontal scroll helper text.
   */
  addScrollIndicator() {
    const tableSection = document.querySelector(".table-section");
    if (!tableSection || tableSection.querySelector(".scroll-indicator")) {
      return;
    }

    const indicator = document.createElement("div");
    indicator.className = "scroll-indicator";
    indicator.textContent = this.priceEditor.getText(
      "mobile.scrollIndicator",
      "Scroll horizontally"
    );
    indicator.style.cssText = `
      position: absolute;
      bottom: 5px;
      right: 10px;
      font-size: 11px;
      color: #999;
      background: rgba(255, 255, 255, 0.9);
      padding: 2px 6px;
      border-radius: 3px;
      pointer-events: none;
      z-index: 10;
      transition: opacity 0.3s ease;
    `;

    tableSection.style.position = "relative";
    tableSection.appendChild(indicator);
  }

  /**
   * Updates indicator opacity based on scroll position.
   */
  updateScrollIndicator() {
    const tableSection = document.querySelector(".table-section");
    const indicator = tableSection?.querySelector(".scroll-indicator");
    if (!tableSection || !indicator) {
      return;
    }

    const maxScroll = tableSection.scrollWidth - tableSection.clientWidth;
    const currentScroll = tableSection.scrollLeft;

    indicator.style.opacity =
      maxScroll > 0 && currentScroll > 0 && currentScroll < maxScroll
        ? "1"
        : "0.3";
  }

  /**
   * Fades the indicator after inactivity.
   */
  hideScrollIndicatorTemporarily() {
    const indicator = document.querySelector(".scroll-indicator");
    if (indicator) {
      indicator.style.opacity = "0.3";
    }
  }

  /**
   * Enlarges touch targets for better usability.
   */
  enhanceTouchTargets() {
    document.querySelectorAll(".editable-text").forEach((element) => {
      element.style.minHeight = "44px";
      element.style.display = "flex";
      element.style.alignItems = "center";
      element.style.padding = "8px 12px";
    });

    document.querySelectorAll(".btn, .column-filter-btn").forEach((button) => {
      button.style.minHeight = "44px";
      button.style.minWidth = "44px";
    });
  }

  /**
   * Reacts to orientation and resize changes.
   */
  setupOrientationChange() {
    window.addEventListener("orientationchange", () => {
      setTimeout(() => {
        this.handleOrientationChange();
      }, 100);
    });

    window.addEventListener("resize", () => {
      this.handleResize();
    });
  }

  /**
   * Handles orientation changes.
   */
  handleOrientationChange() {
    const tableSection = document.querySelector(".table-section");
    if (tableSection && this.priceEditor.table) {
      this.priceEditor.table.columns.adjust();
    }

    this.addMobileClasses();

    if (window.innerWidth > window.innerHeight) {
      this.priceEditor.uiModule.showNotification(
        this.priceEditor.getText(
          "mobile.rotateToPortrait",
          "Rotate the device to portrait mode for a better view"
        ),
        "info"
      );
    }
  }

  /**
   * Handles window resize.
   */
  handleResize() {
    this.isMobile = this.checkIfMobile();

    if (this.isMobile) {
      this.optimizeForMobile();
    }
  }

  /**
   * Enables smooth scrolling and keyboard helpers.
   */
  setupScrollEnhancements() {
    const tableSection = document.querySelector(".table-section");
    if (tableSection) {
      tableSection.style.scrollBehavior = "smooth";
      tableSection.style.webkitOverflowScrolling = "touch";
    }

    this.setupKeyboardNavigation();
  }

  /**
   * Adds keyboard-based horizontal scrolling.
   */
  setupKeyboardNavigation() {
    document.addEventListener("keydown", (event) => {
      const tableSection = document.querySelector(".table-section");
      if (!tableSection) {
        return;
      }

      switch (event.key) {
        case "ArrowLeft":
          event.preventDefault();
          tableSection.scrollLeft -= 100;
          break;
        case "ArrowRight":
          event.preventDefault();
          tableSection.scrollLeft += 100;
          break;
        case "Home":
          event.preventDefault();
          tableSection.scrollLeft = 0;
          break;
        case "End":
          event.preventDefault();
          tableSection.scrollLeft = tableSection.scrollWidth;
          break;
        default:
          break;
      }
    });
  }

  /**
   * Forces a recalculation of mobile classes and layout.
   */
  forceAdaptation() {
    this.addMobileClasses();
    this.optimizeForMobile();
    this.handleOrientationChange();
  }

  /**
   * Returns current mobile state diagnostics.
   */
  getMobileInfo() {
    return {
      isMobile: this.isMobile,
      screenWidth: window.innerWidth,
      screenHeight: window.innerHeight,
      orientation:
        window.innerWidth > window.innerHeight ? "landscape" : "portrait",
    };
  }
}

window.PriceEditorMobileModule = PriceEditorMobileModule;
