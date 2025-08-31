<?php
// partials/theme-toggle.php — dark-only, no toggle button
?>
<style>
  /* Keep your dark overrides so the site stays dark-only */
  [data-theme="dark"]{
    --bg:#0f1115;
    --text:#f3f4f6;
    --muted:#9aa1ab;
    --line:#27303a;
    --card:#151a1f;
    color-scheme: dark;
  }
  [data-theme="dark"] body{ background:var(--bg) !important; color:var(--text) !important; }

  [data-theme="dark"] .about-card,
  [data-theme="dark"] .sheet,
  [data-theme="dark"] .item,
  [data-theme="dark"] .menu-categories,
  [data-theme="dark"] .category-block,
  [data-theme="dark"] .category-row,
  [data-theme="dark"] .category-content,
  [data-theme="dark"] .item-card {
    background: var(--card) !important;
    border-color: var(--line) !important;
    color: var(--text) !important;
  }

  /* Inline-style killers for any hardcoded whites */
  [data-theme="dark"] [style*="background:#fff"],
  [data-theme="dark"] [style*="background: #fff"],
  [data-theme="dark"] [style*="background:white"],
  [data-theme="dark"] [style*="background: white"],
  [data-theme="dark"] [style*="background:#fdfdfd"],
  [data-theme="dark"] [style*="background: #fdfdfd"],
  [data-theme="dark"] [style*="background:#f1f1f1"],
  [data-theme="dark"] [style*="background: #f1f1f1"],
  [data-theme="dark"] [style*="background:#eee"],
  [data-theme="dark"] [style*="background: #eee"]{
    background: var(--card) !important;
    color: var(--text) !important;
    border-color: var(--line) !important;
  }

  [data-theme="dark"] .open-banner{ background:#0f2a16 !important; color:#86efac !important; }
  [data-theme="dark"] .closed-banner{ background:#2a1212 !important; color:#fca5a5 !important; }
  [data-theme="dark"] .alert-note{ background:#2a1212 !important; color:#fca5a5 !important; }
  [data-theme="dark"] .badge{ background:#1b222b !important; border-color:var(--line) !important; color: var(--text) !important; }

  [data-theme="dark"] .btn{
    background:#1b222b !important;
    border-color: var(--line) !important;
    color: var(--text) !important;
  }
  [data-theme="dark"] .btn.primary{
    background: var(--primary,#f04f32) !important;
    border-color: var(--primary,#f04f32) !important;
    color:#fff !important;
  }

  [data-theme="dark"] .bottom-nav{
    background: var(--card) !important;
    border-top-color: var(--line) !important;
  }
  [data-theme="dark"] .bottom-nav .nav-item{ color: var(--text) !important; }

  /* Safety: if some layout still injects a toggle button, hide it */
  .theme-toggle{ display:none !important; }
</style>

<script>
(function(){
  // Force and persist dark; no button, no toggling
  var root = document.documentElement;
  if (root.getAttribute('data-theme') !== 'dark') {
    root.setAttribute('data-theme','dark');
  }
  try { localStorage.setItem('theme','dark'); } catch(e){}
})();
</script>
