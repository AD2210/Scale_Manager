▶️ Instruction 1

Ouvre PowerShell en tant qu'administrateur.

Exécute :

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force
.\install-wsl-debian.ps1
```

▶️ Instruction 2

copier les script *.sh vers Wsl (dans home)

````bash
cp /mnt/c/Users/{Dir}/{script}.sh ~/
````

Dans Debian WSL :

```bash
nano install-docker-debian.sh
```

Colle le script ci-dessus puis :

```bash
chmod +x install-docker-debian.sh
./install-docker-debian.sh
```

▶️ Instruction 3

Ouvre le gestionnaire de service Windows (services.msc)

Définir le démarrage de WSL sur manuel

ou 

````poweshell
sc config LxssManager start= demand
````

Ouvre le Planificateur de tâches Windows (taskschd.msc)

Crée une nouvelle tâche avec ces paramètres :

Nom : WSL Auto Start

Déclencheur : Au démarrage de l’ordinateur

Action : Démarrer un programme

Programme/script : wsl

Arguments : -d Debian -e true (ou un autre nom de distrib)

Conditions :

Décoche "Démarrer uniquement si l'ordinateur est sur secteur"

Paramètres :

Coche "Exécuter avec les autorisations maximales"

Coche "Exécuter la tâche dès que possible après un démarrage"

Cela forcera l’exécution silencieuse d’un wsl dès le démarrage, ce qui initialisera le noyau et le réseau WSL2.

Donner le propriété du dossier au user actuel (permet d'executer les commande sans sudo)

````bash
sudo chown -R $USER:$(id -gn) {mondossier}
````

Sur le poste serveur ouvrir les port WSL puis autorise le firewall, ajouter une regle pour les postes distants également
```powershell
netsh interface portproxy add v4tov4 listenaddress=0.0.0.0 listenport=80 connectaddress=172.31.251.39 connectport=80
netsh interface portproxy add v4tov4 listenaddress=0.0.0.0 listenport=443 connectaddress=172.31.251.39 connectport=443

New-NetFirewallRule -DisplayName "WSL2 Port Forwarding" -Direction Inbound -LocalPort 80,443 -Action Allow -Protocol TCP

```


Run `docker compose build --pull --no-cache` to build fresh images
Run `docker compose up --wait` to set up and start a fresh Symfony project
Open http://localhost in your favorite web browser
Run `docker compose down --remove-orphans` to stop the Docker containers.

si buildx ne fonctionne pas correctement :

Étape	Commande / Action
Supprimer les liens invalides	`sudo rm -rf /usr/local/lib/docker/cli-plugins`
Installer buildx plugin	`sudo apt install docker-buildx-plugin`
Installer compose plugin	`sudo apt install docker-compose-plugin`
Vérifier les plugins	`docker info`

pour executer des commandes dans le container :
`docker compose exec php bash`

au démarrage du container :

```docker php bash
docker compose exec bin/console doctrine:schema:update --force
npm install
npm run dev
```

