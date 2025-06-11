-- =============================================================
-- PostgreSQL Recovery Simulator Database Setup
-- Database: basdatbackup
-- =============================================================

-- Create database (run this separately as superuser)
-- CREATE DATABASE basdatbackup_local;

-- Connect to basdatbackup_local database
-- \c basdatbackup_local;

-- Note: In Docker, we're already connected to the correct database

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS transactions CASCADE;
DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS flash_sale_products CASCADE;
DROP TABLE IF EXISTS orders CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS backup_data CASCADE;
DROP TABLE IF EXISTS system_status CASCADE;

-- =============================================================
-- CREATE TABLES
-- =============================================================

-- Users table
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    product_id SERIAL PRIMARY KEY,
    product_name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    stock_quantity INTEGER NOT NULL DEFAULT 0,
    category_id INTEGER,
    seller_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Flash Sale Products table (WILL BE CORRUPTED IN SIMULATION)
CREATE TABLE flash_sale_products (
    flash_sale_id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(product_id),
    original_price DECIMAL(12,2) NOT NULL,
    flash_sale_price DECIMAL(12,2) NOT NULL,
    discount_percentage DECIMAL(5,2),
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    stock_limit INTEGER NOT NULL,
    sold_quantity INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table (WILL BE CORRUPTED IN SIMULATION)
CREATE TABLE orders (
    order_id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(user_id),
    order_status VARCHAR(20) DEFAULT 'pending',
    total_amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50),
    shipping_address TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order Items table
CREATE TABLE order_items (
    item_id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(order_id),
    product_id INTEGER REFERENCES products(product_id),
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL
);

-- Transactions table (WILL HAVE MISSING RECORDS IN SIMULATION)
CREATE TABLE transactions (
    transaction_id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(order_id),
    payment_status VARCHAR(20) DEFAULT 'pending',
    payment_amount DECIMAL(12,2) NOT NULL,
    payment_gateway VARCHAR(50),
    transaction_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    gateway_transaction_id VARCHAR(100)
);

-- Backup data table (FOR RECOVERY)
CREATE TABLE backup_data (
    table_name VARCHAR(100) PRIMARY KEY,
    data_json TEXT NOT NULL,
    backup_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT
);

-- System status table (FOR FINAL STEP)
CREATE TABLE system_status (
    status_id SERIAL PRIMARY KEY,
    status VARCHAR(20) DEFAULT 'CRITICAL',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT
);

-- =============================================================
-- INSERT INITIAL DATA (BEFORE CORRUPTION)
-- =============================================================

-- Insert Users
INSERT INTO users (username, email, password_hash, full_name, phone) VALUES
('john_doe', 'john@tokopintar.com', 'hashed_password_123', 'John Doe', '081234567890'),
('jane_smith', 'jane@tokopintar.com', 'hashed_password_456', 'Jane Smith', '081234567891'),
('bob_wilson', 'bob@tokopintar.com', 'hashed_password_789', 'Bob Wilson', '081234567892'),
('alice_brown', 'alice@tokopintar.com', 'hashed_password_abc', 'Alice Brown', '081234567893'),
('charlie_davis', 'charlie@tokopintar.com', 'hashed_password_def', 'Charlie Davis', '081234567894');

-- Insert Products
INSERT INTO products (product_name, description, price, stock_quantity, category_id, seller_id) VALUES
('iPhone 15 Pro Max', 'Latest iPhone with advanced camera and A17 Pro chip', 18999000.00, 100, 1, 1),
('Samsung Galaxy S24 Ultra', 'Premium Android smartphone with S Pen', 15999000.00, 150, 1, 2),
('MacBook Pro M3', 'Professional laptop for creators and developers', 29999000.00, 50, 2, 1),
('Sony WH-1000XM5', 'Industry-leading noise cancelling headphones', 4999000.00, 200, 3, 3),
('iPad Pro 12.9', 'Professional tablet with M2 chip', 16999000.00, 75, 2, 1),
('Dell XPS 13', 'Ultra-portable Windows laptop', 19999000.00, 80, 2, 4),
('AirPods Pro 2', 'Premium wireless earbuds with ANC', 3999000.00, 300, 3, 1),
('Nintendo Switch OLED', 'Popular gaming console with OLED display', 4599000.00, 120, 4, 5);

-- Insert Flash Sale Products (THESE WILL BE DELETED TO SIMULATE CORRUPTION)
INSERT INTO flash_sale_products (product_id, original_price, flash_sale_price, discount_percentage, start_time, end_time, stock_limit, sold_quantity) VALUES
(1, 18999000.00, 15999000.00, 15.79, '2024-12-12 00:00:00', '2024-12-12 23:59:59', 50, 23),
(2, 15999000.00, 12999000.00, 18.75, '2024-12-12 00:00:00', '2024-12-12 23:59:59', 75, 45),
(3, 29999000.00, 24999000.00, 16.67, '2024-12-12 06:00:00', '2024-12-12 18:00:00', 25, 12),
(4, 4999000.00, 3499000.00, 30.01, '2024-12-12 10:00:00', '2024-12-12 22:00:00', 100, 67);

-- Insert Orders (SOME WILL BE CORRUPTED)
INSERT INTO orders (user_id, order_status, total_amount, payment_method, shipping_address) VALUES
(1, 'completed', 15999000.00, 'credit_card', 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta'),
(2, 'processing', 12999000.00, 'bank_transfer', 'Jl. Thamrin No. 456, Jakarta Pusat, DKI Jakarta'),
(3, 'pending', 24999000.00, 'e_wallet', 'Jl. Gatot Subroto No. 789, Jakarta Selatan, DKI Jakarta'),
(4, 'completed', 7998000.00, 'credit_card', 'Jl. Kuningan No. 321, Jakarta Selatan, DKI Jakarta'),
(5, 'processing', 3499000.00, 'e_wallet', 'Jl. Kemang No. 654, Jakarta Selatan, DKI Jakarta');

-- Insert Order Items
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(1, 1, 1, 15999000.00, 15999000.00),
(2, 2, 1, 12999000.00, 12999000.00),
(3, 3, 1, 24999000.00, 24999000.00),
(4, 4, 2, 3999000.00, 7998000.00),
(5, 4, 1, 3499000.00, 3499000.00);

-- Insert Transactions (SOME WILL BE DELETED TO SIMULATE DATA LOSS)
INSERT INTO transactions (order_id, payment_status, payment_amount, payment_gateway, gateway_transaction_id) VALUES
(1, 'success', 15999000.00, 'midtrans', 'TXN-20241212-001'),
(2, 'success', 12999000.00, 'xendit', 'TXN-20241212-002'),
(3, 'pending', 24999000.00, 'gopay', 'TXN-20241212-003'),
(4, 'success', 7998000.00, 'midtrans', 'TXN-20241212-004'),
(5, 'processing', 3499000.00, 'ovo', 'TXN-20241212-005');

-- Insert System Status
INSERT INTO system_status (status, description) VALUES
('CRITICAL', 'Database corruption detected during Harbolnas 2024 event');

-- =============================================================
-- CREATE BACKUP DATA (FOR RECOVERY SIMULATION)
-- =============================================================

-- Store backup of flash_sale_products (THIS IS THE RECOVERY SOURCE)
INSERT INTO backup_data (table_name, data_json, description) VALUES
(
    'flash_sale_products_backup',
    '[
        {"flash_sale_id": 1, "product_id": 1, "original_price": 18999000.00, "flash_sale_price": 15999000.00, "discount_percentage": 15.79, "start_time": "2024-12-12 00:00:00", "end_time": "2024-12-12 23:59:59", "stock_limit": 50, "sold_quantity": 23},
        {"flash_sale_id": 2, "product_id": 2, "original_price": 15999000.00, "flash_sale_price": 12999000.00, "discount_percentage": 18.75, "start_time": "2024-12-12 00:00:00", "end_time": "2024-12-12 23:59:59", "stock_limit": 75, "sold_quantity": 45},
        {"flash_sale_id": 3, "product_id": 3, "original_price": 29999000.00, "flash_sale_price": 24999000.00, "discount_percentage": 16.67, "start_time": "2024-12-12 06:00:00", "end_time": "2024-12-12 18:00:00", "stock_limit": 25, "sold_quantity": 12},
        {"flash_sale_id": 4, "product_id": 4, "original_price": 4999000.00, "flash_sale_price": 3499000.00, "discount_percentage": 30.01, "start_time": "2024-12-12 10:00:00", "end_time": "2024-12-12 22:00:00", "stock_limit": 100, "sold_quantity": 67}
    ]',
    'Backup of flash sale products before Harbolnas corruption incident'
);

-- Store backup of clean orders data
INSERT INTO backup_data (table_name, data_json, description) VALUES
(
    'orders_backup',
    '[
        {"order_id": 1, "user_id": 1, "order_status": "completed", "total_amount": 15999000.00, "payment_method": "credit_card"},
        {"order_id": 2, "user_id": 2, "order_status": "processing", "total_amount": 12999000.00, "payment_method": "bank_transfer"},
        {"order_id": 3, "user_id": 3, "order_status": "pending", "total_amount": 24999000.00, "payment_method": "e_wallet"}
    ]',
    'Backup of clean orders before corruption'
);

-- =============================================================
-- SIMULATE THE INCIDENT (CORRUPTION)
-- =============================================================

-- 1. DELETE all flash_sale_products (TOTAL DATA LOSS)
DELETE FROM flash_sale_products;

-- 2. CORRUPT some orders data (SIMULATE DISK CORRUPTION)
UPDATE orders 
SET order_status = 'CORRUPTED_DATA_###' || order_id 
WHERE order_id IN (1, 3);

-- 3. DELETE some transactions (SIMULATE POWER OUTAGE DATA LOSS)
DELETE FROM transactions 
WHERE transaction_id IN (2, 3, 5);

-- =============================================================
-- CREATE VIEWS FOR EASY MONITORING
-- =============================================================

-- View for damage assessment
CREATE VIEW damage_assessment AS
SELECT 
    'flash_sale_products' AS table_name,
    COUNT(*) AS current_records,
    '4' AS expected_records,
    CASE WHEN COUNT(*) = 0 THEN 'CRITICAL - TOTAL DATA LOSS' ELSE 'OK' END AS status
FROM flash_sale_products
UNION ALL
SELECT 
    'orders' AS table_name,
    COUNT(*) AS current_records,
    '5' AS expected_records,
    CASE 
        WHEN EXISTS(SELECT 1 FROM orders WHERE order_status LIKE 'CORRUPTED%') 
        THEN 'CORRUPTED - SOME RECORDS DAMAGED' 
        ELSE 'OK' 
    END AS status
FROM orders
UNION ALL
SELECT 
    'transactions' AS table_name,
    COUNT(*) AS current_records,
    '5' AS expected_records,
    CASE WHEN COUNT(*) < 5 THEN 'MISSING RECORDS' ELSE 'OK' END AS status
FROM transactions;

-- View for recovery verification
CREATE VIEW recovery_status AS
SELECT 
    fs.flash_sale_id,
    p.product_name,
    fs.original_price,
    fs.flash_sale_price,
    fs.sold_quantity,
    'RESTORED' AS status
FROM flash_sale_products fs
JOIN products p ON fs.product_id = p.product_id
ORDER BY fs.flash_sale_id;

-- =============================================================
-- GRANT PERMISSIONS (ADJUST AS NEEDED)
-- =============================================================

-- Grant SELECT permissions to application user
-- GRANT SELECT ON ALL TABLES IN SCHEMA public TO your_app_user;
-- GRANT USAGE ON ALL SEQUENCES IN SCHEMA public TO your_app_user;

-- =============================================================
-- VERIFICATION QUERIES
-- =============================================================

-- Check the damage
SELECT * FROM damage_assessment;

-- Check available backups
SELECT table_name, description, backup_time FROM backup_data;

-- Check system status
SELECT * FROM system_status;

-- Show table sizes
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables 
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

COMMIT;