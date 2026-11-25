-- FreshChoice Database Schema (MariaDB / MySQL)
CREATE DATABASE IF NOT EXISTS freshchoice_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE freshchoice_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS shift_post;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS availability;
DROP TABLE IF EXISTS request;
DROP TABLE IF EXISTS shift;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS store;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. STORE
CREATE TABLE store (
    store_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    address VARCHAR(100) NOT NULL
);

-- 2. USER
CREATE TABLE user (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_id INT UNSIGNED NOT NULL,
    role ENUM('employee', 'manager') NOT NULL DEFAULT 'employee',
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NULL DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    FOREIGN KEY (store_id) REFERENCES store(store_id) ON DELETE RESTRICT
);

-- 3. SHIFT
CREATE TABLE shift (
    shift_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    shift_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    UNIQUE KEY uq_user_shift_start (user_id, shift_date, start_time),
    CHECK (end_time > start_time),
    INDEX idx_shift_date (shift_date),
    FOREIGN KEY (store_id) REFERENCES store(store_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- 4. REQUEST
CREATE TABLE request (
    request_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (end_date >= start_date),
    INDEX idx_request_status (status),
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES user(user_id) ON DELETE SET NULL
);

-- 5. AVAILABILITY
CREATE TABLE availability (
    availability_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    weekday TINYINT UNSIGNED NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by INT UNSIGNED NULL,
    CHECK (weekday BETWEEN 1 AND 7),
    CHECK (end_time > start_time),
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES user(user_id) ON DELETE SET NULL
);

-- 6. ATTENDANCE
CREATE TABLE attendance (
    attendance_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    attendance_date DATE NOT NULL,
    type ENUM('present', 'late', 'sick') NOT NULL DEFAULT 'present',
    minutes_late SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    recorded_by INT UNSIGNED NULL,
    recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_attendance_day_type (user_id, attendance_date, type),
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES user(user_id) ON DELETE SET NULL
);

-- 7. SHIFT_POST
CREATE TABLE shift_post (
    post_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shift_id INT UNSIGNED NULL UNIQUE,
    store_id INT UNSIGNED NOT NULL,
    shift_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    posted_by INT UNSIGNED NOT NULL,
    claimed_by INT UNSIGNED NULL,
    status ENUM('open', 'claimed', 'cancelled') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (end_time > start_time),
    INDEX idx_post_status_date (status, shift_date),
    FOREIGN KEY (shift_id) REFERENCES shift(shift_id) ON DELETE SET NULL,
    FOREIGN KEY (store_id) REFERENCES store(store_id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (claimed_by) REFERENCES user(user_id) ON DELETE SET NULL
);