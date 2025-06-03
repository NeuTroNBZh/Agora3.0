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
    if (!empty($_FILES['portfolio']['name'][0])) {
        $uploadDir = 'uploads/portfolios/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        foreach ($_FILES['portfolio']['tmp_name'] as $key => $tmp_name) {
            $fileName = basename($_FILES['portfolio']['name'][$key]);
            $fileType = $_FILES['portfolio']['type'][$key];
            $fileSize = $_FILES['portfolio']['size'][$key];
            $fileError = $_FILES['portfolio']['error'][$key];
            if ($fileError === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4'];
                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = "Le type de fichier n'est pas autorisé pour " . $fileName;
                    continue;
                }
                if ($fileSize > 10 * 1024 * 1024) {
                    $errors[] = "Le fichier " . $fileName . " est trop volumineux (max 10MB)";
                    continue;
                }
                $newFileName = uniqid() . '_' . $fileName;
                $uploadFile = $uploadDir . $newFileName;
                if (move_uploaded_file($tmp_name, $uploadFile)) {
                    $portfolioFiles[] = $newFileName;
                } else {
                    $errors[] = "Erreur lors de l'upload de " . $fileName;
                }
            } else {
                $errors[] = "Erreur lors de l'upload de " . $fileName;
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