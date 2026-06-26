<?php
require_once '../../config/config.php';
hrRequirePage('hr_recruitment', 'view');

$offerId = (int)($_GET['id'] ?? 0);
if (!$offerId) die('Invalid offer ID');

OfferLetterService::downloadPdf($offerId);
