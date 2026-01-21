<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formations – OpenDoorsClass</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="../public/css/admins/courses/listCourses.css">
</head>

<body>

    <!-- Menu mobile -->
    <button class="mobile-menu-toggle" aria-label="Ouvrir le menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>OpenDoorsClass</h2>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="../public/dashboard">
                        <i class="fas fa-chart-line"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="../public/courses" class="active">
                        <i class="fas fa-book"></i>
                        <span>Cours</span>
                    </a>
                </li>
                <li>
                    <a href="../public/students">
                        <i class="fas fa-users"></i>
                        <span>Élèves</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Formations</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">

        <header class="page-header">
            <h1>Formations</h1>
            <p class="subtitle">Cours publiés et brouillons en cours</p>
            <a href="../public/courses/create" class="btn-new-course">
                <i class="fas fa-plus-circle"></i>
                Créer un cours
            </a>
        </header>

        <?php if (empty($allCourses)): ?>
            <section class="empty-state">
                <i class="fas fa-book-open"></i>
                <p><a href="../public/courses/create">Commencer à créer votre premier cours</a></p>
            </section>
        <?php else: ?>
            <section class="courses-grid">
                <?php foreach ($allCourses as $course): ?>
                    <?php
                    $isDraft = !empty($course['is_draft']);
                    $courseId = $isDraft ? $course['draft_id'] : $course['id'];
                    ?>

                    <article class="course-card <?= $isDraft ? 'draft' : 'published' ?>">
                        <img
                            src=".<?= htmlspecialchars($course['profile_picture']) ?>"
                            alt="Illustration du cours <?= htmlspecialchars($course['title_course']) ?>"
                            loading="lazy">

                        <div class="course-card-content">
                            <h3><?= htmlspecialchars($course['title_course']) ?></h3>

                            <span class="badge <?= $isDraft ? 'badge-draft' : 'badge-published' ?>">
                                <?= $isDraft ? 'Brouillon' : 'Publié' ?>
                            </span>

                            <div class="course-description">
                                <p>
                                    <?php
                                    $description = html_entity_decode($course['description_course'] ?? '');
                                    $excerpt = mb_strimwidth($description, 0, 100, '…', 'UTF-8');
                                    echo nl2br(htmlspecialchars($excerpt));
                                    ?>
                                </p>
                            </div>

                            <div class="actions">
                                <?php if ($isDraft): ?>
                                    <a
                                        href="../public/courses/create?id=<?= (int) $courseId ?>"
                                        class="btn-primary">
                                        <i class="fas fa-edit"></i> Reprendre
                                    </a>

                                    <a
                                        href="../public/courses/delete-draft?id=<?= (int) $courseId ?>"
                                        class="btn-danger"
                                        onclick="return confirm('Supprimer définitivement ce brouillon ?');">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>

                                <?php else: ?>
                                    <a href="../public/courses/edit?id=<?= (int) $courseId ?>" class="btn-primary">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="/courses/view/<?= (int) $courseId ?>" class="btn-secondary">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

    </main>

</body>

</html>