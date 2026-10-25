-- Import once, after backing up the database.
ALTER TABLE app_invitation_codes ADD COLUMN revoked_at DATETIME NULL;
