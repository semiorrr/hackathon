-- Create the database (if not yet created)
CREATE DATABASE eseal;
USE eseal;

-- Create the containers table
CREATE TABLE IF NOT EXISTS containers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    container_id VARCHAR(50) NOT NULL,
    status ENUM('Sealed', 'Tampered') DEFAULT 'Sealed',
    location VARCHAR(100),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO containers (container_id, location) VALUES
('CNT-001', 'Cebu Port'),
('CNT-002', 'SRP Road'),
('CNT-003', 'Iloilo Terminal'),
('CNT-004', 'Dumaguete Dock'),
('CNT-005', 'Tagbilaran Pier');
