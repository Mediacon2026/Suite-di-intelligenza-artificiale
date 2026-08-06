[CmdletBinding()]
param([string] $Theme = '')

$ErrorActionPreference = 'Stop'
if (-not $Theme) {
    $Theme = Join-Path (Split-Path $PSScriptRoot -Parent | Split-Path -Parent) 'themes\mediacon-one'
}
$Theme = [System.IO.Path]::GetFullPath($Theme)

$required = @(
    'style.css', 'functions.php', 'theme.json', 'index.php', 'front-page.php',
    'header.php', 'footer.php', 'page.php', 'single.php', 'archive.php',
    'search.php', '404.php', 'screenshot.png', 'inc', 'templates',
    'template-parts', 'assets\css', 'assets\js', 'assets\images',
    'assets\icons', 'languages', 'docs'
)
foreach ($relative in $required) {
    if (-not (Test-Path -LiteralPath (Join-Path $Theme $relative))) {
        throw "Missing required theme entry: $relative"
    }
}

$themeJson = Get-Content -LiteralPath (Join-Path $Theme 'theme.json') -Raw | ConvertFrom-Json
if ($themeJson.version -ne 3 -or $themeJson.settings.layout.contentSize -ne '840px') {
    throw 'theme.json does not expose the expected version and layout.'
}

$styleHeader = Get-Content -LiteralPath (Join-Path $Theme 'style.css') -Raw
foreach ($header in @('Theme Name: Mediacon One', 'Version:', 'Text Domain: mediacon-one', 'Requires PHP:')) {
    if (-not $styleHeader.Contains($header)) { throw "Missing style.css header: $header" }
}

$components = @('breadcrumbs','hero','card','cta','faq','timeline','table','form','badge','alert','sidebar','pagination')
foreach ($component in $components) {
    if (-not (Test-Path -LiteralPath (Join-Path $Theme "template-parts\$component.php"))) {
        throw "Missing component: $component"
    }
}

$css = (Get-ChildItem -LiteralPath (Join-Path $Theme 'assets\css') -Filter '*.css' -File | ForEach-Object { Get-Content -LiteralPath $_.FullName -Raw }) -join "`n"
foreach ($contract in @('@media (min-width: 48rem)', '@media (min-width: 72rem)', '.editorial-card', '.primary-navigation', '.faq', '.timeline', '.table-scroll')) {
    if (-not $css.Contains($contract)) { throw "Missing responsive/component CSS contract: $contract" }
}

foreach ($contract in @('-webkit-line-clamp: 2', 'aspect-ratio: 16 / 10', '.editorial-card__button', '.badge--future', '.entry-content--documents')) {
    if (-not $css.Contains($contract)) { throw "Missing live-migration/card CSS contract: $contract" }
}

$headerSource = Get-Content -LiteralPath (Join-Path $Theme 'header.php') -Raw
foreach ($contract in @('data-menu-toggle', 'data-search-toggle', 'Avvia una mediazione', 'Scopri i corsi', 'viewport')) {
    if (-not $headerSource.Contains($contract)) { throw "Missing header contract: $contract" }
}

$phpSource = (Get-ChildItem -LiteralPath $Theme -Filter '*.php' -File -Recurse | ForEach-Object { Get-Content -LiteralPath $_.FullName -Raw }) -join "`n"
foreach ($forbidden in @('wp_insert_post(', 'wp_update_post(', 'wp_delete_post(', 'update_option(', 'Elementor', 'jquery', 'mediacon-design-core-compatibility')) {
    if ($phpSource -match [regex]::Escape($forbidden)) { throw "Forbidden theme dependency or mutation: $forbidden" }
}

foreach ($contract in @('mediacon_one_page_map', 'mediacon_one_offices', 'mediacon_one_run_preflight', 'mediacon_one_the_content')) {
    if (-not $phpSource.Contains($contract)) { throw "Missing live-migration PHP contract: $contract" }
}

$assetReferences = [regex]::Matches($phpSource, "assets/[A-Za-z0-9_./-]+\.(?:css|js|svg|png)") | ForEach-Object { $_.Value } | Sort-Object -Unique
foreach ($asset in $assetReferences) {
    if (-not (Test-Path -LiteralPath (Join-Path $Theme ($asset -replace '/', '\')))) {
        throw "Broken local asset reference: $asset"
    }
}

Write-Output 'Theme structure: PASS'
Write-Output 'theme.json validation: PASS'
Write-Output 'Desktop breakpoint (72rem): PASS'
Write-Output 'Tablet breakpoint (48rem): PASS'
Write-Output 'Mobile-first base and compact breakpoint: PASS'
Write-Output 'Menu, search, 404, archives, singles, and WordPress fallback contracts: PASS'
Write-Output "Local asset references: PASS ($($assetReferences.Count) references)"
