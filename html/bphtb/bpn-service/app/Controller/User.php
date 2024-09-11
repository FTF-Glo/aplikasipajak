<?php
namespace Controller;

Class User{
    public function __construct(){
        $this->json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
    }
    	
    static public function authUser($conn,$json){
		$status = false;
        $user = $json->Username;
        $pwd = $json->Password;
        
        try {
			$query = "
                    SELECT COUNT(*) AS COUNT
                    FROM USER_WEBSERVICE
                    WHERE uid = '{$user}' and password = '{$pwd}'";
            $stmt = $conn->prepare($query);            
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $status = ($data->COUNT > 0) ? true : false;
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $status;
    }	
}
?>
