<?php 
    require_once('queryOpen.php');
    $myFile = "$sRootPath$target";
    $fh = fopen($myFile, 'r') or die("can't open file");

    $sql_del_logH    = $sql_del_log  = "DELETE FROM cppmod_pbb_sppt_update_log WHERE CPM_SPPT_DOC_ID IN ( ";
    $sql_ins_logH    = $sql_ins_log  = "INSERT INTO cppmod_pbb_sppt_update_log VALUES ";
    $sql_del_tranH   = $sql_del_tran = "DELETE FROM `cppmod_pbb_tranmain` WHERE CPM_TRAN_SPPT_DOC_ID IN ( ";
    $sql_ins_tranH   = $sql_ins_tran = "INSERT INTO `cppmod_pbb_tranmain` VALUES ";

    $i = 0; $id = '';
    $date = date("Y-m-d H:i:s");
    if ($fh) {
        while (($content = fgets($fh, 4096)) !== false) {
            $content = str_replace("\n","",$content);
            $row = explode('|',$content);
			
			// echo "<pre>";
			// print_r($row);

            if($i > 0) $sql_ins_log  .= ",";
			
			// echo $sql_ins_log;
            
            if($id != mysql_real_escape_string($row[0])){
                $id = mysql_real_escape_string($row[0]);
                
                if($id){
                    if($i > 0){
						//$sql_del_log  .= ","; 
						// $sql_del_tran .= ","; 
						// $sql_ins_tran .= ",";
                    }

                    $sql_del_log  .= "'$id'";
                    $sql_del_tran .= "'$id'";
                    $sql_ins_tran .= "('$id', '1', '1', '$date', '0', '$uid', null, null)";   
                }
            }

            if($id) 
				$sql_ins_log  .= "('$id','".mysql_real_escape_string($row[1])."','".mysql_real_escape_string($row[2])."','".mysql_real_escape_string($row[3])."')";
            
            $i++;
            
            if($i > 5){
                $i = 0;
                $sql_del_tran .= ")";
                $sql_del_log  .= ")";
                
				//Execute Query
				if($sql_del_tran != $sql_del_tranH.")"){
					$res_del_tran = mysqli_query($DBLink, $sql_del_tran);
					if ($res_del_tran === false) {
						echo $sql_del_tran;
						echo mysqli_error($DBLink);
						exit();
					} 
					// else echo "berhasil1<br>";
					// echo $sql_del_tran. "<br> <br><br>";
				}
                if($sql_ins_tran != $sql_ins_tranH){  
					$res_ins_tran = mysqli_query($DBLink, $sql_ins_tran);
					if ($res_ins_tran === false) {
						echo $sql_del_tran;
						echo mysqli_error($DBLink);
						exit();
					} 
					// else echo "berhasil2<br>";
					// echo $sql_ins_tran. "<br> <br><br>";
				}
				if($sql_del_log  != $sql_del_logH.")"){
					$res_del_log = mysqli_query($DBLink, $sql_del_log);
					if ($res_del_log === false) {
						echo $sql_del_log;
						echo mysqli_error($DBLink);
						exit();
					} 
					// else echo "berhasil3<br>";
					// echo $sql_del_log.  "<br> <br><br>";
				}
                if($sql_ins_log  != $sql_ins_logH){
					$res_ins_log = mysqli_query($DBLink, $sql_ins_log);
					if ($res_ins_log === false) {
						echo $sql_ins_log;
						echo mysqli_error($DBLink);
						exit();
					} 
					// else echo "berhasil4<br>";
					// echo $sql_ins_log.  "<br> <br><br>";
				}
                
                $sql_del_log  = $sql_del_logH;
                $sql_ins_log  = $sql_ins_logH;
                $sql_del_tran = $sql_del_tranH;
                $sql_ins_tran = $sql_ins_tranH;
                
				// echo "Log".$sql_del_log."<br><br> logH".$sql_del_logH;
                // echo "<br>=====================================================<br>";
            }
        }
        if (!feof($fh)) {
                echo "Error: unexpected fgets() fail\n";
        }
        fclose($fh);
        // if($i > 0){
            // $sql_del_tran .= ")";
            // $sql_del_log  .= ")";
            
            // echo $sql_del_log.  "<br> <br>=======================================================================<br>";
            // echo $sql_ins_log.  "<br> <br>=======================================================================<br>";
            // echo $sql_del_tran. "<br> <br>=======================================================================<br>";
            // echo $sql_ins_tran. "<br> <br>=======================================================================<br>";
        // }
    }
    

    //mysql_query($sql);
?>
