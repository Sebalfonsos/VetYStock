$ErrorActionPreference = 'Stop'

$runtimeDir = Join-Path $PSScriptRoot '.runtime'
$pidFile = Join-Path $runtimeDir 'microservices.json'

if (-not (Test-Path $pidFile)) {
    Write-Host 'No hay procesos registrados para detener.'
    exit 0
}

$tracked = Get-Content $pidFile -Raw | ConvertFrom-Json
foreach ($entry in @($tracked)) {
    try {
        Stop-Process -Id $entry.pid -Force -ErrorAction Stop
        Write-Host "Stopped $($entry.name) (PID $($entry.pid))"
    } catch {
        Write-Host "No se pudo detener $($entry.name) (PID $($entry.pid))."
    }
}

Remove-Item $pidFile -Force -ErrorAction SilentlyContinue
