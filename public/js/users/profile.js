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

  // ===== DÉCONNEXION =====
  document
    .querySelector(".btn-setting.logout")
    ?.addEventListener("click", () => {
      window.location.href = "./logout";
    });
});
