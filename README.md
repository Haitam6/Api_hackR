# Api_hackR

Bonjour, voici le readme de mon projet API HackR réalisé en laravel.

# Etapes à suivre pour tester les fonctionnalités :

1. Rendez-vous sur ce lien : https://haitam.elqassimi.angers.mds-project.fr/api/documentation
2. Créez un compte en utilisant la fonctionnalité Register qui se situe dans le tag Authentification.

   Si vous souhaitez avoir un compte admin connectez-vous directement avec les identifiants suivants dans la fonctionnalités login : 
    - email :Haitam_elqassimi10@outlook.fr
    - password : haitam
3. En créant un compte cela génère un token, copiez ce token. 
Dans le bouton authorize en haut à droite, collez le token dans la case value et cliquez sur authorize.
4. Vous pouvez maintenant tester les fonctionnalités que vous souhaitez.
5. Si vous vous connectez avec un compte admin, vous pouvez changer les droits. Pour cela, allez dans le tag Roles Management et vous allez pouvoir donner le droit a l'id de la fonctionnalité que vous voulez tester (se référer au tableau ci-dessus pour connaitre l'id), le rôle 1 vous permet d'avoir le droit et le rôle 2 vous le retire.
6. Enfin, il existe aussi un système de logs, pour les consulter, allez dans le tag Logs. Vous allez pouvoir voir les derniers logs en renseignant ou non le nombre de logs que vous souhaitez voir, voir les logs d'un utilisateur en question en renseignant son email, ou encore voir les logs d'une fonctionnalité en renseignant son nom.

A noter que seul le compte admin peut voir les logs et donner les droits.

Voici les fonctionnalités de mon projet, ainsi que leurs id qui peux être utile pour les tester: 

| **ID** | **Fonctionnalité**        | **Description**                                         |
|--------|---------------------------|---------------------------------------------------------|
| 1      | Register                  | Permet de créer un compte                               |
| 2      | Login                     | Permet de se connecter                                  |
| 4      | Email verification        | Vérification par email                                  |
| 5      | Email spam                | Spammer d'email                                         |
| 6      | Password generator        | Génération de mot de passe                              |
| 7      | Get subdomains            | Récupérer tous domaines & sous-domaines associés à...   |
| 8      | Password check            | Vérifie le mot de passe                                 |
| 9      | Fake identity             | Génération de fausse identité                           |
| 10     | Ddos                       | Fonctionnalité de DDoS                                  |
| 11     | Random image              | Génération d'image random                               |
| 12     | Crawler information       | Crawler d'informations                                  |
| 13     | Phishing                  | Phishing                                                 |
| 14     | Get users profile         | Permet d'avoir le profil de l'utilisateur               |

# Pour tester sur postman 

Récupérer le fichier /Hackr_laravel.postman_collection.json et l'importer dans postman. Vous allez retrouver toutes les requêtes pour tester les fonctionnalités. 

# Pour importer le projet en local

Prérequis :
- PHP 8.2
- Composer
- Laravel

1. Cloner le projet : `git clone https://github.com/Haitam6/Api_hackR.git`
`cd Api_hackR`
2. Installer les dépendances : `composer install`
3. Copier le fichier .env.example dans le .env : `cp .env.example .env`
4. Générer la clé : `php artisan key:generate`
5. Créer une base de données nommée `hackr_`
6. Lancer les migrations : `php artisan migrate:fresh --seed`
7. Lancer le serveur : `php artisan serve`
8. Ajouter /api/documentation à l'url pour accéder à la documentation de l'api

Pour tester les fonctionnalité en tant qu'admin j'ai créé un compte admin avec les identifiants suivants : Email = Admin@test.com
Mot de passe = Password123@


# Auteurs

EL QASSIMI Haitam


 