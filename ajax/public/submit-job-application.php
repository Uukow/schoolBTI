<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$vacancyId = (int)($_POST['vacancy_id'] ?? 0);
$firstName = sanitize($_POST['first_name'] ?? '');
$lastName = sanitize($_POST['last_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$coverLetter = sanitize($_POST['cover_letter'] ?? '');

if (!$vacancyId || empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
    jsonResponse(false, 'All required fields must be filled');
}

$vacancy = fetchOne(executeQuery(
    "SELECT * FROM hr_job_vacancies WHERE id = ? AND status = 'Published'", 'i', [$vacancyId]
));
if (!$vacancy) jsonResponse(false, 'This position is no longer available');

if (empty($_FILES['cv_file']['name'])) jsonResponse(false, 'CV file is required');

if (!is_dir(RECRUITMENT_CV_PATH)) @mkdir(RECRUITMENT_CV_PATH, 0755, true);

$ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['pdf', 'doc', 'docx'])) jsonResponse(false, 'CV must be PDF or Word document');

$filename = 'cv_' . time() . '_' . preg_replace('/[^a-z0-9]/i', '', $firstName) . '.' . $ext;
if (!move_uploaded_file($_FILES['cv_file']['tmp_name'], RECRUITMENT_CV_PATH . $filename)) {
    jsonResponse(false, 'Failed to upload CV');
}

$applicationNo = HrNumberService::next('APP-', 'hr_job_applications', 'application_no');
$cvPath = 'uploads/recruitment/cvs/' . $filename;

executeQuery(
    "INSERT INTO hr_job_applications (application_no, vacancy_id, first_name, last_name, email, phone, cv_path, cover_letter, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Applied')",
    'sissssss',
    [$applicationNo, $vacancyId, $firstName, $lastName, $email, $phone, $cvPath, $coverLetter]
);

jsonResponse(true, 'Application submitted successfully', ['application_no' => $applicationNo]);
