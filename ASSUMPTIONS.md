# Assumptions

| # | Gap in the brief | Choice | Why |
|---|---|---|---|
| 1 | How is a returning visitor identified? | By email (lower-cased). Only SHA-256(email + secret salt) is stored. | No passwords or login for a casual event; email is unique and stable. Hash means a database leak does not reveal addresses. Names are not unique, so they can't identify people. |
| 2 | Email ownership is not verified | Accepted as typed | Verification would add friction. Risk: someone typing another person's email gets their old photos displayed. Photos were already public, and every photo can be removed. |
| 3 | "Specific duration" | 8 s per photo, set by `SLIDE_SECONDS` in `.env` or `?d=` on the display URL (min 3 s) | Configurable per event. |
| 4 | "As soon as upload completes" | Display polls every 4 s; new photos jump the queue | Polling is simple and works on any smart-TV browser (no WebSocket server needed). |
| 5 | What plays when nobody is uploading | The display loops all visible photos | Keeps the screen alive. |
| 6 | Returning visitor playback | New photos first, then that visitor's older photos, labelled "From an earlier visit" | Matches the brief. |
| 7 | Allowed files | JPG, PNG, WebP; 5 MB each; 5 per upload; max 40 megapixels | Phone photos fit; limits protect the server. |
| 8 | Consent | Mandatory checkbox covering public display and the people in the photo; `consent_at` stored per photo | Photos of people are shown publicly. |
| 9 | Withdrawing consent | Delete button shown right after upload (secret token per photo). No admin panel. | Keeps scope small. Deleting also removes the file from disk. |
| 10 | Privacy of images | Re-encoded with GD, so EXIF (GPS, camera) is removed; filenames are random | Prevents location leaks and filename guessing. |
| 11 | Concurrent uploads | Random 128-bit filenames, DB transaction per upload, MySQL `ON DUPLICATE KEY` for visitor row | No two uploads can overwrite each other or create duplicate visitors. |
| 12 | Abuse | 3 uploads/minute per session. No content moderation or captcha. | Out of scope for a college project; listed as future work. |
| 13 | Users | Public, no admin accounts | Not required by the brief. |
