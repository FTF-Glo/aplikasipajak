<?php
    function queryOpen($DBLink, $sql){
        $result = mysqli_query($DBLink, $sql);
        $array = array();
        while($row = mysqli_fetch_array($result)){
            //$field_array = array();
            for($i=0; $i < mysqli_num_fields($result); $i++) {
                //array_push($field_array, array(mysql_field_name($result, $i) => $row[$i]));
                $field_array[mysqli_field_name($result, $i)] = $row[$i]; 
            }
            array_push($array, (object)$field_array);
        }
        return ($array);
    }
    
?>
