<?php
$basePath = '/mrnathanenglish/public';
?>
<link rel="stylesheet" href="<?= $basePath ?>/css/layouts/header.min.css">

<header class="main-header">
    <div class="container">

        <div class="logo">
            <a href="<?= $basePath ?>/"><strong>OpenDoorsClass</strong></a>
        </div>

        <nav class="nav-menu">
            <ul>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_confirmed'] == 1): ?>

                    <li><a href="<?= $basePath ?>/">Accueil</a></li>

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            Examens
                            <i class="fas fa-chevron-down" style="font-size:0.65rem;margin-left:4px;transition:transform 0.2s ease;"></i>
                        </a>
                        <ul class="dropdown-content">
                            <li>
                                <a href="<?= $basePath ?>/toefl">
                                    TOEFL
                                    <small>Test of English as a Foreign Language</small>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $basePath ?>/ielts">
                                    IELTS
                                    <small>International English Language Testing System</small>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $basePath ?>/cambridge">
                                    Cambridge English
                                    <small>Certificats Cambridge</small>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $basePath ?>/toeic">
                                    TOEIC
                                    <small>Test of English for International Communication</small>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $basePath ?>/pte">
                                    PTE Academic
                                    <small>Pearson Test of English Academic</small>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li><a href="<?= $basePath ?>/courses">Cours</a></li>

                    <?php if (($_SESSION['user']['is_admin'] ?? false) === true): ?>
                        <li><a href="<?= $basePath ?>/dashboard">Créer un cours</a></li>
                    <?php endif; ?>

                    <?php
                    $profilePicture = $_SESSION['user']['profile_picture']
                        ?? $_SESSION['user']['profile']['profile_picture']
                        ?? 'default.png';
                    ?>
                    <li id="profile-btn">
                        <img
                            src="<?= $basePath ?>/uploads/profiles/<?= htmlspecialchars($profilePicture) ?>"
                            alt="Photo de profil"
                            style="width:38px;height:38px;border-radius:50%;vertical-align:middle;object-fit:cover;cursor:pointer;">

                        <ul class="dropdown-menu" id="dropdown-menu">
                            <div class="dropdown-content-now">
                                <li>
                                    <a href="<?= $basePath ?>/profile" class="username-on-dropdown">
                                        <img
                                            src="<?= $basePath ?>/uploads/profiles/<?= htmlspecialchars($profilePicture) ?>"
                                            alt="Photo de profil">
                                        <div>
                                            <span>
                                                <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?>
                                            </span>
                                            <small>
                                                <?php
                                                $level = $_SESSION['user']['english_level'] ?? '';
                                                echo match ($level) {
                                                    'beginner'     => 'Débutant',
                                                    'intermediate' => 'Intermédiaire',
                                                    default        => 'Niveau avancé',
                                                };
                                                ?>
                                            </small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $basePath ?>/courses">
                                        <i class="fas fa-book-open" style="width:16px;color:#94a3b8;"></i>
                                        Mes cours
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $basePath ?>/profile">
                                        <i class="fas fa-user" style="width:16px;color:#94a3b8;"></i>
                                        Mon profil
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $basePath ?>/logout" style="color:#dc2626;">
                                        <i class="fas fa-right-from-bracket" style="width:16px;color:#dc2626;"></i>
                                        Déconnexion
                                    </a>
                                </li>
                            </div>
                        </ul>
                    </li>

                <?php else: ?>

                    <li><a href="<?= $basePath ?>/">Accueil</a></li>

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            Examens
                            <i class="fas fa-chevron-down" style="font-size:0.65rem;margin-left:4px;transition:transform 0.2s ease;"></i>
                        </a>
                        <ul class="dropdown-content">
                            <li>
                                <a href="<?= $basePath ?>/toefl">
                                    TOEFL
                                    <small>Test of English as a Foreign Language</small>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $basePath ?>/ielts">
                                    IELTS
                                    <small>International English Language Testing System</small>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $basePath ?>/cambridge">
                                    Cambridge English
                                    <small>Certificats Cambridge</small>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $basePath ?>/toeic">
                                    TOEIC
                                    <small>Test of English for International Communication</small>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $basePath ?>/pte">
                                    PTE Academic
                                    <small>Pearson Test of English Academic</small>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li><a href="<?= $basePath ?>/courses">Cours</a></li>
                    <li><a href="<?= $basePath ?>/register">Inscription</a></li>
                    <li>
                        <a href="<?= $basePath ?>/login" style="
                            background: #0B1F45;
                            color: #fff !important;
                            padding: 8px 18px !important;
                            border-radius: 8px;
                        ">
                            Connexion
                        </a>
                    </li>

                <?php endif; ?>
            </ul>
        </nav>

        <button class="hamburger" aria-label="Menu" aria-expanded="false" aria-controls="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

    </div>

    <!-- ===== MENU MOBILE ===== -->
    <nav id="mobile-menu" class="nav-mobile" aria-hidden="true">
        <ul class="mobile-nav-links">
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_confirmed'] == 1): ?>

                <li><a href="<?= $basePath ?>/">Accueil</a></li>

                <li class="dropdown-mobile">
                    <a href="#" class="dropbtn-mobile">Examens</a>
                    <ul class="dropdown-content-mobile">
                        <li><a href="<?= $basePath ?>/toefl">TOEFL</a></li>
                        <li><a href="<?= $basePath ?>/ielts">IELTS</a></li>
                        <li><a href="<?= $basePath ?>/cambridge">Cambridge</a></li>
                        <li><a href="<?= $basePath ?>/toeic">TOEIC</a></li>
                        <li><a href="<?= $basePath ?>/pte">PTE</a></li>
                    </ul>
                </li>

                <li><a href="<?= $basePath ?>/courses">Cours</a></li>

                <?php if (($_SESSION['user']['is_admin'] ?? false) === true): ?>
                    <li><a href="<?= $basePath ?>/dashboard">Créer un cours</a></li>
                <?php endif; ?>

                <?php
                $profilePicture = $_SESSION['user']['profile_picture']
                    ?? $_SESSION['user']['profile']['profile_picture']
                    ?? 'default.png';
                ?>
                <li>
                    <a href="<?= $basePath ?>/profile" style="display:flex;align-items:center;gap:12px;">
                        <img
                            src="<?= $basePath ?>/uploads/profiles/<?= htmlspecialchars($profilePicture) ?>"
                            alt="Photo de profil"
                            style="width:38px;height:38px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                        <div style="line-height:1.4;">
                            <span style="display:block;font-weight:700;font-size:0.9rem;color:#0B1F45;">
                                <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?>
                            </span>
                            <small style="font-size:0.72rem;color:#94a3b8;">
                                <?php
                                $level = $_SESSION['user']['english_level'] ?? '';
                                echo match ($level) {
                                    'beginner'     => 'Débutant',
                                    'intermediate' => 'Intermédiaire',
                                    default        => 'Niveau avancé',
                                };
                                ?>
                            </small>
                        </div>
                    </a>
                </li>

                <li><a href="<?= $basePath ?>/logout" style="color:#dc2626;">
                        <i class="fas fa-right-from-bracket"></i> Déconnexion
                    </a></li>

            <?php else: ?>

                <li><a href="<?= $basePath ?>/">Accueil</a></li>

                <li class="dropdown-mobile">
                    <a href="#" class="dropbtn-mobile">Examens</a>
                    <ul class="dropdown-content-mobile">
                        <li><a href="<?= $basePath ?>/toefl">TOEFL</a></li>
                        <li><a href="<?= $basePath ?>/ielts">IELTS</a></li>
                        <li><a href="<?= $basePath ?>/cambridge">Cambridge</a></li>
                        <li><a href="<?= $basePath ?>/toeic">TOEIC</a></li>
                        <li><a href="<?= $basePath ?>/pte">PTE</a></li>
                    </ul>
                </li>

                <li><a href="<?= $basePath ?>/courses">Cours</a></li>
                <li><a href="<?= $basePath ?>/register">Inscription</a></li>
                <li><a href="<?= $basePath ?>/login">Connexion</a></li>

            <?php endif; ?>
        </ul>
    </nav>

    <div class="overlay"></div>
</header>


<!-- ===== SCRIPT HEADER — chemin absolu ===== -->
<script src="<?= $basePath ?>/js/header.min.js" defer></script>