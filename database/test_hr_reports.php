<?php
require_once __DIR__ . '/../config/config.php';

$passed = 0; $failed = 0;
function ok($l, $c, $d = '') {
    global $passed, $failed;
    if ($c) { echo "[PASS] $l\n"; $passed++; }
    else { echo "[FAIL] $l" . ($d ? " — $d" : '') . "\n"; $failed++; }
}

echo "=== HR Reports Module Tests ===\n\n";

$types = ['summary', 'headcount', 'employee_master', 'payroll', 'attendance', 'leave'];
foreach ($types as $type) {
    try {
        $report = HrReportService::generate([
            'type' => $type,
            'month' => date('Y-m'),
            'year' => (int)date('Y'),
            'department' => '',
            'status' => '',
            'branch_id' => null,
            'is_super_admin' => true,
            'user_branch_id' => null,
        ]);
        ok("Generate $type", !empty($report['title']) && isset($report['format']));
    } catch (Throwable $e) {
        ok("Generate $type", false, $e->getMessage());
    }
}

try {
    $summary = HrReportService::generate(['type' => 'summary', 'month' => date('Y-m'), 'is_super_admin' => true]);
    ok('Summary KPIs', !empty($summary['kpis']) && count($summary['kpis']) >= 4);
} catch (Throwable $e) {
    ok('Summary KPIs', false, $e->getMessage());
}

try {
    $html = HrReportService::renderPdfHtml([
        'title' => 'Test Report',
        'subtitle' => 'Unit Test',
        'type' => 'headcount',
        'format' => 'table',
        'columns' => [
            ['key' => 'department', 'label' => 'Department'],
            ['key' => 'headcount', 'label' => 'Count', 'align' => 'right'],
        ],
        'rows' => [['department' => 'IT', 'headcount' => 5]],
        'summary' => ['total_headcount' => 5],
        'generated_at' => date('Y-m-d H:i:s'),
    ], ['user_name' => 'Tester', 'branch_name' => 'Main']);
    ok('PDF HTML render', strpos($html, 'Test Report') !== false && strpos($html, 'IT') !== false);
} catch (Throwable $e) {
    ok('PDF HTML render', false, $e->getMessage());
}

ok('mPDF available', HrReportService::ensureMpdf());

$files = [
    'includes/services/hr/HrReportService.php',
    'ajax/hr/get-hr-reports.php',
    'ajax/hr/export-hr-report.php',
    'modules/hr/reports.php',
];
foreach ($files as $f) {
    $out = []; $ec = 0;
    exec('php -l ' . escapeshellarg(ABSPATH . $f) . ' 2>&1', $out, $ec);
    ok("Syntax $f", $ec === 0, implode(' ', $out));
}

echo "\n=== Results: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
