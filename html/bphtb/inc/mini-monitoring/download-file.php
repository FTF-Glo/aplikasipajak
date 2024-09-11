<?php
set_time_limit(0);

// ------------
// LOCAL FUNCTIONS
// ------------
$UPDATE_DEFAULT_PATH="/home/onpays/update";

function downloadFile ($file, $mimetype)
{
 $status = 0;
 if (($file != NULL) && file_exists($file)) {
   if(isset($_SERVER['HTTP_USER_AGENT']) &&
      preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT']))
   {
     // IE Bug in download name workaround
     ini_set( 'zlib.output_compression','Off' );
   }
   // header ('Content-type: ' . mime_content_type($file)
   header ('Content-type: ' . $mimetype);
   header ('Content-Disposition: attachment; filename="'.basename($file).'"');
   header ('Expires: '.gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y"))).' GMT');
   header ('Accept-Ranges: bytes');
   // Use Cache-control: private not following:
   // header ('Cache-control: no-cache, must-revalidate');
   header("Cache-control: private");                   
   header ('Pragma: private');
   
   $size = filesize($file);
   if(isset($_SERVER['HTTP_RANGE'])) {
     list($a, $range) = explode("=",$_SERVER['HTTP_RANGE']);
     //if yes, download missing part
     str_replace($range, "-", $range);
     $size2 = $size-1;
     $new_length = $size2-$range;
     header("HTTP/1.1 206 Partial Content");
     header("Content-Length: $new_length");
     header("Content-Range: bytes $range-$size2/$size");
   }
   else
   {
     $size2=$size-1;
     header("Content-Range: bytes 0-$size2/$size");
     header("Content-Length: ".$size);
   }
  
   if ($file = fopen($file, 'r')) {
     while(!feof($file) and (connection_status()==0)) {
       $buff=fread($file, 1024);
	   print($buff);
		
       flush();
     }
     $status = (connection_status() == 0);
     fclose($file);
   }
 } else {
 	die("<b>404 File not found!</b>");
 }
 return($status);
}


// ------------
// MAIN PROGRAM
// ------------

// get remote parameters
$sQuery = (@isset($_REQUEST['q']) ? $_REQUEST['q'] : '');

$strfile=base64_decode($sQuery);
//var_dump($sClientInfo);

//echo $_SERVER{'DOCUMENT_ROOT'}.$strfile;
if ($strfile != '')
{
 
  if(file_exists($_SERVER{'DOCUMENT_ROOT'}.$strfile)){
	  downloadFile($_SERVER{'DOCUMENT_ROOT'}.$strfile,"application");
  }else if(file_exists($strfile)){
	  downloadFile($strfile,"application");
  }
  else{
	  die("<b>404 File not found!</b>");
  }

}
else // no subscriber id was sent
{
  die("<b>404 File not found!</b>");
}


?>
