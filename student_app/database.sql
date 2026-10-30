-- ============================================================
-- PRACTICAL-19: SQL Script
-- Purpose : Create the database and students table
-- Run this in phpMyAdmin or MySQL CLI before starting the app
-- ============================================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS student_db;

-- Step 2: Select / use that database
USE student_db;

-- Step 3: Create the students table
CREATE TABLE IF NOT EXISTS students (
    id         INT          AUTO_INCREMENT PRIMARY KEY,  -- Unique ID, auto increases
    name       VARCHAR(100) NOT NULL,                    -- Student full name
    email      VARCHAR(150) NOT NULL UNIQUE,             -- Email must be unique
    course     VARCHAR(100) NOT NULL,                    -- Course enrolled in
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP   -- Auto-set on insert
);

-- Step 4: (Optional) Insert sample data to test the display page
INSERT INTO students (name, email, course) VALUES
('Alice Johnson',  'alice@example.com',  'B.Tech Computer Science'),
('Bob Smith',      'bob@example.com',    'BCA'),
('Charlie Brown',  'charlie@example.com','MCA');
