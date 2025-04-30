CREATE DATABASE IF NOT EXISTS eseal;
USE eseal;

-- Modified containers table with full ETA support
CREATE TABLE IF NOT EXISTS containers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    container_id VARCHAR(50) NOT NULL,
    status ENUM('Sealed', 'Tampered') DEFAULT 'Sealed',
    location VARCHAR(100),
    pin VARCHAR(10),
    origin VARCHAR(100),
    destination VARCHAR(100),
    estimated_arrival DATETIME DEFAULT NULL,
    latitude DECIMAL(10, 7) DEFAULT NULL,
    longitude DECIMAL(10, 7) DEFAULT NULL,
    latitude_origin DECIMAL(10, 7) DEFAULT NULL,
    longitude_origin DECIMAL(10, 7) DEFAULT NULL,
    latitude_dest DECIMAL(10, 7) DEFAULT NULL,
    longitude_dest DECIMAL(10, 7) DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample containers with ETA details
INSERT INTO containers (container_id, location, pin, origin, destination, estimated_arrival, latitude, longitude, latitude_origin, longitude_origin, latitude_dest, longitude_dest) VALUES
('CNT-001', 'Cebu Port', '1111', 'Cebu', 'Iloilo', '2025-05-05 12:00:00', 10.2926, 123.9031, 10.2926, 123.9031, 10.7202, 122.5621),
('CNT-002', 'SRP Road', '2222', 'Cebu', 'Tagbilaran', '2025-05-06 10:30:00', 10.2639, 123.8783, 10.2639, 123.8783, 9.6550, 123.8552),
('CNT-003', 'Iloilo Terminal', '3333', 'Iloilo', 'Dumaguete', '2025-05-07 15:00:00', 10.7202, 122.5621, 10.7202, 122.5621, 9.3085, 123.3080),
('CNT-004', 'Dumaguete Dock', '4444', 'Dumaguete', 'Cebu', '2025-05-08 09:45:00', 9.3085, 123.3080, 9.3085, 123.3080, 10.2926, 123.9031),
('CNT-005', 'Tagbilaran Pier', '5555', 'Tagbilaran', 'SRP', '2025-05-09 11:15:00', 9.6550, 123.8552, 9.6550, 123.8552, 10.2639, 123.8783);

-- Users table with roles
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'security') DEFAULT 'staff',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Preview queries
SELECT * FROM containers;
DESCRIBE users;

drop database eseal;