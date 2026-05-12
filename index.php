<?php
function nettoyer($data) {
    return htmlspecialchars(trim($data));
}

// Formulaire contact
$nom = $prenom = $email = $message = "";
$erreurs = [];
$succes = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["form_contact"])) {

    $nom     = trim($_POST["nom"] ?? "");
    $prenom  = trim($_POST["prenom"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if (empty($nom)) {
        $erreurs["nom"] = "Nom obligatoire";
    }

    if (empty($email)) {
        $erreurs["email"] = "Email obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs["email"] = "Email invalide";
    }

    if (empty($message)) {
        $erreurs["message"] = "Message obligatoire";
    }

    if (empty($erreurs)) {
        $succes = "Message envoyé avec succès !";
        $nom = $prenom = $email = $message = "";
    }
}

// Formulaire projet
$erreursProjet = [];
$succesProjet = false;
$type_projet = $description = $budget = $emailProjet = $nomProjet = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["form_projet"])) {

    $nomProjet   = nettoyer($_POST["nomProjet"] ?? "");
    $emailProjet = nettoyer($_POST["emailProjet"] ?? "");
    $type_projet = nettoyer($_POST["type_projet"] ?? "");
    $description = nettoyer($_POST["description"] ?? "");
    $budget      = nettoyer($_POST["budget"] ?? "");

    if (empty($nomProjet)) {
        $erreursProjet["nomProjet"] = "Nom obligatoire";
    }

    if (empty($emailProjet)) {
        $erreursProjet["emailProjet"] = "Email obligatoire";
    } elseif (!filter_var($emailProjet, FILTER_VALIDATE_EMAIL)) {
        $erreursProjet["emailProjet"] = "Email invalide";
    }

    if (empty($type_projet)) {
        $erreursProjet["type_projet"] = "Type de projet requis";
    }

    if (empty($description)) {
        $erreursProjet["description"] = "Description obligatoire";
    }

    if (empty($budget)) {
        $erreursProjet["budget"] = "Budget obligatoire";
    }

    if (empty($erreursProjet)) {
        $succesProjet = true;
        $nomProjet = $emailProjet = $type_projet = $description = $budget = "";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php require 'composants/navigation.php'; ?>

    <section class="accueil">
        <div class="accueil-info">
            <h1>Fousseinou Saidou Cissé</h1>
            <h2>Étudiant en Génie Logiciel et Administration des Réseaux</h2>
            <p><h3>Passionné par l'informatique, je m'intéresse particulièrement au développement backend. <br>
Toujours en quête d'apprentissage, je souhaite concevoir des applications performantes, sécurisées et évolutives.</h3></p>
            <div class="cv-download">
                <a href="cv/CV.pdf" download class="cv-button">
                    <i class='bx bxs-download'></i> Télécharger mon CV
                </a>
            </div>

            <div class="tel">
                <a href="#" class="tel-cv">Cliquez ici pour accéder à mes réseaux</a>
                <div class="icons">
                    <a href="https://github.com/Fousseinou-Cisse"><i class='bx bxl-github'></i></a>
                    <a href="https://www.linkedin.com/in/fousseinou-cissé-09182b2aa?utm_source=share_via&utm_content=profile&utm_medium=member_ios"><i class='bx bxl-linkedin'></i></a>
                    <a href="https://www.facebook.com/share/15gPEjXxPxn/?mibextid=wwXIfr"><i class='bx bxl-facebook'></i></a>
                    <a href="https://www.instagram.com/cissefisto?igsh=ODd6NXMwNmF4cDN0&utm_source=ig_contact_invite"><i class='bx bxl-instagram-alt'></i></a>
                    <a href="https://wa.me/221773346405" target="_blank">
                        <i class='bx bxl-whatsapp'></i>
                    </a>
                </div>
            </div>

            <div class="call-icon">
                <a href="tel:+221773346505">
                    <i class='bx bxs-phone'></i>
                </a>
                <span class="phone-number">+221 77 334 64 05</span>
            </div>
        </div>

        <div class="image">
            <div class="img-box">
                <div class="img-item">
                    <img src="image/image.png" alt="">
                </div>
            </div>
        </div>
    </section>

    <!-- Formulaire contact -->
    <p class="contact-texte">N'hésitez pas à me contacter pour toute collaboration ou question.</p>

    <?php if (!empty($succes)) : ?>
        <p style="color:green;"><?= $succes ?></p>
    <?php endif; ?>

    <form action="" method="POST" style="margin-top: 40px;">
        <input type="hidden" name="form_contact" value="1">

        <div class="sortie">
            <input type="text" name="nom" placeholder="Nom" value="<?= htmlspecialchars($nom) ?>">
            <span style="color:red;"><?= $erreurs["nom"] ?? "" ?></span>

            <input type="text" name="prenom" placeholder="Prénom" value="<?= htmlspecialchars($prenom) ?>">
        </div>

        <div class="sortie">
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>">
            <span style="color:red;"><?= $erreurs["email"] ?? "" ?></span>
        </div>

        <textarea name="message" placeholder="Votre message..." rows="6"><?= htmlspecialchars($message) ?></textarea>
        <span style="color:red;"><?= $erreurs["message"] ?? "" ?></span>

        <button type="submit" class="bouton">Envoyer le message</button>
    </form>

    <!-- Formulaire projet -->
    <div class="project-form">
        <h2>Proposer un projet</h2>

        <?php if ($succesProjet) : ?>
            
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="form_projet" value="1">

            <input type="text" name="nomProjet" placeholder="Votre nom" value="<?= $nomProjet ?>">
            <span style="color:red;"><?= $erreursProjet["nomProjet"] ?? "" ?></span>

            <input type="email" name="emailProjet" placeholder="Votre email" value="<?= $emailProjet ?>">
            <span style="color:red;"><?= $erreursProjet["emailProjet"] ?? "" ?></span>

            <input type="text" name="type_projet" placeholder="Type de projet" value="<?= $type_projet ?>">
            <span style="color:red;"><?= $erreursProjet["type_projet"] ?? "" ?></span>

            <textarea name="description" placeholder="Décrivez votre projet..."><?= $description ?></textarea>
            <span style="color:red;"><?= $erreursProjet["description"] ?? "" ?></span>

            <input type="text" name="budget" placeholder="Budget estimé" value="<?= $budget ?>">
            <span style="color:red;"><?= $erreursProjet["budget"] ?? "" ?></span>

            <button type="submit">Envoyer</button>
        </form>
    </div>

    <section class="Merci">
        <p>Merci de visiter mon portfolio</p>
    </section>

    <?php require 'composants/footer.php'; ?>

</body>
</html>