USE dummy_perpus;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS username VARCHAR(255) NULL AFTER name,
  ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL AFTER email,
  ADD COLUMN IF NOT EXISTS role VARCHAR(255) NOT NULL DEFAULT 'student' AFTER password,
  ADD COLUMN IF NOT EXISTS status VARCHAR(255) NOT NULL DEFAULT 'active' AFTER role;

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY categories_name_unique (name),
    UNIQUE KEY categories_slug_unique (slug),
    KEY categories_is_active_index (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    student_number VARCHAR(255) NOT NULL,
    class VARCHAR(255) NULL,
    major VARCHAR(255) NULL,
    phone VARCHAR(255) NULL,
    address TEXT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'active',
    joined_at DATE NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY students_user_id_unique (user_id),
    UNIQUE KEY students_student_number_unique (student_number),
    KEY students_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS books (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id BIGINT UNSIGNED NULL,
    book_code VARCHAR(255) NOT NULL,
    isbn VARCHAR(255) NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    publisher VARCHAR(255) NULL,
    publication_year SMALLINT UNSIGNED NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    available_stock INT UNSIGNED NOT NULL DEFAULT 0,
    shelf_location VARCHAR(255) NULL,
    cover VARCHAR(255) NULL,
    description TEXT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY books_book_code_unique (book_code),
    KEY books_category_id_foreign (category_id),
    KEY books_isbn_index (isbn),
    KEY books_title_index (title),
    KEY books_author_index (author),
    KEY books_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS book_copies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    barcode VARCHAR(255) NOT NULL,
    condition VARCHAR(255) NOT NULL DEFAULT 'good',
    status VARCHAR(255) NOT NULL DEFAULT 'available',
    shelf_location VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY book_copies_barcode_unique (barcode),
    KEY book_copies_book_id_foreign (book_id),
    KEY book_copies_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loans (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    loan_code VARCHAR(255) NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    approved_by BIGINT UNSIGNED NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    requested_at TIMESTAMP NULL DEFAULT NULL,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    borrowed_at TIMESTAMP NULL DEFAULT NULL,
    due_at DATE NULL,
    returned_at TIMESTAMP NULL DEFAULT NULL,
    extension_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY loans_loan_code_unique (loan_code),
    KEY loans_student_id_foreign (student_id),
    KEY loans_approved_by_foreign (approved_by),
    KEY loans_status_index (status),
    KEY loans_due_at_index (due_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loan_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    loan_id BIGINT UNSIGNED NOT NULL,
    book_copy_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY loan_items_loan_id_book_copy_id_unique (loan_id, book_copy_id),
    KEY loan_items_loan_id_foreign (loan_id),
    KEY loan_items_book_copy_id_foreign (book_copy_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reservations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reservation_code VARCHAR(255) NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    book_id BIGINT UNSIGNED NOT NULL,
    queue_number INT UNSIGNED NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'waiting',
    reserved_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY reservations_reservation_code_unique (reservation_code),
    KEY reservations_student_id_foreign (student_id),
    KEY reservations_book_id_foreign (book_id),
    KEY reservations_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fines (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    loan_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    overdue_days INT UNSIGNED NOT NULL DEFAULT 0,
    daily_rate BIGINT UNSIGNED NOT NULL DEFAULT 0,
    total_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(255) NOT NULL DEFAULT 'unpaid',
    paid_at TIMESTAMP NULL DEFAULT NULL,
    paid_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY fines_loan_id_unique (loan_id),
    KEY fines_student_id_foreign (student_id),
    KEY fines_paid_by_foreign (paid_by),
    KEY fines_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    key_name VARCHAR(255) NOT NULL,
    value TEXT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'string',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY system_settings_key_name_unique (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    book_copy_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    condition VARCHAR(255) NULL,
    status VARCHAR(255) NULL,
    location VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY inventory_logs_book_copy_id_foreign (book_copy_id),
    KEY inventory_logs_user_id_foreign (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(255) NOT NULL,
    subject_type VARCHAR(255) NULL,
    subject_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY activity_logs_user_id_foreign (user_id),
    KEY activity_logs_subject_type_subject_id_index (subject_type, subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (
    id, name, email, password, remember_token,
    two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at,
    profile_photo_path, is_active, role_id, created_at, updated_at,
    username, email_verified_at, role, status
)
SELECT
    id + 2, name, email, password, remember_token,
    NULL, NULL, NULL,
    NULL, CASE WHEN status = 'active' THEN 1 ELSE 0 END, 5, created_at, updated_at,
    NULL, NULL, role, status
FROM perpustakaan_skenda.users
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    email = VALUES(email),
    password = VALUES(password),
    remember_token = VALUES(remember_token),
    is_active = VALUES(is_active),
    role_id = VALUES(role_id),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at),
    username = VALUES(username),
    email_verified_at = VALUES(email_verified_at),
    role = VALUES(role),
    status = VALUES(status);

INSERT INTO students (id, user_id, student_number, class, major, phone, address, status, joined_at, created_at, updated_at)
SELECT
    s.id,
    s.user_id + 2,
    s.student_number,
    s.class,
    s.major,
    s.phone,
    s.address,
    s.status,
    s.joined_at,
    s.created_at,
    s.updated_at
FROM perpustakaan_skenda.students s
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    student_number = VALUES(student_number),
    class = VALUES(class),
    major = VALUES(major),
    phone = VALUES(phone),
    address = VALUES(address),
    status = VALUES(status),
    joined_at = VALUES(joined_at),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

INSERT INTO categories (id, name, slug, description, is_active, created_at, updated_at)
SELECT id, name, slug, description, is_active, created_at, updated_at
FROM perpustakaan_skenda.categories
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    slug = VALUES(slug),
    description = VALUES(description),
    is_active = VALUES(is_active),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

INSERT INTO books (id, category_id, book_code, isbn, title, author, publisher, publication_year, stock, available_stock, shelf_location, cover, description, status, created_at, updated_at, deleted_at)
SELECT id, category_id, book_code, isbn, title, author, publisher, publication_year, stock, available_stock, shelf_location, cover, description, status, created_at, updated_at, deleted_at
FROM perpustakaan_skenda.books
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id),
    book_code = VALUES(book_code),
    isbn = VALUES(isbn),
    title = VALUES(title),
    author = VALUES(author),
    publisher = VALUES(publisher),
    publication_year = VALUES(publication_year),
    stock = VALUES(stock),
    available_stock = VALUES(available_stock),
    shelf_location = VALUES(shelf_location),
    cover = VALUES(cover),
    description = VALUES(description),
    status = VALUES(status),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at),
    deleted_at = VALUES(deleted_at);

INSERT INTO book_copies (id, book_id, barcode, condition, status, shelf_location, notes, created_at, updated_at, deleted_at)
SELECT id, book_id, barcode, condition, status, shelf_location, notes, created_at, updated_at, deleted_at
FROM perpustakaan_skenda.book_copies
ON DUPLICATE KEY UPDATE
    book_id = VALUES(book_id),
    barcode = VALUES(barcode),
    condition = VALUES(condition),
    status = VALUES(status),
    shelf_location = VALUES(shelf_location),
    notes = VALUES(notes),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at),
    deleted_at = VALUES(deleted_at);

INSERT INTO loans (id, loan_code, student_id, approved_by, status, requested_at, approved_at, borrowed_at, due_at, returned_at, extension_count, notes, created_at, updated_at)
SELECT id, loan_code, student_id, approved_by, status, requested_at, approved_at, borrowed_at, due_at, returned_at, extension_count, notes, created_at, updated_at
FROM perpustakaan_skenda.loans
ON DUPLICATE KEY UPDATE
    loan_code = VALUES(loan_code),
    student_id = VALUES(student_id),
    approved_by = VALUES(approved_by),
    status = VALUES(status),
    requested_at = VALUES(requested_at),
    approved_at = VALUES(approved_at),
    borrowed_at = VALUES(borrowed_at),
    due_at = VALUES(due_at),
    returned_at = VALUES(returned_at),
    extension_count = VALUES(extension_count),
    notes = VALUES(notes),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

INSERT INTO loan_items (id, loan_id, book_copy_id, created_at, updated_at)
SELECT id, loan_id, book_copy_id, created_at, updated_at
FROM perpustakaan_skenda.loan_items
ON DUPLICATE KEY UPDATE
    loan_id = VALUES(loan_id),
    book_copy_id = VALUES(book_copy_id),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

INSERT INTO reservations (id, reservation_code, student_id, book_id, queue_number, status, reserved_at, expires_at, notes, created_at, updated_at)
SELECT id, reservation_code, student_id, book_id, queue_number, status, reserved_at, expires_at, notes, created_at, updated_at
FROM perpustakaan_skenda.reservations
ON DUPLICATE KEY UPDATE
    reservation_code = VALUES(reservation_code),
    student_id = VALUES(student_id),
    book_id = VALUES(book_id),
    queue_number = VALUES(queue_number),
    status = VALUES(status),
    reserved_at = VALUES(reserved_at),
    expires_at = VALUES(expires_at),
    notes = VALUES(notes),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

INSERT INTO fines (id, loan_id, student_id, overdue_days, daily_rate, total_amount, status, paid_at, paid_by, notes, created_at, updated_at)
SELECT id, loan_id, student_id, overdue_days, daily_rate, total_amount, status, paid_at, paid_by, notes, created_at, updated_at
FROM perpustakaan_skenda.fines
ON DUPLICATE KEY UPDATE
    loan_id = VALUES(loan_id),
    student_id = VALUES(student_id),
    overdue_days = VALUES(overdue_days),
    daily_rate = VALUES(daily_rate),
    total_amount = VALUES(total_amount),
    status = VALUES(status),
    paid_at = VALUES(paid_at),
    paid_by = VALUES(paid_by),
    notes = VALUES(notes),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

INSERT INTO system_settings (id, key_name, value, type, created_at, updated_at)
SELECT id, key, value, type, created_at, updated_at
FROM perpustakaan_skenda.system_settings
ON DUPLICATE KEY UPDATE
    key_name = VALUES(key_name),
    value = VALUES(value),
    type = VALUES(type),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

INSERT INTO inventory_logs (id, book_copy_id, user_id, condition, status, location, notes, created_at, updated_at)
SELECT id, book_copy_id, user_id, condition, status, location, notes, created_at, updated_at
FROM perpustakaan_skenda.inventory_logs
ON DUPLICATE KEY UPDATE
    book_copy_id = VALUES(book_copy_id),
    user_id = VALUES(user_id),
    condition = VALUES(condition),
    status = VALUES(status),
    location = VALUES(location),
    notes = VALUES(notes),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

INSERT INTO activity_logs (id, user_id, action, subject_type, subject_id, description, ip_address, created_at, updated_at)
SELECT id, user_id, action, subject_type, subject_id, description, ip_address, created_at, updated_at
FROM perpustakaan_skenda.activity_logs
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    action = VALUES(action),
    subject_type = VALUES(subject_type),
    subject_id = VALUES(subject_id),
    description = VALUES(description),
    ip_address = VALUES(ip_address),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);
