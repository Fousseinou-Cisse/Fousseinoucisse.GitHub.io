<?php
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

verifier_authentification();

$erreurs = [];
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Contrôle CSRF
    if (!isset($_POST['csrf_token']) || !verifier_csrf($_POST['csrf_token'])) {
        $erreurs[] = "Jeton CSRF invalide.";
    } else {
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $technologies = trim($_POST['technologies'] ?? '');
        $lien = trim($_POST['lien'] ?? '');

        // Validation des champs obligatoires (Section 2.1)
        if (empty($titre)) $erreurs[] = "Le titre est obligatoire.";
        if (empty($description)) $erreurs[] = "La description est obligatoire.";
        if (empty($technologies)) $erreurs[] = "Le champ technologies est obligatoire.";

        // Traitement de l'image de couverture (Section 5.3)
        $nom_image_bdd = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            // Formats autorisés imposés
            $extensions_autorisees = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($extension, $extensions_autorisees)) {
                $erreurs[] = "Format d'image invalide. Uniquement : jpg, jpeg, png, webp, gif.";
            } else {
                // Génération d'un nom de fichier unique automatique
                $nom_image_bdd = bin2hex(random_bytes(16)) . '.' . $extension;
                $dossier_destination = '../../images/projets/' . $nom_image_bdd;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $dossier_destination)) {
                    $erreurs[] = "Échec du téléchargement de l'image.";
                }
            }
        }

        // Insertion
        if (empty($erreurs)) {
            try {
                $sql = "INSERT INTO projets (titre, description, technologies, image, lien, date_creation) 
                        VALUES (:titre, :description, :technologies, :image, :lien, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':titre' => $titre,
                    ':description' => $description,
                    ':technologies' => $technologies,
                    ':image' => $nom_image_bdd,
                    ':lien' => !empty($lien) ? $lien : null
                ]);
                $succes = "Le projet a bien été ajouté avec succès !";
            } catch (PDOException $e) {
                error_log("Erreur Insertion Projet : " . $e->getMessage());
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
    <title>Ajouter un Projet — Administration</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body { background-color: #081b29; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        .form-container { max-width: 700px; margin: 40px auto; background: rgba(255,255,255,0.02); border: 2px solid #00abf0; border-radius: 8px; padding: 30px; box-shadow: 0 0 15px rgba(0,171,240,0.1); }
        h1 { color: #00abf0; font-size: 1.8rem; margin-top: 0; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #aaa; font-size: 14px; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 12px; background: rgba(8, 27, 41, 0.6); border: 1px solid #00abf0; border-radius: 6px; color: #fff; font-size: 15px; box-sizing: border-box; outline: none; }
        textarea { height: 150px; resize: vertical; }
        .btn-submit { background: #00abf0; color: #081b29; padding: 12px 25px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 16px; transition: 0.3s; }
        .btn-submit:hover { background: #fff; box-shadow: 0 0 10px #fff; }
        .msg-err { color: #ff3333; margin-bottom: 20px; }
        .msg-ok { color: #00ffcc; margin-bottom: 20px; }
        .back-link { display: inline-flex; align-items: center; color: #aaa; text-decoration: none; margin-bottom: 20px; gap: 5px; }
        .back-link:hover { color: #00abf0; }
    </style>
</head>
<body>

<div class="form-container">
    <a href="index.php" class="back-link"><i class='bx bx-arrow-back'></i> Retour à la liste</a>
    <h1>Créer un nouveau projet</h1>

    <?php if(!empty($erreurs)): ?>
        <div class="msg-err"><ul><?php foreach($erreurs as $err) echo "<li>".echapper($err)."</li>"; ?></ul></div>
    <?php endif; ?>
    <?php if(!empty($succes)): ?>
        <div class="msg-ok"><?= $succes; ?></div>
    <?php endif; ?>

    <form action="ajouter.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $token_csrf; ?>">

        <div class="form-group">
            <label>Titre du projet *</label>
            <input type="text" name="titre" required>
        </div>

        <div class="form-group">
            <label>Description des réalisations *</label>
            <textarea name="description" required></textarea>
        </div>

        <div class="form-group">
            <label>Technologies utilisées (séparées par des virgules) *</label>
            <input type="text" name="technologies" placeholder="Ex: PHP, PDO, MySQL" required>
        </div>

        <div class="form-group">
            <label>Lien externe du projet (Optionnel)</label>
            <input type="text" name="lien" placeholder="https://github.com/...">
        </div>

        <div class="form-group">
            <label>Image d'illustration (jpg, jpeg, png, webp, gif) *</label>
            <input type="file" name="image" accept="image/*">
        </div>

        <button type="submit" class="btn-submit">Enregistrer le projet</button>
    </form>
</div>

</body>
</html>