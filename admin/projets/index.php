<?php
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

verifier_authentification();

try {
    // EXIGENCE 5.3 : Lister les projets triés par date de création décroissante
    $sql = "SELECT id, titre, technologies, date_creation FROM projets ORDER BY date_creation DESC";
    $projets = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur Liste Projets : " . $e->getMessage());
    die("Erreur lors de la récupération des données.");
}

// Génération du token CSRF pour le bouton de suppression POST
$token_csrf = generer_csrf();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Projets — Administration</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body { background-color: #081b29; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        .admin-container { max-width: 1200px; margin: 0 auto; }
        .admin-nav { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #00abf0; padding-bottom: 20px; margin-bottom: 30px; }
        .admin-nav a { color: #00abf0; text-decoration: none; margin-left: 15px; font-weight: 500; }
        .admin-nav a:hover { color: #fff; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; }
        .btn-add { background: #00abf0; color: #081b29; }
        .btn-add:hover { background: #fff; box-shadow: 0 0 10px #fff; }
        .btn-edit { background: rgba(0, 171, 240, 0.2); color: #00abf0; border: 1px solid #00abf0; padding: 6px 12px; }
        .btn-edit:hover { background: #00abf0; color: #081b29; }
        .btn-delete { background: rgba(255, 51, 51, 0.2); color: #ff3333; border: 1px solid #ff3333; padding: 6px 12px; }
        .btn-delete:hover { background: #ff3333; color: #fff; }
        table { width: 100%; border-collapse: collapse; background: rgba(255,255,255,0.02); border: 1px solid rgba(0,171,240,0.2); border-radius: 8px; }
        th, td { padding: 14px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: left; }
        th { color: #00abf0; background: rgba(0, 171, 240, 0.05); }
        .actions-cell { display: flex; gap: 10px; align-items: center; }
        form { margin: 0; }
    </style>
</head>
<body>

<div class="admin-container">
    <header class="admin-nav">
        <h1>Gestion des <span>Projets</span></h1>
        <nav>
            <a href="../dashboard.php"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="index.php" style="color:#fff;"><i class='bx bx-code-block'></i> Projets</a>
            <a href="../utilisateurs/index.php"><i class='bx bxs-user-account'></i> Admins</a>
            <a href="../messages/index.php"><i class='bx bxs-envelope'></i> Messages</a>
            <a href="../demandes/index.php"><i class='bx bxs-briefcase'></i> Demandes</a>
            <a href="../deconnexion.php" style="color: #ff3333;"><i class='bx bx-log-out'></i> Déconnexion</a>
        </nav>
    </header>

    <div class="header-actions">
        <h2>Liste des réalisations</h2>
        <a href="ajouter.php" class="btn btn-add"><i class='bx bx-plus-circle'></i> Créer un projet</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Technologies</th>
                <th>Date d'ajout</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($projets)): ?>
                <tr><td colspan="4">Aucun projet enregistré pour le moment.</td></tr>
            <?php else: ?>
                <?php foreach($projets as $projet): ?>
                    <tr>
                        <td><strong><?= echapper($projet['titre']); ?></strong></td>
                        <td><?= echapper($projet['technologies']); ?></td>
                        <td><?= echapper($projet['date_creation']); ?></td>
                        <td class="actions-cell">
                            <a href="modifier.php?id=<?= $projet['id']; ?>" class="btn btn-edit"><i class='bx bx-edit-alt'></i> Modifier</a>
                            
                            <form action="supprimer.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce projet ?');">
                                <input type="hidden" name="csrf_token" value="<?= $token_csrf; ?>">
                                <input type="hidden" name="id" value="<?= $projet['id']; ?>">
                                <button type="submit" class="btn btn-delete"><i class='bx bx-trash'></i> Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>