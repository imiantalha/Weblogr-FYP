-- Run this migration only when upgrading an existing Weblogr database.
-- Fresh installations should import database/weblogr.sql instead.
ALTER TABLE users
    ADD COLUMN google_id VARCHAR(255) NULL,
    ADD UNIQUE KEY uq_users_google_id (google_id);
