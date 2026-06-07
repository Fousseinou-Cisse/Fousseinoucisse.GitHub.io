<?php
// Définition des paramètres de connexion à la base de données
// En local (XAMPP), l'utilisateur est 'root' et le mot de passe est vide ''
// Ces valeurs seront à modifier lors du passage en production (Hébergeur)
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');



try {
    // Construction de la chaîne de connexion (DSN) avec l'encodage imposé utf8mb4
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    // Options de configuration de PDO
    $options = [
        // Activation obligatoire du mode exception pour capturer les erreurs de manière sécurisée
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Mode de récupération par défaut des résultats sous forme de tableau associatif
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Désactivation de la simulation des requêtes préparées pour plus de sécurité
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Initialisation de la connexion PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    

} catch (PDOException $e) {
    // EXIGENCE SÉCURITÉ : Enregistrement de l'erreur réelle dans les logs du serveur (error_log)
    // Le message réel contenant les identifiants ou chemins ne sera JAMAIS affiché dans le navigateur
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());

    // EXIGENCE SÉCURITÉ : Affichage d'un message générique et propre pour l'utilisateur
    // Le code s'arrête immédiatement (die) pour éviter de charger le reste de la page sans connexion
    die("Une erreur est survenue lors de la connexion au serveur. Veuillez réessayer plus tard.");
}