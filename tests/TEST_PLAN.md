# Manual Test Plan
Fill the **Result** column after running each test on your machine.

| # | Test | Steps | Expected | Result |
|---|---|---|---|---|
| 1 | Happy path | Upload 1 JPG with name + email + consent | Success message; photo on display within ~5 s | Pass |
| 2 | Returning visitor | Upload again with same email (different name case/spaces ok) | "Welcome back"; new then earlier photos play; `visit_count`=2 | Pass |
| 3 | No consent | Leave checkbox empty | Rejected with message | Pass |
| 4 | Too large | Upload 8 MB image | Rejected, nothing stored | not tested |
| 5 | Too many files | Select 6 photos | Rejected (client and server) | Pass |
| 6 | Fake image | Rename `test.php` to `test.jpg` and upload | Rejected: not a real image | Pass |
| 7 | GIF/PDF | Upload a PDF | Rejected | Pass |
| 8 | EXIF stripped | Upload GPS-tagged photo, inspect saved file | No EXIF data | not tested |
| 9 | XSS name | Name = `<script>alert(1)</script>` | Displayed as plain text everywhere | Pass |
| 10 | Concurrent | Two devices upload together | Both stored, unique filenames, no errors | not tested |
| 11 | Delete | Click Remove after upload | Photo disappears from display; file deleted | Pass |
| 12 | Rate limit | 4 uploads in a minute | 4th rejected | Pass |
| 13 | Empty display | Empty database | Shows "Your photo could be here." | not tested |
