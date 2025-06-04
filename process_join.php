<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données (noms identiques au formulaire HTML)
    // $fullName = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $pseudo = htmlspecialchars(trim($_POST['pseudo'] ?? ''));
    $category = htmlspecialchars(trim($_POST['category'] ?? ''));
    $platforms = isset($_POST['platforms']) ? $_POST['platforms'] : [];
    $links = htmlspecialchars(trim($_POST['links'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $motivation = htmlspecialchars(trim($_POST['motivation'] ?? ''));
    $followers = htmlspecialchars(trim($_POST['followers'] ?? ''));
    $contact = htmlspecialchars(trim($_POST['contact'] ?? ''));
    $examples = htmlspecialchars(trim($_POST['examples'] ?? ''));

    // Validation des données
    $errors = [];
    // if (empty($fullName)) $errors[] = "Le nom complet est requis";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email n'est pas valide";
    if (empty($pseudo)) $errors[] = "Le pseudo est requis";
    if (empty($category)) $errors[] = "La catégorie est requise";
    if (empty($platforms)) $errors[] = "Veuillez sélectionner au moins une plateforme";
    if (empty($description)) $errors[] = "La description est requise";
    if (empty($motivation)) $errors[] = "La motivation est requise";
    if (empty($followers)) $errors[] = "Le nombre d'abonnés/followers est requis";
    if (empty($contact)) $errors[] = "Le contact est requis";

    // Traitement des fichiers uploadés
    $portfolioFiles = [];
    if (isset($_FILES['portfolio']) && is_array($_FILES['portfolio']['name'])) {
        $uploadDir = 'uploads/portfolio/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['portfolio']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['portfolio']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['portfolio']['name'][$key];
                $fileSize = $_FILES['portfolio']['size'][$key];
                $fileType = $_FILES['portfolio']['type'][$key];

                // Vérification du type de fichier
                $allowedTypes = ['image/jpeg', 'image/png', 'video/mp4'];
                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = "Type de fichier non autorisé pour $fileName";
                    continue;
                }

                // Vérification de la taille (10MB max)
                if ($fileSize > 10 * 1024 * 1024) {
                    $errors[] = "Le fichier $fileName est trop volumineux (max 10MB)";
                    continue;
                }

                // Génération d'un nom de fichier unique
                $newFileName = uniqid() . '_' . $fileName;
                $uploadFile = $uploadDir . $newFileName;

                if (move_uploaded_file($tmp_name, $uploadFile)) {
                    $portfolioFiles[] = $newFileName;
                } else {
                    $errors[] = "Erreur lors de l'upload de $fileName";
                }
            }
        }
    }

    // Construction du message email (toujours envoyé)
    $to = 'contact@agora.rip';
    $subject = 'Nouvelle candidature Agora';
    $message = "Nouvelle candidature Agora :\n\n";
    $message .= "Pseudo créateur : $pseudo\n";
    $message .= "Email : $email\n";
    $message .= "Catégorie : $category\n";
    $message .= "Plateformes : " . implode(', ', $platforms) . "\n";
    $message .= "Liens réseaux : $links\n";
    $message .= "Description : $description\n";
    $message .= "Motivation : $motivation\n";
    $message .= "Nombre d'abonnés/followers : $followers\n";
    $message .= "Contact (email/Discord) : $contact\n";
    $message .= "Exemples de contenu : $examples\n";
    if (!empty($portfolioFiles)) {
        $message .= "Fichiers portfolio uploadés :\n";
        foreach ($portfolioFiles as $file) {
            $message .= "- $file\n";
        }
    }
    $headers = "From: Agora <no-reply@agora.rip>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    mail($to, $subject, $message, $headers);

    // Si pas d'erreurs, on traite la candidature SQL
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO creator_applications (
                    email, pseudo, category, platforms, links,
                    description, motivation, followers, contact, examples, portfolio_files, created_at
                ) VALUES (
                    :email, :pseudo, :category, :platforms, :links,
                    :description, :motivation, :followers, :contact, :examples, :portfolioFiles, NOW()
                )
            ");
            $stmt->execute([
                'email' => $email,
                'pseudo' => $pseudo,
                'category' => $category,
                'platforms' => implode(',', $platforms),
                'links' => $links,
                'description' => $description,
                'motivation' => $motivation,
                'followers' => $followers,
                'contact' => $contact,
                'examples' => $examples,
                'portfolioFiles' => implode(',', $portfolioFiles)
            ]);

            // Email de confirmation pour le candidat
            $to = $email;
            $subject = "Confirmation de votre candidature - Agora";
            $message = "
                <html>
                <head>
                    <title>Confirmation de candidature</title>
                </head>
                <body>
                    <h2>Merci pour votre candidature !</h2>
                    <p>Nous avons bien reçu votre candidature pour rejoindre Agora. Notre équipe va l'étudier et vous recontactera dans les plus brefs délais.</p>
                    <p>Voici un récapitulatif de votre candidature :</p>
                    <ul>
                        <li><strong>Pseudo :</strong> {$pseudo}</li>
                        <li><strong>Catégorie :</strong> {$category}</li>
                        <li><strong>Plateformes :</strong> " . implode(', ', $platforms) . "</li>
                    </ul>
                    <p>Cordialement,<br>L'équipe Agora</p>
                </body>
                </html>
            ";

            $headers = "From: Agora <contact@agora.rip>\r\n";
            $headers .= "Reply-To: contact@agora.rip\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            mail($to, $subject, $message, $headers);

            // Email de notification pour l'administrateur
            $adminTo = "contact@agora.rip";
            $adminSubject = "Nouvelle candidature créateur - Agora";
            $adminMessage = "
                <html>
                <head>
                    <title>Nouvelle candidature créateur</title>
                </head>
                <body>
                    <h2>Nouvelle candidature reçue</h2>
                    <p>Une nouvelle candidature a été soumise :</p>
                    <ul>
                        <li><strong>Pseudo :</strong> {$pseudo}</li>
                        <li><strong>Email :</strong> {$email}</li>
                        <li><strong>Catégorie :</strong> {$category}</li>
                        <li><strong>Plateformes :</strong> " . implode(', ', $platforms) . "</li>
                        <li><strong>Liens réseaux :</strong> {$links}</li>
                        <li><strong>Description :</strong> {$description}</li>
                        <li><strong>Motivation :</strong> {$motivation}</li>
                        <li><strong>Nombre d'abonnés :</strong> {$followers}</li>
                        <li><strong>Contact :</strong> {$contact}</li>
                        <li><strong>Exemples :</strong> {$examples}</li>
                    </ul>
            ";

            if (!empty($portfolioFiles)) {
                $adminMessage .= "<p><strong>Fichiers portfolio :</strong></p><ul>";
                foreach ($portfolioFiles as $file) {
                    $adminMessage .= "<li>{$file}</li>";
                }
                $adminMessage .= "</ul>";
            }

            $adminMessage .= "
                    <p>Connectez-vous à l'administration pour gérer cette candidature.</p>
                </body>
                </html>
            ";

            $adminHeaders = "From: Agora <contact@agora.rip>\r\n";
            $adminHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";

            mail($adminTo, $adminSubject, $adminMessage, $adminHeaders);

            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Candidature envoyée</title><link rel="stylesheet" href="assets/css/style.css"></head><body><main style="min-height:60vh;display:flex;align-items:center;justify-content:center;"><div style="background:#fff;padding:2rem 3rem;border-radius:12px;box-shadow:0 2px 12px #0002;text-align:center;"><h1 style="color:#3498db;">Merci !</h1><p>Votre candidature a bien été envoyée.<br>L\'équipe Agora vous répondra rapidement.</p><a href="index.html" class="btn btn-primary" style="margin-top:1.5rem;">Retour à l\'accueil</a></div></main></body></html>';
        } catch (PDOException $e) {
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur</title><link rel="stylesheet" href="assets/css/style.css"></head><body><main style="min-height:60vh;display:flex;align-items:center;justify-content:center;"><div style="background:#fff;padding:2rem 3rem;border-radius:12px;box-shadow:0 2px 12px #0002;text-align:center;"><h1 style="color:#e74c3c;">Erreur</h1><p>Erreur SQL : ' . htmlspecialchars($e->getMessage()) . '</p><a href="rejoindre.html" class="btn btn-primary" style="margin-top:1.5rem;">Retour</a></div></main></body></html>';
        }
        exit;
    } else {
        // Affichage des erreurs de validation
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur</title><link rel="stylesheet" href="assets/css/style.css"></head><body><main style="min-height:60vh;display:flex;align-items:center;justify-content:center;"><div style="background:#fff;padding:2rem 3rem;border-radius:12px;box-shadow:0 2px 12px #0002;text-align:center;"><h1 style="color:#e74c3c;">Erreur</h1><ul style="color:#e74c3c;">';
        foreach ($errors as $err) echo '<li>' . htmlspecialchars($err) . '</li>';
        echo '</ul><a href="rejoindre.html" class="btn btn-primary" style="margin-top:1.5rem;">Retour</a></div></main></body></html>';
        exit;
    }
} else {
    header('Location: rejoindre.html');
    exit;
} 