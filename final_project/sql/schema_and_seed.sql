-- final_project/sql/schema_and_seed.sql
CREATE DATABASE IF NOT EXISTS tp_web_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE tp_web_db;

-- users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','professor','student') NOT NULL,
  fullname VARCHAR(150),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- students
CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  matricule VARCHAR(100) NOT NULL UNIQUE,
  fullname VARCHAR(150) NOT NULL,
  group_name VARCHAR(100) DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- courses
CREATE TABLE IF NOT EXISTS courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50),
  title VARCHAR(200) NOT NULL
);

-- groups table
CREATE TABLE IF NOT EXISTS groups_tbl (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

-- course_groups mapping
CREATE TABLE IF NOT EXISTS course_groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  group_id INT NOT NULL,
  professor_id INT NOT NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (group_id) REFERENCES groups_tbl(id),
  FOREIGN KEY (professor_id) REFERENCES users(id)
);

-- attendance_sessions
CREATE TABLE IF NOT EXISTS attendance_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_group_id INT NOT NULL,
  date DATE NOT NULL,
  opened_by INT NOT NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_group_id) REFERENCES course_groups(id),
  FOREIGN KEY (opened_by) REFERENCES users(id)
);

-- attendance
CREATE TABLE IF NOT EXISTS attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  session_id INT NOT NULL,
  status ENUM('present','absent') NOT NULL,
  recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (session_id) REFERENCES attendance_sessions(id)
);

-- justifications
CREATE TABLE IF NOT EXISTS justifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  session_id INT NOT NULL,
  reason TEXT,
  file_path VARCHAR(255) DEFAULT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (session_id) REFERENCES attendance_sessions(id)
);

-- sample seed for courses and groups (safe to run even if data exists)
INSERT IGNORE INTO courses (code, title) VALUES ('CS101','Intro to Programming'), ('CS102','Data Structures');
INSERT IGNORE INTO groups_tbl (name) VALUES ('G1'), ('G2');

-- (Optional) create sample students - adjust matr/firstname as needed
INSERT IGNORE INTO students (matricule, fullname, group_name) VALUES
('S1001','Omar Zidoun','G1'),
('S1002','Amina Belaid','G1'),
('S1003','Khaled Mou','G2');

-- Note: do NOT rely on this file to create admin/professor users with passwords.
-- Use the helper script set_admin_password.php to set the admin user's password,
-- or insert users manually and set password_hash using password_hash() output.
