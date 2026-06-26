<?php
require_once __DIR__ . '/../config/config.php';

$passed = 0; $failed = 0;
function ok($l, $c, $d = '') {
    global $passed, $failed;
    if ($c) { echo "[PASS] $l\n"; $passed++; }
    else { echo "[FAIL] $l" . ($d ? " — $d" : '') . "\n"; $failed++; }
}

echo "=== Leave Calendar Tests ===\n\n";

try {
    $start = date('Y-m-01');
    $end = date('Y-m-t');
    $events = HrDashboardService::getLeaveCalendarEvents($start, $end, [
        'is_super_admin' => true,
        'show_leaves' => true,
        'show_holidays' => true,
    ]);
    ok('getLeaveCalendarEvents', is_array($events));

    $stats = HrDashboardService::getLeaveCalendarStats($events);
    ok('getLeaveCalendarStats', isset($stats['total_events']) && isset($stats['holidays']));

    if (!empty($events)) {
        $first = $events[0];
        ok('Event has type', !empty($first['type']));
        ok('Event has color', !empty($first['color']));
    } else {
        ok('Events array (empty ok)', true);
    }
} catch (Throwable $e) {
    ok('Service', false, $e->getMessage());
}

$files = [
    'includes/services/hr/HrDashboardService.php',
    'ajax/hr/get-leave-calendar.php',
    'modules/hr/leave-calendar.php',
];
foreach ($files as $f) {
    $out = []; $ec = 0;
    exec('php -l ' . escapeshellarg(ABSPATH . $f) . ' 2>&1', $out, $ec);
    ok("Syntax $f", $ec === 0, implode(' ', $out));
}

echo "\n=== Results: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
