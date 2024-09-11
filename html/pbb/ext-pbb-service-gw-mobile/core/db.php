<?php
class db
{
    protected $conn;
    private $json;
    public function __construct(){
        $this->json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
    }

    private function connectToMysql($file){
        $conf = $this->json->decode(file_get_contents($file));
        $conn = null;
        try {
            $conn = new PDO("mysql:host={$conf->host};dbname={$conf->dbname}", $conf->user, $conf->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";die();
        }
        $this->conn = $conn;
    }

    protected function connect(){
        $this->connectToMysql("config/pbb-conf.json");
    }

    public function __destruct(){
        $this->conn = null;
    }
}
