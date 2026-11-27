-- FreshChoice shift management - database schema
-- Import this file through phpMyAdmin (Import tab) or:
--   mariadb -h 127.0.0.1 -u root < database/schema.sql
--
-- It drops and recreates the database, so run it only on a development machine.

DROP DATABASE IF EXISTS freshchoice;
CREATE DATABASE freshchoice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE freshchoice;

-- The three supermarkets. min_staff is the number of people a store
-- wants scheduled on one day; the calendar marks a day that falls below it.
CREATE TABLE stores (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(60)  NOT NULL,
    address    VARCHAR(120) NOT NULL,
    min_staff  TINYINT      NOT NULL DEFAULT 2
);

-- One row per account. role decides which portal the person enters.
-- store_id is the home store; a shift can still be scheduled in another store.
CREATE TABLE users (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    first_name     VARCHAR(50)  NOT NULL,
    last_name      VARCHAR(50)  NOT NULL,
    email          VARCHAR(120) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role           ENUM('manager', 'employee') NOT NULL DEFAULT 'employee',
    store_id       INT          NULL,
    contract_hours TINYINT      NOT NULL DEFAULT 16,
    CONSTRAINT fk_users_store FOREIGN KEY (store_id) REFERENCES stores(id)
);

-- A scheduled shift: one employee, one store, one date and time range.
CREATE TABLE shifts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT  NOT NULL,
    store_id   INT  NOT NULL,
    work_date  DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time   TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_shifts_user  FOREIGN KEY (user_id)  REFERENCES users(id),
    CONSTRAINT fk_shifts_store FOREIGN KEY (store_id) REFERENCES stores(id),
    INDEX idx_shifts_date (work_date)
);

-- Days an employee says they can or cannot work. Only approved rows are
-- used by the conflict checks.
CREATE TABLE availability (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT  NOT NULL,
    work_date    DATE NOT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    status       ENUM('pending', 'approved', 'declined') NOT NULL DEFAULT 'pending',
    note         VARCHAR(255) NULL,
    CONSTRAINT fk_availability_user FOREIGN KEY (user_id) REFERENCES users(id)
);

-- A shift with no employee yet, shown on the staff portal.
CREATE TABLE shift_posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    store_id   INT  NOT NULL,
    work_date  DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time   TIME NOT NULL,
    needed     TINYINT NOT NULL DEFAULT 1,
    status     ENUM('open', 'filled') NOT NULL DEFAULT 'open',
    created_by INT  NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_posts_store   FOREIGN KEY (store_id)   REFERENCES stores(id),
    CONSTRAINT fk_posts_creator FOREIGN KEY (created_by) REFERENCES users(id)
);

-- One row for every change made in the app, written in the same
-- transaction as the change itself.
CREATE TABLE audit_log (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    action     VARCHAR(60) NOT NULL,
    table_name VARCHAR(60) NOT NULL,
    record_id  INT NOT NULL,
    reason     VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO stores (name, address, min_staff) VALUES
('FreshChoice Central', 'Marktstraat 12', 3),
('FreshChoice North',   'Noorderweg 4',   2),
('FreshChoice South',   'Zuiderlaan 88',  2);

-- Every demo account uses the password: password123
INSERT INTO users (first_name, last_name, email, password_hash, role, store_id, contract_hours) VALUES
('Sara',  'Bakker',  'manager@freshchoice.test', '$2y$12$qt0BIOu6XjiDRMF9Cth8oOAN4e/M/M4jyECIOxrErOIKgHpSPuVUC', 'manager',  1, 40),
('Diego', 'Martins', 'diego@freshchoice.test',   '$2y$12$qt0BIOu6XjiDRMF9Cth8oOAN4e/M/M4jyECIOxrErOIKgHpSPuVUC', 'manager',  1, 40),
('Lena',  'Visser',  'lena@freshchoice.test',    '$2y$12$qt0BIOu6XjiDRMF9Cth8oOAN4e/M/M4jyECIOxrErOIKgHpSPuVUC', 'employee', 1, 24),
('Tom',   'de Boer', 'tom@freshchoice.test',     '$2y$12$qt0BIOu6XjiDRMF9Cth8oOAN4e/M/M4jyECIOxrErOIKgHpSPuVUC', 'employee', 1, 16),
('Maya',  'Haddad',  'maya@freshchoice.test',    '$2y$12$qt0BIOu6XjiDRMF9Cth8oOAN4e/M/M4jyECIOxrErOIKgHpSPuVUC', 'employee', 1, 20),
('Noah',  'Jansen',  'noah@freshchoice.test',    '$2y$12$qt0BIOu6XjiDRMF9Cth8oOAN4e/M/M4jyECIOxrErOIKgHpSPuVUC', 'employee', 2, 16),
('Iris',  'Smit',    'iris@freshchoice.test',    '$2y$12$qt0BIOu6XjiDRMF9Cth8oOAN4e/M/M4jyECIOxrErOIKgHpSPuVUC', 'employee', 2, 20),
('Youssef', 'Karim', 'youssef@freshchoice.test', '$2y$12$qt0BIOu6XjiDRMF9Cth8oOAN4e/M/M4jyECIOxrErOIKgHpSPuVUC', 'employee', 3, 16),
('Emma',  'Peters',  'emma@freshchoice.test',    '$2y$12$qt0BIOu6XjiDRMF9Cth8oOAN4e/M/M4jyECIOxrErOIKgHpSPuVUC', 'employee', 3, 12);

-- Example shifts for the current week, so the calendar is not empty.
-- WEEKDAY(CURDATE()) gives 0 for Monday, so this is always this week's Monday.
SET @monday = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

INSERT INTO shifts (user_id, store_id, work_date, start_time, end_time) VALUES
(3, 1, @monday,                        '09:00', '17:00'),
(4, 1, @monday,                        '12:00', '20:00'),
(6, 2, @monday,                        '08:00', '14:00'),
(5, 1, DATE_ADD(@monday, INTERVAL 1 DAY), '09:00', '17:00'),
(7, 2, DATE_ADD(@monday, INTERVAL 1 DAY), '14:00', '20:00'),
(8, 3, DATE_ADD(@monday, INTERVAL 1 DAY), '09:00', '13:00'),
(3, 1, DATE_ADD(@monday, INTERVAL 2 DAY), '09:00', '17:00'),
(9, 3, DATE_ADD(@monday, INTERVAL 2 DAY), '13:00', '20:00');

-- One approved day off, three days from now, so the availability warning
-- can be seen on the assign form.
INSERT INTO availability (user_id, work_date, is_available, status, note) VALUES
(3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 0, 'approved', 'Family appointment');

-- Two open shifts for next week.
INSERT INTO shift_posts (store_id, work_date, start_time, end_time, needed, created_by) VALUES
(1, DATE_ADD(@monday, INTERVAL 7 DAY), '09:00', '17:00', 1, 1),
(2, DATE_ADD(@monday, INTERVAL 8 DAY), '12:00', '20:00', 1, 1);
