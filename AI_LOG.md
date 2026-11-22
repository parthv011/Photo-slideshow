# AI Log

## 1. Project request
**Prompt:** I uploaded the assignment PDF and asked: "make this website using PHP, Bootstrap and a phpMyAdmin database, create the best attractive website."
**AI output:** A PHP app with upload page, slideshow display, JSON API, MySQL schema and the required docs.
**Decision:** Accepted the structure. I checked that returning visitors are identified by hashed email, and that consent, file validation and upload limits are handled.

## 2. AI was wrong: placeholder .env
**Prompt:** Asked how to run the project.
**AI output:** The zip contained a .env file copied from .env.example, with placeholder values (your_db_user, your_db_password).
**How I noticed:** Clicking "Show my photos" showed "Something went wrong on our side."
**Fix:** I opened .env and put my real MySQL user and password. The upload then worked.

## 3. AI was wrong: GD extension not checked
**Prompt:** Clicked Submit on the upload form.
**AI output:** upload.php calls imagecreatefromstring(), but the code never checked that PHP's GD extension was enabled.
**How I noticed:** Fatal error: "Call to undefined function imagecreatefromstring()".
**Fix:** In php.ini I removed the semicolon from extension=gd and extension=exif, then restarted Apache.

## 4. Wrong URL
**Prompt:** Asked why the site showed "Not Found".
**AI output:** Explained that htdocs is already the server root, so the URL must be localhost/photo-slideshow/.
**Decision:** I used the correct URL and the upload page opened.

## 5. Git push failed
**Prompt:** Shared the terminal error from git push.
**AI output:** Said GitHub rejected an old saved login, and told me to remove it in Windows Credential Manager.
**How I fixed it:** I removed the old GitHub entry from 2025 and pushed again. The push worked.

## 6. AI was wrong: missing file in commit
**Prompt:** Asked for git commit commands.
**AI output:** The first command list did not include uploads/.htaccess, which blocks PHP files from running in the uploads folder.
**How I noticed:** The AI caught it later and added it to the command list. I checked it is on GitHub.
**Fix:** Added uploads/.htaccess in the upload-handling commit.