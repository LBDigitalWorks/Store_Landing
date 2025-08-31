<?php
// partials/theme-head.php
?>
<script>
(function () {
  try {
    var saved = localStorage.getItem('theme');
    var mode  = saved || 'dark';   // default is DARK now
    document.documentElement.setAttribute('data-theme', mode);
  } catch (e) {
    document.documentElement.setAttribute('data-theme', 'dark'); // fallback DARK
  }
})();
</script>

<!-- Site-wide i18n (loads on every page) -->
<script src="/websites/shop/assets/js/i18n.js" defer></script>
