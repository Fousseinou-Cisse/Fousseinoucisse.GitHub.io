<?php
function nettoyer($valeur) {
    return htmlspecialchars(strip_tags(trim($valeur)));
}

$projets = [
    [
        'titre'        => 'Mon projet en réseau',
        'description'  => 'Projet : Surveillance intelligente d\'une chambre de malade <br>
Description : Ce projet a été réalisé dans le cadre du module de conception générale des réseaux, dans le but d\'évaluer <br>
mes compétences pratiques. Il consiste à mettre en place un système de surveillance permettant à un médecin <br>
de suivre à distance les mouvements d\'un patient dans sa chambre.<br>
Fonctionnement : Le système repose sur un détecteur de mouvement installé dans la <br>
chambre du malade. Lorsqu\'un mouvement est détecté, les informations sont transmises <br>
au médecin, lui permettant de surveiller l\'état du patient en temps réel. <br>
Architecture du projet : Le projet est structuré autour de deux environnements : <br>
Maison 1 : représente le domicile du médecin <br>
Maison 2 : représente la chambre du malade <br>
Technologie utilisée : Le projet a été réalisé avec Cisco Packet Tracer, un outil de simulation réseau largement <br>
utilisé dans la formation en réseaux informatiques. <br>
Équipements utilisés : Routeur , Switch , Câbles droits , Téléphone IP , Caméra , Serveur DHCP <br>
Sirène d\'alarme , Point d\'accès (Wi-Fi) , Lampe connectée.',
        'technologies' => ['Routeur', 'Switch', 'Téléphone IP', 'Caméra', 'Serveur DHCP', 'Point d\'accès Wi-Fi', 'Lampe connectée'],
        'video'        => 'video/réseau.mp4',
        'images'       => [],
        'classe'       => 'video',
        'classe_p'     => 'description-projet',
        'classe_video' => 'project-video',
    ],
    [
        'titre'        => 'Mon projet en langage C',
        'description'  => 'Projet : Application de gestion de répertoire téléphonique en C <br>
Description :<br>
Ce projet a été réalisé en groupe dans le cadre du module de langage C, comme évaluation <br>
de fin de module. Il consiste à développer une application permettant de gérer un répertoire <br>
téléphonique de manière efficace. <br>
Ce projet a été réalisé en collaboration avec mes camarades de classe. Grâce à une bonne <br>
organisation et un travail sérieux, nous avons réussi à valider le module avec la mention <br>
bien, en obtenant une note de 15/20. <br>
Objectif : L\'objectif principal est de concevoir une application capable d\'enregistrer, <br>
consulter, modifier et supprimer des contacts dans un répertoire téléphonique. <br>
Base de données : Le projet intègre une base de données MySQL reliée au programme en C, <br>
permettant de stocker et gérer les informations des contacts de manière structurée et persistante. <br>
Technologies utilisées : Langage C et MySQL. <br>
Fonctionnalités principales : Ajout de contacts , Consultation des contacts , Modification des informations <br>
Suppression de contacts <br>
Concepts utilisés : Fonctions , Variables , Structures , Chaînes de caractères , Boucles.',
        'technologies' => ['Langage C', 'MySQL'],
        'video'        => 'video/langage.mp4',
        'images'       => [],
        'classe'       => 'Langage-C',
        'classe_p'     => 'langage',
        'classe_video' => 'langage-video',
    ],
    [
        'titre'        => 'Mon projet en sécurité',
        'description'  => 'Projet : Génération de clés cryptographiques avec Kali-Linux <br>
Description : <br>
Ce projet a été réalisé après le module de sécurité informatique afin de mettre en pratique les notions apprises. <br>
Il consiste à générer des clés cryptographiques (clé publique et clé privée) utilisées pour sécuriser les <br>
communications et les données. <br>
Objectif : <br>
L\'objectif est de comprendre les principes de base de la cryptographie et de maîtriser le processus <br>
de génération de clés pour assurer la confidentialité et la sécurité des échanges. <br>
Technologie utilisée : Kali Linux <br>
Outils et méthodes : <br>
Kali Linux est un système spécialisé dans les tests de sécurité informatique, permettant d\'effectuer <br>
des analyses, de protéger les systèmes et de gérer des connexions à distance. <br>
Compétences développées : <br>
Utilisation des commandes de base sous Linux <br>
Compréhension des mécanismes de cryptographie <br>
Gestion des clés publiques et privées <br>
Initiation à la sécurité des systèmes',
        'technologies' => ['Kali Linux'],
        'video'        => '',
        'images'       => ['image/image1.png', 'image/image2.png', 'image/image3.png', 'image/image4.png'],
        'classe'       => 'sécurité',
        'classe_p'     => 'sécurité-S',
        'classe_video' => '',
    ],
];

$mot_cle   = nettoyer($_GET['q'] ?? '');
$resultats = [];

if ($mot_cle !== '') {
    foreach ($projets as $projet) {
        if (stripos($projet['titre'],       $mot_cle) !== false ||
            stripos($projet['description'], $mot_cle) !== false) {
            $resultats[] = $projet;
        }
    }
} else {
    $resultats = $projets;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
</head>
<body>
   <nav class="navbar">

    <div class="navbar-left">
        <a href="#" class="logo">portfolio.</a>
    </div>

   <?php require 'composants/navigation.php'; ?> 

</nav>

<div class="recherche" style="margin-top: 150px; margin-left: 30px;">
    <form action="projets.php" method="GET" class="rechercher">
        <input type="text" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($mot_cle) ?>">
        <button type="submit">🔍</button>
    </form>
</div>

<?php if ($mot_cle !== '') : ?>
    <p style="margin-top: 19%; color: cyan;">Résultats pour : <strong><?= $mot_cle ?></strong></p>
<?php endif; ?>

<?php if (empty($resultats)) : ?>
    <p style="margin-top: 19%;">Aucun projet ne correspond à votre recherche.</p>
<?php else : ?>

    <?php foreach ($resultats as $projet) : ?>
        <section class="<?= $projet['classe'] ?>" style="margin-top: 15px;">
            <h2><?= $projet['titre'] ?></h2>
            <p class="<?= $projet['classe_p'] ?>"><?= $projet['description'] ?></p>

            <?php if (!empty($projet['video'])) : ?>
                <div class="<?= $projet['classe_video'] ?>">
                    <video controls>
                        <source src="<?= $projet['video'] ?>" type="video/mp4">
                    </video>
                </div>
            <?php endif; ?>

            <?php if (!empty($projet['images'])) : ?>
                <div class="sécurité-image">
                    <?php foreach ($projet['images'] as $img) : ?>
                        <img src="<?= $img ?>" alt="<?= $projet['titre'] ?>">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>

<?php endif; ?>

  <?php require 'composants/footer.php'; ?> 
</body>
</html>