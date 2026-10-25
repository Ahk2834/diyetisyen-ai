-- Run only after a verified backup of the live database.
-- app_users is expected to already have google_sub and pending status.
ALTER TABLE app_users
  ADD COLUMN first_name VARCHAR(100) NULL,
  ADD COLUMN last_name VARCHAR(100) NULL;

CREATE TABLE app_invitation_codes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  used_by BIGINT UNSIGNED NULL,
  used_at DATETIME NULL,
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_invitation_hash (code_hash),
  UNIQUE KEY uq_invitation_used_by (used_by),
  CONSTRAINT fk_invitation_user FOREIGN KEY (used_by) REFERENCES app_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
