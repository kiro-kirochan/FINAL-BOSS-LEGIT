-- ============================================================
-- Hospital Management System (HMS) — Full Database
-- Rebuilt from kishan0725/Hospital-Management-System
-- Stack: React 18 + Laravel 11 + Bootstrap 5 + MySQL 8.0
-- ============================================================

CREATE DATABASE IF NOT EXISTS `myhmsdb`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `myhmsdb`;

-- --------------------------------------------------------
-- 1. SPECIALIZATIONS TABLE
-- Stores available medical specialties (used as FK in doctors)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `specialtb`;
CREATE TABLE `specialtb` (
    `id`            bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `specialization` varchar(100)        NOT NULL UNIQUE,
    `created_at`    timestamp           NULL DEFAULT NULL,
    `updated_at`    timestamp           NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `specialtb` (`specialization`, `created_at`, `updated_at`) VALUES
('Cardiology',        NOW(), NOW()),
('Pediatrics',        NOW(), NOW()),
('Dermatology',       NOW(), NOW()),
('Neurology',         NOW(), NOW()),
('General Medicine',  NOW(), NOW()),
('Orthopedics',       NOW(), NOW()),
('Oncology',          NOW(), NOW()),
('Psychiatry',        NOW(), NOW()),
('Gynecology',        NOW(), NOW()),
('ENT',               NOW(), NOW());

-- --------------------------------------------------------
-- 2. DOCTORS TABLE
-- --------------------------------------------------------
DROP TABLE IF EXISTS `doctb`;
CREATE TABLE `doctb` (
    `id`             bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`           varchar(100)        NOT NULL,
    `email`          varchar(100)        NOT NULL UNIQUE,
    `password`       varchar(255)        NOT NULL,
    `specialization` varchar(100)        NOT NULL,
    `docFees`        decimal(10,2)       NOT NULL DEFAULT 0.00,
    `created_at`     timestamp           NULL DEFAULT NULL,
    `updated_at`     timestamp           NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bcrypt hashes of "password123"
INSERT INTO `doctb` (`name`, `email`, `password`, `specialization`, `docFees`, `created_at`, `updated_at`) VALUES
('Dr. John Smith',    'john.smith@hms.com',    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cardiology',       150.00, NOW(), NOW()),
('Dr. Sarah Jenkins', 'sarah.jenkins@hms.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pediatrics',       120.00, NOW(), NOW()),
('Dr. Michael Chang', 'michael.chang@hms.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dermatology',      100.00, NOW(), NOW()),
('Dr. Emily Watson',  'emily.watson@hms.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Neurology',        200.00, NOW(), NOW()),
('Dr. Robert Patel',  'robert.patel@hms.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'General Medicine',  80.00, NOW(), NOW());

-- --------------------------------------------------------
-- 3. PATIENTS TABLE  (patreg in original)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `patreg`;
CREATE TABLE `patreg` (
    `id`         bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `fname`      varchar(100)        NOT NULL,
    `lname`      varchar(100)        NOT NULL,
    `email`      varchar(100)        NOT NULL UNIQUE,
    `password`   varchar(255)        NOT NULL,
    `contact`    varchar(20)         NOT NULL,
    `gender`     enum('Male','Female','Other') NOT NULL,
    `created_at` timestamp           NULL DEFAULT NULL,
    `updated_at` timestamp           NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `patreg` (`fname`, `lname`, `email`, `password`, `contact`, `gender`, `created_at`, `updated_at`) VALUES
('Alice',  'Reyes',    'alice.reyes@email.com',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09171234567', 'Female', NOW(), NOW()),
('Ben',    'Torres',   'ben.torres@email.com',    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09281234567', 'Male',   NOW(), NOW()),
('Clara',  'Santos',   'clara.santos@email.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09391234567', 'Female', NOW(), NOW());

-- --------------------------------------------------------
-- 4. APPOINTMENTS TABLE  (appointmenttb in original)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `appointmenttb`;
CREATE TABLE `appointmenttb` (
    `id`           bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `patient_id`   bigint(20) UNSIGNED NOT NULL,
    `doctor_id`    bigint(20) UNSIGNED NOT NULL,
    `appdate`      date                NOT NULL,
    `apptime`      time                NOT NULL,
    `status`       enum('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
    `created_at`   timestamp           NULL DEFAULT NULL,
    `updated_at`   timestamp           NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`patient_id`) REFERENCES `patreg`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`doctor_id`)  REFERENCES `doctb`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `appointmenttb` (`patient_id`, `doctor_id`, `appdate`, `apptime`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-06-01', '09:00:00', 'Confirmed', NOW(), NOW()),
(2, 3, '2026-06-02', '10:30:00', 'Pending',   NOW(), NOW()),
(3, 2, '2026-06-03', '14:00:00', 'Pending',   NOW(), NOW());
