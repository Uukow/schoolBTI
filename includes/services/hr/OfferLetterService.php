<?php
if (!defined('ABSPATH')) exit;

class OfferLetterService
{
    public static function getOfferData($offerId)
    {
        return fetchOne(executeQuery(
            "SELECT o.*, a.application_no, a.first_name, a.last_name, a.email, a.phone,
                    v.job_title, v.department, v.employment_type, b.branch_name
             FROM hr_offer_letters o
             INNER JOIN hr_job_applications a ON o.application_id = a.id
             INNER JOIN hr_job_vacancies v ON a.vacancy_id = v.id
             LEFT JOIN branches b ON v.branch_id = b.id
             WHERE o.id = ?",
            'i', [$offerId]
        ));
    }

    public static function renderHtml($o)
    {
        $settings = fetchOne(executeQuery("SELECT school_name, school_address, school_phone, school_email FROM system_settings LIMIT 1"));
        $org = $settings['school_name'] ?? APP_NAME;
        $html = '<html><head><style>
            body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#333;line-height:1.6}
            .header{text-align:center;border-bottom:2px solid #1a56db;padding-bottom:12px;margin-bottom:24px}
            .title{font-size:22px;color:#1a56db;font-weight:bold}
            .section{margin:18px 0}
            table{width:100%;border-collapse:collapse}
            td{padding:6px 0}
            .label{font-weight:bold;width:160px}
        </style></head><body>';
        $html .= '<div class="header"><div class="title">' . htmlspecialchars($org) . '</div>';
        $html .= '<div>Official Offer of Employment</div></div>';
        $html .= '<p>Date: <strong>' . date('d F Y', strtotime($o['offer_date'])) . '</strong></p>';
        $html .= '<p>Dear <strong>' . htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) . '</strong>,</p>';
        $html .= '<p>We are pleased to offer you the position of <strong>' . htmlspecialchars($o['job_title']) . '</strong>';
        if (!empty($o['department'])) {
            $html .= ' in the <strong>' . htmlspecialchars($o['department']) . '</strong> department';
        }
        $html .= ' at ' . htmlspecialchars($org) . '.</p>';
        $html .= '<table class="section">';
        $html .= '<tr><td class="label">Position</td><td>' . htmlspecialchars($o['job_title']) . '</td></tr>';
        $html .= '<tr><td class="label">Employment Type</td><td>' . htmlspecialchars(str_replace('_', ' ', $o['employment_type'] ?? 'Full Time')) . '</td></tr>';
        $html .= '<tr><td class="label">Offered Salary</td><td>' . CURRENCY_SYMBOL . number_format($o['offered_salary'], 2) . ' per month</td></tr>';
        $html .= '<tr><td class="label">Start Date</td><td>' . date('d F Y', strtotime($o['start_date'])) . '</td></tr>';
        if (!empty($o['expiry_date'])) {
            $html .= '<tr><td class="label">Offer Valid Until</td><td>' . date('d F Y', strtotime($o['expiry_date'])) . '</td></tr>';
        }
        if (!empty($o['branch_name'])) {
            $html .= '<tr><td class="label">Work Location</td><td>' . htmlspecialchars($o['branch_name']) . '</td></tr>';
        }
        $html .= '</table>';
        $html .= '<p>Please confirm your acceptance by replying to this offer before the expiry date. We look forward to welcoming you to our team.</p>';
        $html .= '<p style="margin-top:40px">Sincerely,<br><strong>Human Resources Department</strong><br>' . htmlspecialchars($org) . '</p>';
        if (!empty($settings['school_phone']) || !empty($settings['school_email'])) {
            $html .= '<p style="font-size:10px;color:#666">';
            if (!empty($settings['school_phone'])) $html .= 'Tel: ' . htmlspecialchars($settings['school_phone']) . ' ';
            if (!empty($settings['school_email'])) $html .= 'Email: ' . htmlspecialchars($settings['school_email']);
            $html .= '</p>';
        }
        $html .= '</body></html>';
        return $html;
    }

    public static function ensureMpdfLoaded()
    {
        if (class_exists('Mpdf\Mpdf')) {
            return true;
        }
        $autoload = ABSPATH . 'vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }
        return class_exists('Mpdf\Mpdf');
    }

    public static function savePdf($offerId)
    {
        $o = self::getOfferData($offerId);
        if (!$o) {
            return null;
        }

        if (!self::ensureMpdfLoaded()) {
            error_log('OfferLetterService: mPDF not installed — run composer install');
            return null;
        }

        if (!is_dir(RECRUITMENT_OFFER_PATH)) {
            @mkdir(RECRUITMENT_OFFER_PATH, 0755, true);
        }

        try {
            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
            $mpdf->WriteHTML(self::renderHtml($o));
            $filename = 'Offer_' . $o['application_no'] . '_' . date('Ymd') . '.pdf';
            $fullPath = RECRUITMENT_OFFER_PATH . $filename;
            $mpdf->Output($fullPath, 'F');

            $relative = 'uploads/recruitment/offers/' . $filename;
            executeQuery("UPDATE hr_offer_letters SET letter_path = ? WHERE id = ?", 'si', [$relative, $offerId]);
            return $relative;
        } catch (Throwable $e) {
            error_log('OfferLetterService::savePdf: ' . $e->getMessage());
            return null;
        }
    }

    public static function downloadPdf($offerId)
    {
        $o = self::getOfferData($offerId);
        if (!$o) {
            return false;
        }

        if (!self::ensureMpdfLoaded()) {
            return false;
        }

        try {
            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
            $mpdf->WriteHTML(self::renderHtml($o));
            $filename = 'Offer_' . $o['application_no'] . '.pdf';
            $mpdf->Output($filename, 'D');
            return true;
        } catch (Throwable $e) {
            error_log('OfferLetterService::downloadPdf: ' . $e->getMessage());
            return false;
        }
    }
}
