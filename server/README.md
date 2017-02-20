Serveru VPS

PARAMETRES SERVEUR :
* L'adresse IP du serveur est : 51.255.39.42
* Le nom du serveur est : vps224554.ovh.net

DEPLOY FRONT :
* Se connecter en ssh
* `cd /var/www/mydoctool-front/`
* `ssh-agent bash -c 'ssh-add /root/.ssh/id_rsa_front; git pull'`
* webpack