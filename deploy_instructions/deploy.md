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
