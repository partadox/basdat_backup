@echo off
REM =============================================================
REM Local Development Setup Script (Windows)
REM Uses External Database Server
REM =============================================================

echo 🚀 PostgreSQL Recovery Simulator - Local Setup
echo ==============================================
echo 📡 Using External Database Server (145.79.10.205)

REM Check if Docker is running
docker info >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker is not running. Please start Docker Desktop first.
    pause
    exit /b 1
)

echo 🔍 Checking if port 8080 is available...

REM Check port 8080
netstat -an | find ":8080 " >nul
if not errorlevel 1 (
    echo ❌ Port 8080 is already in use
    echo 💡 Please stop the service using port 8080 or change the port in docker-compose.yml
    pause
    exit /b 1
)

echo ✅ Port 8080 is available

REM Clean up any existing containers
echo 🧹 Cleaning up existing containers...
docker-compose down -v 2>nul

REM Build and start services
echo 🏗️ Building and starting web application...
docker-compose up --build -d

if errorlevel 1 (
    echo ❌ Failed to start services
    echo 📋 Checking logs...
    docker-compose logs
    pause
    exit /b 1
)

REM Wait for application to be ready
echo ⏳ Waiting for application to be ready...
timeout /t 15 /nobreak >nul

REM Test database connection
echo 🔗 Testing database connection...
curl -s http://localhost:8080/controller.php >nul
if errorlevel 1 (
    echo ⚠️  Warning: Could not test API endpoint immediately
    echo 💡 The application might still be starting up
) else (
    echo ✅ API endpoint is responding
)

REM Check services status
echo 📊 Services Status:
docker-compose ps

echo.
echo ✅ Setup Complete!
echo.
echo 🌐 Application URL:
echo    • Web App:    http://localhost:8080
echo.
echo 🗄️ Database Info (External Server):
echo    • Host:       145.79.10.205
echo    • Port:       5432
echo    • Database:   basdatbackup
echo    • Username:   dbPro
echo.
echo 🎮 Ready to use! Open http://localhost:8080 to start the simulation.
echo 💡 The app uses the existing database server, no local database needed.
echo.
pause