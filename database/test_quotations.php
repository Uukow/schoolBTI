<?php
require_once __DIR__ . '/../config/config.php';

$passed = 0; $failed = 0;
function ok($l, $c, $d = '') {
    global $passed, $failed;
    if ($c) { echo "[PASS] $l\n"; $passed++; }
    else { echo "[FAIL] $l" . ($d ? " — $d" : '') . "\n"; $failed++; }
}

echo "=== Quotation Module Tests ===\n\n";
global $conn;

foreach (['hr_quotations', 'hr_quotation_vendors', 'hr_quotation_items'] as $t) {
    $row = fetchOne(executeQuery("SHOW TABLES LIKE '" . $conn->real_escape_string($t) . "'"));
    ok("Table $t", !empty($row));
}

$cols = fetchAll(executeQuery("SHOW COLUMNS FROM hr_quotations LIKE 'is_public'"));
ok('Column is_public', count($cols) === 1);
$cols2 = fetchAll(executeQuery("SHOW COLUMNS FROM hr_quotations LIKE 'public_token'"));
ok('Column public_token', count($cols2) === 1);

$code = 'TEST-QUO-' . time();
try {
    $stmt = executeQuery(
        "INSERT INTO hr_quotations (quotation_no, title, description, requested_by, branch_id, required_by_date, total_estimated, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        'sssiisds',
        [$code, 'Test RFQ', 'Desc', null, null, '2026-08-01', 5000.0, 'Draft']
    );
    ok('Quotation INSERT', $stmt !== false);
    $q = fetchOne(executeQuery("SELECT id FROM hr_quotations WHERE quotation_no = ?", 's', [$code]));
    $qid = (int)($q['id'] ?? 0);
    ok('Quotation created', $qid > 0);

    $token = bin2hex(random_bytes(8));
    executeQuery(
        "UPDATE hr_quotations SET is_public=1, public_token=?, public_deadline=?, published_at=NOW() WHERE id=?",
        'ssi', [$token, '2026-09-01', $qid]
    );
    ok('Publish public', true);

    $stmt2 = executeQuery(
        "INSERT INTO hr_quotation_vendors (quotation_id, vendor_name, vendor_contact, quoted_amount, delivery_days, notes)
         VALUES (?, ?, ?, ?, ?, ?)",
        'issdis',
        [$qid, 'Acme Supplies', 'acme@test.com', 4800.0, 7, 'Test quote']
    );
    ok('Vendor INSERT', $stmt2 !== false);

    $stmtItem = executeQuery(
        "INSERT INTO hr_quotation_items (quotation_id, line_no, item_name, description, quantity, unit, unit_price, line_total)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        'iissdsdd',
        [$qid, 1, 'Test Item', 'Spec', 5.0, 'pcs', 100.0, 500.0]
    );
    ok('Item INSERT', $stmtItem !== false);

    $stmt3 = executeQuery(
        "UPDATE hr_quotations SET title=?, description=?, required_by_date=?, total_estimated=?, branch_id=?, status=? WHERE id=?",
        'sssdisi',
        ['Updated', 'D2', '2026-08-15', 5200.0, null, 'Pending_Approval', $qid]
    );
    ok('Quotation UPDATE bind', $stmt3 !== false);

    executeQuery("DELETE FROM hr_quotations WHERE id = ?", 'i', [$qid]);
    ok('Cleanup', true);
} catch (Throwable $e) {
    ok('Workflow', false, $e->getMessage());
}

$files = [
    'ajax/hr/get-quotations.php', 'ajax/hr/save-quotation.php',
    'ajax/hr/get-quotation-items.php', 'ajax/hr/get-quotation-vendors.php', 'ajax/hr/save-quotation-vendor.php',
    'ajax/public/submit-vendor-quote.php', 'modules/hr/quotations.php', 'quotation-portal.php'
];
foreach ($files as $f) {
    $out = []; $ec = 0;
    exec('php -l ' . escapeshellarg(ABSPATH . $f) . ' 2>&1', $out, $ec);
    ok("Syntax $f", $ec === 0, implode(' ', $out));
}

echo "\n=== Results: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
