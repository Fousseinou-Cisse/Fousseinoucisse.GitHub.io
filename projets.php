<?php
require_once 'config/connexion.php';
require_once 'fonctions.php';

// Journalisation de la visite
journaliser_visite($pdo, 'projets.php');

// Recherche de projets
$mot_cle = isset($_GET['q']) ? trim($_GET['q']) : '';
$resultats = [];

try {
    if ($mot_cle !== '') {
        $sql = "SELECT * FROM projets WHERE titre LIKE :motif OR description LIKE :motif ORDER BY date_creation DESC";
        $stmt = $pdo->prepare($sql);
        $motif = "%" . $mot_cle . "%";
        $stmt->execute([':motif' => $motif]);
        $resultats = $stmt->fetchAll();
    } else {
        $sql = "SELECT * FROM projets ORDER BY date_creation DESC";
        $stmt = $pdo->query($sql);
        $resultats = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Projets — Portfolio</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <style>
        .projets-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 800px)); 
            gap: 25px; 
            padding: 20px; 
            margin-top: 100px; 
            justify-content: center;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .project-card { 
            background: rgba(255,255,255,0.03); 
            border: 2px solid #00abf0; 
            border-radius: 8px; 
            padding: 15px; 
            transition: 0.4s; 
            width: 100%;
        }
        
        .project-card:hover { transform: translateY(-5px); box-shadow: 0 0 15px rgba(0, 171, 240, 0.3); }
        
        .project-media { 
            width: 100%; 
            height: 560px; 
            overflow: hidden; 
            margin-top: 12px; 
            border-radius: 6px; 
        }
        
        .project-media img, .project-media video { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }
        
        .project-card h2 { font-size: 1.1rem; color: #00abf0; margin-bottom: 8px; }
        .project-card p { font-size: 0.85rem; color: #ccc; line-height: 1.4; margin-bottom: 8px; }
        .tech-tags { font-size: 0.75rem; color: #00abf0; font-style: italic; margin-bottom: 10px; display: block; }
        
        .recherche-container { margin-top: 150px; text-align: center; }
        .rechercher input { padding: 8px; width: 250px; border-radius: 4px; border: 1px solid #00abf0; background: #081b29; color: white; }
        .rechercher button { padding: 8px 15px; background: #00abf0; border: none; border-radius: 4px; cursor: pointer; color: #081b29; font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <a href="index.php" class="logo">portfolio.</a>
    </div>
    <?php require 'composants/navigation.php'; ?> 
</nav>

<div class="recherche-container">
    <form action="projets.php" method="GET" class="rechercher">
        <input type="text" name="q" placeholder="Rechercher un projet..." value="<?= echapper($mot_cle) ?>">
        <button type="submit"><i class='bx bx-search'></i></button>
    </form>
</div>

<?php if ($mot_cle !== '') : ?>
    <p style="text-align: center; color: cyan; margin-top: 20px;">Résultats pour : <strong><?= echapper($mot_cle) ?></strong></p>
<?php endif; ?>

<main class="projets-grid">
    <?php if (empty($resultats)) : ?>
        <p style="color: #fff; text-align: center;">Aucun projet trouvé.</p>
    <?php else : ?>
        <?php foreach ($resultats as $projet) : ?>
            <article class="project-card">
                <h2><?= echapper($projet['titre']) ?></h2>
                <p><?= echapper($projet['description']) ?></p>
                
                <span class="tech-tags">Technologies : <?= echapper($projet['technologies']) ?></span>

                <?php if (!empty($projet['video'])) : ?>
                    <div class="project-media">
                        <video controls>
                            <source src="<?= echapper($projet['video']) ?>" type="video/mp4">
                        </video>
                    </div>
                <?php endif; ?>

                <?php if (!empty($projet['image'])) : ?>
                    <div class="project-media">
                        <img src="images/projets/<?= echapper($projet['image']) ?>" alt="<?= echapper($projet['titre']) ?>">
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($projet['lien'])) : ?>
                    <a href="<?= echapper($projet['lien']) ?>" style="display:inline-block; margin-top:10px; color:#00abf0; text-decoration:none; font-size: 0.85rem;">Voir le projet</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php require 'composants/footer.php'; ?> 
</body>
</html>