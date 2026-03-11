CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50),
    name VARCHAR(100),
    role ENUM('requestor', 'approver', 'admin'),
    system_assigned VARCHAR(100),
    email VARCHAR(100)
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requestor_id INT,
    system_name VARCHAR(100),
    access_type VARCHAR(50),
    remove_from VARCHAR(100) NULL,
    description TEXT,
    status ENUM('pending','approved','denied','served') DEFAULT 'pending',
    admin_status ENUM('pending','served') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT,
    sender_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
