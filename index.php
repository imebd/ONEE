<?php 
session_start();
require_once 'connexion.php';

$erreur = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    if (!empty($email) && !empty($mot_de_passe)) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $mot_de_passe === $user['mot_de_passe']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'EMPLOYE') {
                header("Location: employe/dashboard_employe.php");
            } elseif ($user['role'] === 'TECHNICIEN') {
                header("Location: technicien/dashboard_technicien.php");
            } elseif ($user['role'] === 'ADMINISTRATEUR') {
                header("Location: administrateur/dashboard_admin.php");
            }
            exit();
        } else {
            $erreur = "Identifiants incorrects.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<?DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ONEE Support -Connexion</title>
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-card">
    <div class="text-center">
        <img src="logo_onee.png" alt="Logo ONEE" class="logo-onee">
        <h3 class="brand-title">ONEE SUPPORT</h3>
        <p class="text-muted">Portail d'Assistance Technique & IA</p>
    </div>
<?php if (!empty($erreur)): ?>
        <div class="alert-danger text-center">
            <?php echo $erreur; ?>
        </div>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <div class="form-group">
            <label for="email" class="form-label">Adresse Email Professionnelle</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="ex: imane@onee.ma" required>
        </div>
        
        <div class="form-group">
            <label for="mot_de_passe" class="form-label">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-custom">Se connecter</button>
    </form>
</div>

</body>
</html>
