<?php
/**
 * Email System using PHPMailer
 * 
 * Handles all email sending functionality
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// Note: PHPMailer should be installed via Composer or downloaded manually
// For manual installation: Download from https://github.com/PHPMailer/PHPMailer
// Place in: includes/PHPMailer/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Try to load PHPMailer (if using Composer)
if (file_exists(ABSPATH . 'vendor/autoload.php')) {
    require ABSPATH . 'vendor/autoload.php';
} elseif (file_exists(ABSPATH . 'includes/PHPMailer/PHPMailer.php')) {
    // Manual installation
    require ABSPATH . 'includes/PHPMailer/PHPMailer.php';
    require ABSPATH . 'includes/PHPMailer/SMTP.php';
    require ABSPATH . 'includes/PHPMailer/Exception.php';
}

/**
 * Send email using PHPMailer
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text alternative
 * @param array $attachments Array of file paths to attach
 * @return array Result with success status and message
 */
function sendEmail($to, $subject, $body, $altBody = '', $attachments = []) {
    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        // Fallback to PHP mail() function
        return sendEmailFallback($to, $subject, $body);
    }
    
    // Check if SMTP credentials are configured
    if (empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
        // Fallback to PHP mail() function if SMTP not configured
        return sendEmailFallback($to, $subject, $body);
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        
        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(ADMIN_EMAIL, APP_NAME);
        
        // Attachments
        if (!empty($attachments)) {
            foreach ($attachments as $file) {
                if (file_exists($file)) {
                    $mail->addAttachment($file);
                }
            }
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        // Send email
        $mail->send();
        
        // Log communication
        logCommunication('Email', $to, $subject, $body, 'Sent');
        
        return ['success' => true, 'message' => 'Email sent successfully'];
        
    } catch (Exception $e) {
        // Log failure
        $errorMessage = isset($mail) ? $mail->ErrorInfo : $e->getMessage();
        logCommunication('Email', $to, $subject, $body, 'Failed', $errorMessage);
        
        return ['success' => false, 'message' => 'Email sending failed: ' . $errorMessage];
    }
}

/**
 * Fallback email function using PHP mail()
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body
 * @return array Result array
 */
function sendEmailFallback($to, $subject, $body) {
    $headers = "From: " . MAIL_FROM_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $success = mail($to, $subject, $body, $headers);
    
    $status = $success ? 'Sent' : 'Failed';
    logCommunication('Email', $to, $subject, $body, $status);
    
    if ($success) {
        return ['success' => true, 'message' => 'Email sent successfully'];
    } else {
        return ['success' => false, 'message' => 'Email sending failed'];
    }
}

/**
 * Send welcome email to new user
 * 
 * @param string $email User email
 * @param string $username Username
 * @param string $password Plain text password (only for new accounts)
 * @return array Result array
 */
function sendWelcomeEmail($email, $username, $password = null) {
    $subject = "Welcome to " . APP_NAME;
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f8f9fa; padding: 30px; }
            .credentials { background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to " . APP_NAME . "</h1>
            </div>
            <div class='content'>
                <p>Hello <strong>" . htmlspecialchars($username) . "</strong>,</p>
                <p>Your account has been successfully created. You can now access the system using the credentials below:</p>
                
                <div class='credentials'>
                    <p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>
                    " . ($password ? "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>" : "") . "
                    <p><strong>Login URL:</strong> <a href='" . APP_URL . "'>" . APP_URL . "</a></p>
                </div>
                
                <p>Please change your password after first login for security purposes.</p>
                <p>If you have any questions, please contact us at " . ADMIN_EMAIL . "</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . APP_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body);
}

/**
 * Send fee payment reminder
 * 
 * @param string $email Parent email
 * @param string $studentName Student name
 * @param string $invoiceNo Invoice number
 * @param float $amount Due amount
 * @param string $dueDate Due date
 * @return array Result array
 */
function sendFeeReminder($email, $studentName, $invoiceNo, $amount, $dueDate) {
    $subject = "Fee Payment Reminder - " . APP_NAME;
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #f8b500; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: white; }
            .amount { font-size: 24px; color: #f8b500; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Fee Payment Reminder</h2>
            </div>
            <div class='content'>
                <p>Dear Parent/Guardian,</p>
                <p>This is a friendly reminder regarding the pending fee payment for <strong>" . htmlspecialchars($studentName) . "</strong>.</p>
                
                <p><strong>Invoice Number:</strong> " . htmlspecialchars($invoiceNo) . "</p>
                <p><strong>Amount Due:</strong> <span class='amount'>" . formatCurrency($amount) . "</span></p>
                <p><strong>Due Date:</strong> " . formatDate($dueDate) . "</p>
                
                <p>Please make the payment at your earliest convenience to avoid any late fees.</p>
                <p>You can make the payment at the school office or through our online payment portal.</p>
                
                <p>Thank you for your cooperation!</p>
                <p><strong>" . APP_NAME . "</strong></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body);
}

/**
 * Send admission confirmation email
 * 
 * @param string $email Applicant email
 * @param string $applicantName Applicant name
 * @param string $applicationNo Application number
 * @param string $status Status (Accepted/Rejected)
 * @return array Result array
 */
function sendAdmissionStatusEmail($email, $applicantName, $applicationNo, $status) {
    $subject = "Admission Application Status - " . APP_NAME;
    
    $statusColor = ($status == 'Accepted') ? '#0acf97' : '#fa5c7c';
    $statusText = ucfirst($status);
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
            .content { padding: 30px; background: white; }
            .status { padding: 15px; background: $statusColor; color: white; text-align: center; font-size: 20px; font-weight: bold; border-radius: 8px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Admission Application Status</h1>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($applicantName) . ",</p>
                <p>Thank you for applying to " . APP_NAME . ". Your application has been reviewed.</p>
                
                <div class='status'>$statusText</div>
                
                <p><strong>Application Number:</strong> " . htmlspecialchars($applicationNo) . "</p>
                
                " . ($status == 'Accepted' ? 
                    "<p>Congratulations! Your application has been accepted. Please visit the school office to complete the enrollment process.</p>" :
                    "<p>We regret to inform you that we are unable to proceed with your application at this time.</p>") . "
                
                <p>For any queries, please contact us at " . ADMIN_EMAIL . "</p>
                <p>Best regards,<br><strong>" . APP_NAME . "</strong></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body);
}

/**
 * Send admin-initiated password reset email to user
 * 
 * @param string $email User email
 * @param string $username Username
 * @param string $newPassword New password (plain text)
 * @return array Result array
 */
function sendAdminPasswordResetEmail($email, $username, $newPassword) {
    $subject = "Password Reset - " . APP_NAME;
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #fa5c7c 0%, #ff6b9d 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f8f9fa; padding: 30px; }
            .credentials { background: white; padding: 25px; border-left: 4px solid #fa5c7c; margin: 20px 0; border-radius: 4px; }
            .password-box { background: #fff3cd; border: 2px dashed #ffc107; padding: 15px; margin: 15px 0; text-align: center; border-radius: 4px; }
            .password-text { font-size: 24px; font-weight: bold; color: #856404; letter-spacing: 2px; font-family: 'Courier New', monospace; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .button { display: inline-block; padding: 12px 30px; background: #fa5c7c; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔒 Password Reset</h1>
            </div>
            <div class='content'>
                <p>Hello <strong>" . htmlspecialchars($username) . "</strong>,</p>
                <p>Your password has been reset by an administrator. Please use the new password below to log in to your account:</p>
                
                <div class='credentials'>
                    <p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>
                    <div class='password-box'>
                        <p style='margin: 0 0 10px 0; color: #856404;'><strong>Your New Password:</strong></p>
                        <div class='password-text'>" . htmlspecialchars($newPassword) . "</div>
                    </div>
                    <p style='margin-top: 15px;'><strong>Login URL:</strong> <a href='" . APP_URL . "'>" . APP_URL . "</a></p>
                </div>
                
                <div class='warning'>
                    <p style='margin: 0;'><strong>⚠️ Security Notice:</strong></p>
                    <p style='margin: 10px 0 0 0;'>For your security, please change this password immediately after logging in. Do not share this password with anyone.</p>
                </div>
                
                <p style='text-align: center;'>
                    <a href='" . APP_URL . "' class='button'>Login to Your Account</a>
                </p>
                
                <p>If you did not request this password reset, please contact us immediately at " . ADMIN_EMAIL . "</p>
                <p>Best regards,<br><strong>" . APP_NAME . " Team</strong></p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . APP_NAME . ". All rights reserved.</p>
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "Password Reset - " . APP_NAME . "\n\n" .
               "Hello " . $username . ",\n\n" .
               "Your password has been reset. Your new password is: " . $newPassword . "\n\n" .
               "Please log in at: " . APP_URL . "\n\n" .
               "For security, please change this password after logging in.\n\n" .
               "If you did not request this, please contact us at " . ADMIN_EMAIL . "\n\n" .
               "Best regards,\n" . APP_NAME . " Team";
    
    return sendEmail($email, $subject, $body, $altBody);
}

/**
 * Notify candidate of scheduled interview
 */
function sendHrInterviewEmail($email, $candidateName, $jobTitle, $interviewDate, $interviewType, $location)
{
    $typeLabel = str_replace('_', ' ', $interviewType);
    $formattedDate = date('l, d F Y \a\t h:i A', strtotime($interviewDate));
    $subject = "Interview Invitation — {$jobTitle} — " . APP_NAME;
    $body = "
    <html><body style='font-family:Arial,sans-serif;color:#333'>
    <div style='max-width:600px;margin:0 auto;padding:20px'>
    <h2 style='color:#1a56db'>Interview Invitation</h2>
    <p>Dear <strong>" . htmlspecialchars($candidateName) . "</strong>,</p>
    <p>Thank you for your application for the position of <strong>" . htmlspecialchars($jobTitle) . "</strong>.</p>
    <p>We would like to invite you for an interview:</p>
    <table style='margin:16px 0'>
    <tr><td style='padding:4px 12px 4px 0'><strong>Date &amp; Time:</strong></td><td>{$formattedDate}</td></tr>
    <tr><td style='padding:4px 12px 4px 0'><strong>Type:</strong></td><td>{$typeLabel}</td></tr>
    <tr><td style='padding:4px 12px 4px 0'><strong>Location:</strong></td><td>" . htmlspecialchars($location ?: 'To be confirmed') . "</td></tr>
    </table>
    <p>Please arrive on time and bring any documents requested in the job posting.</p>
    <p>Best regards,<br><strong>HR Team</strong><br>" . APP_NAME . "</p>
    </div></body></html>";
    return sendEmail($email, $subject, $body);
}

/**
 * Send offer letter email to candidate
 */
function sendHrOfferEmail($email, $candidateName, $jobTitle, $offeredSalary, $startDate, $pdfPath = null)
{
    $subject = "Job Offer — {$jobTitle} — " . APP_NAME;
    $body = "
    <html><body style='font-family:Arial,sans-serif;color:#333'>
    <div style='max-width:600px;margin:0 auto;padding:20px'>
    <h2 style='color:#0acf97'>Congratulations!</h2>
    <p>Dear <strong>" . htmlspecialchars($candidateName) . "</strong>,</p>
    <p>We are delighted to offer you the position of <strong>" . htmlspecialchars($jobTitle) . "</strong> at " . APP_NAME . ".</p>
    <table style='margin:16px 0'>
    <tr><td style='padding:4px 8px 4px 0'><strong>Salary:</strong></td><td>" . CURRENCY_SYMBOL . number_format($offeredSalary, 2) . " per month</td></tr>
    <tr><td style='padding:4px 8px 4px 0'><strong>Start Date:</strong></td><td>" . date('d F Y', strtotime($startDate)) . "</td></tr>
    </table>
    <p>Please find your official offer letter attached. We look forward to your positive response.</p>
    <p>Best regards,<br><strong>HR Team</strong><br>" . APP_NAME . "</p>
    </div></body></html>";
    $attachments = [];
    if ($pdfPath && file_exists(ABSPATH . $pdfPath)) {
        $attachments[] = ABSPATH . $pdfPath;
    }
    return sendEmail($email, $subject, $body, '', $attachments);
}

/**
 * Get beautiful email template
 * 
 * @param string $subject Email subject
 * @param string $message Email message content
 * @param string $recipientName Optional recipient name for personalization
 * @return string HTML email template
 */
function getEmailTemplate($subject, $message, $recipientName = '') {
    // Get school settings if available
    $schoolName = APP_NAME;
    $schoolEmail = ADMIN_EMAIL;
    
    $sql = "SELECT school_name, school_email, school_phone, school_address FROM system_settings LIMIT 1";
    $stmt = executeQuery($sql);
    $settings = fetchOne($stmt);
    
    if ($settings) {
        $schoolName = $settings['school_name'] ?? APP_NAME;
        $schoolEmail = $settings['school_email'] ?? ADMIN_EMAIL;
    }
    
    $greeting = !empty($recipientName) ? "Hello " . htmlspecialchars($recipientName) . "," : "Hello,";
    
    $template = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($subject) . '</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                line-height: 1.6;
                color: #333333;
                background-color: #f4f4f4;
                padding: 20px;
            }
            .email-wrapper {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .email-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #ffffff;
                padding: 40px 30px;
                text-align: center;
            }
            .email-header h1 {
                font-size: 28px;
                font-weight: 600;
                margin-bottom: 10px;
            }
            .email-header .logo {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .email-body {
                padding: 40px 30px;
                background-color: #ffffff;
            }
            .email-greeting {
                font-size: 18px;
                color: #333333;
                margin-bottom: 20px;
            }
            .email-content {
                font-size: 16px;
                color: #555555;
                line-height: 1.8;
                margin-bottom: 30px;
            }
            .email-content p {
                margin-bottom: 15px;
            }
            .email-content a {
                color: #667eea;
                text-decoration: none;
            }
            .email-content a:hover {
                text-decoration: underline;
            }
            .email-divider {
                height: 2px;
                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);
                margin: 30px 0;
            }
            .email-footer {
                background-color: #f8f9fa;
                padding: 30px;
                text-align: center;
                border-top: 1px solid #e0e0e0;
            }
            .email-footer p {
                font-size: 14px;
                color: #666666;
                margin-bottom: 10px;
            }
            .email-footer .school-name {
                font-weight: 600;
                color: #333333;
                font-size: 16px;
                margin-bottom: 5px;
            }
            .email-footer .contact-info {
                font-size: 13px;
                color: #888888;
            }
            .email-footer a {
                color: #667eea;
                text-decoration: none;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #ffffff !important;
                text-decoration: none;
                border-radius: 5px;
                font-weight: 600;
                margin: 20px 0;
                text-align: center;
            }
            .button:hover {
                opacity: 0.9;
                text-decoration: none;
            }
            @media only screen and (max-width: 600px) {
                .email-wrapper {
                    width: 100% !important;
                }
                .email-header, .email-body, .email-footer {
                    padding: 20px !important;
                }
                .email-header h1 {
                    font-size: 24px !important;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-wrapper">
            <div class="email-header">
                <div class="logo">' . htmlspecialchars($schoolName) . '</div>
                <h1>' . htmlspecialchars($subject) . '</h1>
            </div>
            
            <div class="email-body">
                <div class="email-greeting">
                    ' . $greeting . '
                </div>
                
                <div class="email-content">
                    ' . str_replace(["\r\n", "\n", "\r"], '<br>', htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '
                </div>
            </div>
            
            <div class="email-divider"></div>
            
            <div class="email-footer">
                <p class="school-name">' . htmlspecialchars($schoolName) . '</p>';
    
    if ($settings && !empty($settings['school_address'])) {
        $template .= '<p class="contact-info">' . htmlspecialchars($settings['school_address']) . '</p>';
    }
    
    if ($settings && !empty($settings['school_phone'])) {
        $template .= '<p class="contact-info">Phone: ' . htmlspecialchars($settings['school_phone']) . '</p>';
    }
    
    $template .= '
                <p class="contact-info">
                    Email: <a href="mailto:' . htmlspecialchars($schoolEmail) . '">' . htmlspecialchars($schoolEmail) . '</a>
                </p>
                <p class="contact-info" style="margin-top: 20px; font-size: 12px; color: #999999;">
                    &copy; ' . date('Y') . ' ' . htmlspecialchars($schoolName) . '. All rights reserved.<br>
                    This is an automated email. Please do not reply directly to this message.
                </p>
            </div>
        </div>
    </body>
    </html>';
    
    return $template;
}

/**
 * Log communication to database
 * 
 * @param string $type Communication type (Email, SMS, WhatsApp)
 * @param string $recipient Recipient
 * @param string $subject Subject
 * @param string $message Message body
 * @param string $status Status (Sent, Failed)
 * @param string $errorMessage Error message if failed
 */
function logCommunication($type, $recipient, $subject, $message, $status = 'Sent', $errorMessage = null) {
    $sql = "INSERT INTO communication_logs (communication_type, recipient, subject, message, status, error_message, sent_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $userId = isLoggedIn() ? getCurrentUser()['id'] : null;
    
    executeQuery($sql, 'ssssssi', [$type, $recipient, $subject, $message, $status, $errorMessage, $userId]);
}

