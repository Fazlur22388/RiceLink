-- Create database if not exists
CREATE DATABASE IF NOT EXISTS ricelink;

-- Use database
USE ricelink;

-- Create farmers table
CREATE TABLE IF NOT EXISTS farmers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    whatsapp VARCHAR(20),
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create buyers table
CREATE TABLE IF NOT EXISTS buyers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    company_name VARCHAR(255),
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    buyer_type ENUM('trader', 'mill', 'retailer', 'other') DEFAULT 'other',
    profile_image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create listings table
CREATE TABLE IF NOT EXISTS listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    rice_type VARCHAR(100) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL, -- in kg
    price_per_kg DECIMAL(10,2) NOT NULL,
    harvest_date DATE,
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE
);

-- Create bookmarks table
CREATE TABLE IF NOT EXISTS bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    listing_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    UNIQUE KEY buyer_listing (buyer_id, listing_id)
);

-- Create messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    farmer_id INT NOT NULL,
    listing_id INT,
    message TEXT NOT NULL,
    sent_by ENUM('farmer', 'buyer') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE SET NULL
);

-- Create admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert demo data

-- Demo farmers
INSERT INTO farmers (name, age, address, phone, whatsapp, email, password, created_at) VALUES
('Anura Perera', 45, 'Polonnaruwa', '0771234567', '0771234567', 'anura@example.com', 'farmer123', '2023-05-15 10:30:00'), -- password: farmer123
('Kamal Silva', 52, 'Anuradhapura', '0772345678', '0772345678', 'kamal@example.com', 'farmer123', '2023-05-16 12:45:00'), -- password: farmer123
('Nimal Jayasuriya', 38, 'Kurunegala', '0773456789', '0773456789', 'nimal@example.com', 'farmer123', '2023-05-18 09:15:00'), -- password: farmer123
('Lakshman Peris', 60, 'Ampara', '0774567890', '0774567890', 'lakshman@example.com', 'farmer123', '2023-05-20 11:20:00'); -- password: farmer123

-- Demo buyers
INSERT INTO buyers (name, company_name, address, phone, email, password, buyer_type, created_at) VALUES
('Sunil Fernando', 'Lanka Rice Mills', 'Colombo', '0761234567', 'sunil@example.com', 'buyer123', 'mill', '2023-05-22 14:20:00'), -- password: buyer123
('Priyantha Gunawardena', 'Priyantha Traders', 'Kandy', '0762345678', 'priyantha@example.com', 'buyer123', 'trader', '2023-05-24 16:30:00'), -- password: buyer123
('Ranee Jayasinghe', 'Golden Rice Suppliers', 'Galle', '0763456789', 'ranee@example.com', 'buyer123', 'retailer', '2023-05-26 10:45:00'); -- password: buyer123

-- Admin user
INSERT INTO admins (name, email, password, created_at) VALUES
('Admin User', 'admin@ricelink.com', 'admin123', '2023-05-01 09:00:00'); -- password: admin123

-- Demo listings
INSERT INTO listings (farmer_id, title, description, rice_type, quantity, price_per_kg, harvest_date, is_available, created_at) VALUES
(1, 'Premium Samba Rice', 'Freshly harvested premium quality Samba rice from Polonnaruwa. Excellent taste and aroma.', 'Samba', 1000.00, 210.00, '2023-05-01', TRUE, '2023-05-15 15:30:00'),
(1, 'Red Nadu Rice', 'Organically grown Red Nadu rice. No chemicals used.', 'Red Nadu', 750.00, 195.00, '2023-05-05', TRUE, '2023-05-16 11:20:00'),
(2, 'White Raw Rice', 'Good quality white raw rice suitable for everyday cooking.', 'White Raw', 1200.00, 180.00, '2023-05-10', TRUE, '2023-05-16 14:45:00'),
(2, 'Keeri Samba Rice', 'Premium Keeri Samba rice with excellent texture.', 'Keeri Samba', 800.00, 240.00, '2023-05-07', TRUE, '2023-05-17 09:30:00'),
(3, 'Basmati Rice', 'High-quality fragrant Basmati rice.', 'Basmati', 600.00, 260.00, '2023-05-12', TRUE, '2023-05-18 13:15:00'),
(3, 'Red Samba Rice', 'Nutritious red samba rice suitable for health-conscious consumers.', 'Red Samba', 500.00, 220.00, '2023-05-08', TRUE, '2023-05-19 10:40:00'),
(4, 'White Samba Rice', 'Classic white samba rice from Ampara region.', 'White Samba', 1500.00, 200.00, '2023-05-14', TRUE, '2023-05-20 12:50:00'),
(4, 'Brown Rice', 'Nutritious brown rice with full bran layer.', 'Brown Rice', 700.00, 230.00, '2023-05-09', TRUE, '2023-05-21 11:30:00');

-- Demo bookmarks
INSERT INTO bookmarks (buyer_id, listing_id, created_at) VALUES
(1, 2, '2023-05-23 09:45:00'),
(1, 5, '2023-05-23 10:15:00'),
(2, 1, '2023-05-25 14:20:00'),
(2, 4, '2023-05-25 14:30:00'),
(3, 3, '2023-05-27 11:10:00'),
(3, 7, '2023-05-27 11:25:00');

-- Demo messages
INSERT INTO messages (buyer_id, farmer_id, listing_id, message, sent_by, is_read, created_at) VALUES
(1, 1, 1, 'Hello, I am interested in your Premium Samba Rice. Is it available for bulk purchase?', 'buyer', TRUE, '2023-05-23 13:30:00'),
(1, 1, 1, 'Yes, it is available. How much quantity do you need?', 'farmer', TRUE, '2023-05-23 14:15:00'),
(1, 1, 1, 'I need about 500kg. Can you offer a discounted price for bulk purchase?', 'buyer', TRUE, '2023-05-23 15:00:00'),
(1, 1, 1, 'I can offer Rs. 200 per kg for 500kg. Does that work for you?', 'farmer', FALSE, '2023-05-23 16:30:00'),

(2, 2, 4, 'Is your Keeri Samba rice parboiled?', 'buyer', TRUE, '2023-05-25 10:20:00'),
(2, 2, 4, 'No, it is not parboiled. It is raw rice.', 'farmer', TRUE, '2023-05-25 11:45:00'),
(2, 2, 4, 'Great, can you deliver to Kandy?', 'buyer', TRUE, '2023-05-25 13:10:00'),
(2, 2, 4, 'Yes, delivery to Kandy is possible but there will be an additional charge.', 'farmer', FALSE, '2023-05-25 14:30:00'),

(3, 3, 5, 'I am interested in your Basmati rice. Is it locally grown?', 'buyer', TRUE, '2023-05-27 09:30:00'),
(3, 3, 5, 'Yes, it is locally grown in Kurunegala region using traditional methods.', 'farmer', FALSE, '2023-05-27 10:45:00'); 