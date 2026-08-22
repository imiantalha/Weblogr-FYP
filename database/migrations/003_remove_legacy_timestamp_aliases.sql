-- Remove generated compatibility timestamp aliases introduced by the previous normalization migration.
-- Run this once on databases that already applied 002_database_normalization.sql.

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE blogs DROP COLUMN IF EXISTS created_date;
ALTER TABLE comments DROP COLUMN IF EXISTS comment_date;

SET FOREIGN_KEY_CHECKS = 1;
