<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new \PDO(
        sprintf(
            "dblib:host=%s;dbname=%s",
            '103.76.172.165',
            'IPROTAX'
        ),
        'iprotax',
        'iprotax'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "There was a problem connecting. " . $e->getMessage();
}