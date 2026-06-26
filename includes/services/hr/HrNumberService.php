<?php
if (!defined('ABSPATH')) exit('Direct access forbidden.');

class HrNumberService
{
    public static function next($prefix, $table, $column)
    {
        $sql = "SELECT $column FROM $table WHERE $column LIKE ? ORDER BY id DESC LIMIT 1";
        $row = fetchOne(executeQuery($sql, 's', [$prefix . '%']));
        $num = 1;
        if ($row && !empty($row[$column])) {
            $num = (int) preg_replace('/\D/', '', $row[$column]) + 1;
        }
        return generateUniqueId($prefix, $num, 6);
    }
}
