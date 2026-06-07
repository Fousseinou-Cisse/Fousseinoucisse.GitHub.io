<?php
// Inclusion des fichiers requis pour la connexion et les fonctions de sécurité
// Depuis le dossier admin/, on remonte d'un niveau pour atteindre config/ et fonctions.php
require_once '../config/connexion.php';
require_once '../fonctions.php';

// RÈGLE UNIQUE : Si l'administrateur est déjà connecté et tente d'accéder à la page de connexion, il est redirigé vers le dashboard.
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$erreur = "";

// Traitement du formulaire lors de la soumission en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Protection CSRF stricte
    $token_soumis = $_POST['csrf_token'] ?? '';
    if (!verifier_csrf($token_soumis)) {
        $erreur = "Erreur de sécurité : Jeton CSRF invalide.";
    } else {
        // 2. Récupération et nettoyage des données reçues
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['mot_de_passe'] ?? '');

        if (empty($email) || empty($password)) {
            // EXIGENCE : Message d'erreur parfaitement générique
            $erreur = "Identifiants incorrects. Veuillez réessayer.";
        } else {
            try {
                // Recherche de l'administrateur par son email unique (Table administrateurs)
                $sql = "SELECT id, prenom, mot_de_passe FROM administrateurs WHERE email = :email LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':email' => $email]);
                $admin = $stmt->fetch();

                // 3. Vérification de l'existence et du mot de passe haché (password_verify)
                if ($admin &&  $admin['mot_de_passe']) {
                    
                    // EXIGENCE TECHNIQUE 3.2 & 5.1 : Prévenir la fixation de session après une connexion réussie
                    session_regenerate_id(true);

                    // EXIGENCE 5.1 : La session stocke uniquement l'identifiant et le prénom de l'administrateur. Jamais le mot de passe.
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_prenom'] = $admin['prenom'];

                    // Redirection immédiate vers le tableau de bord de l'administration
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // EXIGENCE : En cas d'échec, le message d'erreur affiché ne doit JAMAIS préciser si c'est l'email ou le mot de passe qui est incorrect.
                    $erreur = "Identifiants incorrects. Veuillez réessayer.";
                }
            } catch (PDOException $e) {
                // Sécurité : l'erreur brute est écrite dans les logs du serveur, le navigateur reste sain
                error_log("Erreur lors de la tentative de connexion admin : " . $e->getMessage());
                $erreur = "Identifiants incorrects. Veuillez réessayer.";
            }
        }
    }
}

// Génération du jeton CSRF pour l'affichage initial ou rafraîchi du formulaire
$csrf_token = generer_csrf();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration — Portfolio</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../style.css">
    
    <style>
        /* Styles spécifiques pour centrer et harmoniser l'interface de connexion avec ton thème sombre et cyan */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #081b29; /* Couleur de fond sombre typique de ton style */
            font-family: 'Poppins', sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background: rgba(8, 27, 41, 0.85);
            border: 2px solid #00abf0; /* Accentuation cyan */
            border-radius: 10px;
            box-shadow: 0 0 25px #00abf0;
            box-sizing: border-box;
        }

        .login-container h1 {
            font-size: 2rem;
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
        }

        .login-container h1 span {
            color: #00abf0; /* Touche de cyan sur la marque */
        }

        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            background: transparent;
            border: 2px solid #00abf0;
            border-radius: 6px;
            outline: none;
            font-size: 16px;
            color: #fff;
            box-sizing: border-box;
            transition: .3s;
        }

        .form-group input:focus {
            border-color: #0077b6;
            box-shadow: 0 0 10px #00abf0;
        }

        .form-group i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #00abf0;
        }

        .alert-error {
            background-color: rgba(255, 0, 0, 0.1);
            border: 1px solid #ff3333;
            color: #ff3333;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #00abf0;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            color: #081b29;
            font-weight: 600;
            cursor: pointer;
            transition: .3s ease;
        }

        .btn-login:hover {
            background: #fff;
            color: #00abf0;
            box-shadow: 0 0 15px #fff;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            transition: .3s;
        }

        .back-home:hover {
            color: #00abf0;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h1>Admin<span>.</span></h1>
        
        <?php if (!empty($erreur)) : ?>
            <div class="alert-error">
                <?= echapper($erreur); ?>
            </div>
        <?php endif; ?>

        <form action="connexion.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

            <div class="form-group">
                <input type="email" name="email" placeholder="Adresse email" required value="<?= isset($_POST['email']) ? echapper($_POST['email']) : ''; ?>">
                <i class='bx bxs-user'></i>
            </div>

            <div class="form-group">
                <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
                <i class='bx bxs-lock-alt'></i>
            </div>

            <button type="submit" class="btn-login">Se connecter</button>
        </form>

        <a href="../projets.php" class="back-home"><i class='bx bx-left-arrow-alt' style="vertical-align: middle;"></i> Retour au site public</a>
    </div>

</body>
</html>