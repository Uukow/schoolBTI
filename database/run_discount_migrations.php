<?php
/**
 * Run Student Discount Management Migrations
 * 
 * This script adds discount fields to students and monthly_fee_assignments tables
 */

require_once '../config/config.php';

echo "Running Student Discount Management Migrations...\n\n";

// Migration 1: Add discount fields to students table
echo "1. Adding discount fields to students table...\n";
$sql1 = "ALTER TABLE `students` 
ADD COLUMN IF NOT EXISTS `discount_type` enum('Fixed','Percentage') DEFAULT NULL AFTER `special_needs`,
ADD COLUMN IF NOT EXISTS `discount_value` decimal(10,2) DEFAULT NULL AFTER `discount_type`";

try {
    $result = $conn->query($sql1);
    if ($result) {
        echo "   ✓ Successfully added discount fields to students table\n";
    } else {
        // Try alternative syntax (MySQL 8.0+ doesn't support IF NOT EXISTS in ALTER TABLE)
        $sql1a = "ALTER TABLE `students` 
ADD COLUMN `discount_type` enum('Fixed','Percentage') DEFAULT NULL AFTER `special_needs`";
        $sql1b = "ALTER TABLE `students` 
ADD COLUMN `discount_value` decimal(10,2) DEFAULT NULL AFTER `discount_type`";
        
        // Check if columns exist first
        $check1 = $conn->query("SHOW COLUMNS FROM `students` LIKE 'discount_type'");
        if ($check1->num_rows == 0) {
            $conn->query($sql1a);
            echo "   ✓ Added discount_type column\n";
        } else {
            echo "   - discount_type column already exists\n";
        }
        
        $check2 = $conn->query("SHOW COLUMNS FROM `students` LIKE 'discount_value'");
        if ($check2->num_rows == 0) {
            $conn->query($sql1b);
            echo "   ✓ Added discount_value column\n";
        } else {
            echo "   - discount_value column already exists\n";
        }
    }
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "   - Columns already exist, skipping\n";
    } else {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
    }
}

// Add index
echo "\n2. Adding index for discount fields...\n";
try {
    $indexSql = "ALTER TABLE `students` ADD INDEX IF NOT EXISTS `idx_discount` (`discount_type`, `discount_value`)";
    $conn->query($indexSql);
    echo "   ✓ Index added (or already exists)\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate key') !== false) {
        echo "   - Index already exists, skipping\n";
    } else {
        // Try without IF NOT EXISTS
        try {
            $indexSql2 = "ALTER TABLE `students` ADD INDEX `idx_discount` (`discount_type`, `discount_value`)";
            $conn->query($indexSql2);
            echo "   ✓ Index added\n";
        } catch (Exception $e2) {
            echo "   - Index may already exist\n";
        }
    }
}

// Migration 2: Add discount fields to monthly_fee_assignments table
echo "\n3. Adding discount fields to monthly_fee_assignments table...\n";
try {
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'monthly_fee_assignments'");
    if ($tableCheck->num_rows == 0) {
        echo "   - monthly_fee_assignments table does not exist. Please run database/monthly_tuition_fees_schema.sql first.\n";
    } else {
        // Check if columns exist
        $checkOriginal = $conn->query("SHOW COLUMNS FROM `monthly_fee_assignments` LIKE 'original_amount'");
        if ($checkOriginal->num_rows == 0) {
            $sql2a = "ALTER TABLE `monthly_fee_assignments` 
ADD COLUMN `original_amount` decimal(10,2) DEFAULT NULL COMMENT 'Original fee amount before discount' AFTER `amount`";
            $conn->query($sql2a);
            echo "   ✓ Added original_amount column\n";
        } else {
            echo "   - original_amount column already exists\n";
        }
        
        $checkDiscount = $conn->query("SHOW COLUMNS FROM `monthly_fee_assignments` LIKE 'discount_amount'");
        if ($checkDiscount->num_rows == 0) {
            $sql2b = "ALTER TABLE `monthly_fee_assignments` 
ADD COLUMN `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Discount amount applied' AFTER `original_amount`";
            $conn->query($sql2b);
            echo "   ✓ Added discount_amount column\n";
        } else {
            echo "   - discount_amount column already exists\n";
        }
        
        $checkType = $conn->query("SHOW COLUMNS FROM `monthly_fee_assignments` LIKE 'discount_type'");
        if ($checkType->num_rows == 0) {
            $sql2c = "ALTER TABLE `monthly_fee_assignments` 
ADD COLUMN `discount_type` enum('Fixed','Percentage') DEFAULT NULL COMMENT 'Type of discount applied' AFTER `discount_amount`";
            $conn->query($sql2c);
            echo "   ✓ Added discount_type column\n";
        } else {
            echo "   - discount_type column already exists\n";
        }
        
        // Update existing records
        echo "\n4. Updating existing records...\n";
        $updateSql = "UPDATE `monthly_fee_assignments` 
SET `original_amount` = `assigned_amount` 
WHERE `original_amount` IS NULL";
        $conn->query($updateSql);
        echo "   ✓ Updated existing records\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n✓ Migration completed!\n";
echo "\nNext steps:\n";
echo "1. Register or edit students to set discount information\n";
echo "2. When assigning monthly fees, discounts will be automatically applied\n";
echo "3. Discounts are recorded in the system but not displayed to students\n";

