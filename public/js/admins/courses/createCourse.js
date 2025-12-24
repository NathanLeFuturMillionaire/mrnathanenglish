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
  const modulesContainer = document.getElementById('modules-container');
  const addModuleBtn = document.getElementById('add-module');

  let moduleCount = 0;

  // ===================================================================
  // 1. AJAX : Sauvegarde du brouillon (étape 1) + Gestion des erreurs par champ
  // ===================================================================
  if (nextBtn && step1 && step2) {
    nextBtn.addEventListener('click', (e) => {
      e.preventDefault();

      // Réinitialiser les erreurs précédentes
      document.querySelectorAll('.error-message').forEach(el => {
        el.innerHTML = '';
        el.closest('.form-group')?.classList.remove('has-error');
      });

      const formData = new FormData(form);

      nextBtn.disabled = true;
      nextBtn.classList.add('loading');

      fetch('../courses/create', {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(response => {
          if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          nextBtn.disabled = false;
          nextBtn.classList.remove('loading');

          if (data.success) {
            // Passage à l'étape 2
            step1.classList.remove('active');
            step2.classList.add('active');
            steps[0].classList.remove('active');
            steps[1].classList.add('active');

            const draftId = data.draft_id;

            // Stocker l'ID du brouillon
            document.getElementById('course_id_hidden').value = draftId;
            form.dataset.draftId = draftId;

            // Message de succès
            // alert(data.message || 'Brouillon sauvegardé avec succès !');

            // Scroll doux vers l'ajout de module
            if (addModuleBtn) {
              addModuleBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          } else {
            // === AFFICHAGE DES ERREURS SOUS CHAQUE CHAMP ===
            if (data.errors && Array.isArray(data.errors)) {
              data.errors.forEach(error => {
                let fieldId = null;

                // Mapping intelligent des messages d'erreur aux champs
                const lowerError = error.toLowerCase();
                if (lowerError.includes('titre')) fieldId = 'title_course';
                else if (lowerError.includes('description')) fieldId = 'description_course';
                else if (lowerError.includes('langue')) fieldId = 'language_taught';
                else if (lowerError.includes('niveau')) fieldId = 'learner_level';
                else if (lowerError.includes('période') || lowerError.includes('validation')) fieldId = 'validation_period';
                else if (lowerError.includes('prix')) fieldId = 'price_course';
                else if (lowerError.includes('image') || lowerError.includes('couverture')) fieldId = 'profile_picture';

                if (fieldId) {
                  const errorEl = document.getElementById('error-' + fieldId);
                  const groupEl = document.getElementById('group-' + fieldId);
                  if (errorEl && groupEl) {
                    errorEl.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${error}`;
                    groupEl.classList.add('has-error');
                  }
                }
              });
            } else {
              alert(data.message || 'Une erreur est survenue.');
            }
          }
        })
        .catch(err => {
          nextBtn.disabled = false;
          nextBtn.classList.remove('loading');
          // alert('Erreur de connexion. Veuillez vérifier votre réseau.');
          console.error('Erreur AJAX :', err);
        });
    });
  }

  // Bouton Précédent
  if (prevBtn) {
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
  // 3. AJOUT DYNAMIQUE DE MODULES ET LEÇONS + QUILL
  // ===================================================================
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

      // === SUPPRESSION D'UN MODULE + RENUMÉROTATION ===
      if (target.classList.contains('btn-remove-module')) {
        if (confirm('Supprimer ce module et toutes ses leçons ?')) {
          target.closest('.module-card').remove();

          // === RENUMÉROTATION DES MODULES RESTANTS ===
          const remainingModules = modulesContainer.querySelectorAll('.module-card');
          remainingModules.forEach((module, index) => {
            const newIndex = index + 1;

            // Mettre à jour data-module
            module.dataset.module = newIndex;

            // Mettre à jour le titre affiché
            module.querySelector('.module-header h3').innerHTML =
              `Module ${newIndex} <span class="module-title-preview">${module.querySelector('.module-title-preview').textContent}</span>`;

            // Mettre à jour les inputs du module
            module.querySelector('input[name*="title"]').name = `modules[${newIndex}][title]`;
            module.querySelector('textarea[name*="description"]').name = `modules[${newIndex}][description]`;

            // Mettre à jour le bouton "Ajouter une leçon"
            const addLessonBtn = module.querySelector('.btn-add-lesson');
            if (addLessonBtn) addLessonBtn.dataset.module = newIndex;

            // Mettre à jour les leçons existantes
            const lessons = module.querySelectorAll('.lesson-item');
            lessons.forEach((lesson, lessonIndex) => {
              const newLessonIndex = lessonIndex + 1;

              lesson.querySelector('.lesson-header h5').textContent = `Leçon ${newLessonIndex}`;

              // Inputs de la leçon
              lesson.querySelector('input[name*="title"]').name = `modules[${newIndex}][lessons][${newLessonIndex}][title]`;
              lesson.querySelector('input[name*="content"]').name = `modules[${newIndex}][lessons][${newLessonIndex}][content]`;
              lesson.querySelector('input[name*="video_url"]').name = `modules[${newIndex}][lessons][${newLessonIndex}][video_url]`;
              lesson.querySelector('input[name*="duration"]').name = `modules[${newIndex}][lessons][${newLessonIndex}][duration]`;
              lesson.querySelector('input[name*="is_free"]').name = `modules[${newIndex}][lessons][${newLessonIndex}][is_free]`;
            });
          });

          // Mettre à jour le compteur global
          moduleCount = remainingModules.length;
        }
        return;
      }

      // === COLLAPSE / EXPAND ===
      if (target.classList.contains('btn-collapse')) {
        const content = target.closest('.module-header').nextElementSibling;
        const icon = target.querySelector('i');
        const isHidden = content.style.display === 'none' || !content.style.display;

        content.style.display = isHidden ? 'block' : 'none';
        icon.classList.toggle('fa-chevron-down', !isHidden);
        icon.classList.toggle('fa-chevron-up', isHidden);
        return;
      }

      // === AJOUT D'UNE LEÇON ===
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
            <div class="quill-editor" style="height: 320px;"></div>
            <input type="hidden" name="modules[${moduleId}][lessons][${lessonCount}][content]" class="lesson-content-hidden">
          </div>

          <div class="form-group">
            <label>URL de la vidéo (facultatif)</label>
            <input type="url" name="modules[${moduleId}][lessons][${lessonCount}][video_url]" placeholder="https://youtube.com/...">
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

        // Initialisation Quill
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
          placeholder: 'Rédigez le contenu détaillé de votre leçon ici...'
        });

        quill.on('text-change', () => {
          hiddenInput.value = quill.root.innerHTML;
        });
        hiddenInput.value = quill.root.innerHTML;
      }

      // === SUPPRESSION D'UNE LEÇON ===
      if (target.classList.contains('btn-remove-lesson')) {
        if (confirm('Supprimer cette leçon ?')) {
          target.closest('.lesson-item').remove();

          // Rénuméroter les leçons restantes dans ce module
          const lessonsContainer = target.closest('.lessons-list').querySelector('.lessons-container');
          const lessons = lessonsContainer.querySelectorAll('.lesson-item');
          const moduleId = target.closest('.module-card').dataset.module;

          lessons.forEach((lesson, index) => {
            const newIndex = index + 1;
            lesson.querySelector('.lesson-header h5').textContent = `Leçon ${newIndex}`;

            lesson.querySelector('input[name*="title"]').name = `modules[${moduleId}][lessons][${newIndex}][title]`;
            lesson.querySelector('input[name*="content"]').name = `modules[${moduleId}][lessons][${newIndex}][content]`;
            lesson.querySelector('input[name*="video_url"]').name = `modules[${moduleId}][lessons][${newIndex}][video_url]`;
            lesson.querySelector('input[name*="duration"]').name = `modules[${moduleId}][lessons][${newIndex}][duration]`;
            lesson.querySelector('input[name*="is_free"]').name = `modules[${moduleId}][lessons][${newIndex}][is_free]`;
          });
        }
      }
    });

    // Mise à jour du titre du module en live
    modulesContainer.addEventListener('input', (e) => {
      if (e.target.name && e.target.name.includes('[title]') && !e.target.name.includes('[lessons]')) {
        const moduleCard = e.target.closest('.module-card');
        const preview = moduleCard.querySelector('.module-title-preview');
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

    uploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadArea.style.background = 'rgba(99, 102, 241, 0.1)';
      uploadArea.style.borderColor = '#6366f1';
    });

    uploadArea.addEventListener('dragleave', () => {
      uploadArea.style.background = '';
      uploadArea.style.borderColor = '';
    });

    uploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadArea.style.background = '';
      uploadArea.style.borderColor = '';
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
      fileInput.setAttribute('required', 'required');
    });
  }

  // ===================================================================
  // 5. SYNCHRONISATION GRATUIT / PAYANT
  // ===================================================================
  const priceRadios = document.querySelectorAll('input[name="is_free"]');
  const priceInput = document.querySelector('input[name="price_course"]');

  if (priceRadios.length && priceInput) {
    const updatePriceField = () => {
      if (priceRadios[0].checked) {
        priceInput.value = '0';
        priceInput.disabled = true;
        priceInput.placeholder = 'Gratuit (0 F CFA)';
      } else {
        priceInput.disabled = false;
        priceInput.placeholder = 'Ex: 15000';
        if (priceInput.value === '0') priceInput.value = '';
      }
    };

    priceRadios.forEach(radio => radio.addEventListener('change', updatePriceField));
    updatePriceField();
  }

  // ===================================================================
  // 6. LOADING SOUMISSION FINALE
  // ===================================================================
  if (form && submitBtn) {
    form.addEventListener('submit', () => {
      submitBtn.classList.add('loading');
      submitBtn.disabled = true;
    });
  }
});