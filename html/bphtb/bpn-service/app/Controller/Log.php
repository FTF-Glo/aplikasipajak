<?php
namespace Controller;

Class Log{
	
    static public function addToLog($pConnGw,$pUser,$pRequest){
        $ip	= $_SERVER['REMOTE_ADDR'];
        try {
            $query = "
                    INSERT INTO SSB_ACCESS_LOG_WEBSERVICE 
                    (user,date,request,ip) VALUES (:uid,now(),:req,:ip);
            ";

            $stmt = $pConnGw->prepare($query);

            $stmt->bindValue(':uid', $pUser, PDO::PARAM_STR);
            $stmt->bindValue(':req', $pRequest, PDO::PARAM_STR);
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
