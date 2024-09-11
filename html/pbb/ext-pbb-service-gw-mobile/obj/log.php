<?php
Class LogAcc{
	
    static public function addToLog($conn){
        $json 	= new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
        $uid    = '';
        $ip	= $_SERVER['REMOTE_ADDR'];
        $req 	= json_encode($_POST);

        $json = file_get_contents('php://input');
        $pReq = json_decode($json);
        $uid = $pReq->uid;

        try {
            $query = "
                    INSERT INTO PBB_ACCESS_LOG_WEBSERVICE 
                    (user,date,request,ip) VALUES (:uid,now(),:req,:ip);
            ";

            $stmt = $conn->prepare($query);

            $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
            $stmt->bindValue(':req', $req, PDO::PARAM_STR);
            $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
            $res = $stmt->execute();
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $res;
    }
}
?>
