document.addEventListener('DOMContentLoaded', () => {
  // ===================================================================
  // VARIABLES GLOBALES
  // ===================================================================
  const step1 = document.getElementById('step-1');
  const step2 = document.getElementById('step-2');
  const nextBtn = document.getElementById('next-step');
  const prevBtn = document.getElementById('prev-step');
  const steps = document.querySelectorAll('.step');
  const form = document.getElementById('create-course-form');
  const submitBtn = document.querySelector('.btn-submit');

  let moduleCount = 0;

  // ===================================================================
  // 1. GESTION DU WIZARD + AJAX ÉTAPE 1 → ÉTAPE 2
  // ===================================================================
  if (nextBtn && step1 && step2) {
    nextBtn.addEventListener('click', (e) => {
      e.preventDefault();

      const formData = new FormData(form);

      // Désactiver le bouton pendant l'envoi
      nextBtn.disabled = true;
      nextBtn.classList.add('loading');

      fetch('../courses/create', {  // Même URL que la page, ton routeur gère l'AJAX
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        nextBtn.disabled = false;
        nextBtn.classList.remove('loading');

        if (data.success) {
          // Stocker l'ID du cours créé pour l'étape 2
          form.dataset.courseId = data.course_id;

          // Passage fluide à l'étape 2
          step1.classList.remove('active');
          step2.classList.add('active');
          steps[0].classList.remove('active');
          steps[1].classList.add('active');

          // Message de confirmation
          alert(data.message || 'Étape 1 terminée ! Passez au contenu du cours.');
        } else {
          // Gestion des erreurs
          const errorMsg = data.errors
            ? 'Erreurs :\n• ' + data.errors.join('\n• ')
            : data.message || 'Une erreur est survenue.';
          alert(errorMsg);
        }
      })
      .catch(err => {
        nextBtn.disabled = false;
        nextBtn.classList.remove('loading');
        alert('Erreur réseau. Veuillez réessayer.');
        console.error(err);
      });
    });
  }

  // Bouton Précédent
  if (prevBtn && step1 && step2) {
    prevBtn.addEventListener('click', () => {
      step2.classList.remove('active');
      step1.classList.add('active');
      steps[1].classList.remove('active');
      steps[0].classList.add('active');
    });
  }

  // ===================================================================
  // 2. MENU MOBILE
  // ===================================================================
  const mobileToggle = document.querySelector('.mobile-menu-toggle');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');

  if (mobileToggle && sidebar && overlay) {
    mobileToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
  }

  // ===================================================================
  // 3. AJOUT DYNAMIQUE DE MODULES ET LEÇONS (avec Quill)
  // ===================================================================
  const addModuleBtn = document.getElementById('add-module');
  const modulesContainer = document.getElementById('modules-container');

  if (addModuleBtn && modulesContainer) {
    addModuleBtn.addEventListener('click', () => {
      moduleCount++;

      const moduleHTML = `
        <div class="module-card" data-module="${moduleCount}">
          <div class="module-header">
            <h3>Module ${moduleCount} <span class="module-title-preview">(sans titre)</span></h3>
            <div>
              <button type="button" class="btn-collapse"><i class="fas fa-chevron-down"></i></button>
              <button type="button" class="btn-remove-module"><i class="fas fa-trash"></i></button>
            </div>
          </div>

          <div class="module-content">
            <div class="form-group">
              <label>Titre du module <span class="required">*</span></label>
              <input type="text" name="modules[${moduleCount}][title]" required placeholder="Ex: Introduction à l'anglais professionnel">
            </div>

            <div class="form-group">
              <label>Description (facultatif)</label>
              <textarea name="modules[${moduleCount}][description]" rows="3" placeholder="Décrivez brièvement ce module..."></textarea>
            </div>

            <div class="lessons-list">
              <h4>Leçons du module</h4>
              <button type="button" class="btn-add-lesson" data-module="${moduleCount}">
                <i class="fas fa-plus"></i> Ajouter une leçon
              </button>
              <div class="lessons-container" data-module="${moduleCount}"></div>
            </div>
          </div>
        </div>
      `;

      modulesContainer.insertAdjacentHTML('beforeend', moduleHTML);
    });

    // Délégation d'événements
    modulesContainer.addEventListener('click', (e) => {
      const target = e.target.closest('button');
      if (!target) return;

      // Supprimer module
      if (target.classList.contains('btn-remove-module')) {
        target.closest('.module-card').remove();
        return;
      }

      // Collapse module
      if (target.classList.contains('btn-collapse')) {
        const content = target.closest('.module-header').nextElementSibling;
        const icon = target.querySelector('i');
        const isHidden = content.style.display === 'none' || !content.style.display;

        content.style.display = isHidden ? 'block' : 'none';
        icon.classList.toggle('fa-chevron-down', !isHidden);
        icon.classList.toggle('fa-chevron-up', isHidden);
        return;
      }

      // Ajouter une leçon
      if (target.classList.contains('btn-add-lesson')) {
        const moduleId = target.dataset.module;
        const lessonsContainer = target.closest('.lessons-list').querySelector('.lessons-container');
        const lessonCount = lessonsContainer.querySelectorAll('.lesson-item').length + 1;

        const lessonHTML = `
          <div class="lesson-item">
            <div class="lesson-header">
              <h5>Leçon ${lessonCount}</h5>
              <button type="button" class="btn-remove-lesson"><i class="fas fa-trash"></i></button>
            </div>

            <div class="form-group">
              <label>Titre de la leçon <span class="required">*</span></label>
              <input type="text" name="modules[${moduleId}][lessons][${lessonCount}][title]" required>
            </div>

            <div class="form-group">
              <label>Contenu de la leçon <span class="required">*</span></label>
              <div class="quill-editor" style="height: 300px;"></div>
              <input type="hidden" name="modules[${moduleId}][lessons][${lessonCount}][content]" class="lesson-content-hidden">
            </div>

            <div class="form-group">
              <label>URL de la vidéo (facultatif)</label>
              <input type="url" name="modules[${moduleId}][lessons][${lessonCount}][video_url]" placeholder="https://...">
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

        lessonsContainer.insertAdjacentHTML('beforeend', lessonHTML);

        // === INITIALISATION DE QUILL POUR CETTE LEÇON ===
        const quillContainer = lessonsContainer.lastElementChild.querySelector('.quill-editor');
        const hiddenInput = lessonsContainer.lastElementChild.querySelector('.lesson-content-hidden');

        const quill = new Quill(quillContainer, {
          theme: 'snow',
          modules: {
            toolbar: [
              ['bold', 'italic'],
              [{ 'header': 1 }, { 'header': 2 }],
              [{ 'list': 'ordered' }, { 'list': 'bullet' }],
              ['link', 'image'],
              ['clean']
            ]
          },
          placeholder: 'Rédigez le contenu de votre leçon ici...'
        });

        // Synchroniser avec le champ caché
        quill.on('text-change', () => {
          hiddenInput.value = quill.root.innerHTML;
        });
        hiddenInput.value = quill.root.innerHTML; // Valeur initiale
      }

      // Supprimer leçon
      if (target.classList.contains('btn-remove-lesson')) {
        target.closest('.lesson-item').remove();
      }
    });

    // Mise à jour du titre du module en live
    modulesContainer.addEventListener('input', (e) => {
      if (e.target.name && e.target.name.includes('[title]') && !e.target.name.includes('[lessons]')) {
        const moduleCard = e.target.closest('.module-card');
        const preview = moduleCard?.querySelector('.module-title-preview');
        if (preview) {
          preview.textContent = e.target.value ? `: ${e.target.value}` : '(sans titre)';
        }
      }
    });
  }

  // ===================================================================
  // 4. UPLOAD IMAGE DE COUVERTURE
  // ===================================================================
  const uploadArea = document.getElementById('upload-area');
  const fileInput = document.getElementById('profile_picture');
  const previewContainer = document.getElementById('preview-container');
  const previewImg = document.getElementById('image-preview');
  const removeBtn = document.getElementById('remove-image');

  if (uploadArea && fileInput && previewContainer && previewImg && removeBtn) {
    uploadArea.addEventListener('click', () => fileInput.click());

    ['dragover', 'dragenter'].forEach(event => {
      uploadArea.addEventListener(event, (e) => {
        e.preventDefault();
        uploadArea.style.background = 'rgba(99, 102, 241, 0.1)';
        uploadArea.style.borderColor = '#6366f1';
      });
    });

    ['dragleave', 'dragend', 'drop'].forEach(event => {
      uploadArea.addEventListener(event, (e) => {
        e.preventDefault();
        uploadArea.style.background = '';
        uploadArea.style.borderColor = '';
      });
    });

    uploadArea.addEventListener('drop', (e) => {
      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        previewImage(e.dataTransfer.files[0]);
      }
    });

    fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0) {
        previewImage(fileInput.files[0]);
      }
    });

    function previewImage(file) {
      if (file.size > 5 * 1024 * 1024) {
        alert('L\'image ne doit pas dépasser 5 Mo');
        fileInput.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        previewImg.src = e.target.result;
        previewContainer.classList.remove('preview-hidden');
        uploadArea.style.display = 'none';
      };
      reader.readAsDataURL(file);
    }

    removeBtn.addEventListener('click', () => {
      fileInput.value = '';
      previewContainer.classList.add('preview-hidden');
      uploadArea.style.display = 'block';
    });
  }

  // ===================================================================
  // 5. LOADING BOUTON SUBMIT FINAL
  // ===================================================================
  if (form && submitBtn) {
    form.addEventListener('submit', () => {
      submitBtn.classList.add('loading');
      submitBtn.disabled = true;
    });
  }
});