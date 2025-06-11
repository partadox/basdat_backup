// =============================================================
// PostgreSQL Recovery Simulator - Frontend JavaScript - FIXED
// File: index.js
// =============================================================

// Game State Management
let gameState = {
    startTime: null,
    currentStep: 1,
    totalSteps: 5,
    score: 0,
    errors: 0,
    hintsUsed: 0,
    timerInterval: null,
    simulationInterval: null,
    concurrentUsers: 0,
    revenueLoss: 0,
    stepProgress: {
        1: { required: 3, completed: [], executedQueries: [] }, // Must execute 3 different COUNT queries
        2: { required: 1, completed: [], executedQueries: [] }, // Must SELECT from backup_data
        3: { required: 1, completed: [], executedQueries: [] }, // Must RESTORE flash_sale_products
        4: { required: 2, completed: [], executedQueries: [] }, // Must validate restoration (COUNT + SELECT)
        5: { required: 1, completed: [], executedQueries: [] }  // Must UPDATE system status
    }
};

// API Configuration
const API_BASE_URL = './controller.php';

// Utility Functions
async function makeAPICall(data) {
    try {
        const response = await fetch(API_BASE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API call failed:', error);
        throw error;
    }
}

// Task definitions with proper validation based on database output
const tasks = {
    1: {
        title: "🔍 Step 1: Damage Assessment",
        hint: "Lakukan analisis kerusakan database dengan menghitung jumlah record di setiap tabel kritis. Anda perlu menjalankan 3 query berbeda: (1) SELECT COUNT(*) FROM flash_sale_products; (2) SELECT COUNT(*) FROM orders; (3) SELECT COUNT(*) FROM transactions;",
        expectedQueries: [
            "SELECT COUNT(*) FROM flash_sale_products", 
            "SELECT COUNT(*) FROM orders", 
            "SELECT COUNT(*) FROM transactions"
        ],
        requiredQueries: 3,
        validation: async (query, dbResult) => {
            const lowerQuery = query.toLowerCase().replace(/\s+/g, ' ').trim();
            const progress = gameState.stepProgress[1];
            
            // Check if it's a valid COUNT query
            if (!lowerQuery.includes('count') || !lowerQuery.includes('from')) {
                return { valid: false, complete: false, message: "❌ This step requires COUNT queries to assess damage." };
            }
            
            // Check specific tables and validate based on actual database results
            if (lowerQuery.includes('flash_sale_products')) {
                const queryKey = 'flash_sale_products_count';
                if (!progress.completed.includes(queryKey) && dbResult.success && dbResult.data.length > 0) {
                    const count = parseInt(dbResult.data[0].count) || 0;
                    if (count === 0) {
                        progress.completed.push(queryKey);
                        return { 
                            valid: true, 
                            complete: progress.completed.length >= progress.required,
                            message: `✅ Confirmed: flash_sale_products table is EMPTY (${count} records) - CRITICAL!`
                        };
                    }
                }
            }
            
            if (lowerQuery.includes('orders')) {
                const queryKey = 'orders_count';
                if (!progress.completed.includes(queryKey) && dbResult.success && dbResult.data.length > 0) {
                    const count = parseInt(dbResult.data[0].count) || 0;
                    if (count > 0) {
                        progress.completed.push(queryKey);
                        return { 
                            valid: true, 
                            complete: progress.completed.length >= progress.required,
                            message: `✅ Confirmed: orders table has ${count} records - Some may be corrupted`
                        };
                    }
                }
            }
            
            if (lowerQuery.includes('transactions')) {
                const queryKey = 'transactions_count';
                if (!progress.completed.includes(queryKey) && dbResult.success && dbResult.data.length > 0) {
                    const count = parseInt(dbResult.data[0].count) || 0;
                    progress.completed.push(queryKey);
                    return { 
                        valid: true, 
                        complete: progress.completed.length >= progress.required,
                        message: `✅ Confirmed: transactions table has ${count} records - Some data may be missing`
                    };
                }
            }
            
            return { valid: false, complete: false, message: "⚠️ Query executed but not part of damage assessment requirements." };
        }
    },
    2: {
        title: "🏗️ Step 2: Check Recovery Options",
        hint: "Periksa ketersediaan data backup yang bisa digunakan untuk pemulihan. Jalankan query: SELECT * FROM backup_data;",
        expectedQueries: ["SELECT * FROM backup_data"],
        requiredQueries: 1,
        validation: async (query, dbResult) => {
            const lowerQuery = query.toLowerCase().replace(/\s+/g, ' ').trim();
            const progress = gameState.stepProgress[2];
            
            if (lowerQuery.includes('backup_data') && lowerQuery.includes('select')) {
                const queryKey = 'backup_check';
                if (!progress.completed.includes(queryKey) && dbResult.success && dbResult.data.length > 0) {
                    // Check if backup contains flash_sale_products data
                    const hasFlashSaleBackup = dbResult.data.some(backup => 
                        backup.table_name && backup.table_name.includes('flash_sale_products')
                    );
                    
                    if (hasFlashSaleBackup) {
                        progress.completed.push(queryKey);
                        return { 
                            valid: true, 
                            complete: progress.completed.length >= progress.required,
                            message: `✅ Backup data found! flash_sale_products backup is available for restoration.`
                        };
                    }
                }
            }
            
            return { valid: false, complete: false, message: "❌ Please check the backup_data table to see available recovery options." };
        }
    },
    3: {
        title: "📥 Step 3: Restore Critical Data",
        hint: "Lakukan pemulihan data flash_sale_products dari backup. Gunakan perintah: RESTORE flash_sale_products;",
        expectedQueries: ["RESTORE flash_sale_products"],
        requiredQueries: 1,
        validation: async (query, dbResult) => {
            const lowerQuery = query.toLowerCase().replace(/\s+/g, ' ').trim();
            const progress = gameState.stepProgress[3];
            
            if (lowerQuery.includes('restore') && lowerQuery.includes('flash_sale_products')) {
                const queryKey = 'restore_operation';
                if (!progress.completed.includes(queryKey) && dbResult.success) {
                    // Check if restore was successful based on API response
                    if (dbResult.message && dbResult.message.includes('RESTORE COMPLETED')) {
                        progress.completed.push(queryKey);
                        return { 
                            valid: true, 
                            complete: progress.completed.length >= progress.required,
                            message: `✅ Restoration successful! Flash sale products have been recovered.`
                        };
                    }
                }
            }
            
            return { valid: false, complete: false, message: "❌ Use the RESTORE command to recover flash_sale_products data." };
        }
    },
    4: {
        title: "✅ Step 4: Data Validation",
        hint: "Validasi bahwa data yang dipulihkan sudah benar dan lengkap. Jalankan 2 query: (1) SELECT COUNT(*) FROM flash_sale_products; (2) SELECT * FROM flash_sale_products LIMIT 3;",
        expectedQueries: ["SELECT COUNT(*) FROM flash_sale_products", "SELECT * FROM flash_sale_products"],
        requiredQueries: 2,
        validation: async (query, dbResult) => {
            const lowerQuery = query.toLowerCase().replace(/\s+/g, ' ').trim();
            const progress = gameState.stepProgress[4];
            
            if (lowerQuery.includes('select') && lowerQuery.includes('flash_sale_products')) {
                
                // Count validation
                if (lowerQuery.includes('count')) {
                    const queryKey = 'validation_count';
                    if (!progress.completed.includes(queryKey) && dbResult.success && dbResult.data.length > 0) {
                        const count = parseInt(dbResult.data[0].count) || 0;
                        if (count === 4) { // Should have exactly 4 flash sale products
                            progress.completed.push(queryKey);
                            return { 
                                valid: true, 
                                complete: progress.completed.length >= progress.required,
                                message: `✅ Data count validated: ${count} flash sale products restored (Expected: 4)`
                            };
                        } else {
                            return { 
                                valid: false, 
                                complete: false, 
                                message: `⚠️ Data count mismatch: Found ${count} records, expected 4. Restoration may be incomplete.`
                            };
                        }
                    }
                }
                
                // Content validation
                if (lowerQuery.includes('*') || lowerQuery.includes('limit')) {
                    const queryKey = 'validation_content';
                    if (!progress.completed.includes(queryKey) && dbResult.success && dbResult.data.length > 0) {
                        // Check if data has required structure
                        const hasValidStructure = dbResult.data.every(item => 
                            item.flash_sale_id && item.product_id && item.flash_sale_price
                        );
                        
                        if (hasValidStructure) {
                            progress.completed.push(queryKey);
                            return { 
                                valid: true, 
                                complete: progress.completed.length >= progress.required,
                                message: `✅ Data structure validated: All required fields present`
                            };
                        }
                    }
                }
            }
            
            return { valid: false, complete: false, message: "❌ Please validate the restored data using COUNT and SELECT queries." };
        }
    },
    5: {
        title: "🚀 Step 5: System Recovery",
        hint: "Tandai sistem sebagai operasional untuk menyelesaikan proses pemulihan. Gunakan query: UPDATE system_status SET status = 'OPERATIONAL';",
        expectedQueries: ["UPDATE system_status SET status = 'OPERATIONAL'"],
        requiredQueries: 1,
        validation: async (query, dbResult) => {
            const normalizedQuery = query.toLowerCase().replace(/\s+/g, ' ').trim();
            const progress = gameState.stepProgress[5];
            
            if ((normalizedQuery === "update system_status set status = 'operational'" || 
                 normalizedQuery === 'update system_status set status = "operational"')) {
                const queryKey = 'system_update';
                if (!progress.completed.includes(queryKey) && dbResult.success) {
                    if (dbResult.message && dbResult.message.includes('SYSTEM RECOVERY COMPLETED')) {
                        progress.completed.push(queryKey);
                        return { 
                            valid: true, 
                            complete: progress.completed.length >= progress.required,
                            message: `✅ System recovery completed! Database is now operational.`
                        };
                    }
                }
            }
            
            return { valid: false, complete: false, message: "❌ Use exact syntax: UPDATE system_status SET status = 'OPERATIONAL';" };
        }
    }
};

// Game Flow Functions
async function startSimulation() {
    document.getElementById('welcomeScreen').style.display = 'none';
    document.getElementById('gameScreen').style.display = 'block';
    
    gameState.startTime = Date.now();
    startTimer();
    startSystemSimulation();
    
    updateCurrentTask();
}

function startTimer() {
    gameState.timerInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - gameState.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        document.getElementById('timer').textContent = `⏱️ ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000);
}

function startSystemSimulation() {
    gameState.concurrentUsers = 450000;
    gameState.revenueLoss = 2500000;
    
    gameState.simulationInterval = setInterval(() => {
        if (gameState.concurrentUsers > 50000) {
            gameState.concurrentUsers -= Math.floor(Math.random() * 2000) + 1000;
            if (gameState.concurrentUsers < 50000) gameState.concurrentUsers = 50000;
        }
        
        gameState.revenueLoss += Math.floor(Math.random() * 100000) + 50000;
        updateStatusDashboard();
    }, 3000);
    
    updateStatusDashboard();
}

function updateStatusDashboard() {
    document.getElementById('concurrentUsers').textContent = gameState.concurrentUsers.toLocaleString();
    document.getElementById('revenueLoss').textContent = 'Rp ' + (gameState.revenueLoss / 1000000).toFixed(1) + 'M';
    document.getElementById('dbStatus').textContent = gameState.currentStep >= 5 ? 'OPERATIONAL' : 'CRITICAL';
    
    const progress = (gameState.currentStep - 1) / gameState.totalSteps * 100;
    document.getElementById('progressPercent').textContent = Math.floor(progress) + '%';
    document.getElementById('progressBar').style.width = progress + '%';
}

function updateCurrentTask() {
    const task = tasks[gameState.currentStep];
    if (!task) return;
    
    document.getElementById('currentTaskTitle').textContent = task.title;
    document.getElementById('currentHint').textContent = task.hint;
    
    // Update roadmap
    document.querySelectorAll('.roadmap-item').forEach(item => {
        item.classList.remove('active', 'completed');
    });
    
    const currentItem = document.querySelector(`[data-step="${gameState.currentStep}"]`);
    if (currentItem) currentItem.classList.add('active');
    
    for (let i = 1; i < gameState.currentStep; i++) {
        const completedItem = document.querySelector(`[data-step="${i}"]`);
        if (completedItem) {
            completedItem.classList.add('completed');
            const checkbox = completedItem.querySelector('.roadmap-checkbox');
            if (checkbox) checkbox.textContent = '✓';
        }
    }
}

function toggleDbReference() {
    const dbRef = document.getElementById('dbReference');
    dbRef.style.display = dbRef.style.display === 'none' ? 'block' : 'none';
}

// FIXED: New function to display database output like PostgreSQL terminal
function formatDatabaseOutput(data, query) {
    if (!data || data.length === 0) {
        return "(0 rows)";
    }
    
    // Get column names from first row
    const columns = Object.keys(data[0]);
    
    // Calculate column widths
    const columnWidths = {};
    columns.forEach(col => {
        columnWidths[col] = Math.max(
            col.length,
            ...data.map(row => String(row[col] || '').length)
        );
    });
    
    let output = '';
    
    // Header
    output += columns.map(col => col.padEnd(columnWidths[col])).join(' | ') + '\n';
    output += columns.map(col => '-'.repeat(columnWidths[col])).join('-+-') + '\n';
    
    // Data rows
    data.forEach(row => {
        output += columns.map(col => String(row[col] || '').padEnd(columnWidths[col])).join(' | ') + '\n';
    });
    
    output += `(${data.length} row${data.length !== 1 ? 's' : ''})`;
    
    return output;
}

async function executeQuery() {
    const queryInput = document.getElementById('queryInput');
    const queryResult = document.getElementById('queryResult');
    const query = queryInput.value.trim();
    
    if (!query) {
        queryResult.textContent = 'Please enter a query.';
        return;
    }
    
    try {
        // Show loading state
        queryResult.textContent = 'Executing query...\n';
        
        const result = await makeAPICall({
            action: 'query',
            query: query
        });
        
        let displayMessage = '';
        
        if (result.success) {
            // FIXED: Display output like PostgreSQL terminal
            if (result.data && result.data.length >= 0) {
                displayMessage = formatDatabaseOutput(result.data, query);
            } else {
                displayMessage = result.message || 'Query executed successfully.';
            }
            
            // FIXED: Check validation based on actual database output
            const task = tasks[gameState.currentStep];
            if (task) {
                const validation = await task.validation(query, result);
                
                if (validation.valid) {
                    gameState.score += 100;
                    
                    // Show progress and validation message
                    const progress = gameState.stepProgress[gameState.currentStep];
                    const progressText = `\n\nStep Progress: ${progress.completed.length}/${progress.required} requirements completed`;
                    displayMessage += progressText;
                    displayMessage += '\n' + validation.message;
                    
                    if (validation.complete) {
                        displayMessage += '\n\n🎉 Step completed! Moving to next step...';
                        setTimeout(() => {
                            if (gameState.currentStep < gameState.totalSteps) {
                                gameState.currentStep++;
                                updateCurrentTask();
                                updateStatusDashboard();
                            } else {
                                completeSimulation();
                            }
                        }, 2000);
                    }
                } else {
                    if (validation.message) {
                        displayMessage += '\n\n' + validation.message;
                    }
                }
            }
        } else {
            gameState.errors++;
            displayMessage = 'ERROR: ' + (result.error || 'Unknown error occurred');
        }
        
        queryResult.textContent = displayMessage;
        
    } catch (error) {
        gameState.errors++;
        queryResult.textContent = 'Connection Error: ' + error.message;
        console.error('Query execution failed:', error);
    }
}

function showHint() {
    gameState.hintsUsed++;
    const task = tasks[gameState.currentStep];
    if (task) {
        alert(`💡 Hint: ${task.hint}`);
    }
}

function completeSimulation() {
    clearInterval(gameState.timerInterval);
    clearInterval(gameState.simulationInterval);
    
    const totalTime = Math.floor((Date.now() - gameState.startTime) / 1000);
    const minutes = Math.floor(totalTime / 60);
    const seconds = totalTime % 60;
    
    // Calculate final score
    let finalScore = gameState.score;
    
    // Time bonus (faster = better)
    if (totalTime < 600) { // Under 10 minutes
        finalScore += 500;
    } else if (totalTime < 1200) { // Under 20 minutes
        finalScore += 300;
    } else if (totalTime < 1800) { // Under 30 minutes
        finalScore += 100;
    }
    
    // Error penalty
    finalScore -= (gameState.errors * 50);
    
    // Hint penalty
    finalScore -= (gameState.hintsUsed * 25);
    
    // Revenue saved bonus
    const revenueSaved = Math.max(0, 10000000 - gameState.revenueLoss);
    finalScore += Math.floor(revenueSaved / 100000);
    
    let grade = 'F';
    let feedback = 'Keep practicing!';
    
    if (finalScore >= 800) {
        grade = 'A+';
        feedback = 'Outstanding! You\'re ready for production!';
    } else if (finalScore >= 700) {
        grade = 'A';
        feedback = 'Excellent work! You handled the crisis well.';
    } else if (finalScore >= 600) {
        grade = 'B';
        feedback = 'Good job! Some room for improvement.';
    } else if (finalScore >= 500) {
        grade = 'C';
        feedback = 'Decent work, but practice more scenarios.';
    } else if (finalScore >= 400) {
        grade = 'D';
        feedback = 'You completed it, but work on speed and accuracy.';
    }
    
    const resultsHTML = `
        <div class="alert alert-success">
            <h3>🎉 Mission Completed!</h3>
            <p>You successfully recovered the TokoPintar database during the Harbolnas crisis!</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div class="status-card">
                <div>Final Grade</div>
                <div class="status-value">${grade}</div>
                <div>Performance</div>
            </div>
            <div class="status-card">
                <div>Total Time</div>
                <div class="status-value">${minutes}:${seconds.toString().padStart(2, '0')}</div>
                <div>Minutes</div>
            </div>
            <div class="status-card">
                <div>Final Score</div>
                <div class="status-value">${finalScore}</div>
                <div>Points</div>
            </div>
            <div class="status-card">
                <div>Revenue Saved</div>
                <div class="status-value">Rp ${(revenueSaved / 1000000).toFixed(1)}M</div>
                <div>Million</div>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <h4>📊 Performance Breakdown:</h4>
            <ul style="margin: 15px 0 15px 30px;">
                <li><strong>Base Score:</strong> ${gameState.score} points (tasks completed)</li>
                <li><strong>Time Bonus:</strong> ${finalScore - gameState.score + (gameState.errors * 50) + (gameState.hintsUsed * 25) - Math.floor(revenueSaved / 100000)} points</li>
                <li><strong>Error Penalty:</strong> -${gameState.errors * 50} points (${gameState.errors} errors)</li>
                <li><strong>Hint Penalty:</strong> -${gameState.hintsUsed * 25} points (${gameState.hintsUsed} hints used)</li>
                <li><strong>Revenue Bonus:</strong> ${Math.floor(revenueSaved / 100000)} points</li>
            </ul>
        </div>
        
        <div class="alert alert-warning">
            <h4>🎓 Learning Summary:</h4>
            <p><strong>${feedback}</strong></p>
            <p>Key skills practiced:</p>
            <ul style="margin: 10px 0 10px 30px;">
                <li>✅ Database damage assessment</li>
                <li>✅ Backup verification and restoration</li>
                <li>✅ Data validation and integrity checking</li>
                <li>✅ Crisis management under pressure</li>
                <li>✅ SQL query optimization for recovery</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <button class="btn" onclick="location.reload()">
                🔄 Try Again
            </button>
            <button class="btn btn-success" onclick="downloadCertificate()">
                📜 Download Certificate
            </button>
        </div>
    `;
    
    document.getElementById('finalResults').innerHTML = resultsHTML;
    document.getElementById('resultsCard').style.display = 'block';
    
    // Update status to show success
    document.getElementById('dbStatus').textContent = 'OPERATIONAL';
    document.getElementById('concurrentUsers').textContent = '50,000';
    document.getElementById('progressPercent').textContent = '100%';
    document.getElementById('progressBar').style.width = '100%';
}

function downloadCertificate() {
    const totalTime = Math.floor((Date.now() - gameState.startTime) / 1000);
    const minutes = Math.floor(totalTime / 60);
    const seconds = totalTime % 60;
    
    const certificateContent = `
===============================================
    POSTGRESQL RECOVERY SIMULATION CERTIFICATE
===============================================

This certifies that the participant has successfully
completed the PostgreSQL Backup & Recovery Simulation:
"Harbolnas Crisis Management"

Performance Summary:
- Completion Time: ${minutes}:${seconds.toString().padStart(2, '0')}
- Final Score: ${gameState.score} points
- Tasks Completed: ${gameState.currentStep}/${gameState.totalSteps}
- Errors: ${gameState.errors}
- Hints Used: ${gameState.hintsUsed}

Skills Demonstrated:
✓ Database damage assessment
✓ Backup verification and restoration  
✓ Data validation and integrity checking
✓ Crisis management under pressure
✓ SQL query optimization for recovery

Date: ${new Date().toLocaleDateString()}
Simulator: PostgreSQL Recovery Game v2.0

===============================================
    `;
    
    const blob = new Blob([certificateContent], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'PostgreSQL_Recovery_Certificate.txt';
    a.click();
    URL.revokeObjectURL(url);
}

// Event Listeners and Initialization
document.addEventListener('DOMContentLoaded', function() {
    const statusCards = document.querySelectorAll('.status-card');
    statusCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    const queryInput = document.getElementById('queryInput');
    if (queryInput) {
        queryInput.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.keyCode === 13) { // Ctrl+Enter
                executeQuery();
            }
            if (e.ctrlKey && e.keyCode === 86) {
                e.preventDefault();
                alert('🚫 Copy-paste tidak diperbolehkan! Ketik query sendiri untuk melatih skill Anda.');
                return false;
            }
        });
    }
    
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('keydown', function(e) {
        if (!e.target.classList.contains('query-input')) {
            if (e.ctrlKey && (e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88)) {
                e.preventDefault();
                return false;
            }
        }
        
        if (e.keyCode === 123 || (e.ctrlKey && e.shiftKey && e.keyCode === 73) || (e.ctrlKey && e.keyCode === 85)) {
            e.preventDefault();
            return false;
        }
    });
    
    const timer = document.getElementById('timer');
    if (timer) {
        timer.title = 'Recovery time - faster is better!';
    }
    
    setTimeout(() => {
        if (gameState.currentStep === 1) {
            const dbRef = document.getElementById('dbReference');
            if (dbRef) {
                dbRef.style.display = 'block';
            }
        }
    }, 3000);
});

// Global functions for HTML onclick events
window.startSimulation = startSimulation;
window.executeQuery = executeQuery;
window.showHint = showHint;
window.toggleDbReference = toggleDbReference;
window.downloadCertificate = downloadCertificate;