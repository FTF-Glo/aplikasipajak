<?php
Class User{
    static public function getDataUser($conn){
        $pReq = '';
        $uid = '';

        $json = file_get_contents('php://input');
        $pReq = json_decode($json);
        $uid = $pReq->uid;
        
        try {
            $stmt = $conn->prepare("
                    SELECT
                            uid AS UID,
                            password AS PASS,
                            ip AS IP

                    FROM
                            PBB_USER_WEBSERVICE
                    WHERE
                            uid = :uid
            ");

            $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
            $stmt->execute();
            $data = array();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){
                $data = array('uid'=>$row['UID'], 'pass'=>$row['PASS'], 'ip'=>$row['IP']);
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
