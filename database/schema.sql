-- Grading System Database Schema
-- Drop database if exists and create new
DROP DATABASE IF EXISTS grading_system;
CREATE DATABASE grading_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE grading_system;

-- Table: admins
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- Table: students
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    course VARCHAR(100) NOT NULL,
    year_level INT NOT NULL,
    email VARCHAR(100),
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student_id (student_id),
    INDEX idx_name (last_name, first_name),
    INDEX idx_course (course)
) ENGINE=InnoDB;

-- Table: semesters
CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    active_flag TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (active_flag)
) ENGINE=InnoDB;

-- Table: subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(100) NOT NULL,
    units INT NOT NULL DEFAULT 3,
    semester_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_semester (semester_id)
) ENGINE=InnoDB;

-- Table: grades
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    prelim DECIMAL(5,2) DEFAULT 0.00,
    midterm DECIMAL(5,2) DEFAULT 0.00,
    final DECIMAL(5,2) DEFAULT 0.00,
    final_grade DECIMAL(5,2) DEFAULT 0.00,
    letter_grade CHAR(1) DEFAULT 'F',
    remarks VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_subject (student_id, subject_id),
    INDEX idx_student (student_id),
    INDEX idx_subject (subject_id),
    INDEX idx_letter_grade (letter_grade)
) ENGINE=InnoDB;

-- Table: audit_logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    target_table VARCHAR(50) NOT NULL,
    target_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_admin (admin_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Insert Sample Data

-- Admins (Password: Admin123!)
INSERT INTO admins (username, password_hash, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gradingsystem.com'),
('principal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'School Principal', 'principal@gradingsystem.com');

-- Semesters
INSERT INTO semesters (name, start_date, end_date, active_flag) VALUES
('2024-2025 First Semester', '2024-08-01', '2024-12-20', 1),
('2024-2025 Second Semester', '2025-01-06', '2025-05-30', 0);

-- Students (Password: Student123!)
INSERT INTO students (student_id, first_name, last_name, middle_name, course, year_level, email, password_hash) VALUES
('2024-0001', 'Juan', 'Dela Cruz', 'Santos', 'BS Computer Science', 3, 'juan.delacruz@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('2024-0002', 'Maria', 'Garcia', 'Reyes', 'BS Information Technology', 2, 'maria.garcia@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('2024-0003', 'Pedro', 'Gonzales', 'Lopez', 'BS Computer Science', 3, 'pedro.gonzales@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('2024-0004', 'Ana', 'Rodriguez', 'Martinez', 'BS Information Technology', 2, 'ana.rodriguez@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('2024-0005', 'Carlos', 'Fernandez', 'Torres', 'BS Computer Science', 4, 'carlos.fernandez@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Subjects
INSERT INTO subjects (code, title, units, semester_id) VALUES
('CS101', 'Introduction to Programming', 3, 1),
('CS102', 'Data Structures and Algorithms', 3, 1),
('CS201', 'Database Management Systems', 3, 1),
('IT101', 'Web Development Fundamentals', 3, 1),
('IT201', 'Network Administration', 3, 1),
('GE101', 'Mathematics in the Modern World', 3, 1);

-- Grades (Sample grades for 3 students)
INSERT INTO grades (student_id, subject_id, prelim, midterm, final, final_grade, letter_grade, remarks) VALUES
-- Student 1 (Juan Dela Cruz)
(1, 1, 88.00, 92.00, 90.00, 90.00, 'A', 'Passed'),
(1, 2, 85.00, 87.00, 89.00, 87.20, 'B', 'Passed'),
(1, 3, 78.00, 82.00, 85.00, 82.20, 'B', 'Passed'),

-- Student 2 (Maria Garcia)
(2, 4, 92.00, 94.00, 95.00, 93.80, 'A', 'Passed'),
(2, 5, 88.00, 85.00, 90.00, 87.80, 'B', 'Passed'),
(2, 6, 75.00, 78.00, 80.00, 78.00, 'C', 'Passed'),

-- Student 3 (Pedro Gonzales)
(3, 1, 70.00, 75.00, 78.00, 74.60, 'C', 'Passed'),
(3, 2, 65.00, 68.00, 72.00, 68.80, 'D', 'Passed'),
(3, 3, 85.00, 88.00, 90.00, 87.80, 'B', 'Passed');

-- Update final grades based on calculation (Prelim 30%, Midterm 30%, Final 40%)
UPDATE grades SET 
    final_grade = ROUND((prelim * 0.30) + (midterm * 0.30) + (final * 0.40), 2),
    letter_grade = CASE
        WHEN (prelim * 0.30) + (midterm * 0.30) + (final * 0.40) >= 90 THEN 'A'
        WHEN (prelim * 0.30) + (midterm * 0.30) + (final * 0.40) >= 80 THEN 'B'
        WHEN (prelim * 0.30) + (midterm * 0.30) + (final * 0.40) >= 70 THEN 'C'
        WHEN (prelim * 0.30) + (midterm * 0.30) + (final * 0.40) >= 60 THEN 'D'
        ELSE 'F'
    END,
    remarks = CASE
        WHEN (prelim * 0.30) + (midterm * 0.30) + (final * 0.40) >= 60 THEN 'Passed'
        ELSE 'Failed'
    END;