<?php
require_once '../../config/config.php';
if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$sql = "SELECT id, student_id, first_name, last_name FROM students WHERE status = 'Active' ORDER BY first_name";
$students = fetchAll(executeQuery($sql));

jsonResponse(true, 'Students loaded', $students);

