-- SQLite compatible schema

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT CHECK(role IN ('admin', 'customer')) DEFAULT 'customer',
    cibil_status TEXT CHECK(cibil_status IN ('good', 'low')) DEFAULT 'good',
    referrer_id INTEGER DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT CHECK(category IN ('solar_panel', 'battery')) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS product_properties (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    property_name TEXT NOT NULL,
    property_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    cibil_status_at_purchase TEXT CHECK(cibil_status_at_purchase IN ('good', 'low')) NOT NULL,
    order_status TEXT CHECK(order_status IN ('pending', 'completed', 'cancelled')) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    type TEXT CHECK(type IN ('subsidy_payout', 'referral_commission', 'company_revenue', 'third_party_subsidy')) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS level_income (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    source_order_id INTEGER NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    level INTEGER NOT NULL,
    status TEXT CHECK(status IN ('pending', 'credited')) DEFAULT 'credited',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (source_order_id) REFERENCES orders(id)
);

-- Table for existing Burfee members
CREATE TABLE IF NOT EXISTS customer (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    MemberId TEXT NOT NULL UNIQUE,
    MemberPass TEXT NOT NULL,
    name TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed some dummy data
INSERT INTO customer (MemberId, MemberPass, name) VALUES ('BC1001', 'password123', 'John Burfee');
