<?php
// fonctions.php

// EXIGENCE TECHNIQUE 3.2 : Chaque page doit démarrer la session avec session_start() avant tout autre traitement.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 3.2. Sécurité — Protection XSS
 * Échappe les données dynamiques à afficher en HTML
 */
function echapper($valeur) {
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * 3.2. Sécurité — Génération du jeton CSRF et stockage en session
 */
function generer_csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 3.2. Sécurité — Vérifie la validité du jeton CSRF soumis
 */
function verifier_csrf($token_soumis) {
    if (!isset($_SESSION['csrf_token']) || empty($token_soumis)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token_soumis);
}

/**
 * 4.1. Récupère l'adresse IP réelle, même derrière un proxy/hébergeur
 */
function obtenir_ip() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * 4.1. Journalisation des visites
 * Enregistre automatiquement la visite d'une page publique dans la table 'visites'
 */
function journaliser_visite($pdo, $nom_page) {
    $ip = obtenir_ip();
    
    try {
        $sql = "INSERT INTO visites (adresse_ip, page, date_visite) VALUES (?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ip, $nom_page]);
    } catch (PDOException $e) {
        error_log("Erreur lors de la journalisation de la visite : " . $e->getMessage());
    }
}

/**
 * 5. Espace d'administration
 * Vérifie si l'administrateur est connecté, sinon redirection immédiate
 */
function verifier_authentification() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: http://" . $_SERVER['HTTP_HOST'] . "/portfolio/admin/connexion.php");
        exit();
    }
}

/**
 * 4.2. Recherche de projets
 * Interroge la base de données avec une requête utilisant LIKE sur le titre et la description
 * @param PDO $pdo
 * @param string $terme_recherche
 * @return array Liste des projets correspondants
 */
function rechercher_projets(PDO $pdo, $terme_recherche) {
    try {
        // Préparation du motif pour le LIKE (%terme%)
        $recherche_motif = "%" . $terme_recherche . "%";
        
        // Requête SQL utilisant LIKE sur le titre ET la description (Exigence 4.2)
        $sql = "SELECT * FROM projets WHERE titre LIKE :recherche OR description LIKE :recherche ORDER BY date_creation DESC";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([':recherche' => $recherche_motif]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la recherche de projets : " . $e->getMessage());
        return [];
    }
}

/**
 * 4.3. Formulaire de contact
 * Valide et insère le message dans la table messages_contact
 * @param PDO $pdo
 * @param array $donnees ($_POST)
 * @return array Contient le statut du traitement ('succes' ou 'erreurs')
 */
function traiter_formulaire_contact(PDO $pdo, array $donnees) {
    $erreurs = [];
    $succes = false;

    // 1. Vérification stricte du jeton CSRF (Exigence 4.3)
    if (!isset($donnees['csrf_token']) || !verifier_csrf($donnees['csrf_token'])) {
        $erreurs[] = "Erreur de sécurité : Jeton CSRF invalide.";
        return ['succes' => false, 'erreurs' => $erreurs];
    }

    // 2. Nettoyage des données reçues
    $nom = trim($donnees['nom'] ?? '');
    $email = trim($donnees['email'] ?? '');
    $message = trim($donnees['message'] ?? '');

    // 3. Règles de validation (Héritées de la Partie 2)
    if (empty($nom)) {
        $erreurs[] = "Le nom est obligatoire.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "Un email valide est obligatoire.";
    }
    if (empty($message)) {
        $erreurs[] = "Le message ne peut pas être vide.";
    }

    // 4. Insertion en base de données si aucune erreur
    if (empty($erreurs)) {
        try {
            // Respect strict du nom de la table et des colonnes imposées (lu = 0 par défaut en base)
            $sql = "INSERT INTO messages_contact (nom, email, message, lu, date_envoi) VALUES (:nom, :email, :message, 0, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom'     => $nom,
                ':email'    => $email,
                ':message'  => $message
            ]);
            $succes = true;
        } catch (PDOException $e) {
            error_log("Erreur insertion messages_contact : " . $e->getMessage());
            $erreurs[] = "Une erreur technique est survenue. Impossible d'envoyer votre message.";
        }
    }

    return ['succes' => $succes, 'erreurs' => $erreurs];
}

/**
 * 4.4. Formulaire de demande de projet
 * Valide et insère la demande dans la table demandes_project
 * @param PDO $pdo
 * @param array $donnees ($_POST)
 * @return array Contient le statut du traitement ('succes' ou 'erreurs')
 */
function traiter_demande_projet(PDO $pdo, array $donnees) {
    $erreurs = [];
    $succes = false;

    // 1. Vérification stricte du jeton CSRF (Exigence 4.4)
    if (!isset($donnees['csrf_token']) || !verifier_csrf($donnees['csrf_token'])) {
        $erreurs[] = "Erreur de sécurité : Jeton CSRF invalide.";
        return ['succes' => false, 'erreurs' => $erreurs];
    }

    // 2. Nettoyage des données reçues
    $nom = trim($donnees['nom'] ?? '');
    $email = trim($donnees['email'] ?? '');
    $type_projet = trim($donnees['type_projet'] ?? '');
    $description = trim($donnees['description'] ?? '');
    $budget = trim($donnees['budget'] ?? ''); // Optionnel, NULL par défaut

    // 3. Règles de validation (Héritées de la Partie 2)
    if (empty($nom)) {
        $erreurs[] = "Le nom est obligatoire.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "Un email valide est obligatoire.";
    }
    if (empty($type_projet)) {
        $erreurs[] = "Le type de projet est obligatoire.";
    }
    if (empty($description)) {
        $erreurs[] = "La description du projet est obligatoire.";
    }

    // 4. Insertion en base de données si aucune erreur
    if (empty($erreurs)) {
        try {
            // Gestion de la colonne optionnelle budget
            $budget_valeur = !empty($budget) ? $budget : null;

            // Respect strict des colonnes de la table 'demandes_projet'
            $sql = "INSERT INTO demandes_projet (nom, email, type_projet, description, budget, lu, date_demande) 
                    VALUES (:nom, :email, :type_projet, :description, :budget, 0, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom'         => $nom,
                ':email'        => $email,
                ':type_projet'  => $type_projet,
                ':description'  => $description,
                ':budget'       => $budget_valeur
            ]);
            $succes = true;
        } catch (PDOException $e) {
            error_log("Erreur insertion demandes_projet : " . $e->getMessage());
            $erreurs[] = "Une erreur technique est survenue. Impossible d'enregistrer votre demande.";
        }
    }

    return ['succes' => $succes, 'erreurs' => $erreurs];
}