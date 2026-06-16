[CmdletBinding()]
param(
    [string[]]$Services = @('all')
)

$ErrorActionPreference = 'Stop'

$runtimeDir = Join-Path $PSScriptRoot '.runtime'
$pidFile = Join-Path $runtimeDir 'microservices.json'
$root = Split-Path -Parent $PSScriptRoot

function Get-ServiceMap {
    return @{
        main = @{ Name = 'main'; Port = 8000 }
        auth = @{ Name = 'auth'; Port = 8101 }
        catalog = @{ Name = 'catalog'; Port = 8102 }
        inventory = @{ Name = 'inventory'; Port = 8103 }
        animals = @{ Name = 'animals'; Port = 8104 }
    }
}

function Get-RequestedServices {
    param([string[]]$Names)

    $serviceMap = Get-ServiceMap
    if ($Names.Count -eq 0 -or ($Names.Count -eq 1 -and $Names[0] -eq 'all')) {
        return @($serviceMap.Values)
    }

    $selected = @()
    foreach ($name in $Names) {
        $key = $name.ToLowerInvariant()
        if (-not $serviceMap.ContainsKey($key)) {
            throw "Servicio desconocido: $name"
        }
        $selected += $serviceMap[$key]
    }

    return $selected
}

function Read-Registry {
    if (-not (Test-Path $pidFile)) {
        return @()
    }

    $content = Get-Content $pidFile -Raw
    if ([string]::IsNullOrWhiteSpace($content)) {
        return @()
    }

    $decoded = $content | ConvertFrom-Json
    if ($decoded -is [System.Array]) {
        return @($decoded)
    }

    return @($decoded)
}

function Save-Registry {
    param([array]$Entries)

    if ($Entries.Count -eq 0) {
        Remove-Item $pidFile -Force -ErrorAction SilentlyContinue
        return
    }

    New-Item -ItemType Directory -Path $runtimeDir -Force | Out-Null
    $Entries | ConvertTo-Json -Depth 5 | Set-Content -Path $pidFile -Encoding UTF8
}

function Get-ListeningProcess {
    param([int]$Port)

    $connection = Get-NetTCPConnection -State Listen -ErrorAction SilentlyContinue | Where-Object { $_.LocalPort -eq $Port } | Select-Object -First 1
    if (-not $connection) {
        return $null
    }

    return [int]$connection.OwningProcess
}

$selectedServices = Get-RequestedServices -Names $Services
$registry = Read-Registry
$remaining = @()
$handledByName = @()

foreach ($entry in $registry) {
    $isSelected = $selectedServices | Where-Object { $_.Name -eq $entry.name }
    if (-not $isSelected) {
        $remaining += $entry
        continue
    }

    $handledByName += $entry.name
    try {
        Stop-Process -Id [int]$entry.pid -Force -ErrorAction Stop
        Write-Host "Stopped $($entry.name) (PID $($entry.pid))"
    } catch {
        Write-Host "No se pudo detener $($entry.name) (PID $($entry.pid))."
    }
}

foreach ($service in $selectedServices) {
    if ($handledByName -contains $service.Name) {
        continue
    }

    $procId = Get-ListeningProcess -Port $service.Port
    if ($null -eq $procId) {
        continue
    }

    try {
        Stop-Process -Id $procId -Force -ErrorAction Stop
        Write-Host "Stopped $($service.Name) by port $($service.Port) (PID $procId)"
    } catch {
        Write-Host "No se pudo detener $($service.Name) por el puerto $($service.Port)."
    }
}

Save-Registry -Entries $remaining
