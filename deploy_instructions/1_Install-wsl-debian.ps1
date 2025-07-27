# Script PowerShell pour activer WSL2 et installer Debian (Windows 10 ou 11)
# À exécuter en tant qu'administrateur

# 1. Vérification des privilèges administrateur
If (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Warning "Ce script doit être exécuté en tant qu'administrateur !"
    exit 1
}

Write-Host "`n✅ Vérification de la version de Windows..." -ForegroundColor Cyan
$winVersion = (Get-CimInstance Win32_OperatingSystem).Version
Write-Host "Version de Windows détectée : $winVersion"

# 2. Activer les fonctionnalités requises
Write-Host "`n🔧 Activation des fonctionnalités WSL et VM Platform..." -ForegroundColor Cyan
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart

# 3. Télécharger et installer le noyau WSL 2 si nécessaire
$wslUpdateUrl = "https://wslstorestorage.blob.core.windows.net/wslblob/wsl_update_x64.msi"
$installerPath = "$env:TEMP\wsl_update_x64.msi"

Write-Host "`n⬇️ Téléchargement du noyau WSL 2..." -ForegroundColor Cyan
Invoke-WebRequest -Uri $wslUpdateUrl -OutFile $installerPath

Write-Host "📦 Installation du noyau WSL 2..."
Start-Process msiexec.exe -ArgumentList "/i $installerPath /quiet /norestart" -Wait

# 4. Définir WSL2 par défaut
Write-Host "`n⚙️ Configuration de WSL2 par défaut..." -ForegroundColor Cyan
wsl --set-default-version 2

# 5. Installation de Debian
Write-Host "`n📥 Installation de Debian depuis le Microsoft Store..." -ForegroundColor Cyan
wsl --install -d Debian

Write-Host "`n🎉 Terminé ! Redémarre ta machine puis ouvre Debian depuis le menu démarrer pour finaliser l'installation." -ForegroundColor Green
