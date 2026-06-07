<?php
// Récupération du nom du fichier actuel pour gérer la classe active
$page_courante = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <div class="navbar-left">
        <a href="index.php" class="logo">portfolio.</a>
    </div>

    <ul>
        <li><a href="index.php" class="<?= ($page_courante == 'index.php') ? 'active' : ''; ?>">Accueil</a></li>
        <li><a href="about.php" class="<?= ($page_courante == 'about.php') ? 'active' : ''; ?>">À propos</a></li>
        <li><a href="projets.php" class="<?= ($page_courante == 'projets.php') ? 'active' : ''; ?>">Les projets</a></li>
        <li><a href="bloc.php" class="<?= ($page_courante == 'bloc.php') ? 'active' : ''; ?>">Bloc</a></li>
        
        <li><a href="admin/connexion.php">Admin</a></li>
    </ul>
</nav>