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
      // Force le reflow pour que l'animation se déclenche
      target.offsetHeight;
      target.classList.add("active");
    }
  }

  function setActiveLink(link) {
    menuLinks.forEach((l) => l.classList.remove("active"));
    link.classList.add("active");
  }

  // Initialisation : affiche la section active par défaut
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

  // ===== SOUMISSION AJAX =====
  document
    .getElementById("profile-edit-form")
    ?.addEventListener("submit", async (e) => {
      e.preventDefault();

      const btn = document.getElementById("btn-save");
      const msgBox = document.getElementById("edit-message");
      const btnText = btn.querySelector(".btn-text");
      const spinner = btn.querySelector(".btn-spinner");

      // Reset erreurs
      document
        .querySelectorAll(".edit-error")
        .forEach((el) => (el.textContent = ""));
      msgBox.style.display = "none";
      msgBox.className = "edit-message";

      // Loading
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

          // Met à jour les champs en lecture sans recharger
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
              if (el && data.user[key] !== undefined) {
                el.textContent = data.user[key] || "Non renseigné";
              }
            });

            // Met à jour l'avatar partout si changé
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

            // Met à jour le badge de niveau dans la sidebar
            if (data.user.english_level) {
              const levelLabels = {
                beginner: "Débutant",
                intermediate: "Intermédiaire",
                advanced: "Avancé",
              };

              const levelBadge = document.querySelector(".level-badge");
              if (levelBadge) {
                levelBadge.innerHTML = `<i class="fas fa-graduation-cap"></i> ${levelLabels[data.user.english_level] ?? data.user.english_level}`;
              }
            }

            // Met à jour le username dans la sidebar
            const sidebarUsername = document.querySelector(".menu-header h2");
            if (sidebarUsername && data.user.username) {
              sidebarUsername.textContent = data.user.username;
            }
          }

          // Met à jour la barre de complétion si retournée par le serveur
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

            if (percent) {
              percent.textContent = data.completion.percentage + "%";
            }

            // Disparition si 100%
            if (data.completion.percentage === 100 && completion) {
              setTimeout(() => {
                completion.classList.add("is-complete");
                // Supprime du DOM après l'animation
                setTimeout(() => completion.remove(), 1500);
              }, 800);
            }

            // Réapparition si on redescend sous 70% (cas d'un champ effacé)
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
      } catch (err) {
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
  const langPanel = document.getElementById("lang-panel");
  const langSearchInput = document.getElementById("lang-search-input");
  const langSearchClear = document.getElementById("lang-search-clear");
  const langResults = document.getElementById("lang-results");
  const langNoResults = document.getElementById("lang-no-results");
  const langSelectedText = document.getElementById("lang-selected-text");
  const langHiddenInput = document.getElementById("native_language_input");

  if (langDropdown) {
    // Ouvrir / fermer
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

    // Fermer en cliquant dehors
    document.addEventListener("click", (e) => {
      if (!langDropdown.contains(e.target)) {
        langDropdown.classList.remove("is-open");
        langSearchInput.value = "";
        resetSearch();
      }
    });

    // Fermer avec Escape
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && langDropdown.classList.contains("is-open")) {
        langDropdown.classList.remove("is-open");
        langSearchInput.value = "";
        resetSearch();
      }
    });

    // Recherche
    langSearchInput.addEventListener("input", () => {
      const query = langSearchInput.value.trim().toLowerCase();
      langSearchClear.style.display = query ? "flex" : "none";
      filterLanguages(query);
    });

    // Clear recherche
    langSearchClear.addEventListener("click", () => {
      langSearchInput.value = "";
      langSearchClear.style.display = "none";
      resetSearch();
      langSearchInput.focus();
    });

    // Sélection d'une option
    langResults.addEventListener("click", (e) => {
      const option = e.target.closest(".lang-option");
      if (!option) return;

      const value = option.dataset.value;
      const label = option.dataset.label;

      // Met à jour l'input caché
      langHiddenInput.value = value;

      // Met à jour le trigger
      langSelectedText.textContent = label;
      langSelectedText.classList.remove("is-placeholder");

      // Met à jour l'état visuel des options
      document.querySelectorAll(".lang-option").forEach((opt) => {
        opt.classList.remove("is-selected");
        const check = opt.querySelector(".lang-option__check");
        if (check) check.remove();
      });

      option.classList.add("is-selected");
      const check = document.createElement("i");
      check.className = "fas fa-check lang-option__check";
      option.appendChild(check);

      // Ferme le panel
      langDropdown.classList.remove("is-open");
      langSearchInput.value = "";
      langSearchClear.style.display = "none";
      resetSearch();
    });

    // Filtre les langues
    function filterLanguages(query) {
      let totalVisible = 0;
      const groups = langResults.querySelectorAll(".lang-group");

      groups.forEach((group) => {
        let groupVisible = 0;
        const options = group.querySelectorAll(".lang-option");

        options.forEach((option) => {
          const label = option.dataset.label.toLowerCase();
          const match = !query || label.includes(query);

          option.style.display = match ? "flex" : "none";

          if (match) {
            groupVisible++;
            totalVisible++;

            // Highlight
            const labelEl = option.querySelector(".lang-option__label");
            if (query) {
              const idx = label.indexOf(query);
              const orig = option.dataset.label;
              labelEl.innerHTML =
                orig.slice(0, idx) +
                "<mark>" +
                orig.slice(idx, idx + query.length) +
                "</mark>" +
                orig.slice(idx + query.length);
            } else {
              labelEl.textContent = option.dataset.label;
            }
          }
        });

        // Cache le groupe si aucun résultat
        group.style.display = groupVisible > 0 ? "block" : "none";
      });

      langNoResults.style.display = totalVisible === 0 ? "flex" : "none";
    }

    function resetSearch() {
      document
        .querySelectorAll(".lang-group")
        .forEach((g) => (g.style.display = "block"));
      document.querySelectorAll(".lang-option").forEach((opt) => {
        opt.style.display = "flex";
        const labelEl = opt.querySelector(".lang-option__label");
        labelEl.textContent = opt.dataset.label;
      });
      langNoResults.style.display = "none";
    }

    function scrollToSelected() {
      const selected = langResults.querySelector(".lang-option.is-selected");
      if (selected) {
        setTimeout(() => {
          selected.scrollIntoView({ block: "center", behavior: "smooth" });
        }, 150);
      }
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

      // Animation de sortie
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
      const btn = this;
      const list = document.getElementById("login-history-list");
      const isOpen = btn.classList.contains("is-open");

      if (isOpen) {
        // Refermer — ne garder que les 4 premiers
        const items = list.querySelectorAll(".login-history__item");
        items.forEach((item, i) => {
          if (i >= 4) {
            item.style.transition = "opacity 0.2s ease, transform 0.2s ease";
            item.style.opacity = "0";
            item.style.transform = "translateY(-6px)";
            setTimeout(() => item.remove(), 200);
          }
        });

        btn.classList.remove("is-open");
        btn.querySelector(".fa-clock-rotate-left").style.display = "";
        btn.childNodes[2].textContent = " Voir toutes les connexions ";
        return;
      }

      // Loading
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';

      try {
        const res = await fetch("./profile/login-history", {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const data = await res.json();

        if (data.success && data.logins) {
          // Ajoute les connexions supplémentaires (après la 4ème)
          data.logins.slice(4).forEach((login, i) => {
            const li = document.createElement("li");
            li.className = "login-history__item";
            li.style.opacity = "0";
            li.style.transform = "translateY(8px)";
            li.style.transition = `opacity 0.25s ease ${i * 0.04}s, transform 0.25s ease ${i * 0.04}s`;

            li.innerHTML = `
                    <div class="login-history__icon">
                        <i class="fas fa-${login.device === "mobile" ? "mobile-screen" : login.device === "tablette" ? "tablet-screen-button" : "display"}"></i>
                    </div>
                    <div class="login-history__info">
                        <span class="login-history__device">
                            ${login.browser} sur ${login.os}
                        </span>
                        <span class="login-history__meta">
                            <i class="fas fa-location-dot"></i> ${login.ip_address}
                            <span class="sep">·</span>
                            <i class="fas fa-clock"></i> ${login.created_at}
                        </span>
                    </div>
                    <button class="login-history__delete" data-id="${login.id}" title="Supprimer cette session">
                        <i class="fas fa-trash-can"></i>
                    </button>
                `;

            list.appendChild(li);

            // Déclenche l'animation
            requestAnimationFrame(() => {
              li.style.opacity = "1";
              li.style.transform = "translateY(0)";
            });
          });

          btn.disabled = false;
          btn.classList.add("is-open");
          btn.innerHTML = `
                <i class="fas fa-clock-rotate-left"></i>
                Réduire
                <span class="login-history__more-count">${data.logins.length} au total</span>
                <i class="fas fa-chevron-down login-history__more-arrow" style="transform:rotate(180deg)"></i>
            `;
        }
      } catch {
        btn.disabled = false;
        btn.innerHTML = `
            <i class="fas fa-clock-rotate-left"></i>
            Voir toutes les connexions
            <i class="fas fa-chevron-down login-history__more-arrow"></i>
        `;
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

        if (!data.success) {
          // Revert le toggle si erreur
          this.checked = !enabled;
        }

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

  // ===== DÉCONNEXION =====
  document
    .querySelector(".btn-setting.logout")
    ?.addEventListener("click", () => {
      window.location.href = "./logout";
    });
});
