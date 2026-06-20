<?php
$db_file = __DIR__ . '/solar_shop.sqlite';
$dsn = "sqlite:$db_file";

try {
     $pdo = new PDO($dsn);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
     $pdo->exec("PRAGMA foreign_keys = ON;");
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
