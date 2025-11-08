<?php
/**
 * Database Connection Handler
 * Uses PDO for secure database operations
 */

require_once __DIR__ . '/config.php';

/**
 * Get PDO Database Connection
 * @return PDO
 */
function getPDO() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error in production, display in development
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please contact the administrator.");
        }
    }
    
    return $pdo;
}

/**
 * Execute a query and return results
 * @param string $sql
 * @param array $params
 * @return array
 */
function query($sql, $params = []) {
    $pdo = getPDO();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a query and return single row
 * @param string $sql
 * @param array $params
 * @return array|false
 */
function queryOne($sql, $params = []) {
    $pdo = getPDO();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Execute an insert/update/delete query
 * @param string $sql
 * @param array $params
 * @return bool
 */
function execute($sql, $params = []) {
    $pdo = getPDO();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Get last insert ID
 * @return string
 */
function lastInsertId() {
    return getPDO()->lastInsertId();
}
?>