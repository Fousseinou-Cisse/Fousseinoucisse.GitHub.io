<?php
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

verifier_authentification();

$erreurs = [];
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifier_csrf($_POST['csrf_token'])) {
        $erreurs[] = "Jeton CSRF invalide.";
    } else {
        $prenom = trim($_POST['prenom'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['mot_de_passe'] ?? '');

        if (empty($prenom) || empty($nom) || empty($email) || empty($password)) {
            $erreurs[] = "Tous les champs sont obligatoires.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = "Format d'email invalide.";
        }

        if (empty($erreurs)) {
            try {
                // Vérifier si l'email existe déjà
                $check = $pdo->prepare("SELECT id FROM administrateurs WHERE email = ?");
                $check->execute([$email]);
                if ($check->fetch()) {
                    $erreurs[] = "Cette adresse email est déjà attribuée.";
                } else {
                    // EXIGENCE 5.4 : Le mot de passe saisi doit être haché avant d'être inséré en base.
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    $sql = "INSERT INTO administrateurs (prenom, nom, email, mot_de_passe, date_creation) VALUES (?, ?, ?, ?, NOW())";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$prenom, $nom, $email, $password_hash]);
                    
                    $succes = "L'administrateur a été créé avec succès.";
                }
            } catch (PDOException $e) {
                error_log("Erreur Ajout Admin : " . $e->getMessage());
                $erreurs[] = "Une erreur technique s'est produite.";
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
    <title>Créer un Administrateur</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body { background-color: #081b29; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        .form-container { max-width: 600px; margin: 40px auto; background: rgba(255,255,255,0.02); border: 2px solid #00abf0; border-radius: 8px; padding: 30px; }
        h1 { color: #00abf0; font-size: 1.8rem; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #aaa; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px; background: rgba(8, 27, 41, 0.6); border: 1px solid #00abf0; border-radius: 6px; color: #fff; box-sizing: border-box; outline: none; }
        .btn-submit { background: #00abf0; color: #081b29; padding: 12px 25px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #fff; }
        .msg-err { color: #ff3333; }
        .msg-ok { color: #00ffcc; }
    </style>
</head>
<body>

<div class="form-container">
    <a href="index.php" style="color: #aaa; text-decoration: none;"><i class='bx bx-arrow-back'></i> Retour</a>
    <h1>Créer un compte administrateur</h1>

    <?php if(!empty($erreurs)): ?>
        <div class="msg-err"><ul><?php foreach($erreurs as $err) echo "<li>".echapper($err)."</li>"; ?></ul></div>
    <?php endif; ?>
    <?php if(!empty($succes)): ?>
        <div class="msg-ok"><?= $succes; ?></div>
    <?php endif; ?>

    <form action="ajouter.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $token_csrf; ?>">

        <div class="form-group">
            <label>Prénom *</label>
            <input type="text" name="prenom" required>
        </div>
        <div class="form-group">
            <label>Nom *</label>
            <input type="text" name="nom" required>
        </div>
        <div class="form-group">
            <label>Adresse Email *</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Mot de passe *</label>
            <input type="password" name="mot_de_passe" required>
        </div>

        <button type="submit" class="btn-submit">Créer le compte</button>
    </form>
</div>

</body>
</html>