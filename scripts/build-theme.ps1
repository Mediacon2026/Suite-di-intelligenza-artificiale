[CmdletBinding()]
param([string] $Php = '')

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$repositoryRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$source = Join-Path $repositoryRoot 'themes\mediacon-one'
$package = Join-Path $repositoryRoot 'build\mediacon-one-package'
$stage = Join-Path $package 'mediacon-one'
$verify = Join-Path $repositoryRoot 'build\mediacon-one-verify'
$dist = Join-Path $repositoryRoot 'dist'
$zipPath = Join-Path $dist 'mediacon-one.zip'

if (-not $Php) {
    $command = Get-Command php -ErrorAction SilentlyContinue
    if ($null -ne $command) { $Php = $command.Source }
}

foreach ($target in @($package, $verify, $zipPath)) {
    $full = [System.IO.Path]::GetFullPath($target)
    if (-not $full.StartsWith($repositoryRoot.TrimEnd('\') + '\', [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Unsafe build target: $full"
    }
    if (Test-Path -LiteralPath $full) { Remove-Item -LiteralPath $full -Recurse -Force }
}

New-Item -ItemType Directory -Path $package -Force | Out-Null
New-Item -ItemType Directory -Path $dist -Force | Out-Null
Copy-Item -LiteralPath $source -Destination $stage -Recurse

$forbidden = @(Get-ChildItem -LiteralPath $stage -Force -Recurse | Where-Object { $_.Name -in @('.git','.github','.worktrees','node_modules','tests','build','dist') -or $_.Name -match '(\.tmp|\.temp|~)$' })
if ($forbidden.Count -gt 0) { throw "Forbidden package entries: $($forbidden.FullName -join ', ')" }
if (Test-Path -LiteralPath (Join-Path $stage 'mediacon-one')) { throw 'Nested mediacon-one directory detected.' }

Get-Content -LiteralPath (Join-Path $stage 'theme.json') -Raw | ConvertFrom-Json | Out-Null
if ($Php) {
    foreach ($file in Get-ChildItem -LiteralPath $stage -Filter '*.php' -File -Recurse) {
        & $Php -l $file.FullName | Out-Null
        if ($LASTEXITCODE -ne 0) { throw "PHP lint failed: $($file.FullName)" }
    }
}

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$zipStream = [System.IO.File]::Open($zipPath, [System.IO.FileMode]::CreateNew)
$archive = New-Object System.IO.Compression.ZipArchive($zipStream, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    foreach ($file in Get-ChildItem -LiteralPath $stage -File -Recurse | Sort-Object FullName) {
        $relative = $file.FullName.Substring($package.Length).TrimStart('\').Replace('\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
            $archive,
            $file.FullName,
            $relative,
            [System.IO.Compression.CompressionLevel]::Optimal
        ) | Out-Null
    }
} finally {
    $archive.Dispose()
    $zipStream.Dispose()
}
New-Item -ItemType Directory -Path $verify -Force | Out-Null
[System.IO.Compression.ZipFile]::ExtractToDirectory($zipPath, $verify)

$roots = @(Get-ChildItem -LiteralPath $verify -Force)
if ($roots.Count -ne 1 -or -not $roots[0].PSIsContainer -or $roots[0].Name -ne 'mediacon-one') {
    throw "ZIP must contain exactly one mediacon-one root; found: $($roots.Name -join ', ')"
}
foreach ($required in @('style.css','functions.php','theme.json','index.php','screenshot.png')) {
    if (-not (Test-Path -LiteralPath (Join-Path $verify "mediacon-one\$required") -PathType Leaf)) {
        throw "Extracted ZIP is missing $required"
    }
}
if (Test-Path -LiteralPath (Join-Path $verify 'mediacon-one\mediacon-one')) { throw 'Extracted ZIP contains a duplicate theme root.' }

$archive = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
try {
    $entryCount = $archive.Entries.Count
    $entryRoots = @($archive.Entries | ForEach-Object { ($_.FullName -split '/')[0] } | Sort-Object -Unique)
    if ($entryRoots.Count -ne 1 -or $entryRoots[0] -ne 'mediacon-one') {
        throw "ZIP entries must use one portable mediacon-one root; found: $($entryRoots -join ', ')"
    }
    if (@($archive.Entries | Where-Object { $_.FullName.Contains('\') }).Count -gt 0) {
        throw 'ZIP entries contain non-portable backslash separators.'
    }
} finally {
    $archive.Dispose()
}
$item = Get-Item -LiteralPath $zipPath
$hash = (Get-FileHash -LiteralPath $zipPath -Algorithm SHA256).Hash
Write-Output 'Theme ZIP verification: PASS'
Write-Output 'Root directories: mediacon-one'
Write-Output "ZIP entries: $entryCount"
Write-Output "ZIP bytes: $($item.Length)"
Write-Output "SHA-256: $hash"
Write-Output "ZIP path: $zipPath"
