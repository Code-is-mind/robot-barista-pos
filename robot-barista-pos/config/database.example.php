<?php
/**
 * Database Configuration - EXAMPLE FILE
 * 
 * Copy this file to database.php and update with your credentials
 * 
 * SECURITY: Never commit database.php to version control
 */

class Database {
    private static $instance = null;
    private $conn;
    
    // UPDATE THESE VALUES WITH YOUR DATABASE CREDENTIALS
    private $host = "localhost";                    // Your database host
    private $db_name = "robot_barista_pos";        // Your database name
    private $username = "your_db_username";        // Your database username
    private $password = "your_db_password";        // Your database password
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
}