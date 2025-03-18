Cette API permet aux utilisateurs connectés d'obtenir des informations météorologiques de la ville de leur choix, sélectionnée à partir de son code postale, depuis le site http://api.openweathermap.org/geo/1.0/zip?zip=%s,FR&appid=%s

ETAPES D'INSTALLATION
Récupérer le code depuis le dépot GitHub via un terminal avec la commande :
$ clone git https://github.com/BDnartreb/EcoGarden

Se positionner dans le dossier cloné :
cd EcoGarden

Puis taper dans le terminal la commande :
$ composer install

Dans le fichier .env ou .env.local (copie locale du ficher .env) modifier les lignes suivantes pour indiquer :
le nom de la base de données
DATABASE_URL="mysql://root@127.0.0.1:3306/nomBaseDonnees?charset=utf8mb4"

Créer la base de données via le terminal:
$ symfony console doctrine:database:create

Créer les tables de la base de données à partir des entity via le terminal :
$ symfony console make:migration
$ symfony console doctrine:migrations:migrate

Remplir les tables avec des données fictives générées par les fixtures :
$ symfony console doctrine:fixtures:load

Pour accéder à l'API créer des clefs pour générer les token :
Créer un dossier config/jwt (pour accueillir les futurs fichiers  config/jwt/private.pem et  config/jwt/public.pem)
Générer les clefs :
$ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
Entrer un mot de passe et confirmer le
$ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
Redonner le mot de passe

Si JWT n'est pas déjà installer, lancer son installation :
$ composer require lexik/jwt-authentication-bundle

Copier les lignes suivantes dans .env ou .env.local et remplacer le code de JWT_PASSPHRASE= par le mot de passe :
###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=fdd719e8855fdf770a5141fd0afb817b
###< lexik/jwt-authentication-bundle ###

Lancer le server Symfony avec le commande :
symfony server:start

Accéder à la documentation de la base de données à l'adresse suivante (ou autre adresse IP données par le serveur Symfony):
http://127.0.0.1:8000/api/doc

Lancer les requêtes à partir de l'adresse /api/doc ou avec une application tel que Postman