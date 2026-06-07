<?php
// admin/setup_prof.php

// Inclusion de la connexion à la base de données et des fonctions d'échappement
require_once '../config/connexion.php';
require_once '../fonctions.php';

$message_succes = "";
$identifiants = [];
$erreur = "";

// Configuration du compte pour le professeur
$email_prof = "professeur.diouf@uam.edu.sn"; // Exemple d'email institutionnel pour le professeur
$prenom_prof = "Monsieur";
$nom_prof = "Diouf";

try {
    // 1. Vérification sécuritaire : on regarde si le professeur existe déjà pour éviter les doublons
    $check = $pdo->prepare("SELECT id FROM administrateurs WHERE email = ?");
    $check->execute([$email_prof]);
    $existe = $check->fetch();

    if ($existe) {
        $erreur = "Le compte de l'administrateur enseignant existe déjà dans la base de données.";
    } else {
        // 2. Génération d'un mot de passe aléatoire sécurisé de 12 caractères
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $mot_de_passe_clair = '';
        $max = strlen($caracteres) - 1;
        for ($i = 0; $i < 12; $i++) {
            $mot_de_passe_clair .= $caracteres[random_int(0, $max)];
        }

        // 3. Hachage strict du mot de passe (Exigence 5.4 & 6 : Jamais stocké en clair)
        $mot_de_passe_hache = password_hash($mot_de_passe_clair, PASSWORD_DEFAULT);

        // 4. Insertion dans la table des administrateurs
        $sql = "INSERT INTO administrateurs (prenom, nom, email, mot_de_passe, date_creation) 
                VALUES (:prenom, :nom, :email, :mdp, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':prenom' => $prenom_prof,
            ':nom'    => $nom_prof,
            ':email'   => $email_prof,
            ':mdp'     => $mot_de_passe_hache
        ]);

        // Stockage des identifiants pour l'affichage unique à l'écran
        $identifiants = [
            'email' => $email_prof,
            'mdp'   => $mot_de_passe_clair
        ];
        $message_succes = "Le compte administrateur du professeur a été créé avec succès !";
    }
} catch (PDOException $e) {
    error_log("Erreur setup_prof.php : " . $e->getMessage());
    $erreur = "Une erreur technique est survenue lors de la création du compte.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Compte Enseignant</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #081b29;
            font-family: 'Poppins', sans-serif;
            color: #fff;
        }
        .setup-box {
            width: 100%;
            max-width: 550px;
            padding: 40px;
            background: rgba(8, 27, 41, 0.85);
            border: 2px solid #00abf0;
            border-radius: 10px;
            box-shadow: 0 0 25px #00abf0;
            text-align: center;
        }
        h1 { color: #00abf0; font-size: 1.8rem; margin-bottom: 20px; }
        .alert-success { background: rgba(0, 255, 204, 0.1); border: 1px solid #00ffcc; color: #00ffcc; padding: 15px; border-radius: 6px; margin-bottom: 25px; font-size: 14px; }
        .alert-danger { background: rgba(255, 51, 51, 0.1); border: 1px solid #ff3333; color: #ff3333; padding: 15px; border-radius: 6px; margin-bottom: 25px; font-size: 14px; }
        .credentials-box { background: rgba(255,255,255,0.03); border: 1px dashed #00abf0; padding: 20px; border-radius: 6px; text-align: left; margin-bottom: 25px; }
        .credentials-box p { margin: 10px 0; font-size: 15px; }
        .credentials-box strong { color: #00abf0; }
        .warning-zone { background: rgba(255, 51, 51, 0.15); border-left: 4px solid #ff3333; padding: 15px; text-align: left; border-radius: 4px; font-size: 13px; color: #ffcccc; line-height: 1.5; }
        .warning-zone i { color: #ff3333; font-size: 18px; vertical-align: middle; margin-right: 5px; }
        .btn-admin { display: inline-block; margin-top: 25px; padding: 10px 20px; background: #00abf0; color: #081b29; text-decoration: none; border-radius: 6px; font-weight: 600; transition: .3s; }
        .btn-admin:hover { background: #fff; box-shadow: 0 0 10px #fff; }
    </style>
</head>
<body>

    <div class="setup-box">
        <h1><i class='bx bxs-user-badge'></i> Initialisation Compte Professeur</h1>

        <?php if (!empty($erreur)): ?>
            <div class="alert-danger">
                <i class='bx bx-error-circle'></i> <?= echapper($erreur); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($message_succes)): ?>
            <div class="alert-success">
                <i class='bx bx-check-circle'></i> <?= $message_succes; ?>
            </div>

            <div class="credentials-box">
                <p><strong>Nom complet :</strong> <?= echapper($prenom_prof . ' ' . $nom_prof); ?></p>
                <p><strong>Adresse Email :</strong> <span style="color: #fff;"><?= echapper($identifiants['email']); ?></span></p>
                <p><strong>Mot de passe généré :</strong> <span style="background: #fff; color: #081b29; padding: 2px 6px; font-family: monospace; font-weight: 700; border-radius: 3px;"><?= echapper($identifiants['mdp']); ?></span></p>
            </div>

            <div class="warning-zone">
                <p><i class='bx bxs-error'></i> <strong>DANGER CRITIQUE SÉCURITÉ :</strong></p>
                <p>Vous devez copier le mot de passe ci-dessus immédiatement. Pour respecter la consigne de Monsieur Diouf, supprimez maintenant le fichier <code>admin/setup_prof.php</code> de votre serveur local (XAMPP) avant de continuer.</p>
            </div>
        <?php endif; ?>

        <a href="connexion.php" class="btn-admin"><i class='bx bx-log-in-circle'></i> Aller à la page de connexion</a>
    </div>

</body>
</html>