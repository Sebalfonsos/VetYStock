[CmdletBinding()]
param(
    [string[]]$Services = @('all'),
    [switch]$Force
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$php = 'php'
$runtimeDir = Join-Path $PSScriptRoot '.runtime'
$pidFile = Join-Path $runtimeDir 'microservices.json'

function Get-ServiceMap {
    return @{
        main = @{ Name = 'main'; Port = 8000; Path = Join-Path $root 'public' }
        auth = @{ Name = 'auth'; Port = 8101; Path = Join-Path $root 'services\auth\public' }
        catalog = @{ Name = 'catalog'; Port = 8102; Path = Join-Path $root 'services\catalog\public' }
        inventory = @{ Name = 'inventory'; Port = 8103; Path = Join-Path $root 'services\inventory\public' }
        animals = @{ Name = 'animals'; Port = 8104; Path = Join-Path $root 'services\animals\public' }
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
$running = @()

foreach ($service in $selectedServices) {
    $existing = $registry | Where-Object { $_.name -eq $service.Name }
    if ($existing) {
        $stillRunning = $true
        try {
            Get-Process -Id [int]$existing.pid -ErrorAction Stop | Out-Null
        } catch {
            $stillRunning = $false
        }

        if ($stillRunning) {
            if ($Force) {
                Stop-Process -Id [int]$existing.pid -Force -ErrorAction SilentlyContinue
                $registry = @($registry | Where-Object { $_.name -ne $service.Name })
            } else {
                Write-Host "Skipping $($service.Name): ya está levantado (PID $($existing.pid))."
                continue
            }
        }

        $registry = @($registry | Where-Object { $_.name -ne $service.Name })
    } elseif (Get-ListeningProcess -Port $service.Port) {
        Write-Host "Skipping $($service.Name): el puerto $($service.Port) ya está en uso."
        continue
    }

    $process = Start-Process -WindowStyle Hidden -PassThru -FilePath $php -ArgumentList @(
        '-S',
        "localhost:$($service.Port)",
        '-t',
        $service.Path
    ) -WorkingDirectory $root

    $running += [pscustomobject]@{
        name = $service.Name
        port = $service.Port
        pid = $process.Id
    }

    Write-Host "Started $($service.Name) on http://localhost:$($service.Port)"
}

if ($running.Count -gt 0 -or $registry.Count -gt 0) {
    Save-Registry -Entries @($registry + $running)
}
