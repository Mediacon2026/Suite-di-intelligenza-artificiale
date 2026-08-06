[CmdletBinding()]
param(
    [string] $Php = '',
    [string] $Composer = ''
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$repositoryRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$sourceRoot = Join-Path $repositoryRoot 'plugin\mediacon-enterprise'
$buildRoot = Join-Path $repositoryRoot 'build\mediacon-enterprise-package'
$verifyRoot = Join-Path $repositoryRoot 'build\mediacon-enterprise-verify'
$stageRoot = Join-Path $buildRoot 'mediacon-enterprise'
$distRoot = Join-Path $repositoryRoot 'dist'
$zipPath = Join-Path $distRoot 'mediacon-enterprise.zip'

function Assert-RepositoryChild {
    param([Parameter(Mandatory)][string] $Path)

    $fullPath = [System.IO.Path]::GetFullPath($Path)
    $prefix = $repositoryRoot.TrimEnd('\') + '\'
    if (-not $fullPath.StartsWith($prefix, [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Refusing to modify a path outside the repository: $fullPath"
    }
}

function Resolve-Toolchain {
    if (-not $Php) {
        $localPhp = Join-Path $repositoryRoot 'build\tools\php\php.exe'
        if (Test-Path -LiteralPath $localPhp -PathType Leaf) {
            $script:Php = $localPhp
        } else {
            $phpCommand = Get-Command php -ErrorAction SilentlyContinue
            if ($null -eq $phpCommand) {
                throw 'PHP was not found. Pass -Php or place the portable runtime in build/tools/php/php.exe.'
            }
            $script:Php = $phpCommand.Source
        }
    }

    if (-not $Composer) {
        $localComposer = Join-Path $repositoryRoot 'build\tools\composer.phar'
        if (Test-Path -LiteralPath $localComposer -PathType Leaf) {
            $script:Composer = $localComposer
        } else {
            $composerCommand = Get-Command composer -ErrorAction SilentlyContinue
            if ($null -eq $composerCommand) {
                throw 'Composer was not found. Pass -Composer or place composer.phar in build/tools/composer.phar.'
            }
            $script:Composer = $composerCommand.Source
        }
    }

    $script:Php = [System.IO.Path]::GetFullPath($Php)
    if ($Composer -match '\.phar$') {
        $script:Composer = [System.IO.Path]::GetFullPath($Composer)
    }
}

function Invoke-Php {
    param([Parameter(ValueFromRemainingArguments)][string[]] $Arguments)

    & $Php @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "PHP command failed with exit code $LASTEXITCODE."
    }
}

function Invoke-Composer {
    param([Parameter(ValueFromRemainingArguments)][string[]] $Arguments)

    if ($Composer -match '\.phar$') {
        & $Php $Composer @Arguments
    } else {
        & $Composer @Arguments
    }
    if ($LASTEXITCODE -ne 0) {
        throw "Composer command failed with exit code $LASTEXITCODE."
    }
}

Resolve-Toolchain

$mainFile = Join-Path $sourceRoot 'mediacon-enterprise.php'
$nestedSource = Join-Path $sourceRoot 'mediacon-enterprise\mediacon-enterprise.php'
if (-not (Test-Path -LiteralPath $mainFile -PathType Leaf)) {
    throw "The plugin entry point is missing: $mainFile"
}
if (Test-Path -LiteralPath $nestedSource) {
    throw "A nested plugin directory already exists in the source: $nestedSource"
}

$requiredHeaders = @('Plugin Name', 'Description', 'Version', 'Author', 'Text Domain', 'Requires PHP', 'Requires at least')
$headerText = Get-Content -LiteralPath $mainFile -Raw
foreach ($header in $requiredHeaders) {
    if ($headerText -notmatch "(?mi)^\s*\*\s*$([regex]::Escape($header))\s*:\s*\S.+$") {
        throw "Required WordPress plugin header is missing or empty: $header"
    }
}

foreach ($target in @($buildRoot, $verifyRoot, $zipPath)) {
    Assert-RepositoryChild -Path $target
    if (Test-Path -LiteralPath $target) {
        Remove-Item -LiteralPath $target -Recurse -Force
    }
}
New-Item -ItemType Directory -Path $stageRoot -Force | Out-Null
New-Item -ItemType Directory -Path $distRoot -Force | Out-Null

$productionFiles = @(
    'mediacon-enterprise.php',
    'uninstall.php',
    'composer.json',
    'composer.lock',
    'readme.md'
)
$productionDirectories = @(
    'assets',
    'docs',
    'languages',
    'src',
    'templates'
)

foreach ($relativePath in $productionFiles) {
    $source = Join-Path $sourceRoot $relativePath
    if (-not (Test-Path -LiteralPath $source -PathType Leaf)) {
        throw "Required production file is missing: $relativePath"
    }
    Copy-Item -LiteralPath $source -Destination (Join-Path $stageRoot $relativePath)
}
foreach ($relativePath in $productionDirectories) {
    $source = Join-Path $sourceRoot $relativePath
    if (-not (Test-Path -LiteralPath $source -PathType Container)) {
        throw "Required production directory is missing: $relativePath"
    }
    Copy-Item -LiteralPath $source -Destination (Join-Path $stageRoot $relativePath) -Recurse
}

Push-Location $stageRoot
try {
    Invoke-Composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
    Invoke-Composer dump-autoload --no-dev --optimize --strict-psr
} finally {
    Pop-Location
}

$vendorBin = Join-Path $stageRoot 'vendor\bin'
if (Test-Path -LiteralPath $vendorBin) {
    Remove-Item -LiteralPath $vendorBin -Recurse -Force
}

$forbiddenNames = @('tests', 'phpunit.xml', 'phpcs.xml', '.git', '.github', '.worktrees', 'build', 'dist')
foreach ($forbiddenName in $forbiddenNames) {
    if (Test-Path -LiteralPath (Join-Path $stageRoot $forbiddenName)) {
        throw "Forbidden package entry was copied: $forbiddenName"
    }
}
$temporaryFiles = @(
    Get-ChildItem -LiteralPath $stageRoot -File -Recurse -Force |
        Where-Object { $_.Name -in @('.DS_Store', 'Thumbs.db') -or $_.Name -match '(\.tmp|\.temp|~)$' }
)
if ($temporaryFiles.Count -gt 0) {
    throw "Temporary files were copied into the package: $($temporaryFiles.FullName -join ', ')"
}
if (Test-Path -LiteralPath (Join-Path $stageRoot 'mediacon-enterprise')) {
    throw 'The staging directory contains a second mediacon-enterprise directory.'
}
$nestedArchives = @(Get-ChildItem -LiteralPath $stageRoot -Filter '*.zip' -File -Recurse)
if ($nestedArchives.Count -gt 0) {
    throw "A secondary archive was copied into the plugin: $($nestedArchives.FullName -join ', ')"
}
$secondaryPluginFiles = @(
    Get-ChildItem -LiteralPath $stageRoot -Filter '*.php' -File -Recurse |
        Where-Object { $_.FullName -ne (Join-Path $stageRoot 'mediacon-enterprise.php') } |
        Where-Object { (Get-Content -LiteralPath $_.FullName -TotalCount 50) -match '^\s*\*\s*Plugin Name\s*:' }
)
if ($secondaryPluginFiles.Count -gt 0) {
    throw "A secondary WordPress plugin entry point was copied: $($secondaryPluginFiles.FullName -join ', ')"
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory(
    $buildRoot,
    $zipPath,
    [System.IO.Compression.CompressionLevel]::Optimal,
    $false
)
$distArchives = @(Get-ChildItem -LiteralPath $distRoot -Filter '*.zip' -File)
if ($distArchives.Count -ne 1 -or $distArchives[0].Name -ne 'mediacon-enterprise.zip') {
    throw "Distribution must contain only mediacon-enterprise.zip; found: $($distArchives.Name -join ', ')"
}

New-Item -ItemType Directory -Path $verifyRoot -Force | Out-Null
[System.IO.Compression.ZipFile]::ExtractToDirectory($zipPath, $verifyRoot)

$extractedMain = Join-Path $verifyRoot 'mediacon-enterprise\mediacon-enterprise.php'
$extractedNestedMain = Join-Path $verifyRoot 'mediacon-enterprise\mediacon-enterprise\mediacon-enterprise.php'
$rootEntries = @(Get-ChildItem -LiteralPath $verifyRoot -Force)
if ($rootEntries.Count -ne 1 -or -not $rootEntries[0].PSIsContainer -or $rootEntries[0].Name -ne 'mediacon-enterprise') {
    $found = ($rootEntries.Name -join ', ')
    throw "ZIP must contain exactly one root directory named mediacon-enterprise; found: $found"
}
if (-not (Test-Path -LiteralPath $extractedMain -PathType Leaf)) {
    throw 'ZIP is missing mediacon-enterprise/mediacon-enterprise.php.'
}
if (Test-Path -LiteralPath $extractedNestedMain) {
    throw 'ZIP contains the forbidden nested plugin path.'
}

$pluginBasename = $extractedMain.Substring($verifyRoot.TrimEnd('\').Length + 1).Replace('\', '/')
if ($pluginBasename -ne 'mediacon-enterprise/mediacon-enterprise.php') {
    throw "Unexpected plugin_basename path: $pluginBasename"
}

$extractedHeader = Get-Content -LiteralPath $extractedMain -Raw
foreach ($header in $requiredHeaders) {
    if ($extractedHeader -notmatch "(?mi)^\s*\*\s*$([regex]::Escape($header))\s*:\s*\S.+$") {
        throw "Extracted plugin header is missing or empty: $header"
    }
}

$phpFiles = @(Get-ChildItem -LiteralPath (Join-Path $verifyRoot 'mediacon-enterprise') -Filter '*.php' -File -Recurse)
foreach ($phpFile in $phpFiles) {
    Invoke-Php -l $phpFile.FullName | Out-Null
}

$autoloadPath = Join-Path $verifyRoot 'mediacon-enterprise\vendor\autoload.php'
if (-not (Test-Path -LiteralPath $autoloadPath -PathType Leaf)) {
    throw 'Extracted Composer autoload.php is missing.'
}
$autoloadForPhp = $autoloadPath.Replace('\', '/').Replace("'", "\\'")
Invoke-Php -r "require '$autoloadForPhp'; exit(class_exists('Mediacon\\Enterprise\\Core\\Plugin') ? 0 : 1);"

$activationSmoke = Join-Path $sourceRoot 'tests\activation-smoke.php'
Invoke-Php $activationSmoke $extractedMain

$archive = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
try {
    $entryCount = $archive.Entries.Count
    $archiveRoots = @(
        $archive.Entries |
            ForEach-Object { ($_.FullName -replace '\\', '/').Split('/')[0] } |
            Where-Object { $_ } |
            Sort-Object -Unique
    )
    if ($archiveRoots.Count -ne 1 -or $archiveRoots[0] -ne 'mediacon-enterprise') {
        throw "Archive entry scan found invalid roots: $($archiveRoots -join ', ')"
    }
    $entryNames = @($archive.Entries | ForEach-Object { $_.FullName -replace '\\', '/' })
    if ($entryNames -contains 'mediacon-enterprise/mediacon-enterprise/mediacon-enterprise.php') {
        throw 'Archive entry scan found the forbidden nested plugin path.'
    }
} finally {
    $archive.Dispose()
}

$zipItem = Get-Item -LiteralPath $zipPath
$sha256 = (Get-FileHash -LiteralPath $zipPath -Algorithm SHA256).Hash

Write-Host 'ZIP verification passed.'
Write-Host "Root directories: $($archiveRoots -join ', ')"
Write-Host 'mediacon-enterprise/mediacon-enterprise.php: present'
Write-Host 'mediacon-enterprise/mediacon-enterprise/mediacon-enterprise.php: absent'
Write-Host "plugin_basename: $pluginBasename"
Write-Host "PHP files linted: $($phpFiles.Count)"
Write-Host 'Extracted autoload: valid'
Write-Host 'WordPress plugin header: valid'
Write-Host 'Activation smoke: valid'
Write-Host 'Shortcode mediacon_calcolatore: registered'
Write-Host "ZIP entries: $entryCount"
Write-Host "ZIP bytes: $($zipItem.Length)"
Write-Host "SHA-256: $sha256"
Write-Host "ZIP path: $zipPath"
