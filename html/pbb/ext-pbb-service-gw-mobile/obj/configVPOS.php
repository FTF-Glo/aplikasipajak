<?php
Class ConfigVPOS{
	
    static public function getConfig($conn){
        try {
            $query = "
                    SELECT 
                            CTR_AC_VALUE
                    FROM central_app_config
                    WHERE 
                            CTR_AC_AID = :app
                            AND CTR_AC_KEY = :key
            ";
            // echo $query;exit;
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':app', 'aPBB', PDO::PARAM_STR);
            $stmt->bindValue(':key', 'IP_FILTER', PDO::PARAM_STR);
            $stmt->execute();
            $row  = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $row;
    }
}
?>
