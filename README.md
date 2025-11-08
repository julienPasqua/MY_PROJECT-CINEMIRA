# 🎬 Projet Cinéma Symfony "CINEMIRA"

![Symfony](https://img.shields.io/badge/Symfony-6.4-black?logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?logo=mysql)
![Doctrine](https://img.shields.io/badge/Doctrine-ORM-lightgrey)
![Twig](https://img.shields.io/badge/Twig-Template-brightgreen)
![License](https://img.shields.io/badge/License-MIT-green)

## 🚀 Description

Application Symfony de **gestion de cinéma**, permettant de gérer :

-   Les **Cinémas**
-   Les **Salles**
-   Les **Sièges**
-   Les **Séances**
-   Les **Réservations**
-   Les **Films**
-   Les **Genre**
-   Les **Utilisateur**
-   Les **Avis**

Le projet repose sur **Symfony 6.4**, **Doctrine ORM**, **Twig** et **MySQL**.

---

## ⚙️ Installation

```bash
# 1️⃣ Cloner le dépôt
git clone https://github.com/julienPasqua/MY_PROJECT-CINEMIRA.git

# 2️⃣ Aller dans le dossier
cd <MY_PROJECT>

# 3️⃣ Installer les dépendances
composer install

# 4️⃣ Créer la base de données
php bin/console doctrine:database:create

# 5️⃣ Lancer les migrations
php bin/console doctrine:migrations:migrate

# 6️⃣ Démarrer le serveur Symfony
symfony serve

------------------------------------------------

🧩 Technologies utilisées

PHP 8.2

Symfony 6.4

Doctrine ORM

MySQL

Twig

Composer


-----------------------------------------------


👨‍💻 Auteur

Julien Pasqua
Étudiant en conception web & développement logiciel
🎓 MIRA – Objectif Bac+3 en Intelligence Artificielle




-----------------------------------------------

🧾 Licence

Ce projet est sous licence MIT.
Vous pouvez l’utiliser, le modifier et le redistribuer librement.


-----------------------------------------------

## 🗺️ Roadmap ##

### 📅 Phase 1 : Conception & Modélisation ✅
- [x] 📐 Création du MCD (Modèle Conceptuel de Données)
- [x] 🎨 Diagrammes UML (Classes, Cas d'utilisation, Séquences)
- [x] 🗄️ Définition de la structure de la base de données
- [x] 🎯 Identification des entités et relations

### 📅 Phase 2 : Backend Symfony (En cours 🔄)
- [x] 🏗️ Initialisation du projet Symfony
- [x] 🎬 Création de l'entité `Film`
- [x] 🎭 Création de l'entité `Genre`
- [x] 🏢 Création de l'entité `Cinema`
- [x] 🚪 Création de l'entité `Salle`
- [x] 💺 Création de l'entité `Siege`
- [x] 🎫 Création de l'entité `Seance`
- [x] 📝 Création de l'entité `Reservation`
- [x] 👤 Création de l'entité `Utilisateur`
- [x] ⭐ Création de l'entité `Avis`
- [x] 🔗 Configuration des relations entre entités
- [x] 📋 Formulaires de création et d'édition (Cinema, Salle)
- [x] 🔄 Migrations de base de données
- [ ] 🔌 Intégration de l'API TheMovieDB
- [ ] 🎛️ Création des Controllers
- [ ] 🔐 Système d'authentification (Login/Register)
- [ ] 👨‍💼 Gestion des rôles (ROLE_USER, ROLE_ADMIN)

### 📅 Phase 3 : Frontend & Interface Utilisateur
- [x] 🎨 Page d'accueil avec films populaires
- [x] 🏆 Section "Film du Mois"
- [ ] 🔍 Page de recherche de films
- [ ] 📄 Page détails d'un film
- [ ] 🗓️ Page sélection de séance
- [x] 💺 Plan de salle interactif (HTML/CSS/JS)
- [ ] 🎟️ Page récapitulatif de réservation
- [ ] ✅ Page confirmation de réservation
- [ ] 👤 Espace utilisateur (mes réservations)
- [ ] 📱 Responsive design (mobile/tablet)

### 📅 Phase 4 : Fonctionnalités Avancées
- [ ] 💳 Système de paiement (simulation ou Stripe)
- [ ] 📧 Envoi d'emails de confirmation
- [ ] 🔔 Notifications et rappels de séance
- [ ] ⭐ Système de notation et avis
- [ ] 📊 Dashboard administrateur
- [ ] 📈 Statistiques (films les plus réservés, etc.)
- [ ] 🎁 Système de tarifs (réduit, enfant, senior)
- [ ] 🎫 Génération de QR codes pour les billets

### 📅 Phase 5 : Tests & Qualité
- [ ] ✅ Tests unitaires (PHPUnit)
- [ ] 🧪 Tests fonctionnels
- [ ] 🔍 Validation des formulaires
- [ ] 🐛 Correction des bugs
- [ ] ⚡ Optimisation des performances
- [ ] 🔒 Sécurité (validation OWASP)

### 📅 Phase 6 : Déploiement
- [ ] 🐳 Configuration Docker
- [ ] 🔄 CI/CD avec GitHub Actions
- [ ] 🌐 Déploiement en production
- [ ] 📚 Documentation complète
- [ ] 🎓 Préparation de la présentation UE1
```

---

📫 Contact :

Pour toute question :

Nom : Pasqua julien

-   **Email :** [julienpasqua2a@gmail.com](mailto:julienpasqua2a@gmail.com)
-   **Git :** [Github](https://github.com/julienPasqua)
-   **LinkedIn :** [linkedIn](https://www.linkedin.com/in/julien-pasqua-3a89b478/)
