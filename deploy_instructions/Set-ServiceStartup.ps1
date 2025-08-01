param(
    [string]$ServiceName = "wslservice",
    [ValidateSet("Automatic", "Manual", "Disabled")]
    [string]$StartupType = "Automatic"
)

# Vérifie si le service existe
$service = Get-Service -Name $ServiceName -ErrorAction SilentlyContinue

if ($null -eq $service) {
    Write-Error "Le service '$ServiceName' n'existe pas."
    exit 1
}

# Modifie le type de démarrage
Set-Service -Name $ServiceName -StartupType $StartupType

# Démarre le service si ce n’est pas déjà fait
if ($service.Status -ne "Running") {
    Start-Service -Name $ServiceName
    Write-Output "Service '$ServiceName' démarré."
} else {
    Write-Output "Service '$ServiceName' déjà en cours d’exécution."
}
