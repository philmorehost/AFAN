-- AFAN Database Schema

-- Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    permissions JSON NOT NULL, -- List of permission keys
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Beneficiaries Table
CREATE TABLE IF NOT EXISTS beneficiaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100),
    location VARCHAR(255),
    nin VARCHAR(20) UNIQUE,
    gender VARCHAR(10),
    state_of_origin VARCHAR(50),
    photo LONGTEXT, -- Base64 encoded photo from NIN API
    farm_size DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Programs Table
CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tokens Table
CREATE TABLE IF NOT EXISTS tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    beneficiary_id INT NOT NULL,
    token_code VARCHAR(16) UNIQUE NOT NULL,
    status ENUM('unused', 'redeemed', 'expired') DEFAULT 'unused',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    redeemed_at TIMESTAMP NULL,
    redeemed_by INT NULL, -- Agent ID
    FOREIGN KEY (program_id) REFERENCES programs(id),
    FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries(id),
    FOREIGN KEY (redeemed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
