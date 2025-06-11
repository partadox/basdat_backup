#!/bin/bash
# =============================================================
# Local Development Setup Script
# =============================================================

echo "🚀 PostgreSQL Recovery Simulator - Local Setup"
echo "=============================================="

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker Desktop first."
    exit 1
fi

# Check if ports are available
check_port() {
    if netstat -an | grep -q ":$1 "; then
        echo "❌ Port $1 is already in use"
        exit 1
    fi
}

echo "🔍 Checking ports..."
check_port 8080
check_port 5432
check_port 8081

# Clean up any existing containers
echo "🧹 Cleaning up existing containers..."
docker-compose down -v 2>/dev/null || true

# Build and start services
echo "🏗️ Building and starting services..."
docker-compose up --build -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 30

# Check services status
echo "📊 Services Status:"
docker-compose ps

echo ""
echo "✅ Setup Complete!"
echo ""
echo "🌐 Application URLs:"
echo "   • Web App:    http://localhost:8080"
echo "   • PgAdmin:    http://localhost:8081"
echo "   • Database:   localhost:5432"
echo ""
echo "🔑 Database Credentials:"
echo "   • Database:   basdatbackup_local"
echo "   • Username:   dbPro"
echo "   • Password:   local_password"
echo ""
echo "🔧 PgAdmin Credentials:"
echo "   • Email:      admin@simulator.local"
echo "   • Password:   admin123"
echo ""
echo "🎮 Ready to use! Open http://localhost:8080 to start the simulation."