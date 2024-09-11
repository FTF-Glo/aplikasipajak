<?php
Class User{
    static public function getDataUser($conn){
        $pReq = '';
        $user = '';

        $json = file_get_contents('php://input');
        $pReq = json_decode($json);
        $user = $pReq->user;
        
        try {
            $stmt = $conn->prepare("
                    SELECT
                            uid AS UID,
                            password AS PASS,
                            ip AS IP

                    FROM
                            PATDA_USER_WEBSERVICE
                    WHERE
                            uid = :uid
            ");

            $stmt->bindValue(':uid', $user, PDO::PARAM_STR);
            $stmt->execute();
            $data = array();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){
                $data = array('user'=>$row['UID'], 'pass'=>$row['PASS'], 'ip'=>$row['IP']);
            }
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $data;
    }	
}
?>
