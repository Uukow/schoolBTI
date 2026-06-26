<?php

ob_start();

require_once '../../config/config.php';

error_reporting(E_ALL);

ini_set('display_errors', 0);

ob_clean();

header('Content-Type: application/json; charset=utf-8');



try {

    if (!isLoggedIn()) {

        jsonResponse(false, 'Unauthorized');

    }



    $currentUser = getCurrentUser();

    $canManage = hasRole(['Super Admin', 'Admin'])

        || (function_exists('canPerform') && canPerform('hr_grievances', 'manage'));

    $canViewAnonymous = $canManage

        || (function_exists('canPerform') && canPerform('hr_grievances', 'view_anonymous'));



    $status = sanitize($_GET['status'] ?? '');

    $category = sanitize($_GET['category'] ?? '');

    $priority = sanitize($_GET['priority'] ?? '');

    $search = sanitize($_GET['q'] ?? '');

    $year = (int)($_GET['year'] ?? 0);

    $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;

    $mineOnly = isset($_GET['mine']) && $_GET['mine'] === '1';



    $staffRow = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$currentUser['id']]));

    $myStaffId = (int)($staffRow['id'] ?? 0);



    $sql = "SELECT g.*, b.branch_name,

            s.first_name, s.last_name, s.staff_id AS employee_code,

            u.username AS assigned_username,

            CONCAT(sa.first_name, ' ', sa.last_name) AS assigned_name,

            (SELECT COUNT(*) FROM hr_grievance_actions a WHERE a.grievance_id = g.id) AS action_count

            FROM hr_grievances g

            LEFT JOIN branches b ON g.branch_id = b.id

            LEFT JOIN staff s ON g.staff_id = s.id

            LEFT JOIN users u ON g.assigned_to = u.id

            LEFT JOIN staff sa ON sa.user_id = u.id

            WHERE 1=1";

    $params = [];

    $types = '';



    if (!$canManage || $mineOnly) {

        if ($myStaffId > 0) {

            $sql .= " AND g.staff_id = ?";

            $params[] = $myStaffId;

            $types .= 'i';

        } else {

            $sql .= " AND 1=0";

        }

    }



    if ($status) {

        $sql .= " AND g.status = ?";

        $params[] = $status;

        $types .= 's';

    }

    if ($category) {

        $sql .= " AND g.category = ?";

        $params[] = $category;

        $types .= 's';

    }

    if ($priority) {

        $sql .= " AND g.priority = ?";

        $params[] = $priority;

        $types .= 's';

    }

    if ($branchId) {

        $sql .= " AND g.branch_id = ?";

        $params[] = $branchId;

        $types .= 'i';

    }

    if ($year > 0) {

        $sql .= " AND YEAR(g.created_at) = ?";

        $params[] = $year;

        $types .= 'i';

    }

    if ($search) {

        $sql .= " AND (g.subject LIKE ? OR g.grievance_no LIKE ? OR g.description LIKE ?)";

        $like = '%' . $search . '%';

        $params = array_merge($params, [$like, $like, $like]);

        $types .= 'sss';

    }



    $sql .= " ORDER BY FIELD(g.priority,'Critical','High','Medium','Low'), g.created_at DESC";

    $grievances = fetchAll(executeQuery($sql, $types, $params));



    if (!$canViewAnonymous) {

        foreach ($grievances as &$g) {

            if (!empty($g['is_anonymous'])) {

                $g['first_name'] = null;

                $g['last_name'] = null;

                $g['employee_code'] = null;

            }

        }

        unset($g);

    }



    $stats = fetchOne(executeQuery(

        "SELECT

            COUNT(*) AS total,

            SUM(CASE WHEN status IN ('Submitted','Under_Review','Investigating','Escalated') THEN 1 ELSE 0 END) AS open_cases,

            SUM(CASE WHEN status = 'Under_Review' THEN 1 ELSE 0 END) AS under_review,

            SUM(CASE WHEN status = 'Investigating' THEN 1 ELSE 0 END) AS investigating,

            SUM(CASE WHEN priority = 'Critical' AND status NOT IN ('Resolved','Closed') THEN 1 ELSE 0 END) AS critical_open,

            SUM(CASE WHEN status IN ('Resolved','Closed') AND MONTH(resolved_at) = MONTH(CURDATE()) AND YEAR(resolved_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS resolved_month,

            SUM(CASE WHEN is_anonymous = 1 THEN 1 ELSE 0 END) AS anonymous_count

         FROM hr_grievances"

    ));



    jsonResponse(true, 'OK', [

        'grievances' => $grievances,

        'stats' => $stats,

        'can_manage' => $canManage,

        'my_staff_id' => $myStaffId,

    ]);

} catch (Throwable $e) {

    error_log('get-grievances.php: ' . $e->getMessage());

    jsonResponse(false, 'Server error: ' . $e->getMessage());

}

