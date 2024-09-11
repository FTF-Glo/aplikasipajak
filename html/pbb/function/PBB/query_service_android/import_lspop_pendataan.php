<?php 
    require_once('queryOpen.php');
    $myFile = "$sRootPath$target";
    $fh = fopen($myFile, 'r') or die("can't open file");

    $sql_del_log_extH_ext    = $sql_del_log_ext  = "DELETE FROM cppmod_pbb_sppt_update_ext_log WHERE CPM_SPPT_DOC_ID IN ( ";
    $sql_ins_log_extH_ext    = $sql_ins_log_ext  = "INSERT INTO cppmod_pbb_sppt_update_ext_log VALUES ";
    // $sql_del_tranH   = $sql_del_tran = "DELETE FROM `cppmod_pbb_tranmain` WHERE CPM_TRAN_SPPT_DOC_ID IN ( ";
    // $sql_ins_tranH   = $sql_ins_tran = "INSERT INTO `cppmod_pbb_tranmain` VALUES ";

    $i = 0; $id = '';
    $date = date("Y-m-d H:i:s");
    if ($fh) {
        while (($content = fgets($fh, 4096)) !== false) {
            $content = str_replace("\n","",$content);
            $row = explode('|',$content);
			
			// echo "<pre>";
			// print_r($row);

            if($i > 0) $sql_ins_log_ext  .= ",";
			
			// echo $sql_ins_log_ext;
            
            if($id != mysql_real_escape_string($row[0])){
                $id = mysql_real_escape_string($row[0]);
                
                if($id){
                    if($i > 0){
						//$sql_del_log_ext  .= ","; 
						// $sql_del_tran .= ","; 
						// $sql_ins_tran .= ",";
                    }

                    $sql_del_log_ext  .= "'$id'";
                    // $sql_del_tran .= "'$id'";
                    // $sql_ins_tran .= "('$id', '1', '1', '$date', '0', '$uid', null, null)";   
                }
            }

            if($id) 
				$sql_ins_log_ext  .= "('$id','".mysql_real_escape_string($row[1])."','".mysql_real_escape_string($row[2])."','".mysql_real_escape_string($row[3])."','".mysql_real_escape_string($row[4])."')";
            
            $i++;
            
            if($i > 5){
                $i = 0;
                $sql_del_tran .= ")";
                $sql_del_log_ext  .= ")";
                
				//Execute Query
				if($sql_del_log_ext  != $sql_del_log_extH_ext.")"){
					$res_del_log = mysqli_query($DBLink, $sql_del_log_ext);
					if ($res_del_log === false) {
						echo $sql_del_log_ext;
						echo mysqli_error($DBLink);
						exit();
					} 
					// else echo "berhasil3<br>";
					// echo $sql_del_log_ext.  "<br> <br><br>";
				}
                if($sql_ins_log_ext  != $sql_ins_log_extH_ext){
					$res_ins_log = mysqli_query($DBLink, $sql_ins_log_ext);
					if ($res_ins_log === false) {
						echo $sql_ins_log_ext;
						echo mysqli_error($DBLink);
						exit();
					} 
					// else echo "berhasil4<br>";
					// echo $sql_ins_log_ext.  "<br> <br><br>";
				}
                
                $sql_del_log_ext  = $sql_del_log_extH_ext;
                $sql_ins_log_ext  = $sql_ins_log_extH_ext;
                
				// echo "Log".$sql_del_log_ext."<br><br> logH".$sql_del_log_extH_ext;
                // echo "<br>=====================================================<br>";
            }
        }
        if (!feof($fh)) {
                echo "Error: unexpected fgets() fail\n";
        }
        fclose($fh);
        // if($i > 0){
            // $sql_del_tran .= ")";
            // $sql_del_log_ext  .= ")";
            
            // echo $sql_del_log_ext.  "<br> <br>=======================================================================<br>";
            // echo $sql_ins_log_ext.  "<br> <br>=======================================================================<br>";
            // echo $sql_del_tran. "<br> <br>=======================================================================<br>";
            // echo $sql_ins_tran. "<br> <br>=======================================================================<br>";
        // }
    }
    

    //mysql_query($sql);
?>
