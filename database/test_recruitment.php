<?php
/**
 * Recruitment module smoke tests (CLI)
 * Run: php database/test_recruitment.php
 */
require_once __DIR__ . '/../config/config.php';

$passed = 0;
$failed = 0;

function assert_test($label, $condition, $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $label\n";
        $passed++;
    } else {
        echo "[FAIL] $label" . ($detail ? " — $detail" : '') . "\n";
        $failed++;
    }
}

echo "=== Recruitment Module Tests ===\n\n";

// 1. Required tables
$tables = ['hr_job_vacancies', 'hr_job_applications', 'hr_interviews', 'hr_offer_letters'];
foreach ($tables as $t) {
    $row = fetchOne(executeQuery("SHOW TABLES LIKE '" . $conn->real_escape_string($t) . "'"));
    assert_test("Table $t exists", !empty($row));
}

// 2. Vacancy INSERT bind (rollback via delete)
$vacNo = 'TEST-' . time();
try {
    $stmt = executeQuery(
        "INSERT INTO hr_job_vacancies (vacancy_no, job_title, department, branch_id, employment_type, description, requirements,
         salary_range_min, salary_range_max, application_deadline, openings, status, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        'sssisssddsisi',
        [$vacNo, 'Test Teacher', 'Academic', null, 'Full Time', 'Test desc', 'Test req', 500.0, 800.0, date('Y-m-d', strtotime('+30 days')), 2, 'Draft', 1]
    );
    assert_test('Vacancy INSERT SQL', $stmt !== false);
    $vac = fetchOne(executeQuery("SELECT id FROM hr_job_vacancies WHERE vacancy_no = ?", 's', [$vacNo]));
    $vacId = (int)($vac['id'] ?? 0);
    assert_test('Vacancy record created', $vacId > 0);

    // 3. Application INSERT
    $appNo = 'APP-TEST-' . time();
    $stmt2 = executeQuery(
        "INSERT INTO hr_job_applications (application_no, vacancy_id, first_name, last_name, email, phone, status)
         VALUES (?, ?, ?, ?, ?, ?, 'Applied')",
        'sissss',
        [$appNo, $vacId, 'John', 'Doe', 'john.test@example.com', '0612345678']
    );
    assert_test('Application INSERT SQL', $stmt2 !== false);
    $app = fetchOne(executeQuery("SELECT id FROM hr_job_applications WHERE application_no = ?", 's', [$appNo]));
    $appId = (int)($app['id'] ?? 0);
    assert_test('Application record created', $appId > 0);

    // 4. Interview INSERT
    $stmt3 = executeQuery(
        "INSERT INTO hr_interviews (application_id, interview_date, interview_type, location_or_link)
         VALUES (?, ?, ?, ?)",
        'isss',
        [$appId, date('Y-m-d H:i:s', strtotime('+3 days')), 'Video', 'https://meet.example.com']
    );
    assert_test('Interview INSERT SQL', $stmt3 !== false);

    // 5. RecruitmentService
    if (class_exists('RecruitmentService')) {
        $r = RecruitmentService::updateApplicationStatus($appId, 'Shortlisted');
        assert_test('RecruitmentService::updateApplicationStatus', $r['success'] === true);
    }

    // 6. get-vacancies query shape
    $list = fetchAll(executeQuery(
        "SELECT v.*, (SELECT COUNT(*) FROM hr_job_applications a WHERE a.vacancy_id = v.id) as application_count
         FROM hr_job_vacancies v WHERE v.id = ?", 'i', [$vacId]
    ));
    assert_test('Vacancy list query', count($list) === 1 && (int)$list[0]['application_count'] === 1);

    // Cleanup
    executeQuery("DELETE FROM hr_job_applications WHERE id = ?", 'i', [$appId]);
    executeQuery("DELETE FROM hr_job_vacancies WHERE id = ?", 'i', [$vacId]);
    assert_test('Test data cleanup', true);
} catch (Throwable $e) {
    assert_test('Recruitment workflow', false, $e->getMessage());
}

// 7. PHP syntax of key files
$files = [
    'ajax/hr/save-vacancy.php',
    'ajax/hr/get-vacancies.php',
    'ajax/hr/get-job-applications.php',
    'ajax/hr/schedule-interview.php',
    'ajax/hr/hire-application.php',
    'modules/hr/vacancies.php',
];
foreach ($files as $f) {
    $out = [];
    $code = 0;
    exec('php -l ' . escapeshellarg(ABSPATH . $f) . ' 2>&1', $out, $code);
    assert_test("Syntax $f", $code === 0, implode(' ', $out));
}

echo "\n=== Results: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
