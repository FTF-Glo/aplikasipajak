<?php
function CTOOLS_IsInFlag($iDebug, $iFlag)
{
  return (($iDebug & $iFlag) == $iFlag);
} // end of IsInFlag

function CTOOLS_ValidateQueryForDB($sStrVal, $sValDelim, $sDBType)
{
  $sVal = $sStrVal;

  switch ($sValDelim)
  {
    case "'":
      switch ($sDBType)
      {
        case 'MYSQL';
          $sVal = str_replace("'", "\\'", $sVal);
          break;
        case 'MSSQL';
          $sVal = str_replace("'", "''", $sVal);
          break;
      }
      break;
    case '"':
      switch ($sDBType)
      {
        case 'MYSQL';
          $sVal = str_replace('"', "\\\"", $sVal);
          break;
      }
      break;
  }

  return $sVal;
} // end of CTOOLS_ValidateQueryForDB

function CTOOLS_GetTempDir()
{
  $sTempDir = "";

  // Try to get from environment variable
  if ( !empty($_ENV['TMP']) )
  {
    $sTempDir = realpath( $_ENV['TMP'] );
  }
  else if ( !empty($_ENV['TMPDIR']) )
  {
    $sTempDir = realpath( $_ENV['TMPDIR'] );
  }
  else if ( !empty($_ENV['TEMP']) )
  {
    $sTempDir = realpath( $_ENV['TEMP'] );
  }
  // Detect by creating a temporary file
  else
  {
    // Try to use system's temporary directory
    // as random name shouldn't exist
    $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
    if ( $temp_file )
    {
      $temp_dir = realpath( dirname($temp_file) );
      @unlink( $temp_file );
      $sTempDir = $temp_dir;
    }
  }

  return $sTempDir;
} // end of CTOOLS_GetTempDir

function CTOOLS_ArrayRemoveAllElement(&$aRemove)
{
  if(!empty($aRemove) && $aRemove != null){
    while (sizeof($aRemove) > 0) array_shift($aRemove);
  }
  return $aRemove;
} // end of CTOOLS_ArrayRemoveAllElement

function CTOOLS_ExecCommand($sCmd, $sIn = "", &$sOut = "", &$sErr = "")
{
  $iRetVal = 0;

  $aDescriptorSpec = array(
   0 => array("pipe", "r"), // stdin
   1 => array("pipe", "w"), // stdout
   2 => array("pipe", "w") // stderr
  );

  $Process = proc_open("\"$sCmd\"", $aDescriptorSpec, $aPipes);
  if (is_resource($Process))
  {
    // $aPipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be written to log file

    if ($sIn != '')
    {
      fwrite($aPipes[0], $sIn);
    }
    fclose($aPipes[0]);

    $sOut = '';
    while(!feof($aPipes[1])) $sOut .= fread($aPipes[1], 1024);
    fclose($aPipes[1]);
    //echo "out [$sOut]\n";

    $sErr = '';
    while(!feof($aPipes[2])) $sErr .= fread($aPipes[2], 1024);
    fclose($aPipes[2]);
    //echo "err [$sErr]\n";

    $iRetVal = proc_close($Process);
  }

  return $iRetVal;
} // end of CTOOLS_ExecCommand

?>
