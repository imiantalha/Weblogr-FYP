-- Persistent rate limiting: replaces session-based throttling, which a new
-- session (fresh cookie jar, incognito tab, or scripted client that never
-- sends cookies) trivially bypasses.

CREATE TABLE IF NOT EXISTS rate_limits (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    limit_key VARCHAR(191) NOT NULL,
    attempt_count INT UNSIGNED NOT NULL DEFAULT 1,
    window_started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rate_limits_key (limit_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
