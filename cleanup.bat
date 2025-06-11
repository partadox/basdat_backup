@echo off
REM =============================================================
REM Cleanup Script (Windows) - Simplified
REM =============================================================

echo 🧹 PostgreSQL Recovery Simulator - Cleanup
echo ==========================================

echo 🛑 Stopping web container...
docker-compose down

echo 🧽 Removing unused Docker images...
docker image prune -f

echo 📊 Current Docker status:
docker ps -a

echo.
echo ✅ Cleanup completed!
echo 💡 External database server remains untouched.
echo.
pause