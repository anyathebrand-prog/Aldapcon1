# ALDAPCON Platform — enable the Windows features WSL2 needs
#
# Run this ONCE, as Administrator, AFTER enabling Intel VT-x in the BIOS.
# Right-click the file and choose "Run with PowerShell" as administrator, or:
#
#   Start → type "PowerShell" → right-click → Run as administrator
#   cd C:\Users\HomePC\Aldapcon\Aldapcon1
#   Set-ExecutionPolicy -Scope Process Bypass -Force
#   .\bin\enable-wsl.ps1
#
# It changes nothing about how Windows boots. It enables two optional
# components and installs a Linux distribution. A reboot is required in
# between, and the script tells you when.

#Requires -RunAsAdministrator

$ErrorActionPreference = 'Stop'

function Say($m) { Write-Host "`n==> $m" -ForegroundColor Green }
function Warn($m) { Write-Host "    ! $m" -ForegroundColor Yellow }

Say "Checking hardware virtualization"

$cpu = Get-CimInstance Win32_Processor | Select-Object -First 1
if (-not $cpu.VirtualizationFirmwareEnabled) {
    Write-Host ""
    Write-Host "  Virtualization is still DISABLED in firmware." -ForegroundColor Red
    Write-Host ""
    Write-Host "  Enable it first:"
    Write-Host "    1. Shut down fully (not Restart)."
    Write-Host "    2. Press the Novo button (pinhole, left edge) to power on."
    Write-Host "    3. BIOS Setup -> Configuration -> Intel Virtual Technology -> Enabled."
    Write-Host "    4. F10 to save and exit, then shut down fully once more."
    Write-Host ""
    Write-Host "  Nothing below will work until that reads True."
    exit 1
}

Write-Host "    Intel VT-x is enabled in firmware. Good."

# ---------------------------------------------------------------------------
# Optional components
# ---------------------------------------------------------------------------
Say "Enabling Virtual Machine Platform and Windows Subsystem for Linux"

$needsReboot = $false

foreach ($feature in @('VirtualMachinePlatform', 'Microsoft-Windows-Subsystem-Linux')) {
    $state = (Get-WindowsOptionalFeature -Online -FeatureName $feature).State
    if ($state -eq 'Enabled') {
        Write-Host "    $feature already enabled"
    } else {
        Write-Host "    Enabling $feature ..."
        $result = Enable-WindowsOptionalFeature -Online -FeatureName $feature -All -NoRestart
        if ($result.RestartNeeded) { $needsReboot = $true }
    }
}

if ($needsReboot) {
    Write-Host ""
    Write-Host "  A reboot is required before continuing." -ForegroundColor Yellow
    Write-Host "  Reboot now, then run this script again to install Ubuntu."
    Write-Host ""
    $answer = Read-Host "  Reboot now? (y/N)"
    if ($answer -eq 'y') { Restart-Computer -Force }
    exit 0
}

# ---------------------------------------------------------------------------
# Distribution
# ---------------------------------------------------------------------------
Say "Installing Ubuntu 24.04 LTS"

# Ubuntu 24.04 ships the whole TRD §14 stack from its own repositories --
# PHP 8.3, PostgreSQL 16, Redis 7, nginx -- with no PPAs and no pinning. It is
# also the production target (TRD §1.1).
$installed = (wsl.exe --list --quiet) -replace "`0", ""
if ($installed -match 'Ubuntu-24\.04') {
    Write-Host "    Ubuntu-24.04 already installed"
} else {
    wsl.exe --install -d Ubuntu-24.04
}

Say "Done"

Write-Host @"

  Next, open Ubuntu (Start -> Ubuntu 24.04) and create your Linux user when
  prompted. Then, inside Ubuntu:

      git clone <repository-url> ~/aldapcon
      cd ~/aldapcon
      ./bin/setup-native.sh

  Clone into ~ , not /mnt/c/... . Cross-filesystem I/O makes Composer and
  Vite several times slower.

  Enabling virtualization should also let Docker Desktop start, if you would
  rather use ./bin/setup.sh instead.

"@
