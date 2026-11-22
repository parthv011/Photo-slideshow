<?php require __DIR__ . '/config.php'; session_write_close(); $secs = max(3, (int)($_GET['d'] ?? SLIDE_SECONDS)); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Snap Wall – display</title>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,800&family=DM+Sans:wght@500;700&display=swap" rel="stylesheet">
<style>
  :root { --night:#0F1226; --lamp:#FFD166; --coral:#FF5C8A; --ice:#E8ECFF; --dur:<?= $secs ?>s; }
  * { box-sizing:border-box; margin:0 }
  html,body { height:100%; background:var(--night); color:var(--ice); font-family:'DM Sans',sans-serif; overflow:hidden; cursor:none }
  .layer { position:absolute; inset:0; opacity:0; transition:opacity 1s ease }
  .layer.on { opacity:1 }
  .bg { background-size:cover; background-position:center; filter:blur(40px) brightness(.45); transform:scale(1.15) }
  .fg { background-size:contain; background-position:center; background-repeat:no-repeat }
  .caption { position:absolute; left:4vw; bottom:6vh; max-width:70vw; text-shadow:0 2px 20px #000a; opacity:0; transition:opacity .8s .4s }
  .caption.on { opacity:1 }
  .caption h2 { font:800 clamp(28px,4.5vw,72px)/1 'Bricolage Grotesque',sans-serif }
  .badge { display:inline-block; margin-bottom:1.2vh; padding:.4em 1em; border-radius:99px; background:var(--lamp); color:var(--night); font-weight:700; font-size:clamp(14px,1.6vw,26px) }
  .badge.new { background:var(--coral); color:#fff }
  .bar { position:absolute; left:0; bottom:0; height:6px; background:var(--lamp); width:0 }
  .bar.run { width:100%; transition:width var(--dur) linear }
  .empty { position:absolute; inset:0; display:grid; place-content:center; text-align:center; gap:1rem }
  .empty h1 { font:800 clamp(36px,6vw,96px)/1 'Bricolage Grotesque',sans-serif }
  .empty p { font-size:clamp(16px,2vw,32px); opacity:.8 }
  @media (prefers-reduced-motion:reduce){ .layer,.caption{transition:none} }
</style>
</head>
<body>
<div class="empty" id="empty"><h1>Your photo could be here.</h1><p>Scan the code or visit <b id="url"></b> to upload.</p></div>
<div class="layer bg" id="bgA"></div><div class="layer fg" id="fgA"></div>
<div class="layer bg" id="bgB"></div><div class="layer fg" id="fgB"></div>
<div class="caption" id="cap"><span class="badge" id="badge"></span><h2 id="who"></h2></div>
<div class="bar" id="bar"></div>
<script>
const SECS = <?= $secs ?>, $ = id => document.getElementById(id);
document.getElementById('url').textContent = location.origin + location.pathname.replace('slideshow.php','');
let all = [], lastId = null, priority = [], cursor = 0, flip = false, shown = new Set();

async function poll() {
  try {
    const list = await (await fetch('api.php')).json();
    const maxId = list.reduce((m, p) => Math.max(m, p.id), 0);
    if (lastId === null) lastId = maxId;                       // first load: don't treat the backlog as "new"
    const fresh = list.filter(p => p.id > lastId);
    if (fresh.length) {
      lastId = maxId;
      fresh.forEach(p => p.tag = 'new');
      const visitors = new Set(fresh.map(p => p.visitor));
      // returning visitor: queue their earlier photos right after the new ones
      const earlier = list.filter(p => visitors.has(p.visitor) && p.id <= maxId && !fresh.includes(p))
                          .map(p => ({...p, tag: 'back'}));
      priority.push(...fresh, ...earlier);
    }
    all = list;
  } catch (e) { /* network hiccup: keep playing what we have */ }
}

function next() {
  if (priority.length) return priority.shift();
  if (!all.length) return null;
  return all[(cursor++) % all.length];
}

function play() {
  // drop photos that were removed since they were queued
  const p = next();
  $('empty').style.display = p ? 'none' : 'grid';
  if (!p) return setTimeout(play, 3000);
  flip = !flip;
  const [inBg, inFg, outBg, outFg] = flip ? ['bgA','fgA','bgB','fgB'] : ['bgB','fgB','bgA','fgA'];
  const img = new Image();
  img.onload = () => {
    $(inBg).style.backgroundImage = $(inFg).style.backgroundImage = `url(${p.url})`;
    $(inBg).classList.add('on'); $(inFg).classList.add('on');
    $(outBg).classList.remove('on'); $(outFg).classList.remove('on');
    $('who').textContent = p.name;
    const b = $('badge');
    b.textContent = p.tag === 'new' ? (p.returning ? 'Welcome back!' : 'Just in') : p.tag === 'back' ? 'From an earlier visit' : 'Visitor photo';
    b.className = 'badge' + (p.tag === 'new' ? ' new' : '');
    $('cap').classList.add('on');
    const bar = $('bar'); bar.classList.remove('run'); void bar.offsetWidth; bar.classList.add('run');
    setTimeout(play, SECS * 1000);
  };
  img.onerror = () => setTimeout(play, 200);                   // skip broken/removed image fast
  img.src = p.url;
}
document.addEventListener('click', () => document.documentElement.requestFullscreen?.());
poll().then(play); setInterval(poll, 4000);
</script>
</body>
</html>
