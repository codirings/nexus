<?php
require_once 'config.php';

try {
    // Testing the connection using the $pdo instance from config.php
    if ($pdo) {
        // Query to fetch the database name
        $stmt = $pdo->query("SELECT DATABASE()");
        $db_name = $stmt->fetchColumn();
        
        echo "<div style='font-family: Arial, sans-serif; padding: 20px; color: #155724; background-color: #d4edda; border-color: #c3e6cb;'>";
        echo "<strong>Success!</strong> Your connection to phpMyAdmin is active.";
        echo "<br>Connected to database: <strong>" . $db_name . "</strong>";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; color: #721c24; background-color: #f8d7da; border-color: #f5c6cb;'>";
    echo "<strong>Connection Failed:</strong> " . $e->getMessage();
    echo "</div>";
}
?>