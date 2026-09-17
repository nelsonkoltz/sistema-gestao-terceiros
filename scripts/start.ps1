$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
$utf8 = New-Object System.Text.UTF8Encoding($false)
function New-Secret {
    $bytes = New-Object byte[] 32
    $rng = [Security.Cryptography.RandomNumberGenerator]::Create()
    try { $rng.GetBytes($bytes) } finally { $rng.Dispose() }
    return [Convert]::ToBase64String($bytes)
}
if (!(Test-Path -LiteralPath '.env.docker')) {
    $password = New-Secret
    $adminPassword = New-Secret
    $config = Get-Content '.env.docker.example' -Raw
    $config = $config.Replace('APP_KEY=', ('APP_KEY=base64:' + (New-Secret)))
    $config = $config.Replace('DB_PASSWORD=', ('DB_PASSWORD=' + $password))
    $config = $config.Replace('MYSQL_PASSWORD=', ('MYSQL_PASSWORD=' + $password))
    $config = $config.Replace('MYSQL_ROOT_PASSWORD=', ('MYSQL_ROOT_PASSWORD=' + (New-Secret)))
    $config = $config.Replace('ADMIN_PASSWORD=', ('ADMIN_PASSWORD=' + $adminPassword))
    [IO.File]::WriteAllText((Join-Path $PWD '.env.docker'), $config, $utf8)
    New-Item -ItemType Directory -Path '.local' -Force | Out-Null
    [IO.File]::WriteAllText((Join-Path $PWD '.local/acesso.txt'), "URL: http://localhost:8090`nUsuario: admin`nSenha: $adminPassword`n", $utf8)
}
docker compose up -d --build --wait --wait-timeout 180
if ($LASTEXITCODE -ne 0) { throw 'Falha ao iniciar o Docker. Consulte docker compose logs.' }
Write-Host 'Sistema: http://localhost:8090'
Write-Host 'Credenciais iniciais: .local/acesso.txt (na primeira instalacao).'
