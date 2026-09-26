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

  // -----------------------------
  // Registration form validation
  // -----------------------------
  const registrationForm = document.querySelector("[data-registration-form]");
  if (registrationForm) {
    const field = (id) => registrationForm.querySelector(`#${id}`);
    const nameInput = field("full-name");
    const emailInput = field("email");
    const mobileInput = field("mobile");
    const courseInput = field("course");
    const yearInput = field("year");
    const passwordInput = field("password");
    const confirmInput = field("confirm-password");
    const termsInput = field("terms");
    const successMessage = registrationForm.querySelector("[data-form-success]");
    const strength = registrationForm.querySelector("#password-strength");
    const genderInputs = Array.from(registrationForm.querySelectorAll('input[name="gender"]'));

    const patterns = {
      name: /^[\p{L}]+(?:[ '-][\p{L}]+)*$/u,
      email: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
      mobile: /^(?:\+?91)?[6-9]\d{9}$/,
      password: /^(?=.{8,64}$)(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9\s])\S+$/
    };

    function inputErrorTarget(control) {
      if (control.type === "radio") return control.closest("fieldset");
      if (control.type === "checkbox") return control.closest(".form-field");
      return control.parentElement;
    }

    function errorNode(control) {
      const id = `${control.id || "gender"}-error`;
      let node = registrationForm.querySelector(`#${id}`);
      if (!node) {
        node = document.createElement("span");
        node.id = id;
        node.className = "field-error";
        node.setAttribute("role", "alert");
        inputErrorTarget(control).append(node);
      }
      return node;
    }

    function connectDescription(control, node) {
      const tokens = (control.getAttribute("aria-describedby") || "").split(/\s+/).filter(Boolean);
      if (!tokens.includes(node.id)) tokens.push(node.id);
      control.setAttribute("aria-describedby", tokens.join(" "));
    }

    function setError(control, message) {
      const node = errorNode(control);
      node.textContent = message;
      node.hidden = !message;
      control.setAttribute("aria-invalid", String(Boolean(message)));
      connectDescription(control, node);
    }

    function validate(control) {
      if (!control) return true;
      let message = "";
      const value = control.value.trim();

      if (control === nameInput && !patterns.name.test(value)) message = "Enter your name using letters, spaces, apostrophes, or hyphens.";
      else if (control === emailInput && (!patterns.email.test(value) || !control.validity.valid)) message = "Enter a valid email address, such as name@example.edu.";
      else if (control === mobileInput && !patterns.mobile.test(value.replace(/[\s()-]/g, ""))) message = "Enter a valid 10-digit mobile number, optionally with the +91 country code.";
      else if (control === courseInput && !value) message = "Choose your course.";
      else if (control === yearInput && !value) message = "Choose your year of study.";
      else if (control === passwordInput && !patterns.password.test(control.value)) message = "Use 8–64 characters with a letter, a number, and a symbol; spaces are not allowed.";
      else if (control === confirmInput && control.value !== passwordInput.value) message = "Passwords do not match.";
      else if (control === termsInput && !control.checked) message = "You must agree to the terms to continue.";

      setError(control, message);
      return !message;
    }

    function updateStrength() {
      if (!strength || !passwordInput) return;
      const value = passwordInput.value;
      const checks = [value.length >= 8, value.length >= 12, /[A-Z]/.test(value) && /[a-z]/.test(value), /\d/.test(value), /[^A-Za-z0-9\s]/.test(value)];
      const score = checks.filter(Boolean).length;
      const level = !value ? "empty" : score <= 2 ? "weak" : score <= 3 ? "fair" : "strong";
      strength.dataset.strength = level;
      strength.querySelector(".strength-label").textContent = `Password strength: ${level === "empty" ? "not entered" : level}`;
    }

    [nameInput, emailInput, mobileInput, courseInput, yearInput, passwordInput, confirmInput, termsInput].forEach((control) => {
      if (!control) return;
      const eventName = control.type === "checkbox" || control.tagName === "SELECT" ? "change" : "input";
      control.addEventListener(eventName, () => {
        validate(control);
        if (control === passwordInput) {
          updateStrength();
          if (confirmInput.value) validate(confirmInput);
        }
      });
      control.addEventListener("blur", () => validate(control));
    });

    genderInputs.forEach((radio) => radio.addEventListener("change", () => {
      const error = registrationForm.querySelector("#gender-error");
      if (error) { error.textContent = ""; error.hidden = true; }
      genderInputs.forEach((item) => item.removeAttribute("aria-invalid"));
    }));

    registrationForm.addEventListener("submit", (event) => {
      event.preventDefault();
      if (successMessage) successMessage.hidden = true;
      const controls = [nameInput, emailInput, mobileInput, courseInput, yearInput, passwordInput, confirmInput, termsInput].filter(Boolean);
      const valid = controls.map(validate).every(Boolean);
      const genderSelected = genderInputs.some((radio) => radio.checked);
      if (!genderSelected) {
        const firstRadio = genderInputs[0];
        if (firstRadio) {
          firstRadio.setAttribute("aria-invalid", "true");
          const node = errorNode(firstRadio);
          node.textContent = "Select a gender option, or choose ‘Prefer not to say’.";
          node.hidden = false;
          genderInputs.forEach((radio) => connectDescription(radio, node));
        }
      }
      if (!valid || !genderSelected) {
        const firstInvalid = registrationForm.querySelector('[aria-invalid="true"]');
        firstInvalid?.focus();
        return;
      }
      if (successMessage) {
        successMessage.textContent = "All fields are valid. Thank you for registering!";
        successMessage.hidden = false;
        successMessage.focus();
      }
    });

    updateStrength();
  }

});