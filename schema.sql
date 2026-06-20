-- 📦 SolarShop Database Schema
-- Optimized for MySQL 8.x

-- 1. Users Table (Main System)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    cibil_status ENUM('good', 'low') DEFAULT 'good',
    referrer_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 2. Legacy Burfee Customers (To be migrated upon first login)
CREATE TABLE IF NOT EXISTS customer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    MemberId VARCHAR(100) NOT NULL UNIQUE,
    MemberPass VARCHAR(255) NOT NULL,
    FullName VARCHAR(255),
    CibilStatus ENUM('good', 'low') DEFAULT 'good'
);

-- 3. Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category ENUM('solar_panel', 'battery') NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Product Properties (Custom Attributes)
CREATE TABLE IF NOT EXISTS product_properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    property_name VARCHAR(100) NOT NULL,
    property_value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 5. Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    total_price DECIMAL(15, 2) NOT NULL,
    cibil_status_at_purchase ENUM('good', 'low') NOT NULL,
    order_status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- 6. Transactions (Subsidy & Commission Ledger)
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    type ENUM('subsidy_payout', 'referral_commission', 'company_revenue', 'third_party_subsidy') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 7. Level Income (Upline Network Earnings)
CREATE TABLE IF NOT EXISTS level_income (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    source_order_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    level INT NOT NULL,
    status ENUM('pending', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (source_order_id) REFERENCES orders(id)
);

-- Seed Initial Data
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT IGNORE INTO products (name, category, price, stock) VALUES
('EcoSolar 500W', 'solar_panel', 25000.00, 10),
('VoltMax 150Ah', 'battery', 15000.00, 15);

INSERT IGNORE INTO product_properties (product_id, property_name, property_value) VALUES
(1, 'Wattage', '500W'),
(1, 'Efficiency', '21.5%'),
(2, 'Capacity', '150Ah'),
(2, 'Voltage', '12V');
