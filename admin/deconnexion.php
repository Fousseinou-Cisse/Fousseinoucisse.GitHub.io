<?php
// admin/deconnexion.php

// 1. Démarrer la session pour pouvoir la manipuler
session_start();

// 2. Détruire toutes les variables de session
$_SESSION = [];

// 3. Si un cookie de session est utilisé, le supprimer du navigateur
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Détruire la session côté serveur
session_destroy();

// 5. Rediriger l'utilisateur vers la page de connexion
header("Location: connexion.php");
exit();