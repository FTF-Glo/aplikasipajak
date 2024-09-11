<?php

// echo "123";
// if ( isset($_REQUEST["submit"]) ) {

   if ( isset($_FILES["file"])) {

            //if there was an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

        }
        else {
                 //Print file details
             // echo "Upload: " . $_FILES["file"]["name"] . "<br />";
             // echo "Type: " . $_FILES["file"]["type"] . "<br />";
             // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
             // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

                 //if file already exists
             if (file_exists("upload/" . $_FILES["file"]["name"])) {
           		 echo $_FILES["file"]["name"] . " already exists. ";
             }
             else {
             	$handle = fopen($_FILES["file"]["tmp_name"], 'r');
	        }
		       


	            if ( fopen($_FILES["file"]["tmp_name"], 'r') ) {
				    // echo "File opened.<br />";
				    $file = fopen($_FILES["file"]["tmp_name"], 'r');

				    // $firstline = fgets ($file, 4096 );
				    //     //Gets the number of fields, in CSV-files the names of the fields are mostly given in the first line
				    // $num = strlen($firstline) - strlen(str_replace(";", "", $firstline));

				    //     //save the different fields of the firstline in an array called fields
				    // $fields = array();
				    // $fields = explode( ";", $firstline, ($num+1) );

				    // // echo $fields;
				    // // print_r($fields);
				    // $line = array();
				    // $i = 0;

				        //CSV: one line is one record and the cells/fields are seperated by ";"
				        //so $dsatz is an two dimensional array saving the records like this: $dsatz[number of record][number of cell]
				    $dsatz = array();
				    	// print_r($line);
				    while ( $line[$i] = fgets ($file) ) {

				        // $dsatz[$i] = array();
				        $nilai =  explode( ";", $line[$i]) ;
				        if (!empty($nilai))
					        array_push($dsatz,$nilai);
				        // $dsatz[$i] = explode( ";", $line[$i], ($num+1) );

				        $i++;
				    }
				    // echo "<pre>";
				    // print_r($dsatz);
				    // echo "</pre>";
				    // exit;
				    // echo "123";
				    $array_nop  = array();
				    foreach ($dsatz as $key => $value) {
				    	foreach ($value as $key2 => $value2) {
				    		array_push($array_nop, $value2);
				    	}
				    }
				}else{
					echo "gagal fopen";
				}
	            //end
	             // exit;
            }
        }
     // }
     // else {
     //         echo "No file selected <br />";
     // }

$array_all = array();
$kunci = 0;
$temp_array = array();
for($x=0;$x<count($array_nop);$x++){
	array_push($array_all, $array_nop[$x]
		// array(
		// 	'nop'=>$array_nop[$x],
		// 	'tahun'=>$tahun
		// )
	);
}
$nop_string = "";
foreach ($array_all as $key => $value) {
	$nop_string .= "$value,";
}
$nop_string = rtrim($nop_string,",");
echo $nop_string;
// echo json_encode($array_all);
// echo "<pre>";
// print_r($array_all);
// echo "</pre>";
?>