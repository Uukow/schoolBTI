<?php
/**
 * One‑time script to auto‑register students:
 * - Ensures EACH section has up to 40 students
 * - Uses Somali first/last names
 * - Uses Somali‑style phone numbers (+252 …)
 *
 * Usage:
 * 1) Open in browser:  http://localhost/bti/database/seed_somali_students.php
 * 2) Read the summary carefully. Run only once on a fresh/empty database, or it will only top‑up sections to 40.
 */

// Load full app config & database helpers
require_once __DIR__ . '/../config/config.php';

// Basic safety: only allow if system is installed
if (!SYSTEM_INSTALLED) {
    die('System is not installed yet.');
}

header('Content-Type: text/plain; charset=utf-8');

echo "Starting Somali student seeding...\n\n";

try {
    // Make sure we have a DB connection
    $db = getDB();
    if (!$db) {
        throw new Exception('Database connection not available.');
    }

    // Get role_id for Student
    $sqlRole = "SELECT id FROM roles WHERE role_name = 'Student' LIMIT 1";
    $stmtRole = executeQuery($sqlRole);
    if ($stmtRole === false) {
        throw new Exception('Failed to fetch Student role.');
    }
    $roleRow = fetchOne($stmtRole);
    if (!$roleRow) {
        throw new Exception('Student role not found in roles table.');
    }
    $studentRoleId = (int) $roleRow['id'];

    // Get all sections with their parent class & branch
    $sqlSections = "SELECT 
            s.id AS section_id,
            s.section_name,
            s.capacity,
            c.id AS class_id,
            c.class_name,
            c.branch_id
        FROM sections s
        INNER JOIN classes c ON c.id = s.class_id
        WHERE s.is_active = 1 AND c.is_active = 1";

    $stmtSections = executeQuery($sqlSections);
    if ($stmtSections === false) {
        throw new Exception('Failed to fetch sections/classes.');
    }

    $sections = fetchAll($stmtSections);
    if (empty($sections)) {
        throw new Exception('No active sections/classes found.');
    }

    // Somali first & last names pool
    $somaliFirstNames = [
        'Ayaan','Hodan','Ifrah','Najma','Fartun','Safiya','Zamzam','Rahma',
        'Sagal','Ilhan','Sahra','Nimco','Maryan','Asha','Khadra','Layla',
        'Abdi','Ahmed','Mohamed','Hassan','Yusuf','Ali','Omar','Ismail',
        'Mahad','Mukhtar','Mustafa','Jama','Farah','Ibrahim'
    ];

    $somaliLastNames = [
        'Mohamed','Hassan','Yusuf','Ali','Omar','Nur','Farah','Ismail',
        'Abdulle','Jama','Warsame','Osman','Sheikh','Adam','Abdullahi',
        'Muse','Guled','Roble','Dirie','Said'
    ];

    // Somali mobile prefixes (common)
    $somaliPrefixes = ['61','62','63','65','66','90','91'];

    $totalCreated = 0;
    $perSectionSummary = [];

    beginTransaction();

    foreach ($sections as $section) {
        $classId    = (int) $section['class_id'];
        $sectionId  = (int) $section['section_id'];
        $branchId   = (int) $section['branch_id'];
        $capacity   = !empty($section['capacity']) ? (int) $section['capacity'] : 40;

        // Count existing students already assigned to this class+section
        $sqlCount = "SELECT COUNT(*) AS cnt 
                     FROM students 
                     WHERE current_class_id = ? AND current_section_id = ?";
        $stmtCount = executeQuery($sqlCount, 'ii', [$classId, $sectionId]);
        if ($stmtCount === false) {
            throw new Exception("Failed to count students for class {$classId}, section {$sectionId}.");
        }
        $rowCount = fetchOne($stmtCount);
        $existing = (int) ($rowCount['cnt'] ?? 0);

        $target = 40; // hard requirement: 40 students per section
        if ($existing >= $target) {
            $perSectionSummary[] = "Class {$section['class_name']} / Section {$section['section_name']}: already has {$existing} students (no new students added).";
            continue;
        }

        $toCreate = $target - $existing;

        $perSectionSummary[] = "Class {$section['class_name']} / Section {$section['section_name']}: existing {$existing}, creating {$toCreate} students.";

        for ($i = 1; $i <= $toCreate; $i++) {
            // Pick random Somali name
            $firstName = $somaliFirstNames[array_rand($somaliFirstNames)];
            $lastName  = $somaliLastNames[array_rand($somaliLastNames)];

            // Simple random gender
            $gender = (mt_rand(0, 1) === 0) ? 'Male' : 'Female';

            // Random DOB between 2005‑01‑01 and 2015‑12‑31
            $startDob = strtotime('2005-01-01');
            $endDob   = strtotime('2015-12-31');
            $randDob  = mt_rand($startDob, $endDob);
            $dob      = date('Y-m-d', $randDob);

            // Somali‑style phone: +252 + prefix + 7 digits
            $prefix   = $somaliPrefixes[array_rand($somaliPrefixes)];
            $number7  = str_pad((string) mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $phone    = '+252' . $prefix . $number7;

            // Unique key per student to avoid username/email clashes
            $uniqKey  = $classId . '_' . $sectionId . '_' . $existing . '_' . $i . '_' . time();

            $username = 'stu_' . strtolower(preg_replace('/[^a-z0-9]/i', '', $firstName . $lastName)) . '_' . substr(md5($uniqKey), 0, 5);
            $email    = $username . '@example.som'; // demo email, unique

            // Basic default password (admin can change later)
            $plainPassword  = 'Somali1234'; // >= PASSWORD_MIN_LENGTH
            $hashedPassword = hashPassword($plainPassword);

            // Insert into users table
            $sqlUser = "INSERT INTO users (username, email, password, role_id, branch_id, is_active, is_verified, created_at)
                        VALUES (?, ?, ?, ?, ?, 1, 1, NOW())";
            $stmtUser = executeQuery($sqlUser, 'sssii', [
                $username,
                $email,
                $hashedPassword,
                $studentRoleId,
                $branchId ?: DEFAULT_BRANCH_ID
            ]);

            if ($stmtUser === false) {
                throw new Exception('Failed to insert user record for student: ' . $firstName . ' ' . $lastName);
            }

            $userId = getLastInsertId();

            // Generate UNIQUE student_id & admission_no using uniqid() to avoid collisions
            $uniqueHash  = strtoupper(substr(md5(uniqid((string) $userId, true)), 0, 8));
            $studentId   = STUDENT_ID_PREFIX . $uniqueHash;
            $admissionNo = 'ADM' . $uniqueHash;

            // Admission date = today
            $admissionDate = date('Y-m-d');

            $sqlStudent = "INSERT INTO students 
                (user_id, student_id, admission_no, branch_id, first_name, last_name, gender, date_of_birth,
                 nationality, phone, address, city, state, admission_date,
                 current_class_id, current_section_id, status, created_at)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, 'Somali', ?, 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', ?, ?, ?, 'Active', NOW())";

            // Types: i (user_id), s (student_id), s (admission_no), i (branch_id),
            //        s (first_name), s (last_name), s (gender), s (date_of_birth),
            //        s (phone), s (admission_date), i (current_class_id), i (current_section_id)
            $stmtStudent = executeQuery($sqlStudent, 'ississssssii', [
                $userId,
                $studentId,
                $admissionNo,
                $branchId ?: DEFAULT_BRANCH_ID,
                $firstName,
                $lastName,
                $gender,
                $dob,
                $phone,
                $admissionDate,
                $classId,
                $sectionId
            ]);

            if ($stmtStudent === false) {
                throw new Exception('Failed to insert student record for: ' . $firstName . ' ' . $lastName);
            }

            $totalCreated++;
        }
    }

    commitTransaction();

    echo "Seeding completed successfully.\n";
    echo "---------------------------------------------\n";
    echo "Total new students created: {$totalCreated}\n\n";

    foreach ($perSectionSummary as $line) {
        echo "- {$line}\n";
    }

    echo "\nDefault password for all created student accounts: Somali1234\n";
    echo "You can now log in as a student using the generated usernames/emails.\n";
} catch (Exception $ex) {
    rollbackTransaction();
    echo "ERROR: " . $ex->getMessage() . "\n";
    exit(1);
}


