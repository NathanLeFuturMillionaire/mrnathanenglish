document.addEventListener("DOMContentLoaded", () => {
  /* ============================================================
     VARIABLES GLOBALES
  ============================================================ */
  const step1 = document.getElementById("step-1");
  const step2 = document.getElementById("step-2");
  const nextBtn = document.getElementById("next-step");
  const saveDraftBtn = document.getElementById("save-draft");
  const prevBtn = document.getElementById("prev-step");
  const steps = document.querySelectorAll(".step");
  const form = document.getElementById("create-course-form");
  const submitBtn = document.querySelector(".btn-submit");
  const globalErrors = document.getElementById("global-errors");

  /* ============================================================
     UTILITAIRES ERREURS
  ============================================================ */
  function clearError(field) {
    const group = field.closest(".form-group");
    if (!group) return;
    const error = group.querySelector(".error-message");
    if (error) error.innerHTML = "";
    group.classList.remove("has-error");
  }

  function isAjax() {
    return {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    };
  }

  /* ============================================================
     SAUVEGARDE AJAX (Brouillon / Étape 1 / Final)
  ============================================================ */
  function saveDraft(goNext = false) {
    const formData = new FormData(form);
    formData.append("mode", goNext ? "step1" : "draft");

    fetch("../courses/create", {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          if (globalErrors && data.errors) {
            globalErrors.innerHTML = data.errors
              .map((e) => `<p>⚠️ ${e}</p>`)
              .join("");
          }
          return;
        }

        // Transition étape 1 → 2
        if (goNext) {
          step1.classList.remove("active");
          step2.classList.add("active");
          steps[0].classList.remove("active");
          steps[1].classList.add("active");
        }
      })
      .catch((err) => {
        console.error("Erreur sauvegarde :", err);
      });
  }

  /* ============================================================
     VALIDATION ÉTAPE 1
  ============================================================ */
  function validateStep1() {
    let valid = true;

    document
      .querySelectorAll("#step-1 .error-message")
      .forEach((e) => (e.innerHTML = ""));
    document
      .querySelectorAll("#step-1 .form-group")
      .forEach((e) => e.classList.remove("has-error"));

    const fields = [
      ["title_course", "Le titre du cours"],
      ["description_course", "La description"],
      ["language_taught", "La langue"],
      ["learner_level", "Le niveau"],
      ["validation_period", "La période"],
      ["price_course", "Le prix"],
    ];

    fields.forEach(([name, label]) => {
      const input = document.querySelector(`[name="${name}"]`);
      if (!input || input.value.trim()) return;
      const group = input.closest(".form-group");
      group.classList.add("has-error");
      group.querySelector(".error-message").innerHTML = `⚠️ ${label} requis`;
      valid = false;
    });

    const isFree =
      document.querySelector('input[name="is_free"]:checked').value === "1";
    const price = document.querySelector('[name="price_course"]');

    if (!isFree && (!price.value || price.value <= 0)) {
      price.closest(".form-group").classList.add("has-error");
      price.nextElementSibling.innerHTML = "⚠️ Prix invalide";
      valid = false;
    }

    return valid;
  }

  /* ============================================================
     VALIDATION ÉTAPE 2
  ============================================================ */
  function validateStep2() {
    let valid = true;
    if (globalErrors) globalErrors.innerHTML = "";

    const modules = modulesContainer.querySelectorAll(".module-card");
    if (!modules.length) {
      globalErrors.innerHTML = "⚠️ Ajoutez au moins un module";
      return false;
    }

    modules.forEach((module) => {
      const moduleTitle = module.querySelector('input[name$="[title]"]');
      if (!moduleTitle.value.trim()) {
        moduleTitle.closest(".form-group").classList.add("has-error");
        moduleTitle.nextElementSibling.innerHTML = "⚠️ Titre requis";
        valid = false;
      }

      module.querySelectorAll(".lesson-item").forEach((lesson) => {
        const title = lesson.querySelector('input[name$="[title]"]');
        const content = lesson.querySelector(".lesson-content-hidden");

        if (!title.value.trim()) {
          title.closest(".form-group").classList.add("has-error");
          title.nextElementSibling.innerHTML = "⚠️ Titre requis";
          valid = false;
        }

        if (!content.value || content.value === "<p><br></p>") {
          content.closest(".form-group").classList.add("has-error");
          content.nextElementSibling.innerHTML = "⚠️ Contenu requis";
          valid = false;
        }
      });
    });

    return valid;
  }

  /* ============================================================
     BOUTONS ÉTAPES
  ============================================================ */
  if (nextBtn) {
    nextBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (validateStep1()) saveDraft(true);
    });
  }

  if (saveDraftBtn) {
    saveDraftBtn.addEventListener("click", (e) => {
      e.preventDefault();
      saveDraft(false);
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener("click", () => {
      step2.classList.remove("active");
      step1.classList.add("active");
    });
  }

  /* ============================================================
     QUILL – INITIALISATION + CLEAN ERREURS
  ============================================================ */
  function initQuill(container) {
    const hidden = container.nextElementSibling;

    const quill = new Quill(container, {
      theme: "snow",
      modules: {
        toolbar: [
          ["bold", "italic"],
          [{ header: 1 }, { header: 2 }],
          [{ list: "ordered" }, { list: "bullet" }],
          ["link"],
          ["clean"],
        ],
      },
    });

    quill.on("text-change", () => {
      hidden.value = quill.root.innerHTML;
      if (hidden.value.trim()) clearError(hidden);
    });

    hidden.value = quill.root.innerHTML;
  }

  document.querySelectorAll(".quill-editor").forEach(initQuill);

  /* ============================================================
     SOUMISSION FINALE
  ============================================================ */
  if (form) {
    /* ============================================================
    FINAL SUBMIT → PUBLISH COURSE
 ============================================================ */
    form?.addEventListener("submit", (e) => {
      e.preventDefault();

      const data = new FormData(form);
      data.append("mode", "final");

      submitBtn.disabled = true;

      fetch("../courses/publish", {
        method: "POST",
        body: data,
        ...isAjax(),
      })
        .then((r) => r.json())
        .then((res) => {
          submitBtn.disabled = false;
          if (res.success) {
            alert("Cours publié avec succès");
            window.location.href = "../courses";
          } else {
            globalErrors.innerHTML = res.errors
              ?.map((e) => `<p>⚠️ ${e}</p>`)
              .join("");
          }
        })
        .catch(console.error);
    });
  }

  // ===================================================================
  // GESTION AVANCÉE DE L'UPLOAD DE L'IMAGE DE COUVERTURE (CLIC + DRAG & DROP)
  // ===================================================================
  const uploadArea = document.getElementById("upload-area");
  const fileInput = document.getElementById("profile_picture");
  const previewContainer = document.getElementById("preview-container");
  const previewImg = document.getElementById("image-preview");
  const removeBtn = document.getElementById("remove-image");

  if (uploadArea && fileInput && previewContainer && previewImg && removeBtn) {
    // 1. Clic sur la zone → ouvre le sélecteur de fichiers natif
    uploadArea.addEventListener("click", (e) => {
      // Empêche le déclenchement si on clique sur un élément enfant qui pourrait interférer
      // (par exemple si tu ajoutes du texte ou des icônes plus tard)
      if (e.target !== uploadArea && e.target.closest("button, a")) return;
      fileInput.click();
    });

    // 2. Drag & Drop : feedback visuel + upload immédiat au drop
    uploadArea.addEventListener("dragover", (e) => {
      e.preventDefault(); // Obligatoire pour autoriser le drop
      e.stopPropagation();
      uploadArea.classList.add("drag-over");
    });

    uploadArea.addEventListener("dragenter", (e) => {
      e.preventDefault();
      e.stopPropagation();
      uploadArea.classList.add("drag-over");
    });

    uploadArea.addEventListener("dragleave", (e) => {
      e.preventDefault();
      e.stopPropagation();
      // Ne retire la classe que si on sort vraiment de la zone
      if (!uploadArea.contains(e.relatedTarget)) {
        uploadArea.classList.remove("drag-over");
      }
    });

    uploadArea.addEventListener("drop", (e) => {
      e.preventDefault();
      e.stopPropagation();
      uploadArea.classList.remove("drag-over");

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        // On prend uniquement le premier fichier (on pourrait gérer plusieurs si besoin)
        handleFile(files[0]);
      }
    });

    // 3. Sélection classique via l'input file
    fileInput.addEventListener("change", () => {
      if (fileInput.files.length > 0) {
        handleFile(fileInput.files[0]);
      }
    });

    // 4. Fonction centralisée : validation + prévisualisation
    function handleFile(file) {
      // Validation du type (optionnel mais recommandé)
      if (!file.type.startsWith("image/")) {
        alert("Veuillez sélectionner une image valide (JPG, PNG, etc.).");
        resetUpload();
        return;
      }

      // Validation de la taille (5 Mo max)
      if (file.size > 5 * 1024 * 1024) {
        alert("L'image ne doit pas dépasser 5 Mo.");
        resetUpload();
        return;
      }

      const reader = new FileReader();
      reader.onload = (event) => {
        previewImg.src = event.target.result;
        previewContainer.classList.remove("preview-hidden");
        uploadArea.style.display = "none"; // Masque la zone de dépôt
      };
      reader.readAsDataURL(file);
    }

    // 5. Suppression de l'image sélectionnée
    removeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      resetUpload();
    });

    // Fonction de réinitialisation complète de l'état
    function resetUpload() {
      fileInput.value = ""; // Vide l'input file
      previewImg.src = ""; // Vide l'aperçu
      previewContainer.classList.add("preview-hidden");
      uploadArea.style.display = "block"; // Réaffiche la zone de dépôt
      uploadArea.classList.remove("drag-over");
    }

    // Optionnel : si une image est déjà chargée au chargement de la page (édition d'un cours existant)
    // Tu peux appeler handleFile() ou simuler l'état preview si profile_picture existe déjà.
  }

  // ===================================================================
  // SYNCHRONISATION DES RADIOS GRATUIT / PAYANT AVEC LE CHAMP PRIX
  // ===================================================================
  const priceRadios = document.querySelectorAll('input[name="is_free"]');
  const priceInput = document.querySelector('input[name="price_course"]');

  if (priceRadios.length > 0 && priceInput) {
    // Fonction qui met à jour l'état du champ prix selon le radio sélectionné
    function updatePriceField() {
      const isFree =
        document.querySelector('input[name="is_free"]:checked').value === "1";

      if (isFree) {
        // Mode Gratuit
        priceInput.value = "0"; // Force la valeur à 0
        priceInput.disabled = true; // Désactive le champ
        priceInput.placeholder = "Gratuit (0 F CFA)";
        priceInput.removeAttribute("required"); // Plus obligatoire en mode gratuit
        // Optionnel : effacer les erreurs précédentes
        clearError(priceInput);
      } else {
        // Mode Payant
        priceInput.disabled = false; // Réactive le champ
        priceInput.placeholder = "Ex: 15000";
        priceInput.setAttribute("required", "required"); // Réactive l'obligation
        // Si la valeur était 0 (provenant du mode gratuit), on la vide pour forcer la saisie
        if (priceInput.value === "0" || priceInput.value === "") {
          priceInput.value = "";
        }
        priceInput.focus(); // Bonus UX : place le curseur dans le champ pour saisie immédiate
      }
    }

    // Écoute les changements sur les radios
    priceRadios.forEach((radio) => {
      radio.addEventListener("change", updatePriceField);
    });

    // Exécution immédiate au chargement pour gérer l'état initial (Gratuit coché par défaut)
    updatePriceField();

    // Fonction utilitaire pour effacer les erreurs (si tu l'as déjà définie ailleurs, ignore-la)
    function clearError(field) {
      const group = field.closest(".form-group");
      if (group) {
        const errorEl = group.querySelector(".error-message");
        if (errorEl) errorEl.innerHTML = "";
        group.classList.remove("has-error");
      }
    }
  }

  // ===================================================================
  // ÉTAPE 2 : GESTION DYNAMIQUE DES MODULES ET LEÇONS
  // ===================================================================
  const addModuleBtn = document.getElementById("add-module");
  const modulesContainer = document.getElementById("modules-container");

  let moduleCounter =
    modulesContainer.querySelectorAll(".module-card").length || 0;

  // Fonction pour ajouter un nouveau module
  function addNewModule() {
    moduleCounter++;

    const moduleHTML = `
    <div class="module-card" data-module="${moduleCounter}">
      <div class="module-header">
        <h3>Module ${moduleCounter} <span class="module-title-preview">(sans titre)</span></h3>
        <div class="module-actions">
          <button type="button" class="btn-collapse" title="Réduire/Développer">
            <i class="fas fa-chevron-down"></i>
          </button>
          <button type="button" class="btn-remove-module" title="Supprimer le module">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>

      <div class="module-content">
        <div class="form-group">
          <label>Titre du module <span class="required">*</span></label>
          <input type="text" name="modules[${moduleCounter}][title]" required 
                 placeholder="Ex: Introduction à l'anglais professionnel">
          <div class="error-message"></div>
        </div>

        <div class="form-group">
          <label>Description (facultatif)</label>
          <textarea name="modules[${moduleCounter}][description]" rows="3" 
                    placeholder="Décrivez brièvement ce module..."></textarea>
        </div>

        <div class="lessons-list">
          <h4>Leçons du module</h4>
          <button type="button" class="btn-add-lesson btn-add" data-module="${moduleCounter}">
            <i class="fas fa-plus"></i> Ajouter une leçon
          </button>
          <div class="lessons-container" data-module="${moduleCounter}"></div>
        </div>
      </div>
    </div>
  `;

    modulesContainer.insertAdjacentHTML("beforeend", moduleHTML);
  }

  // Fonction pour ajouter une nouvelle leçon dans un module spécifique
  function addNewLesson(moduleId) {
    const lessonsContainer = document.querySelector(
      `.lessons-container[data-module="${moduleId}"]`
    );
    const lessonCount =
      lessonsContainer.querySelectorAll(".lesson-item").length + 1;

    const lessonHTML = `
    <div class="lesson-item">
      <div class="lesson-header">
        <h5>Leçon ${lessonCount}</h5>
        <button type="button" class="btn-remove-lesson" title="Supprimer la leçon">
          <i class="fas fa-trash"></i>
        </button>
      </div>

      <div class="form-group">
        <label>Titre de la leçon <span class="required">*</span></label>
        <input type="text" name="modules[${moduleId}][lessons][${lessonCount}][title]" required>
        <div class="error-message"></div>
      </div>

      <div class="form-group">
        <label>Contenu de la leçon <span class="required">*</span></label>
        <div class="quill-editor" style="height: 320px;"></div>
        <input type="hidden" name="modules[${moduleId}][lessons][${lessonCount}][content]" class="lesson-content-hidden">
        <div class="error-message"></div>
      </div>

      <div class="form-group">
        <label>URL de la vidéo (facultatif)</label>
        <input type="url" name="modules[${moduleId}][lessons][${lessonCount}][video_url]" 
               placeholder="https://youtube.com/...">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Durée (minutes)</label>
          <input type="number" name="modules[${moduleId}][lessons][${lessonCount}][duration]" min="1">
        </div>
        <div class="form-group checkbox-group">
          <label class="checkbox-label">
            <input type="checkbox" name="modules[${moduleId}][lessons][${lessonCount}][is_free]">
            <span class="checkmark"></span> Leçon gratuite (aperçu)
          </label>
        </div>
      </div>
    </div>
  `;

    lessonsContainer.insertAdjacentHTML("beforeend", lessonHTML);

    // Initialisation de Quill pour la nouvelle leçon
    const newEditor =
      lessonsContainer.lastElementChild.querySelector(".quill-editor");
    const hiddenInput = lessonsContainer.lastElementChild.querySelector(
      ".lesson-content-hidden"
    );

    const quill = new Quill(newEditor, {
      theme: "snow",
      modules: {
        toolbar: [
          ["bold", "italic"],
          [{ header: 1 }, { header: 2 }],
          [{ list: "ordered" }, { list: "bullet" }],
          ["link", "image"],
          ["clean"],
        ],
      },
      placeholder: "Rédigez le contenu détaillé de votre leçon ici...",
    });

    quill.on("text-change", () => {
      hiddenInput.value = quill.root.innerHTML;
    });
  }

  // Délégation d'événements pour tous les modules (ajout, suppression, collapse)
  if (modulesContainer && addModuleBtn) {
    // Bouton principal "Ajouter un module"
    addModuleBtn.addEventListener("click", addNewModule);

    // Délégation pour les actions dynamiques
    modulesContainer.addEventListener("click", (e) => {
      const target = e.target.closest("button");
      if (!target) return;

      const moduleCard = target.closest(".module-card");

      // Suppression d'un module entier
      if (target.classList.contains("btn-remove-module")) {
        if (
          confirm(
            "Supprimer ce module et toutes ses leçons ? Cette action est irréversible."
          )
        ) {
          moduleCard.remove();
          // Optionnel : renuméroter les modules restants (visuellement ou pour les noms)
        }
        return;
      }

      // Réduire / Développer le module
      if (target.classList.contains("btn-collapse")) {
        const content = moduleCard.querySelector(".module-content");
        const icon = target.querySelector("i");
        content.classList.toggle("collapsed");
        icon.classList.toggle("fa-chevron-down");
        icon.classList.toggle("fa-chevron-up");
        return;
      }

      // Ajouter une leçon
      if (target.classList.contains("btn-add-lesson")) {
        const moduleId = target.dataset.module;
        addNewLesson(moduleId);
        return;
      }

      // Suppression d'une leçon
      if (target.classList.contains("btn-remove-lesson")) {
        if (confirm("Supprimer cette leçon ?")) {
          target.closest(".lesson-item").remove();
          // Optionnel : renuméroter les leçons du module
        }
      }
    });

    // Mise à jour du titre du module en live (prévisualisation)
    modulesContainer.addEventListener("input", (e) => {
      if (
        e.target.name &&
        e.target.name.includes("[title]") &&
        !e.target.name.includes("[lessons]")
      ) {
        const moduleCard = e.target.closest(".module-card");
        const preview = moduleCard.querySelector(".module-title-preview");
        if (preview) {
          preview.textContent = e.target.value
            ? `: ${e.target.value}`
            : "(sans titre)";
        }
      }
    });
  }

  // ===================================================================
  // AUTO-SAVE DES MODULES ET LEÇONS (ÉTAPE 2)
  // ===================================================================

  let autoSaveTimeout = null;
  const AUTO_SAVE_DELAY = 2000; // 2 secondes d'inactivité avant envoi

  // Fonction pour construire le JSON du contenu pédagogique (modules + leçons)
  function buildContentData() {
    const modules = [];

    document
      .querySelectorAll(".module-card")
      .forEach((moduleCard, moduleIndex) => {
        const moduleId = moduleCard.dataset.module; // Utile pour le mapping, mais on utilise l'index réel

        const module = {
          title: moduleCard
            .querySelector('input[name*="[title]"]')
            .value.trim(),
          description:
            moduleCard
              .querySelector('textarea[name*="[description]"]')
              .value.trim() || null,
          lessons: [],
        };

        moduleCard.querySelectorAll(".lesson-item").forEach((lessonItem) => {
          const lesson = {
            title: lessonItem
              .querySelector('input[name*="[title]"]')
              .value.trim(),
            content: lessonItem.querySelector(".lesson-content-hidden").value,
            video_url:
              lessonItem
                .querySelector('input[name*="[video_url]"]')
                .value.trim() || null,
            duration:
              lessonItem.querySelector('input[name*="[duration]"]').value ||
              null,
            is_free: lessonItem.querySelector('input[name*="[is_free]"]')
              .checked,
          };

          // Nettoyage du contenu Quill vide
          if (
            lesson.content === "" ||
            lesson.content === "<p><br></p>" ||
            lesson.content === "<p></p>"
          ) {
            lesson.content = "";
          }

          module.lessons.push(lesson);
        });

        modules.push(module);
      });

    return {
      modules: modules,
    };
  }

  // Fonction debounce personnalisée (sans dépendance externe)
  function debounceAutoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
      performAutoSave();
    }, AUTO_SAVE_DELAY);
  }

  // Fonction principale d'auto-save AJAX
  function performAutoSave() {
    const contentData = buildContentData();

    // Si aucun module n'existe encore, on n'envoie rien (évite les auto-saves inutiles)
    if (contentData.modules.length === 0) {
      console.log("Auto-save ignoré : aucun module présent.");
      return;
    }

    const formData = new FormData();
    formData.append("content_data", JSON.stringify(contentData));
    formData.append("auto_save_content", "1"); // Flag pour identifier cet appel côté PHP

    // Si tu as déjà un draft_id (de l'étape 1), on l'ajoute
    const entityInput = document.getElementById("course_id_hidden");

    if (entityInput && entityInput.name && Number(entityInput.value) > 0) {
      formData.append(entityInput.name, entityInput.value);
    }

    const endpoint =
      entityInput && entityInput.name === "course_id"
        ? "../courses/update-content"
        : "../courses/auto-save-content";

    fetch(endpoint, {
      method: "POST",
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          showAutoSaveFeedback("Contenu enregistré");

          const entityInput = document.getElementById("course_id_hidden");

          // ⚠️ ON NE MET À JOUR L'INPUT QUE SI ON EST EN MODE DRAFT
          if (entityInput && entityInput.name === "draft_id" && data.draft_id) {
            entityInput.value = data.draft_id;
          }
        }
      })
      .catch((err) => {
        console.error("Erreur AJAX auto-save contenu :", err);
        showAutoSaveFeedback("Erreur réseau lors de la sauvegarde", "error");
      });
  }

  // Feedback visuel discret (tu peux styliser cela en CSS)
  function showAutoSaveFeedback(message, type = "success") {
    // Supprime un ancien feedback
    const old = document.getElementById("auto-save-feedback");
    if (old) old.remove();

    const feedback = document.createElement("div");
    feedback.id = "auto-save-feedback";
    feedback.textContent = message;
    feedback.style.position = "fixed";
    feedback.style.bottom = "20px";
    feedback.style.right = "20px";
    feedback.style.padding = "12px 24px";
    feedback.style.borderRadius = "8px";
    feedback.style.color = "#fff";
    feedback.style.fontSize = "14px";
    feedback.style.zIndex = "9999";
    feedback.style.opacity = "0";
    feedback.style.transition = "opacity 0.4s ease";
    feedback.style.backgroundColor = type === "error" ? "#dc3545" : "#28a745";

    document.body.appendChild(feedback);

    // Fade in
    setTimeout(() => (feedback.style.opacity = "1"), 100);
    // Fade out et suppression après 3 secondes
    setTimeout(() => {
      feedback.style.opacity = "0";
      setTimeout(() => feedback.remove(), 400);
    }, 3000);
  }

  // Écouteurs pour déclencher l'auto-save sur toute modification dans les modules
  if (modulesContainer) {
    // Tout changement de texte, checkbox, etc.
    modulesContainer.addEventListener("input", debounceAutoSave);
    modulesContainer.addEventListener("change", debounceAutoSave); // Pour les checkboxes et selects

    // Contenu Quill des leçons
    modulesContainer.addEventListener("text-change", debounceAutoSave); // Événement Quill

    // Ajout/suppression de module ou leçon → déclenche immédiatement
    modulesContainer.addEventListener("click", (e) => {
      if (
        e.target.closest(".btn-add-lesson") ||
        e.target.closest(".btn-remove-module") ||
        e.target.closest(".btn-remove-lesson") ||
        e.target.closest("#add-module")
      ) {
        // Petit délai pour laisser le DOM se mettre à jour
        setTimeout(debounceAutoSave, 300);
      }
    });
  }

  // Auto-save initial si des modules existent déjà au chargement (édition)
  if (modulesContainer && modulesContainer.querySelector(".module-card")) {
    debounceAutoSave();
  }

  /* ============================================================
   CONVERSION DURÉE (HEURES → JOURS)
   24h = 1 jour
============================================================ */
  const timeCourseInput = document.querySelector('input[name="time_course"]');
  const validationPeriodInput = document.querySelector(
    'input[name="validation_period"]'
  );

  if (timeCourseInput && validationPeriodInput) {
    timeCourseInput.addEventListener("input", () => {
      const hours = parseFloat(timeCourseInput.value);

      // Si vide ou invalide, on n’écrase rien
      if (isNaN(hours) || hours <= 0) {
        return;
      }

      // Conversion : 24h = 1 jour
      const days = Math.ceil(hours / 24);

      validationPeriodInput.value = days;

      // Nettoyage d’erreur éventuelle côté UI
      const group = validationPeriodInput.closest(".form-group");
      if (group) {
        group.classList.remove("has-error");
        const error = group.querySelector(".error-message");
        if (error) error.innerHTML = "";
      }
    });
  }

  /* ============================================================
   CONVERSION DURÉE (JOURS → HEURES)
   1 jour = 24 heures
   (sens inverse, sans boucle infinie)
============================================================ */
  let isAutoConverting = false;

  if (timeCourseInput && validationPeriodInput) {
    validationPeriodInput.addEventListener("input", () => {
      // Empêche la boucle heures ↔ jours
      if (isAutoConverting) return;

      const days = parseFloat(validationPeriodInput.value);

      // Valeur invalide → on n’écrase rien
      if (isNaN(days) || days <= 0) {
        return;
      }

      isAutoConverting = true;

      const hours = days * 24;
      timeCourseInput.value = hours;

      // Nettoyage d’erreur éventuelle
      const group = timeCourseInput.closest(".form-group");
      if (group) {
        group.classList.remove("has-error");
        const error = group.querySelector(".error-message");
        if (error) error.innerHTML = "";
      }

      // Libère le verrou après la mise à jour
      setTimeout(() => {
        isAutoConverting = false;
      }, 0);
    });
  }
});
