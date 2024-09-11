<?php
function mysqli_field_name($result, $field_offset)
{
    $properties = mysqli_fetch_field_direct($result, $field_offset);
    return is_object($properties) ? $properties->name : null;
}
    function queryOpen($sql, $DBLink = ''){
        $result = mysqli_query($DBLink,$sql);
        $array = array();
        while($row = mysqli_fetch_array($result)){
            //$field_array = array();
            for($i=0; $i < mysqli_num_fields($result); $i++) {
                //array_push($field_array, array(mysqli_field_name($result, $i) => $row[$i]));
                $field_array[mysqli_field_name($result, $i)] = $row[$i]; 
            }
            array_push($array, (object)$field_array);
        }
        return ($array);
    }
    
?>
