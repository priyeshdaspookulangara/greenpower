-- 📦 SolarShop Database Schema (SQLite Version)

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'customer', -- 'admin', 'customer'
    cibil_status TEXT DEFAULT 'good', -- 'good', 'low'
    referrer_id INTEGER NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE customer (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    MemberId TEXT NOT NULL UNIQUE,
    MemberPass TEXT NOT NULL,
    FullName TEXT,
    CibilStatus TEXT DEFAULT 'good'
);

CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT NOT NULL, -- 'solar_panel', 'battery'
    price REAL NOT NULL,
    stock INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE product_properties (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    property_name TEXT NOT NULL,
    property_value TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    total_price REAL NOT NULL,
    cibil_status_at_purchase TEXT NOT NULL,
    order_status TEXT DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    amount REAL NOT NULL,
    type TEXT NOT NULL, -- 'subsidy_payout', 'referral_commission', 'company_revenue', 'third_party_subsidy'
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE level_income (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    source_order_id INTEGER NOT NULL,
    amount REAL NOT NULL,
    level INTEGER NOT NULL,
    status TEXT DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (source_order_id) REFERENCES orders(id)
);

-- Seed Initial Data
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO products (name, category, price, stock) VALUES
('EcoSolar 500W', 'solar_panel', 25000.00, 10),
('VoltMax 150Ah', 'battery', 15000.00, 15);

INSERT INTO product_properties (product_id, property_name, property_value) VALUES
(1, 'Wattage', '500W'),
(1, 'Efficiency', '21.5%'),
(2, 'Capacity', '150Ah'),
(2, 'Voltage', '12V');
