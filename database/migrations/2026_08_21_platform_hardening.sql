-- Platform hardening: notification state, integrity constraints, and useful indexes.
-- Run after weblogr.sql and the admin moderation migration.

ALTER TABLE notifications
    ADD COLUMN IF NOT EXISTS is_read TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE followers
    ADD UNIQUE KEY uq_followers_pair (blogger_id, follower_id);

ALTER TABLE profile
    ADD UNIQUE KEY uq_profile_user (user_id);

ALTER TABLE notifications
    ADD KEY idx_notifications_user_read (user_id, is_read, id);

ALTER TABLE blogs
    ADD KEY idx_blogs_user_date (user_id, created_date),
    ADD KEY idx_blogs_category_date (category, created_date);

ALTER TABLE comments
    ADD KEY idx_comments_blog_date (blog_id, comment_date);
