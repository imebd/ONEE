<?php 
session_start();
require_once '../connexion.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'EMPLOYE') {
    header("Location: ../index.php");
    exit();
}

$id_employe = $_SESSION['user_id'];
$message_succes = "";
$message_erreur = "";

// Traitement de la création du ticket lors de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['creer_ticket'])) {
    $description = trim($_POST['description']);
    $priorite = $_POST['priorite'];

    if (!empty($description)) {
        // Insertion du nouveau ticket lié à l'employé connecté
        $stmt = $pdo->prepare("INSERT INTO tickets (description, priorite, employe_id, statut) VALUES (?, ?, ?, 'EN_ATTENTE')");
        if ($stmt->execute([$description, $priorite, $id_employe])) {
            $message_succes = "Votre ticket a bien été enregistré avec succès.";
        } else {
            $message_erreur = "Une erreur est survenue lors de la création de votre ticket.";
        }
    } else {
        $message_erreur = "Veuillez décrire le problème rencontré.";
    }
}

// Récupération de l'historique des tickets de cet employé
$stmt_tickets = $pdo->prepare("SELECT * FROM tickets WHERE employe_id = ? ORDER BY date_creation DESC");
$stmt_tickets->execute([$id_employe]);
$tickets = $stmt_tickets->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon espace Support - Employé</title>
    <link href="../css/style.css" rel="stylesheet">
</head> 
<body class="dashboard-body">

<!-- 1. La barre de bienvenue est bien TOUTE SEULE en haut -->
<div class="header-bar">
    <div class="user-info">
        <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></h2>
        <span class="text-muted">Espace Employé — ONEE</span>
    </div>
    <div>
        <a href="../deconnexion.php" class="btn-logout">Déconnexion</a>
    </div>
</div>

<!-- 2. La grille commence ICI uniquement pour les deux boîtes du bas -->
<div class="dashboard-grid">
    
    <!-- Panneau de gauche : Formulaire -->
    <div class="panel">
        <h3 class="panel-title">Déclarer un incident technique</h3>
        
        <?php if (!empty($message_succes)): ?>
            <div class="msg msg-success"><?php echo $message_succes; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($message_erreur)): ?>
            <div class="msg msg-error"><?php echo $message_erreur; ?></div>
        <?php endif; ?>

        <form action="dashboard_employe.php" method="POST">
            <div class="form-group">
                <label for="priorite" class="form-label">Niveau d'urgence</label>
                <select name="priorite" id="priorite" class="form-control">
                    <option value="NORMAL">Normal (Panne simple, consommable...)</option>
                    <option value="URGENT">Urgent (Bloquant pour travailler)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description détaillée du problème</label>
                <textarea name="description" id="description" class="form-control" rows="6" placeholder="Ex: Impossible d'accéder au réseau local, ou mon imprimante n'imprime plus..." required></textarea>
            </div>

            <button type="submit" name="creer_ticket" class="btn-custom">Envoyer la demande</button>
        </form>
    </div>

    <!-- Panneau de droite : Historique -->
    <div class="panel">
        <h3 class="panel-title">Suivi de mes demandes</h3>
        
        <?php if (empty($tickets)): ?>
            <p class="text-muted text-center" style="margin-top: 40px;">Vous n'avez pas encore déclaré d'incident.</p>
        <?php else: ?>
            <table class="table-tickets">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Urgence</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo date('d/m à H:i', strtotime($ticket['date_creation'])); ?></td>
                            <td>
                                <?php 
                                    echo htmlspecialchars(substr($ticket['description'], 0, 45)); 
                                    if (strlen($ticket['description']) > 45) echo '...';
                                ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($ticket['priorite']); ?>">
                                    <?php echo $ticket['priorite']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-en_attente">
                                    <?php echo str_replace('_', ' ', $ticket['statut']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div> 

</body>
</html>