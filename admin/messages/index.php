<?php
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

verifier_authentification();

// Action de marquage comme lu lors du clic d'ouverture (appel asynchrone ou rafraîchissement)
if (isset($_GET['lire'])) {
    $id_msg = intval($_GET['lire']);
    try {
        // EXIGENCE 5.5 : Quand un admin ouvre un message, la colonne lu est mise à 1
        $update = $pdo->prepare("UPDATE messages_contact SET lu = 1 WHERE id = ?");
        $update->execute([$id_msg]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

try {
    // EXIGENCE 5.5 : Liste triée du plus récent au plus ancien
    $messages = $pdo->query("SELECT * FROM messages_contact ORDER BY date_envoi DESC")->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des messages.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages de Contact — Administration</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body { background-color: #081b29; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        .admin-container { max-width: 1200px; margin: 0 auto; }
        .admin-nav { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #00abf0; padding-bottom: 20px; margin-bottom: 30px; }
        .admin-nav a { color: #00abf0; text-decoration: none; margin-left: 15px; }
        .message-card { background: rgba(255,255,255,0.02); border-left: 4px solid #aaa; border-radius: 4px; padding: 20px; margin-bottom: 15px; transition: 0.3s; }
        
        /* EXIGENCE 5.5 : Les messages non lus sont visuellement distingués */
        .message-card.non-lu { border-left-color: #00abf0; background: rgba(0, 171, 240, 0.05); box-shadow: 0 0 10px rgba(0,171,240,0.1); }
        
        .message-header { display: flex; justify-content: space-between; font-size: 14px; color: #aaa; margin-bottom: 10px; }
        .message-header strong { color: #fff; font-size: 16px; }
        .message-body { display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05); color: #ddd; line-height: 1.6; }
        .btn-read { background: transparent; border: 1px solid #00abf0; color: #00abf0; padding: 5px 10px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; }
        .btn-read:hover { background: #00abf0; color: #081b29; }
    </style>
    <script>
        function toggleMessage(id) {
            var body = document.getElementById('msg-body-' + id);
            if(body.style.display === 'block') {
                body.style.display = 'none';
            } else {
                body.style.display = 'block';
                // Si le message était non lu, on recharge la page pour valider l'état lu en BDD
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
        <h1>Messages de <span>Contact</span></h1>
        <nav>
            <a href="../dashboard.php"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="../projets/index.php"><i class='bx bx-code-block'></i> Projets</a>
            <a href="../utilisateurs/index.php"><i class='bx bxs-user-account'></i> Admins</a>
            <a href="index.php" style="color:#fff;"><i class='bx bxs-envelope'></i> Messages</a>
            <a href="../demandes/index.php"><i class='bx bxs-briefcase'></i> Demandes</a>
            <a href="../deconnexion.php" style="color: #ff3333;"><i class='bx bx-log-out'></i> Déconnexion</a>
        </nav>
    </header>

    <?php if(empty($messages)): ?>
        <p>Aucun message reçu pour le moment.</p>
    <?php else: ?>
        <?php foreach($messages as $msg): ?>
            <div id="card-<?= $msg['id']; ?>" class="message-card <?= $msg['lu'] == 0 ? 'non-lu' : ''; ?>">
                <div class="message-header">
                    <div>
                        <strong><?= echapper($msg['nom']); ?></strong> (<?= echapper($msg['email']); ?>)
                    </div>
                    <div>
                        <i class='bx bx-time-five'></i> <?= echapper($msg['date_envoi']); ?> &nbsp;
                        <button class="btn-read" onclick="toggleMessage(<?= $msg['id']; ?>)">Lire le message</button>
                    </div>
                </div>
                <div id="msg-body-<?= $msg['id']; ?>" class="message-body">
                    <?= nl2br(echapper($msg['message'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>