<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "parking_system";

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) die("Connection failed");

$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

/* ===== ROLE ===== */
$conn->query("
CREATE TABLE role (
  role_id INT PRIMARY KEY,
  role_name ENUM('admin','user') NOT NULL
) ENGINE=InnoDB
");

$conn->query("
INSERT IGNORE INTO role VALUES (1,'admin'),(2,'user')
");

/* ===== USERS ===== */
$conn->query("
CREATE TABLE users (
  u_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  contact VARCHAR(15) UNIQUE,
  password VARCHAR(255),
  role_id INT DEFAULT 2,
  is_verified TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id)
    REFERENCES role(role_id)
    ON UPDATE CASCADE
) ENGINE=InnoDB
");

/* ===== OTP ===== */
$conn->query("
CREATE TABLE otp_verification (
  otp_id INT AUTO_INCREMENT PRIMARY KEY,
  u_id INT NOT NULL,
  otp_code VARCHAR(6),
  purpose ENUM('login','register','reset'),
  expires_at DATETIME,
  is_used TINYINT DEFAULT 0,
  attempt_count INT DEFAULT 0,
  blocked_until DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (u_id)
    REFERENCES users(u_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
");

/* ===== LOGIN AUDIT ===== */
$conn->query("
CREATE TABLE login (
  login_id INT AUTO_INCREMENT PRIMARY KEY,
  u_id INT,
  login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  login_type ENUM('otp','password'),
  login_status ENUM('success','failed'),
  FOREIGN KEY (u_id)
    REFERENCES users(u_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
");

/* ===== VEHICLE ===== */
$conn->query("
CREATE TABLE vehicle (
  v_id INT AUTO_INCREMENT PRIMARY KEY,
  u_id INT NOT NULL,
  v_num VARCHAR(20) UNIQUE,
  v_type ENUM('2_wheeler','4_wheeler'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (u_id)
    REFERENCES users(u_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
");

/* ===== SLOT ===== */
$conn->query("
CREATE TABLE slot (
  slot_id INT AUTO_INCREMENT PRIMARY KEY,
  slot_num VARCHAR(10) UNIQUE,
  slot_type ENUM('2_wheeler','4_wheeler') NOT NULL,
  slot_status ENUM('Available','Blocked') DEFAULT 'Available'
) ENGINE=InnoDB
");
/* ===== INSERT PARKING SLOTS ===== */
$conn->query("
INSERT IGNORE INTO slot (slot_num, slot_type) VALUES

-- A to C BLOCK → 2 WHEELER
('A1','2_wheeler'),('A2','2_wheeler'),('A3','2_wheeler'),('A4','2_wheeler'),
('A5','2_wheeler'),('A6','2_wheeler'),('A7','2_wheeler'),('A8','2_wheeler'),
('A9','2_wheeler'),('A10','2_wheeler'),('A11','2_wheeler'),('A12','2_wheeler'),

('B1','2_wheeler'),('B2','2_wheeler'),('B3','2_wheeler'),('B4','2_wheeler'),
('B5','2_wheeler'),('B6','2_wheeler'),('B7','2_wheeler'),('B8','2_wheeler'),
('B9','2_wheeler'),('B10','2_wheeler'),('B11','2_wheeler'),('B12','2_wheeler'),

('C1','2_wheeler'),('C2','2_wheeler'),('C3','2_wheeler'),('C4','2_wheeler'),
('C5','2_wheeler'),('C6','2_wheeler'),('C7','2_wheeler'),('C8','2_wheeler'),
('C9','2_wheeler'),('C10','2_wheeler'),('C11','2_wheeler'),('C12','2_wheeler'),

-- D to F BLOCK → 4 WHEELER
('D1','4_wheeler'),('D2','4_wheeler'),('D3','4_wheeler'),('D4','4_wheeler'),
('D5','4_wheeler'),('D6','4_wheeler'),('D7','4_wheeler'),('D8','4_wheeler'),
('D9','4_wheeler'),('D10','4_wheeler'),('D11','4_wheeler'),('D12','4_wheeler'),

('E1','4_wheeler'),('E2','4_wheeler'),('E3','4_wheeler'),('E4','4_wheeler'),
('E5','4_wheeler'),('E6','4_wheeler'),('E7','4_wheeler'),('E8','4_wheeler'),
('E9','4_wheeler'),('E10','4_wheeler'),('E11','4_wheeler'),('E12','4_wheeler'),

('F1','4_wheeler'),('F2','4_wheeler'),('F3','4_wheeler'),('F4','4_wheeler'),
('F5','4_wheeler'),('F6','4_wheeler'),('F7','4_wheeler'),('F8','4_wheeler'),
('F9','4_wheeler'),('F10','4_wheeler'),('F11','4_wheeler'),('F12','4_wheeler'),

('G1','4_wheeler'),('G2','4_wheeler'),('G3','4_wheeler'),('G4','4_wheeler'),
('G5','4_wheeler'),('G6','4_wheeler'),('G7','4_wheeler'),('G8','4_wheeler'),
('G9','4_wheeler'),('G10','4_wheeler'),('G11','4_wheeler'),('G12','4_wheeler')
");

/* ===== BOOKING ===== */
$conn->query("
CREATE TABLE booking (
  b_id INT AUTO_INCREMENT PRIMARY KEY,
  u_id INT,
  v_id INT,
  v_type ENUM('2_wheeler','4_wheeler'),   -- ✅ SNAPSHOT of vehicle type
  slot_id INT,
  start_time DATETIME,
  end_time DATETIME,
  total_amount DECIMAL(10,2),
  payment_status ENUM('paid','failed') DEFAULT 'paid',
  b_status ENUM('Active','Completed','Cancelled') DEFAULT 'Active',
  booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  cancelled_at DATETIME NULL,
  cancel_reason VARCHAR(255) NULL,
  refund_amount DECIMAL(10,2) DEFAULT 0,
  FOREIGN KEY (u_id)
    REFERENCES users(u_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (v_id)
    REFERENCES vehicle(v_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (slot_id)
    REFERENCES slot(slot_id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB
");

/* ===== PAYMENT ===== */
$conn->query("
CREATE TABLE payment (
  p_id INT AUTO_INCREMENT PRIMARY KEY,
  b_id INT,
  paid_amount DECIMAL(10,2),
  payment_type ENUM('advance'),
  mode ENUM('upi','card'),
  pay_status ENUM('paid','failed') DEFAULT 'paid',
  pay_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (b_id)
    REFERENCES booking(b_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
");

/* ===== RECEIPT ===== */
$conn->query("
CREATE TABLE receipt (
  rec_id INT AUTO_INCREMENT PRIMARY KEY,
  p_id INT,
  rec_no VARCHAR(30) UNIQUE,
  generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (p_id)
    REFERENCES payment(p_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
");

/* ===== FEEDBACK ===== */
$conn->query("
CREATE TABLE feedback (
  f_id INT AUTO_INCREMENT PRIMARY KEY,
  b_id INT,
  rating TINYINT CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (b_id)
    REFERENCES booking(b_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
");
/* ===== PRICING ===== */
$conn->query("
CREATE TABLE pricing (
  price_id INT AUTO_INCREMENT PRIMARY KEY,
  v_type ENUM('2_wheeler','4_wheeler') UNIQUE ,
  price_per_hour INT NOT NULL,
  price_per_day INT NOT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
");
/* refunds */
$conn->query("
CREATE TABLE refunds (
    refund_id INT AUTO_INCREMENT PRIMARY KEY,
    b_id INT UNIQUE,
    u_id INT,
    amount DECIMAL(10,2),
    refund_status ENUM('pending','processed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (b_id) REFERENCES booking(b_id) ON DELETE CASCADE,
    FOREIGN KEY (u_id) REFERENCES users(u_id) ON DELETE CASCADE
)
");
// Refund recipt table
$conn->query("
CREATE TABLE refund_receipt (
    refund_rec_id INT AUTO_INCREMENT PRIMARY KEY,
    refund_id INT UNIQUE,
    rec_no VARCHAR(30) UNIQUE,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (refund_id) REFERENCES refunds(refund_id) ON DELETE CASCADE
)");
echo '✅ DATABASE CREATED CORRECTLY';
$conn->close();
?>