CREATE DATABASE IF NOT EXISTS pharmacy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pharmacy_db;

CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 fullname VARCHAR(120) NOT NULL,
 username VARCHAR(60) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('admin','pharmacist','attendant') NOT NULL DEFAULT 'attendant',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
 id INT AUTO_INCREMENT PRIMARY KEY,
 category_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE suppliers (
 id INT AUTO_INCREMENT PRIMARY KEY,
 supplier_name VARCHAR(150) NOT NULL,
 phone VARCHAR(30), email VARCHAR(120), address TEXT
) ENGINE=InnoDB;

CREATE TABLE medicines (
 id INT AUTO_INCREMENT PRIMARY KEY,
 medicine_name VARCHAR(150) NOT NULL,
 category_id INT NULL,
 supplier_id INT NULL,
 batch_number VARCHAR(80),
 quantity INT NOT NULL DEFAULT 0,
 reorder_level INT NOT NULL DEFAULT 10,
 buying_price DECIMAL(10,2) NOT NULL DEFAULT 0,
 selling_price DECIMAL(10,2) NOT NULL DEFAULT 0,
 expiry_date DATE NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_med_category FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
 CONSTRAINT fk_med_supplier FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
 INDEX idx_med_name(medicine_name), INDEX idx_med_expiry(expiry_date)
) ENGINE=InnoDB;

CREATE TABLE customers (
 id INT AUTO_INCREMENT PRIMARY KEY,
 fullname VARCHAR(150) NOT NULL,
 phone VARCHAR(30), email VARCHAR(120), address TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE prescriptions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 customer_id INT NOT NULL,
 pharmacist_id INT NOT NULL,
 prescription_date DATE NOT NULL,
 notes TEXT,
 status ENUM('pending','verified','dispensed','rejected') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(customer_id) REFERENCES customers(id),
 FOREIGN KEY(pharmacist_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE prescription_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 prescription_id INT NOT NULL,
 medicine_id INT NOT NULL,
 quantity INT NOT NULL,
 dosage VARCHAR(150),
 FOREIGN KEY(prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
 FOREIGN KEY(medicine_id) REFERENCES medicines(id)
) ENGINE=InnoDB;

CREATE TABLE sales (
 id INT AUTO_INCREMENT PRIMARY KEY,
 customer_id INT NULL,
 user_id INT NOT NULL,
 total_amount DECIMAL(10,2) NOT NULL,
 payment_method ENUM('cash','mobile_money','card') NOT NULL DEFAULT 'cash',
 sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
 FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE sale_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 sale_id INT NOT NULL,
 medicine_id INT NOT NULL,
 quantity INT NOT NULL,
 unit_price DECIMAL(10,2) NOT NULL,
 FOREIGN KEY(sale_id) REFERENCES sales(id) ON DELETE CASCADE,
 FOREIGN KEY(medicine_id) REFERENCES medicines(id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
 id INT AUTO_INCREMENT PRIMARY KEY,
 message TEXT NOT NULL,
 notification_type VARCHAR(50) NOT NULL,
 status ENUM('unread','read') NOT NULL DEFAULT 'unread',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO categories(category_name) VALUES
('Pain Relief'),('Antibiotics'),('Vitamins'),('Allergy'),('Other');
