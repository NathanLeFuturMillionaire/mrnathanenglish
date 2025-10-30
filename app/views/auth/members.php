<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des membres premium - OpenDoorsClass</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/auth/members.css">
</head>

<body>
    <main class="members-main">
        <div class="members-container">
            <h1>Liste des membres premium</h1>
            <p class="members-subtitle">Gérez vos membres et suivez leurs abonnements.</p>

            <?php if (empty($members)): ?>
                <div class="no-members">
                    <h3>Aucun membre premium</h3>
                    <p>Ajoutez votre premier membre pour commencer.</p>
                    <a href="/admins" class="btn-add-member">Ajouter un membre</a>
                </div>
            <?php else: ?>
                <div class="members-table-container">
                    <table class="members-table">
                        <thead>
                            <tr>
                                <th>Nom d'utilisateur</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Durée</th>
                                <th>Prix</th>
                                <th>début</th>
                                <th>fin</th>
                                <th>Jours</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr class="members-row <?= $member['status'] === 'Expiré' ? 'expired' : '' ?>">
                                    <td class="username"><?= htmlspecialchars($member['username']) ?></td>
                                    <td class="email"><?= htmlspecialchars($member['email']) ?></td>
                                    <td class="phone"><?= htmlspecialchars($member['phone_number']) ?></td>
                                    <td class="duration"><?= $member['subscription_duration'] ?> m</td>
                                    <td class="price"><?= number_format($member['price'], 0, ',', ' ') ?></td>
                                    <td class="start-date"><?= date('d/m/Y', strtotime($member['subscription_start'])) ?></td>
                                    <td class="end-date"><?= date('d/m/Y', strtotime($member['subscription_end'])) ?></td>
                                    <td class="days-remaining <?= $member['days_remaining'] <= 7 ? 'low-days' : '' ?>">
                                        <?= $member['days_remaining'] ?>
                                    </td>
                                    <td class="status <?= $member['status'] === 'Actif' ? 'active' : 'expired' ?>">
                                        <?= $member['status'] ?>
                                    </td>
                                    <td class="actions">
                                        <a href="./members/edit/<?= $member['id'] ?>" class="btn-edit" title="Modifier">✏️</a>
                                        <form action="./members/delete&amp;id=<?= $member["id"]; ?>" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce membre ?')">
                                            <button type="submit" class="btn-delete" title="Supprimer">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="members-actions">
                <a href="../admins" class="btn-back">← Retour aux admins</a>
                <a href="../admins" class="btn-add-member">Ajouter un membre</a>
            </div>
        </div>
    </main>

    <script src="./js/members.js"></script>
</body>

</html>