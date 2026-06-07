<?php
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

verifier_authentification();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$erreurs = [];
$succes = "";

// Chargement des données de l'administrateur
try {
    // CORRECTION : Utilisation correcte de la variable $id avec le signe $
    $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE id = ?");
    $stmt->execute([$id]);
    $admin = $stmt->fetch();

    if (!$admin) {
        die("Administrateur introuvable.");
    }
} catch (PDOException $e) {
    error_log("Erreur chargement admin : " . $e->getMessage());
    die("Erreur lors de la récupération des données.");
}

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifier_csrf($_POST['csrf_token'])) {
        $erreurs[] = "Jeton CSRF invalide.";
    } else {
        $prenom = trim($_POST['prenom'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['mot_de_passe'] ?? '');

        if (empty($prenom) || empty($nom) || empty($email)) {
            $erreurs[] = "Les champs Prénom, Nom et Email sont obligatoires.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = "Format d'email invalide.";
        }

        if (empty($erreurs)) {
            try {
                // Si le mot de passe est vide, on conserve l'ancien hash, sinon on hache le nouveau
                if (empty($password)) {
                    $password_hash = $admin['mot_de_passe'];
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                }

                $sql = "UPDATE administrateurs SET prenom = ?, nom = ?, email = ?, mot_de_passe = ? WHERE id = ?";
                $pdo->prepare($sql)->execute([$prenom, $nom, $email, $password_hash, $id]);
                
                $succes = "Compte mis à jour avec succès.";
                
                // Rafraîchissement des données locales pour affichage dans le formulaire
                $admin['prenom'] = $prenom;
                $admin['nom'] = $nom;
                $admin['email'] = $email;
            } catch (PDOException $e) {
                error_log("Erreur Modification Admin : " . $e->getMessage());
                $erreurs[] = "Impossible de mettre à jour le compte.";
            }
        }
    }
}

$token_csrf = generer_csrf();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'Administrateur</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body { background-color: #081b29; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        .form-container { max-width: 600px; margin: 40px auto; background: rgba(255,255,255,0.02); border: 2px solid #00abf0; border-radius: 8px; padding: 30px; }
        h1 { color: #00abf0; font-size: 1.8rem; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #aaa; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px; background: rgba(8, 27, 41, 0.6); border: 1px solid #00abf0; border-radius: 6px; color: #fff; box-sizing: border-box; }
        .btn-submit { background: #00abf0; color: #081b29; padding: 12px 25px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .msg-err { color: #ff3333; margin-bottom: 15px; }
        .msg-ok { color: #00ffcc; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="form-container">
    <a href="index.php" style="color: #aaa; text-decoration: none;"><i class='bx bx-arrow-back'></i> Retour</a>
    <h1>Modifier l'administrateur</h1>

    <?php if(!empty($erreurs)): ?>
        <div class="msg-err"><ul><?php foreach($erreurs as $err) echo "<li>".htmlspecialchars($err)."</li>"; ?></ul></div>
    <?php endif; ?>
    <?php if(!empty($succes)): ?>
        <div class="msg-ok"><?= htmlspecialchars($succes); ?></div>
    <?php endif; ?>

    <form action="modifier.php?id=<?= $id; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $token_csrf; ?>">

        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($admin['prenom']); ?>" required>
        </div>
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($admin['nom']); ?>" required>
        </div>
        <div class="form-group">
            <label>Adresse Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']); ?>" required>
        </div>
        <div class="form-group">
            <label>Nouveau mot de passe (Laisser vide pour conserver l'actuel)</label>
            <input type="password" name="mot_de_passe" placeholder="••••••••">
        </div>

        <button type="submit" class="btn-submit">Mettre à jour</button>
    </form>
</div>

</body>
</html>