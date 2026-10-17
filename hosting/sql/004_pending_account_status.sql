-- Required before any public registration.
-- Existing accounts remain active; newly created accounts must explicitly use pending.
ALTER TABLE app_users
  MODIFY COLUMN status ENUM('pending','active','disabled') NOT NULL DEFAULT 'pending';
