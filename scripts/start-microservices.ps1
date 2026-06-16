$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$php = 'php'

$services = @(
    @{ Name = 'auth'; Port = 8101; Path = Join-Path $root 'services\auth\public' },
    @{ Name = 'catalog'; Port = 8102; Path = Join-Path $root 'services\catalog\public' },
    @{ Name = 'inventory'; Port = 8103; Path = Join-Path $root 'services\inventory\public' },
    @{ Name = 'animals'; Port = 8104; Path = Join-Path $root 'services\animals\public' }
)

foreach ($service in $services) {
    Start-Process -WindowStyle Hidden -FilePath $php -ArgumentList @(
        '-S',
        "localhost:$($service.Port)",
        '-t',
        $service.Path
    ) -WorkingDirectory $root | Out-Null
    Write-Host "Started $($service.Name) on http://localhost:$($service.Port)"
}
