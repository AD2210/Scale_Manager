$taskName = "Restart WSL on Stop"
$xmlPath = "C:\Scripts\RestartWslOnStop.xml"

# Supprimer l’ancienne tâche si elle existe
schtasks /Delete /TN "$taskName" /F 2>$null

# Créer la tâche depuis l’XML
schtasks /Create /TN "$taskName" /XML "$xmlPath" /RU SYSTEM
