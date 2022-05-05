### WPCeption

A Proof of Concept and Work in Progress repository for launching new WordPress instances from within WordPress

#### Uses
- Docker Engine API
- Traefik
- WordPress + MySQL images

#### Configuration
- Configure the .env file
- Configure the Premia settings page after starting the admin compose config and activating the premia plugin
- Directory path = path to where you cloned this repo

#### Starting
1. You'll find 2 docker-compose.yml in this repository, in admin and in proxy, launch them both.
2. Run composer install in admin/plugins/premia-admin