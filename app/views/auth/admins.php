<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/auth/admins.css">
    <link rel="stylesheet" href="../public/css/style.css">
    <title>OpenDoorsClass - Administration</title>
</head>

<body>

    <main class="admin-container">
        <div class="admin-form-wrapper">
            <div class="admin-header">
                <h1>Ajouter un Nouvel Abonné <em>OpenDoorsClass</em></h1>
                <p class="subtitle">Remplissez les informations pour créer un nouvel abonnement.</p>
                <a href="./admins/members">Abonnement en cours</a>
            </div>

            <form id="adminForm" class="admin-form" method="POST" action="./admins">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Entrez le nom complet">
                    <div class="error-message" data-for="nom"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Entrez l'adresse email">
                    <div class="error-message" data-for="email"></div>
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" placeholder="Entrez le numéro de téléphone">
                    <div class="error-message" data-for="telephone"></div>
                </div>

                <div class="form-group">
                    <label for="duree">Durée d'abonnement (mois)</label>
                    <input type="number" id="duree" name="duree" min="1" max="12" placeholder="Entrez la durée (1-12)">
                    <div class="error-message" data-for="duree"></div>
                </div>

                <div class="form-group">
                    <label for="prix">Prix</label>
                    <input type="text" id="prix" name="prix" readonly placeholder="Le prix sera calculé automatiquement">
                </div>

                <div class="form-group">
                    <label for="dateDebut">Date de début</label>
                    <input type="date" id="dateDebut" name="dateDebut">
                </div>

                <div class="form-group">
                    <label for="dateFin">Date de fin</label>
                    <input type="date" id="dateFin" name="dateFin"  >
                </div>

                <button type="submit" class="btn-submit">Ajouter</button>
            </form>
        </div>
    </main>

    <script src="js/admins.js">

    </script>

    <div id="toast-container"></div>
</body>

</html>