<?php
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

verifier_authentification();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifier_csrf($_POST['csrf_token'])) {
        die("Jeton CSRF invalide.");
    }

    $id_a_supprimer = isset($_POST['id']) ? intval($_POST['id']) : 0;

    // EXIGENCE 5.4 : Un administrateur ne peut pas supprimer son propre compte (Vérification côté SERVEUR)
    if ($id_a_supprimer === intval($_SESSION['admin_id'])) {
        die("Erreur de sécurité : Il est interdit de supprimer votre propre compte actif.");
    }

    if ($id_a_supprimer > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM administrateurs WHERE id = ?");
            $stmt->execute([$id_a_supprimer]);
        } catch (PDOException $e) {
            error_log("Erreur Suppression Admin : " . $e->getMessage());
        }
    }
}

header("Location: index.php");
exit();