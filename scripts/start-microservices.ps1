$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$php = 'php'
$runtimeDir = Join-Path $PSScriptRoot '.runtime'
$pidFile = Join-Path $runtimeDir 'microservices.json'

function Stop-TrackedProcesses {
    param([string]$Path)

    if (-not (Test-Path $Path)) {
        return
    }

    $tracked = Get-Content $Path -Raw | ConvertFrom-Json
    foreach ($entry in @($tracked)) {
        try {
            Stop-Process -Id $entry.pid -Force -ErrorAction Stop
            Write-Host "Stopped $($entry.name) (PID $($entry.pid))"
        } catch {
        }
    }

    Remove-Item $Path -Force -ErrorAction SilentlyContinue
}

New-Item -ItemType Directory -Path $runtimeDir -Force | Out-Null
Stop-TrackedProcesses -Path $pidFile

$services = @(
    @{ Name = 'main'; Port = 8000; Path = Join-Path $root 'public' },
    @{ Name = 'auth'; Port = 8101; Path = Join-Path $root 'services\auth\public' },
    @{ Name = 'catalog'; Port = 8102; Path = Join-Path $root 'services\catalog\public' },
    @{ Name = 'inventory'; Port = 8103; Path = Join-Path $root 'services\inventory\public' },
    @{ Name = 'animals'; Port = 8104; Path = Join-Path $root 'services\animals\public' }
)

$started = @()
foreach ($service in $services) {
    $process = Start-Process -WindowStyle Hidden -PassThru -FilePath $php -ArgumentList @(
        '-S',
        "localhost:$($service.Port)",
        '-t',
        $service.Path
    ) -WorkingDirectory $root
    $started += [pscustomobject]@{
        name = $service.Name
        port = $service.Port
        pid = $process.Id
    }
    Write-Host "Started $($service.Name) on http://localhost:$($service.Port)"
}

$started | ConvertTo-Json | Set-Content -Path $pidFile -Encoding UTF8
