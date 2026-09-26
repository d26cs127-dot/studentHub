// js/script.js
document.addEventListener("DOMContentLoaded", () => {
  // -----------------------------
  // Theme toggle with localStorage
  // -----------------------------
  const themeButton = document.querySelector("[data-theme-toggle]");
  let savedTheme = null;
  try { savedTheme = localStorage.getItem("studenthub-theme"); } catch (_) {}
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

  function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    document.documentElement.classList.toggle("dark", theme === "dark");

    if (themeButton) {
      themeButton.setAttribute(
        "aria-label",
        `Switch to ${theme === "dark" ? "light" : "dark"} theme`
      );
      themeButton.setAttribute("aria-pressed", String(theme === "dark"));
      const themeIcon = themeButton.querySelector(".theme-icon");
      const themeLabel = themeButton.querySelector(".theme-label");
      if (themeIcon) themeIcon.textContent = theme === "dark" ? "☀️" : "🌙";
      if (themeLabel) themeLabel.textContent = theme === "dark" ? "Light mode" : "Dark mode";
    }
  }

  applyTheme(savedTheme || (prefersDark ? "dark" : "light"));

  themeButton?.addEventListener("click", () => {
    const nextTheme =
      document.documentElement.dataset.theme === "dark" ? "light" : "dark";

    applyTheme(nextTheme);
    try { localStorage.setItem("studenthub-theme", nextTheme); } catch (_) {}
  });

  // -----------------------------
  // Hamburger navigation menu
  // -----------------------------
  const menuButton = document.querySelector("[data-menu-toggle]");
  const menu = document.querySelector("[data-mobile-menu]");

  function setMenuOpen(open) {
    if (!menuButton || !menu) return;

    menuButton.setAttribute("aria-expanded", String(open));
    menuButton.setAttribute("aria-label", open ? "Close navigation menu" : "Open navigation menu");
    menu.hidden = !open;
  }

  if (menuButton && menu) {
    const mobileMenuQuery = window.matchMedia("(max-width: 767px)");
    setMenuOpen(!mobileMenuQuery.matches);
    mobileMenuQuery.addEventListener("change", (event) => setMenuOpen(!event.matches));

    menuButton.addEventListener("click", () => {
      const isOpen = menuButton.getAttribute("aria-expanded") === "true";
      setMenuOpen(!isOpen);
    });

    menu.addEventListener("click", (event) => {
      if (event.target.closest("a")) setMenuOpen(false);
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && mobileMenuQuery.matches) {
        setMenuOpen(false);
        menuButton.focus();
      }
    });
  }

  // -----------------------------
  // FAQ accordion
  // -----------------------------
  document.querySelectorAll("[data-faq-question]").forEach((button) => {
    const answerId = button.getAttribute("aria-controls");
    const answer = answerId && document.getElementById(answerId);

    if (!answer) return;

    const initiallyOpen = button.getAttribute("aria-expanded") === "true";
    answer.hidden = !initiallyOpen;

    button.addEventListener("click", () => {
      const isOpen = button.getAttribute("aria-expanded") === "true";
      button.setAttribute("aria-expanded", String(!isOpen));
      answer.hidden = isOpen;
    });
  });

  // -----------------------------
  // Modal dialog
  // -----------------------------
  const modal = document.querySelector("[data-modal]");
  const modalOpenButtons = document.querySelectorAll("[data-modal-open]");
  const modalCloseButtons = document.querySelectorAll("[data-modal-close]");
  let previouslyFocusedElement = null;

  function openModal() {
    if (!modal) return;

    previouslyFocusedElement = document.activeElement;
    modal.hidden = false;
    modal.setAttribute("aria-hidden", "false");

    const firstControl = modal.querySelector(
      "button, a[href], input, select, textarea, [tabindex]:not([tabindex='-1'])"
    );
    firstControl?.focus();
  }

  function closeModal() {
    if (!modal) return;

    modal.hidden = true;
    modal.setAttribute("aria-hidden", "true");
    previouslyFocusedElement?.focus();
  }

  modalOpenButtons.forEach((button) =>
    button.addEventListener("click", openModal)
  );

  modalCloseButtons.forEach((button) =>
    button.addEventListener("click", closeModal)
  );

  modal?.addEventListener("click", (event) => {
    if (event.target === modal) closeModal();
  });

  document.addEventListener("keydown", (event) => {
    if (!modal || modal.hidden) return;

    if (event.key === "Escape") {
      closeModal();
    }

    // Keep keyboard focus inside the open dialog.
    if (event.key === "Tab") {
      const focusable = modal.querySelectorAll(
        "button:not([disabled]), a[href], input:not([disabled]), " +
          "select:not([disabled]), textarea:not([disabled]), " +
          "[tabindex]:not([tabindex='-1'])"
      );

      if (!focusable.length) return;

      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });

  if (modal) {
    modal.hidden = true;
    modal.setAttribute("aria-hidden", "true");
  }

  // -----------------------------
  // Slider / carousel
  // -----------------------------
  document.querySelectorAll("[data-slider]").forEach((slider) => {
    const slides = Array.from(slider.querySelectorAll("[data-slide]"));
    const previousButton = slider.querySelector("[data-slide-previous]");
    const nextButton = slider.querySelector("[data-slide-next]");
    let currentIndex = slides.findIndex(
      (slide) => slide.getAttribute("aria-hidden") !== "true"
    );

    if (!slides.length) return;
    if (currentIndex < 0) currentIndex = 0;

    function showSlide(index) {
      currentIndex = (index + slides.length) % slides.length;

      slides.forEach((slide, slideIndex) => {
        const active = slideIndex === currentIndex;
        slide.hidden = !active;
        slide.setAttribute("aria-hidden", String(!active));
      });
    }

    previousButton?.addEventListener("click", () =>
      showSlide(currentIndex - 1)
    );
    nextButton?.addEventListener("click", () =>
      showSlide(currentIndex + 1)
    );

    showSlide(currentIndex);
  });

  // -----------------------------
  // Dismissible notification banner
  // -----------------------------
  document.querySelectorAll("[data-notification]").forEach((banner) => {
    const closeButton = banner.querySelector("[data-notification-close]");

    closeButton?.addEventListener("click", () => {
      banner.hidden = true;
    });
  });
});