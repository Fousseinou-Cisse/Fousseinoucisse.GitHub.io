<?php
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

// EXIGENCE SÉCURITÉ : Validation de session active
verifier_authentification();

// EXIGENCE SÉCURITÉ 5.3 : La suppression doit obligatoirement passer par POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // EXIGENCE SÉCURITÉ 5.3 : Vérification obligatoire du jeton CSRF
    $token_soumis = $_POST['csrf_token'] ?? '';
    if (!verifier_csrf($token_soumis)) {
        die("Erreur de sécurité critique : Jeton CSRF non valide.");
    }

    $id_projet = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id_projet > 0) {
        try {
            // Optionnel : Tu peux récupérer le nom de l'image pour la détruire sur le disque avec unlink() avant d'effacer la ligne SQL
            
            // Requête préparée pour éviter toute injection
            $sql = "DELETE FROM projets WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id_projet]);

        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du projet : " . $e->getMessage());
        }
    }
}

// Redirection systématique vers la liste après le traitement
header("Location: index.php");
exit();