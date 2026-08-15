# zjmf-api-doc crawler for http://w2.test.idcsmart.com/doc
# Usage: powershell -ExecutionPolicy Bypass -File fetch_api_docs.ps1
# Outputs (same dir):
#   doc_list.json   raw api catalog (JSON)
#   raw/*.html      per-action detail pages (resumable; existing files are skipped)
#   zjmf-api.md     merged Markdown doc
#
# NOTE: this script is ASCII-only on purpose (avoids PS5.1 script-encoding quirks).
# The doc content itself is Chinese because it comes from the crawled pages.

param(
    [string]$BaseUrl = 'http://w2.test.idcsmart.com',
    [int]$SleepMs = 80,
    [int]$MaxRetries = 3
)

$ErrorActionPreference = 'Continue'
$OutDir   = Split-Path -Parent $MyInvocation.MyCommand.Path
$RawDir   = Join-Path $OutDir 'raw'
$ListPath = Join-Path $OutDir 'doc_list.json'
$MdPath   = Join-Path $OutDir 'zjmf-api.md'
New-Item -ItemType Directory -Force -Path $RawDir | Out-Null

function Invoke-WithRetry([string]$url) {
    for ($i = 0; $i -lt $MaxRetries; $i++) {
        try {
            return Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 30
        } catch {
            Start-Sleep -Milliseconds 800
        }
    }
    Write-Warning "fetch failed: $url"
    return $null
}

# ---------- 1. catalog ----------
if (-not (Test-Path $ListPath)) {
    Write-Host 'Fetching /doc/list ...'
    $r = Invoke-WithRetry "$BaseUrl/doc/list"
    if ($r) {
        [System.IO.File]::WriteAllText($ListPath, $r.Content, [System.Text.Encoding]::UTF8)
    }
}
$tree = Get-Content $ListPath -Raw -Encoding UTF8 | ConvertFrom-Json

# ---------- 2. flatten all actions (multi-level children) ----------
$actions = New-Object System.Collections.ArrayList
function Add-Actions($nodes) {
    foreach ($n in $nodes) {
        if ($n.actions) {
            foreach ($a in $n.actions) {
                if ($a.name) {
                    [void]$actions.Add([pscustomobject]@{
                        name   = $a.name
                        title  = $a.title
                        url    = $a.url
                        method = $a.method
                        desc   = $a.desc
                        params = $a.param
                        rets   = $a.return
                    })
                }
            }
        }
        if ($n.children) { Add-Actions $n.children }
    }
}
Add-Actions $tree.list
$total = $actions.Count
Write-Host "Total actions: $total"

# ---------- 3. fetch detail pages (resumable) ----------
function Get-SafeName([string]$name) {
    return ($name -replace '[^A-Za-z0-9._-]', '_')
}

$done = 0
foreach ($a in $actions) {
    $rawFile = Join-Path $RawDir ((Get-SafeName $a.name) + '.html')
    if (Test-Path $rawFile) { $done++; continue }
    $r = Invoke-WithRetry ("$BaseUrl/doc/info?name=" + $a.name)
    if ($r) {
        [System.IO.File]::WriteAllText($rawFile, $r.Content, [System.Text.Encoding]::UTF8)
    }
    $done++
    if (($done % 25) -eq 0) { Write-Host ("progress {0}/{1}" -f $done, $total) }
    Start-Sleep -Milliseconds $SleepMs
}
Write-Host "Fetch done: $done/$total"

# ---------- 4. parse one detail page ----------
function Parse-DocHtml([string]$html) {
    $doc = [pscustomobject]@{ title = ''; url = ''; method = ''; desc = ''; json = ''; params = @() }
    if (-not $html) { return $doc }
    if ($html -match '<h2>[^<]*([^<]*)</h2>') { $doc.title = $Matches[1].Trim() }
    if ($html -match 'class="text-primary">([^<]*)</p>') { $doc.desc = $Matches[1].Trim() }
    if ($html -match '<code id="json_text">(.*?)</code>') {
        $doc.json = ([System.Net.WebUtility]::HtmlDecode($Matches[1]) -replace '<br\s*/?>', "`n").Trim()
    }
    # url + method: line like: <p>[address] <span class="label label-success">POST</span></p>
    $pMatch = [regex]::Match($html, '<p>[^<]*?\s+([^<]+?)\s*<span class="label[^"]*"[^>]*>\s*([^<]*?)\s*</span></p>', 'Singleline')
    if ($pMatch.Success) {
        $doc.url = $pMatch.Groups[1].Value.Trim()
        $doc.method = $pMatch.Groups[2].Value.Trim()
    }
    # param table after <h3>
    $tblMatch = [regex]::Match($html, '<h3>[^<]*</h3>\s*<table[^>]*>(.*?)</table>', 'Singleline')
    if ($tblMatch.Success) {
        foreach ($row in [regex]::Matches($tblMatch.Groups[1].Value, '<tr>(.*?)</tr>', 'Singleline')) {
            $cells = [regex]::Matches($row.Groups[1].Value, '<td>(.*?)</td>', 'Singleline')
            if ($cells.Count -ge 2) {
                $vals = @()
                foreach ($c in $cells) {
                    $vals += [System.Net.WebUtility]::HtmlDecode($c.Groups[1].Value).Trim()
                }
                $doc.params += , $vals
            }
        }
    }
    return $doc
}

function Get-ActionDetail($action) {
    $rawFile = Join-Path $RawDir ((Get-SafeName $action.name) + '.html')
    if (Test-Path $rawFile) {
        return Parse-DocHtml (Get-Content $rawFile -Raw -Encoding UTF8)
    }
    return $null
}

# ---------- 5. generate Markdown ----------
$sb = New-Object System.Text.StringBuilder
[void]$sb.AppendLine('# zjmf finance API doc')
[void]$sb.AppendLine('')
[void]$sb.AppendLine(('> crawled from ' + $BaseUrl + '/doc at ' + (Get-Date -Format 'yyyy-MM-dd HH:mm') + ' | actions: ' + $total))
[void]$sb.AppendLine('> sources: doc_list.json + raw/*.html | re-crawl: powershell -ExecutionPolicy Bypass -File fetch_api_docs.ps1')
[void]$sb.AppendLine('')

function Write-ActionMd($a) {
    $d = Get-ActionDetail $a
    $title = ''
    $method = ''
    $url = ''
    $desc = ''
    if ($d) {
        $title = $d.title
        $method = $d.method
        $url = $d.url
        $desc = $d.desc
    }
    if (-not $title) { $title = $a.title }
    if (-not $method) { $method = $a.method }
    if (-not $url) { $url = $a.url }
    if (-not $desc) { $desc = $a.desc }

    $heading = '### ' + $title
    if ($method) { $heading += ' -- ' + $method.ToUpper() }
    if ($url) { $heading += ' ' + $url }
    [void]$sb.AppendLine($heading)
    [void]$sb.AppendLine('')
    [void]$sb.AppendLine('- controller: ``' + $a.name + '``')
    if ($desc) { [void]$sb.AppendLine('- desc: ' + $desc) }
    [void]$sb.AppendLine('')

    $paramRows = $null
    if ($d -and $d.params -and $d.params.Count -gt 0) { $paramRows = $d.params }
    elseif ($a.params -and $a.params.Count -gt 0) { $paramRows = $a.params }

    if ($paramRows -and $paramRows.Count -gt 0) {
        [void]$sb.AppendLine('**params**')
        [void]$sb.AppendLine('')
        [void]$sb.AppendLine('| name | type | required | default | other | desc |')
        [void]$sb.AppendLine('| --- | --- | --- | --- | --- | --- |')
        $isList = $paramRows[0] -is [System.Array]
        if ($isList) {
            foreach ($p in $paramRows) {
                $cells = @()
                foreach ($v in $p) { $cells += ('' + $v) -replace "`n", ' ' }
                $line = '| ' + ($cells -join ' | ') + ' |'
                [void]$sb.AppendLine($line)
            }
        } else {
            foreach ($p in $paramRows) {
                $f = @($p.name, $p.type, $p.require, $p.default, $p.other, $p.desc)
                [void]$sb.AppendLine('| ' + ($f -join ' | ') + ' |')
            }
        }
        [void]$sb.AppendLine('')
    }

    if ($d -and $d.json) {
        [void]$sb.AppendLine('**response example**')
        [void]$sb.AppendLine('')
        [void]$sb.AppendLine('```json')
        [void]$sb.AppendLine($d.json)
        [void]$sb.AppendLine('```')
        [void]$sb.AppendLine('')
    }

    if ($a.rets) {
        foreach ($r in @($a.rets)) {
            if ($r) { [void]$sb.AppendLine('- return-field: ' + $r) }
        }
        [void]$sb.AppendLine('')
    }
}

function Walk-ForMd($nodes) {
    foreach ($n in $nodes) {
        if ($n.actions -and $n.actions.Count -gt 0) {
            [void]$sb.AppendLine('---')
            [void]$sb.AppendLine('')
            [void]$sb.AppendLine('## ' + $n.title)
            [void]$sb.AppendLine('')
            foreach ($a in $n.actions) {
                if ($a.name) { Write-ActionMd $a }
            }
            [void]$sb.AppendLine('')
        }
        if ($n.children) { Walk-ForMd $n.children }
    }
}
Walk-ForMd $tree.list

[System.IO.File]::WriteAllText($MdPath, $sb.ToString(), [System.Text.Encoding]::UTF8)
Write-Host "Generated $MdPath"
Write-Host 'All done'
