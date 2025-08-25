<?php
// Timezone
date_default_timezone_set('Europe/London');

// Where your shop lives (keep trailing slash)
if (!defined('BASE_URL')) {
  define('BASE_URL', 'https://lbdigitalworks.com/websites/shop/');
}

// Admin folder name (change if yours is different)
if (!defined('ADMIN_PATH')) {
  define('ADMIN_PATH', 'Admin_CP/'); // e.g. 'admin/' if that's your folder
}
