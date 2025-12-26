-- Robot Barista POS Database Schema
CREATE DATABASE IF NOT EXISTS robot_barista_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE robot_barista_pos;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_order (display_order)
) ENGINE=InnoDB;

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    price_usd DECIMAL(10,2) NOT NULL,
    price_khr DECIMAL(10,2) NOT NULL,
    is_available TINYINT(1) DEFAULT 1,
    has_modifiers TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_available (is_available)
) ENGINE=InnoDB;

-- Modifiers table (sizes, toppings, sugar levels)
CREATE TABLE modifiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('size', 'topping', 'sugar', 'ice') NOT NULL,
    price_usd DECIMAL(10,2) DEFAULT 0,
    price_khr DECIMAL(10,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Product modifiers relationship
CREATE TABLE product_modifiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    modifier_id INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (modifier_id) REFERENCES modifiers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_modifier (product_id, modifier_id)
) ENGINE=InnoDB;

-- Customers table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) DEFAULT 'Walk-In Customer',
    phone VARCHAR(20),
    email VARCHAR(100),
    total_orders INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    customer_name VARCHAR(200) DEFAULT 'Walk-In Customer',
    currency ENUM('USD', 'KHR') DEFAULT 'USD',
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('KHQR', 'Cash') NOT NULL,
    payment_status ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
    order_status ENUM('Pending', 'Preparing', 'Ready', 'Completed', 'Cancelled') DEFAULT 'Pending',
    receipt_printed TINYINT(1) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    modifiers_json TEXT,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Users table (admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(200),
    role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Print logs table
CREATE TABLE print_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    print_type ENUM('receipt', 'report') NOT NULL,
    print_status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    printed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('exchange_rate_usd_to_khr', '4100'),
('tax_percent', '10'),
('khqr_merchant_id', 'MERCHANT123'),
('khqr_bank_account', '000123456789'),
('khqr_merchant_name', 'Robot Barista Cafe'),
('printer_enabled', '1'),
('printer_ip', '192.168.1.100'),
('printer_port', '9100'),
('printer_type', 'network'),
('printer_paper_width', '80'),
('business_name', 'Robot Barista Cafe'),
('business_address', 'Phnom Penh, Cambodia'),
('business_phone', '+855 12 345 678');

-- Insert sample categories
INSERT INTO categories (name, description, display_order) VALUES
('Coffee', 'Hot and cold coffee beverages', 1),
('Tea', 'Various tea selections', 2),
('Drinks', 'Refreshing beverages', 3),
('Bakery', 'Fresh baked goods', 4),
('Snacks', 'Light snacks and treats', 5);

-- Insert sample modifiers
INSERT INTO modifiers (name, type, price_usd, price_khr) VALUES
('Small', 'size', 0, 0),
('Medium', 'size', 0.50, 2050),
('Large', 'size', 1.00, 4100),
('No Sugar', 'sugar', 0, 0),
('Less Sugar', 'sugar', 0, 0),
('Normal Sugar', 'sugar', 0, 0),
('Extra Sugar', 'sugar', 0, 0),
('No Ice', 'ice', 0, 0),
('Less Ice', 'ice', 0, 0),
('Normal Ice', 'ice', 0, 0),
('Pearl', 'topping', 0.50, 2050),
('Jelly', 'topping', 0.50, 2050),
('Cream', 'topping', 0.75, 3075);

-- Insert sample products
INSERT INTO products (category_id, name, description, price_usd, price_khr, image) VALUES
(1, 'Espresso', 'Strong and bold coffee', 2.50, 10250, 'espresso.jpg'),
(1, 'Cappuccino', 'Espresso with steamed milk foam', 3.50, 14350, 'cappuccino.jpg'),
(1, 'Latte', 'Smooth espresso with milk', 3.75, 15375, 'latte.jpg'),
(1, 'Americano', 'Espresso with hot water', 2.75, 11275, 'americano.jpg'),
(2, 'Green Tea', 'Fresh green tea', 2.00, 8200, 'green-tea.jpg'),
(2, 'Milk Tea', 'Classic milk tea', 3.00, 12300, 'milk-tea.jpg'),
(3, 'Orange Juice', 'Freshly squeezed', 3.50, 14350, 'orange-juice.jpg'),
(3, 'Smoothie', 'Mixed fruit smoothie', 4.00, 16400, 'smoothie.jpg'),
(4, 'Croissant', 'Buttery croissant', 2.50, 10250, 'croissant.jpg'),
(4, 'Muffin', 'Blueberry muffin', 2.25, 9225, 'muffin.jpg'),
(5, 'Cookie', 'Chocolate chip cookie', 1.50, 6150, 'cookie.jpg'),
(5, 'Brownie', 'Fudge brownie', 2.00, 8200, 'brownie.jpg');
