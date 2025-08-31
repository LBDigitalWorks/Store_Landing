<?php
// admin/api/menu.php
// Simple file-based menu API (JSON). No DB required.
// Requires admin session via _guard.php. Returns JSON.

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// Protect the endpoint (comment the next line if your guard path differs)
require_once __DIR__ . '/../_guard.php';

if (php_sapi_name() !== 'cli') {
  // CORS not needed when called from same origin; ensure POST for writes.
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }
}

$DATA_DIR = realpath(__DIR__ . '/..') . '/data';
$FILE     = $DATA_DIR . '/menu.json';

// Ensure data dir
if (!is_dir($DATA_DIR)) {
  @mkdir($DATA_DIR, 0775, true);
}

// Initialize storage if missing
if (!file_exists($FILE)) {
  $initial = ['seq' => 1, 'categories' => []];
  @file_put_contents($FILE, json_encode($initial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

// Helpers
function read_store(string $file): array {
  $h = @fopen($file, 'r');
  if (!$h) return ['seq'=>1,'categories'=>[]];
  @flock($h, LOCK_SH);
  $raw = stream_get_contents($h);
  @flock($h, LOCK_UN); fclose($h);
  $data = json_decode($raw ?: '', true);
  return is_array($data) ? $data : ['seq'=>1,'categories'=>[]];
}
function write_store(string $file, array $data): bool {
  $h = @fopen($file, 'c+');
  if (!$h) return false;
  @flock($h, LOCK_EX);
  ftruncate($h, 0);
  $ok = fwrite($h, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !== false;
  fflush($h);
  @flock($h, LOCK_UN); fclose($h);
  return $ok;
}
function next_id(array &$store): int { $store['seq'] = max(1, (int)$store['seq']) + 1; return (int)$store['seq']; }
function ok($data=null){ echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit; }
function err($msg, $code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }

// Parse input
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $raw = file_get_contents('php://input');
  $input = json_decode($raw, true) ?: [];
}
$action = $input['action'] ?? ($_GET['action'] ?? 'list');

// Only admins
if (empty($_SESSION['is_admin']) && empty($_SESSION['admin_email'])) {
  err('Unauthorized', 401);
}

$store = read_store($FILE);
$cats  = &$store['categories'];

// Utilities to locate by id
$findCat = function($id) use (&$cats) {
  foreach ($cats as $i=>$c) if ((int)$c['id']===(int)$id) return [$i,$c];
  return [null,null];
};
$findItem = function($itemId) use (&$cats){
  foreach ($cats as $ci=>$c) {
    foreach ($c['items'] ?? [] as $ii=>$it) {
      if ((int)$it['id']===(int)$itemId) return [$ci,$ii,$it];
    }
  }
  return [null,null,null];
};
$findSize = function($sizeId) use (&$cats){
  foreach ($cats as $ci=>$c) {
    foreach ($c['items'] ?? [] as $ii=>$it) {
      foreach ($it['sizes'] ?? [] as $si=>$s) {
        if ((int)$s['id']===(int)$sizeId) return [$ci,$ii,$si,$s];
      }
    }
  }
  return [null,null,null,null];
};
$findGroup = function($groupId) use (&$cats){
  foreach ($cats as $ci=>$c) {
    foreach ($c['items'] ?? [] as $ii=>$it) {
      foreach ($it['option_groups'] ?? [] as $gi=>$g) {
        if ((int)$g['id']===(int)$groupId) return [$ci,$ii,$gi,$g];
      }
    }
  }
  return [null,null,null,null];
};

switch ($action) {
  case 'list':
    ok($cats);

  case 'create_category':
    $name = trim((string)($input['name'] ?? ''));
    if ($name==='') err('Name required');
    $id = next_id($store);
    $cats[] = ['id'=>$id,'name'=>$name,'active'=>1,'items'=>[]];
    write_store($FILE, $store) ?: err('Write failed', 500);
    ok($cats);

  case 'delete_category':
    $id = (int)($input['id'] ?? 0);
    foreach ($cats as $i=>$c) if ((int)$c['id']===$id){ array_splice($cats,$i,1); write_store($FILE,$store) ?: err('Write failed',500); ok($cats); }
    err('Category not found', 404);

  case 'create_item':
    $catId = (int)($input['category_id'] ?? 0);
    [$idx,$cat] = $findCat($catId);
    if ($idx===null) err('Category not found', 404);
    $id = next_id($store);
    $item = [
      'id'=>$id,
      'name'=>trim((string)($input['name'] ?? '')),
      'description'=>trim((string)($input['description'] ?? '')),
      'veg'=> (int)($input['veg'] ?? 0),
      'spicy'=> (int)($input['spicy'] ?? 0),
      'image_url'=>trim((string)($input['image_url'] ?? '')),
      'sizes'=>[],
      'option_groups'=>[]
    ];
    if ($item['name']==='') err('Item name required');
    $cats[$idx]['items'][] = $item;
    write_store($FILE,$store) ?: err('Write failed',500);
    ok($cats);

  case 'delete_item':
    $itemId = (int)($input['id'] ?? 0);
    [$ci,$ii,$it] = $findItem($itemId);
    if ($ci===null) err('Item not found',404);
    array_splice($cats[$ci]['items'],$ii,1);
    write_store($FILE,$store) ?: err('Write failed',500);
    ok($cats);

  case 'add_size':
    $itemId = (int)($input['item_id'] ?? 0);
    [$ci,$ii,$it] = $findItem($itemId);
    if ($ci===null) err('Item not found',404);
    $label = trim((string)($input['label'] ?? ''));
    $price = (float)($input['price'] ?? 0);
    if ($label==='') err('Label required');
    $sid = next_id($store);
    $cats[$ci]['items'][$ii]['sizes'][] = ['id'=>$sid,'label'=>$label,'price'=>$price];
    write_store($FILE,$store) ?: err('Write failed',500);
    ok($cats);

  case 'delete_size':
    $sizeId = (int)($input['id'] ?? 0);
    [$ci,$ii,$si,$s] = $findSize($sizeId);
    if ($ci===null) err('Size not found',404);
    array_splice($cats[$ci]['items'][$ii]['sizes'],$si,1);
    write_store($FILE,$store) ?: err('Write failed',500);
    ok($cats);

  case 'create_group':
    $itemId = (int)($input['item_id'] ?? 0);
    [$ci,$ii,$it] = $findItem($itemId);
    if ($ci===null) err('Item not found',404);
    $name = trim((string)($input['name'] ?? ''));
    if ($name==='') err('Group name required');
    $type = in_array(($input['type'] ?? 'multi'), ['single','multi'], true) ? $input['type'] : 'multi';
    $max  = (int)($input['max_select'] ?? 0);
    $gid = next_id($store);
    $cats[$ci]['items'][$ii]['option_groups'][] = [
      'id'=>$gid,'name'=>$name,'type'=>$type,'max_select'=>$max,'options'=>[]
    ];
    write_store($FILE,$store) ?: err('Write failed',500);
    ok($cats);

  case 'add_option':
    $groupId = (int)($input['group_id'] ?? 0);
    [$ci,$ii,$gi,$g] = $findGroup($groupId);
    if ($ci===null) err('Group not found',404);
    $name = trim((string)($input['name'] ?? ''));
    $delta = (float)($input['price_delta'] ?? 0);
    if ($name==='') err('Option name required');
    $oid = next_id($store);
    $cats[$ci]['items'][$ii]['option_groups'][$gi]['options'][] = ['id'=>$oid,'name'=>$name,'price_delta'=>$delta];
    write_store($FILE,$store) ?: err('Write failed',500);
    ok($cats);

  default:
    err('Unknown action');
}

