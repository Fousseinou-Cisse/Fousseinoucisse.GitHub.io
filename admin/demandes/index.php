<?php
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

verifier_authentification();

if (isset($_GET['lire'])) {
    $id_demande = intval($_GET['lire']);
    try {
        // EXIGENCE 5.6 : Mise à jour de la colonne lu à 1 à l'ouverture
        $update = $pdo->prepare("UPDATE demandes_projet SET lu = 1 WHERE id = ?");
        $update->execute([$id_demande]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

try {
    // EXIGENCE 5.6 : Liste triée par date décroissante
    $demandes = $pdo->query("SELECT * FROM demandes_projet ORDER BY date_demande DESC")->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des demandes.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demandes de Projet — Administration</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body { background-color: #081b29; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        .admin-container { max-width: 1200px; margin: 0 auto; }
        .admin-nav { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #00abf0; padding-bottom: 20px; margin-bottom: 30px; }
        .admin-nav a { color: #00abf0; text-decoration: none; margin-left: 15px; }
        .demand-card { background: rgba(255,255,255,0.02); border-left: 4px solid #aaa; border-radius: 4px; padding: 20px; margin-bottom: 15px; transition: 0.3s; }
        
        /* EXIGENCE 5.6 : Distinction visuelle non lu / lu */
        .demand-card.non-lu { border-left-color: #00abf0; background: rgba(0, 171, 240, 0.05); }
        
        .demand-header { display: flex; justify-content: space-between; font-size: 14px; color: #aaa; }
        .demand-header strong { color: #fff; font-size: 16px; }
        .demand-body { display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05); color: #ddd; }
        .budget-tag { color: #00ffcc; font-weight: 600; margin-top: 10px; display: block; }
        .btn-read { background: transparent; border: 1px solid #00abf0; color: #00abf0; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .btn-read:hover { background: #00abf0; color: #081b29; }
    </style>
    <script>
        function toggleDemande(id) {
            var body = document.getElementById('demand-body-' + id);
            if(body.style.display === 'block') {
                body.style.display = 'none';
            } else {
                body.style.display = 'block';
                if(document.getElementById('card-'+id).classList.contains('non-lu')) {
                    window.location.href = "index.php?lire=" + id;
                }
            }
        }
    </script>
</head>
<body>

<div class="admin-container">
    <header class="admin-nav">
        <h1>Demandes de <span>Projet</span></h1>
        <nav>
            <a href="../dashboard.php"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="../projets/index.php"><i class='bx bx-code-block'></i> Projets</a>
            <a href="../utilisateurs/index.php"><i class='bx bxs-user-account'></i> Admins</a>
            <a href="../messages/index.php"><i class='bx bxs-envelope'></i> Messages</a>
            <a href="index.php" style="color:#fff;"><i class='bx bxs-briefcase'></i> Demandes</a>
            <a href="../deconnexion.php" style="color: #ff3333;"><i class='bx bx-log-out'></i> Déconnexion</a>
        </nav>
    </header>

    <?php if(empty($demandes)): ?>
        <p>Aucune demande de projet reçue.</p>
    <?php else: ?>
        <?php foreach($demandes as $dem): ?>
            <div id="card-<?= $dem['id']; ?>" class="demand-card <?= $dem['lu'] == 0 ? 'non-lu' : ''; ?>">
                <div class="demand-header">
                    <div>
                        <strong><?= echapper($dem['nom']); ?></strong> (<?= echapper($dem['email']); ?>) - <span style="color: #00abf0;"><?= echapper($dem['type_projet']); ?></span>
                    </div>
                    <div>
                        <i class='bx bx-calendar'></i> <?= echapper($dem['date_demande']); ?> &nbsp;
                        <button class="btn-read" onclick="toggleDemande(<?= $dem['id']; ?>)">Consulter</button>
                    </div>
                </div>
                <div id="demand-body-<?= $dem['id']; ?>" class="demand-body">
                    <strong>Cahier des charges soumis :</strong>
                    <p style="line-height:1.6;"><?= nl2br(echapper($dem['description'])); ?></p>
                    
                    <?php if(!empty($dem['budget'])): ?>
                        <span class="budget-tag"><i class='bx bx-money'></i> Budget estimé : <?= echapper($dem['budget']); ?> FCFA</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>