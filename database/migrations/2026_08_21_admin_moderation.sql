CREATE TABLE IF NOT EXISTS reports (
    report_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason VARCHAR(100) NOT NULL,
    details TEXT NULL,
    status ENUM('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reports_status_created (status, created_at),
    INDEX idx_reports_blog (blog_id),
    INDEX idx_reports_reporter (reporter_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS moderation_logs (
    log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    report_id INT UNSIGNED NULL,
    blog_id INT NULL,
    action VARCHAR(50) NOT NULL,
    notes VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_moderation_admin_created (admin_id, created_at),
    INDEX idx_moderation_report (report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
