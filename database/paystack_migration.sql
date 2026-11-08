USE pharmacy_db;
CREATE TABLE IF NOT EXISTS paystack_payments (
 id INT AUTO_INCREMENT PRIMARY KEY,
 reference VARCHAR(100) NOT NULL UNIQUE,
 user_id INT NOT NULL,
 customer_id INT NULL,
 medicine_id INT NOT NULL,
 quantity INT NOT NULL,
 amount DECIMAL(10,2) NOT NULL,
 currency CHAR(3) NOT NULL DEFAULT 'GHS',
 customer_email VARCHAR(190) NOT NULL,
 status ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
 sale_id INT NULL,
 gateway_response VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id),
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
 FOREIGN KEY(medicine_id) REFERENCES medicines(id),
 FOREIGN KEY(sale_id) REFERENCES sales(id) ON DELETE SET NULL,
 INDEX idx_paystack_status(status)
) ENGINE=InnoDB;
