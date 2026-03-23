<?php
if (!isset($_SESSION['user']['id'])) {
  header('Location: ./login');
  exit;
}

$levelLabels = [
  'débutant'      => 'Débutant',
  'intermédiaire' => 'Intermédiaire',
];
$langLabels = [
  'anglais'  => 'Anglais',
  'espagnol' => 'Espagnol',
];
$langFlags = [
  'anglais'  => '🇬🇧',
  'espagnol' => '🇪🇸',
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catalogue — OpenDoorsClass</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Lora:ital,wght@0,400;0,500;0,600;1,400;1,500&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../public/css/style.min.css">
  <link rel="stylesheet" href="../public/css/users/courses/list.css">
</head>

<body class="cl-page">

  <?php require_once '../app/views/layouts/header.php'; ?>

  <!-- ======================================================
     HERO
====================================================== -->
  <section class="cl-hero">

    <!-- Mesh animé -->
    <div class="cl-hero__mesh" aria-hidden="true">
      <div class="mesh-orb mesh-orb--1"></div>
      <div class="mesh-orb mesh-orb--2"></div>
      <div class="mesh-orb mesh-orb--3"></div>
    </div>

    <!-- Grille décorative -->
    <div class="cl-hero__grid" aria-hidden="true"></div>

    <div class="cl-hero__inner">

      <div class="cl-hero__content">

        <div class="cl-hero__pill">
          <span class="pill-dot"></span>
          Catalogue · <?= $totalCourses ?> formation<?= $totalCourses > 1 ? 's' : '' ?> disponible<?= $totalCourses > 1 ? 's' : '' ?>
        </div>

        <h1 class="cl-hero__title">
          Parlez anglais avec<br>
          <em class="cl-hero__title-em">confiance</em>
        </h1>

        <p class="cl-hero__sub">
          Des formations intensives et structurées, conçues depuis le Gabon
          pour propulser votre anglais vers un niveau d'excellence.
        </p>

        <div class="cl-hero__stats">
          <?php
          $stats = [
            ['value' => $totalCourses . '+',  'label' => 'Cours',          'icon' => 'fa-book-open'],
            ['value' => '6',                   'label' => 'Mois de suivi',  'icon' => 'fa-calendar'],
            ['value' => '100%',                'label' => 'En ligne',       'icon' => 'fa-wifi'],
          ];
          foreach ($stats as $i => $stat): ?>
            <div class="cl-stat" style="animation-delay:<?= 0.1 + $i * 0.1 ?>s">
              <i class="fas <?= $stat['icon'] ?> cl-stat__icon"></i>
              <div>
                <span class="cl-stat__value"><?= $stat['value'] ?></span>
                <span class="cl-stat__label"><?= $stat['label'] ?></span>
              </div>
            </div>
            <?php if ($i < count($stats) - 1): ?>
              <div class="cl-stat__sep" aria-hidden="true"></div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>

      </div>

      <!-- Carte flottante décorative -->
      <div class="cl-hero__deco" aria-hidden="true">
        <div class="deco-card deco-card--main">
          <div class="deco-card__header">
            <div class="deco-card__avatar">MN</div>
            <div>
              <div class="deco-card__name">Mr Nathan</div>
              <div class="deco-card__role">Formateur · Anglais</div>
            </div>
            <div class="deco-card__live">
              <span class="live-dot"></span> Live
            </div>
          </div>
          <div class="deco-card__bar">
            <div class="deco-bar__label">
              <span>Progression</span><span>78%</span>
            </div>
            <div class="deco-bar__track">
              <div class="deco-bar__fill"></div>
            </div>
          </div>
          <div class="deco-card__tags">
            <span>Grammar</span><span>Speaking</span><span>Business</span>
          </div>
        </div>

        <div class="deco-card deco-card--mini deco-card--mini-1">
          <i class="fas fa-trophy"></i>
          <div>
            <div class="deco-mini__val">+2 430</div>
            <div class="deco-mini__lab">Étudiants</div>
          </div>
        </div>

        <div class="deco-card deco-card--mini deco-card--mini-2">
          <i class="fas fa-star"></i>
          <div>
            <div class="deco-mini__val">4.9 / 5</div>
            <div class="deco-mini__lab">Note moyenne</div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ======================================================
     BARRE FILTRE
====================================================== -->
  <div class="cl-filterbar" id="cl-filterbar">
    <div class="cl-filterbar__inner">

      <form method="GET" action="" id="filter-form" class="cl-filterbar__form">

        <!-- Search -->
        <label class="cl-search">
          <i class="fas fa-magnifying-glass cl-search__ico"></i>
          <input
            type="text"
            name="search"
            id="cl-search-input"
            class="cl-search__input"
            placeholder="Rechercher une formation..."
            value="<?= htmlspecialchars($search) ?>"
            autocomplete="off">
          <button
            type="button"
            class="cl-search__clear <?= $search ? '' : 'is-hidden' ?>"
            id="cl-search-clear"
            aria-label="Effacer">
            <i class="fas fa-xmark"></i>
          </button>
        </label>

        <!-- Filtres select -->
        <div class="cl-selects">
          <label class="cl-select-wrap">
            <i class="fas fa-signal"></i>
            <select name="level" class="cl-select" onchange="submitFilters()">
              <option value="">Tout niveau</option>
              <option value="débutant" <?= $level === 'débutant'      ? 'selected' : '' ?>>Débutant</option>
              <option value="intermédiaire" <?= $level === 'intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
            </select>
            <i class="fas fa-chevron-down cl-select-wrap__arrow"></i>
          </label>

          <label class="cl-select-wrap">
            <i class="fas fa-globe"></i>
            <select name="language" class="cl-select" onchange="submitFilters()">
              <option value="">Toutes langues</option>
              <option value="anglais" <?= $language === 'anglais'  ? 'selected' : '' ?>>🇬🇧 Anglais</option>
              <option value="espagnol" <?= $language === 'espagnol' ? 'selected' : '' ?>>🇪🇸 Espagnol</option>
            </select>
            <i class="fas fa-chevron-down cl-select-wrap__arrow"></i>
          </label>

          <label class="cl-select-wrap">
            <i class="fas fa-tag"></i>
            <select name="free" class="cl-select" onchange="submitFilters()">
              <option value="">Tous les prix</option>
              <option value="1" <?= $free === '1' ? 'selected' : '' ?>>Gratuit</option>
              <option value="0" <?= $free === '0' ? 'selected' : '' ?>>Premium</option>
            </select>
            <i class="fas fa-chevron-down cl-select-wrap__arrow"></i>
          </label>

          <label class="cl-select-wrap">
            <i class="fas fa-arrow-up-wide-short"></i>
            <select name="sort" class="cl-select" onchange="submitFilters()">
              <option value="recent" <?= $sort === 'recent'     ? 'selected' : '' ?>>Récents</option>
              <option value="popular" <?= $sort === 'popular'    ? 'selected' : '' ?>>Populaires</option>
              <option value="rating" <?= $sort === 'rating'     ? 'selected' : '' ?>>Mieux notés</option>
              <option value="price_asc" <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Prix ↑</option>
              <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix ↓</option>
            </select>
            <i class="fas fa-chevron-down cl-select-wrap__arrow"></i>
          </label>
        </div>

        <!-- Compteur + reset -->
        <div class="cl-filterbar__meta">
          <span class="cl-count">
            <strong><?= $totalCourses ?></strong>
            résultat<?= $totalCourses > 1 ? 's' : '' ?>
          </span>
          <?php if ($search || $level || $language || $free !== ''): ?>
            <a href="./courses" class="cl-reset">
              <i class="fas fa-xmark"></i> Effacer
            </a>
          <?php endif; ?>
        </div>

      </form>
    </div>
  </div>

  <!-- ======================================================
     CATALOGUE
====================================================== -->
  <main class="cl-main">
    <div class="cl-main__inner">

      <?php if (empty($courses)): ?>

        <!-- Vide -->
        <div class="cl-empty">
          <div class="cl-empty__visual">
            <i class="fas fa-face-sad-sweat"></i>
          </div>
          <h2 class="cl-empty__title">Aucune formation trouvée</h2>
          <p class="cl-empty__desc">Modifiez vos filtres ou explorez tout le catalogue.</p>
          <a href="./courses" class="cl-empty__btn">
            <i class="fas fa-compass"></i> Explorer le catalogue
          </a>
        </div>

      <?php else: ?>

        <div class="cl-grid">
          <?php foreach ($courses as $i => $course):
            $isEnrolled = (int) $course['is_enrolled'] === 1;
            $isFree     = (int) $course['is_free']     === 1;
            $rating     = (float) $course['course_rate'];
            $price      = number_format((float) $course['price_course'], 0, ',', ' ');

            // Étoiles
            $stars = '';
            if ($rating > 0) {
              for ($s = 0; $s < floor($rating); $s++) $stars .= '<i class="fas fa-star"></i>';
              if ($rating - floor($rating) >= 0.5) $stars .= '<i class="fas fa-star-half-stroke"></i>';
            }

            $delay = ($i % 3) * 0.08;
          ?>
            <article
              class="cl-card <?= $isEnrolled ? 'cl-card--enrolled' : '' ?>"
              style="animation-delay: <?= $delay ?>s">
              <!-- ===== THUMB ===== -->
              <div class="cl-card__thumb">

                <?php if (!empty($course['profile_picture'])): ?>
                  <img
                    src=".<?= htmlspecialchars($course['profile_picture']) ?>"
                    alt="<?= htmlspecialchars($course['title_course']) ?>"
                    loading="lazy"
                    class="cl-card__img"
                    onerror="this.style.display='none'">
                <?php endif; ?>

                <!-- Overlay gradient -->
                <div class="cl-card__thumb-overlay"></div>

                <!-- Top badges -->
                <div class="cl-card__top-badges">
                  <?php if ($isFree): ?>
                    <span class="cl-badge cl-badge--free">
                      <i class="fas fa-gift"></i> Gratuit
                    </span>
                  <?php else: ?>
                    <span class="cl-badge cl-badge--premium">
                      <i class="fas fa-crown"></i> Premium
                    </span>
                  <?php endif; ?>

                  <?php if ($isEnrolled): ?>
                    <span class="cl-badge cl-badge--enrolled">
                      <i class="fas fa-check-circle"></i> Inscrit
                    </span>
                  <?php endif; ?>
                </div>

                <!-- Durée -->
                <div class="cl-card__duration">
                  <i class="fas fa-clock"></i>
                  <?= htmlspecialchars($course['duration_label']) ?>
                </div>

                <!-- Hover CTA -->
                <div class="cl-card__hover-cta">
                  <a href="./courses/view/<?= $course['id'] ?>" class="cl-card__hover-btn">
                    <?php if ($isEnrolled): ?>
                      <i class="fas fa-play"></i> Reprendre
                    <?php else: ?>
                      <i class="fas fa-eye"></i> Voir le cours
                    <?php endif; ?>
                  </a>
                </div>

              </div>

              <!-- ===== BODY ===== -->
              <div class="cl-card__body">

                <!-- Méta -->
                <div class="cl-card__meta">
                  <span class="cl-meta-chip cl-meta-chip--level">
                    <i class="fas fa-chart-simple"></i>
                    <?= htmlspecialchars($levelLabels[$course['learner_level']] ?? $course['learner_level']) ?>
                  </span>
                  <span class="cl-meta-chip cl-meta-chip--lang">
                    <?= $langFlags[$course['language_taught']] ?? '' ?>
                    <?= htmlspecialchars($langLabels[$course['language_taught']] ?? $course['language_taught']) ?>
                  </span>
                </div>

                <!-- Titre -->
                <h2 class="cl-card__title">
                  <a href="./courses/view/<?= $course['id'] ?>">
                    <?= htmlspecialchars($course['title_course']) ?>
                  </a>
                </h2>

                <!-- Description -->
                <p class="cl-card__desc">
                  <?= htmlspecialchars($course['description_course']) ?>
                </p>

                <!-- Outcomes -->
                <?php if (!empty($course['outcomes'])): ?>
                  <ul class="cl-card__outcomes">
                    <?php foreach (array_slice($course['outcomes'], 0, 3) as $outcome):
                      if (empty(trim($outcome))) continue; ?>
                      <li>
                        <i class="fas fa-circle-check"></i>
                        <?= htmlspecialchars($outcome) ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <!-- Stats -->
                <div class="cl-card__stats">
                  <div class="cl-card__stat">
                    <i class="fas fa-layer-group"></i>
                    <span><?= $course['total_modules'] ?> module<?= $course['total_modules'] > 1 ? 's' : '' ?></span>
                  </div>
                  <div class="cl-card__stat">
                    <i class="fas fa-film"></i>
                    <span><?= $course['total_lessons'] ?> leçon<?= $course['total_lessons'] > 1 ? 's' : '' ?></span>
                  </div>
                  <?php if ((int) $course['enrolled_count'] > 0): ?>
                    <div class="cl-card__stat">
                      <i class="fas fa-users"></i>
                      <span><?= $course['enrolled_count'] ?></span>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Rating -->
                <?php if ($rating > 0): ?>
                  <div class="cl-card__rating">
                    <div class="cl-card__stars"><?= $stars ?></div>
                    <span class="cl-card__rating-val"><?= number_format($rating, 1) ?></span>
                  </div>
                <?php endif; ?>

              </div>

              <!-- ===== FOOTER ===== -->
              <div class="cl-card__footer">
                <div class="cl-card__price">
                  <?php if ($isFree): ?>
                    <span class="cl-price-free">Gratuit</span>
                  <?php else: ?>
                    <span class="cl-price-amount"><?= $price ?> <small>FCFA</small></span>
                  <?php endif; ?>
                </div>
                <a
                  href="./courses/view/<?= $course['id'] ?>"
                  class="cl-card__btn <?= $isEnrolled ? 'cl-card__btn--continue' : '' ?>">
                  <?php if ($isEnrolled): ?>
                    <i class="fas fa-play"></i> Continuer
                  <?php else: ?>
                    <i class="fas fa-arrow-right"></i> Découvrir
                  <?php endif; ?>
                </a>
              </div>

            </article>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    </div>
  </main>

  <!-- ======================================================
     SECTION CTA
====================================================== -->
  <section class="cl-cta">
    <div class="cl-cta__bg" aria-hidden="true">
      <div class="cta-orb cta-orb--1"></div>
      <div class="cta-orb cta-orb--2"></div>
    </div>
    <div class="cl-cta__inner">
      <div class="cl-cta__icon" aria-hidden="true">
        <i class="fas fa-rocket"></i>
      </div>
      <div class="cl-cta__text">
        <h2 class="cl-cta__title">Prêt à transformer votre anglais ?</h2>
        <p class="cl-cta__desc">
          Des milliers d'apprenants francophones ont déjà choisi OpenDoorsClass.
          Votre tour commence maintenant.
        </p>
      </div>
      <a href="./profile" class="cl-cta__btn">
        <i class="fas fa-user-graduate"></i>
        Mon espace apprenant
        <i class="fas fa-arrow-right cl-cta__btn-arrow"></i>
      </a>
    </div>
  </section>

  <script src="../public/js/header.min.js"></script>
  <script>
    (function() {
      // ===== SEARCH DEBOUNCE =====
      let searchTimer;
      const searchInput = document.getElementById('cl-search-input');
      const searchClear = document.getElementById('cl-search-clear');

      searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchClear?.classList.toggle('is-hidden', !this.value);
        searchTimer = setTimeout(() => submitFilters(), 480);
      });

      searchClear?.addEventListener('click', function() {
        searchInput.value = '';
        this.classList.add('is-hidden');
        submitFilters();
      });

      // ===== SUBMIT FORM =====
      window.submitFilters = function() {
        document.getElementById('filter-form').submit();
      };

      // ===== STICKY FILTERBAR =====
      const filterbar = document.getElementById('cl-filterbar');
      const hero = document.querySelector('.cl-hero');
      if (filterbar && hero) {
        const observer = new IntersectionObserver(
          ([entry]) => filterbar.classList.toggle('is-stuck', !entry.isIntersecting), {
            threshold: 0
          }
        );
        observer.observe(hero);
      }

      // ===== CARD REVEAL ON SCROLL =====
      const cards = document.querySelectorAll('.cl-card');
      if ('IntersectionObserver' in window) {
        const cardObserver = new IntersectionObserver(
          (entries) => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                cardObserver.unobserve(entry.target);
              }
            });
          }, {
            threshold: 0.08
          }
        );
        cards.forEach(card => cardObserver.observe(card));
      } else {
        cards.forEach(card => card.classList.add('is-visible'));
      }
    })();
  </script>
</body>

</html>