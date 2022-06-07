<#
  Powershell script for Win10 dev tools installation
  Run script as Administrator
    - Installs all packages listed in packages.json
    - Enables Microsoft-Hyper-V
#>

$ChocoInstalled = $false
$packages = Get-Content -Raw -Path .\packages.json | ConvertFrom-Json

if (Get-Command choco.exe -ErrorAction SilentlyContinue) {
    $ChocoInstalled = $true
} else {
  Set-ExecutionPolicy Bypass -Scope Process -Force; iex ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))
  $ChocoInstalled = $true
}

if($ChocoInstalled -Eq $true) {
  foreach ($package in $packages.packages) {
    if ($package.version -Ne 'latest') {
      choco install -y $package.name --version $package.version
    } else {
      choco install -y $package.name
    }
  }
}

Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Hyper-V -All
