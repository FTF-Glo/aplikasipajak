<?php
class db
{
    protected $connSw;
    protected $connGw;
    protected $connPg;
    private $json;
    
    public function __construct(){
        $this->json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
    }

    private function connectToSWMysql($file){
        $conf = $this->json->decode(file_get_contents($file));
        $conn = null;
        try {
            $conn = new PDO("mysql:host={$conf->host};dbname={$conf->dbname}", $conf->user, $conf->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";die();
        }
        $this->connSw = $conn;
    }

    private function connectToGWMysql($file){
        $conf = $this->json->decode(file_get_contents($file));
        $conn = null;
        try {
            $conn = new PDO("mysql:host={$conf->host};dbname={$conf->dbname}", $conf->user, $conf->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";die();
        }
        $this->connGw = $conn;
    }

    private function connectToPGsql($file){
        $conf = $this->json->decode(file_get_contents($file));
        $conn = null;
        try {
            $conn = new PDO("pgsql:host={$conf->host};dbname={$conf->dbname}", $conf->user, $conf->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";die();
        }
        $this->connPg = $conn;
    }

    protected function connectSW(){
        $this->connectToSWMysql("config/sw-pbb-conf.json");
    }

    protected function connectGW(){
        $this->connectToGWMysql("config/gw-pbb-conf.json");
    }

    protected function connectPG(){
        $this->connectToPGsql("config/postgres-conf.json");
    }

    public function __destruct(){
        $this->connSw = null;
        $this->connGw = null;
        $this->connPg = null;
    }
}
