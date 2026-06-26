<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }

    $canManage = hasRole(['Super Admin', 'Admin'])
        || (function_exists('canPerform') && canPerform('hr_reports', 'create'));

    if (!$canManage) {
        jsonResponse(false, 'Permission denied');
    }

    $currentUser = getCurrentUser();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $id = (int)($data['id'] ?? 0);

    $allowedStatus = ['Draft', 'Submitted', 'Acknowledged', 'Archived'];

    if ($id > 0 && !empty($data['status']) && empty($data['staff_id'])) {
        $status = sanitize($data['status']);
        if (!in_array($status, $allowedStatus, true)) {
            jsonResponse(false, 'Invalid status');
        }
        executeQuery("UPDATE hr_performance_reviews SET status = ? WHERE id = ?", 'si', [$status, $id]);
        logActivity($currentUser['id'], 'Update Performance Status', 'HR', "Review ID $id → $status");
        jsonResponse(true, 'Review status updated');
    }

    $staffId = (int)($data['staff_id'] ?? 0);
    $reviewPeriod = sanitize($data['review_period'] ?? '');
    $reviewDate = sanitize($data['review_date'] ?? date('Y-m-d'));
    $rating = isset($data['rating']) && $data['rating'] !== '' ? (float)$data['rating'] : null;
    $status = sanitize($data['status'] ?? 'Draft');
    $comments = sanitize($data['comments'] ?? '');
    $goals = sanitize($data['goals'] ?? '');
    $strengths = sanitize($data['strengths'] ?? '');
    $improvements = sanitize($data['improvements'] ?? '');
    $reviewerId = (int)($data['reviewer_id'] ?? $currentUser['id']);

    if (!in_array($status, $allowedStatus, true)) {
        $status = 'Draft';
    }

    $kpisJson = null;
    if (!empty($data['kpis']) && is_array($data['kpis'])) {
        $cleanKpis = [];
        foreach ($data['kpis'] as $k) {
            if (!is_array($k) || empty($k['name'])) {
                continue;
            }
            $cleanKpis[] = [
                'name' => sanitize($k['name']),
                'score' => max(0, min(5, (float)($k['score'] ?? 0))),
            ];
        }
        if ($cleanKpis) {
            $kpisJson = json_encode($cleanKpis);
            if ($rating === null) {
                $sum = array_sum(array_column($cleanKpis, 'score'));
                $rating = round($sum / count($cleanKpis), 1);
            }
        }
    }

    if ($id > 0) {
        if (!$staffId) {
            jsonResponse(false, 'Staff is required');
        }
        if ($reviewPeriod === '') {
            jsonResponse(false, 'Review period is required');
        }
        $stmt = executeQuery(
            "UPDATE hr_performance_reviews SET staff_id=?, reviewer_id=?, review_period=?, rating=?, comments=?,
             goals=?, strengths=?, improvements=?, kpis=?, status=?, review_date=? WHERE id=?",
            'iisdsssssssi',
            [$staffId, $reviewerId, $reviewPeriod, $rating, $comments, $goals, $strengths, $improvements, $kpisJson, $status, $reviewDate, $id]
        );
        if ($stmt) {
            logActivity($currentUser['id'], 'Update Performance Review', 'HR', "Review ID: $id");
            jsonResponse(true, 'Performance review updated');
        }
        jsonResponse(false, 'Failed to update review');
    }

    if (!$staffId) {
        jsonResponse(false, 'Staff is required');
    }
    if ($reviewPeriod === '') {
        jsonResponse(false, 'Review period is required');
    }

    $stmt = executeQuery(
        "INSERT INTO hr_performance_reviews (staff_id, reviewer_id, review_period, rating, comments, goals, strengths, improvements, kpis, status, review_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        'iisdsssssss',
        [$staffId, $reviewerId, $reviewPeriod, $rating, $comments, $goals, $strengths, $improvements, $kpisJson, $status, $reviewDate]
    );

    if ($stmt) {
        logActivity($currentUser['id'], 'Create Performance Review', 'HR', "Staff ID: $staffId, period: $reviewPeriod");
        jsonResponse(true, 'Performance review saved successfully');
    }

    global $conn;
    jsonResponse(false, 'Failed to save review: ' . ($conn->error ?? 'Unknown error'));
} catch (Throwable $e) {
    error_log('save-performance-review.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
