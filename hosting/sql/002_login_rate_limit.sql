-- Run once against the Diet AI database after review.
-- No personal or health information is stored here.
CREATE TABLE IF NOT EXISTS app_login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  subject_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_subject_time (subject_hash, attempted_at),
  KEY idx_login_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
