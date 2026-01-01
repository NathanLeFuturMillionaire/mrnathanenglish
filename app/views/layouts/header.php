<?php

use App\controllers\AuthController;

?>

<header class="main-header">
    <div class="container">
        <div class="logo">
            <a href="./"><strong>OpenDoorsClass</strong></a>
        </div>

        <nav class="nav-menu">
            <ul>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_confirmed'] == 1): ?>
                    <!-- Accueil -->
                    <li><a href="./">Accueil</a></li>

                    <!-- Examens -->
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Examens ▾</a>
                        <ul class="dropdown-content">
                            <li><a href="./toefl">TOEFL <small>- Test of English as a Foreign Language</small></a></li>
                            <li><a href="./ielts">IELTS <small>- International English Language Testing System</small></a></li>
                            <li><a href="./cambridge">Cambridge English <small>- Certificats Cambridge</small></a></li>
                            <li><a href="./toeic">TOEIC <small>- Test of English for International Communication</small></a></li>
                            <li><a href="./pte">PTE Academic <small>- Pearson Test of English Academic</small></a></li>
                        </ul>
                    </li>

                    <!-- Cours -->
                    <li><a href="./courses">Cours</a></li>

                    <?php if (isset($_SESSION['user']) && ($_SESSION['user']['is_admin'] ?? false) === true): ?>
                        <li><a href="./dashboard">Créer un cours</a></li>
                    <?php endif; ?>



                    <!-- Photo de profil -->
                    <li id="profile-btn">
                        <?php
                        $profilePicture = $_SESSION['user']['profile_picture']
                            ?? $_SESSION['user']['profile']['profile_picture']
                            ?? 'default.png';
                        ?>
                        <img src="../public/uploads/profiles/<?= htmlspecialchars($profilePicture) ?>"
                            alt="Photo de profil"
                            style="width:40px; height:40px; border-radius:50%; vertical-align:middle;object-fit:cover;">
                        <!-- Dropdown -->
                        <ul class="dropdown-menu" id="dropdown-menu">
                            <div class="dropdown-content-now">
                                <li id="profile-btn">
                                    <a href="./profile" class="username-on-dropdown">
                                        <?php
                                        $profilePicture = $_SESSION['user']['profile_picture']
                                            ?? $_SESSION['user']['profile']['profile_picture']
                                            ?? 'default.png';
                                        ?>
                                        <img src="../public/uploads/profiles/<?= htmlspecialchars($profilePicture) ?>"
                                            alt="Photo de profil"
                                            style="width:40px; height:40px; border-radius:50%; vertical-align:middle;object-fit:cover;">
                                        <div style="line-height: 20px;margin-left:10px;" class="username">
                                            <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?><br> <small style="display:inline-block;font-size: 0.7em;">
                                                <!-- Affiche le niveau de l'élève -->
                                                <?php
                                                $englishLevel = $_SESSION["user"]["english_level"] ?? '';
                                                if ($englishLevel === "beginner") {
                                                    echo "Débutant";
                                                } else if ($englishLevel === "intermediate") {
                                                    echo "Intermédiaire";
                                                } else {
                                                    echo "Niveau avancé";
                                                }
                                                ?>
                                            </small>
                                        </div>
                                    </a>
                                </li>
                                <li><a href="./lessons">Cours</a></li>
                                <li><a href="./logout">Déconnexion</a></li>
                            </div>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Version visiteur -->
                    <li><a href="./">Accueil</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Examens ▾</a>
                        <ul class="dropdown-content">
                            <li><a href="./toefl">TOEFL <small>- Test of English as a Foreign Language</small></a></li>
                            <li><a href="./ielts">IELTS <small>- International English Language Testing System</small></a></li>
                            <li><a href="./cambridge">Cambridge English <small>- Certificats Cambridge</small></a></li>
                            <li><a href="./toeic">TOEIC <small>- Test of English for International Communication</small></a></li>
                            <li><a href="./pte">PTE Academic <small>- Pearson Test of English Academic</small></a></li>
                        </ul>
                    </li>
                    <li><a href="./courses">Cours</a></li>

                    <li><a href="./register">Inscription</a></li>
                    <li><a href="./login">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Bouton hamburger (visible mobile) -->
        <button class="hamburger" aria-label="Menu" aria-expanded="false" aria-controls="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
    </div>

    <!-- Menu mobile -->
    <nav id="mobile-menu" class="nav-mobile" aria-hidden="true">
        <ul class="mobile-nav-links">
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_confirmed'] == 1): ?>
                <li><a href="./">Accueil</a></li>
                <li class="dropdown-mobile">
                    <a href="#" class="dropbtn-mobile">Examens</a>
                    <ul class="dropdown-content-mobile">
                        <li><a href="./toefl">TOEFL</a></li>
                        <li><a href="./ielts">IELTS</a></li>
                        <li><a href="./cambridge">Cambridge</a></li>
                        <li><a href="./toeic">TOEIC</a></li>
                        <li><a href="./pte">PTE</a></li>
                    </ul>
                </li>
                <li><a href="./courses">Mon cours</a></li>
                <?php if (isset($_SESSION['user']) && ($_SESSION['user']['is_admin'] ?? false) === true): ?>
                    <li><a href="./dashboard">Créer un cours</a></li>
                <?php endif; ?>
                <li style="display: flex;">
                    <img src="../public/uploads/profiles/<?= htmlspecialchars($_SESSION['user']['profile_picture'] ?? $_SESSION['user']['profile']['profile_picture'] ?? 'default.png') ?>"
                        alt="Photo de profil"
                        style="width:40px; height:40px; border-radius:50%; vertical-align:middle; object-fit:cover;">

                    <div style="line-height: 20px;margin-left:10px;" class="username">
                        <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?><br> <small style="display:inline-block;font-size: 0.7em;">
                            <!-- Affiche le niveau de l'élève -->
                            <?php
                            $englishLevel = $_SESSION["user"]["english_level"] ?? '';
                            if ($englishLevel === "beginner") {
                                echo "Débutant";
                            } else if ($englishLevel === "intermediate") {
                                echo "Intermédiaire";
                            } else {
                                echo "Niveau avancé";
                            }
                            ?>
                        </small>
                    </div>
                </li>
                <li><a href="./logout">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="./">Accueil</a></li>
                <li class="dropdown-mobile">
                    <a href="#" class="dropbtn-mobile">Examens</a>
                    <ul class="dropdown-content-mobile">
                        <li><a href="./toefl">TOEFL</a></li>
                        <li><a href="./ielts">IELTS</a></li>
                        <li><a href="./cambridge">Cambridge</a></li>
                        <li><a href="./toeic">TOEIC</a></li>
                        <li><a href="./pte">PTE</a></li>
                    </ul>
                </li>
                <li><a href="./courses">Cours</a></li>
                <?php if ($showAdminLink): ?>
                    <li><a href="./admins">Admins</a></li>
                <?php endif; ?>
                <li><a href="./register">Inscription</a></li>
                <li><a href="./login">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Overlay sombre -->
    <div class="overlay"></div>
</header>