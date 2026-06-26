<?php

ob_start();

require_once '../../config/config.php';

error_reporting(E_ALL);

ini_set('display_errors', 0);

ob_clean();

header('Content-Type: application/json; charset=utf-8');



try {

    $currentUser = hrAjaxRequire('hr_recruitment', 'create');

    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;



    $applicationId = (int)($data['application_id'] ?? 0);

    if (!$applicationId) {

        jsonResponse(false, 'Application required');

    }

    if (empty($data['offered_salary']) || empty($data['start_date'])) {

        jsonResponse(false, 'Salary and start date required');

    }



    $result = RecruitmentService::createOfferLetter($applicationId, $data, $currentUser['id']);

    if ($result['success']) {

        logActivity($currentUser['id'], 'Create Offer Letter', 'HR', "Application ID: $applicationId");

    }

    jsonResponse(

        $result['success'],

        $result['message'],

        $result['success'] ? ['offer_id' => $result['offer_id'] ?? null] : null

    );

} catch (Throwable $e) {

    error_log('save-offer-letter.php: ' . $e->getMessage());

    jsonResponse(false, 'Server error: ' . $e->getMessage());

}

