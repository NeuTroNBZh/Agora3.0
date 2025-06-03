# Agora - Plateforme Communautaire pour Créateurs de Contenu

Agora est une plateforme web communautaire dédiée aux créateurs de contenu, offrant un espace d'échange, de partage et de collaboration.

## Fonctionnalités

- **Actualités** : Articles et nouvelles de la communauté
- **Vidéos** : Galerie de vidéos des créateurs
- **Événements** : Calendrier des événements et rencontres
- **Espace Créateurs** : Profils et portfolios des créateurs
- **Communauté Discord** : Intégration avec le serveur Discord
- **Formulaire de Contact** : Pour toute question ou suggestion
- **Candidature Créateur** : Pour rejoindre la communauté

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx)
- Compte Discord (pour l'intégration)
- Compte SMTP (pour l'envoi d'emails)

## Installation

1. Cloner le dépôt :
```bash
git clone https://github.com/votre-username/agora.git
cd agora
```

2. Créer la base de données :
```bash
mysql -u root -p < database.sql
```

3. Configurer les variables d'environnement :
```bash
cp .env.example .env
# Éditer le fichier .env avec vos paramètres
```

4. Configurer la base de données :
```bash
cp config/database.example.php config/database.php
# Éditer le fichier avec vos paramètres de connexion
```

5. Créer les répertoires nécessaires :
```bash
mkdir -p uploads/{portfolios,profiles,articles,events}
mkdir -p cache logs
chmod -R 777 uploads cache logs
```

6. Configurer le serveur web :
- Pour Apache, assurez-vous que le module `mod_rewrite` est activé
- Pour Nginx, configurez la redirection vers `index.php`

## Structure du Projet

```
agora/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   ├── config.php
│   └── database.php
├── uploads/
│   ├── portfolios/
│   ├── profiles/
│   ├── articles/
│   └── events/
├── cache/
├── logs/
├── .env
├── .gitignore
├── database.sql
└── README.md
```

## Configuration

### Base de Données
- Créer une base de données MySQL
- Importer le fichier `database.sql`
- Configurer les paramètres dans `config/database.php`

### Email
- Configurer les paramètres SMTP dans `.env`
- Tester l'envoi d'emails

### Discord
- Créer un serveur Discord
- Obtenir l'URL d'invitation
- Configurer l'URL dans `.env`

## Sécurité

- Changer le mot de passe administrateur par défaut
- Configurer les permissions des répertoires
- Activer HTTPS
- Mettre à jour régulièrement les dépendances

## Contribution

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## Contact

- Email : contact@agora.com
- Discord : [Rejoindre le serveur](https://discord.gg/your-invite-code)
- Twitter : [@agora](https://twitter.com/agora)

## Remerciements

- Tous les contributeurs
- La communauté des créateurs de contenu
- Les outils et bibliothèques utilisés 