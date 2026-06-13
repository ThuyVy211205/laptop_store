<?php
/**
 * One-time fix: re-insert Vietnamese product names with correct UTF-8 via PDO
 * Run once at: http://localhost/laptop_store/fix_encoding.php
 * Auto-deletes itself after success.
 */
require_once __DIR__ . '/config/co_so_du_lieu.php';

$fixes = [
    12 => 'Chuột có dây Aula AM104',
    13 => 'Chuột Apple Magic Mouse 3 2024',
    20 => 'Chuột Bluetooth Logitech Pebble M350S',
    21 => 'Chuột Gaming có dây Asus TUF M3 Gen 2',
];

$pdo = db()->getConnection();
$pdo->exec("SET NAMES utf8mb4");

// Apply fixes
$results = [];
foreach ($fixes as $id => $name) {
    $stmt = $pdo->prepare("UPDATE san_pham SET ten = ? WHERE id = ?");
    $ok   = $stmt->execute([$name, $id]);
    $results[] = "ID $id → " . ($ok ? "✓ OK: $name" : "✗ FAILED");
}

// Verify fixed rows
$fixed = $pdo->query(
    "SELECT id, ten AS name FROM san_pham WHERE id IN (12,13,20,21) ORDER BY id"
)->fetchAll();

// Scan ALL products for suspicious characters (cp850 corruption markers)
$allProducts = $pdo->query("SELECT id, ten AS name FROM san_pham ORDER BY id")->fetchAll();
$suspicious  = [];
foreach ($allProducts as $p) {
    // Box-drawing chars and mojibake common in cp850→utf8 double-encode
    if (preg_match('/[╗╔╛╚■└┘┐┌│─╒╕╘╙╞╡╢╣╤╥╦╧╨╩╪╫╬▀▄█░▒▓]/', $p['name'])
        || !mb_detect_encoding($p['name'], 'UTF-8', true) === false) {
        $suspicious[] = $p;
    }
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<style>body{font-family:monospace;font-size:14px;padding:24px;background:#f5f5f5}";
echo "h2{color:#1a1f36} .ok{color:#059669} .err{color:#dc2626} .warn{color:#d97706}";
echo "table{border-collapse:collapse;width:100%} td,th{padding:8px 12px;border:1px solid #ddd;text-align:left}";
echo "th{background:#e8ecf0} tr:nth-child(even){background:#fafafa}</style></head><body>";

echo "<h2>🔧 UTF-8 Encoding Fix</h2>";
echo "<h3>Fix Results</h3><pre>";
foreach ($results as $r) echo htmlspecialchars($r, ENT_QUOTES, 'UTF-8') . "\n";
echo "</pre>";

echo "<h3>Verified Fixed Names</h3><table><tr><th>ID</th><th>Name</th></tr>";
foreach ($fixed as $r) {
    echo "<tr><td>{$r['id']}</td><td>" . htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
}
echo "</table>";

if (!empty($suspicious)) {
    echo "<h3 class='warn'>⚠ Suspicious Names (possible encoding issues)</h3><table><tr><th>ID</th><th>Name (raw)</th></tr>";
    foreach ($suspicious as $r) {
        echo "<tr><td>{$r['id']}</td><td class='err'>" . htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='ok'>✓ No other suspicious names found in remaining products.</p>";
}

echo "<h3>All Product Names (verify manually)</h3><table><tr><th>ID</th><th>Name</th></tr>";
foreach ($allProducts as $p) {
    echo "<tr><td>{$p['id']}</td><td>" . htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
}
echo "</table>";

// Self-delete only when all 4 verified names look correct (contain 'u')
$allOk = count($fixed) === 4
    && str_contains($fixed[0]['name'], 'Chu')
    && str_contains($fixed[1]['name'], 'Chu')
    && str_contains($fixed[2]['name'], 'Chu')
    && str_contains($fixed[3]['name'], 'Chu');

if ($allOk) {
    unlink(__FILE__);
    echo "<p class='ok'><strong>✓ Script deleted. Tải lại trang chủ để kiểm tra.</strong></p>";
} else {
    echo "<p class='err'>✗ Fix chưa hoàn chỉnh — script giữ lại để debug.</p>";
}

echo "</body></html>";
