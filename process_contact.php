<?php
// Démarrer la session
session_start();

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $accept_rules = isset($_POST['accept-rules']) ? true : false;

    // Tableau pour stocker les erreurs
    $errors = [];

    // Validation des données
    if (empty($name)) {
        $errors[] = "Le nom est requis.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Une adresse email valide est requise.";
    }

    if (empty($subject)) {
        $errors[] = "Le sujet est requis.";
    }

    if (empty($message)) {
        $errors[] = "Le message est requis.";
    }

    if (!$accept_rules) {
        $errors[] = "Vous devez accepter le traitement de vos données.";
    }

    // Si pas d'erreurs, procéder à l'envoi des emails
    if (empty($errors)) {
        // Configuration des en-têtes pour l'email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Agora <contact@agora.rip>" . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";

        // Email pour l'administrateur
        $admin_subject = "Nouveau message de contact - " . $subject;
        $admin_message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #7289da; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Nouveau message de contact</h2>
                </div>
                <div class='content'>
                    <p><strong>De :</strong> {$name} ({$email})</p>
                    <p><strong>Sujet :</strong> {$subject}</p>
                    <p><strong>Message :</strong></p>
                    <p>" . nl2br(htmlspecialchars($message)) . "</p>
                </div>
                <div class='footer'>
                    <p>Ce message a été envoyé depuis le formulaire de contact d'Agora.</p>
                </div>
            </div>
        </body>
        </html>";

        // Email de confirmation pour l'utilisateur
        $user_subject = "Confirmation de votre message - Agora";
        $user_message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #7289da; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Confirmation de votre message</h2>
                </div>
                <div class='content'>
                    <p>Bonjour {$name},</p>
                    <p>Nous avons bien reçu votre message et nous vous en remercions.</p>
                    <p>Nous vous répondrons dans les plus brefs délais.</p>
                    <p>Voici un récapitulatif de votre message :</p>
                    <p><strong>Sujet :</strong> {$subject}</p>
                    <p><strong>Message :</strong></p>
                    <p>" . nl2br(htmlspecialchars($message)) . "</p>
                </div>
                <div class='footer'>
                    <p>Ceci est un message automatique, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>";

        // Envoi des emails
        $admin_sent = mail('contact@agora.rip', $admin_subject, $admin_message, $headers);
        $user_sent = mail($email, $user_subject, $user_message, $headers);

        if ($admin_sent && $user_sent) {
            // Redirection avec message de succès
            header('Location: contact?status=success');
            exit;
        } else {
            // Redirection avec message d'erreur
            header('Location: contact?status=error&message=Erreur lors de l\'envoi du message. Veuillez réessayer.');
            exit;
        }
    } else {
        // Redirection avec les erreurs
        $error_message = implode(', ', $errors);
        header('Location: contact?status=error&message=' . urlencode($error_message));
        exit;
    }
} else {
    // Redirection si accès direct au script
    header('Location: contact');
    exit;
} 