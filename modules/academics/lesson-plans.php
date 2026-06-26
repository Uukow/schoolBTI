<?php
/**
 * Lesson Plans Management - Academics
 * 
 * View and manage all lesson plans (Admin/Super Admin)
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Lesson Plans Management';

// Get current user
$currentUser = getCurrentUser();
$isSuperAdmin = hasRole(['Super Admin']);
$isAdmin = hasRole(['Admin']);
$isTeacher = hasRole(['Teacher']);

// Get teacher record if user is a teacher
$teacher = null;
$teacherId = null;
if ($isTeacher && !$isSuperAdmin) {
    $teacher = getTeacherByUserId($currentUser['id']);
    if ($teacher) {
        $teacherId = $teacher['id'];
    } else {
        // Log for debugging
        error_log("Teacher profile not found for user ID: " . $currentUser['id'] . ", Email: " . ($currentUser['email'] ?? 'N/A'));
        
        // AGGRESSIVE LOOKUP: Try multiple methods to find the teacher ID
        
        // Method 1: Find staff by user_id (direct link)
        $checkSql = "SELECT s.id FROM staff s WHERE s.user_id = ? LIMIT 1";
        $checkStmt = executeQuery($checkSql, 'i', [$currentUser['id']]);
        $directMatch = fetchOne($checkStmt);
        if ($directMatch) {
            $teacherId = $directMatch['id'];
            error_log("DEBUG: Found teacher ID by user_id: $teacherId");
        }
        
        // Method 2: Find staff by email match (staff.email = user.email)
        if (!$teacherId && !empty($currentUser['email'])) {
            $emailCheckSql = "SELECT s.id FROM staff s WHERE s.email = ? LIMIT 1";
            $emailStmt = executeQuery($emailCheckSql, 's', [$currentUser['email']]);
            $emailMatch = fetchOne($emailStmt);
            if ($emailMatch) {
                $teacherId = $emailMatch['id'];
                error_log("DEBUG: Found teacher ID by staff email match: $teacherId");
            }
        }
        
        // Method 3: Find staff by username/name match
        if (!$teacherId) {
            $nameParts = explode(' ', $currentUser['username'] ?? '');
            if (count($nameParts) >= 2) {
                $firstName = $nameParts[0];
                $lastName = implode(' ', array_slice($nameParts, 1));
                $nameCheckSql = "SELECT s.id FROM staff s WHERE s.first_name LIKE ? AND s.last_name LIKE ? LIMIT 1";
                $nameStmt = executeQuery($nameCheckSql, 'ss', ["%$firstName%", "%$lastName%"]);
                $nameMatch = fetchOne($nameStmt);
                if ($nameMatch) {
                    $teacherId = $nameMatch['id'];
                    error_log("DEBUG: Found teacher ID by name match: $teacherId");
                }
            }
        }
        
        // Method 4: Find lesson plans directly and extract teacher_id from them
        // This is a last resort - if lesson plans exist, use their teacher_id
        if (!$teacherId && !empty($currentUser['email'])) {
            $lpDirectSql = "SELECT DISTINCT lp.teacher_id, s.email as staff_email, s.first_name, s.last_name
                           FROM lesson_plans lp
                           INNER JOIN staff s ON lp.teacher_id = s.id
                           WHERE s.email = ? 
                           LIMIT 1";
            $lpDirectStmt = executeQuery($lpDirectSql, 's', [$currentUser['email']]);
            $lpDirectMatch = fetchOne($lpDirectStmt);
            if ($lpDirectMatch) {
                $teacherId = $lpDirectMatch['teacher_id'];
                error_log("DEBUG: Found teacher ID from lesson plans by email: $teacherId");
            }
        }
        
        // Method 5: If still no match, check all lesson plans and see if any staff has matching email
        // This handles cases where staff.email matches but staff.user_id is NULL
        if (!$teacherId && !empty($currentUser['email'])) {
            $allStaffSql = "SELECT s.id, s.email, s.first_name, s.last_name, s.user_id
                           FROM staff s
                           WHERE s.email = ? OR s.email LIKE ?
                           LIMIT 5";
            $allStaffStmt = executeQuery($allStaffSql, 'ss', [$currentUser['email'], '%' . $currentUser['email'] . '%']);
            $allStaffMatches = fetchAll($allStaffStmt);
            if (!empty($allStaffMatches)) {
                // Use the first match
                $teacherId = $allStaffMatches[0]['id'];
                error_log("DEBUG: Found teacher ID from staff table (broad search): $teacherId");
                error_log("DEBUG: Staff matches: " . print_r($allStaffMatches, true));
            }
        }
        
        if ($teacherId) {
            error_log("DEBUG: Successfully determined teacher ID: $teacherId for user ID: {$currentUser['id']}");
        } else {
            error_log("DEBUG: Could not determine teacher ID for user ID: {$currentUser['id']}, Email: " . ($currentUser['email'] ?? 'N/A'));
        }
    }
    // Don't redirect if teacher profile not found - allow page to load with empty data
}

// Get filter parameters
$classFilter = $_GET['class_id'] ?? '';
$subjectFilter = $_GET['subject_id'] ?? '';
$teacherFilter = $_GET['teacher_id'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Get branch filter
$branchFilter = '';
$branchFilterClasses = '';
$branchId = null;

if ($isSuperAdmin) {
    $branchId = $_GET['branch_id'] ?? null;
    if ($branchId) {
        $branchFilter = " AND c.branch_id = $branchId";
        $branchFilterClasses = " AND branch_id = $branchId";
    }
} else {
    $branchId = $currentUser['branch_id'] ?? null;
    if ($branchId) {
        $branchFilter = " AND c.branch_id = $branchId";
        $branchFilterClasses = " AND branch_id = $branchId";
    }
}

$currentSession = getCurrentSession();

// Check if session exists
if (!$currentSession) {
    $_SESSION['error'] = 'No active session found. Please contact administrator.';
    if ($isTeacher) {
        redirect(APP_URL . 'modules/teacher/dashboard.php');
    } else {
        redirect(APP_URL . 'dashboard.php');
    }
}

// Build query for lesson plans
if ($isTeacher && !$isSuperAdmin && !$teacherId) {
    // Teacher without profile - try to find lesson plans by email or staff record
    $potentialTeacherIds = [];
    
    // Try to find staff record by email
    if (!empty($currentUser['email'])) {
        $emailCheckSql = "SELECT s.id FROM staff s WHERE s.email = ? LIMIT 1";
        $emailStmt = executeQuery($emailCheckSql, 's', [$currentUser['email']]);
        $emailMatch = fetchOne($emailStmt);
        if ($emailMatch) {
            $potentialTeacherIds[] = $emailMatch['id'];
            error_log("DEBUG: Found potential teacher ID by email: " . $emailMatch['id']);
        }
    }
    
    // Try to find staff record by matching first/last name from user
    $nameParts = explode(' ', $currentUser['username'] ?? '');
    if (count($nameParts) >= 2) {
        $firstName = $nameParts[0];
        $lastName = implode(' ', array_slice($nameParts, 1));
        $nameCheckSql = "SELECT s.id FROM staff s WHERE s.first_name LIKE ? AND s.last_name LIKE ? LIMIT 1";
        $nameStmt = executeQuery($nameCheckSql, 'ss', ["%$firstName%", "%$lastName%"]);
        $nameMatch = fetchOne($nameStmt);
        if ($nameMatch && !in_array($nameMatch['id'], $potentialTeacherIds)) {
            $potentialTeacherIds[] = $nameMatch['id'];
            error_log("DEBUG: Found potential teacher ID by name: " . $nameMatch['id']);
        }
    }
    
    // If we found potential teacher IDs, try to show their lesson plans
    if (!empty($potentialTeacherIds)) {
        $teacherId = $potentialTeacherIds[0]; // Use the first match
        error_log("DEBUG: Using potential teacher ID: $teacherId for user ID: " . $currentUser['id']);
        
        // Now fetch lesson plans using this teacher ID
        $sql = "SELECT lp.*, c.class_name, c.class_code, s.subject_name, s.subject_code,
                st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id,
                b.branch_name
                FROM lesson_plans lp
                INNER JOIN classes c ON lp.class_id = c.id
                INNER JOIN subjects s ON lp.subject_id = s.id
                LEFT JOIN staff st ON lp.teacher_id = st.id
                LEFT JOIN branches b ON c.branch_id = b.id
                WHERE lp.teacher_id = ? AND lp.session_id = ?
                AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')";
        
        $params = [$teacherId, $currentSession['id']];
        $types = 'ii';
        
        // Apply filters
        if (!empty($classFilter)) {
            $sql .= " AND lp.class_id = ?";
            $params[] = $classFilter;
            $types .= 'i';
        }
        
        if (!empty($subjectFilter)) {
            $sql .= " AND lp.subject_id = ?";
            $params[] = $subjectFilter;
            $types .= 'i';
        }
        
        if (!empty($statusFilter)) {
            $sql .= " AND lp.status = ?";
            $params[] = $statusFilter;
            $types .= 's';
        }
        
        $sql .= " ORDER BY lp.lesson_date DESC, lp.created_at DESC";
        
        $stmt = executeQuery($sql, $types, $params);
        $lessonPlans = fetchAll($stmt);
        
        // Get classes and subjects for this teacher (excluding graduated classes)
        $classesSql = "SELECT DISTINCT c.* 
                       FROM classes c
                       WHERE c.is_active = 1 
                       AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
                       AND (
                           c.id IN (
                               SELECT DISTINCT cs.class_id 
                               FROM class_subjects cs 
                               WHERE cs.teacher_id = ? AND cs.session_id = ?
                           )
                           OR c.id IN (
                               SELECT DISTINCT lp.class_id 
                               FROM lesson_plans lp 
                               WHERE lp.teacher_id = ? AND lp.session_id = ?
                           )
                       )
                       ORDER BY c.class_order";
        $classes = fetchAll(executeQuery($classesSql, 'iiii', [$teacherId, $currentSession['id'], $teacherId, $currentSession['id']]));
        
        $subjectsSql = "SELECT DISTINCT s.* 
                        FROM subjects s
                        WHERE s.is_active = 1 
                        AND (
                            s.id IN (
                                SELECT DISTINCT cs.subject_id 
                                FROM class_subjects cs 
                                WHERE cs.teacher_id = ? AND cs.session_id = ?
                            )
                            OR s.id IN (
                                SELECT DISTINCT lp.subject_id 
                                FROM lesson_plans lp 
                                WHERE lp.teacher_id = ? AND lp.session_id = ?
                            )
                        )
                        ORDER BY s.subject_name";
        $subjects = fetchAll(executeQuery($subjectsSql, 'iiii', [$teacherId, $currentSession['id'], $teacherId, $currentSession['id']]));
        
        $teachers = [];
        
        $_SESSION['info'] = 'Note: Your teacher profile is not properly linked. Lesson plans are shown based on email/name match. Please contact administrator to properly link your account.';
    } else {
        // No teacherId found - try one more method: query lesson plans directly by email
        if (!empty($currentUser['email'])) {
            // Direct query: find lesson plans where staff.email matches user.email
            $directLpSql = "SELECT lp.*, c.class_name, c.class_code, s.subject_name, s.subject_code,
                           st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id, st.id as staff_id,
                           b.branch_name
                           FROM lesson_plans lp
                           INNER JOIN classes c ON lp.class_id = c.id
                           INNER JOIN subjects s ON lp.subject_id = s.id
                           INNER JOIN staff st ON lp.teacher_id = st.id
                           LEFT JOIN branches b ON c.branch_id = b.id
                           WHERE st.email = ? AND lp.session_id = ?
                           AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')";
            
            $directParams = [$currentUser['email'], $currentSession['id']];
            $directTypes = 'si';
            
            // Apply filters
            if (!empty($classFilter)) {
                $directLpSql .= " AND lp.class_id = ?";
                $directParams[] = $classFilter;
                $directTypes .= 'i';
            }
            
            if (!empty($subjectFilter)) {
                $directLpSql .= " AND lp.subject_id = ?";
                $directParams[] = $subjectFilter;
                $directTypes .= 'i';
            }
            
            if (!empty($statusFilter)) {
                $directLpSql .= " AND lp.status = ?";
                $directParams[] = $statusFilter;
                $directTypes .= 's';
            }
            
            $directLpSql .= " ORDER BY lp.lesson_date DESC, lp.created_at DESC";
            
            $directLpStmt = executeQuery($directLpSql, $directTypes, $directParams);
            $directLessonPlans = fetchAll($directLpStmt);
            
            if (!empty($directLessonPlans)) {
                // Found lesson plans by email! Use the first one's teacher_id
                $teacherId = $directLessonPlans[0]['staff_id'];
                $lessonPlans = $directLessonPlans;
                error_log("DEBUG: Found lesson plans directly by email match. Using teacher_id: $teacherId");
                
                // Get classes and subjects from these lesson plans
                $classIds = array_unique(array_column($directLessonPlans, 'class_id'));
                $subjectIds = array_unique(array_column($directLessonPlans, 'subject_id'));
                
                if (!empty($classIds)) {
                    $placeholders = implode(',', array_fill(0, count($classIds), '?'));
                    $classesSql = "SELECT * FROM classes 
                                    WHERE id IN ($placeholders) 
                                    AND is_active = 1 
                                    AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                                    ORDER BY class_order";
                    $classes = fetchAll(executeQuery($classesSql, str_repeat('i', count($classIds)), $classIds));
                } else {
                    $classes = [];
                }
                
                if (!empty($subjectIds)) {
                    $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
                    $subjectsSql = "SELECT * FROM subjects WHERE id IN ($placeholders) AND is_active = 1 ORDER BY subject_name";
                    $subjects = fetchAll(executeQuery($subjectsSql, str_repeat('i', count($subjectIds)), $subjectIds));
                } else {
                    $subjects = [];
                }
                
                $teachers = [];
                $_SESSION['info'] = 'Found lesson plans by email match. Your teacher profile (Staff ID: ' . $teacherId . ') is not properly linked to your user account. Please contact administrator to link your account.';
            } else {
                // No matches found - show empty data with helpful message
                $lessonPlans = [];
                $classes = [];
                $subjects = [];
                $teachers = [];
                $_SESSION['info'] = 'Teacher profile not found. Please contact administrator to link your staff profile to your account.';
            }
        } else {
            // No email available - show empty data with helpful message
            $lessonPlans = [];
            $classes = [];
            $subjects = [];
            $teachers = [];
            $_SESSION['info'] = 'Teacher profile not found. Please contact administrator to link your staff profile to your account.';
        }
    }
} elseif ($isTeacher && !$isSuperAdmin && $teacherId) {
    // Teachers see only their own lesson plans
    // First, let's check what lesson plans exist for this teacher (for debugging)
    $debugSql = "SELECT lp.id, lp.teacher_id, lp.session_id, st.first_name, st.last_name 
                 FROM lesson_plans lp 
                 LEFT JOIN staff st ON lp.teacher_id = st.id 
                 WHERE lp.teacher_id = ?";
    $debugStmt = executeQuery($debugSql, 'i', [$teacherId]);
    $debugPlans = fetchAll($debugStmt);
    error_log("DEBUG: Teacher ID $teacherId has " . count($debugPlans) . " lesson plans total");
    if (!empty($debugPlans)) {
        error_log("DEBUG: Sample lesson plan - ID: {$debugPlans[0]['id']}, Teacher ID: {$debugPlans[0]['teacher_id']}, Session ID: {$debugPlans[0]['session_id']}");
    }
    
    // Try current session first, but if no results, show all sessions
    $sql = "SELECT lp.*, c.class_name, c.class_code, s.subject_name, s.subject_code,
            st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id,
            b.branch_name, ses.session_name
            FROM lesson_plans lp
            INNER JOIN classes c ON lp.class_id = c.id
            INNER JOIN subjects s ON lp.subject_id = s.id
            LEFT JOIN staff st ON lp.teacher_id = st.id
            LEFT JOIN branches b ON c.branch_id = b.id
            LEFT JOIN academic_sessions ses ON lp.session_id = ses.id
            WHERE lp.teacher_id = ? AND lp.session_id = ?
            AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')";
    
    $params = [$teacherId, $currentSession['id']];
    $types = 'ii';
    
    // Apply branch filter if needed
    if (!empty($branchFilter)) {
        $sql .= $branchFilter;
    }
    
    // Apply filters
    if (!empty($classFilter)) {
        $sql .= " AND lp.class_id = ?";
        $params[] = $classFilter;
        $types .= 'i';
    }
    
    if (!empty($subjectFilter)) {
        $sql .= " AND lp.subject_id = ?";
        $params[] = $subjectFilter;
        $types .= 'i';
    }
    
    if (!empty($statusFilter)) {
        $sql .= " AND lp.status = ?";
        $params[] = $statusFilter;
        $types .= 's';
    }
    
    $sql .= " ORDER BY lp.lesson_date DESC, lp.created_at DESC";
    
    error_log("DEBUG: Query: $sql");
    error_log("DEBUG: Params: " . print_r($params, true));
    error_log("DEBUG: Types: $types");
    
    $stmt = executeQuery($sql, $types, $params);
    $lessonPlans = fetchAll($stmt);
    
    // Log for debugging
    error_log("Teacher lesson plans query - Teacher ID: $teacherId, Session ID: {$currentSession['id']}, Found: " . count($lessonPlans));
    
    // If no results, check if there are lesson plans with different session
    if (empty($lessonPlans)) {
        error_log("DEBUG: No lesson plans found for current session. Checking all sessions...");
        
        // Query all lesson plans for this teacher (any session) with full details
        $allSessionSql = "SELECT lp.*, c.class_name, c.class_code, s.subject_name, s.subject_code,
                         st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id,
                         b.branch_name, ses.session_name
                         FROM lesson_plans lp
                         INNER JOIN classes c ON lp.class_id = c.id
                         INNER JOIN subjects s ON lp.subject_id = s.id
                         LEFT JOIN staff st ON lp.teacher_id = st.id
                         LEFT JOIN branches b ON c.branch_id = b.id
                         LEFT JOIN academic_sessions ses ON lp.session_id = ses.id
                         WHERE lp.teacher_id = ?
                         AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')";
        
        $allSessionParams = [$teacherId];
        $allSessionTypes = 'i';
        
        // Apply branch filter if needed
        if (!empty($branchFilter)) {
            $allSessionSql .= $branchFilter;
        }
        
        // Apply filters (but not session filter)
        if (!empty($classFilter)) {
            $allSessionSql .= " AND lp.class_id = ?";
            $allSessionParams[] = $classFilter;
            $allSessionTypes .= 'i';
        }
        
        if (!empty($subjectFilter)) {
            $allSessionSql .= " AND lp.subject_id = ?";
            $allSessionParams[] = $subjectFilter;
            $allSessionTypes .= 'i';
        }
        
        if (!empty($statusFilter)) {
            $allSessionSql .= " AND lp.status = ?";
            $allSessionParams[] = $statusFilter;
            $allSessionTypes .= 's';
        }
        
        $allSessionSql .= " ORDER BY lp.session_id DESC, lp.lesson_date DESC LIMIT 50";
        
        $allSessionStmt = executeQuery($allSessionSql, $allSessionTypes, $allSessionParams);
        $allSessionPlans = fetchAll($allSessionStmt);
        
        if (!empty($allSessionPlans)) {
            $lessonPlans = $allSessionPlans;
            $_SESSION['info'] = 'Showing lesson plans from all sessions. Current active session is "' . htmlspecialchars($currentSession['session_name'] ?? 'N/A') . '".';
            error_log("DEBUG: Found " . count($allSessionPlans) . " lesson plans across all sessions");
        } else {
            // Check all lesson plans for this teacher (no filters, all sessions) - just for info
            $checkAllSql = "SELECT lp.id, lp.teacher_id, lp.session_id, lp.class_id, lp.subject_id, lp.status, 
                                   c.class_name, s.subject_name, ses.session_name
                            FROM lesson_plans lp
                            LEFT JOIN classes c ON lp.class_id = c.id
                            LEFT JOIN subjects s ON lp.subject_id = s.id
                            LEFT JOIN academic_sessions ses ON lp.session_id = ses.id
                            WHERE lp.teacher_id = ?
                            ORDER BY lp.session_id DESC, lp.lesson_date DESC";
            $checkAllStmt = executeQuery($checkAllSql, 'i', [$teacherId]);
            $allPlans = fetchAll($checkAllStmt);
            
            if (!empty($allPlans)) {
                error_log("DEBUG: Teacher has " . count($allPlans) . " lesson plans total:");
                foreach ($allPlans as $plan) {
                    error_log("  - Plan ID: {$plan['id']}, Session: {$plan['session_id']} ({$plan['session_name']}), Class: {$plan['class_id']} ({$plan['class_name']}), Subject: {$plan['subject_id']} ({$plan['subject_name']}), Status: {$plan['status']}");
                }
                error_log("DEBUG: Current filters - Session: {$currentSession['id']}, Class: " . ($classFilter ?: 'none') . ", Subject: " . ($subjectFilter ?: 'none') . ", Status: " . ($statusFilter ?: 'none'));
                
                // Check if any plans are in different sessions
                $differentSessions = array_filter($allPlans, function($plan) use ($currentSession) {
                    return $plan['session_id'] != $currentSession['id'];
                });
                
                if (!empty($differentSessions)) {
                    $_SESSION['info'] = 'You have lesson plans in other academic sessions. Current session: ' . htmlspecialchars($currentSession['session_name'] ?? 'N/A');
                }
            } else {
                error_log("DEBUG: Teacher ID $teacherId has NO lesson plans at all in the database");
            }
        }
    }
} else {
    // Super Admin and Admin see all lesson plans
    $sql = "SELECT lp.*, c.class_name, c.class_code, s.subject_name, s.subject_code,
            st.first_name as teacher_first_name, st.last_name as teacher_last_name, st.staff_id,
            b.branch_name
            FROM lesson_plans lp
            INNER JOIN classes c ON lp.class_id = c.id
            INNER JOIN subjects s ON lp.subject_id = s.id
            LEFT JOIN staff st ON lp.teacher_id = st.id
            LEFT JOIN branches b ON c.branch_id = b.id
            WHERE lp.session_id = ? 
            AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
            $branchFilter";
    
    $params = [$currentSession['id']];
    $types = 'i';
    
    // Apply filters
    if (!empty($classFilter)) {
        $sql .= " AND lp.class_id = ?";
        $params[] = $classFilter;
        $types .= 'i';
    }
    
    if (!empty($subjectFilter)) {
        $sql .= " AND lp.subject_id = ?";
        $params[] = $subjectFilter;
        $types .= 'i';
    }
    
    if (!empty($teacherFilter)) {
        $sql .= " AND lp.teacher_id = ?";
        $params[] = $teacherFilter;
        $types .= 'i';
    }
    
    if (!empty($statusFilter)) {
        $sql .= " AND lp.status = ?";
        $params[] = $statusFilter;
        $types .= 's';
    }
    
    $sql .= " ORDER BY lp.lesson_date DESC, lp.created_at DESC";
    
    $stmt = executeQuery($sql, $types, $params);
    $lessonPlans = fetchAll($stmt);
}

// Get classes for filter
if ($isTeacher && !$isSuperAdmin && !$teacherId) {
    // Teacher without profile - try to get classes from user's branch as fallback
    $userBranchId = $currentUser['branch_id'] ?? null;
    if ($userBranchId) {
        // Show classes from user's branch as fallback (excluding graduated classes)
        $classesSql = "SELECT * FROM classes 
                        WHERE is_active = 1 
                        AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                        AND branch_id = ? 
                        ORDER BY class_order";
        $classes = fetchAll(executeQuery($classesSql, 'i', [$userBranchId]));
        
        $subjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
        $subjects = fetchAll(executeQuery($subjectsSql));
        
        error_log("DEBUG: Teacher profile not found, using user branch $userBranchId - Classes: " . count($classes) . ", Subjects: " . count($subjects));
    } else {
        // No branch info - empty arrays
        $classes = [];
        $subjects = [];
    }
    $teachers = [];
} elseif ($isTeacher && !$isSuperAdmin && $teacherId) {
    // Get teacher's branch_id for fallback
    $teacherBranchId = $teacher['branch_id'] ?? null;
    
    // Teachers see classes from their assignments AND from their lesson plans (excluding graduated classes)
    $classesSql = "SELECT DISTINCT c.* 
                   FROM classes c
                   WHERE c.is_active = 1 
                   AND (c.graduation_status IS NULL OR c.graduation_status != 'Graduated')
                   AND (
                       c.id IN (
                           SELECT DISTINCT cs.class_id 
                           FROM class_subjects cs 
                           WHERE cs.teacher_id = ? AND cs.session_id = ?
                       )
                       OR c.id IN (
                           SELECT DISTINCT lp.class_id 
                           FROM lesson_plans lp 
                           WHERE lp.teacher_id = ? AND lp.session_id = ?
                       )
                   )";
    
    if (!empty($branchFilterClasses)) {
        $classesSql .= $branchFilterClasses;
    } elseif ($teacherBranchId) {
        // Fallback: if no branch filter but teacher has branch, filter by teacher's branch
        $classesSql .= " AND c.branch_id = $teacherBranchId";
    }
    
    $classesSql .= " ORDER BY c.class_order";
    $classes = fetchAll(executeQuery($classesSql, 'iiii', [$teacherId, $currentSession['id'], $teacherId, $currentSession['id']]));
    
    // If no classes found from assignments/lesson plans, show all classes in teacher's branch as fallback
    if (empty($classes) && $teacherBranchId) {
        $fallbackClassesSql = "SELECT * FROM classes WHERE is_active = 1 AND branch_id = ? ORDER BY class_order";
        $classes = fetchAll(executeQuery($fallbackClassesSql, 'i', [$teacherBranchId]));
        error_log("DEBUG: No classes from assignments/lesson plans for teacher $teacherId, using fallback (branch $teacherBranchId): " . count($classes) . " classes");
    }
    
    // Teachers see subjects from their assignments AND from their lesson plans
    $subjectsSql = "SELECT DISTINCT s.* 
                    FROM subjects s
                    WHERE s.is_active = 1 
                    AND (
                        s.id IN (
                            SELECT DISTINCT cs.subject_id 
                            FROM class_subjects cs 
                            WHERE cs.teacher_id = ? AND cs.session_id = ?
                        )
                        OR s.id IN (
                            SELECT DISTINCT lp.subject_id 
                            FROM lesson_plans lp 
                            WHERE lp.teacher_id = ? AND lp.session_id = ?
                        )
                    )
                    ORDER BY s.subject_name";
    $subjects = fetchAll(executeQuery($subjectsSql, 'iiii', [$teacherId, $currentSession['id'], $teacherId, $currentSession['id']]));
    
    // If no subjects found, show all active subjects as fallback
    if (empty($subjects)) {
        $fallbackSubjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
        $subjects = fetchAll(executeQuery($fallbackSubjectsSql));
        error_log("DEBUG: No subjects from assignments/lesson plans for teacher $teacherId, using fallback: " . count($subjects) . " subjects");
    }
    
    // Log for debugging
    error_log("DEBUG: Teacher $teacherId - Classes: " . count($classes) . ", Subjects: " . count($subjects));
    
    // Teachers don't need teacher filter (they only see their own)
    $teachers = [];
} else {
    // Super Admin and Admin see all (excluding graduated classes)
    $classesSql = "SELECT * FROM classes 
                    WHERE is_active = 1 
                    AND (graduation_status IS NULL OR graduation_status != 'Graduated')
                    $branchFilterClasses 
                    ORDER BY class_order";
    $classes = fetchAll(executeQuery($classesSql));
    
    $subjectsSql = "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name";
    $subjects = fetchAll(executeQuery($subjectsSql));
    
    $teachersSql = "SELECT DISTINCT s.* 
                    FROM staff s
                    INNER JOIN lesson_plans lp ON s.id = lp.teacher_id
                    INNER JOIN classes c ON lp.class_id = c.id
                    WHERE 1=1 $branchFilter
                    ORDER BY s.first_name, s.last_name";
    $teachers = fetchAll(executeQuery($teachersSql));
}

// Get branches for filter (Super Admin only)
$branches = [];
if ($isSuperAdmin) {
    $branchesSql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name";
    $branches = fetchAll(executeQuery($branchesSql));
}

// Get all teachers for Admin/Super Admin (for add modal)
$allTeachers = [];
if ($isSuperAdmin || $isAdmin) {
    // Try to get teachers by designation first, then by user role
    $allTeachersSql = "SELECT DISTINCT s.* 
                       FROM staff s 
                       LEFT JOIN users u ON s.user_id = u.id 
                       LEFT JOIN roles r ON u.role_id = r.id 
                       WHERE s.status = 'Active' 
                       AND (s.designation LIKE '%Teacher%' 
                            OR s.designation LIKE '%teacher%' 
                            OR r.role_name = 'Teacher')";
    
    // Add branch filter for Admin
    if ($isAdmin && !$isSuperAdmin && $branchId) {
        $allTeachersSql .= " AND s.branch_id = $branchId";
    }
    
    $allTeachersSql .= " ORDER BY s.first_name, s.last_name";
    $allTeachers = fetchAll(executeQuery($allTeachersSql));
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <?php if ($isTeacher && !$isSuperAdmin && !$isAdmin && $teacherId): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLessonPlanModal">
                                <i class="ri-add-line"></i> Add Lesson Plan
                            </button>
                            <?php elseif ($isSuperAdmin || $isAdmin): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLessonPlanModal">
                                <i class="ri-add-line"></i> Add Lesson Plan
                            </button>
                            <?php endif; ?>
                        </div>
                        <h4 class="page-title">Lesson Plans Management</h4>
                    </div>
                </div>
            </div>

            <?php if ($isSuperAdmin): ?>
            <!-- Branch Filter -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Branch</label>
                                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" <?php echo ($branchId == $branch['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <?php if ($isSuperAdmin && $branchId): ?>
                                <input type="hidden" name="branch_id" value="<?php echo $branchId; ?>">
                                <?php endif; ?>
                                <div class="col-md-3">
                                    <label class="form-label">Class</label>
                                    <select name="class_id" class="form-select">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($classFilter == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Subject</label>
                                    <select name="subject_id" class="form-select">
                                        <option value="">All Subjects</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>" <?php echo ($subjectFilter == $subject['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if (!$isTeacher || $isSuperAdmin): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Teacher</label>
                                    <select name="teacher_id" class="form-select">
                                        <option value="">All Teachers</option>
                                        <?php foreach ($teachers as $t): ?>
                                            <option value="<?php echo $t['id']; ?>" <?php echo ($teacherFilter == $t['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="Draft" <?php echo ($statusFilter == 'Draft') ? 'selected' : ''; ?>>Draft</option>
                                        <option value="Published" <?php echo ($statusFilter == 'Published') ? 'selected' : ''; ?>>Published</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Filter
                                    </button>
                                    <a href="<?php echo APP_URL; ?>modules/academics/lesson-plans.php<?php echo $isSuperAdmin && $branchId ? '?branch_id=' . $branchId : ''; ?>" class="btn btn-secondary">
                                        <i class="ri-refresh-line"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lesson Plans List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3"><?php echo ($isTeacher && !$isSuperAdmin) ? 'My Lesson Plans' : 'All Lesson Plans'; ?> (<?php echo count($lessonPlans); ?>)</h4>
                            
                            <?php if ($isTeacher && !$isSuperAdmin): ?>
                            <!-- Diagnostic Information for Teachers -->
                            <div class="alert alert-secondary mb-3">
                                <strong><i class="ri-bug-line"></i> Diagnostic Information:</strong><br>
                                <small>
                                    <strong>User ID:</strong> <?php echo $currentUser['id']; ?><br>
                                    <strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email'] ?? 'N/A'); ?><br>
                                    <strong>Username:</strong> <?php echo htmlspecialchars($currentUser['username'] ?? 'N/A'); ?><br>
                                    <strong>Teacher ID Found:</strong> <?php echo $teacherId ? $teacherId : 'NOT FOUND'; ?><br>
                                    <strong>Current Session:</strong> <?php echo htmlspecialchars($currentSession['session_name'] ?? 'N/A'); ?> (ID: <?php echo $currentSession['id'] ?? 'N/A'; ?>)<br><br>
                                    
                                    <?php
                                    // Check what staff records exist with this email
                                    if (!empty($currentUser['email'])):
                                        $staffCheckSql = "SELECT s.id, s.staff_id, s.first_name, s.last_name, s.email, s.user_id, s.designation, s.status
                                                         FROM staff s
                                                         WHERE s.email = ? OR s.email LIKE ?
                                                         LIMIT 5";
                                        $staffCheckStmt = executeQuery($staffCheckSql, 'ss', [$currentUser['email'], '%' . $currentUser['email'] . '%']);
                                        $staffMatches = fetchAll($staffCheckStmt);
                                    ?>
                                    <strong>Staff Records with Matching Email:</strong><br>
                                    <?php if (!empty($staffMatches)): ?>
                                        <ul class="mb-0">
                                            <?php foreach ($staffMatches as $sm): ?>
                                                <li>
                                                    Staff ID: <?php echo $sm['id']; ?> | 
                                                    Name: <?php echo htmlspecialchars($sm['first_name'] . ' ' . $sm['last_name']); ?> | 
                                                    Email: <?php echo htmlspecialchars($sm['email'] ?? 'N/A'); ?> | 
                                                    User ID: <?php echo $sm['user_id'] ?? 'NULL'; ?> | 
                                                    Designation: <?php echo htmlspecialchars($sm['designation'] ?? 'N/A'); ?> | 
                                                    Status: <?php echo htmlspecialchars($sm['status'] ?? 'N/A'); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="text-danger">No staff records found with email "<?php echo htmlspecialchars($currentUser['email']); ?>"</span><br>
                                    <?php endif; ?>
                                    
                                    <br>
                                    <?php
                                    // Check what lesson plans exist for staff with this email
                                    $lpCheckSql = "SELECT lp.id, lp.teacher_id, lp.session_id, lp.lesson_title, lp.status,
                                                  s.id as staff_id, s.first_name, s.last_name, s.email as staff_email,
                                                  ses.session_name
                                                  FROM lesson_plans lp
                                                  INNER JOIN staff s ON lp.teacher_id = s.id
                                                  LEFT JOIN academic_sessions ses ON lp.session_id = ses.id
                                                  WHERE s.email = ?
                                                  ORDER BY lp.session_id DESC, lp.lesson_date DESC
                                                  LIMIT 10";
                                    $lpCheckStmt = executeQuery($lpCheckSql, 's', [$currentUser['email']]);
                                    $lpMatches = fetchAll($lpCheckStmt);
                                    ?>
                                    <strong>Lesson Plans for Staff with Matching Email:</strong><br>
                                    <?php if (!empty($lpMatches)): ?>
                                        <ul class="mb-0">
                                            <?php foreach ($lpMatches as $lpm): ?>
                                                <li>
                                                    Lesson Plan ID: <?php echo $lpm['id']; ?> | 
                                                    Teacher ID: <?php echo $lpm['teacher_id']; ?> | 
                                                    "<?php echo htmlspecialchars($lpm['lesson_title']); ?>" | 
                                                    Session: <?php echo htmlspecialchars($lpm['session_name'] ?? 'ID ' . $lpm['session_id']); ?> | 
                                                    Status: <?php echo htmlspecialchars($lpm['status']); ?>
                                                    <?php if ($lpm['session_id'] != $currentSession['id']): ?>
                                                        <span class="badge bg-warning">Different Session</span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="text-danger">No lesson plans found for staff with email "<?php echo htmlspecialchars($currentUser['email']); ?>"</span><br>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <br>
                                    <?php
                                    // Show ALL staff records to help identify which one should be linked
                                    $allStaffSql = "SELECT s.id, s.staff_id, s.first_name, s.last_name, s.email, s.user_id, s.designation, s.status,
                                                   u.id as linked_user_id, u.username as linked_username, u.email as linked_user_email,
                                                   (SELECT COUNT(*) FROM lesson_plans lp WHERE lp.teacher_id = s.id) as lesson_plan_count
                                                   FROM staff s
                                                   LEFT JOIN users u ON s.user_id = u.id
                                                   WHERE s.designation LIKE '%Teacher%' OR s.designation LIKE '%teacher%'
                                                   ORDER BY s.first_name, s.last_name
                                                   LIMIT 20";
                                    $allStaffStmt = executeQuery($allStaffSql);
                                    $allStaffRecords = fetchAll($allStaffStmt);
                                    ?>
                                    <strong>All Teacher Staff Records (to help identify which one to link):</strong><br>
                                    <?php if (!empty($allStaffRecords)): ?>
                                        <div class="table-responsive mt-2">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Staff ID</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Linked User</th>
                                                        <th>Lesson Plans</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($allStaffRecords as $staff): ?>
                                                        <tr <?php echo ($staff['user_id'] == $currentUser['id']) ? 'class="table-success"' : ''; ?>>
                                                            <td><?php echo htmlspecialchars($staff['staff_id']); ?></td>
                                                            <td><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($staff['email'] ?? 'N/A'); ?></td>
                                                            <td>
                                                                <?php if ($staff['linked_user_id']): ?>
                                                                    User ID: <?php echo $staff['linked_user_id']; ?> (<?php echo htmlspecialchars($staff['linked_username'] ?? 'N/A'); ?>)
                                                                    <?php if ($staff['linked_user_id'] == $currentUser['id']): ?>
                                                                        <span class="badge bg-success">This is you!</span>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Not linked</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo $staff['lesson_plan_count']; ?> plan(s)</td>
                                                            <td>
                                                                <?php if ($staff['lesson_plan_count'] > 0 && !$staff['linked_user_id']): ?>
                                                                    <small class="text-info">This staff has lesson plans but is not linked to any user</small>
                                                                <?php elseif ($staff['lesson_plan_count'] > 0 && $staff['linked_user_id'] != $currentUser['id']): ?>
                                                                    <small class="text-warning">This staff has lesson plans but is linked to a different user</small>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <small class="text-muted">To link a staff record to your account, go to Settings → User Management → Edit your user account → Select the staff record in "Link to Staff" field.</small>
                                    <?php else: ?>
                                        <span class="text-danger">No staff records found</span>
                                    <?php endif; ?>
                                    
                                    <br><br>
                                    <?php
                                    // Show ALL lesson plans to see which teacher_id they're assigned to
                                    $allLpSql = "SELECT lp.id, lp.teacher_id, lp.lesson_title, lp.session_id, lp.status,
                                               s.id as staff_id, s.staff_id as staff_code, s.first_name, s.last_name, s.email as staff_email, s.user_id as staff_user_id,
                                               c.class_name, sub.subject_name, ses.session_name
                                               FROM lesson_plans lp
                                               INNER JOIN staff s ON lp.teacher_id = s.id
                                               LEFT JOIN classes c ON lp.class_id = c.id
                                               LEFT JOIN subjects sub ON lp.subject_id = sub.id
                                               LEFT JOIN academic_sessions ses ON lp.session_id = ses.id
                                               ORDER BY lp.session_id DESC, lp.lesson_date DESC
                                               LIMIT 20";
                                    $allLpStmt = executeQuery($allLpSql);
                                    $allLessonPlans = fetchAll($allLpStmt);
                                    ?>
                                    <strong>All Lesson Plans in System (to see which teacher they're assigned to):</strong><br>
                                    <?php if (!empty($allLessonPlans)): ?>
                                        <div class="table-responsive mt-2">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>LP ID</th>
                                                        <th>Title</th>
                                                        <th>Teacher ID</th>
                                                        <th>Teacher Name</th>
                                                        <th>Teacher Email</th>
                                                        <th>Linked User</th>
                                                        <th>Class</th>
                                                        <th>Subject</th>
                                                        <th>Session</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($allLessonPlans as $lp): ?>
                                                        <tr <?php echo ($lp['staff_user_id'] == $currentUser['id']) ? 'class="table-success"' : ''; ?>>
                                                            <td><?php echo $lp['id']; ?></td>
                                                            <td><?php echo htmlspecialchars($lp['lesson_title']); ?></td>
                                                            <td><?php echo $lp['teacher_id']; ?></td>
                                                            <td><?php echo htmlspecialchars($lp['first_name'] . ' ' . $lp['last_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($lp['staff_email'] ?? 'N/A'); ?></td>
                                                            <td>
                                                                <?php if ($lp['staff_user_id']): ?>
                                                                    User ID: <?php echo $lp['staff_user_id']; ?>
                                                                    <?php if ($lp['staff_user_id'] == $currentUser['id']): ?>
                                                                        <span class="badge bg-success">You!</span>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <span class="text-danger">Not linked</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($lp['class_name'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($lp['subject_name'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($lp['session_name'] ?? 'ID ' . $lp['session_id']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-danger">No lesson plans found in the system</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (empty($lessonPlans)): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> 
                                    <?php if ($isTeacher && !$isSuperAdmin && !$teacherId): ?>
                                        <div class="alert alert-warning">
                                            <strong><i class="ri-error-warning-line"></i> Teacher Profile Not Linked</strong><br><br>
                                            Your teacher profile is not linked to your account. This means the system cannot identify which teacher you are, so lesson plans assigned to you cannot be displayed.<br><br>
                                            <strong>To fix this, contact your administrator and ask them to:</strong><br>
                                            1. Go to <strong>Settings → User Management</strong><br>
                                            2. Find your user account (Username: <code><?php echo htmlspecialchars($currentUser['username'] ?? 'N/A'); ?></code>)<br>
                                            3. Edit your user account<br>
                                            4. In the "Link to Staff" field, select your staff/teacher profile<br>
                                            5. Save the changes<br><br>
                                            <small class="text-muted">User ID: <?php echo $currentUser['id']; ?>, Email: <?php echo htmlspecialchars($currentUser['email'] ?? 'N/A'); ?></small>
                                        </div>
                                    <?php elseif ($isTeacher && !$isSuperAdmin && $teacherId): ?>
                                        <div class="alert alert-info">
                                            <strong>No lesson plans found</strong><br><br>
                                            <?php if (!empty($classFilter) || !empty($subjectFilter) || !empty($statusFilter)): ?>
                                                No lesson plans match your current filters:<br>
                                                <?php if ($classFilter): ?>
                                                    - Class: <?php 
                                                        $classCheck = fetchOne(executeQuery("SELECT class_name FROM classes WHERE id = ?", 'i', [$classFilter]));
                                                        echo htmlspecialchars($classCheck['class_name'] ?? 'ID ' . $classFilter);
                                                    ?><br>
                                                <?php endif; ?>
                                                <?php if ($subjectFilter): ?>
                                                    - Subject: <?php 
                                                        $subjectCheck = fetchOne(executeQuery("SELECT subject_name FROM subjects WHERE id = ?", 'i', [$subjectFilter]));
                                                        echo htmlspecialchars($subjectCheck['subject_name'] ?? 'ID ' . $subjectFilter);
                                                    ?><br>
                                                <?php endif; ?>
                                                <?php if ($statusFilter): ?>
                                                    - Status: <?php echo htmlspecialchars($statusFilter); ?><br>
                                                <?php endif; ?>
                                                <br>Try removing filters or click "Reset" to see all your lesson plans.
                                            <?php else: ?>
                                                No lesson plans found for the current session (<?php echo htmlspecialchars($currentSession['session_name'] ?? 'N/A'); ?>).<br>
                                                Click "Add Lesson Plan" to create your first lesson plan.
                                            <?php endif; ?>
                                            <br><br>
                                            <small class="text-muted">
                                                Teacher ID: <?php echo $teacherId; ?>, 
                                                Session ID: <?php echo $currentSession['id']; ?>, 
                                                Session: <?php echo htmlspecialchars($currentSession['session_name'] ?? 'N/A'); ?>
                                            </small>
                                        </div>
                                        
                                        <?php
                                        // Diagnostic: Show all lesson plans for this teacher (for debugging)
                                        // Only show if we have a teacherId (either from profile or fallback)
                                        if ($teacherId):
                                            $diagSql = "SELECT lp.id, lp.teacher_id, lp.session_id, lp.class_id, lp.subject_id, lp.status, lp.lesson_title,
                                                       c.class_name, s.subject_name, ses.session_name
                                                       FROM lesson_plans lp
                                                       LEFT JOIN classes c ON lp.class_id = c.id
                                                       LEFT JOIN subjects s ON lp.subject_id = s.id
                                                       LEFT JOIN academic_sessions ses ON lp.session_id = ses.id
                                                       WHERE lp.teacher_id = ?
                                                       ORDER BY lp.session_id DESC, lp.lesson_date DESC
                                                       LIMIT 10";
                                            $diagStmt = executeQuery($diagSql, 'i', [$teacherId]);
                                            $diagPlans = fetchAll($diagStmt);
                                            
                                            if (!empty($diagPlans)):
                                        ?>
                                        <div class="alert alert-secondary mt-3">
                                            <strong>Diagnostic Information:</strong><br>
                                            Found <?php echo count($diagPlans); ?> lesson plan(s) for your teacher ID (<?php echo $teacherId; ?>):<br>
                                            <ul class="mb-0 mt-2">
                                                <?php foreach ($diagPlans as $diagPlan): ?>
                                                    <li>
                                                        ID: <?php echo $diagPlan['id']; ?> - 
                                                        "<?php echo htmlspecialchars($diagPlan['lesson_title']); ?>" - 
                                                        Session: <?php echo htmlspecialchars($diagPlan['session_name'] ?? 'ID ' . $diagPlan['session_id']); ?> - 
                                                        Class: <?php echo htmlspecialchars($diagPlan['class_name'] ?? 'ID ' . $diagPlan['class_id']); ?> - 
                                                        Subject: <?php echo htmlspecialchars($diagPlan['subject_name'] ?? 'ID ' . $diagPlan['subject_id']); ?> - 
                                                        Status: <?php echo htmlspecialchars($diagPlan['status']); ?>
                                                        <?php if ($diagPlan['session_id'] != $currentSession['id']): ?>
                                                            <span class="badge bg-warning">Different Session</span>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <small class="text-muted">If lesson plans are shown above but not in the list, check if they match your current filters or session.</small>
                                        </div>
                                        <?php 
                                            else:
                                                // No lesson plans found at all for this teacher ID
                                        ?>
                                        <div class="alert alert-warning mt-3">
                                            <strong>No Lesson Plans Found</strong><br>
                                            No lesson plans found in the database for Teacher ID: <?php echo $teacherId; ?>.<br>
                                            <small class="text-muted">If you believe lesson plans should be assigned to you, please verify that the lesson plan's teacher_id matches your staff ID.</small>
                                        </div>
                                        <?php 
                                            endif; // !empty($diagPlans)
                                        else:
                                            // If no teacherId found, try to find lesson plans by email directly
                                            if (!empty($currentUser['email'])):
                                                $emailDiagSql = "SELECT lp.id, lp.teacher_id, lp.session_id, lp.class_id, lp.subject_id, lp.status, lp.lesson_title,
                                                               c.class_name, s.subject_name, ses.session_name, st.email as staff_email, st.first_name, st.last_name
                                                               FROM lesson_plans lp
                                                               LEFT JOIN classes c ON lp.class_id = c.id
                                                               LEFT JOIN subjects s ON lp.subject_id = s.id
                                                               LEFT JOIN academic_sessions ses ON lp.session_id = ses.id
                                                               INNER JOIN staff st ON lp.teacher_id = st.id
                                                               WHERE st.email = ?
                                                               ORDER BY lp.session_id DESC, lp.lesson_date DESC
                                                               LIMIT 10";
                                                $emailDiagStmt = executeQuery($emailDiagSql, 's', [$currentUser['email']]);
                                                $emailDiagPlans = fetchAll($emailDiagStmt);
                                                
                                                if (!empty($emailDiagPlans)):
                                        ?>
                                        <div class="alert alert-info mt-3">
                                            <strong>Found Lesson Plans by Email Match:</strong><br>
                                            Found <?php echo count($emailDiagPlans); ?> lesson plan(s) for staff with email "<?php echo htmlspecialchars($currentUser['email']); ?>":<br>
                                            <ul class="mb-0 mt-2">
                                                <?php foreach ($emailDiagPlans as $emailDiagPlan): ?>
                                                    <li>
                                                        ID: <?php echo $emailDiagPlan['id']; ?> - 
                                                        "<?php echo htmlspecialchars($emailDiagPlan['lesson_title']); ?>" - 
                                                        Teacher ID: <?php echo $emailDiagPlan['teacher_id']; ?> (<?php echo htmlspecialchars($emailDiagPlan['first_name'] . ' ' . $emailDiagPlan['last_name']); ?>) - 
                                                        Session: <?php echo htmlspecialchars($emailDiagPlan['session_name'] ?? 'ID ' . $emailDiagPlan['session_id']); ?> - 
                                                        Class: <?php echo htmlspecialchars($emailDiagPlan['class_name'] ?? 'ID ' . $emailDiagPlan['class_id']); ?> - 
                                                        Subject: <?php echo htmlspecialchars($emailDiagPlan['subject_name'] ?? 'ID ' . $emailDiagPlan['subject_id']); ?> - 
                                                        Status: <?php echo htmlspecialchars($emailDiagPlan['status']); ?>
                                                        <?php if ($emailDiagPlan['session_id'] != $currentSession['id']): ?>
                                                            <span class="badge bg-warning">Different Session</span>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <small class="text-muted">These lesson plans exist but your teacher profile is not linked. Please contact administrator to link Staff ID <?php echo $emailDiagPlans[0]['teacher_id']; ?> to your user account.</small>
                                        </div>
                                        <?php 
                                                endif; // !empty($emailDiagPlans)
                                            endif; // !empty($currentUser['email'])
                                        endif; // $teacherId
                                        ?>
                                    <?php else: ?>
                                        No lesson plans found.
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered dt-responsive nowrap" id="lesson-plans-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <?php if (!$isTeacher || $isSuperAdmin): ?>
                                                <th>Teacher</th>
                                                <?php endif; ?>
                                                <th>Class</th>
                                                <th>Subject</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                                <?php if ($isSuperAdmin): ?>
                                                <th>Branch</th>
                                                <?php endif; ?>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lessonPlans as $plan): ?>
                                                <tr>
                                                    <td><?php echo formatDate($plan['lesson_date']); ?></td>
                                                    <?php if (!$isTeacher || $isSuperAdmin): ?>
                                                    <td>
                                                        <?php if ($plan['teacher_first_name']): ?>
                                                            <?php echo htmlspecialchars($plan['teacher_first_name'] . ' ' . $plan['teacher_last_name']); ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($plan['staff_id'] ?? ''); ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php endif; ?>
                                                    <td><?php echo htmlspecialchars($plan['class_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($plan['subject_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($plan['lesson_title']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $plan['status'] == 'Published' ? 'success' : 'warning'; ?>">
                                                            <?php echo htmlspecialchars($plan['status']); ?>
                                                        </span>
                                                    </td>
                                                    <?php if ($isSuperAdmin): ?>
                                                    <td><?php echo htmlspecialchars($plan['branch_name'] ?? 'N/A'); ?></td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <a href="<?php echo APP_URL; ?>modules/teacher/view-lesson-plan.php?id=<?php echo $plan['id']; ?>" 
                                                           class="btn btn-sm btn-info" title="View Details">
                                                            <i class="ri-eye-line"></i> View
                                                        </a>
                                                        <?php if ($isTeacher && !$isSuperAdmin && !$isAdmin && $teacherId && $plan['teacher_id'] == $teacherId): ?>
                                                        <button type="button" class="btn btn-sm btn-primary" onclick="editLessonPlan(<?php echo $plan['id']; ?>)" title="Edit">
                                                            <i class="ri-edit-line"></i> Edit
                                                        </button>
                                                        <?php elseif ($isSuperAdmin || $isAdmin): ?>
                                                        <button type="button" class="btn btn-sm btn-primary" onclick="editLessonPlan(<?php echo $plan['id']; ?>)" title="Edit">
                                                            <i class="ri-edit-line"></i> Edit
                                                        </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php if (($isTeacher && !$isSuperAdmin && !$isAdmin && $teacherId) || $isSuperAdmin || $isAdmin): ?>
<!-- Add Lesson Plan Modal -->
<div class="modal fade" id="addLessonPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lessonPlanModalTitle">Add Lesson Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="add-lesson-plan-form" method="POST" action="javascript:void(0);">
                <input type="hidden" name="id" id="lesson_plan_id">
                <input type="hidden" name="session_id" value="<?php echo $currentSession['id']; ?>">
                <div class="modal-body">
                    <div class="row">
                        <?php if ($isSuperAdmin || $isAdmin): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teacher <span class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select Teacher</option>
                                <?php foreach ($allTeachers as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="teacher_id" value="<?php echo $teacherId; ?>">
                        <?php endif; ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lesson Date <span class="text-danger">*</span></label>
                            <input type="date" name="lesson_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lesson Title <span class="text-danger">*</span></label>
                            <input type="text" name="lesson_title" class="form-control" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Objectives</label>
                            <textarea name="objectives" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="5"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Methodology</label>
                            <textarea name="methodology" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Resources</label>
                            <textarea name="resources" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Assessment</label>
                            <textarea name="assessment" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Draft">Draft</option>
                                <option value="Published">Published</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="save-lesson-plan-btn" class="btn btn-primary">Save Lesson Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

    <?php 
    // Prepare additional JavaScript
    ob_start();
    ?>
<script>
$(document).ready(function() {
    // Wait for DataTables to be available - use a retry mechanism
    var dataTableRetries = 0;
    var maxRetries = 50; // 5 seconds max wait time
    
    function initDataTable() {
        // Check if table exists
        var $table = $('#lesson-plans-table');
        if ($table.length === 0) {
            // Table doesn't exist (no lesson plans to display) - this is normal
            return;
        }
        
        // Check if DataTables is available
        if (typeof $.fn.DataTable !== 'undefined') {
            try {
                // Check if already initialized
                if ($.fn.DataTable.isDataTable('#lesson-plans-table')) {
                    console.log('DataTable: Already initialized');
                    return;
                }
                
                $table.DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']],
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                });
                console.log('DataTables initialized successfully');
            } catch (e) {
                console.error('Error initializing DataTables:', e);
            }
        } else {
            dataTableRetries++;
            if (dataTableRetries < maxRetries) {
                // Retry after a short delay if DataTables is not yet loaded
                setTimeout(initDataTable, 100);
            } else {
                console.warn('DataTables not loaded after ' + maxRetries + ' retries. Table will display without DataTables features.');
            }
        }
    }
    
    // Start initialization after a short delay to ensure scripts are loaded
    setTimeout(initDataTable, 200);
    
    // Function to handle form submission
    function handleLessonPlanSubmit(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Form submit intercepted');
        
        var form = $('#add-lesson-plan-form');
        
        // Validate required fields
        var teacherId = form.find('[name="teacher_id"]').val() || form.find('input[type="hidden"][name="teacher_id"]').val();
        var classId = form.find('[name="class_id"]').val();
        var subjectId = form.find('[name="subject_id"]').val();
        var lessonDate = form.find('[name="lesson_date"]').val();
        var lessonTitle = form.find('[name="lesson_title"]').val();
        
        if (!classId || !subjectId || !lessonDate || !lessonTitle) {
            alert('Please fill all required fields');
            return false;
        }
        
        if (!teacherId || teacherId === '' || teacherId === null) {
            alert('Please select a teacher');
            return false;
        }
        
        var formData = form.serialize();
        console.log('Form data:', formData);
        
        var submitBtn = $('#save-lesson-plan-btn');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin"></i> Saving...');
        
        $.ajax({
            url: '<?php echo APP_URL; ?>ajax/teacher/save-lesson-plan.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                console.log('Response:', response);
                
                if (response && response.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Lesson plan saved successfully');
                    } else if (typeof showToast !== 'undefined') {
                        showToast(response.message || 'Lesson plan saved successfully', 'success');
                    } else {
                        alert(response.message || 'Lesson plan saved successfully');
                    }
                    $('#addLessonPlanModal').modal('hide');
                    form[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    var errorMsg = (response && response.message) ? response.message : 'Failed to save lesson plan';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                    } else if (typeof showToast !== 'undefined') {
                        showToast(errorMsg, 'error');
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                var errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch(e) {
                        errorMsg = 'Server error: ' + xhr.status + ' - ' + error;
                    }
                }
                console.error('AJAX Error:', xhr, status, error, xhr.responseText);
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else if (typeof showToast !== 'undefined') {
                    showToast(errorMsg, 'error');
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        });
        
        return false;
    }
    
    // Attach handler using event delegation (works even if form is added dynamically)
    $(document).off('submit', '#add-lesson-plan-form').on('submit', '#add-lesson-plan-form', handleLessonPlanSubmit);
    
    // Also attach when modal is shown (backup)
    $('#addLessonPlanModal').on('shown.bs.modal', function() {
        $('#add-lesson-plan-form').off('submit').on('submit', handleLessonPlanSubmit);
    });
    
    // Handle button click directly (more reliable)
    $(document).off('click', '#save-lesson-plan-btn').on('click', '#save-lesson-plan-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Save button clicked');
        
        var form = $('#add-lesson-plan-form');
        
        // Validate required fields manually
        var teacherId = form.find('[name="teacher_id"]').val() || form.find('input[type="hidden"][name="teacher_id"]').val();
        var classId = form.find('[name="class_id"]').val();
        var subjectId = form.find('[name="subject_id"]').val();
        var lessonDate = form.find('[name="lesson_date"]').val();
        var lessonTitle = form.find('[name="lesson_title"]').val();
        
        if (!classId || !subjectId || !lessonDate || !lessonTitle) {
            alert('Please fill all required fields');
            return false;
        }
        
        if (!teacherId || teacherId === '' || teacherId === null) {
            alert('Please select a teacher');
            return false;
        }
        
        // Trigger the form submit handler
        var submitEvent = $.Event('submit');
        form.trigger(submitEvent);
        
        return false;
    });
    
    // Reset form when modal is hidden
    $('#addLessonPlanModal').on('hidden.bs.modal', function() {
        $('#add-lesson-plan-form')[0].reset();
        $('#lesson_plan_id').val('');
        $('#lessonPlanModalTitle').text('Add Lesson Plan');
    });
});

// Function to edit lesson plan (must be in global scope for onclick)
window.editLessonPlan = function(id) {
    console.log('editLessonPlan called with id:', id);
    
    if (!id) {
        alert('Invalid lesson plan ID');
        return;
    }
    
    $.ajax({
        url: '<?php echo APP_URL; ?>ajax/teacher/get-lesson-plan.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            console.log('Response received:', response);
            
            if (response.success && response.data) {
                var plan = response.data;
                console.log('Plan data:', plan);
                
                // Populate form fields
                var form = $('#add-lesson-plan-form');
                form.find('[name="id"]').val(plan.id);
                $('#lessonPlanModalTitle').text('Edit Lesson Plan');
                form.find('[name="class_id"]').val(plan.class_id);
                form.find('[name="subject_id"]').val(plan.subject_id);
                form.find('[name="lesson_date"]').val(plan.lesson_date);
                form.find('[name="lesson_title"]').val(plan.lesson_title);
                form.find('[name="objectives"]').val(plan.objectives || '');
                form.find('[name="content"]').val(plan.content || '');
                form.find('[name="methodology"]').val(plan.methodology || '');
                form.find('[name="resources"]').val(plan.resources || '');
                form.find('[name="assessment"]').val(plan.assessment || '');
                form.find('[name="status"]').val(plan.status || 'Draft');
                
                // Show modal using Bootstrap 5
                var modalElement = document.getElementById('addLessonPlanModal');
                var modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                var errorMsg = response.message || 'Failed to load lesson plan';
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else if (typeof showToast !== 'undefined') {
                    showToast(errorMsg, 'error');
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr, status, error, xhr.responseText);
            var errorMsg = 'An error occurred while loading lesson plan.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    errorMsg = 'Server error: ' + xhr.status + ' - ' + error;
                }
            }
            if (typeof toastr !== 'undefined') {
                toastr.error(errorMsg);
            } else if (typeof showToast !== 'undefined') {
                showToast(errorMsg, 'error');
            } else {
                alert('Error: ' + errorMsg);
            }
        }
    });
}
</script>
    <?php
    $additionalJS = ob_get_clean();
    include '../../includes/footer.php'; 
    ?>
</div>

