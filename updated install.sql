-- install.sql (full updated)

DROP DATABASE IF EXISTS gps_attendance_bd;
CREATE DATABASE IF NOT EXISTS gps_attendance_bd
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gps_attendance_bd;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','employee') NOT NULL DEFAULT 'employee',
    team VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    designation VARCHAR(100) DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    check_type ENUM('IN','OUT') NOT NULL,
    latitude VARCHAR(50) NOT NULL,
    longitude VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default admin (password: admin123)
INSERT INTO users (name, email, password_hash, role, team, phone, designation, status) VALUES
('System Admin', 'admin@example.com',
'$2y$10$QlmMe7CPgsT.MH8cuGL1qOumj5WxPcvEJgBkTItoiPYqeo1Q3I8We',
'admin', 'Administration', '+8801700000000', 'System Administrator', 'active');

-- Dummy employees (password: emp12345 for all)
INSERT INTO users (name, email, password_hash, role, team, phone, designation, status) VALUES
('John Doe', 'john.doe@example.com',
'$2y$10$qbw7gqvL.l7mxioi4xumQOWKf7xXoeirhZy8aCPYWcwZ2p30bqgCC',
'employee', 'Development', '+8801711000001', 'Software Engineer', 'active'),
('Sarah Johnson', 'sarah.johnson@example.com',
'$2y$10$qbw7gqvL.l7mxioi4xumQOWKf7xXoeirhZy8aCPYWcwZ2p30bqgCC',
'employee', 'Marketing', '+8801711000002', 'Marketing Specialist', 'active'),
('Michael Chen', 'michael.chen@example.com',
'$2y$10$qbw7gqvL.l7mxioi4xumQOWKf7xXoeirhZy8aCPYWcwZ2p30bqgCC',
'employee', 'Sales', '+8801711000003', 'Sales Executive', 'active'),
('David Wilson', 'david.wilson@example.com',
'$2y$10$qbw7gqvL.l7mxioi4xumQOWKf7xXoeirhZy8aCPYWcwZ2p30bqgCC',
'employee', 'Support', '+8801711000004', 'Support Engineer', 'active');
