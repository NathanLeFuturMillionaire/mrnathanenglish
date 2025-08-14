<header class="main-header">
    <div class="container">
        <div class="logo">
            <a href="/"><strong>Mr Nathan English</strong></a>
        </div>

        <nav class="nav-menu">
            <ul>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['confirmed'] == 1): ?>
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

                    <!-- Photo de profil -->
                    <li>
                        <img src="./uploads/profiles/<?= htmlspecialchars($_SESSION['user']['profile_picture'] ?? '/assets/img/default.png') ?>"
                             alt="Photo de profil"
                             style="width:40px; height:40px; border-radius:50%; vertical-align:middle;">
                    </li>

                    <!-- Déconnexion -->
                    <!-- <li><a href="/logout">Déconnexion</a></li> -->
                <?php else: ?>
                    <!-- Version visiteur -->
                    <li><a href="/">Accueil</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Examens ▾</a>
                        <ul class="dropdown-content">
                            <li><a href="/toefl">TOEFL <small>- Test of English as a Foreign Language</small></a></li>
                            <li><a href="/ielts">IELTS <small>- International English Language Testing System</small></a></li>
                            <li><a href="/cambridge">Cambridge English <small>- Certificats Cambridge</small></a></li>
                            <li><a href="/toeic">TOEIC <small>- Test of English for International Communication</small></a></li>
                            <li><a href="/pte">PTE Academic <small>- Pearson Test of English Academic</small></a></li>
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
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['confirmed'] == 1): ?>
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
                <li>
                    <img src="../<?= htmlspecialchars($_SESSION['user']['profile_picture'] ?? '/assets/img/default.png') ?>"
                         alt="Photo de profil"
                         style="width:40px; height:40px; border-radius:50%;">
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
                <li><a href="./register">Inscription</a></li>
                <li><a href="./login">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Overlay sombre -->
    <div class="overlay"></div>
</header>
