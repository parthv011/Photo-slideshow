# Architecture

## Stack and reasons
- **PHP + MySQL (phpMyAdmin)** – required for the course, runs on XAMPP and cheap hosting.
- **Bootstrap 5** – responsive form with little custom CSS; custom CSS gives the Snap Wall look.
- **Polling JSON (`api.php`)** – works on any TV browser, no extra server. Trade-off: up to 4 s delay and one small request per display every 4 s.
- **GD** – ships with PHP; used to re-encode, rotate by EXIF and resize.

## Data model
```mermaid
erDiagram
  VISITORS ||--o{ PHOTOS : uploads
  VISITORS { int id PK
    string name
    char email_hash UK
    int visit_count
    datetime created_at
    datetime last_visit_at }
  PHOTOS { int id PK
    int visitor_id FK
    char filename UK
    string original_name
    int size_bytes
    datetime consent_at
    char delete_token
    bool is_visible
    datetime created_at }
```

## Flow
```mermaid
sequenceDiagram
  participant V as Visitor phone
  participant S as upload.php
  participant DB as MySQL
  participant T as TV slideshow.php
  V->>S: name, email, consent, photos
  S->>S: CSRF, size, real MIME, pixels, rate limit
  S->>S: GD re-encode (strip EXIF), random filename
  S->>DB: transaction: upsert visitor, insert photos
  loop every 4 s
    T->>DB: api.php (visible photos)
  end
  T->>T: new ids first, then visitor's earlier photos, then loop
```

## Security notes
Prepared statements everywhere; output escaped with `e()`; CSRF token on POST forms; `uploads/.htaccess` blocks script execution; `.env` is git-ignored.
