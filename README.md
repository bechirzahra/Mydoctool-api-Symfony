# mydoctool-api

Ce repository contient l'ensemble du code source de l'API.
Technologies utilisées :
- Symfony 2.7
- Doctrine 2.4.*
- JMS Serializer
- JWT Authentication Bundle


### Installation

Sur son ordinateur, dans le dossier du repo déjà cloné.

```
    $> curl -sS https://getcomposer.org/installer | php
    $> php composer.phar self-update
    $> php composer.phar install
    $> php app/console doctrine:database:create
    $> php app/console d:s:u --force
    $> mkdir -p app/var/jwt
    $> openssl genrsa -out app/var/jwt/private.pem -aes256 4096
    $> openssl rsa -pubout -in app/var/jwt/private.pem -out app/var/jwt/public.pem
```

Puis, pour lancer le serveur `php app/console s:r`


### Serveur pré-prod:
- Adresse : `51.255.39.42`
- User : `root`
- Clé d'identification : `mydoctool-api/server/mydoctool_rsa`

### How to deploy:

1. Installer `capifony` : http://capifony.org/
2. Une fois installé, se placer dans le dossier `mydoctool-api`
3. Lancer `$> cap prod deploy`

### Mettre à jour la base de données

En cas de modification de la base de données (changement des Entités), voici la procédure pour mettre à jour la base.

Se connecter au serveur `ssh -i server/mydoctool_rsa root@51.255.39.42`

```
    $> cd /var/www/mydoctool.com/current
    $> php app/console d:s:u --force --env=prod
    $> php app/console c:c --env=prod
    $> exit
```

### Relancer le serveur PHP

Si l'API devient temporairement indisponible, une des solutions peut être de relancer PHP

Se connecter au serveur `ssh -i server/mydoctool_rsa root@51.255.39.42`

```
    $> service php5-fpm restart
    $> exit
```