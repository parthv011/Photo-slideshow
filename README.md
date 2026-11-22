# Snap Wall – Visitor Photo Slideshow

Visitors enter a name and email, tick a consent box and upload photos. Each photo appears on a TV/computer display
(`slideshow.php`) within ~4 seconds and plays for a set duration. A returning visitor's earlier photos play right after the new ones.

**Tech stack:** PHP 8.1+, MySQL/MariaDB (phpMyAdmin), Bootstrap 5, vanilla JS, GD for image processing.
**Deployed link:** Not deployed. Runs locally on XAMPP. Repository: https://github.com/parthv011/Photo-slideshow

## Setup (XAMPP / WAMP / any PHP host)
1. Copy this folder to `htdocs/photo-slideshow`.
2. Open phpMyAdmin → *Import* → choose `schema.sql` → Go (creates DB `photo_slideshow` and tables).
3. Copy `.env.example` to `.env` and set `DB_USER`, `DB_PASS` and a long random `EMAIL_SALT`.
4. In `php.ini` make sure `extension=gd` and `extension=exif` are enabled, and `upload_max_filesize` ≥ 6M, `post_max_size` ≥ 30M. Restart Apache.
5. Make sure `uploads/` is writable by the web server.
6. Visitors: `http://localhost/photo-slideshow/` · TV: `http://localhost/photo-slideshow/slideshow.php` (click once for fullscreen; `?d=10` sets seconds per photo).

For a TV on the same Wi-Fi, use the computer's LAN IP instead of `localhost`.

## Files
`index.php` upload page · `upload.php` validation + storage · `api.php` JSON feed · `slideshow.php` display · `delete.php` photo removal · `schema.sql` database.
See ARCHITECTURE.md, ASSUMPTIONS.md, AI_LOG.md and `tests/TEST_PLAN.md`.
