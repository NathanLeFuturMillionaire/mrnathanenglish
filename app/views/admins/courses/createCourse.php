<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenDoorsClass - Créer un cours</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Quill Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

    <link rel="stylesheet" href="../../public/css/admins/courses/createCourse.css">

</head>

<body>


    <!-- Bouton burger mobile -->
    <div class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>OpenDoorsClass</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../dashboard"><i class="fas fa-chart-line"></i><span>Tableau de bord</span></a></li>
                <li><a href="../courses" class="active"><i class="fas fa-book"></i><span>Cours</span></a></li>
                <li><a href="#"><i class="fas fa-users"></i><span>Élèves</span></a></li>
                <li><a href="#"><i class="fas fa-book-open"></i><span>Formations</span></a></li>
            </ul>
        </nav>
    </aside>

    <div class="sidebar-overlay"></div>

    <main class="main-content">
        <header class="page-header">
            <h1>Concevoir un nouveau cours chez OpenDoorsClass</h1>
            <p class="subtitle">Assistant de création en 2 étapes</p>
        </header>

        <!-- Indicateur d'étapes -->
        <div class="steps-indicator">
            <div class="step active" data-step="1">
                <span class="step-number">1</span>
                <span class="step-label">Informations générales</span>
            </div>
            <div class="step" data-step="2">
                <span class="step-number">2</span>
                <span class="step-label">Contenu du cours</span>
            </div>
        </div>

        <?php
        // Récupération du brouillon du formateur connecté
        $trainerId = $_SESSION['user']['id'] ?? 0; // À adapter si votre clé est différente
        $draft = $this->draftRepository->findByTrainer($trainerId);

        // Récupération sécurisée du brouillon
        $draftData = [];
        $courseInfos = [];
        $modules = [];
        $draftId = '';

        if ($draft && is_array($draft) && isset($draft['draft_data'])) {
            // Vérification que draft_data n'est pas null et est une chaîne valide
            if ($draft['draft_data'] !== null && is_string($draft['draft_data'])) {
                $decoded = json_decode($draft['draft_data'], true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $draftData = $decoded;
                    $courseInfos = $draftData['course_infos'] ?? [];
                    $modules = $draftData['modules'] ?? [];
                    $draftId = $draft['id'];
                }
                // Sinon, on garde les valeurs par défaut (vide)
            }
        }

        $courseId = $draft ? $draft['id'] : ''; // ID du brouillon, pas du cours final
        ?>

        <form id="create-course-form" class="course-builder" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="course_id" id="course_id_hidden" value="<?= htmlspecialchars($courseId) ?>">

            <!-- ====================== ÉTAPE 1 : Informations générales ====================== -->
            <section id="step-1" class="step-section active">
                <div class="section-card">
                    <h2><i class="fas fa-info-circle"></i> Étape 1 : Informations générales</h2>

                    <div class="form-grid">
                        <div class="form-column">
                            <div class="form-group" id="group-title_course">
                                <label>Titre du cours <span class="required">*</span></label>
                                <input type="text" name="title_course" required placeholder="Ex: Anglais des affaires avancé"
                                    value="<?= htmlspecialchars($courseInfos['title_course'] ?? '') ?>">
                                <div class="error-message" id="error-title_course"></div>
                            </div>

                            <div class="form-group" id="group-description_course">
                                <label>Description <span class="required">*</span></label>
                                <textarea name="description_course" rows="5" required placeholder="Présentez votre cours..."><?= htmlspecialchars($courseInfos['description_course'] ?? '') ?></textarea>
                                <div class="error-message" id="error-description_course"></div>
                            </div>

                            <div class="form-row">
                                <div class="form-group" id="group-language_taught">
                                    <label>Langue enseignée <span class="required">*</span></label>
                                    <select name="language_taught" required>
                                        <option value="">Choisir</option>
                                        <option value="anglais" <?= ($courseInfos['language_taught'] ?? '') === 'anglais' ? 'selected' : '' ?>>Anglais</option>
                                        <option value="espagnol" <?= ($courseInfos['language_taught'] ?? '') === 'espagnol' ? 'selected' : '' ?>>Espagnol</option>
                                    </select>
                                    <div class="error-message" id="error-language_taught"></div>
                                </div>

                                <div class="form-group" id="group-learner_level">
                                    <label>Niveau <span class="required">*</span></label>
                                    <select name="learner_level" required>
                                        <option value="">Choisir</option>
                                        <option value="débutant" <?= ($courseInfos['learner_level'] ?? '') === 'débutant' ? 'selected' : '' ?>>Débutant</option>
                                        <option value="intermédiaire" <?= ($courseInfos['learner_level'] ?? '') === 'intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                                    </select>
                                    <div class="error-message" id="error-learner_level"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Durée estimée (heures)</label>
                                    <input type="number" name="time_course" min="1" placeholder="Ex: 20"
                                        value="<?= htmlspecialchars($courseInfos['time_course'] ?? '') ?>">
                                    <div class="error-message" id="error-time_course"></div>
                                </div>

                                <div class="form-group" id="group-validation_period">
                                    <label>Sur une période de (jours) <span class="required">*</span></label>
                                    <input type="number" name="validation_period" min="1" required placeholder="Ex: 90"
                                        value="<?= htmlspecialchars($courseInfos['validation_period'] ?? '') ?>">
                                    <div class="error-message" id="error-validation_period"></div>
                                </div>

                                <div class="form-group" id="group-price_course">
                                    <label>Prix (F CFA) <span class="required">*</span></label>
                                    <input type="number" name="price_course" step="100" min="0" required placeholder="0 = gratuit"
                                        value="<?= htmlspecialchars($courseInfos['price_course'] ?? '') ?>">
                                    <div class="error-message" id="error-price_course"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Professeur responsable <span class="required">*</span></label>
                                <input type="text" name="teacher_course" readonly
                                    value="<?= htmlspecialchars($courseInfos['teacher_course'] ?? $_SESSION['user']['username'] ?? '') ?>">
                                <div class="error-message" id="error-teacher_course"></div>
                            </div>
                        </div>

                        <div class="form-column">
                            <div class="form-group" id="group-profile_picture">
                                <label>Image de couverture <span class="required">*</span></label>
                                <div class="upload-area" id="upload-area">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Cliquez ou glissez-déposez</p>
                                    <span>JPG, PNG • Max 5 Mo</span>
                                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required hidden>
                                </div>
                                <div id="preview-container" class="preview-hidden">
                                    <img id="image-preview" src="" alt="Prévisualisation">
                                    <button type="button" id="remove-image"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="error-message" id="error-profile_picture"></div>
                            </div>

                            <!-- Radios Gratuit / Payant -->
                            <div class="price-type-radio">
                                <label class="radio-label">
                                    <input type="radio" name="is_free" value="1" checked="<?= ($courseInfos['is_free'] ?? 0) == 1 ? 'checked' : '' ?>">
                                    <span class="radio-custom"></span> Gratuit
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="is_free" value="0" checked="<?= ($courseInfos['is_free'] ?? 0) == 0 ? 'checked' : '' ?>">
                                    <span class="radio-custom"></span> Payant
                                </label>
                            </div>

                            <!-- Checkbox Publier immédiatement -->
                            <!-- <div class="form-group checkbox-group" style="justify-content: center; margin-top: 30px;">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="publish_now" value="1"
                                           <?= ($courseInfos['publish_now'] ?? 0) == 1 ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    Publier immédiatement le cours
                                </label>
                            </div> -->
                        </div>
                    </div>

                    <div class="form-actions step-actions">
                        <a href="../myCourses" class="btn-cancel">Annuler</a>
                        <button type="button" id="next-step" class="btn-submit">
                            Suivant <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- ====================== ÉTAPE 2 : Contenu du cours ====================== -->
            <section id="step-2" class="step-section">
                <div class="section-card">
                    <div class="section-header">
                        <h2><i class="fas fa-list-ol"></i> Étape 2 : Contenu du cours</h2>
                        <button type="button" id="add-module" class="btn-add">
                            <i class="fas fa-plus"></i> Ajouter un module
                        </button>
                    </div>

                    <div id="modules-container">
                        <?php if (!empty($modules)): ?>
                            <?php foreach ($modules as $moduleIndex => $module): ?>
                                <div class="module-card" data-module="<?= $moduleIndex + 1 ?>">
                                    <div class="module-header">
                                        <h3>Module <?= $moduleIndex + 1 ?> <span class="module-title-preview">: <?= htmlspecialchars($module['title'] ?? '(sans titre)') ?></span></h3>
                                        <div>
                                            <button type="button" class="btn-collapse"><i class="fas fa-chevron-down"></i></button>
                                            <button type="button" class="btn-remove-module"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>

                                    <div class="module-content">
                                        <div class="form-group">
                                            <label>Titre du module <span class="required">*</span></label>
                                            <input type="text" name="modules[<?= $moduleIndex + 1 ?>][title]" required value="<?= htmlspecialchars($module['title'] ?? '') ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Description (facultatif)</label>
                                            <textarea name="modules[<?= $moduleIndex + 1 ?>][description]" rows="3"><?= htmlspecialchars($module['description'] ?? '') ?></textarea>
                                        </div>

                                        <div class="lessons-list">
                                            <h4>Leçons du module</h4>
                                            <button type="button" class="btn-add-lesson" data-module="<?= $moduleIndex + 1 ?>">
                                                <i class="fas fa-plus"></i> Ajouter une leçon
                                            </button>
                                            <div class="lessons-container" data-module="<?= $moduleIndex + 1 ?>">
                                                <?php if (!empty($module['lessons'])): ?>
                                                    <?php foreach ($module['lessons'] as $lessonIndex => $lesson): ?>
                                                        <div class="lesson-item">
                                                            <div class="lesson-header">
                                                                <h5>Leçon <?= $lessonIndex + 1 ?></h5>
                                                                <button type="button" class="btn-remove-lesson"><i class="fas fa-trash"></i></button>
                                                            </div>

                                                            <div class="form-group">
                                                                <label>Titre de la leçon <span class="required">*</span></label>
                                                                <input type="text" name="modules[<?= $moduleIndex + 1 ?>][lessons][<?= $lessonIndex + 1 ?>][title]" required value="<?= htmlspecialchars($lesson['title'] ?? '') ?>">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>Contenu de la leçon <span class="required">*</span></label>
                                                                <div class="quill-editor" style="height: 320px;"><?= $lesson['content'] ?? '' ?></div>
                                                                <input type="hidden" name="modules[<?= $moduleIndex + 1 ?>][lessons][<?= $lessonIndex + 1 ?>][content]" class="lesson-content-hidden" value="<?= htmlspecialchars($lesson['content'] ?? '') ?>">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>URL de la vidéo (facultatif)</label>
                                                                <input type="url" name="modules[<?= $moduleIndex + 1 ?>][lessons][<?= $lessonIndex + 1 ?>][video_url]" value="<?= htmlspecialchars($lesson['video_url'] ?? '') ?>">
                                                            </div>

                                                            <div class="form-row">
                                                                <div class="form-group">
                                                                    <label>Durée (minutes)</label>
                                                                    <input type="number" name="modules[<?= $moduleIndex + 1 ?>][lessons][<?= $lessonIndex + 1 ?>][duration]" min="1" value="<?= htmlspecialchars($lesson['duration'] ?? '') ?>">
                                                                </div>
                                                                <div class="form-group checkbox-group">
                                                                    <label class="checkbox-label">
                                                                        <input type="checkbox" name="modules[<?= $moduleIndex + 1 ?>][lessons][<?= $lessonIndex + 1 ?>][is_free]" <?= ($lesson['is_free'] ?? 0) == 1 ? 'checked' : '' ?>>
                                                                        <span class="checkmark"></span> Leçon gratuite (aperçu)
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <p class="info-text">Ajoutez les modules et leçons qui composent votre formation.</p>

                    <div class="form-actions step-actions">
                        <button type="button" id="prev-step" class="btn-cancel">
                            <i class="fas fa-arrow-left"></i> Précédent
                        </button>
                        <button type="submit" class="btn-submit">
                            <span class="btn-text">Créer le cours complet</span>
                            <span class="spinner"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </div>
                </div>
            </section>
        </form>
    </main>

    <!-- Script pour restaurer l'image de couverture après rafraîchissement -->
    <?php if (!empty($courseInfos['profile_picture'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const previewImg = document.getElementById('image-preview');
                const previewContainer = document.getElementById('preview-container');
                const uploadArea = document.getElementById('upload-area');
                const fileInput = document.getElementById('profile_picture');

                if (previewImg && previewContainer && uploadArea && fileInput) {
                    previewImg.src = "../<?= htmlspecialchars($courseInfos['profile_picture']) ?>";
                    previewContainer.classList.remove('preview-hidden');
                    uploadArea.style.display = 'none';
                    fileInput.removeAttribute('required');
                }
            });
        </script>
    <?php endif; ?>

    <!-- Initialisation de Quill pour les leçons existantes -->
    <?php if (!empty($modules)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.quill-editor').forEach((editorEl, index) => {
                    const hiddenInput = editorEl.nextElementSibling;

                    const quill = new Quill(editorEl, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                ['bold', 'italic'],
                                [{
                                    'header': 1
                                }, {
                                    'header': 2
                                }],
                                [{
                                    'list': 'ordered'
                                }, {
                                    'list': 'bullet'
                                }],
                                ['link', 'image'],
                                ['clean']
                            ]
                        }
                    });

                    // Charger le contenu existant
                    quill.root.innerHTML = hiddenInput.value;

                    quill.on('text-change', () => {
                        hiddenInput.value = quill.root.innerHTML;
                    });
                });
            });
        </script>
    <?php endif; ?>

    <script src="../../public/js/admins/courses/createCourse.js"></script>
</body>

</html>