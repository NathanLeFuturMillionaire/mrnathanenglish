<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenDoorsClass - Tableau de bord Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="../public/css/admins/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <?php

    /**
     * Sécurité : accès admin uniquement
     */
    if (!isset($_SESSION['user']) || !($_SESSION['user']['is_admin'] ?? false)) {
        header('Location: ./404');
        exit;
    }
    ?>

    <!-- Menu latéral fixe -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>OpenDoorsClass</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="#" class="active"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
                <li><a href="./courses"><i class="fas fa-users"></i> Cours</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Étudiants</a></li>
                <li><a href="#"><i class="fas fa-book-open"></i> Formations</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Contenu principal -->
    <main class="main-content">
        <header class="dashboard-header">
            <h1>Bienvenue <?= $_SESSION["user"]["username"]; ?> !</h1>
            <p class="subtitle">Administrateur • 22 décembre 2025</p>
        </header>

        <!-- Cartes statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="card-icon"><i class="fas fa-graduation-cap"></i></div> <!-- Total cours -->
                <h3>Total des cours</h3>
                <p class="value">142</p>
            </div>
            <div class="stat-card">
                <div class="card-icon"><i class="fas fa-gift"></i></div> <!-- Gratuits -->
                <h3>Cours gratuits</h3>
                <p class="value">38</p>
            </div>
            <div class="stat-card">
                <div class="card-icon"><i class="fas fa-euro-sign"></i></div> <!-- Payants -->
                <h3>Cours payants</h3>
                <p class="value">104</p>
            </div>
            <div class="stat-card">
                <div class="card-icon"><i class="fas fa-language"></i></div> <!-- Anglais/Espagnol -->
                <h3>Cours en anglais</h3>
                <p class="value">118</p>
            </div>
            <div class="stat-card">
                <div class="card-icon"><i class="fas fa-chart-bar"></i></div> <!-- CA -->
                <h3>Chiffre d'affaires total</h3>
                <p class="value">€48 920</p>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="charts-grid">
            <div class="chart-card">
                <h2>Répartition par niveau</h2>
                <canvas id="levelChart"></canvas>
            </div>
            <div class="chart-card">
                <h2>Évolution des cours créés</h2>
                <canvas id="evolutionChart"></canvas>
            </div>
        </div>

        <!-- Tableau derniers cours -->
        <div class="table-card">
            <h2>10 derniers cours ajoutés</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Professeur</th>
                            <th>Date création</th>
                            <th>Prix</th>
                            <th>Langue</th>
                            <th>Niveau</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Advanced Business English</td>
                            <td>Sarah Johnson</td>
                            <td>20/12/2025</td>
                            <td>€149</td>
                            <td>Anglais</td>
                            <td>Intermédiaire</td>
                            <td><span class="status published">Publié</span></td>
                        </tr>
                        <tr>
                            <td>Español para Principiantes</td>
                            <td>Maria López</td>
                            <td>18/12/2025</td>
                            <td><span class="price-free">Gratuit</span></td>
                            <td>Espagnol</td>
                            <td>Débutant</td>
                            <td><span class="status published">Publié</span></td>
                        </tr>
                        <tr>
                            <td>English Conversation Mastery</td>
                            <td>David Brown</td>
                            <td>15/12/2025</td>
                            <td>€99</td>
                            <td>Anglais</td>
                            <td>Intermédiaire</td>
                            <td><span class="status draft">Brouillon</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="js/admins/dashboard.js"></script>

</body>

</html>