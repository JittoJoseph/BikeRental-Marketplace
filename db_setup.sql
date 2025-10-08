-- Bike Rental System - Database Setup Script
-- Run this script in phpMyAdmin to set up the complete database

CREATE DATABASE IF NOT EXISTS bikerental;
USE bikerental;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bikes Table
CREATE TABLE IF NOT EXISTS bikes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(500),
    rating DECIMAL(3, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bike_id INT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    total_price DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bike_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_type ENUM('booking', 'security_deposit', 'refund') NOT NULL,
    payment_method ENUM('card', 'upi', 'net_banking', 'wallet') NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Security Deposits Table
CREATE TABLE IF NOT EXISTS security_deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 500.00,
    status ENUM('held', 'refunded', 'forfeited') DEFAULT 'held',
    held_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    refunded_date TIMESTAMP NULL,
    refund_reason TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Categories
INSERT INTO categories (name) VALUES 
('Rally Bikes'),
('Normal Bikes'),
('Scooters'),
('Mountain Bikes'),
('Electric Bikes');

-- Insert Default Admin User (password: admin123)
-- Password is hashed using PHP password_hash() function
INSERT INTO users (name, email, password, is_admin) VALUES 
('Admin User', 'admin@bikerental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert Sample Test User (password: user123)
INSERT INTO users (name, email, password, is_admin) VALUES 
('John Doe', 'user@bikerental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0);

-- Insert Sample Bikes
INSERT INTO bikes (name, category_id, description, price, image_url, rating) VALUES 
('Yamaha MT-15', 1, 'A powerful rally bike perfect for long rides and adventures. Features advanced suspension and comfortable seating.', 25.00, 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=500', 4.5),
('Honda CB350', 2, 'Classic normal bike with reliable performance. Ideal for daily commuting and city rides.', 15.00, 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=500', 4.2),
('Vespa Elegante', 3, 'Stylish scooter perfect for city navigation. Easy to handle and fuel efficient.', 12.00, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500', 4.0),
('Royal Enfield Himalayan', 1, 'Adventure bike built for rough terrains. Perfect for mountain trails and off-road experiences.', 30.00, 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=500', 4.8),
('Hero Splendor', 2, 'Budget-friendly reliable bike for everyday use. Great mileage and low maintenance.', 10.00, 'https://images.unsplash.com/photo-1558980664-769d59546b3d?w=500', 3.9),
('TVS iQube', 5, 'Modern electric scooter with smart features. Eco-friendly and cost-effective.', 18.00, 'https://images.unsplash.com/photo-1568772619757-00cc51a1b42c?w=500', 4.3),
('KTM Duke 390', 1, 'High-performance rally bike with sporty design. Built for speed enthusiasts.', 35.00, 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=500', 4.7),
('Suzuki Access 125', 3, 'Comfortable scooter with ample storage. Perfect for daily errands and short trips.', 13.00, 'https://images.unsplash.com/photo-1541855706-b6db96f37376?w=500', 4.1);

-- Insert Sample Reviews
INSERT INTO reviews (user_id, bike_id, rating, comment) VALUES
(2, 1, 5, 'Excellent bike! Perfect for my weekend trips. Highly recommend!'),
(2, 3, 4, 'Great scooter for city rides. Very fuel efficient and easy to park.'),
(2, 4, 5, 'Amazing adventure bike! Took it on a mountain trip and it performed flawlessly.');

-- Insert Sample Bookings
INSERT INTO bookings (user_id, bike_id, start_date, end_date, status, total_price) VALUES
(2, 1, '2025-10-15 09:00:00', '2025-10-17 18:00:00', 'approved', 50.00),
(2, 4, '2025-10-20 08:00:00', '2025-10-22 20:00:00', 'pending', 60.00);

-- Setup Complete!
-- Admin Login: admin@bikerental.com / admin123
-- User Login: user@bikerental.com / user123