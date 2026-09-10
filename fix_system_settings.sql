USE dummy_perpus;
ALTER TABLE system_settings CHANGE COLUMN `key_name` `key` VARCHAR(255) NOT NULL;
ALTER TABLE system_settings DROP INDEX system_settings_key_name_unique;
ALTER TABLE system_settings ADD UNIQUE KEY system_settings_key_unique (`key`);
