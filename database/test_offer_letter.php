<?php
require_once __DIR__ . '/../config/config.php';

echo "=== Offer Letter Debug ===\n";

// Find an application
$app = fetchOne(executeQuery("SELECT id FROM hr_job_applications ORDER BY id DESC LIMIT 1"));
if (!$app) {
    echo "No applications found\n";
    exit(1);
}
$appId = (int)$app['id'];
echo "Using application ID: $appId\n";

try {
    $result = RecruitmentService::createOfferLetter($appId, [
        'offered_salary' => 5000,
        'start_date' => '2026-07-01',
        'expiry_date' => '2026-08-01',
        'status' => 'Draft',
    ], 1);
    print_r($result);
    if (!empty($result['offer_id'])) {
        executeQuery("DELETE FROM hr_offer_letters WHERE id = ?", 'i', [$result['offer_id']]);
        echo "Cleaned up test offer\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
