/**
 * Модуль горизонтального скролла для таблицы
 * Управляет отдельным горизонтальным скроллбаром, который всегда виден над технической строкой
 */

class PriceEditorHorizontalScrollModule {
  constructor(priceEditor) {
    this.priceEditor = priceEditor;
    this.horizontalScrollBar = null;
    this.scrollContent = null;
    this.scrollSpacer = null;
    this.isEnabled = false;

    this.init();
  }

  /**
   * Инициализация модуля
   */
  init() {
    this.horizontalScrollBar = $("#horizontal-scroll-bar");
    this.scrollContent = this.horizontalScrollBar.find(".scroll-content");
    this.scrollSpacer = this.horizontalScrollBar.find(".scroll-spacer");

    if (this.horizontalScrollBar.length) {
      this.bindEvents();
      this.updateScrollBar();
    }
  }

  /**
   * Привязка событий
   */
  bindEvents() {
    // Синхронизация прокрутки между основной таблицей и горизонтальным скроллбаром
    this.scrollContent.on("scroll", () => {
      this.syncScroll();
    });

    // Обновление скроллбара при изменении размера окна
    $(window).on("resize", () => {
      this.debounce(this.updateScrollBar.bind(this), 250)();
    });

    // Обновление после загрузки данных в таблицу
    $(document).on("draw.dt", () => {
      this.updateScrollBar();
    });

    // Обновление при изменении колонок
    $(document).on("column-sizing.dt", () => {
      this.updateScrollBar();
    });
  }

  /**
   * Синхронизация прокрутки между элементами
   */
  syncScroll() {
    const scrollLeft = this.scrollContent.scrollLeft();
    const tableWrapper = $(".dataTables_wrapper");

    if (tableWrapper.length) {
      tableWrapper.scrollLeft(scrollLeft);
    }
  }

  /**
   * Обновление горизонтального скроллбара
   */
  updateScrollBar() {
    const tableWrapper = $(".dataTables_wrapper");
    const table = $("#products-table");

    if (!tableWrapper.length || !table.length) {
      this.hideScrollBar();
      return;
    }

    // Получаем размеры таблицы и контейнера
    const tableWidth = table.outerWidth();
    const containerWidth = tableWrapper.outerWidth();

    // Если таблица шире контейнера, показываем скроллбар
    if (tableWidth > containerWidth) {
      this.showScrollBar(tableWidth);
    } else {
      this.hideScrollBar();
    }
  }

  /**
   * Показать горизонтальный скроллбар
   */
  showScrollBar(tableWidth) {
    if (!this.isEnabled) {
      this.isEnabled = true;
      this.horizontalScrollBar.addClass("visible");
    }

    // Устанавливаем ширину spacera равной ширине таблицы
    this.scrollSpacer.css("width", tableWidth + "px");

    // Синхронизируем прокрутку с основной таблицей
    this.syncScrollPosition();
  }

  /**
   * Скрыть горизонтальный скроллбар
   */
  hideScrollBar() {
    if (this.isEnabled) {
      this.isEnabled = false;
      this.horizontalScrollBar.removeClass("visible");
    }
  }

  /**
   * Синхронизация позиции прокрутки
   */
  syncScrollPosition() {
    const tableWrapper = $(".dataTables_wrapper");
    const scrollLeft = tableWrapper.scrollLeft();

    this.scrollContent.scrollLeft(scrollLeft);
  }

  /**
   * Дебаунс функция для оптимизации
   */
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  /**
   * Принудительное обновление скроллбара
   */
  refresh() {
    this.updateScrollBar();
  }

  /**
   * Уничтожение модуля
   */
  destroy() {
    // Отвязываем события
    this.scrollContent.off("scroll");
    $(window).off("resize");
    $(document).off("draw.dt");
    $(document).off("column-sizing.dt");

    this.hideScrollBar();
  }
}

// Экспортируем класс для использования в других модулях
if (typeof module !== "undefined" && module.exports) {
  module.exports = PriceEditorHorizontalScrollModule;
}
