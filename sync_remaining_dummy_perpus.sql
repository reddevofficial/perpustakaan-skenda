USE dummy_perpus;

CREATE TABLE IF NOT EXISTS book_copies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    barcode VARCHAR(255) NOT NULL,
    `condition` VARCHAR(255) NOT NULL DEFAULT 'good',
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
    `key_name` VARCHAR(255) NOT NULL,
    value TEXT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'string',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY system_settings_key_name_unique (`key_name`)
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

INSERT IGNORE INTO users (
    id, name, email, password, remember_token,
    two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at,
    profile_photo_path, is_active, role_id, created_at, updated_at,
    username, email_verified_at, role, status
)
SELECT
    u.id,
    u.name,
    u.email,
    u.password,
    u.remember_token,
    NULL,
    NULL,
    NULL,
    NULL,
    CASE WHEN u.status = 'active' THEN 1 ELSE 0 END,
    CASE u.role
        WHEN 'super_admin' THEN 1
        WHEN 'staff' THEN 5
        ELSE 5
    END,
    u.created_at,
    u.updated_at,
    NULL,
    NULL,
    u.role,
    u.status
FROM perpustakaan_skenda.users u;

INSERT IGNORE INTO students (id, user_id, student_number, class, major, phone, address, status, joined_at, created_at, updated_at)
SELECT s.id, s.user_id, s.student_number, s.class, s.major, s.phone, s.address, s.status, s.joined_at, s.created_at, s.updated_at
FROM perpustakaan_skenda.students s;

INSERT IGNORE INTO categories (id, name, slug, description, is_active, created_at, updated_at)
SELECT c.id, c.name, c.slug, c.description, c.is_active, c.created_at, c.updated_at
FROM perpustakaan_skenda.categories c;

INSERT IGNORE INTO books (id, category_id, book_code, isbn, title, author, publisher, publication_year, stock, available_stock, shelf_location, cover, description, status, created_at, updated_at, deleted_at)
SELECT b.id, b.category_id, b.book_code, b.isbn, b.title, b.author, b.publisher, b.publication_year, b.stock, b.available_stock, b.shelf_location, b.cover, b.description, b.status, b.created_at, b.updated_at, b.deleted_at
FROM perpustakaan_skenda.books b;

INSERT IGNORE INTO book_copies (id, book_id, barcode, `condition`, status, shelf_location, notes, created_at, updated_at, deleted_at)
SELECT bc.id, bc.book_id, bc.barcode, bc.condition, bc.status, bc.shelf_location, bc.notes, bc.created_at, bc.updated_at, bc.deleted_at
FROM perpustakaan_skenda.book_copies bc;

INSERT IGNORE INTO loans (id, loan_code, student_id, approved_by, status, requested_at, approved_at, borrowed_at, due_at, returned_at, extension_count, notes, created_at, updated_at)
SELECT l.id, l.loan_code, l.student_id, l.approved_by, l.status, l.requested_at, l.approved_at, l.borrowed_at, l.due_at, l.returned_at, l.extension_count, l.notes, l.created_at, l.updated_at
FROM perpustakaan_skenda.loans l;

INSERT IGNORE INTO loan_items (id, loan_id, book_copy_id, created_at, updated_at)
SELECT li.id, li.loan_id, li.book_copy_id, li.created_at, li.updated_at
FROM perpustakaan_skenda.loan_items li;

INSERT IGNORE INTO reservations (id, reservation_code, student_id, book_id, queue_number, status, reserved_at, expires_at, notes, created_at, updated_at)
SELECT r.id, r.reservation_code, r.student_id, r.book_id, r.queue_number, r.status, r.reserved_at, r.expires_at, r.notes, r.created_at, r.updated_at
FROM perpustakaan_skenda.reservations r;

INSERT IGNORE INTO fines (id, loan_id, student_id, overdue_days, daily_rate, total_amount, status, paid_at, paid_by, notes, created_at, updated_at)
SELECT f.id, f.loan_id, f.student_id, f.overdue_days, f.daily_rate, f.total_amount, f.status, f.paid_at, f.paid_by, f.notes, f.created_at, f.updated_at
FROM perpustakaan_skenda.fines f;

INSERT IGNORE INTO system_settings (id, `key_name`, value, type, created_at, updated_at)
SELECT ss.id, ss.key, ss.value, ss.type, ss.created_at, ss.updated_at
FROM perpustakaan_skenda.system_settings ss;

INSERT IGNORE INTO inventory_logs (id, book_copy_id, user_id, condition, status, location, notes, created_at, updated_at)
SELECT il.id, il.book_copy_id, il.user_id, il.condition, il.status, il.location, il.notes, il.created_at, il.updated_at
FROM perpustakaan_skenda.inventory_logs il;

INSERT IGNORE INTO activity_logs (id, user_id, action, subject_type, subject_id, description, ip_address, created_at, updated_at)
SELECT al.id, al.user_id, al.action, al.subject_type, al.subject_id, al.description, al.ip_address, al.created_at, al.updated_at
FROM perpustakaan_skenda.activity_logs al;
