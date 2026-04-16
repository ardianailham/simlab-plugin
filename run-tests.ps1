# run-tests.ps1
# Helper script to run local compatibility tests using Docker

param (
    [string]$PHP_VERSION = "8.1",
    [string]$WP_VERSION = "latest",
    [switch]$Cleanup
)

Write-Host "--- Local Compatibility Testing Setup ---" -ForegroundColor Cyan

if ($Cleanup) {
    Write-Host "Cleaning up existing containers..." -ForegroundColor Yellow
    docker compose -f docker-compose.test.yml down -v
    exit
}

# Ensure Docker is running
docker info > $null 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Error "Docker is not running. Please start Docker Desktop."
    exit 1
}

# Build and Run
Write-Host "Running tests for PHP $PHP_VERSION and WordPress $WP_VERSION..." -ForegroundColor Green

# We pass versions as env vars to the docker command
$env:PHP_VER = $PHP_VERSION
$env:WP_VER = $WP_VERSION

docker compose -f docker-compose.test.yml up --build --exit-code-from phpunit

if ($LASTEXITCODE -eq 0) {
    Write-Host "Tests Passed!" -ForegroundColor Green
} else {
    Write-Host "Tests Failed!" -ForegroundColor Red
}
