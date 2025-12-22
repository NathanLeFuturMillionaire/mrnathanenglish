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
    <!-- Quill CSS (thème snow = toolbar en haut) -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />

    <!-- Quill JS -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <link rel="stylesheet" href="../../public/css/admins/courses/createCourse.css"> <!-- On réutilise exactement le même CSS ! -->
</head>

<body>

    <!-- Bouton burger mobile -->
    <div class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar (identique au dashboard) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>OpenDoorsClass</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../dashboard"><i class="fas fa-chart-line"></i><span>Tableau de bord</span></a></li>
                <li><a href="../courses/create" class="active"><i class="fas fa-plus-circle"></i><span>Créer un cours</span></a></li>
                <li><a href="#"><i class="fas fa-users"></i><span>Mes étudiants</span></a></li>
                <li><a href="#"><i class="fas fa-book-open"></i><span>Mes formations</span></a></li>
            </ul>
        </nav>
    </aside>

    <!-- Overlay pour fermer le menu sur mobile -->
    <div class="sidebar-overlay"></div>

    <!-- Contenu principal -->
    <main class="main-content">
        <header class="page-header">
            <h1>Concevoir un nouveau cours chez OpenDoorsclass</h1>
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

        <form id="create-course-form" class="course-builder" method="POST" enctype="multipart/form-data">

            <!-- ====================== ÉTAPE 1 : Informations générales ====================== -->
            <section id="step-1" class="step-section active">
                <div class="section-card">
                    <h2><i class="fas fa-info-circle"></i> Étape 1 : Informations générales</h2>

                    <div class="form-grid">
                        <!-- ... (tout le contenu de la section 1 que tu avais déjà : titre, description, langue, niveau, durée, prix, professeur, image) ... -->
                        <!-- Je le remets identique -->
                        <div class="form-column">
                            <div class="form-group">
                                <label>Titre du cours <span class="required">*</span></label>
                                <input type="text" name="title_course" required placeholder="Ex: Anglais des affaires avancé">
                            </div>

                            <div class="form-group">
                                <label>Description <span class="required">*</span></label>
                                <textarea name="description_course" rows="5" required placeholder="Présentez votre cours..."></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Langue enseignée <span class="required">*</span></label>
                                    <select name="language_taught" required>
                                        <option value="">Choisir</option>
                                        <option value="anglais">Anglais</option>
                                        <option value="espagnol">Espagnol</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Niveau <span class="required">*</span></label>
                                    <select name="learner_level" required>
                                        <option value="">Choisir</option>
                                        <option value="débutant">Débutant</option>
                                        <option value="intermédiaire">Intermédiaire</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Durée estimée (heures)</label>
                                    <input type="number" name="time_course" min="1" placeholder="Ex: 20">
                                </div>
                                <div class="form-group">
                                    <label>Sur une période de</label>
                                    <input type="number" name="validation_period" required>
                                </div>
                                <div class="form-group">
                                    <label>Prix (F CFA) <span class="required">*</span></label>
                                    <input type="number" name="price_course" step="0.01" min="0" required placeholder="0 = gratuit">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Professeur responsable <span class="required">*</span></label>
                                <input type="text" name="teacher_course" required value="<?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <div class="form-column">
                            <div class="form-group">
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
                            </div>
                        </div>
                    </div>

                    <div class="form-actions step-actions">
                        <a href="myCourses.php" class="btn-cancel">Annuler</a>
                        <button type="button" id="next-step" class="btn-submit">
                            Suivant <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- ====================== ÉTAPE 2 : Modules & Leçons ====================== -->
            <section id="step-2" class="step-section">
                <div class="section-card">
                    <div class="section-header">
                        <h2><i class="fas fa-list-ol"></i> Étape 2 : Contenu du cours</h2>
                        <button type="button" id="add-module" class="btn-add">
                            <i class="fas fa-plus"></i> Ajouter un module
                        </button>
                    </div>

                    <div id="modules-container">
                        <!-- Modules ajoutés dynamiquement -->
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
                <input type="hidden" name="course_id" value="${document.getElementById('create-course-form').dataset.courseId}">
            </section>

        </form>
    </main>

    <script src="../js/admins/courses/createCourse.js"></script>
</body>