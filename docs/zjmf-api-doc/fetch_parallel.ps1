# Parallel resumable fetcher for http://w2.test.idcsmart.com/doc/info
# Fetches the missing raw/*.html files using N parallel Start-Job workers.
# Usage: powershell -ExecutionPolicy Bypass -File fetch_parallel.ps1
# Then re-run fetch_api_docs.ps1 (it skips existing files and generates zjmf-api.md).
#
# NOTE: ASCII-only on purpose (avoids PS5.1 script-encoding quirks).

param(
    [string]$BaseUrl = 'http://w2.test.idcsmart.com',
    [int]$Jobs = 8
)

$ErrorActionPreference = 'Continue'
$OutDir   = Split-Path -Parent $MyInvocation.MyCommand.Path
$RawDir   = Join-Path $OutDir 'raw'
$ListPath = Join-Path $OutDir 'doc_list.json'

if (-not (Test-Path $ListPath)) {
    Write-Host "missing $ListPath ; run fetch_api_docs.ps1 first"
    exit 1
}

# ---------- flatten all action names ----------
$tree = Get-Content $ListPath -Raw -Encoding UTF8 | ConvertFrom-Json
$actions = New-Object System.Collections.ArrayList
function Add-Actions($nodes) {
    foreach ($n in $nodes) {
        if ($n.actions) {
            foreach ($a in $n.actions) {
                if ($a.name) { [void]$actions.Add([string]$a.name) }
            }
        }
        if ($n.children) { Add-Actions $n.children }
    }
}
Add-Actions $tree.list

function Get-RawPath([string]$name) {
    return Join-Path $RawDir ((( $name ) -replace '[^A-Za-z0-9._-]', '_') + '.html')
}

$missing = @($actions | Where-Object { -not (Test-Path (Get-RawPath $_)) })

Write-Host ("actions: {0}, already fetched: {1}, missing: {2}" -f $actions.Count, ($actions.Count - $missing.Count), $missing.Count)
if ($missing.Count -eq 0) {
    Write-Host 'nothing to fetch'
    exit 0
}

# ---------- split into chunks ----------
$size = [Math]::Ceiling($missing.Count / $Jobs)
$chunks = New-Object System.Collections.ArrayList
for ($i = 0; $i -lt $missing.Count; $i += $size) {
    $end = [Math]::Min($i + $size - 1, $missing.Count - 1)
    $slice = @($missing[$i..$end])
    [void]$chunks.Add($slice)
}
Write-Host ("workers: {0}, chunk size: {1}" -f $chunks.Count, $size)

# ---------- worker ----------
$worker = {
    param($names, $base, $rawDir)
    $ErrorActionPreference = 'Continue'
    $done = 0
    foreach ($name in $names) {
        $f = Join-Path $rawDir ((($name) -replace '[^A-Za-z0-9._-]', '_') + '.html')
        if (Test-Path $f) { continue }
        $ok = $false
        for ($t = 0; $t -lt 3 -and -not $ok; $t++) {
            try {
                $r = Invoke-WebRequest -Uri ($base + '/doc/info?name=' + $name) -UseBasicParsing -TimeoutSec 30
                [System.IO.File]::WriteAllText($f, $r.Content, [System.Text.Encoding]::UTF8)
                $ok = $true
            } catch {
                Start-Sleep -Milliseconds 400
            }
        }
        $done++
        if (($done % 100) -eq 0) { Write-Host ("  worker progress {0}" -f $done) }
        Start-Sleep -Milliseconds 10
    }
    Write-Host ("worker finished {0}" -f $names.Count)
}

foreach ($c in $chunks) {
    Start-Job -ScriptBlock $worker -ArgumentList $c, $BaseUrl, $RawDir | Out-Null
}

Get-Job | Wait-Job | Out-Null

foreach ($j in Get-Job) {
    Receive-Job $j
    Remove-Job $j -Force
}

# ---------- verify ----------
$stillMissing = @($actions | Where-Object { -not (Test-Path (Get-RawPath $_)) })
Write-Host ("verify: missing {0}/{1}" -f $stillMissing.Count, $actions.Count)
