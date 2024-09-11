<?php
namespace Core;

use \PDO;

class Database{
    protected function connectDB($host, $db, $user, $pass){
        $conn = null;
        
        try {
            $conn = new PDO("mysql:host={$host};dbname={$db}", $user, $pass);
            //$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";die();
        }
        
        return $conn;
    }
}
