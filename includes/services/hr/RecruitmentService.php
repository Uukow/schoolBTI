<?php
if (!defined('ABSPATH')) exit;

class RecruitmentService
{
    public static function getApplication($applicationId)
    {
        return fetchOne(executeQuery(
            "SELECT a.*, v.job_title, v.department, v.branch_id, v.employment_type, v.vacancy_no, v.openings
             FROM hr_job_applications a
             INNER JOIN hr_job_vacancies v ON a.vacancy_id = v.id
             WHERE a.id = ?",
            'i', [$applicationId]
        ));
    }

    public static function updateApplicationStatus($applicationId, $status, $extra = [])
    {
        $allowed = ['Applied', 'Screening', 'Shortlisted', 'Interview', 'Offer', 'Hired', 'Rejected'];
        if (!in_array($status, $allowed, true)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        $score = isset($extra['screening_score']) ? (float)$extra['screening_score'] : null;
        if ($score !== null) {
            executeQuery(
                "UPDATE hr_job_applications SET status = ?, screening_score = ? WHERE id = ?",
                'sdi', [$status, $score, $applicationId]
            );
        } else {
            executeQuery("UPDATE hr_job_applications SET status = ? WHERE id = ?", 'si', [$status, $applicationId]);
        }
        return ['success' => true, 'message' => 'Application status updated'];
    }

    public static function scheduleInterview($applicationId, $data)
    {
        $app = self::getApplication($applicationId);
        if (!$app) {
            return ['success' => false, 'message' => 'Application not found'];
        }
        executeQuery(
            "INSERT INTO hr_interviews (application_id, interview_date, interview_type, location_or_link, comments)
             VALUES (?, ?, ?, ?, ?)",
            'issss',
            [
                $applicationId,
                sanitize($data['interview_date']),
                sanitize($data['interview_type'] ?? 'In_Person'),
                sanitize($data['location_or_link'] ?? ''),
                sanitize($data['comments'] ?? ''),
            ]
        );
        if ($app['status'] !== 'Hired' && $app['status'] !== 'Rejected') {
            self::updateApplicationStatus($applicationId, 'Interview');
        }
        if (!empty($app['email']) && ($data['notify_candidate'] ?? true)) {
            if (file_exists(ABSPATH . 'includes/mailer.php')) {
                require_once ABSPATH . 'includes/mailer.php';
                sendHrInterviewEmail(
                    $app['email'],
                    $app['first_name'] . ' ' . $app['last_name'],
                    $app['job_title'],
                    $data['interview_date'],
                    $data['interview_type'] ?? 'In_Person',
                    $data['location_or_link'] ?? ''
                );
            }
        }
        return ['success' => true, 'message' => 'Interview scheduled'];
    }

    public static function completeInterview($interviewId, $data)
    {
        executeQuery(
            "UPDATE hr_interviews SET status='Completed', overall_rating=?, recommendation=?, comments=?
             WHERE id=?",
            'dssi',
            [
                (float)($data['overall_rating'] ?? 0),
                sanitize($data['recommendation'] ?? ''),
                sanitize($data['comments'] ?? ''),
                (int)$interviewId,
            ]
        );
        return ['success' => true, 'message' => 'Interview evaluation saved'];
    }

    public static function createOfferLetter($applicationId, $data, $createdBy)
    {
        $app = self::getApplication($applicationId);
        if (!$app) {
            return ['success' => false, 'message' => 'Application not found'];
        }
        executeQuery(
            "INSERT INTO hr_offer_letters (application_id, offered_salary, start_date, offer_date, expiry_date, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            'idssssi',
            [
                $applicationId,
                (float)$data['offered_salary'],
                sanitize($data['start_date']),
                sanitize($data['offer_date'] ?? date('Y-m-d')),
                sanitize($data['expiry_date'] ?? null),
                sanitize($data['status'] ?? 'Draft'),
                (int)$createdBy,
            ]
        );
        $offerId = (int)(getDBConnection()->insert_id ?? 0);
        $pdfPath = null;
        if ($offerId > 0) {
            try {
                $pdfPath = OfferLetterService::savePdf($offerId);
            } catch (Throwable $e) {
                error_log('createOfferLetter PDF: ' . $e->getMessage());
            }
        }
        if ($app['status'] !== 'Hired' && $app['status'] !== 'Rejected') {
            self::updateApplicationStatus($applicationId, 'Offer');
        }
        $status = sanitize($data['status'] ?? 'Draft');
        if ($status === 'Sent' && !empty($app['email'])) {
            self::sendOfferEmail($offerId, $pdfPath);
        }
        $message = 'Offer letter created';
        if ($offerId > 0 && !$pdfPath) {
            $message .= ' (PDF will be available after mPDF is installed)';
        }
        return ['success' => true, 'message' => $message, 'offer_id' => $offerId];
    }

    public static function hireApplication($applicationId, $data, $user)
    {
        $app = self::getApplication($applicationId);
        if (!$app) {
            return ['success' => false, 'message' => 'Application not found'];
        }
        if ($app['status'] === 'Hired') {
            return ['success' => false, 'message' => 'Candidate already hired'];
        }

        $roleName = $user['role_name'] ?? '';
        $branchId = (int)($data['branch_id'] ?? $app['branch_id'] ?? $user['branch_id'] ?? 0);
        if ($roleName !== 'Super Admin' && $branchId !== (int)($user['branch_id'] ?? 0)) {
            return ['success' => false, 'message' => 'Cannot hire to another branch'];
        }
        if (!$branchId) {
            return ['success' => false, 'message' => 'Branch is required'];
        }

        $lastRow = fetchOne(executeQuery("SELECT MAX(id) as max_id FROM staff"));
        $staffCode = generateUniqueId(STAFF_ID_PREFIX, ((int)($lastRow['max_id'] ?? 0)) + 1, 6);

        $gender = sanitize($data['gender'] ?? 'Male');
        $dob = sanitize($data['date_of_birth'] ?? '1990-01-01');
        $joiningDate = sanitize($data['joining_date'] ?? date('Y-m-d'));
        $designation = sanitize($data['designation'] ?? $app['job_title']);
        $department = sanitize($data['department'] ?? $app['department'] ?? '');
        $employmentType = sanitize($data['employment_type'] ?? $app['employment_type'] ?? 'Full Time');
        $basicSalary = isset($data['basic_salary']) ? (float)$data['basic_salary'] : null;

        executeQuery(
            "INSERT INTO staff (staff_id, branch_id, first_name, last_name, gender, date_of_birth, email, phone,
             designation, department, joining_date, employment_type, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')",
            'sissssssssss',
            [
                $staffCode, $branchId, $app['first_name'], $app['last_name'], $gender, $dob,
                $app['email'], $app['phone'], $designation, $department ?: null,
                $joiningDate, $employmentType,
            ]
        );
        $newStaffId = getDBConnection()->insert_id;

        if ($basicSalary && $basicSalary > 0) {
            executeQuery(
                "INSERT INTO payroll_structures (staff_id, basic_salary, effective_from)
                 VALUES (?, ?, ?)",
                'ids',
                [$newStaffId, $basicSalary, $joiningDate]
            );
        }

        self::updateApplicationStatus($applicationId, 'Hired');

        executeQuery(
            "INSERT INTO hr_talent_pool (application_id, first_name, last_name, email, phone, cv_path, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            'issssss',
            [
                $applicationId, $app['first_name'], $app['last_name'], $app['email'],
                $app['phone'], $app['cv_path'], 'Hired as staff ' . $staffCode,
            ]
        );

        $hiredCount = fetchOne(executeQuery(
            "SELECT COUNT(*) as c FROM hr_job_applications WHERE vacancy_id = ? AND status = 'Hired'",
            'i', [$app['vacancy_id']]
        ))['c'] ?? 0;
        if ($hiredCount >= (int)($app['openings'] ?? 1)) {
            executeQuery("UPDATE hr_job_vacancies SET status = 'Filled' WHERE id = ?", 'i', [$app['vacancy_id']]);
        }

        logActivity($user['id'], 'Hire Candidate', 'HR', "Hired {$app['first_name']} {$app['last_name']} as $staffCode");

        return [
            'success' => true,
            'message' => 'Candidate hired successfully',
            'staff_id' => $newStaffId,
            'staff_code' => $staffCode,
        ];
    }

    public static function sendOfferEmail($offerId, $pdfPath = null)
    {
        $offer = OfferLetterService::getOfferData($offerId);
        if (!$offer || empty($offer['email'])) {
            return ['success' => false, 'message' => 'Offer or email not found'];
        }
        if (!$pdfPath && empty($offer['letter_path'])) {
            $pdfPath = OfferLetterService::savePdf($offerId);
        } else {
            $pdfPath = $pdfPath ?: $offer['letter_path'];
        }
        if (file_exists(ABSPATH . 'includes/mailer.php')) {
            require_once ABSPATH . 'includes/mailer.php';
            sendHrOfferEmail(
                $offer['email'],
                $offer['first_name'] . ' ' . $offer['last_name'],
                $offer['job_title'],
                $offer['offered_salary'],
                $offer['start_date'],
                $pdfPath
            );
        }
        executeQuery("UPDATE hr_offer_letters SET status = 'Sent' WHERE id = ?", 'i', [$offerId]);
        return ['success' => true, 'message' => 'Offer letter sent'];
    }
}
