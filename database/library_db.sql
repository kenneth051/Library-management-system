CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

CREATE TABLE IF NOT EXISTS users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS authors(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(60) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS categories(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS books(
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    author_id INT NOT NULL,
    category_id INT NOT NULL,
    publisher VARCHAR(120) DEFAULT NULL,
    year_published SMALLINT DEFAULT NULL,
    total_copies INT NOT NULL DEFAULT 1 CHECK (total_copies >= 1),
    available_copies INT NOT NULL DEFAULT 1 CHECK (available_copies >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES authors(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS book_loans(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    issued_by INT NOT NULL,
    loan_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status ENUM('borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'borrowed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (issued_by) REFERENCES users(id)
);

INSERT IGNORE INTO authors (id, name, country) VALUES
(1, 'Chinua Achebe', 'Nigeria'),
(2, 'Ngũgĩ wa Thiongo', 'Kenya');

INSERT IGNORE INTO categories (id, name, description) VALUES
(1, 'Fiction', 'Novels and stories'),
(2, 'Science', 'Science and technology');

INSERT IGNORE INTO books (id, isbn, title, author_id, category_id, total_copies, available_copies) VALUES
(1, '9780385474542', 'Things Fall Apart', 1, 1, 3, 3);

-- admin@library.com password: testadmin123
-- user@library.com password: testuser123
-- password_hash("testadmin123", PASSWORD_DEFAULT);
INSERT IGNORE INTO users (email, password_hash, full_name, is_admin) VALUES
('admin@library.com', '$2y$10$VzMTrnGAs1RFVSLPe/lIo.qJ7P9RWEU2xs89j0qtUpDES1oYDAyhe', 'Test Admin', TRUE),
('user@library.com', '$2y$10$BthDerhDR/VRTlSye37i9OimdIPpx.YoXQE3dKWXRoLRyvy9FZYsy', 'Test User', FALSE);
