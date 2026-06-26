<?php
/**
 * Certificates & Academic Records Module - Migration Script
 *
 * Creates database tables for:
 *  - Grading schemes (multiple scales per session/program)
 *  - Certificate templates (configurable layouts & metadata)
 *  - Issued student certificates (registry with status & verification)
 *  - Academic transcripts (GPA/CGPA summaries with PDF links)
 *
 * This script is idempotent and safe to run multiple times.
 *
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Set content type
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates & Academic Records - Migration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .info {
            color: #17a2b8;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Certificates & Academic Records - Migration</h1>
        <p>
            This script will create the necessary database tables for the Certificates & Academic Records module,
            including grading schemes, certificate templates, issued certificates registry, and academic transcripts.
        </p>

        <?php
        $errors = [];
        $success = [];

        try {
            global $conn;

            /**
             * Helper: run SQL and collect errors without stopping execution
             */
            function runMigrationQuery(mysqli $conn, $sql, $description, array &$success, array &$errors)
            {
                if ($conn->query($sql) === true) {
                    echo "<p class='success'>✓ {$description}</p>";
                    $success[] = $description;
                } else {
                    $msg = $conn->error;
                    echo "<p class='error'>✗ {$description} failed: " . htmlspecialchars($msg) . "</p>";
                    $errors[] = "{$description} failed: {$msg}";
                }
            }

            echo "<div class='step'>";
            echo "<h3>Step 1: Creating grading scheme tables (support multiple grading scales)</h3>";

            // Grading schemes (metadata) - base table (may already exist)
            $sqlGradingSchemes = "CREATE TABLE IF NOT EXISTS `grading_schemes` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `scheme_name` varchar(100) NOT NULL,
                `description` text DEFAULT NULL,
                `session_id` int(11) DEFAULT NULL,
                `class_id` int(11) DEFAULT NULL,
                `is_default` tinyint(1) DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `session_id` (`session_id`),
                KEY `class_id` (`class_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            runMigrationQuery($conn, $sqlGradingSchemes, "Grading schemes table created/verified", $success, $errors);

            // Ensure additional columns exist to match application logic
            echo "<p>Checking additional columns on <code>grading_schemes</code>...</p>";

            // scale_type
            $colCheck = $conn->query("SHOW COLUMNS FROM `grading_schemes` LIKE 'scale_type'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `grading_schemes`
                        ADD COLUMN `scale_type` varchar(20) NOT NULL DEFAULT 'percentage' AFTER `scheme_name`";
                runMigrationQuery($conn, $sql, "Added column grading_schemes.scale_type", $success, $errors);
            }

            // max_gpa
            $colCheck = $conn->query("SHOW COLUMNS FROM `grading_schemes` LIKE 'max_gpa'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `grading_schemes`
                        ADD COLUMN `max_gpa` decimal(3,2) NOT NULL DEFAULT 4.00 AFTER `scale_type`";
                runMigrationQuery($conn, $sql, "Added column grading_schemes.max_gpa", $success, $errors);
            }

            // branch_id
            $colCheck = $conn->query("SHOW COLUMNS FROM `grading_schemes` LIKE 'branch_id'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `grading_schemes`
                        ADD COLUMN `branch_id` int(11) DEFAULT NULL AFTER `class_id`,
                        ADD KEY `branch_id` (`branch_id`)";
                runMigrationQuery($conn, $sql, "Added column grading_schemes.branch_id", $success, $errors);
            }

            // passing_percentage
            $colCheck = $conn->query("SHOW COLUMNS FROM `grading_schemes` LIKE 'passing_percentage'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `grading_schemes`
                        ADD COLUMN `passing_percentage` decimal(5,2) NOT NULL DEFAULT 50.00 AFTER `max_gpa`";
                runMigrationQuery($conn, $sql, "Added column grading_schemes.passing_percentage", $success, $errors);
            }

            // is_active
            $colCheck = $conn->query("SHOW COLUMNS FROM `grading_schemes` LIKE 'is_active'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `grading_schemes`
                        ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `is_default`";
                runMigrationQuery($conn, $sql, "Added column grading_schemes.is_active", $success, $errors);
            }

            // created_by
            $colCheck = $conn->query("SHOW COLUMNS FROM `grading_schemes` LIKE 'created_by'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `grading_schemes`
                        ADD COLUMN `created_by` int(11) DEFAULT NULL AFTER `is_active`,
                        ADD KEY `created_by` (`created_by`)";
                runMigrationQuery($conn, $sql, "Added column grading_schemes.created_by", $success, $errors);
            }

            // Grading scale items (scale details) - table used by application
            $sqlGradingScaleItems = "CREATE TABLE IF NOT EXISTS `grading_scale_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `grading_scheme_id` int(11) NOT NULL,
                `grade_letter` varchar(10) NOT NULL,
                `min_percentage` decimal(5,2) NOT NULL,
                `max_percentage` decimal(5,2) NOT NULL,
                `grade_point` decimal(3,2) NOT NULL,
                `description` varchar(100) DEFAULT NULL,
                `display_order` int(11) DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `grading_scheme_id` (`grading_scheme_id`),
                CONSTRAINT `grading_scale_items_ibfk_1` FOREIGN KEY (`grading_scheme_id`) REFERENCES `grading_schemes` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            runMigrationQuery($conn, $sqlGradingScaleItems, "Grading scale items table created/verified", $success, $errors);

            echo "<p class='info'>You can configure different grading schemes per academic session or class/program.</p>";
            echo "</div>";

            echo "<div class='step'>";
            echo "<h3>Step 2: Creating certificate templates table</h3>";

            // Base table definition (may already exist)
            $sqlCertificateTemplates = "CREATE TABLE IF NOT EXISTS `certificate_templates` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `template_name` varchar(150) NOT NULL,
                `template_type` enum('Completion','Promotion','Graduation','Character','Transcript','Custom') NOT NULL DEFAULT 'Custom',
                `code` varchar(50) NOT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `layout_html` longtext DEFAULT NULL,
                `placeholders_json` text DEFAULT NULL,
                `header_logo_path` varchar(255) DEFAULT NULL,
                `background_image_path` varchar(255) DEFAULT NULL,
                `signatory_1_name` varchar(150) DEFAULT NULL,
                `signatory_1_title` varchar(150) DEFAULT NULL,
                `signatory_1_signature_path` varchar(255) DEFAULT NULL,
                `signatory_2_name` varchar(150) DEFAULT NULL,
                `signatory_2_title` varchar(150) DEFAULT NULL,
                `signatory_2_signature_path` varchar(255) DEFAULT NULL,
                `issue_date_label` varchar(100) DEFAULT 'Date of Issue',
                `serial_label` varchar(100) DEFAULT 'Certificate No.',
                `qr_label` varchar(100) DEFAULT 'Verification QR',
                `created_by` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `code_unique` (`code`),
                KEY `template_type` (`template_type`),
                KEY `created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            runMigrationQuery($conn, $sqlCertificateTemplates, "Certificate templates table created/verified", $success, $errors);

            // Ensure additional columns exist to match application logic (templates.php, add-template.php)
            echo "<p>Checking additional columns on <code>certificate_templates</code>...</p>";

            // branch_id
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'branch_id'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `branch_id` int(11) DEFAULT NULL AFTER `code`,
                        ADD KEY `branch_id` (`branch_id`)";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.branch_id", $success, $errors);
            }

            // certificate_type (used by PHP; keep template_type for legacy)
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'certificate_type'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `certificate_type` varchar(50) NOT NULL DEFAULT 'custom' AFTER `template_type`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.certificate_type", $success, $errors);
            }

            // page_orientation
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'page_orientation'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `page_orientation` varchar(20) NOT NULL DEFAULT 'landscape' AFTER `branch_id`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.page_orientation", $success, $errors);
            }

            // page_size
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'page_size'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `page_size` varchar(20) NOT NULL DEFAULT 'A4' AFTER `page_orientation`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.page_size", $success, $errors);
            }

            // header_html
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'header_html'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `header_html` longtext DEFAULT NULL AFTER `background_image_path`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.header_html", $success, $errors);
            }

            // body_html
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'body_html'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `body_html` longtext DEFAULT NULL AFTER `header_html`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.body_html", $success, $errors);
            }

            // footer_html
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'footer_html'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `footer_html` longtext DEFAULT NULL AFTER `body_html`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.footer_html", $success, $errors);
            }

            // signature_1_label
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'signature_1_label'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `signature_1_label` varchar(150) DEFAULT NULL AFTER `signatory_1_signature_path`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.signature_1_label", $success, $errors);
            }

            // signature_2_label
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'signature_2_label'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `signature_2_label` varchar(150) DEFAULT NULL AFTER `signatory_2_signature_path`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.signature_2_label", $success, $errors);
            }

            // include_qr_code
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'include_qr_code'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `include_qr_code` tinyint(1) NOT NULL DEFAULT 1 AFTER `qr_label`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.include_qr_code", $success, $errors);
            }

            // include_watermark
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'include_watermark'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `include_watermark` tinyint(1) NOT NULL DEFAULT 0 AFTER `include_qr_code`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.include_watermark", $success, $errors);
            }

            // is_default
            $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'is_default'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $sql = "ALTER TABLE `certificate_templates`
                        ADD COLUMN `is_default` tinyint(1) NOT NULL DEFAULT 0 AFTER `include_watermark`";
                runMigrationQuery($conn, $sql, "Added column certificate_templates.is_default", $success, $errors);
            }

            echo "</div>";

            echo "<div class='step'>";
            echo "<h3>Step 3: Creating issued certificates registry</h3>";

            // Legacy table (kept for backward compatibility if already used)
            $sqlStudentCertificates = "CREATE TABLE IF NOT EXISTS `student_certificates` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `certificate_no` varchar(100) NOT NULL,
                `verification_code` varchar(64) NOT NULL,
                `student_id` int(11) NOT NULL,
                `template_id` int(11) NOT NULL,
                `certificate_type` enum('Completion','Promotion','Graduation','Character','Custom') NOT NULL,
                `session_id` int(11) DEFAULT NULL,
                `class_id` int(11) DEFAULT NULL,
                `issue_date` date NOT NULL,
                `status` enum('Issued','Reissued','Revoked') DEFAULT 'Issued',
                `revoked_reason` text DEFAULT NULL,
                `reissued_from_id` int(11) DEFAULT NULL,
                `pdf_path` varchar(255) DEFAULT NULL,
                `qr_code_path` varchar(255) DEFAULT NULL,
                `meta_json` text DEFAULT NULL,
                `issued_by` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `certificate_no_unique` (`certificate_no`),
                UNIQUE KEY `verification_code_unique` (`verification_code`),
                KEY `student_id` (`student_id`),
                KEY `template_id` (`template_id`),
                KEY `session_id` (`session_id`),
                KEY `class_id` (`class_id`),
                KEY `issued_by` (`issued_by`),
                KEY `reissued_from_id` (`reissued_from_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            runMigrationQuery($conn, $sqlStudentCertificates, "Legacy student_certificates table created/verified", $success, $errors);

            // Primary table used by the application: certificates
            $sqlCertificates = "CREATE TABLE IF NOT EXISTS `certificates` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `certificate_number` varchar(100) NOT NULL,
                `verification_code` varchar(64) NOT NULL,
                `template_id` int(11) NOT NULL,
                `student_id` int(11) NOT NULL,
                `session_id` int(11) DEFAULT NULL,
                `class_id` int(11) DEFAULT NULL,
                `certificate_type` varchar(50) NOT NULL,
                `issue_date` date NOT NULL,
                `valid_until` date DEFAULT NULL,
                `academic_data` longtext DEFAULT NULL,
                `gpa` decimal(3,2) DEFAULT NULL,
                `cgpa` decimal(3,2) DEFAULT NULL,
                `attendance_percentage` decimal(5,2) DEFAULT NULL,
                `class_rank` int(11) DEFAULT NULL,
                `remarks` text DEFAULT NULL,
                `status` enum('issued','reissued','revoked') DEFAULT 'issued',
                `issued_by` int(11) DEFAULT NULL,
                `revoked_at` datetime DEFAULT NULL,
                `revoked_by` int(11) DEFAULT NULL,
                `revoke_reason` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `certificate_number_unique` (`certificate_number`),
                UNIQUE KEY `verification_code_unique` (`verification_code`),
                KEY `template_id` (`template_id`),
                KEY `student_id` (`student_id`),
                KEY `session_id` (`session_id`),
                KEY `class_id` (`class_id`),
                KEY `issued_by` (`issued_by`),
                KEY `revoked_by` (`revoked_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            runMigrationQuery($conn, $sqlCertificates, "Certificates registry table created/verified", $success, $errors);

            echo "</div>";

            echo "<div class='step'>";
            echo "<h3>Step 4: Creating academic transcripts table</h3>";

            // Legacy table (kept for backward compatibility if already used)
            $sqlTranscriptsLegacy = "CREATE TABLE IF NOT EXISTS `student_transcripts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `transcript_no` varchar(100) NOT NULL,
                `verification_code` varchar(64) NOT NULL,
                `student_id` int(11) NOT NULL,
                `from_session_id` int(11) DEFAULT NULL,
                `to_session_id` int(11) DEFAULT NULL,
                `program_class_id` int(11) DEFAULT NULL,
                `total_credits` decimal(6,2) DEFAULT NULL,
                `earned_credits` decimal(6,2) DEFAULT NULL,
                `gpa` decimal(3,2) DEFAULT NULL,
                `cgpa` decimal(3,2) DEFAULT NULL,
                `remarks` text DEFAULT NULL,
                `summary_json` longtext DEFAULT NULL,
                `pdf_path` varchar(255) DEFAULT NULL,
                `status` enum('Issued','Revoked') DEFAULT 'Issued',
                `issued_by` int(11) DEFAULT NULL,
                `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `transcript_no_unique` (`transcript_no`),
                UNIQUE KEY `transcript_verification_code_unique` (`verification_code`),
                KEY `student_id` (`student_id`),
                KEY `from_session_id` (`from_session_id`),
                KEY `to_session_id` (`to_session_id`),
                KEY `program_class_id` (`program_class_id`),
                KEY `issued_by` (`issued_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            runMigrationQuery($conn, $sqlTranscriptsLegacy, "Legacy student_transcripts table created/verified", $success, $errors);

            // Primary transcripts table used by the application
            $sqlTranscripts = "CREATE TABLE IF NOT EXISTS `transcripts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `transcript_number` varchar(100) NOT NULL,
                `student_id` int(11) NOT NULL,
                `grading_scheme_id` int(11) NOT NULL,
                `academic_data` longtext DEFAULT NULL,
                `total_credits` decimal(6,2) DEFAULT NULL,
                `cgpa` decimal(3,2) DEFAULT NULL,
                `overall_percentage` decimal(5,2) DEFAULT NULL,
                `generated_by` int(11) DEFAULT NULL,
                `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `status` enum('issued','revoked') DEFAULT 'issued',
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `transcript_number_unique` (`transcript_number`),
                KEY `student_id` (`student_id`),
                KEY `grading_scheme_id` (`grading_scheme_id`),
                KEY `generated_by` (`generated_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            runMigrationQuery($conn, $sqlTranscripts, "Transcripts table created/verified", $success, $errors);

            echo "</div>";

            echo "<div class='step'>";
            echo "<h3>Step 5: Adding foreign key constraints (where possible)</h3>";

            // Add FKs for grading_schemes
            $sqlFkGradingSchemes = "ALTER TABLE `grading_schemes`
                ADD CONSTRAINT `grading_schemes_session_fk` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `grading_schemes_class_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL;";

            runMigrationQuery($conn, $sqlFkGradingSchemes, "Foreign keys for grading_schemes added (if not already present)", $success, $errors);

            // Add FKs for certificate_templates.created_by
            $sqlFkCertificateTemplates = "ALTER TABLE `certificate_templates`
                ADD CONSTRAINT `certificate_templates_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;";

            runMigrationQuery($conn, $sqlFkCertificateTemplates, "Foreign key for certificate_templates.created_by added (if not already present)", $success, $errors);

            // Add FKs for student_certificates (legacy)
            $sqlFkStudentCertificates = "ALTER TABLE `student_certificates`
                ADD CONSTRAINT `student_certificates_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
                ADD CONSTRAINT `student_certificates_template_fk` FOREIGN KEY (`template_id`) REFERENCES `certificate_templates` (`id`) ON DELETE RESTRICT,
                ADD CONSTRAINT `student_certificates_session_fk` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `student_certificates_class_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `student_certificates_issued_by_fk` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `student_certificates_reissued_from_fk` FOREIGN KEY (`reissued_from_id`) REFERENCES `student_certificates` (`id`) ON DELETE SET NULL;";

            runMigrationQuery($conn, $sqlFkStudentCertificates, "Foreign keys for legacy student_certificates added (if not already present)", $success, $errors);

            // Add FKs for primary certificates table
            $sqlFkCertificates = "ALTER TABLE `certificates`
                ADD CONSTRAINT `certificates_template_fk` FOREIGN KEY (`template_id`) REFERENCES `certificate_templates` (`id`) ON DELETE RESTRICT,
                ADD CONSTRAINT `certificates_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
                ADD CONSTRAINT `certificates_session_fk` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `certificates_class_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `certificates_issued_by_fk` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `certificates_revoked_by_fk` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;";

            runMigrationQuery($conn, $sqlFkCertificates, "Foreign keys for certificates added (if not already present)", $success, $errors);

            // Add FKs for student_transcripts (legacy)
            $sqlFkTranscriptsLegacy = "ALTER TABLE `student_transcripts`
                ADD CONSTRAINT `student_transcripts_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
                ADD CONSTRAINT `student_transcripts_from_session_fk` FOREIGN KEY (`from_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `student_transcripts_to_session_fk` FOREIGN KEY (`to_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `student_transcripts_program_class_fk` FOREIGN KEY (`program_class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
                ADD CONSTRAINT `student_transcripts_issued_by_fk` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;";

            runMigrationQuery($conn, $sqlFkTranscriptsLegacy, "Foreign keys for legacy student_transcripts added (if not already present)", $success, $errors);

            // Add FKs for primary transcripts table
            $sqlFkTranscripts = "ALTER TABLE `transcripts`
                ADD CONSTRAINT `transcripts_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
                ADD CONSTRAINT `transcripts_scheme_fk` FOREIGN KEY (`grading_scheme_id`) REFERENCES `grading_schemes` (`id`) ON DELETE RESTRICT,
                ADD CONSTRAINT `transcripts_generated_by_fk` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;";

            runMigrationQuery($conn, $sqlFkTranscripts, "Foreign keys for transcripts added (if not already present)", $success, $errors);

            echo "</div>";

            echo "<div class='step'>";
            echo "<h3>Migration Summary</h3>";
            echo "<p><strong>Successful operations:</strong> " . count($success) . "</p>";
            echo "<p><strong>Errors:</strong> " . count($errors) . "</p>";

            if (count($errors) === 0) {
                echo "<p class='success'><strong>✓ Certificates & Academic Records database migration completed successfully.</strong></p>";
            } else {
                echo "<p class='warning'><strong>⚠ Migration completed with some errors. Please review the messages above and fix any issues if needed.</strong></p>";
            }

            echo "</div>";
        } catch (Exception $e) {
            echo "<div class='step'>";
            echo "<p class='error'>✗ Migration failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">
            <a href="<?php echo APP_URL; ?>dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>


