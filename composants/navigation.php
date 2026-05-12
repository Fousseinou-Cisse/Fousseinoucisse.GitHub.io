<?php
// ici partie vas me permettre de recupérer le nom du fichier (ex: index.php, about.php, projets.php) 
// pour pouvoir ajouter la classe active à l'élément de navigation correspondant
$page_courante = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">

    <div class="navbar-left">
        <a href="#" class="logo">portfolio.</a>
    </div>

    <ul>
        <li><a href="index.php" class="active">Accueil</a></li>
        <li><a href="about.php">À propos</a></li>
        <li><a href="projets.php">Les projets</a></li>
        <li><a href="bloc.php">Bloc</a></li>
    </ul>

</nav>






