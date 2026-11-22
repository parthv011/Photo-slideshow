CREATE DATABASE IF NOT EXISTS photo_slideshow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE photo_slideshow;

CREATE TABLE IF NOT EXISTS visitors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  email_hash CHAR(64) NOT NULL UNIQUE,          -- SHA-256(email + salt); raw email is never stored
  visit_count INT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_visit_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS photos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  visitor_id INT UNSIGNED NOT NULL,
  filename CHAR(36) NOT NULL UNIQUE,            -- random name, e.g. 9f3c...jpg
  original_name VARCHAR(120) NOT NULL,
  size_bytes INT UNSIGNED NOT NULL,
  consent_at DATETIME NOT NULL,                 -- proof the visitor agreed to public display
  delete_token CHAR(32) NOT NULL,               -- lets the visitor remove the photo
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE CASCADE,
  INDEX idx_visible (is_visible, id)
) ENGINE=InnoDB;
