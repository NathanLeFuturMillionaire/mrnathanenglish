<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OpenDoorsClass - <?= htmlspecialchars($course['title_course']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../../public/css/users/courses/viewCourses.css">
</head>

<body>

  <?php require '../app/views/layouts/header.php'; ?>

  <main class="course-page" style="margin-top: 100px;">

    <!-- ===================== BANNIÈRE ===================== -->
    <section class="course-banner">

      <div class="course-banner__info">

        <h1 class="course-banner__title">
          <?= htmlspecialchars($course['title_course']) ?>
        </h1>

        <p class="course-banner__description">
          <?= htmlspecialchars($course['description_course']) ?>
        </p>

        <div class="course-banner__meta">
          <span>
            <i class="fas fa-clock"></i>
            <?= (int) ($course['time_course']) ?>h
          </span>
          <span>
            <i class="fas fa-tag"></i>
            <?= $course['is_free'] ? 'Gratuit' : number_format((float) $course['price_course'], 2, ',', ' ') . ' F CFA / mois' ?>
          </span>
          <span>
            <i class="fas fa-language"></i>
            <?= htmlspecialchars(ucfirst($course['language_taught'])) ?>
          </span>
          <span>
            <i class="fas fa-layer-group"></i>
            <?= htmlspecialchars(ucfirst($course['learner_level'])) ?>
          </span>
          <span>
            <i class="fas fa-calendar-alt"></i>
            Période de validation : <?= (int) $course['validation_period'] ?> jours
          </span>
          <span>
            <i class="fas fa-star"></i>
            <?= number_format((float) $course['course_rate'], 1) ?> / 5
          </span>
        </div>

      </div>

      <div class="course-banner__image">
        <?php if (!empty($course['profile_picture'])): ?>
          <img
            src="../..<?= htmlspecialchars($course['profile_picture']) ?>"
            alt="<?= htmlspecialchars($course['title_course']) ?>">
        <?php else: ?>
          <div class="course-banner__image--placeholder">
            <i class="fas fa-image"></i>
          </div>
        <?php endif; ?>
      </div>

    </section>

    <!-- ===================== MODULES ===================== -->
    <section class="course-modules">

      <h2 class="course-modules__title">Contenu du cours</h2>

      <?php
      $modules  = $course['content_data']['modules'] ?? [];
      $totalLessons = array_sum(array_map(fn($m) => count($m['lessons'] ?? []), $modules));
      ?>

      <p class="course-modules__summary">
        <?= count($modules) ?> module<?= count($modules) > 1 ? 's' : '' ?> &bull;
        <?= $totalLessons ?> leçon<?= $totalLessons > 1 ? 's' : '' ?>
      </p>

      <?php if (empty($modules)): ?>
        <p class="course-modules__empty">Aucun contenu disponible pour ce cours.</p>
      <?php else: ?>

        <div class="course-modules__list">
          <?php foreach ($modules as $moduleIndex => $module): ?>

            <div class="module-card">

              <div class="module-card__header" onclick="toggleModule(this)">
                <div class="module-card__header-top">
                  <div>
                    <div class="module-card__number">Module <?= $moduleIndex + 1 ?></div>
                    <h3 class="module-card__title">
                      <?= htmlspecialchars($module['title']) ?>
                    </h3>
                    <?php if (!empty($module['description'])): ?>
                      <p class="module-card__description">
                        <?= htmlspecialchars($module['description']) ?>
                      </p>
                    <?php endif; ?>
                    <span class="module-card__count">
                      <?= count($module['lessons'] ?? []) ?> leçon<?= count($module['lessons'] ?? []) > 1 ? 's' : '' ?>
                    </span>
                  </div>
                  <i class="fas fa-chevron-down module-card__chevron"></i>
                </div>
              </div>

              <?php if (!empty($module['lessons'])): ?>
                <div class="module-card__body">
                  <ul class="module-card__lessons">
                    <?php foreach ($module['lessons'] as $lessonIndex => $lesson): ?>

                      <?php
                      $lessonUrl = 'lesson/'
                        . (int) $course['id'] . '/'
                        . $moduleIndex . '/'
                        . $lessonIndex;
                      ?>

                      <li class="lesson-item">
                        <a href="<?= $lessonUrl ?>" class="lesson-item__link">

                          <div class="lesson-item__left">
                            <span class="lesson-item__index"><?= $moduleIndex + 1 ?>.<?= $lessonIndex + 1 ?></span>
                            <span class="lesson-item__title">
                              <?= htmlspecialchars($lesson['title']) ?>
                            </span>
                            <?php if (!empty($lesson['is_free'])): ?>
                              <span class="lesson-item__badge lesson-item__badge--free">Gratuit</span>
                            <?php else: ?>
                              <span class="lesson-item__badge lesson-item__badge--locked">
                                <i class="fas fa-lock"></i>
                              </span>
                            <?php endif; ?>
                          </div>

                          <div class="lesson-item__right">
                            <?php if (!empty($lesson['video_url'])): ?>
                              <i class="fas fa-play-circle lesson-item__icon lesson-item__icon--video"></i>
                            <?php endif; ?>
                            <?php if (!empty($lesson['duration'])): ?>
                              <span class="lesson-item__duration">
                                <i class="fas fa-clock"></i>
                                <?= (int) $lesson['duration'] ?> min
                              </span>
                            <?php endif; ?>
                          </div>

                        </a>
                      </li>

                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>

            </div>

          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    </section>

  </main>
  <script>
    function toggleModule(header) {
      const card = header.closest('.module-card');
      const body = card.querySelector('.module-card__body');

      if (!body) return;

      const isOpen = body.classList.contains('is-open');

      body.classList.toggle('is-open', !isOpen);
      header.classList.toggle('is-open', !isOpen);
    }

    // Ouvre le premier module par défaut
    document.addEventListener('DOMContentLoaded', () => {
      const first = document.querySelector('.module-card__header');
      if (first) toggleModule(first);
    });
  </script>
  <script src="../../js/main.js"></script>

  <!-- Bouton Airtel Money pour effectuer le paiement -->
  <?php if (!$course['is_free']): ?>
    <div class="payment-sticky">
      <div class="payment-sticky__inner">
        <div class="payment-sticky__info">
          <span class="payment-sticky__price">
            <?= number_format((float) $course['price_course'], 2, ',', ' ') ?> F CFA <small>/ mois</small>
          </span>
          <span class="payment-sticky__label">Accès illimité à tous les modules</span>
        </div>
        <button class="payment-sticky__btn" id="btn-airtel-pay">
          <!-- <img src="../../../public/img/airtel-money.png" alt="Airtel Money" class="payment-sticky__logo"> -->
          Payer avec Airtel Money
        </button>
      </div>
    </div>
  <?php endif; ?>

</body>

</html>