<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OpenDoorsClass - <?= htmlspecialchars($course['title_course']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../../public/css/users/courses/viewCourses.css">
</head>

<body>

  <?php require '../app/views/layouts/header.php'; ?>

  <main class="course-page" style="margin-top: 100px;">

    <!-- ===================== BANNIÈRE ===================== -->
    <section class="course-banner">

      <div class="course-banner__info">

        <!-- ===== FIL D'ARIANE ===== -->
        <nav class="breadcrumb" aria-label="Fil d'ariane">
          <a href="/mrnathanenglish/public/" class="breadcrumb__item">
            <i class="fas fa-home"></i> Accueil
          </a>
          <span class="breadcrumb__separator"><i class="fas fa-chevron-right"></i></span>
          <a href="/mrnathanenglish/public/courses" class="breadcrumb__item">
            Cours
          </a>
          <span class="breadcrumb__separator"><i class="fas fa-chevron-right"></i></span>
          <span class="breadcrumb__item breadcrumb__item--current">
            <?= htmlspecialchars($course['title_course']) ?>
          </span>
        </nav>

        <h1 class="course-banner__title">
          <?= htmlspecialchars($course['title_course']) ?>
        </h1>

        <p class="course-banner__description">
          <?= htmlspecialchars($course['description_course']) ?>
        </p>

        <div class="course-banner__meta">

          <span class="meta-tip" data-tip="Durée estimée pour valider l'intégralité de la formation et obtenir votre certificat.">
            <i class="fas fa-clock"></i>
            <?php
            $months = (int) $course['validation_period'] / 30;
            if ($months < 1) {
              echo (int) $course['validation_period'] . ' jour' . ($course['validation_period'] > 1 ? 's' : '');
            } elseif ($months < 12) {
              $m = round($months, 1);
              echo $m . ' mois';
            } else {
              $y = round($months / 12, 1);
              echo $y . ' an' . ($y >= 2 ? 's' : '');
            }
            ?>
          </span>

          <span class="meta-tip" data-tip="<?= $course['is_free'] ? 'Ce cours est entièrement gratuit — aucun abonnement requis.' : 'Montant mensuel facturé pour accéder à l\'ensemble des ressources pédagogiques de ce cours.' ?>">
            <i class="fas fa-tag"></i>
            <?= $course['is_free'] ? 'Gratuit' : number_format((float) $course['price_course'], 2, ',', ' ') . ' F CFA / mois' ?>
          </span>

          <span class="meta-tip" data-tip="Langue enseignée.">
            <i class="fas fa-language"></i>
            <?= htmlspecialchars(ucfirst($course['language_taught'])) ?>
          </span>

          <span class="meta-tip" data-tip="Niveau de compétence requis pour suivre ce cours dans les meilleures conditions.">
            <i class="fas fa-layer-group"></i>
            <?= htmlspecialchars(ucfirst($course['learner_level'])) ?>
          </span>

          <span class="meta-tip" data-tip="Date de la dernière révision du contenu pédagogique par le formateur.">
            <i class="fas fa-rotate"></i>
            <?= $updateLabel ?> <?= $lastUpdate ?>
          </span>

          <span class="meta-tip" data-tip="Note moyenne attribuée par les apprenants ayant suivi et évalué cette formation.">
            <i class="fas fa-star"></i>
            <?= number_format((float) $course['course_rate'], 1) ?> / 5
          </span>

        </div>
        <!-- ===== BOUTON SUIVRE ===== -->
        <div class="course-cta">
          <?php if ($course['is_free']): ?>
            <a href="#" class="btn-follow">
              <span class="btn-follow__icon"><i class="fas fa-graduation-cap"></i></span>
              <span class="btn-follow__text">
                <span class="btn-follow__label">Commencer gratuitement</span>
                <span class="btn-follow__sub">Accès immédiat · Sans engagement</span>
              </span>
              <i class="fas fa-arrow-right btn-follow__arrow"></i>
            </a>
          <?php else: ?>
            <a href="#" class="btn-follow">
              <span class="btn-follow__icon"><i class="fas fa-graduation-cap"></i></span>
              <span class="btn-follow__text">
                <span class="btn-follow__label">Suivre ce cours</span>
                <span class="btn-follow__sub">
                  <?= number_format((float) $course['price_course'], 2, ',', ' ') ?> F CFA / mois
                </span>
              </span>
              <i class="fas fa-arrow-right btn-follow__arrow"></i>
            </a>
          <?php endif; ?>
        </div>
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

    <!-- ===================== CE QUE VOUS ALLEZ APPRENDRE ===================== -->
    <div class="course-layout">
      <div class="course-main">
        <section class="course-outcomes">

          <h2 class="course-outcomes__title">Ce que vous allez apprendre</h2>
          <p class="course-outcomes__intro">
            En 6 mois de formation intensive, vous progresserez méthodiquement de la phonétique de base jusqu'aux structures avancées de la langue anglaise.
          </p>
          <?php
          $outcomes = $course['content_data']['outcomes'] ?? [];
          ?>

          <?php if (!empty($outcomes)): ?>
            <div class="course-outcomes__grid">
              <?php foreach ($outcomes as $outcome): ?>
                <div class="course-outcomes__item">
                  <i class="fas fa-circle-check"></i>
                  <span><?= htmlspecialchars($outcome) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p style="font-size:13px;color:#aaa;font-style:italic;">
              Aucun objectif renseigné pour ce cours.
            </p>
          <?php endif; ?>

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
      </div>
      <!-- ===== ASIDE STICKY ===== -->
      <aside class="course-aside">

        <?php if (!empty($course['profile_picture'])): ?>
          <div class="course-aside__image">
            <img src="../..<?= htmlspecialchars($course['profile_picture']) ?>"
              alt="<?= htmlspecialchars($course['title_course']) ?>" loading="lazy">
            <div class="course-aside__image-overlay"></div>
          </div>
        <?php endif; ?>

        <div class="course-aside__body">

          <!-- Titre & sous-titre -->
          <h3 class="course-aside__title"><?= htmlspecialchars($course['title_course']) ?></h3>
          <p class="course-aside__desc"><?= htmlspecialchars($course['description_course']) ?></p>

          <!-- Note -->
          <div class="course-aside__rating">
            <?php
            $rate = (float) $course['course_rate'];
            for ($i = 1; $i <= 5; $i++):
            ?>
              <i class="fas fa-star <?= $i <= round($rate) ? 'star--on' : 'star--off' ?>"></i>
            <?php endfor; ?>
            <span><?= number_format($rate, 1) ?> / 5</span>
          </div>

          <!-- Infos -->
          <ul class="course-aside__infos">
            <li>
              <i class="fas fa-clock"></i>
              <div>
                <span class="aside-info__label">Durée de la formation</span>
                <span class="aside-info__value">
                  <?php
                  $months = (int) $course['validation_period'] / 30;
                  if ($months < 1) echo (int) $course['validation_period'] . ' jours';
                  elseif ($months < 12) echo round($months, 1) . ' mois';
                  else echo round($months / 12, 1) . ' an(s)';
                  ?>
                </span>
              </div>
            </li>
            <li>
              <i class="fas fa-language"></i>
              <div>
                <span class="aside-info__label">Langue enseignée</span>
                <span class="aside-info__value"><?= htmlspecialchars(ucfirst($course['language_taught'])) ?></span>
              </div>
            </li>
            <li>
              <i class="fas fa-layer-group"></i>
              <div>
                <span class="aside-info__label">Niveau requis</span>
                <span class="aside-info__value"><?= htmlspecialchars(ucfirst($course['learner_level'])) ?></span>
              </div>
            </li>
            <li>
              <i class="fas fa-rotate"></i>
              <div>
                <span class="aside-info__label">Dernière mise à jour</span>
                <span class="aside-info__value"><?= $lastUpdate ?></span>
              </div>
            </li>
            <li>
              <i class="fas fa-book-open"></i>
              <div>
                <span class="aside-info__label">Nombre de leçons</span>
                <span class="aside-info__value"><?= $totalLessons ?> leçons · <?= count($modules) ?> modules</span>
              </div>
            </li>
            <li>
              <i class="fas fa-certificate"></i>
              <div>
                <span class="aside-info__label">Certificat inclus</span>
                <span class="aside-info__value">Oui, à l'issue de la formation</span>
              </div>
            </li>
            <li>
              <i class="fas fa-infinity"></i>
              <div>
                <span class="aside-info__label">Accès</span>
                <span class="aside-info__value">Illimité pendant toute la durée</span>
              </div>
            </li>
          </ul>

          <!-- Prix -->
          <div class="course-aside__price">
            <?php if ($course['is_free']): ?>
              <span class="aside-price__amount">Gratuit</span>
              <span class="aside-price__sub">Accès immédiat · Sans engagement</span>
            <?php else: ?>
              <span class="aside-price__amount">
                <?= number_format((float) $course['price_course'], 0, ',', ' ') ?> <small>F CFA</small>
              </span>
              <span class="aside-price__sub">par mois · résiliable à tout moment</span>
            <?php endif; ?>
          </div>

          <!-- Boutons -->
          <div class="course-aside__actions">
            <?php if (!$course['is_free']): ?>
              <a href="#" class="aside-btn aside-btn--pay">
                <i class="fas fa-mobile-screen"></i>
                Payer
              </a>
            <?php endif; ?>
            <a href="#" class="aside-btn aside-btn--start">
              <i class="fas fa-play"></i>
              <?= $course['is_free'] ? 'Commencer gratuitement' : 'Commencer la formation' ?>
            </a>
          </div>

          <!-- Garanties -->
          <ul class="course-aside__guarantees">
            <li><i class="fas fa-shield-halved"></i> Paiement 100% sécurisé</li>
            <li><i class="fas fa-headset"></i> Support disponible 7j/7</li>
            <li><i class="fas fa-rotate-left"></i> Remboursement sous 7 jours</li>
          </ul>

        </div>
      </aside>
    </div>

  </main>

  <script src="../../js/main.js"></script>
  <script src="../../js/viewCourse.js"></script>

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