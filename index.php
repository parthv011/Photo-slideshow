<?php
require __DIR__ . '/config.php';
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$maxMb = MAX_FILE_BYTES / 1048576;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Snap Wall – put your photo on the big screen</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar container py-3">
  <span class="brand"><i class="bi bi-camera-fill"></i> Snap Wall</span>
  <a class="btn btn-sm btn-outline-light rounded-pill" href="slideshow.php" target="_blank"><i class="bi bi-tv"></i> Open display</a>
</nav>

<main class="container pb-5">
  <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> shadow-sm" role="alert"><?= e($flash["msg"]) ?>
      <?php if (!empty($flash['extra']['photos'])): ?>
        <div class="removal mt-3">
          <small class="d-block mb-2">Changed your mind? You can remove any photo right now:</small>
          <div class="d-flex flex-wrap gap-2">
          <?php foreach ($flash['extra']['photos'] as $p): ?>
            <form method="post" action="delete.php" class="thumb-wrap">
              <input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="token" value="<?= e($p['token']) ?>">
              <img src="uploads/<?= e($p['file']) ?>" alt="Your uploaded photo">
              <button class="btn btn-sm btn-danger" title="Remove from display"><i class="bi bi-trash"></i> Remove</button>
            </form>
          <?php endforeach; ?></div>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="row align-items-center g-5 pt-lg-4">
    <div class="col-lg-6">
      <h1 class="display-title">Your photo,<br>on the big screen.</h1>
      <p class="lead-copy">Add your name and a few photos. They appear on the display within seconds. Come back another day and your earlier photos play again.</p>
      <ul class="facts list-unstyled">
        <li><i class="bi bi-lightning-charge-fill"></i> Live on screen right after upload</li>
        <li><i class="bi bi-arrow-repeat"></i> Same email next time = we show your earlier photos too</li>
        <li><i class="bi bi-shield-check"></i> Location data is stripped. You can remove a photo any time.</li>
      </ul>
    </div>

    <div class="col-lg-6">
      <form id="uploadForm" class="polaroid" method="post" action="upload.php" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf" value="<?= csrf() ?>">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= MAX_FILE_BYTES ?>">
        <label id="drop" class="dropzone" for="photos">
          <div id="previews" class="previews"></div>
          <div id="dropHint"><i class="bi bi-images"></i><strong>Tap or drop photos here</strong>
            <small>JPG, PNG or WebP · up to <?= MAX_FILES ?> photos · <?= $maxMb ?> MB each</small></div>
        </label>
        <input id="photos" class="visually-hidden" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
        <div id="clientError" class="text-danger small mb-2" role="alert"></div>

        <div class="row g-2">
          <div class="col-sm-6"><label class="form-label" for="name">Your name</label>
            <input class="form-control" id="name" name="name" maxlength="60" required autocomplete="name"></div>
          <div class="col-sm-6"><label class="form-label" for="email">Email (to recognise you)</label>
            <input class="form-control" id="email" type="email" name="email" required autocomplete="email"></div>
        </div>
        <div class="form-check my-3">
          <input class="form-check-input" type="checkbox" id="consent" name="consent" value="1" required>
          <label class="form-check-label small" for="consent">I agree my photos and name will be shown publicly on the display. Everyone in them has agreed too. I can remove a photo after uploading.</label>
        </div>
        <button id="send" class="btn btn-send w-100" type="submit">Show my photos</button>
        <p class="tiny mb-0 mt-2">We store only a one-way hash of your email, never the address itself.</p>
      </form>
    </div>
  </div>
</main>

<script>
const MAX = <?= MAX_FILES ?>, MAXB = <?= MAX_FILE_BYTES ?>, OK = ['image/jpeg','image/png','image/webp'];
const input = document.getElementById('photos'), prev = document.getElementById('previews'),
      hint = document.getElementById('dropHint'), err = document.getElementById('clientError'),
      drop = document.getElementById('drop'), form = document.getElementById('uploadForm');

function check(files) {
  if (files.length > MAX) return `Choose up to ${MAX} photos at a time.`;
  for (const f of files) {
    if (!OK.includes(f.type)) return `“${f.name}” is not a JPG, PNG or WebP image.`;
    if (f.size > MAXB) return `“${f.name}” is larger than ${MAXB/1048576} MB.`;
  }
  return '';
}
function show() {
  prev.innerHTML = ''; err.textContent = check(input.files);
  hint.style.display = input.files.length && !err.textContent ? 'none' : '';
  if (err.textContent) return;
  [...input.files].forEach((f, i) => {
    const im = new Image(); im.src = URL.createObjectURL(f); im.alt = f.name;
    im.style.setProperty('--r', (i % 2 ? 3 : -3) + 'deg'); prev.appendChild(im);
  });
}
input.addEventListener('change', show);
['dragover','dragenter'].forEach(t => drop.addEventListener(t, e => { e.preventDefault(); drop.classList.add('over'); }));
['dragleave','drop'].forEach(t => drop.addEventListener(t, e => { e.preventDefault(); drop.classList.remove('over'); }));
drop.addEventListener('drop', e => { input.files = e.dataTransfer.files; show(); });
form.addEventListener('submit', e => {
  if (!input.files.length) { e.preventDefault(); err.textContent = 'Choose at least one photo.'; return; }
  if (check(input.files)) { e.preventDefault(); return; }
  if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
  const b = document.getElementById('send'); b.disabled = true; b.textContent = 'Uploading…';
});
</script>
</body>
</html>
