<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Validation des données
    $errors = [];

    if (empty($name)) {
        $errors[] = "Le nom est requis";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }

    if (empty($subject)) {
        $errors[] = "Le sujet est requis";
    }

    if (empty($message)) {
        $errors[] = "Le message est requis";
    }

    // Si pas d'erreurs, on traite le message
    if (empty($errors)) {
        try {
            // Insertion dans la base de données
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message, created_at)
                VALUES (:name, :email, :subject, :message, NOW())
            ");

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);

            // Envoi d'un email de confirmation
            $to = $email;
            $headers = "From: contact@agora.com\r\n";
            $headers .= "Reply-To: contact@agora.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $emailSubject = "Confirmation de votre message - Agora";
            $emailMessage = "
                <html>
                <head>
                    <title>Confirmation de votre message</title>
                </head>
                <body>
                    <h2>Merci de nous avoir contacté !</h2>
                    <p>Nous avons bien reçu votre message et nous vous répondrons dans les plus brefs délais.</p>
                    <p>Voici un récapitulatif de votre message :</p>
                    <ul>
                        <li><strong>Sujet :</strong> {$subject}</li>
                        <li><strong>Message :</strong> {$message}</li>
                    </ul>
                    <p>Cordialement,<br>L'équipe Agora</p>
                </body>
                </html>
            ";

            mail($to, $emailSubject, $emailMessage, $headers);

            // Redirection avec message de succès
            header('Location: contact.html?status=success');
            exit;

        } catch (PDOException $e) {
            $errors[] = "Une erreur est survenue lors de l'envoi du message";
            // Log de l'erreur
            error_log("Erreur d'envoi de message : " . $e->getMessage());
        }
    }

    // Si erreurs, redirection avec les erreurs
    if (!empty($errors)) {
        $errorString = implode(',', $errors);
        header('Location: contact.html?status=error&errors=' . urlencode($errorString));
        exit;
    }
} else {
    // Si accès direct au script, redirection vers la page de contact
    header('Location: contact.html');
    exit;
} 