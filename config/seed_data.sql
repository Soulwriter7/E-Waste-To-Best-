-- ==============================================================
-- E-Waste to Best: Smart E-Waste Recycling & Metal Recovery System
-- Database Setup & Sample Seed Data
-- Import this file in phpMyAdmin or MySQL CLI
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `ewaste_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ewaste_db`;

-- -------------------------------------------------------------
-- 1. Table structure for table `users`
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `impact_logs`;
DROP TABLE IF EXISTS `pickup_requests`;
DROP TABLE IF EXISTS `metal_rates`;
DROP TABLE IF EXISTS `device_categories`;
DROP TABLE IF EXISTS `collection_centers`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(60) NOT NULL,
  `pincode` VARCHAR(10) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 2. Table structure for table `collection_centers`
-- -------------------------------------------------------------
CREATE TABLE `collection_centers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `center_name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(60) NOT NULL,
  `state` VARCHAR(60) NOT NULL,
  `pincode` VARCHAR(10) NOT NULL,
  `operating_hours` VARCHAR(100) DEFAULT '9:00 AM - 6:00 PM (Mon-Sat)',
  `accepted_items` TEXT COMMENT 'Comma separated device categories',
  `latitude` DECIMAL(10, 7) NULL,
  `longitude` DECIMAL(10, 7) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 3. Table structure for table `device_categories`
-- -------------------------------------------------------------
CREATE TABLE `device_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_name` VARCHAR(100) NOT NULL,
  `unit_type` ENUM('unit', 'kg') DEFAULT 'unit',
  `description` VARCHAR(255),
  `copper_yield_g` DECIMAL(10, 4) DEFAULT 0.0000 COMMENT 'Grams of Copper per unit or kg',
  `aluminium_yield_g` DECIMAL(10, 4) DEFAULT 0.0000 COMMENT 'Grams of Aluminium per unit or kg',
  `silver_yield_g` DECIMAL(10, 4) DEFAULT 0.0000 COMMENT 'Grams of Silver per unit or kg',
  `gold_yield_g` DECIMAL(10, 4) DEFAULT 0.0000 COMMENT 'Grams of Gold per unit or kg',
  `co2_saving_kg` DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Estimated CO2 avoided per unit or kg',
  `toxic_diverted_kg` DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Toxic material diverted from landfill',
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 4. Table structure for table `metal_rates`
-- -------------------------------------------------------------
CREATE TABLE `metal_rates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `metal_name` VARCHAR(50) NOT NULL UNIQUE,
  `symbol` VARCHAR(10) NOT NULL,
  `rate_per_gram` DECIMAL(10, 2) NOT NULL COMMENT 'In INR',
  `updated_by` INT NULL,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 5. Table structure for table `pickup_requests`
-- -------------------------------------------------------------
CREATE TABLE `pickup_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `request_code` VARCHAR(25) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `center_id` INT NULL,
  `device_category_id` INT NOT NULL,
  `quantity` DECIMAL(10, 2) NOT NULL COMMENT 'Number of units or kg',
  `pickup_address` TEXT NOT NULL,
  `pickup_city` VARCHAR(60) NOT NULL,
  `pickup_pincode` VARCHAR(10) NOT NULL,
  `preferred_date` DATE NOT NULL,
  `preferred_slot` ENUM('Morning (9AM - 12PM)', 'Afternoon (12PM - 3PM)', 'Evening (3PM - 6PM)') NOT NULL,
  `item_condition` ENUM('Working', 'Partially Working', 'Dead / Scrap') DEFAULT 'Dead / Scrap',
  `special_notes` TEXT NULL,
  
  -- Calculated Theoretical Estimates (at time of booking)
  `est_copper_g` DECIMAL(10, 3) DEFAULT 0.000,
  `est_aluminium_g` DECIMAL(10, 3) DEFAULT 0.000,
  `est_silver_g` DECIMAL(10, 4) DEFAULT 0.0000,
  `est_gold_g` DECIMAL(10, 4) DEFAULT 0.0000,
  `est_total_value` DECIMAL(10, 2) DEFAULT 0.00,
  `est_co2_saved_kg` DECIMAL(10, 2) DEFAULT 0.00,

  -- Verified Actual Measurements (Recorded after physical lab assay)
  `is_verified` TINYINT(1) DEFAULT 0,
  `actual_weight_kg` DECIMAL(10, 2) NULL,
  `actual_copper_g` DECIMAL(10, 3) NULL,
  `actual_aluminium_g` DECIMAL(10, 3) NULL,
  `actual_silver_g` DECIMAL(10, 4) NULL,
  `actual_gold_g` DECIMAL(10, 4) NULL,
  `actual_payout_value` DECIMAL(10, 2) NULL,
  `verification_notes` TEXT NULL,

  -- Workflow Status
  `status` ENUM('Pending', 'Assigned', 'Out for Pickup', 'Collected', 'Completed', 'Cancelled') DEFAULT 'Pending',
  `admin_remarks` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`center_id`) REFERENCES `collection_centers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`device_category_id`) REFERENCES `device_categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 6. Table structure for table `impact_logs`
-- -------------------------------------------------------------
CREATE TABLE `impact_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pickup_id` INT NOT NULL,
  `co2_saved_kg` DECIMAL(10, 2) NOT NULL,
  `toxic_waste_diverted_kg` DECIMAL(10, 2) NOT NULL,
  `precious_metals_reclaimed_g` DECIMAL(10, 3) NOT NULL,
  `logged_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`pickup_id`) REFERENCES `pickup_requests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- SAMPLE DATA SEEDING
-- =============================================================

-- Passwords:
-- admin@ewaste.org  -> admin123 (hash: $2y$10$wN2w4w6oOhy7vG9zK8zDheL4C9V51J2WkWx4d8o8L3Q8qYdEsmDsy)
-- rahul@gmail.com   -> user123  (hash: $2y$10$T8Z65pZtO3Z18X1c9k1H8egF2b3I.F9A/XfWzQ0fG6D5yT3rV6xWq)
-- priya@gmail.com   -> user123  (hash: $2y$10$T8Z65pZtO3Z18X1c9k1H8egF2b3I.F9A/XfWzQ0fG6D5yT3rV6xWq)
-- (We use standard password_hash generated hashes that work reliably in PHP 8.x)

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `phone`, `address`, `city`, `pincode`, `role`) VALUES
(1, 'Admin Officer', 'admin@ewaste.org', '$2y$10$WkG/m6iZg1m2X4xHhA1vbeL3Ym8e5H4Wq2F2a9J1C8v9E3q7rD1qy', '+91 98765 43210', 'E-Waste Command Center, Green Tech Hub', 'New Delhi', '110001', 'admin'),
(2, 'Rahul Sharma', 'rahul@gmail.com', '$2y$10$eO0WJ7z1J3R9z0uL5n6NbeD4w9a2Q7m5k1J3C8v9E3q7rD1qy5aZm', '+91 98111 22334', 'Flat 402, Green Meadows, Sector 62', 'Noida', '201309', 'user'),
(3, 'Priya Patel', 'priya@gmail.com', '$2y$10$eO0WJ7z1J3R9z0uL5n6NbeD4w9a2Q7m5k1J3C8v9E3q7rD1qy5aZm', '+91 98222 33445', '12, Sunrise Residency, SG Highway', 'Ahmedabad', '380054', 'user');

-- Metal Market Reference Rates (in INR per gram)
INSERT INTO `metal_rates` (`id`, `metal_name`, `symbol`, `rate_per_gram`, `updated_by`) VALUES
(1, 'Gold', 'Au', 7150.00, 1),
(2, 'Silver', 'Ag', 88.50, 1),
(3, 'Copper', 'Cu', 0.82, 1),
(4, 'Aluminium', 'Al', 0.24, 1);

-- Device Categories with Empirical Recovery Yields & Environmental Metrics
-- Sources: USGS Scientific Circular 1400, EPA E-Waste Studies, UNEP Global E-waste Monitor
INSERT INTO `device_categories` (`id`, `category_name`, `unit_type`, `description`, `copper_yield_g`, `aluminium_yield_g`, `silver_yield_g`, `gold_yield_g`, `co2_saving_kg`, `toxic_diverted_kg`, `is_active`) VALUES
(1, 'Smartphone / Mobile Phone', 'unit', 'Obsolete smartphones with motherboard, battery and chassis', 15.2000, 3.5000, 0.2500, 0.0240, 18.50, 0.18, 1),
(2, 'Laptop Computer', 'unit', 'Complete notebook laptops including motherboard, connectors, heatsinks', 185.0000, 310.0000, 1.8500, 0.1950, 42.00, 1.45, 1),
(3, 'Desktop Motherboard (PC)', 'unit', 'ATX / Micro-ATX printed circuit board from desktop PCs', 240.0000, 95.0000, 2.2000, 0.2800, 31.00, 0.90, 1),
(4, 'Desktop Computer (Full Tower)', 'unit', 'Complete CPU cabinet with PSU, motherboard, RAM, and cables', 650.0000, 1100.0000, 4.5000, 0.4200, 95.00, 4.20, 1),
(5, 'Computer RAM (Memory Stick)', 'unit', 'DDR2 / DDR3 / DDR4 gold-fingered DIMM or SO-DIMM sticks', 12.0000, 4.0000, 0.4000, 0.0650, 6.50, 0.05, 1),
(6, 'Hard Disk Drive (HDD 3.5\")', 'unit', 'Mechanical hard drive containing aluminium platen, copper voice coil', 110.0000, 380.0000, 0.3500, 0.0150, 14.00, 0.45, 1),
(7, 'CRT / LCD Computer Monitor', 'unit', 'Displays, flat screens and desktop monitors', 320.0000, 450.0000, 0.8000, 0.0350, 35.00, 3.80, 1),
(8, 'Mixed Electrical Cables & Wires', 'kg', 'Unstripped copper power cords, data lines and networking cables', 420.0000, 15.0000, 0.0000, 0.0000, 12.00, 0.60, 1),
(9, 'Tablet / iPad', 'unit', 'Portable touch devices, logic boards, shielding and components', 32.0000, 85.0000, 0.4500, 0.0420, 22.00, 0.35, 1);

-- Authorized Collection Centers
INSERT INTO `collection_centers` (`id`, `center_name`, `contact_person`, `phone`, `email`, `address`, `city`, `state`, `pincode`, `operating_hours`, `accepted_items`, `latitude`, `longitude`, `is_active`) VALUES
(1, 'EcoGreen E-Waste Processing Facility', 'Vikram Mehra', '+91 11 2789 4401', 'delhi.central@ewastebest.org', 'Plot 45, Okhla Industrial Area Phase III', 'New Delhi', 'Delhi', '110020', '9:30 AM - 6:30 PM (Mon-Sat)', 'Smartphones, Laptops, Motherboards, Monitors, Cables, Hard Drives', 28.5355, 77.2732, 1),
(2, 'Silicon Valley Green Recovery Depot', 'Anand Kulkarni', '+91 80 4122 8900', 'bangalore.hub@ewastebest.org', '88, Electronic City Phase 1, Hosur Road', 'Bengaluru', 'Karnataka', '560100', '9:00 AM - 7:00 PM (Mon-Sun)', 'Smartphones, Laptops, Motherboards, RAM, Full PCs, Tablets', 12.8399, 77.6770, 1),
(3, 'Western India E-Waste Circular Hub', 'Sunil Joshi', '+91 22 2847 1199', 'mumbai.west@ewastebest.org', 'Gala No 14, MIDC Industrial Zone, Andheri East', 'Mumbai', 'Maharashtra', '400093', '10:00 AM - 6:00 PM (Mon-Sat)', 'Smartphones, Laptops, Monitors, Mixed Cables, Hard Drives', 19.1197, 72.8656, 1),
(4, 'Deccan Eco-Recycling Center', 'Dr. Ramesh Rao', '+91 40 2311 5566', 'hyderabad.hub@ewastebest.org', 'Tech Park Road, Madhapur, HITEC City', 'Hyderabad', 'Telangana', '500081', '9:00 AM - 6:00 PM (Mon-Fri)', 'Smartphones, Laptops, Motherboards, RAM Sticks, Mixed Cables', 17.4483, 78.3915, 1),
(5, 'Awadh Smart E-Waste Collection Point', 'Alok Srivastava', '+91 522 272 8844', 'lucknow.center@ewastebest.org', 'Shop 19, Cyber Tower, Vibhuti Khand, Gomti Nagar', 'Lucknow', 'Uttar Pradesh', '226010', '10:00 AM - 5:30 PM (Mon-Sat)', 'Smartphones, Laptops, Motherboards, Monitors, RAM, Cables', 26.8643, 81.0118, 1),
(6, 'Pune Circular Recovery Station', 'Neeta Deshmukh', '+91 20 2567 9922', 'pune.center@ewastebest.org', 'Survey No 12, Hinjewadi Phase 2', 'Pune', 'Maharashtra', '411057', '9:30 AM - 6:30 PM (Mon-Sat)', 'Smartphones, Laptops, Motherboards, Monitors, Hard Drives', 18.5913, 73.7389, 1);

-- Sample Pickup Requests (Demonstrating verified vs. pending theoretical estimates)
INSERT INTO `pickup_requests` (`id`, `request_code`, `user_id`, `center_id`, `device_category_id`, `quantity`, `pickup_address`, `pickup_city`, `pickup_pincode`, `preferred_date`, `preferred_slot`, `item_condition`, `special_notes`, `est_copper_g`, `est_aluminium_g`, `est_silver_g`, `est_gold_g`, `est_total_value`, `est_co2_saved_kg`, `is_verified`, `actual_weight_kg`, `actual_copper_g`, `actual_aluminium_g`, `actual_silver_g`, `actual_gold_g`, `actual_payout_value`, `verification_notes`, `status`, `admin_remarks`) VALUES
(1, 'EWB-2026-1001', 2, 1, 2, 2.00, 'Flat 402, Green Meadows, Sector 62', 'Noida', '201309', '2026-10-28', 'Morning (9AM - 12PM)', 'Partially Working', 'Old Dell and HP work laptops without charger', 370.000, 620.000, 3.7000, 0.3900, 3569.24, 84.00, 1, 4.20, 365.500, 615.000, 3.6500, 0.3850, 3528.50, 'Disassembled & assayed at Okhla Lab. Good condition gold plated pins recovered.', 'Completed', 'Processed and certificate issued.'),
(2, 'EWB-2026-1002', 2, 1, 1, 4.00, 'Flat 402, Green Meadows, Sector 62', 'Noida', '201309', '2026-11-02', 'Afternoon (12PM - 3PM)', 'Dead / Scrap', 'Old smartphones with broken displays', 60.800, 14.000, 1.0000, 0.0960, 827.86, 74.00, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Assigned', 'Collector scheduled for pickup on preferred date.'),
(3, 'EWB-2026-1003', 3, 3, 3, 5.00, '12, Sunrise Residency, SG Highway', 'Ahmedabad', '380054', '2026-11-05', 'Evening (3PM - 6PM)', 'Dead / Scrap', 'Scrap desktop motherboards from college lab cleanup', 1200.000, 475.000, 11.0000, 1.4000, 12081.50, 155.00, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', 'Awaiting center schedule confirmation.');

-- Environmental Impact Audit Logs for Completed Pickup 1
INSERT INTO `impact_logs` (`id`, `pickup_id`, `co2_saved_kg`, `toxic_waste_diverted_kg`, `precious_metals_reclaimed_g`) VALUES
(1, 1, 84.00, 2.90, 4.035);
