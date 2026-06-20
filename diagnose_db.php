<?php
require_once 'config/db.php';

echo "<h1>Database Diagnostics</h1>";

try {
    echo "<h3>Connection Status:</h3>";
    echo "Connected successfully to: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";

    $tables = [];
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    echo "<h3>Tables Found:</h3>";
    echo "<ul><li>" . implode("</li><li>", $tables) . "</li></ul>";

    if (in_array('customer', $tables)) {
        echo "<h3>'customer' Table Columns:</h3>";
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
            $stmt = $pdo->query("PRAGMA table_info(customer)");
            $cols = $stmt->fetchAll();
            echo "<table border='1'><tr><th>Name</th><th>Type</th></tr>";
            foreach($cols as $c) echo "<tr><td>{$c['name']}</td><td>{$c['type']}</td></tr>";
            echo "</table>";
        } else {
            $stmt = $pdo->query("DESCRIBE customer");
            $cols = $stmt->fetchAll();
            echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
            foreach($cols as $c) echo "<tr><td>{$c['Field']}</td><td>{$c['Type']}</td></tr>";
            echo "</table>";
        }

        $count = $pdo->query("SELECT COUNT(*) FROM customer")->fetchColumn();
        echo "<p>Total Records in customer: $count</p>";
    } else {
        echo "<p style='color:red;'>ERROR: 'customer' table not found!</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>DIAGNOSTIC ERROR: " . $e->getMessage() . "</p>";
}
?>
