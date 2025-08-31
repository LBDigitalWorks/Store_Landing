<?php
// footer.php — compact legal-only footer, padded above fixed bottom nav

// Build a correct href prefix (works in subfolders too)
$H = '';
if (defined('BASE_URL') && BASE_URL) {
  $H = rtrim(BASE_URL, '/'); // e.g. https://site.com/websites/shop
} else {
  // Fallback: current script folder (e.g. /websites/shop)
  $H = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
  if ($H === '') $H = '/';
}
?>
<style>
  :root { --bottom-nav-h: 84px; }
  body { padding-bottom: calc(var(--bottom-nav-h) + env(safe-area-inset-bottom) + 12px); }

  .site-footer { border-top: 1px solid #27272a; background: transparent; }
  .site-footer .wrap { max-width: 72rem; margin: 0 auto; padding: 16px 16px calc(18px + env(safe-area-inset-bottom)); }
  .site-footer h4 { margin: 0 0 8px; font-size: 0.9rem; font-weight: 700; color: #e4e4e7; }
  .site-footer ul { margin: 0; padding: 0; list-style: none; }
  .site-footer li { margin: 6px 0; }
  .site-footer .muted { color: #a1a1aa; font-size: 0.9rem; }

  .site-footer a { color: inherit; text-decoration: none; }
  .site-footer a:hover { text-decoration: underline; }

  .site-footer .bar {
    border-top: 1px solid #27272a; margin-top: 14px; padding-top: 12px;
    display: flex; flex-wrap: wrap; gap: 8px; justify-content: space-between;
    color: #8b8b93; font-size: 0.8rem;
  }

  .site-footer { padding-bottom: 8px; }
</style>

<footer class="site-footer">
  <div class="wrap">
    <div class="grid gap-8" style="display:grid;grid-template-columns:1fr 1fr;max-width:48rem;">
      <div>
        <h4>Legal</h4>
        <ul class="muted">
          <li><a href="<?= $H ?>/terms.php">Terms of Service</a></li>
          <li><a href="<?= $H ?>/privacy.php">Privacy Policy</a></li>
          <li><a href="<?= $H ?>/cookies.php">Cookie Policy</a></li>
         
          <li><a href="<?= $H ?>/licensing.php">Licensing &amp; Age Policy</a></li>
        </ul>
      </div>
      <div>
        <h4>Notices</h4>
        <ul class="muted">
          <li>© <?= date('Y') ?> <?= htmlspecialchars(defined('RESTAURANT_NAME') ? RESTAURANT_NAME : 'Your Restaurant') ?>. All rights reserved.</li>
          <li>VAT included where applicable.</li>
          <li>For allergies/dietary needs, please contact us before ordering.</li>
        </ul>
      </div>
    </div>

    <div class="bar">
      <span>Registered business details available on request.</span>
      <span>created by <a href="https://lbdigitalworks.com" target="_blank" rel="noopener">lbdigitalworks.com</a></span>
    </div>
  </div>
</footer>
</body>
</html>


