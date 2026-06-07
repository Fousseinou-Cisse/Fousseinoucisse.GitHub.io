<?php
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

verifier_authentification();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$erreurs = [];
$succes = "";

// Chargement initial des données du projet ciblé
try {
    // CORRECTION : Suppression de la ligne avec 'proyectos' qui causait l'erreur SQL
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE id = ?");
    $stmt->execute([$id]);
    $projet = $projet_data = $stmt->fetch(); // On récupère proprement

    if (!$projet) {
        die("Projet introuvable avec l'ID : " . $id);
    }
} catch (PDOException $e) {
    // Pour déboguer, affiche le message réel pendant le développement :
    die("Erreur base de données : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifier_csrf($_POST['csrf_token'])) {
        $erreurs[] = "Jeton CSRF invalide.";
    } else {
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $technologies = trim($_POST['technologies'] ?? '');
        $lien = trim($_POST['lien'] ?? '');
        $nom_image_bdd = $projet['image']; 

        if (empty($titre) || empty($description) || empty($technologies)) {
            $erreurs[] = "Veuillez remplir tous les champs obligatoires.";
        }

        // Gestion de l'upload image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $extensions_autorisees = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($extension, $extensions_autorisees)) {
                $erreurs[] = "Format d'image invalide.";
            } else {
                $nom_image_bdd = bin2hex(random_bytes(16)) . '.' . $extension;
                move_uploaded_file($_FILES['image']['tmp_name'], '../../images/projets/' . $nom_image_bdd);
            }
        }

        if (empty($erreurs)) {
            try {
                $sql = "UPDATE projets SET titre = :t, description = :d, technologies = :tech, image = :img, lien = :l WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':t' => $titre,
                    ':d' => $description,
                    ':tech' => $technologies,
                    ':img' => $nom_image_bdd,
                    ':l' => !empty($lien) ? $lien : null,
                    ':id' => $id
                ]);
                $succes = "Modification enregistrée avec succès !";
                
                // Mettre à jour la variable $projet pour affichage immédiat
                $projet['titre'] = $titre;
                $projet['description'] = $description;
                $projet['technologies'] = $technologies;
                $projet['image'] = $nom_image_bdd;
                $projet['lien'] = $lien;
            } catch (PDOException $e) {
                $erreurs[] = "Erreur lors de la mise à jour : " . $e->getMessage();
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
    <title>Modifier le Projet</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body { background-color: #081b29; color: #fff; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        .form-container { max-width: 700px; margin: 40px auto; background: rgba(255,255,255,0.02); border: 2px solid #00abf0; border-radius: 8px; padding: 30px; }
        h1 { color: #00abf0; font-size: 1.8rem; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #aaa; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 12px; background: rgba(8, 27, 41, 0.6); border: 1px solid #00abf0; border-radius: 6px; color: #fff; box-sizing: border-box; outline: none; }
        textarea { height: 150px; }
        .current-img-preview { margin-top: 10px; display: block; max-width: 150px; border: 1px solid #00abf0; border-radius: 4px; }
        .btn-submit { background: #00abf0; color: #081b29; padding: 12px 25px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>

<div class="form-container">
    <a href="index.php" style="color: #aaa; text-decoration: none;"><i class='bx bx-arrow-back'></i> Retour</a>
    <h1>Modifier : <?= echapper($projet['titre']); ?></h1>

    <?php if(!empty($erreurs)): ?>
        <div style="color: #ff3333;"><ul><?php foreach($erreurs as $err) echo "<li>".echapper($err)."</li>"; ?></ul></div>
    <?php endif; ?>
    <?php if(!empty($succes)): ?>
        <div style="color: #00ffcc; margin-bottom: 20px;"><?= echapper($succes); ?></div>
    <?php endif; ?>

    <form action="modifier.php?id=<?= $projet['id']; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $token_csrf; ?>">

        <div class="form-group">
            <label>Titre du projet *</label>
            <input type="text" name="titre" value="<?= echapper($projet['titre']); ?>" required>
        </div>
        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" required><?= echapper($projet['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Technologies *</label>
            <input type="text" name="technologies" value="<?= echapper($projet['technologies']); ?>" required>
        </div>
        <div class="form-group">
            <label>Lien externe</label>
            <input type="text" name="lien" value="<?= echapper($projet['lien']); ?>">
        </div>
        <div class="form-group">
            <label>Remplacer l'image (Optionnel)</label>
            <input type="file" name="image" accept="image/*">
            <?php if(!empty($projet['image'])): ?>
                <label style="margin-top:10px;">Image actuelle :</label>
                <img src="../../images/projets/<?= echapper($projet['image']); ?>" class="current-img-preview">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn-submit">Mettre à jour le projet</button>
    </form>
</div>

</body>
</html>