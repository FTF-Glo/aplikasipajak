<?php
// DESCRIPTION : * Generic String Flexible Parser (supports variable length component)
// AUTHOR      : Slamet Puji Santuso (slametps@gmail.com)
// COPYRIGHT   : Copyright (c) SCAN Tech Lab., 2008

/*************************************************************

  // USAGE EXAMPLE

  // include this file in your application
  require_once "cgparser.php";

  // use of CGenericFlexibleParser

  // example 1
  $s = "saya makan nasi";
  $a = array("first"=>4, "second"=>2);
  $cg = new CGenericFlexibleParser($s, $a);
  $cg->Parse();
  $aRes = $cg->GetParsedArray();
  print_r($aRes);
  $sResidue = $cg->GetResidue();
  echo "residue [$sResidue]\n"; // residue means unparsed trailing stream (total parsing definition is shorter than source stream)

  // example 2
  $s = "saya05makan nasi";
  $a = array("first"=>4, "second"=>array(0,2));
  $cg = new CGenericFlexibleParser($s, $a);
  $cg->Parse();
  $aRes = $cg->GetParsedArray();
  print_r($aRes);
  $sResidue = $cg->GetResidue();
  echo "residue [$sResidue]\n"; // residue means unparsed trailing stream (total parsing definition is shorter than source stream)

 *************************************************************/

class CGenericFlexibleParser
{
  // internal vars
  private $sStream = "";
  private $aParseConfig = NULL; // element = array(length, type), type = [0=fix (default), 1=LVAR, 2=LLVAR, 3=LLLVAR, 4=LLLLVAR]. If type!=0, length value is ignored.
  private $sResidue = "";

  private $aParsedArray = NULL;

  // debug
  private $bDebug = false;
  private $sLogFileName = "";

  function __construct($sStream = "", $aParseConfig = NULL)
  {
    $this->AssignVars($sStream, $aParseConfig);
  } // end of __construct

  function AssignVars($sStream = "", $aParseConfig = NULL)
  {
    $this->sStream = $sStream;
    $this->aParseConfig = $aParseConfig;
  } // end of AssignVars

  function Debug($bDebug = false, $sLogFileName = "")
  {
    $this->bDebug = $bDebug;
    $this->sLogFileName = $sLogFileName;
  } // end of Debug

  function Parse()
  {
    if ($this->aParseConfig)
    {
      $n = sizeof($this->aParseConfig);
      if ($n > 0)
      {
        $this->aParsedArray = array();
        $iIdx = 0;
        $nStreamLen = strlen($this->sStream);
        foreach($this->aParseConfig as $key=>$MVal)
        {
          $iType = 0; // fix length
          if ($iIdx < $nStreamLen)
          {
            if (@is_array($MVal))
            {
              $val = isset($MVal[0]) ? $MVal[0] : 0;
              if (sizeof($MVal) > 1)
                $iType = isset($MVal[1]) ? $MVal[1] : 0;
            }
            else
            {
              $val = $MVal;
            }

            if ($iType == 0) // fix length
            {
              $nLen = intval($val);
              $this->aParsedArray[$key] = substr($this->sStream, $iIdx, $nLen);
            }
            else // variable length
            {
              $nLen = intval(substr($this->sStream, $iIdx, $iType));
              $iIdx += $iType;
              $this->aParsedArray[$key] = substr($this->sStream, $iIdx, $nLen);
            }

            $iIdx += $nLen;
          }
          else
          {
            $this->aParsedArray[$key] = "";
          }
        }

        $this->sResidue = substr($this->sStream, $iIdx);
      }
      else
      {
        $this->sResidue = $this->sStream;
      }
    }
    else
    {
      $this->sResidue = $this->sStream;
    }
  } // end of Parse

  function GetStream()
  {
    return $this->sStream;
  } // end of GetStream

  function GetParsedArray()
  {
    return $this->aParsedArray;
  } // end of GetParsedArray

  function GetResidue()
  {
    return $this->sResidue;
  } // end of GetResidue

} // end of CGenericFlexibleParser

// DRIVER
/*$s = "saya makan nasi";
$a = array("first"=>4, "second"=>2);
$cg = new CGenericFlexibleParser($s, $a);
$cg->Parse();
$aRes = $cg->GetParsedArray();
print_r($aRes);
$sResidue = $cg->GetResidue();
echo "residue [$sResidue]\n"; // residue means unparsed trailing stream (total parsing definition is shorter than source stream)

$s = "saya05makan nasi";
$a = array("first"=>4, "second"=>array(0,2));
$cg = new CGenericFlexibleParser($s, $a);
$cg->Parse();
$aRes = $cg->GetParsedArray();
print_r($aRes);
$sResidue = $cg->GetResidue();
echo "residue [$sResidue]\n"; // residue means unparsed trailing stream (total parsing definition is shorter than source stream)

$s = "D0000129072907038  0380002000410cvntm gama crb 0050                                         0000000000000.000000014941000.00 0000278074434.84";
$a = array("seq"=>6, "postdt"=>4, "effdt"=>4, "branch"=>3, "sep1"=>2, "refcode"=>13, "content"=>60, "debet"=>16, "credit"=>16, "sep2"=>1, "balance"=>16);
$cg = new CGenericFlexibleParser($s, $a);
$cg->Parse();
$aRes = $cg->GetParsedArray();
print_r($aRes);
$sResidue = $cg->GetResidue();
echo "residue [$sResidue]\n"; // residue means unparsed trailing stream (total parsing definition is shorter than source stream)
*/
?>