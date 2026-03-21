document.addEventListener("DOMContentLoaded", () => {
  // ===== SALUTATION DYNAMIQUE =====
  const greetingEl = document.querySelector(".section-greeting");
  if (greetingEl) {
    const hours = new Date().getHours();
    const greeting =
      hours < 12 ? "Bonjour" : hours < 18 ? "Bonne après-midi" : "Bonsoir";
    greetingEl.textContent = greeting;
  }

  // ===== NAVIGATION SIDEBAR =====
  const menuLinks = document.querySelectorAll(".profile-menu .menu-links a");
  const sections = document.querySelectorAll(
    ".profile-content > .profile-section",
  );

  function showSection(targetId) {
    sections.forEach((s) => {
      s.classList.remove("active");
      s.style.display = "none";
    });
    const target = document.getElementById(targetId);
    if (target) {
      target.style.display = "block";
      target.offsetHeight;
      target.classList.add("active");
    }
  }

  function setActiveLink(link) {
    menuLinks.forEach((l) => l.classList.remove("active"));
    link.classList.add("active");
  }

  const defaultSection = document.querySelector(".profile-section.active");
  if (defaultSection) {
    sections.forEach((s) => {
      if (s !== defaultSection) s.style.display = "none";
    });
  }

  menuLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const targetId = link.getAttribute("data-section");
      if (!targetId) return;
      showSection(targetId);
      setActiveLink(link);
    });
  });

  // ===== TOGGLE VUE / FORMULAIRE ÉDITION =====
  const btnEditToggle = document.getElementById("btn-edit-toggle");
  const btnCancelEdit = document.getElementById("btn-cancel-edit");
  const btnCancelEdit2 = document.getElementById("btn-cancel-edit-2");
  const profileView = document.getElementById("profile-view");
  const profileEdit = document.getElementById("profile-edit");

  function showEdit() {
    if (!profileView || !profileEdit) return;
    profileView.style.display = "none";
    profileEdit.style.display = "block";
    btnEditToggle.innerHTML = '<i class="fas fa-eye"></i> Voir';
    profileEdit.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  function showView() {
    if (!profileView || !profileEdit) return;
    profileEdit.style.display = "none";
    profileView.style.display = "block";
    btnEditToggle.innerHTML = '<i class="fas fa-pen"></i> Modifier';
  }

  btnEditToggle?.addEventListener("click", showEdit);
  btnCancelEdit?.addEventListener("click", showView);
  btnCancelEdit2?.addEventListener("click", showView);

  // ===== PREVIEW AVATAR =====
  document
    .getElementById("profile_picture")
    ?.addEventListener("change", (e) => {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > 5 * 1024 * 1024) {
        alert("L'image ne doit pas dépasser 5 Mo.");
        e.target.value = "";
        return;
      }
      const reader = new FileReader();
      reader.onload = (ev) => {
        document.getElementById("avatar-preview").src = ev.target.result;
      };
      reader.readAsDataURL(file);
    });

  // ===== SOUMISSION AJAX PROFIL =====
  document
    .getElementById("profile-edit-form")
    ?.addEventListener("submit", async (e) => {
      e.preventDefault();

      const btn = document.getElementById("btn-save");
      const msgBox = document.getElementById("edit-message");
      const btnText = btn.querySelector(".btn-text");
      const spinner = btn.querySelector(".btn-spinner");

      document
        .querySelectorAll(".edit-error")
        .forEach((el) => (el.textContent = ""));
      msgBox.style.display = "none";
      msgBox.className = "edit-message";

      btn.disabled = true;
      btnText.style.display = "none";
      spinner.style.display = "flex";

      try {
        const res = await fetch("./profile/update", {
          method: "POST",
          body: new FormData(e.target),
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const data = await res.json();

        if (data.success) {
          msgBox.textContent = data.message ?? "Profil mis à jour avec succès.";
          msgBox.classList.add("success");
          msgBox.style.display = "block";

          if (data.user) {
            const fieldMap = {
              username: '[data-field="username"]',
              fullname: '[data-field="fullname"]',
              email: '[data-field="email"]',
              phone_number: '[data-field="phone"]',
              country: '[data-field="country"]',
              bio: '[data-field="bio"]',
              birth_date: '[data-field="birth_date"]',
              english_level: '[data-field="english_level"]',
              native_language: '[data-field="native_language"]',
            };
            Object.entries(fieldMap).forEach(([key, selector]) => {
              const el = document.querySelector(selector);
              if (el && data.user[key] !== undefined)
                el.textContent = data.user[key] || "Non renseigné";
            });

            if (data.user.profile_picture) {
              const newSrc =
                "../public/uploads/profiles/" +
                data.user.profile_picture +
                "?t=" +
                Date.now();
              document
                .querySelectorAll(".avatar, .edit-avatar-preview")
                .forEach((img) => {
                  img.src = newSrc;
                });
            }

            if (data.user.english_level) {
              const levelLabels = {
                beginner: "Débutant",
                intermediate: "Intermédiaire",
                advanced: "Avancé",
              };
              const levelBadge = document.querySelector(".level-badge");
              if (levelBadge)
                levelBadge.innerHTML = `<i class="fas fa-graduation-cap"></i> ${levelLabels[data.user.english_level] ?? data.user.english_level}`;
            }

            const sidebarUsername = document.querySelector(".menu-header h2");
            if (sidebarUsername && data.user.username)
              sidebarUsername.textContent = data.user.username;
          }

          if (data.completion) {
            const fill = document.getElementById("completion-fill");
            const percent = document.getElementById("completion-percent");
            const completion = document.querySelector(".profile-completion");

            if (fill) {
              fill.style.width = data.completion.percentage + "%";
              fill.className =
                "profile-completion__fill profile-completion__fill--" +
                data.completion.color;
            }
            if (percent) percent.textContent = data.completion.percentage + "%";

            if (data.completion.percentage === 100 && completion) {
              setTimeout(() => {
                completion.classList.add("is-complete");
                setTimeout(() => completion.remove(), 1500);
              }, 800);
            }
            if (data.completion.percentage < 70 && completion) {
              completion.classList.remove("is-complete");
              completion.style.opacity = "1";
              completion.style.maxHeight = "";
            }
          }

          setTimeout(() => showView(), 1500);
        } else {
          if (data.errors) {
            Object.entries(data.errors).forEach(([field, msg]) => {
              const el = document.getElementById("err-" + field);
              if (el) el.textContent = msg;
            });
          }
          msgBox.textContent = data.message ?? "Une erreur est survenue.";
          msgBox.classList.add("error");
          msgBox.style.display = "block";
        }
      } catch {
        msgBox.textContent = "Erreur réseau. Veuillez réessayer.";
        msgBox.classList.add("error");
        msgBox.style.display = "block";
      } finally {
        btn.disabled = false;
        btnText.style.display = "flex";
        spinner.style.display = "none";
      }
    });

  // ===== LANG DROPDOWN =====
  const langDropdown = document.getElementById("lang-dropdown");
  const langTrigger = document.getElementById("lang-trigger");
  const langSearchInput = document.getElementById("lang-search-input");
  const langSearchClear = document.getElementById("lang-search-clear");
  const langResults = document.getElementById("lang-results");
  const langNoResults = document.getElementById("lang-no-results");
  const langSelectedText = document.getElementById("lang-selected-text");
  const langHiddenInput = document.getElementById("native_language_input");

  if (langDropdown) {
    langTrigger.addEventListener("click", () => {
      const isOpen = langDropdown.classList.toggle("is-open");
      if (isOpen) {
        langSearchInput.focus();
        scrollToSelected();
      } else {
        langSearchInput.value = "";
        resetSearch();
      }
    });

    document.addEventListener("click", (e) => {
      if (!langDropdown.contains(e.target)) {
        langDropdown.classList.remove("is-open");
        langSearchInput.value = "";
        resetSearch();
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && langDropdown.classList.contains("is-open")) {
        langDropdown.classList.remove("is-open");
        langSearchInput.value = "";
        resetSearch();
      }
    });

    langSearchInput.addEventListener("input", () => {
      const query = langSearchInput.value.trim().toLowerCase();
      langSearchClear.style.display = query ? "flex" : "none";
      filterLanguages(query);
    });

    langSearchClear.addEventListener("click", () => {
      langSearchInput.value = "";
      langSearchClear.style.display = "none";
      resetSearch();
      langSearchInput.focus();
    });

    langResults.addEventListener("click", (e) => {
      const option = e.target.closest(".lang-option");
      if (!option) return;
      langHiddenInput.value = option.dataset.value;
      langSelectedText.textContent = option.dataset.label;
      langSelectedText.classList.remove("is-placeholder");
      document.querySelectorAll(".lang-option").forEach((opt) => {
        opt.classList.remove("is-selected");
        const check = opt.querySelector(".lang-option__check");
        if (check) check.remove();
      });
      option.classList.add("is-selected");
      const check = document.createElement("i");
      check.className = "fas fa-check lang-option__check";
      option.appendChild(check);
      langDropdown.classList.remove("is-open");
      langSearchInput.value = "";
      langSearchClear.style.display = "none";
      resetSearch();
    });

    function filterLanguages(query) {
      let total = 0;
      langResults.querySelectorAll(".lang-group").forEach((group) => {
        let visible = 0;
        group.querySelectorAll(".lang-option").forEach((opt) => {
          const label = opt.dataset.label.toLowerCase();
          const match = !query || label.includes(query);
          opt.style.display = match ? "flex" : "none";
          if (match) {
            visible++;
            total++;
            const labelEl = opt.querySelector(".lang-option__label");
            if (query) {
              const idx = label.indexOf(query),
                orig = opt.dataset.label;
              labelEl.innerHTML =
                orig.slice(0, idx) +
                "<mark>" +
                orig.slice(idx, idx + query.length) +
                "</mark>" +
                orig.slice(idx + query.length);
            } else {
              labelEl.textContent = opt.dataset.label;
            }
          }
        });
        group.style.display = visible > 0 ? "block" : "none";
      });
      langNoResults.style.display = total === 0 ? "flex" : "none";
    }

    function resetSearch() {
      document
        .querySelectorAll(".lang-group")
        .forEach((g) => (g.style.display = "block"));
      document.querySelectorAll(".lang-option").forEach((opt) => {
        opt.style.display = "flex";
        opt.querySelector(".lang-option__label").textContent =
          opt.dataset.label;
      });
      langNoResults.style.display = "none";
    }

    function scrollToSelected() {
      const sel = langResults.querySelector(".lang-option.is-selected");
      if (sel)
        setTimeout(
          () => sel.scrollIntoView({ block: "center", behavior: "smooth" }),
          150,
        );
    }
  }

  // ===== SUPPRESSION SESSION =====
  document
    .querySelector(".login-history")
    ?.addEventListener("click", async (e) => {
      const btn = e.target.closest(".login-history__delete");
      if (!btn) return;
      const id = btn.dataset.id;
      const item = btn.closest(".login-history__item");
      item.style.transition = "opacity 0.25s ease, transform 0.25s ease";
      item.style.opacity = "0.5";
      item.style.pointerEvents = "none";
      btn.disabled = true;
      try {
        const res = await fetch("./profile/delete-login", {
          method: "POST",
          body: new URLSearchParams({ id }),
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/x-www-form-urlencoded",
          },
        });
        const data = await res.json();
        if (data.success) {
          item.style.opacity = "0";
          item.style.transform = "translateX(20px)";
          setTimeout(() => item.remove(), 250);
        } else {
          item.style.opacity = "1";
          item.style.pointerEvents = "auto";
          btn.disabled = false;
        }
      } catch {
        item.style.opacity = "1";
        item.style.pointerEvents = "auto";
        btn.disabled = false;
      }
    });

  // ===== VOIR TOUTES LES CONNEXIONS =====
  document
    .getElementById("btn-show-all-logins")
    ?.addEventListener("click", async function () {
      const list = document.getElementById("login-history-list");
      const isOpen = this.classList.contains("is-open");

      if (isOpen) {
        list.querySelectorAll(".login-history__item").forEach((item, i) => {
          if (i >= 4) {
            item.style.transition = "opacity 0.2s ease, transform 0.2s ease";
            item.style.opacity = "0";
            item.style.transform = "translateY(-6px)";
            setTimeout(() => item.remove(), 200);
          }
        });
        this.classList.remove("is-open");
        this.innerHTML = `<i class="fas fa-clock-rotate-left"></i> Voir toutes les connexions <i class="fas fa-chevron-down login-history__more-arrow"></i>`;
        return;
      }

      this.disabled = true;
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';

      try {
        const res = await fetch("./profile/login-history", {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const data = await res.json();
        if (data.success && data.logins) {
          data.logins.slice(4).forEach((login, i) => {
            const li = document.createElement("li");
            li.className = "login-history__item";
            li.style.opacity = "0";
            li.style.transform = "translateY(8px)";
            li.style.transition = `opacity 0.25s ease ${i * 0.04}s, transform 0.25s ease ${i * 0.04}s`;
            li.innerHTML = `
                      <div class="login-history__icon"><i class="fas fa-${login.device === "mobile" ? "mobile-screen" : login.device === "tablette" ? "tablet-screen-button" : "display"}"></i></div>
                      <div class="login-history__info">
                          <span class="login-history__device">${login.browser} sur ${login.os}</span>
                          <span class="login-history__meta"><i class="fas fa-location-dot"></i> ${login.ip_address} <span class="sep">·</span> <i class="fas fa-clock"></i> ${login.created_at}</span>
                      </div>
                      <button class="login-history__delete" data-id="${login.id}" title="Supprimer"><i class="fas fa-trash-can"></i></button>
                  `;
            list.appendChild(li);
            requestAnimationFrame(() => {
              li.style.opacity = "1";
              li.style.transform = "translateY(0)";
            });
          });
          this.disabled = false;
          this.classList.add("is-open");
          this.innerHTML = `<i class="fas fa-clock-rotate-left"></i> Réduire <span class="login-history__more-count">${data.logins.length} au total</span> <i class="fas fa-chevron-down login-history__more-arrow" style="transform:rotate(180deg)"></i>`;
        }
      } catch {
        this.disabled = false;
        this.innerHTML = `<i class="fas fa-clock-rotate-left"></i> Voir toutes les connexions <i class="fas fa-chevron-down login-history__more-arrow"></i>`;
      }
    });

  // ===== TOGGLE 2FA =====
  document
    .getElementById("toggle-2fa")
    ?.addEventListener("change", async function () {
      const enabled = this.checked;
      const feedback = document.getElementById("2fa-feedback");
      try {
        const res = await fetch("./profile/toggle-2fa", {
          method: "POST",
          body: new URLSearchParams({ enabled: enabled ? "1" : "0" }),
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/x-www-form-urlencoded",
          },
        });
        const data = await res.json();
        feedback.textContent = data.message;
        feedback.className =
          "setting-item__feedback " + (data.success ? "success" : "error");
        feedback.style.display = "block";
        if (!data.success) this.checked = !enabled;
        setTimeout(() => {
          feedback.style.display = "none";
        }, 3500);
      } catch {
        this.checked = !enabled;
        feedback.textContent = "Erreur réseau. Veuillez réessayer.";
        feedback.className = "setting-item__feedback error";
        feedback.style.display = "block";
        setTimeout(() => {
          feedback.style.display = "none";
        }, 3500);
      }
    });

  // ===== TOTP =====
  const totpFeedback = document.getElementById("totp-feedback");
  const toggleTotp = document.getElementById("toggle-totp");
  const modalSetup = document.getElementById("modal-totp-setup");
  const modalDisable = document.getElementById("modal-totp-disable");
  let totpActionInProgress = false;

  function openModal(modal) {
    modal.style.display = "flex";
    modal.classList.remove("is-closing");
    document.body.style.overflow = "hidden";
  }

  function closeModal(modal) {
    modal.classList.add("is-closing");
    setTimeout(() => {
      modal.style.display = "none";
      modal.classList.remove("is-closing");
      document.body.style.overflow = "";
    }, 250);
  }

  function showTotpFeedback(message, type = "success") {
    if (!totpFeedback) return;
    totpFeedback.textContent = message;
    totpFeedback.className = `setting-item__feedback ${type}`;
    totpFeedback.style.display = "block";
    setTimeout(() => {
      totpFeedback.style.display = "none";
    }, 3500);
  }

  // ===== OTP CASES HELPER =====
  function initOtpInputs(selector, hiddenId, getBtnFn, errorId) {
    const inputs = document.querySelectorAll(selector);
    const hidden = document.getElementById(hiddenId);
    const error = document.getElementById(errorId);

    function getBtn() {
      return typeof getBtnFn === "function"
        ? getBtnFn()
        : document.getElementById(getBtnFn);
    }

    function enable(i) {
      inputs[i].removeAttribute("disabled");
      inputs[i].style.opacity = "1";
      inputs[i].style.cursor = "text";
      inputs[i].focus();
    }

    function disable(i) {
      inputs[i].setAttribute("disabled", true);
      inputs[i].value = "";
      inputs[i].style.opacity = "0.4";
      inputs[i].style.cursor = "not-allowed";
      inputs[i].classList.remove("is-filled", "is-error");
    }

    function sync() {
      if (hidden) hidden.value = [...inputs].map((i) => i.value).join("");
    }

    function complete() {
      return [...inputs].every((i) => i.value !== "");
    }

    function updateBtn() {
      const btn = getBtn();
      if (!btn) return;
      btn.disabled = !complete();
      btn.style.opacity = complete() ? "1" : "0.45";
      btn.style.cursor = complete() ? "pointer" : "not-allowed";
    }

    function clearErr() {
      if (error) error.textContent = "";
      inputs.forEach((i) => i.classList.remove("is-error"));
    }

    function reset() {
      inputs.forEach((inp, i) => {
        inp.value = "";
        inp.classList.remove("is-filled", "is-error");
        if (i !== 0) disable(i);
        else {
          inp.removeAttribute("disabled");
          inp.style.opacity = "1";
          inp.style.cursor = "text";
        }
      });
      sync();
      updateBtn();
    }

    inputs.forEach((input, index) => {
      if (index !== 0) {
        input.setAttribute("disabled", true);
        input.style.opacity = "0.4";
        input.style.cursor = "not-allowed";
      }

      input.addEventListener("input", (e) => {
        const val = e.target.value.replace(/\D/g, "");
        input.value = val ? val[val.length - 1] : "";
        if (input.value) {
          input.classList.add("is-filled");
          if (index < inputs.length - 1) enable(index + 1);
        } else {
          input.classList.remove("is-filled");
        }
        sync();
        updateBtn();
        clearErr();
      });

      input.addEventListener("keydown", (e) => {
        if (e.key === "Backspace") {
          if (input.value) {
            input.value = "";
            input.classList.remove("is-filled");
            sync();
            updateBtn();
          } else if (index > 0) {
            disable(index);
            enable(index - 1);
            inputs[index - 1].value = "";
            inputs[index - 1].classList.remove("is-filled");
            sync();
            updateBtn();
          }
        }
      });

      input.addEventListener("paste", (e) => {
        e.preventDefault();
        const pasted = e.clipboardData
          .getData("text")
          .replace(/\D/g, "")
          .slice(0, 6);
        if (!pasted) return;
        inputs.forEach((inp, i) => {
          if (i !== 0) disable(i);
          inp.value = "";
          inp.classList.remove("is-filled");
        });
        [...pasted].forEach((char, i) => {
          if (i < inputs.length) {
            if (i !== 0) enable(i);
            inputs[i].value = char;
            inputs[i].classList.add("is-filled");
          }
        });
        if (pasted.length < inputs.length) enable(pasted.length);
        else inputs[inputs.length - 1].focus();
        sync();
        updateBtn();
        clearErr();
      });

      input.addEventListener("focus", () => {
        if (!input.disabled) input.select();
      });
    });

    return {
      reset,
      clearErr,
      getCode: () => (hidden ? hidden.value : ""),
      showError: (msg) => {
        if (error) error.textContent = msg;
        inputs.forEach((i) => {
          if (!i.disabled) i.classList.add("is-error");
        });
        setTimeout(
          () => inputs.forEach((i) => i.classList.remove("is-error")),
          600,
        );
      },
      updateBtn,
    };
  }

  // ===== CLONE LES BOUTONS AVANT INIT OTP =====
  const btnActivateOld = document.getElementById("btn-totp-activate");
  let btnActivate = btnActivateOld;
  if (btnActivateOld) {
    btnActivate = btnActivateOld.cloneNode(true);
    btnActivateOld.parentNode.replaceChild(btnActivate, btnActivateOld);
  }

  const btnDisableOld = document.getElementById("btn-totp-disable");
  let btnDisable = btnDisableOld;
  if (btnDisableOld) {
    btnDisable = btnDisableOld.cloneNode(true);
    btnDisableOld.parentNode.replaceChild(btnDisable, btnDisableOld);
  }

  // ===== INIT OTP après cloneNode =====
  const totpOtp = initOtpInputs(
    ".totp-otp-input",
    "totp-code-hidden",
    () => document.getElementById("btn-totp-activate"),
    "totp-error",
  );
  const disableOtp = initOtpInputs(
    ".totp-disable-input",
    "totp-disable-hidden",
    () => document.getElementById("btn-totp-disable"),
    "totp-disable-error",
  );

  // ===== TOGGLE TOTP =====
  toggleTotp?.addEventListener("change", async function () {
    if (totpActionInProgress) return;
    const enabled = this.checked;

    if (enabled) {
      openModal(modalSetup);

      const qrImg = document.getElementById("totp-qr-img");
      const qrLoader = document.getElementById("totp-qr-loader");

      qrImg.style.display = "none";
      qrImg.src = "";
      qrLoader.style.display = "flex";
      totpOtp.reset();

      try {
        const res = await fetch("./profile/generate-totp", {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const data = await res.json();

        if (data.success) {
          // Stocke le secret
          let secretInput = document.getElementById("totp-secret-input");
          if (!secretInput) {
            secretInput = document.createElement("input");
            secretInput.type = "hidden";
            secretInput.id = "totp-secret-input";
            modalSetup.appendChild(secretInput);
          }
          secretInput.value = data.secret;

          // data:image/svg+xml;base64 — affichage direct sans préchargement
          qrImg.src = data.qr_url;
          qrLoader.style.display = "none";
          qrImg.style.display = "block";

          // Affiche le secret formaté
          const secretEl = document.getElementById("totp-secret-text");
          if (secretEl) {
            secretEl.textContent = data.secret.match(/.{1,4}/g).join(" ");
            secretEl.dataset.raw = data.secret;
          }
        } else {
          qrLoader.style.display = "none";
          showTotpFeedback(
            data.message ?? "Erreur lors de la génération.",
            "error",
          );
          closeModal(modalSetup);
          totpActionInProgress = true;
          this.checked = false;
          setTimeout(() => {
            totpActionInProgress = false;
          }, 300);
        }
      } catch {
        qrLoader.style.display = "none";
        showTotpFeedback("Erreur réseau. Veuillez réessayer.", "error");
        closeModal(modalSetup);
        totpActionInProgress = true;
        this.checked = false;
        setTimeout(() => {
          totpActionInProgress = false;
        }, 300);
      }
    } else {
      openModal(modalDisable);
      disableOtp.reset();
    }
  });

  // ===== FERMETURE MODALS =====
  function cancelSetup() {
    closeModal(modalSetup);
    totpOtp.reset();
    totpActionInProgress = true;
    toggleTotp.checked = false;
    setTimeout(() => {
      totpActionInProgress = false;
    }, 300);
  }

  function cancelDisable() {
    closeModal(modalDisable);
    disableOtp.reset();
    totpActionInProgress = true;
    toggleTotp.checked = true;
    setTimeout(() => {
      totpActionInProgress = false;
    }, 300);
  }

  document
    .getElementById("totp-modal-close")
    ?.addEventListener("click", cancelSetup);
  document
    .getElementById("totp-cancel")
    ?.addEventListener("click", cancelSetup);
  document
    .getElementById("totp-disable-modal-close")
    ?.addEventListener("click", cancelDisable);
  document
    .getElementById("totp-disable-cancel")
    ?.addEventListener("click", cancelDisable);
  modalSetup?.addEventListener("click", (e) => {
    if (e.target === modalSetup) cancelSetup();
  });
  modalDisable?.addEventListener("click", (e) => {
    if (e.target === modalDisable) cancelDisable();
  });

  // ===== ACTIVATION TOTP =====
  btnActivate?.addEventListener("click", async function () {
    if (this.disabled) return;

    const btnText = this.querySelector(".btn-text");
    const spinner = this.querySelector(".btn-spinner");
    const code = totpOtp.getCode();
    const secret = document.getElementById("totp-secret-input")?.value ?? "";

    this.disabled = true;
    btnText.style.display = "none";
    spinner.style.display = "flex";
    totpOtp.clearErr();

    try {
      const res = await fetch("./profile/activate-totp", {
        method: "POST",
        body: new URLSearchParams({ code, secret }),
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Content-Type": "application/x-www-form-urlencoded",
        },
      });
      const data = await res.json();

      if (data.success) {
        totpActionInProgress = true;
        closeModal(modalSetup);
        toggleTotp.checked = true;
        showTotpFeedback("Google Authenticator activé avec succès.", "success");
        totpOtp.reset();
        setTimeout(() => {
          totpActionInProgress = false;
        }, 300);
      } else {
        totpOtp.showError(data.message ?? "Code incorrect.");
        totpOtp.reset();
        this.disabled = false;
        btnText.style.display = "inline-flex";
        spinner.style.display = "none";
      }
    } catch {
      totpOtp.showError("Erreur réseau. Veuillez réessayer.");
      this.disabled = false;
      btnText.style.display = "inline-flex";
      spinner.style.display = "none";
    }
  });

  // ===== DÉSACTIVATION TOTP =====
  btnDisable?.addEventListener("click", async function () {
    if (this.disabled) return;

    const btnText = this.querySelector(".btn-text");
    const spinner = this.querySelector(".btn-spinner");

    // Récupère le code depuis l'input caché
    const hidden = document.getElementById("totp-disable-hidden");
    const code = hidden ? hidden.value : disableOtp.getCode();

    if (!code || code.length !== 6) {
      disableOtp.showError("Veuillez saisir le code à 6 chiffres.");
      return;
    }

    this.disabled = true;
    btnText.style.display = "none";
    spinner.style.display = "flex";
    disableOtp.clearErr();

    try {
      const res = await fetch("./profile/disable-totp", {
        method: "POST",
        body: new URLSearchParams({ code }),
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Content-Type": "application/x-www-form-urlencoded",
        },
      });
      const data = await res.json();

      if (data.success) {
        totpActionInProgress = true;
        closeModal(modalDisable);
        toggleTotp.checked = false;
        showTotpFeedback(
          "Google Authenticator désactivé avec succès.",
          "success",
        );
        disableOtp.reset();
        setTimeout(() => {
          totpActionInProgress = false;
        }, 300);
      } else {
        disableOtp.showError(data.message ?? "Code incorrect.");
        disableOtp.reset();
        this.disabled = false;
        btnText.style.display = "inline-flex";
        spinner.style.display = "none";
        totpActionInProgress = true;
        toggleTotp.checked = true;
        setTimeout(() => {
          totpActionInProgress = false;
        }, 300);
      }
    } catch {
      disableOtp.showError("Erreur réseau. Veuillez réessayer.");
      this.disabled = false;
      btnText.style.display = "inline-flex";
      spinner.style.display = "none";
      totpActionInProgress = true;
      toggleTotp.checked = true;
      setTimeout(() => {
        totpActionInProgress = false;
      }, 300);
    }
  });

  // ===== COPIER LE SECRET =====
  document
    .getElementById("totp-copy-secret")
    ?.addEventListener("click", function () {
      const secretEl = document.getElementById("totp-secret-text");
      const secret =
        secretEl.dataset.raw ?? secretEl.textContent.replace(/\s/g, "");
      navigator.clipboard.writeText(secret).then(() => {
        this.innerHTML = '<i class="fas fa-check"></i>';
        this.style.color = "#00c48c";
        setTimeout(() => {
          this.innerHTML = '<i class="fas fa-copy"></i>';
          this.style.color = "";
        }, 2000);
      });
    });

  // ===== CHANGER MOT DE PASSE =====

  const modalPwd2fa = document.getElementById("modal-pwd-2fa");
  const modalPwdTotp = document.getElementById("modal-pwd-totp");
  const modalPwdForm = document.getElementById("modal-pwd-form");
  const modalPwdSuccess = document.getElementById("modal-pwd-success");

  let pwdHas2fa = false;
  let pwdHasTotp = false;

  function openPwdModal(modal) {
    modal.style.display = "flex";
    modal.classList.remove("is-closing");
    document.body.style.overflow = "hidden";
  }

  function closePwdModal(modal, callback) {
    modal.classList.add("is-closing");
    setTimeout(() => {
      modal.style.display = "none";
      modal.classList.remove("is-closing");
      document.body.style.overflow = "";
      if (callback) callback();
    }, 250);
  }

  function closeAllPwdModals() {
    [modalPwd2fa, modalPwdTotp, modalPwdForm, modalPwdSuccess].forEach((m) => {
      if (m) {
        m.style.display = "none";
        m.classList.remove("is-closing");
      }
    });
    document.body.style.overflow = "";
  }

  // ===== OTP CASES POUR MOT DE PASSE =====
  const pwd2faOtp = initOtpInputs(
    ".pwd-otp-input",
    "pwd-2fa-code",
    () => document.getElementById("pwd-2fa-submit"),
    "pwd-2fa-error",
  );
  const pwdTotpOtp = initOtpInputs(
    ".pwd-totp-input",
    "pwd-totp-code",
    () => document.getElementById("pwd-totp-submit"),
    "pwd-totp-error",
  );

  // ===== OUVRE LE FLOW =====
  document
    .querySelector(".btn-setting:not(.logout)")
    ?.addEventListener("click", async function () {
      // Lance le processus
      try {
        const res = await fetch("./profile/change-password-start", {
          method: "POST",
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const data = await res.json();

        if (!data.success) return;

        pwdHas2fa = data.has_2fa;
        pwdHasTotp = data.has_totp;

        if (pwdHas2fa) {
          // Réinitialise les cases
          pwd2faOtp.reset();
          openPwdModal(modalPwd2fa);
        } else if (pwdHasTotp) {
          pwdTotpOtp.reset();
          openPwdModal(modalPwdTotp);
        } else {
          openPwdModal(modalPwdForm);
        }
      } catch {
        // Si erreur réseau, ouvre directement le form
        openPwdModal(modalPwdForm);
      }
    });

  // ===== VÉRIFICATION 2FA =====
  document
    .getElementById("pwd-2fa-submit")
    ?.addEventListener("click", async function () {
      const btnText = this.querySelector(".btn-text");
      const spinner = this.querySelector(".btn-spinner");
      const code = pwd2faOtp.getCode();

      this.disabled = true;
      btnText.style.display = "none";
      spinner.style.display = "flex";
      pwd2faOtp.clearErr();

      try {
        const res = await fetch("./profile/change-password-verify", {
          method: "POST",
          body: new URLSearchParams({ step: "2fa", code }),
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/x-www-form-urlencoded",
          },
        });
        const data = await res.json();

        if (data.success) {
          closePwdModal(modalPwd2fa, () => {
            if (data.next === "totp") {
              pwdTotpOtp.reset();
              openPwdModal(modalPwdTotp);
            } else {
              openPwdModal(modalPwdForm);
            }
          });
        } else {
          pwd2faOtp.showError(data.message ?? "Code incorrect.");
          pwd2faOtp.reset();
          this.disabled = false;
          btnText.style.display = "flex";
          spinner.style.display = "none";
        }
      } catch {
        pwd2faOtp.showError("Erreur réseau. Veuillez réessayer.");
        this.disabled = false;
        btnText.style.display = "flex";
        spinner.style.display = "none";
      }
    });

  // ===== RENVOI CODE 2FA =====
  document
    .getElementById("pwd-resend-2fa")
    ?.addEventListener("click", async (e) => {
      e.preventDefault();
      const link = e.currentTarget;
      link.style.opacity = "0.5";
      link.style.pointerEvents = "none";
      link.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';

      try {
        await fetch("./profile/change-password-start", {
          method: "POST",
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        link.innerHTML = '<i class="fas fa-check"></i> Code envoyé !';
        link.style.color = "#00c48c";
        pwd2faOtp.reset();
        setTimeout(() => {
          link.innerHTML =
            '<i class="fas fa-rotate-right"></i> Renvoyer le code';
          link.style.color = "";
          link.style.opacity = "1";
          link.style.pointerEvents = "auto";
        }, 3000);
      } catch {
        link.innerHTML = '<i class="fas fa-rotate-right"></i> Renvoyer le code';
        link.style.opacity = "1";
        link.style.pointerEvents = "auto";
      }
    });

  // ===== VÉRIFICATION TOTP =====
  document
    .getElementById("pwd-totp-submit")
    ?.addEventListener("click", async function () {
      const btnText = this.querySelector(".btn-text");
      const spinner = this.querySelector(".btn-spinner");
      const code = pwdTotpOtp.getCode();

      this.disabled = true;
      btnText.style.display = "none";
      spinner.style.display = "flex";
      pwdTotpOtp.clearErr();

      try {
        const res = await fetch("./profile/change-password-verify", {
          method: "POST",
          body: new URLSearchParams({ step: "totp", code }),
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/x-www-form-urlencoded",
          },
        });
        const data = await res.json();

        if (data.success) {
          closePwdModal(modalPwdTotp, () => {
            openPwdModal(modalPwdForm);
          });
        } else {
          pwdTotpOtp.showError(data.message ?? "Code incorrect.");
          pwdTotpOtp.reset();
          this.disabled = false;
          btnText.style.display = "flex";
          spinner.style.display = "none";
        }
      } catch {
        pwdTotpOtp.showError("Erreur réseau. Veuillez réessayer.");
        this.disabled = false;
        btnText.style.display = "flex";
        spinner.style.display = "none";
      }
    });

  // ===== INDICATEUR DE FORCE MOT DE PASSE =====
  document.getElementById("pwd-new")?.addEventListener("input", function () {
    const val = this.value;
    const fill = document.getElementById("pwd-strength-fill");
    const label = document.getElementById("pwd-strength-label");

    if (!val) {
      fill.className = "pwd-strength__fill";
      fill.style.width = "0";
      label.textContent = "";
      return;
    }

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
      { cls: "weak", label: "Faible", color: "#f5365c" },
      { cls: "fair", label: "Moyen", color: "#f7b731" },
      { cls: "good", label: "Bon", color: "#1a6fb5" },
      { cls: "strong", label: "Fort", color: "#00c48c" },
    ];

    const level = levels[score - 1] ?? levels[0];
    fill.className = `pwd-strength__fill pwd-strength__fill--${level.cls}`;
    label.textContent = level.label;
    label.style.color = level.color;
  });

  // ===== TOGGLE AFFICHAGE MOT DE PASSE =====
  document.querySelectorAll(".pwd-toggle-eye").forEach((btn) => {
    btn.addEventListener("click", function () {
      const target = document.getElementById(this.dataset.target);
      if (!target) return;
      const isPassword = target.type === "password";
      target.type = isPassword ? "text" : "password";
      this.innerHTML = `<i class="fas fa-${isPassword ? "eye-slash" : "eye"}"></i>`;
    });
  });

  // ===== SOUMISSION FORMULAIRE MOT DE PASSE =====
  document
    .getElementById("pwd-form-submit")
    ?.addEventListener("click", async function () {
      const btnText = this.querySelector(".btn-text");
      const spinner = this.querySelector(".btn-spinner");

      const current = document.getElementById("pwd-current")?.value ?? "";
      const newPwd = document.getElementById("pwd-new")?.value ?? "";
      const confirm = document.getElementById("pwd-confirm")?.value ?? "";

      // Reset erreurs
      [
        "err-pwd-current",
        "err-pwd-new",
        "err-pwd-confirm",
        "pwd-form-error",
      ].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.textContent = "";
      });
      ["pwd-current", "pwd-new", "pwd-confirm"].forEach((id) => {
        document.getElementById(id)?.classList.remove("is-error");
      });

      // Validations JS
      let valid = true;

      if (!current) {
        document.getElementById("err-pwd-current").textContent =
          "Ce champ est obligatoire.";
        document.getElementById("pwd-current").classList.add("is-error");
        valid = false;
      }
      if (newPwd.length < 8) {
        document.getElementById("err-pwd-new").textContent =
          "Au moins 8 caractères.";
        document.getElementById("pwd-new").classList.add("is-error");
        valid = false;
      }
      if (newPwd !== confirm) {
        document.getElementById("err-pwd-confirm").textContent =
          "Les mots de passe ne correspondent pas.";
        document.getElementById("pwd-confirm").classList.add("is-error");
        valid = false;
      }

      if (!valid) return;

      this.disabled = true;
      btnText.style.display = "none";
      spinner.style.display = "flex";

      try {
        const logoutAll =
          document.getElementById("pwd-logout-all-checkbox")?.checked ?? false;

        const res = await fetch("./profile/change-password", {
          method: "POST",
          body: new URLSearchParams({
            current_password: current,
            new_password: newPwd,
            confirm_password: confirm,
            logout_all: logoutAll ? "1" : "0",
          }),
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/x-www-form-urlencoded",
          },
        });
        const data = await res.json();

        if (data.success) {
          // Met à jour le texte du modal succès si logout_all
          if (data.logout_all) {
            const desc = document.querySelector(
              "#modal-pwd-success .pwd-modal__desc",
            );
            if (desc)
              desc.textContent =
                "Votre mot de passe a été mis à jour. Vous avez été déconnecté de tous les autres appareils.";
          }
          closePwdModal(modalPwdForm, () => {
            openPwdModal(modalPwdSuccess);
          });
        } else {
          if (data.field === "current") {
            document.getElementById("err-pwd-current").textContent =
              data.message;
            document.getElementById("pwd-current").classList.add("is-error");
          } else if (data.field === "new") {
            document.getElementById("err-pwd-new").textContent = data.message;
            document.getElementById("pwd-new").classList.add("is-error");
          } else if (data.field === "confirm") {
            document.getElementById("err-pwd-confirm").textContent =
              data.message;
            document.getElementById("pwd-confirm").classList.add("is-error");
          } else {
            document.getElementById("pwd-form-error").textContent =
              data.message ?? "Une erreur est survenue.";
          }
          this.disabled = false;
          btnText.style.display = "flex";
          spinner.style.display = "none";
        }
      } catch {
        document.getElementById("pwd-form-error").textContent =
          "Erreur réseau. Veuillez réessayer.";
        this.disabled = false;
        btnText.style.display = "flex";
        spinner.style.display = "none";
      }
    });

  // ===== FERMETURES =====
  document
    .getElementById("pwd-2fa-close")
    ?.addEventListener("click", closeAllPwdModals);
  document
    .getElementById("pwd-2fa-cancel")
    ?.addEventListener("click", closeAllPwdModals);
  document
    .getElementById("pwd-totp-close")
    ?.addEventListener("click", closeAllPwdModals);
  document
    .getElementById("pwd-totp-cancel")
    ?.addEventListener("click", closeAllPwdModals);
  document
    .getElementById("pwd-form-close")
    ?.addEventListener("click", closeAllPwdModals);
  document
    .getElementById("pwd-form-cancel")
    ?.addEventListener("click", closeAllPwdModals);
  document
    .getElementById("pwd-success-close")
    ?.addEventListener("click", closeAllPwdModals);

  // Ferme en cliquant sur l'overlay
  [modalPwd2fa, modalPwdTotp, modalPwdForm, modalPwdSuccess].forEach(
    (modal) => {
      modal?.addEventListener("click", (e) => {
        if (e.target === modal) closeAllPwdModals();
      });
    },
  );

  // ===== APPAREILS DE CONFIANCE =====
  const btnShowDevices = document.getElementById("btn-show-devices");
  const devicesList = document.getElementById("trusted-devices-list");
  const devicesLoader = document.getElementById("devices-loader");
  const devicesItems = document.getElementById("devices-items");
  const devicesEmpty = document.getElementById("devices-empty");
  const btnRevokeAll = document.getElementById("btn-revoke-all");

  let devicesVisible = false;

  function getBrowserIcon(name) {
    name = name.toLowerCase();
    if (name.includes("chrome")) return "fab fa-chrome";
    if (name.includes("firefox")) return "fab fa-firefox-browser";
    if (name.includes("safari")) return "fab fa-safari";
    if (name.includes("edge")) return "fab fa-edge";
    if (name.includes("opera")) return "fab fa-opera";
    return "fas fa-globe";
  }

  async function loadTrustedDevices() {
    devicesLoader.style.display = "block";
    devicesItems.style.display = "none";
    devicesEmpty.style.display = "none";
    btnRevokeAll.style.display = "none";

    try {
      const res = await fetch("./profile/trusted-devices", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      const data = await res.json();

      devicesLoader.style.display = "none";

      if (!data.success || !data.devices.length) {
        devicesEmpty.style.display = "flex";
        return;
      }

      devicesItems.innerHTML = "";

      data.devices.forEach((device) => {
        const li = document.createElement("li");
        li.className = "trusted-device-item";
        li.dataset.id = device.id;

        const isExpiring = device.days_left <= 7 && !device.is_expired;

        li.innerHTML = `
                <div class="trusted-device-item__icon ${device.is_current ? "trusted-device-item__icon--current" : ""}">
                    <i class="${getBrowserIcon(device.name)}"></i>
                </div>
                <div class="trusted-device-item__info">
                    <span class="trusted-device-item__name">
                        ${device.name}
                        ${device.is_current ? '<span class="trusted-device-item__badge">Cet appareil</span>' : ""}
                    </span>
                    <span class="trusted-device-item__meta">
                        <i class="fas fa-location-dot"></i> ${device.ip_address}
                        <span class="sep">·</span>
                        <i class="fas fa-calendar"></i> ${device.created_at}
                    </span>
                </div>
                <span class="trusted-device-item__days ${isExpiring ? "trusted-device-item__days--expiring" : ""}">
                    ${device.is_expired ? "Expiré" : isExpiring ? `⚠ ${device.days_left}j` : `${device.days_left}j`}
                </span>
                <button type="button" class="trusted-device-item__revoke" data-id="${device.id}" title="Révoquer">
                    <i class="fas fa-trash-can"></i>
                </button>
            `;

        devicesItems.appendChild(li);
      });

      devicesItems.style.display = "flex";
      btnRevokeAll.style.display = "flex";
    } catch {
      devicesLoader.style.display = "none";
      devicesEmpty.style.display = "flex";
    }
  }

  btnShowDevices?.addEventListener("click", async function () {
    devicesVisible = !devicesVisible;

    if (devicesVisible) {
      devicesList.style.display = "block";
      this.innerHTML = '<i class="fas fa-eye-slash"></i> Masquer';
      await loadTrustedDevices();
    } else {
      devicesList.style.display = "none";
      this.innerHTML = '<i class="fas fa-eye"></i> Gérer';
    }
  });

  // ===== RÉVOQUER UN APPAREIL =====
  devicesItems?.addEventListener("click", async (e) => {
    const btn = e.target.closest(".trusted-device-item__revoke");
    if (!btn) return;

    const deviceId = btn.dataset.id;
    const item = btn.closest(".trusted-device-item");

    btn.disabled = true;
    item.style.opacity = "0.5";
    item.style.transition = "opacity 0.25s ease";

    try {
      const res = await fetch("./profile/revoke-device", {
        method: "POST",
        body: new URLSearchParams({ device_id: deviceId }),
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Content-Type": "application/x-www-form-urlencoded",
        },
      });
      const data = await res.json();

      if (data.success) {
        item.style.opacity = "0";
        item.style.transform = "translateX(20px)";
        setTimeout(() => {
          item.remove();
          // Si plus d'appareils → affiche vide
          if (!devicesItems.children.length) {
            devicesItems.style.display = "none";
            btnRevokeAll.style.display = "none";
            devicesEmpty.style.display = "flex";
          }
        }, 250);
      } else {
        item.style.opacity = "1";
        btn.disabled = false;
      }
    } catch {
      item.style.opacity = "1";
      btn.disabled = false;
    }
  });

  // ===== RÉVOQUER TOUS =====
  btnRevokeAll?.addEventListener("click", async function () {
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Révocation...';

    try {
      const res = await fetch("./profile/revoke-all-devices", {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      const data = await res.json();

      if (data.success) {
        // Anime la sortie de tous les items
        [...devicesItems.children].forEach((item, i) => {
          item.style.transition = `opacity 0.2s ease ${i * 0.05}s, transform 0.2s ease ${i * 0.05}s`;
          item.style.opacity = "0";
          item.style.transform = "translateX(20px)";
        });
        setTimeout(() => {
          devicesItems.innerHTML = "";
          devicesItems.style.display = "none";
          this.style.display = "none";
          devicesEmpty.style.display = "flex";
        }, 400);
      } else {
        this.disabled = false;
        this.innerHTML =
          '<i class="fas fa-trash-can"></i> Révoquer tous les appareils';
      }
    } catch {
      this.disabled = false;
      this.innerHTML =
        '<i class="fas fa-trash-can"></i> Révoquer tous les appareils';
    }
  });

  // ===== NOTIFICATIONS =====
  document.querySelectorAll(".notif-toggle").forEach((toggle) => {
    toggle.addEventListener("change", async function () {
      const setting = this.dataset.setting;
      const value = this.checked;
      const feedback = document.getElementById("notif-feedback");
      const original = !value;

      try {
        const res = await fetch("./profile/update-notification", {
          method: "POST",
          body: new URLSearchParams({
            setting,
            value: value ? "1" : "0",
          }),
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/x-www-form-urlencoded",
          },
        });
        const data = await res.json();

        if (data.success) {
          if (feedback) {
            feedback.textContent = value
              ? "Notification activée."
              : "Notification désactivée.";
            feedback.className = "setting-item__feedback success";
            feedback.style.display = "block";
            setTimeout(() => {
              feedback.style.display = "none";
            }, 2500);
          }
        } else {
          this.checked = original;
        }
      } catch {
        this.checked = original;
      }
    });
  });

  // ===== DÉCONNEXION =====
  document
    .querySelector(".btn-setting.logout")
    ?.addEventListener("click", () => {
      window.location.href = "./logout";
    });
});
