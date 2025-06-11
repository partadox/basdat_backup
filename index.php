<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PostgreSQL Recovery Simulator - Harbolnas Crisis</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="container">
        <!-- Welcome Screen -->
        <div class="welcome-screen" id="welcomeScreen">
            <div class="card">
                <h1 class="title">🎮 PostgreSQL Recovery Simulator</h1>
                <h2 class="subtitle">Harbolnas Crisis Management</h2>
                
                <div class="alert alert-warning">
                    <strong>⚠️ EMERGENCY SCENARIO</strong><br>
                    Anda adalah Database Administrator di TokoPintar. Hari ini adalah Harbolnas 2024, 
                    event shopping terbesar tahun ini. Tiba-tiba sistem database mengalami corruption 
                    saat traffic mencapai puncaknya!
                </div>
                
                <div style="text-align: left; margin: 30px 0;">
                    <h3>📋 Mission Objectives:</h3>
                    <ul style="margin: 15px 0 15px 30px;">
                        <li>✅ Assess database damage dalam waktu minimal</li>
                        <li>🔧 Execute recovery procedures dengan benar</li>
                        <li>📊 Restore critical business data</li>
                        <li>⚡ Minimize downtime (Target: < 30 menit)</li>
                        <li>💰 Prevent revenue loss (Rp 2.5M/jam)</li>
                    </ul>
                    
                    <h3>🎯 Scoring System:</h3>
                    <ul style="margin: 15px 0 15px 30px;">
                        <li><strong>Speed Bonus:</strong> Faster recovery = Higher score</li>
                        <li><strong>Accuracy Bonus:</strong> Correct queries without errors</li>
                        <li><strong>Data Integrity:</strong> Complete data restoration</li>
                    </ul>
                    
                    <h3>📊 Database Structure Reference:</h3>
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin: 15px 0; text-align: left; font-family: monospace; font-size: 12px;">
                        <strong>🗂️ TokoPintar Database Tables:</strong><br><br>
                        
                        <div style="background: #ffebee; padding: 10px; border-radius: 5px; margin: 5px 0;">
                            <strong>flash_sale_products</strong> ⚠️ <span style="color: red;">CRITICAL - EMPTY!</span><br>
                            • Columns: flash_sale_id, product_id, original_price, flash_sale_price, sold_quantity<br>
                            • Expected: 4 products (iPhone 15, Galaxy S24, MacBook Pro, Sony Headphones)<br>
                            • Current: 0 records - TOTAL DATA LOSS!
                        </div>
                        
                        <div style="background: #fff3e0; padding: 10px; border-radius: 5px; margin: 5px 0;">
                            <strong>orders</strong> ⚠️ <span style="color: orange;">CORRUPTED</span><br>
                            • Columns: order_id, user_id, order_status, total_amount<br>
                            • Expected: order_status = 'completed', 'processing', 'pending'<br>
                            • Current: order_status = 'CORRUPTED_DATA_###' (some records)
                        </div>
                        
                        <div style="background: #fff3e0; padding: 10px; border-radius: 5px; margin: 5px 0;">
                            <strong>transactions</strong> ⚠️ <span style="color: orange;">MISSING RECORDS</span><br>
                            • Columns: transaction_id, order_id, payment_status, payment_amount<br>
                            • Expected: 3 records<br>
                            • Current: 1 record - 2 transactions lost!
                        </div>
                        
                        <div style="background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 5px 0;">
                            <strong>backup_data</strong> ✅ <span style="color: green;">AVAILABLE</span><br>
                            • Columns: table_name, data_json, backup_time<br>
                            • Contains: flash_sale_products_backup<br>
                            • Status: Ready for restoration
                        </div>
                        
                        <div style="background: #f0f8ff; padding: 10px; border-radius: 5px; margin: 5px 0;">
                            <strong>users, products</strong> ✅ <span style="color: green;">INTACT</span><br>
                            • These tables are not affected by the incident<br>
                        </div>
                        
                        <br><strong>💡 Sample Queries:</strong><br>
                        • SELECT COUNT(*) FROM flash_sale_products;<br>
                        • SELECT * FROM backup_data;<br>
                        • RESTORE flash_sale_products;<br>
                        • SELECT * FROM flash_sale_products LIMIT 3;
                    </div>
                </div>
                
                <button class="btn" onclick="startSimulation()">
                    🚀 START SIMULATION
                </button>
            </div>
        </div>
        
        <!-- Game Screen -->
        <div class="game-screen" id="gameScreen">
            <div class="timer" id="timer">⏱️ 00:00</div>
            
            <!-- Status Dashboard -->
            <div class="card">
                <h2>📊 System Status Dashboard</h2>
                <div class="status-panel">
                    <div class="status-card">
                        <div>Concurrent Users</div>
                        <div class="status-value" id="concurrentUsers">0</div>
                        <div>👥 Active Sessions</div>
                    </div>
                    <div class="status-card">
                        <div>Revenue Loss</div>
                        <div class="status-value" id="revenueLoss">Rp 0</div>
                        <div>💸 Per Hour</div>
                    </div>
                    <div class="status-card">
                        <div>Database Status</div>
                        <div class="status-value" id="dbStatus">CRITICAL</div>
                        <div>🔴 System Health</div>
                    </div>
                    <div class="status-card">
                        <div>Progress</div>
                        <div class="status-value" id="progressPercent">0%</div>
                        <div>✅ Recovery</div>
                    </div>
                </div>
            </div>
            
            <!-- Recovery Roadmap -->
            <div class="card">
                <h2>🗺️ Recovery Roadmap</h2>
                <div class="roadmap" id="roadmap">
                    <div class="roadmap-item active" data-step="1">
                        <div class="roadmap-checkbox">1</div>
                        <div>
                            <strong>Damage Assessment</strong><br>
                            <small>Analyze corrupted tables and estimate data loss</small>
                        </div>
                    </div>
                    <div class="roadmap-item" data-step="2">
                        <div class="roadmap-checkbox">2</div>
                        <div>
                            <strong>Create Recovery Database</strong><br>
                            <small>Setup temporary database for restoration</small>
                        </div>
                    </div>
                    <div class="roadmap-item" data-step="3">
                        <div class="roadmap-checkbox">3</div>
                        <div>
                            <strong>Restore Critical Data</strong><br>
                            <small>Import backup data to recovery database</small>
                        </div>
                    </div>
                    <div class="roadmap-item" data-step="4">
                        <div class="roadmap-checkbox">4</div>
                        <div>
                            <strong>Data Validation</strong><br>
                            <small>Verify data integrity and business logic</small>
                        </div>
                    </div>
                    <div class="roadmap-item" data-step="5">
                        <div class="roadmap-checkbox">5</div>
                        <div>
                            <strong>System Recovery</strong><br>
                            <small>Switch to recovered database and resume operations</small>
                        </div>
                    </div>
                </div>
                
                <div class="progress-bar">
                    <div class="progress-fill" id="progressBar" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Current Task -->
            <div class="card">
                <h2 id="currentTaskTitle">🔍 Step 1: Damage Assessment</h2>
                <div id="currentTaskDescription">
                    <div class="hint-panel">
                        <div class="hint-title">💡 Hint:</div>
                        <div id="currentHint">
                            Start by checking which tables are affected. Use queries to count records in critical tables like flash_sale_products, orders, and transactions.
                        </div>
                    </div>
                    
                    <div class="hint-panel" style="background: #f0f8ff; border-color: #4CAF50; display: none;" id="dbReference">
                        <div class="hint-title">📋 Database Quick Reference:</div>
                        <div style="font-family: monospace; font-size: 11px;">
                            <strong>Critical Tables Status:</strong><br><br>
                            
                            <span style="color: red;">● flash_sale_products</span> (EMPTY - 0/4 records)<br>
                              Expected: iPhone(23 sold), Galaxy(45 sold), MacBook(12 sold), Sony(67 sold)<br><br>
                            
                            <span style="color: orange;">● orders</span> (CORRUPTED - some records damaged)<br>
                              Status values changed to 'CORRUPTED_DATA_###'<br><br>
                            
                            <span style="color: orange;">● transactions</span> (MISSING - 1/3 records)<br>
                              Lost: transaction_id 2 and 3<br><br>
                            
                            <span style="color: green;">● backup_data</span> (AVAILABLE for restore)<br>
                              Table: flash_sale_products_backup<br><br>
                            
                            <strong>Database ERD:</strong><br>
                            <div style="background: #f9f9f9; padding: 8px; border-radius: 4px; margin: 5px 0; font-size: 10px;">
                                ┌─────────────────┐    ┌─────────────────────┐<br>
                                │     USERS      │    │     PRODUCTS     │<br>
                                ├─────────────────┤    ├─────────────────────┤<br>
                                │ user_id (PK)    │    │ product_id (PK)   │<br>
                                │ username       │    │ product_name    │<br>
                                │ email         │    │ price          │<br>
                                │ full_name      │    │ stock_quantity  │<br>
                                └─────────────────┘    └─────────────────────┘<br>
                                        │                                │<br>
                                        │                                │<br>
                                ┌─────────────────┐    ┌────────────────────────┐<br>
                                │     ORDERS     │    │ FLASH_SALE_PRODUCTS │<br>
                                ├─────────────────┤    ├────────────────────────┤<br>
                                │ order_id (PK)   │    │ flash_sale_id (PK) │<br>
                                │ user_id (FK)    │    │ product_id (FK)   │<br>
                                │ order_status   │    │ original_price   │<br>
                                │ total_amount   │    │ flash_sale_price │<br>
                                └─────────────────┘    │ sold_quantity   │<br>
                                        │                         └────────────────────────┘<br>
                                        │<br>
                                ┌────────────────────┐<br>
                                │  TRANSACTIONS  │<br>
                                ├────────────────────┤<br>
                                │ transaction_id (PK)│<br>
                                │ order_id (FK)    │<br>
                                │ payment_status  │<br>
                                │ payment_amount  │<br>
                                └────────────────────┘<br>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Query Interface -->
                <div class="query-panel">
                    <h3>💻 SQL Query Interface</h3>
                    <textarea 
                        class="query-input" 
                        id="queryInput" 
                        placeholder="Enter your SQL query here...&#10;Examples:&#10;SELECT COUNT(*) FROM flash_sale_products;&#10;SELECT * FROM backup_data;&#10;RESTORE flash_sale_products;&#10;SELECT * FROM flash_sale_products LIMIT 3;"
                    ></textarea>
                    <div style="margin: 10px 0;">
                        <button class="btn btn-success" onclick="executeQuery()">
                            ▶️ Execute Query
                        </button>
                        <button class="btn" onclick="showHint()">
                            💡 Show Hint
                        </button>
                        <button class="btn" onclick="toggleDbReference()" style="background: #17a2b8; font-size: 0.9em;">
                            📋 Toggle DB Reference
                        </button>
                    </div>
                    <div class="query-result" id="queryResult">
                        Ready to execute queries. Database status: CORRUPTED
                    </div>
                </div>
            </div>
            
            <!-- Results Summary -->
            <div class="card" id="resultsCard" style="display: none;">
                <h2>🏆 Mission Results</h2>
                <div id="finalResults"></div>
            </div>
        </div>
    </div>

    <script src="index.js"></script>
</body>
</html>