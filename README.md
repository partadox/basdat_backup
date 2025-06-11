# =============================================================
# PostgreSQL Recovery Simulator
# =============================================================

## 📋 Overview
Aplikasi simulasi recovery database PostgreSQL dengan skenario Harbolnas Crisis Management. Dibangun dengan PHP, JavaScript, dan CSS untuk memberikan pengalaman hands-on dalam menangani situasi krisis database.

## 🚀 Features
- **Interactive SQL Interface**: Execute real PostgreSQL queries with authentic output formatting
- **Real-time Crisis Simulation**: Dynamic user count and revenue loss tracking
- **Progressive Learning Path**: 5-step recovery roadmap with database result validation
- **Performance Scoring**: Time-based scoring with bonus/penalty system
- **Security Features**: Granular query permissions and validation
- **PostgreSQL-Authentic Experience**: Terminal-like query output formatting
- **Educational Feedback**: Context-aware hints and validation messages

## 📁 Project Structure
```
basdat_handson-backup/
├── index.php           # Frontend (Main HTML page)
├── controller.php      # Backend API controller  
├── index.js           # Frontend JavaScript logic
├── index.css          # Styling and animations
├── setup_database.sql # Database setup script
├── Dockerfile         # Docker configuration
├── .env              # Environment variables
├── test-fixes.html   # Bug fix test suite
├── BUGFIX_REPORT.md  # Bug fix documentation
└── README.md         # Documentation
```

## 🛠️ Installation

### Local Development
1. **Requirements**:
   - PHP 8.0+
   - PostgreSQL 12+
   - Web server (Apache/Nginx)

2. **Setup**:
   ```bash
   # Clone atau copy files ke web directory
   cp -r basdat_handson-backup/ /var/www/html/
   
   # Set permissions
   chmod -R 755 /var/www/html/basdat_handson-backup/
   chown -R www-data:www-data /var/www/html/basdat_handson-backup/
   
   # Configure database connection in .env
   nano .env
   ```

3. **Database Setup**:
   ```bash
   # Run database setup script
   psql -h 145.79.10.205 -U dbPro -d basdatbackup -f setup_database.sql
   ```

### Docker Deployment
1. **Build and Run**:
   ```bash
   # Build Docker image
   docker build -t postgres-recovery-sim .
   
   # Run container
   docker run -d -p 80:80 --name recovery-sim postgres-recovery-sim
   ```

### Coolify Deployment
1. **Preparation**:
   - Ensure all files are in the project directory
   - Dockerfile is properly configured
   - Environment variables are set in .env

2. **Deploy to Coolify**:
   - Create new project in Coolify dashboard
   - Connect to your Git repository or upload files
   - Configure environment variables if needed
   - Deploy using the provided Dockerfile

## 🎮 How to Use

### For Students
1. **Start Simulation**: Click "🚀 START SIMULATION" on welcome screen
2. **Follow Roadmap**: Complete 5 recovery steps in sequence
3. **Execute Queries**: Use SQL interface to run database commands
4. **Monitor Progress**: Track your performance in real-time
5. **Complete Mission**: Achieve system recovery and get your score

### Recovery Steps
1. **Damage Assessment**: Analyze corrupted tables
2. **Recovery Planning**: Check backup availability
3. **Data Restoration**: Restore critical data from backup
4. **Data Validation**: Verify restored data integrity
5. **System Recovery**: Mark system as operational

### Sample Queries
```sql
-- Step 1: Damage Assessment
SELECT COUNT(*) FROM flash_sale_products;
SELECT COUNT(*) FROM orders;
SELECT COUNT(*) FROM transactions;

-- Step 2: Check Backups
SELECT * FROM backup_data;

-- Step 3: Restore Data
RESTORE flash_sale_products;

-- Step 4: Validate
SELECT COUNT(*) FROM flash_sale_products;
SELECT * FROM flash_sale_products LIMIT 3;

-- Step 5: Complete Recovery
UPDATE system_status SET status = 'OPERATIONAL';
```

## 🔧 Technical Details

### Backend (controller.php)
- **Framework**: Pure PHP with PDO
- **Database**: PostgreSQL connection
- **Security**: Query validation and sanitization
- **API**: RESTful endpoints for query execution
- **Features**: Simulated operations for educational purposes

### Frontend (index.js)
- **Framework**: Vanilla JavaScript
- **API Communication**: Fetch API for AJAX calls
- **Game Logic**: Step validation and progress tracking
- **Security**: Anti-cheat mechanisms (copy-paste protection)
- **UI**: Dynamic updates and animations

### Styling (index.css)
- **Framework**: Pure CSS with modern features
- **Design**: Responsive grid layouts
- **Animations**: CSS keyframes and transitions
- **Theme**: Dark query panel with syntax highlighting
- **Effects**: Hover states and interactive elements

## 🔒 Security Features
- **Query Validation**: Only SELECT and simulation commands allowed
- **Input Sanitization**: SQL injection protection
- **Anti-Cheat**: Copy-paste disabled in query input
- **Session Security**: No persistent data storage
- **CORS Protection**: Configurable origins

## 📊 Scoring System
- **Base Points**: 100 per completed step (500 total)
- **Time Bonus**: 
  - < 10 minutes: +500 points
  - < 20 minutes: +300 points
  - < 30 minutes: +100 points
- **Penalties**:
  - Errors: -50 points each
  - Hints used: -25 points each
- **Revenue Bonus**: Based on crisis resolution speed

## 🎯 Learning Objectives
- Database damage assessment techniques
- Backup verification and validation
- Recovery procedure execution
- Data integrity checking
- Crisis management under pressure
- SQL query optimization for recovery scenarios

## 🌐 Environment Variables
```env
# Database Configuration
DB_HOST=145.79.10.205
DB_PORT=5432
DB_NAME=basdatbackup
DB_USER=dbPro
DB_PASSWORD=your_password

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_NAME="PostgreSQL Recovery Simulator"
APP_VERSION=2.0

# Security
ALLOWED_ORIGINS=*
API_TIMEOUT=30
```

## 🐛 Troubleshooting

### Common Issues
1. **Database Connection Failed**:
   - Check .env configuration
   - Verify PostgreSQL server status
   - Test network connectivity

2. **Queries Not Executing**:
   - Check browser console for errors
   - Verify controller.php accessibility
   - Review Apache/Nginx error logs

3. **Docker Build Fails**:
   - Ensure all files are present
   - Check Dockerfile syntax
   - Verify base image availability

### Debug Mode
Enable debug mode by setting `APP_DEBUG=true` in .env file for detailed error messages.

## 📈 Performance Optimization
- **Caching**: Static assets cached by browser
- **Compression**: Gzip enabled for text files
- **Database**: Optimized queries with proper indexing
- **Frontend**: Minified CSS/JS in production

## 🔄 Updates and Maintenance
- **Version Control**: Use Git for version tracking
- **Database Backups**: Regular backup of simulation data
- **Security Updates**: Keep PHP and dependencies updated
- **Monitoring**: Track application performance and errors

## 📞 Support
For technical support or questions:
- Check troubleshooting section
- Review application logs
- Contact system administrator

## 📄 License
Educational use only. Not for commercial distribution.

---
**Version**: 2.0  
**Last Updated**: $(date)  
**PHP Version**: 8.2+  
**Database**: PostgreSQL 12+