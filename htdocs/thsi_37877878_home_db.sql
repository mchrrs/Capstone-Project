-- Database Initialization
-- phpMyAdmin SQL Dump
-- Version: 5.2.0
-- Generated on: Nov 21, 2022 at 09:26 PM
-- Server Version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Set UTF-8 Character Encoding
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `home_db`

-- --------------------------------------------------------
-- Table: `admins`
CREATE TABLE `admins` (
    `id` VARCHAR(20) NOT NULL PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL -- Support longer hashed passwords
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admins` (`id`, `name`, `password`) 
VALUES ('BcjKNX58e4x7bIqIvxG7', 'admin', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2');

-- --------------------------------------------------------
-- Table: `users`
CREATE TABLE `users` (
    `id` VARCHAR(20) NOT NULL PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `number` VARCHAR(15) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL -- Secure hashed passwords
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: `property`
CREATE TABLE `property` (
    `id` varchar(20) NOT NULL,
    `user_id` varchar(20) NOT NULL,
    `property_name` varchar(50) NOT NULL,
    `address` varchar(100) NOT NULL,
    `price` varchar(10) NOT NULL,
    `type` varchar(10) NOT NULL,
    `offer` varchar(10) NOT NULL,
    `status` varchar(50) NOT NULL,
    `furnished` varchar(50) NOT NULL,
    `bedroom` varchar(10) NOT NULL,
    `bathroom` varchar(10) NOT NULL,
    `carpet` varchar(10) NOT NULL,
    `age` varchar(2) NOT NULL,
    `total_floors` varchar(2) NOT NULL,
    `room_floor` varchar(2) NOT NULL,
    `image_01` VARCHAR(255),
    `image_02` VARCHAR(255),
    `image_03` VARCHAR(255),
    `image_04` VARCHAR(255),
    `image_05` VARCHAR(255),
    `description` TEXT,
    `occupied_by` VARCHAR(20) NULL,
    `occupants` INT NULL,
    `contract` VARCHAR(255) NULL;
    `date` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: `complaints`
CREATE TABLE `complaints` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(20) NOT NULL,
    `complaint_type` ENUM('Maintenance', 'Noise', 'Cleanliness', 'Safety', 'Utility', 'Payment', 'General') NOT NULL,
    `description` TEXT NOT NULL,
    `status` ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Updated Table: `occupied_properties`
CREATE TABLE `occupied_properties` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `property_name` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `occupants` INT NOT NULL,
    `contract` VARCHAR(255),
    `number` VARCHAR(15) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `status` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Table: `bills`
CREATE TABLE `bills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(20) NOT NULL,             -- Link bill to a specific user
    `property_id` VARCHAR(20) NOT NULL,         -- Link bill to a specific property
    `bill_type` ENUM('house_rent', 'water_bill', 'electricity_bill') NOT NULL, -- Type of bill
    `amount` DECIMAL(10, 2) NOT NULL,           -- Amount for the selected bill type
    `total` DECIMAL(10, 2) GENERATED ALWAYS AS 
        (`amount`) STORED,                     -- Total is just the amount now
    `due_date` DATETIME NOT NULL,               -- Due date for payment
    `status` ENUM('pending', 'paid', 'overdue') NOT NULL DEFAULT 'pending', -- Bill status
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: `receipts`
CREATE TABLE `receipts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bill_id` INT NOT NULL,                     -- Link receipt to a specific bill
    `user_id` VARCHAR(20) NOT NULL,              -- Link receipt to a specific user (VARCHAR(20))
    `name` VARCHAR(50) NOT NULL,                 -- Store user's name directly in receipts table
    `receipt_file` VARCHAR(255) NOT NULL,        -- File path for the uploaded receipt
    `remarks` TEXT,                             -- Optional remarks or description
    `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP, -- When the receipt was submitted
    `status` ENUM('pending', 'paid', 'rejected') DEFAULT 'pending', -- Status of payment validation
    `approved_by` INT DEFAULT NULL,             -- Admin who approved/rejected the payment
    `approved_at` DATETIME DEFAULT NULL         -- When the payment was approved/rejected
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: `conversations`
CREATE TABLE `conversations` (
    `conversation_id` VARCHAR(20) PRIMARY KEY,
    `admin_id` VARCHAR(20),
    `user_id` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: `messages`
CREATE TABLE `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` VARCHAR(20) NOT NULL,  -- Should be VARCHAR(20) to match the conversation_id
    `sender_id` VARCHAR(20) NOT NULL,        -- This refers to either user_id or admin_id
    `sender_type` ENUM('user', 'admin') NOT NULL, -- To differentiate between messages from users or admins
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



COMMIT;
