<?php

#Db cedentialr
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'discount_deals');

function getDBConnection() {
try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Connection error check
        if ($conn->connect_error) {
            die("Database Connection Error: " . $conn->connect_error);
        }
        
        // Character set 
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

function getPDOConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        die("PDO Connection Error: " . $e->getMessage());
    }
}

// Test database connection
function testConnection() {
    $conn = getDBConnection();
    if ($conn) {
        echo "Database Connection Successful!";
        $conn->close();
        return true;
    }
    return false;
}



?>