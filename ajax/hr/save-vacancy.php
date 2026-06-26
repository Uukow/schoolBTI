<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) {
        jsonResponse(false, 'Permission denied');
    }

    $currentUser = getCurrentUser();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $id = (int)($data['id'] ?? 0);

    if ($id > 0 && !empty($data['status']) && empty($data['job_title'])) {
        executeQuery("UPDATE hr_job_vacancies SET status=? WHERE id=?", 'si', [sanitize($data['status']), $id]);
        if ($data['status'] === 'Published') {
            executeQuery("UPDATE hr_job_vacancies SET published_at=NOW() WHERE id=?", 'i', [$id]);
        }
        logActivity($currentUser['id'], 'Update Vacancy Status', 'HR', "Vacancy ID: $id → {$data['status']}");
        jsonResponse(true, 'Vacancy updated');
    }

    $title = sanitize($data['job_title'] ?? '');
    $department = sanitize($data['department'] ?? '');
    $employmentType = sanitize($data['employment_type'] ?? 'Full Time');
    $description = sanitize($data['description'] ?? '');
    $requirements = sanitize($data['requirements'] ?? '');
    $deadline = !empty($data['application_deadline']) ? sanitize($data['application_deadline']) : null;
    $openings = (int)($data['openings'] ?? 1);
    $status = sanitize($data['status'] ?? 'Draft');
    $branchId = isset($data['branch_id']) && $data['branch_id'] !== '' ? (int)$data['branch_id'] : ($currentUser['branch_id'] ?? null);
    $salaryMin = isset($data['salary_range_min']) && $data['salary_range_min'] !== '' ? (float)$data['salary_range_min'] : null;
    $salaryMax = isset($data['salary_range_max']) && $data['salary_range_max'] !== '' ? (float)$data['salary_range_max'] : null;

    if ($id > 0) {
        if (empty($title)) {
            jsonResponse(false, 'Job title is required');
        }
        $stmt = executeQuery(
            "UPDATE hr_job_vacancies SET job_title=?, department=?, branch_id=?, employment_type=?, description=?, requirements=?,
             salary_range_min=?, salary_range_max=?, application_deadline=?, openings=?, status=? WHERE id=?",
            'ssissssddsisi',
            [$title, $department, $branchId, $employmentType, $description, $requirements, $salaryMin, $salaryMax, $deadline, $openings, $status, $id]
        );
        if ($status === 'Published') {
            executeQuery("UPDATE hr_job_vacancies SET published_at=NOW() WHERE id=?", 'i', [$id]);
        }
        if ($stmt) {
            logActivity($currentUser['id'], 'Update Vacancy', 'HR', "Vacancy ID: $id");
            jsonResponse(true, 'Vacancy updated successfully');
        }
        jsonResponse(false, 'Failed to update vacancy');
    }

    if (empty($title)) {
        jsonResponse(false, 'Job title is required');
    }

    $vacancyNo = HrNumberService::next('VAC-', 'hr_job_vacancies', 'vacancy_no');
    $stmt = executeQuery(
        "INSERT INTO hr_job_vacancies (vacancy_no, job_title, department, branch_id, employment_type, description, requirements,
         salary_range_min, salary_range_max, application_deadline, openings, status, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        'sssisssddsisi',
        [$vacancyNo, $title, $department, $branchId, $employmentType, $description, $requirements,
         $salaryMin, $salaryMax, $deadline, $openings, $status, (int)$currentUser['id']]
    );

    if ($stmt) {
        if ($status === 'Published') {
            executeQuery("UPDATE hr_job_vacancies SET published_at=NOW() WHERE vacancy_no=?", 's', [$vacancyNo]);
        }
        logActivity($currentUser['id'], 'Create Vacancy', 'HR', "Vacancy: $vacancyNo");
        jsonResponse(true, 'Vacancy created successfully', ['vacancy_no' => $vacancyNo]);
    }

    global $conn;
    jsonResponse(false, 'Failed to create vacancy: ' . ($conn->error ?? 'Unknown error'));
} catch (Throwable $e) {
    error_log('save-vacancy.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
