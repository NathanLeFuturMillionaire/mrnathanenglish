<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenDoorsClass - Créer un cours</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/admins/courses/listCourses.css">
    <title>Cours - OpenDoorsClass</title>
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
                <li><a href="./dashboard"><i class="fas fa-chart-line"></i><span>Tableau de bord</span></a></li>
                <li><a href="./courses" class="active"><i class="fas fa-book"></i><span>Cours</span></a></li>
                <li><a href="#"><i class="fas fa-users"></i><span>Élèves</span></a></li>
                <li><a href="#"><i class="fas fa-book-open"></i><span>Formations</span></a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <h1>Formations</h1>
            <p class="subtitle">Gérez vos cours publiés et vos brouillons en cours</p>
        </header>

        <a href="./courses/create" class="btn-new-course">
            <i class="fas fa-plus-circle"></i>
            Créer
        </a>

        <?php if (empty($allCourses)): ?>
            <div style="text-align: center; padding: 60px 20px; color: #718096;">
                <i class="fas fa-book-open" style="font-size: 4rem; margin-bottom: 20px; color: #cbd5e0;"></i>
                <p style="font-size: 1.2rem;">Vous n'avez aucun cours en cours ou publié pour le moment.</p>
                <p>Commencez par en créer un nouveau !</p>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($allCourses as $course): ?>
                    <div class="course-card <?= ($course['is_draft'] ?? false) ? 'draft' : 'published' ?>">
                        <img src=".<?= htmlspecialchars($course['profile_picture'] ?? '/assets/img/default-course.jpg') ?>"
                            alt="<?= htmlspecialchars($course['title_course'] ?? 'Sans titre') ?>">

                        <div class="course-card-content">
                            <h3><?= htmlspecialchars($course['title_course'] ?? 'Sans titre') ?></h3>

                            <?php if ($course['is_draft'] ?? false): ?>
                                <span class="badge badge-draft">Brouillon</span>
                                <div class="actions">
                                    <a href="./courses/draft/<?= $course['draft_id'] ?>" class="btn-primary">
                                        <i class="fas fa-edit"></i> Reprendre
                                    </a>
                                    <a href="../courses/delete-draft/<?= $course['draft_id'] ?>"
                                        class="btn-danger"
                                        onclick="return confirm('Supprimer définitivement ce brouillon ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="badge badge-published">Publié</span>
                                <div class="actions">
                                    <a href="../courses/edit/<?= $course['id'] ?>" class="btn-primary">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="../courses/view/<?= $course['id'] ?>" class="btn-secondary">
                                        <i class="fas fa-eye"></i> Voir en ligne
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>