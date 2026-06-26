<?php
/**
 * Check Monthly Fees Tables Installation
 * 
 * Run this file to check if monthly fee tables are installed
 * Access via: http://localhost/bti/database/check-monthly-fees-tables.php
 */

require_once '../config/config.php';

// Simple check without full authentication for installation purposes
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Monthly Fees Tables Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h1>Monthly Fees Tables Installation Check</h1>
    
    <?php
    try {
        $conn = getDBConnection();
        
        $requiredTables = [
            'monthly_fee_assignments',
            'student_fee_ledger',
            'student_advance_credits',
            'payment_allocations',
            'student_fee_balance'
        ];
        
        echo '<h2>Checking Required Tables:</h2>';
        echo '<table>';
        echo '<tr><th>Table Name</th><th>Status</th></tr>';
        
        $allTablesExist = true;
        foreach ($requiredTables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            $exists = $result->num_rows > 0;
            
            if ($exists) {
                echo "<tr><td>$table</td><td class='success'>✓ Exists</td></tr>";
            } else {
                echo "<tr><td>$table</td><td class='error'>✗ Missing</td></tr>";
                $allTablesExist = false;
            }
        }
        
        echo '</table>';
        
        if ($allTablesExist) {
            echo '<div class="info">';
            echo '<h3 class="success">✓ All tables are installed!</h3>';
            echo '<p>Your monthly fees system is ready to use.</p>';
            echo '</div>';
        } else {
            echo '<div class="info">';
            echo '<h3 class="error">✗ Some tables are missing!</h3>';
            echo '<p><strong>To install the tables, run the following SQL file:</strong></p>';
            echo '<p><code>database/monthly_tuition_fees_schema.sql</code></p>';
            echo '<p><strong>Or execute this command in phpMyAdmin or MySQL:</strong></p>';
            echo '<pre>source database/monthly_tuition_fees_schema.sql</pre>';
            echo '</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="error">';
        echo '<h3>Error:</h3>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
    ?>
    
    <hr>
    <p><a href="../modules/fees/monthly-assignment.php">← Back to Monthly Fee Assignment</a></p>
</body>
</html>

