<?php
require_once '../config/connexion.php';
require_once '../fonctions.php';

// EXIGENCE SÉCURITÉ 5 : Vérification de session active
verifier_authentification();

try {
    // 1. Nombre total de projets
    $total_projets = $pdo->query("SELECT COUNT(*) FROM projets")->fetchColumn();

    // 2. Nombre de messages de contact non lus (lu = 0)
    $messages_non_lus = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0")->fetchColumn();

    // 3. Nombre de demandes de projet non lues (lu = 0)
    $demandes_non_lus = $pdo->query("SELECT COUNT(*) FROM demandes_projet WHERE lu = 0")->fetchColumn();

    // 4. Liste des 5 dernières visites
    $stmt_visites = $pdo->query("SELECT adresse_ip, page, date_visite FROM visites ORDER BY date_visite DESC LIMIT 5");
    $dernieres_visites = $stmt_visites->fetchAll();

    // 5. Liste des 5 dernières demandes de projet reçues
    $stmt_demandes = $pdo->query("SELECT id, nom, type_projet, date_demande, lu FROM demandes_projet ORDER BY date_demande DESC LIMIT 5");
    $dernieres_demandes = $stmt_demandes->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur Dashboard : " . $e->getMessage());
    die("Une erreur technique est survenue lors du chargement des statistiques.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Administration</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background-color: #081b29; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        .admin-container { max-width: 1200px; margin: 0 auto; }
        .admin-nav { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #00abf0; padding-bottom: 20px; margin-bottom: 30px; }
        .admin-nav a { color: #00abf0; text-decoration: none; margin-left: 15px; font-weight: 500; }
        .admin-nav a:hover { color: #fff; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: rgba(0, 171, 240, 0.05); border: 2px solid #00abf0; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 0 10px rgba(0,171,240,0.2); }
        .stat-card h3 { margin: 0 0 10px 0; font-size: 1.1rem; color: #aaa; }
        .stat-card .number { font-size: 2.5rem; font-weight: 700; color: #00abf0; }
        .lists-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(48%, 1fr)); gap: 4%; }
        .data-table-container { background: rgba(255,255,255,0.02); border: 1px solid rgba(0, 171, 240, 0.3); border-radius: 8px; padding: 20px; }
        .data-table-container h2 { color: #00abf0; font-size: 1.4rem; margin-top: 0; border-bottom: 1px solid rgba(0,171,240,0.2); padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; }
        th { color: #00abf0; font-weight: 600; }
        .badge { background: #00abf0; color: #081b29; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge.lu { background: rgba(255,255,255,0.2); color: #fff; }
    </style>
</head>
<body>

<div class="admin-container">
    <header class="admin-nav">
        <h1>Bienvenue, <span><?= echapper($_SESSION['admin_prenom']); ?></span></h1>
        <nav>
            <a href="dashboard.php" style="color:#fff;"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="projets/index.php"><i class='bx bx-code-block'></i> Projets</a>
            <a href="utilisateurs/index.php"><i class='bx bxs-user-account'></i> Admins</a>
            <a href="messages/index.php"><i class='bx bxs-envelope'></i> Messages</a>
            <a href="demandes/index.php"><i class='bx bxs-briefcase'></i> Demandes</a>
            <a href="deconnexion.php" style="color: #ff3333;"><i class='bx bx-log-out'></i> Déconnexion</a>
        </nav>
    </header>

    <section class="stats-grid">
        <div class="stat-card">
            <h3>Projets Publiés</h3>
            <div class="number"><?= $total_projets; ?></div>
        </div>
        <div class="stat-card">
            <h3>Messages Non Lus</h3>
            <div class="number"><?= $messages_non_lus; ?></div>
        </div>
        <div class="stat-card">
            <h3>Demandes Non Lues</h3>
            <div class="number"><?= $demandes_non_lus; ?></div>
        </div>
    </section>

    <div class="lists-grid">
        <div class="data-table-container">
            <h2><i class='bx bx-history'></i> 5 Dernières Visites</h2>
            <table>
                <thead>
                    <tr>
                        <th>Adresse IP</th>
                        <th>Page</th>
                        <th>Date & Heure</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dernieres_visites)): ?>
                        <tr><td colspan="3">Aucune visite enregistrée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($dernieres_visites as $visite): ?>
                            <tr>
                                <td><?= echapper($visite['adresse_ip']); ?></td>
                                <td><?= echapper(basename($visite['page'])); ?></td>
                                <td><?= echapper($visite['date_visite']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="data-table-container">
            <h2><i class='bx bx-folder-plus'></i> 5 Dernières Demandes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Type de projet</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dernieres_demandes)): ?>
                        <tr><td colspan="3">Aucune demande reçue.</td></tr>
                    <?php else: ?>
                        <?php foreach ($dernieres_demandes as $demande): ?>
                            <tr>
                                <td><?= echapper($demande['nom']); ?></td>
                                <td><?= echapper($demande['type_projet']); ?></td>
                                <td>
                                    <?php if ($demande['lu'] == 0): ?>
                                        <span class="badge">Nouveau</span>
                                    <?php else: ?>
                                        <span class="badge lu">Lu</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>