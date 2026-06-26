<?php
ob_start();
require_once '../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();

require_once __DIR__ . '/_auth.php';
require_once ABSPATH . 'includes/services/hr/RecruitmentService.php';

try {
    $auth = hrApiAuth();
    if (!in_array($auth['role'], ['Super Admin', 'Admin'])) {
        sendApiResponse(false, 'Permission denied', null, 403);
    }

    $resource = sanitize($_GET['resource'] ?? 'vacancies');

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($resource) {
            case 'applications':
                $rows = fetchAll(executeQuery(
                    "SELECT a.*, v.job_title FROM hr_job_applications a
                     INNER JOIN hr_job_vacancies v ON a.vacancy_id = v.id ORDER BY a.applied_at DESC"
                ));
                sendApiResponse(true, 'Applications', $rows);
            case 'interviews':
                $rows = fetchAll(executeQuery(
                    "SELECT i.*, a.first_name, a.last_name, v.job_title FROM hr_interviews i
                     INNER JOIN hr_job_applications a ON i.application_id = a.id
                     INNER JOIN hr_job_vacancies v ON a.vacancy_id = v.id ORDER BY i.interview_date DESC"
                ));
                sendApiResponse(true, 'Interviews', $rows);
            case 'offers':
                $rows = fetchAll(executeQuery(
                    "SELECT o.*, a.first_name, a.last_name, v.job_title FROM hr_offer_letters o
                     INNER JOIN hr_job_applications a ON o.application_id = a.id
                     INNER JOIN hr_job_vacancies v ON a.vacancy_id = v.id ORDER BY o.created_at DESC"
                ));
                sendApiResponse(true, 'Offer letters', $rows);
            default:
                $rows = fetchAll(executeQuery(
                    "SELECT v.*, (SELECT COUNT(*) FROM hr_job_applications a WHERE a.vacancy_id = v.id) as application_count
                     FROM hr_job_vacancies v ORDER BY v.created_at DESC"
                ));
                sendApiResponse(true, 'Vacancies', $rows);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $action = sanitize($data['action'] ?? '');

        switch ($action) {
            case 'schedule_interview':
                $result = RecruitmentService::scheduleInterview((int)$data['application_id'], $data);
                sendApiResponse($result['success'], $result['message']);
            case 'update_status':
                $result = RecruitmentService::updateApplicationStatus((int)$data['application_id'], $data['status'], $data);
                sendApiResponse($result['success'], $result['message']);
            case 'create_offer':
                $result = RecruitmentService::createOfferLetter((int)$data['application_id'], $data, $auth['user']['id']);
                sendApiResponse($result['success'], $result['message']);
            case 'hire':
                $result = RecruitmentService::hireApplication((int)$data['application_id'], $data, $auth['user']);
                sendApiResponse($result['success'], $result['message'], $result['success'] ? [
                    'staff_id' => $result['staff_id'],
                    'staff_code' => $result['staff_code'],
                ] : null);
            default:
                sendApiResponse(false, 'Unknown action', null, 400);
        }
    }

    sendApiResponse(false, 'Invalid request method', null, 405);
} catch (Exception $e) {
    sendApiResponse(false, $e->getMessage(), null, 500);
}
