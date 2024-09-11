<?php
// DESCRIPTION : * ISO8583 Data Element Definition (1987, 1993, 2003)
//               * Base Converter (ASCII HEXA TO BIN, ASCII BIN TO HEXA)
//               * ISO8583 Message Parser (ASCII) - CISO8583Parser
//               * ISO8583 Message Constructor (ASCII) - CISO8583Message
// TO DO       : * binary data handler (currently it is assumed can be manipulated using string utilities)
//               * Support tertiary bitmap (data element 129 - 192)
// AUTHOR      : Slamet Puji Santuso (slametps@gmail.com)
// COPYRIGHT   : Copyright (c) SCAN Tech Lab., 2008

/*************************************************************

  // USAGE EXAMPLE

  // include this file in your application
  require_once "c8583.php";

  // construct ISO8583 message stream
  $sStream = "";
  $m = new CISO8583Message();
  $m->SetValueForDataElement(0, "1410");
  $m->SetValueForDataElement(2, "53501");
  $m->SetValueForDataElement(39, "00112"); // will be truncated to max length (max = 4 char)
  $m->SetValueForDataElement(48, "10000V3");
  $m->SetValueForDataElement(41, "1234567890123456");
  date_default_timezone_set("Asia/Jakarta");
  $m->SetValueForDataElement(12, strftime("%Y%m%d%H%M%S", time()));
  //$m->SetVersion("1987");
  if ($m->ConstructStream())
  {
    $sStream = $m->GetConstructedStream();
    echo "[INFO] Stream [$sStream] [".strlen($sStream)."]\n";
  }
  else
  {
    echo "[ERROR] Unable to construct message. (".sprintf("#%d:%s", $m->GetLastErrorCode(), $m->GetLastErrorMsg()).")\n";
  }

  // parse ISO8583 message stream
  $sStream = "2410503000410201010005535013600000000100000100000000001200805020723006015070110000000026510000D35300000000011101E5BA37AC52083904FDEF185BE297009A874CC544BF2663E46D738268518F8F62SUBSCRIBER NAME          53585021-3754678    R1  000000450000000000200804200804202008032100000100000D00000000000000000000000000000001745000017550000000000000000000000000000000000372200100000000001200805020723000110000";
  $o = new CISO8583Parser($sStream);
  if ($o->Parse())
  {
    $a = $o->GetParsedDataElement();
    echo "[INFO] RESULT:\n";
    print_r($a);
  }
  else
  {
    echo "[ERROR] Unable to parse message stream. It might not a valid ISO8583 message stream. (".sprintf("#%d:%s", $o->GetLastErrorCode(), $o->GetLastErrorMsg()).")\n";
  }

 *************************************************************/

// ISO8583

$aISODataElement["1987"] = array();

$aISODataElement["1987"][0] = array();
$aISODataElement["1987"][0]["name"] = "MESSAGE TYPE IDENTIFIER (MTI)";
$aISODataElement["1987"][0]["format"] = "";
$aISODataElement["1987"][0]["type"] = "n";
$aISODataElement["1987"][0]["len"] = 4;
$aISODataElement["1987"][0]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][0]["cpad"] = "0";
$aISODataElement["1987"][0]["sde"] = NULL;

$aISODataElement["1987"][1] = array();
$aISODataElement["1987"][1]["name"] = "BIT MAP";
$aISODataElement["1987"][1]["format"] = "LLVAR";
$aISODataElement["1987"][1]["type"] = "an";
$aISODataElement["1987"][1]["len"] = 16; // can be extended to secondary (32)
$aISODataElement["1987"][1]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][1]["cpad"] = "0";
$aISODataElement["1987"][1]["sde"] = NULL;

$aISODataElement["1987"][2] = array();
$aISODataElement["1987"][2]["name"] = "PRIMARY ACCOUNT NUMBER (PAN)";
$aISODataElement["1987"][2]["format"] = "LLVAR";
$aISODataElement["1987"][2]["type"] = "n";
$aISODataElement["1987"][2]["len"] = 19;
$aISODataElement["1987"][2]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][2]["cpad"] = " ";
$aISODataElement["1987"][2]["sde"] = NULL;

$aISODataElement["1987"][3] = array();
$aISODataElement["1987"][3]["name"] = "PROCESSING CODE";
$aISODataElement["1987"][3]["format"] = "";
$aISODataElement["1987"][3]["type"] = "n";
$aISODataElement["1987"][3]["len"] = 6;
$aISODataElement["1987"][3]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][3]["cpad"] = "0";
$aISODataElement["1987"][3]["sde"] = NULL;

$aISODataElement["1987"][4] = array();
$aISODataElement["1987"][4]["name"] = "AMOUNT TRANSACTION";
$aISODataElement["1987"][4]["format"] = "";
$aISODataElement["1987"][4]["type"] = "n";
$aISODataElement["1987"][4]["len"] = 12;
$aISODataElement["1987"][4]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][4]["cpad"] = "0";
$aISODataElement["1987"][4]["sde"] = NULL;

$aISODataElement["1987"][5] = array();
$aISODataElement["1987"][5]["name"] = "AMOUNT SETTLEMENT";
$aISODataElement["1987"][5]["format"] = "";
$aISODataElement["1987"][5]["type"] = "n";
$aISODataElement["1987"][5]["len"] = 12;
$aISODataElement["1987"][5]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][5]["cpad"] = "0";
$aISODataElement["1987"][5]["sde"] = NULL;

$aISODataElement["1987"][6] = array();
$aISODataElement["1987"][6]["name"] = "AMOUNT CARDHOLDER BILLING";
$aISODataElement["1987"][6]["format"] = "";
$aISODataElement["1987"][6]["type"] = "n";
$aISODataElement["1987"][6]["len"] = 12;
$aISODataElement["1987"][6]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][6]["cpad"] = "0";
$aISODataElement["1987"][6]["sde"] = array();

$aISODataElement["1987"][7] = array();
$aISODataElement["1987"][7]["name"] = "DATE AND TIME TRANSMISSION";
$aISODataElement["1987"][7]["format"] = "MMDDhhmmss";
$aISODataElement["1987"][7]["type"] = "n";
$aISODataElement["1987"][7]["len"] = 10;
$aISODataElement["1987"][7]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][7]["cpad"] = "0";
$aISODataElement["1987"][7]["sde"] = NULL;

$aISODataElement["1987"][8] = array();
$aISODataElement["1987"][8]["name"] = "AMOUNT CARDHOLDER BILLING FEE";
$aISODataElement["1987"][8]["format"] = "";
$aISODataElement["1987"][8]["type"] = "n";
$aISODataElement["1987"][8]["len"] = 8;
$aISODataElement["1987"][8]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][8]["cpad"] = "0";
$aISODataElement["1987"][8]["sde"] = NULL;

$aISODataElement["1987"][9] = array();
$aISODataElement["1987"][9]["name"] = "CONVERSION RATE SETTLEMENT";
$aISODataElement["1987"][9]["format"] = "";
$aISODataElement["1987"][9]["type"] = "n";
$aISODataElement["1987"][9]["len"] = 8;
$aISODataElement["1987"][9]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][9]["cpad"] = "0";
$aISODataElement["1987"][9]["sde"] = NULL;

$aISODataElement["1987"][10] = array();
$aISODataElement["1987"][10]["name"] = "CONVERSION RATE CARDHOLDER BILLING";
$aISODataElement["1987"][10]["format"] = "";
$aISODataElement["1987"][10]["type"] = "n";
$aISODataElement["1987"][10]["len"] = 8;
$aISODataElement["1987"][10]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][10]["cpad"] = "0";
$aISODataElement["1987"][10]["sde"] = NULL;

$aISODataElement["1987"][11] = array();
$aISODataElement["1987"][11]["name"] = "SYSTEM TRACE AUDIT NUMBER";
$aISODataElement["1987"][11]["format"] = "";
$aISODataElement["1987"][11]["type"] = "n";
$aISODataElement["1987"][11]["len"] = 6;
$aISODataElement["1987"][11]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][11]["cpad"] = "0";
$aISODataElement["1987"][11]["sde"] = NULL;

$aISODataElement["1987"][12] = array();
$aISODataElement["1987"][12]["name"] = "TIME LOCAL TRANSACTION";
$aISODataElement["1987"][12]["format"] = "hhmmss";
$aISODataElement["1987"][12]["type"] = "n";
$aISODataElement["1987"][12]["len"] = 6;
$aISODataElement["1987"][12]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][12]["cpad"] = "0";
$aISODataElement["1987"][12]["sde"] = array();

$aISODataElement["1987"][13] = array();
$aISODataElement["1987"][13]["name"] = "DATE LOCAL TRANSACTION";
$aISODataElement["1987"][13]["format"] = "MMDD";
$aISODataElement["1987"][13]["type"] = "n";
$aISODataElement["1987"][13]["len"] = 4;
$aISODataElement["1987"][13]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][13]["cpad"] = "0";
$aISODataElement["1987"][13]["sde"] = NULL;

$aISODataElement["1987"][14] = array();
$aISODataElement["1987"][14]["name"] = "DATE EXPIRATION";
$aISODataElement["1987"][14]["format"] = "YYMM";
$aISODataElement["1987"][14]["type"] = "n";
$aISODataElement["1987"][14]["len"] = 4;
$aISODataElement["1987"][14]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][14]["cpad"] = "0";
$aISODataElement["1987"][14]["sde"] = NULL;

$aISODataElement["1987"][15] = array();
$aISODataElement["1987"][15]["name"] = "DATE SETTLEMENT";
$aISODataElement["1987"][15]["format"] = "MMDD";
$aISODataElement["1987"][15]["type"] = "n";
$aISODataElement["1987"][15]["len"] = 4;
$aISODataElement["1987"][15]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][15]["cpad"] = "0";
$aISODataElement["1987"][15]["sde"] = NULL;

$aISODataElement["1987"][16] = array();
$aISODataElement["1987"][16]["name"] = "DATE CONVERSION";
$aISODataElement["1987"][16]["format"] = "MMDD";
$aISODataElement["1987"][16]["type"] = "n";
$aISODataElement["1987"][16]["len"] = 4;
$aISODataElement["1987"][16]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][16]["cpad"] = "0";
$aISODataElement["1987"][16]["sde"] = NULL;

$aISODataElement["1987"][17] = array();
$aISODataElement["1987"][17]["name"] = "DATE CAPTURE";
$aISODataElement["1987"][17]["format"] = "MMDD";
$aISODataElement["1987"][17]["type"] = "n";
$aISODataElement["1987"][17]["len"] = 4;
$aISODataElement["1987"][17]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][17]["cpad"] = "0";
$aISODataElement["1987"][17]["sde"] = NULL;

$aISODataElement["1987"][18] = array();
$aISODataElement["1987"][18]["name"] = "MERCHANT TYPE";
$aISODataElement["1987"][18]["format"] = "";
$aISODataElement["1987"][18]["type"] = "n";
$aISODataElement["1987"][18]["len"] = 4;
$aISODataElement["1987"][18]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][18]["cpad"] = "0";
$aISODataElement["1987"][18]["sde"] = NULL;

$aISODataElement["1987"][19] = array();
$aISODataElement["1987"][19]["name"] = "ACQUIRING INSTITUTION COUNTRY CODE";
$aISODataElement["1987"][19]["format"] = "";
$aISODataElement["1987"][19]["type"] = "n";
$aISODataElement["1987"][19]["len"] = 3;
$aISODataElement["1987"][19]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][19]["cpad"] = "0";
$aISODataElement["1987"][19]["sde"] = NULL;

$aISODataElement["1987"][20] = array();
$aISODataElement["1987"][20]["name"] = "PAN EXTENDED COUNTRY CODE";
$aISODataElement["1987"][20]["format"] = "";
$aISODataElement["1987"][20]["type"] = "n";
$aISODataElement["1987"][20]["len"] = 3;
$aISODataElement["1987"][20]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][20]["cpad"] = "0";
$aISODataElement["1987"][20]["sde"] = NULL;

$aISODataElement["1987"][21] = array();
$aISODataElement["1987"][21]["name"] = "FORWARDING INSTITUTION COUNTRY CODE";
$aISODataElement["1987"][21]["format"] = "";
$aISODataElement["1987"][21]["type"] = "n";
$aISODataElement["1987"][21]["len"] = 3;
$aISODataElement["1987"][21]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][21]["cpad"] = "0";
$aISODataElement["1987"][21]["sde"] = NULL;

$aISODataElement["1987"][22] = array();
$aISODataElement["1987"][22]["name"] = "POINT OF SERVICE ENTRY MODE";
$aISODataElement["1987"][22]["format"] = "";
$aISODataElement["1987"][22]["type"] = "n";
$aISODataElement["1987"][22]["len"] = 3;
$aISODataElement["1987"][22]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][22]["cpad"] = "0";
$aISODataElement["1987"][22]["sde"] = NULL;

$aISODataElement["1987"][23] = array();
$aISODataElement["1987"][23]["name"] = "CARD SEQUENCE NUMBER";
$aISODataElement["1987"][23]["format"] = "";
$aISODataElement["1987"][23]["type"] = "n";
$aISODataElement["1987"][23]["len"] = 3;
$aISODataElement["1987"][23]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][23]["cpad"] = "0";
$aISODataElement["1987"][23]["sde"] = NULL;

$aISODataElement["1987"][24] = array();
$aISODataElement["1987"][24]["name"] = "NETWORK INTERNATIONAL IDENTIFIER";
$aISODataElement["1987"][24]["format"] = "";
$aISODataElement["1987"][24]["type"] = "n";
$aISODataElement["1987"][24]["len"] = 3;
$aISODataElement["1987"][24]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][24]["cpad"] = "0";
$aISODataElement["1987"][24]["sde"] = NULL;

$aISODataElement["1987"][25] = array();
$aISODataElement["1987"][25]["name"] = "POINT OF SERVICE CONDITION CODE";
$aISODataElement["1987"][25]["format"] = "";
$aISODataElement["1987"][25]["type"] = "n";
$aISODataElement["1987"][25]["len"] = 2;
$aISODataElement["1987"][25]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][25]["cpad"] = "0";
$aISODataElement["1987"][25]["sde"] = NULL;

$aISODataElement["1987"][26] = array();
$aISODataElement["1987"][26]["name"] = "POINT OF SERVICE PIN CAPTURE CODE";
$aISODataElement["1987"][26]["format"] = "";
$aISODataElement["1987"][26]["type"] = "n";
$aISODataElement["1987"][26]["len"] = 2;
$aISODataElement["1987"][26]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][26]["cpad"] = "0";
$aISODataElement["1987"][26]["sde"] = NULL;

$aISODataElement["1987"][27] = array();
$aISODataElement["1987"][27]["name"] = "AUTHORIZATION IDENTIFICATION RESP LEN";
$aISODataElement["1987"][27]["format"] = "";
$aISODataElement["1987"][27]["type"] = "n";
$aISODataElement["1987"][27]["len"] = 1;
$aISODataElement["1987"][27]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][27]["cpad"] = "0";
$aISODataElement["1987"][27]["sde"] = NULL;

$aISODataElement["1987"][28] = array();
$aISODataElement["1987"][28]["name"] = "AMOUNT, TRANSACTION FEE";
$aISODataElement["1987"][28]["format"] = "";
$aISODataElement["1987"][28]["type"] = "n";
$aISODataElement["1987"][28]["len"] = 9;
$aISODataElement["1987"][28]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][28]["cpad"] = "0";
$aISODataElement["1987"][28]["sde"] = NULL;

$aISODataElement["1987"][29] = array();
$aISODataElement["1987"][29]["name"] = "AMOUNT, TRANSACTION FEE";
$aISODataElement["1987"][29]["format"] = "";
$aISODataElement["1987"][29]["type"] = "n";
$aISODataElement["1987"][29]["len"] = 9;
$aISODataElement["1987"][29]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][29]["cpad"] = "0";
$aISODataElement["1987"][29]["sde"] = NULL;

$aISODataElement["1987"][30] = array();
$aISODataElement["1987"][30]["name"] = "AMOUNT, TRANSACTION PROCESSING FEE";
$aISODataElement["1987"][30]["format"] = "";
$aISODataElement["1987"][30]["type"] = "n";
$aISODataElement["1987"][30]["len"] = 9;
$aISODataElement["1987"][30]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][30]["cpad"] = "0";
$aISODataElement["1987"][30]["sde"] = NULL;

$aISODataElement["1987"][31] = array();
$aISODataElement["1987"][31]["name"] = "AMOUNT, SETTLEMENT PROCESSING FEE";
$aISODataElement["1987"][31]["format"] = "";
$aISODataElement["1987"][31]["type"] = "n";
$aISODataElement["1987"][31]["len"] = 9;
$aISODataElement["1987"][31]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][31]["cpad"] = "0";
$aISODataElement["1987"][31]["sde"] = NULL;

$aISODataElement["1987"][32] = array();
$aISODataElement["1987"][32]["name"] = "ACQUIRING INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1987"][32]["format"] = "LLVAR";
$aISODataElement["1987"][32]["type"] = "n";
$aISODataElement["1987"][32]["len"] = 11;
$aISODataElement["1987"][32]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][32]["cpad"] = "0";
$aISODataElement["1987"][32]["sde"] = NULL;

$aISODataElement["1987"][33] = array();
$aISODataElement["1987"][33]["name"] = "FORWARDING INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1987"][33]["format"] = "LLVAR";
$aISODataElement["1987"][33]["type"] = "n";
$aISODataElement["1987"][33]["len"] = 11;
$aISODataElement["1987"][33]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][33]["cpad"] = "0";
$aISODataElement["1987"][33]["sde"] = NULL;

$aISODataElement["1987"][34] = array();
$aISODataElement["1987"][34]["name"] = "FORWARDING INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1987"][34]["format"] = "LLVAR";
$aISODataElement["1987"][34]["type"] = "an";
$aISODataElement["1987"][34]["len"] = 28;
$aISODataElement["1987"][34]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][34]["cpad"] = "0";
$aISODataElement["1987"][34]["sde"] = NULL;

$aISODataElement["1987"][35] = array();
$aISODataElement["1987"][35]["name"] = "TRACK 2 DATA";
$aISODataElement["1987"][35]["format"] = "LLVAR";
$aISODataElement["1987"][35]["type"] = "z";
$aISODataElement["1987"][35]["len"] = 37;
$aISODataElement["1987"][35]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][35]["cpad"] = " ";
$aISODataElement["1987"][35]["sde"] = NULL;

$aISODataElement["1987"][36] = array();
$aISODataElement["1987"][36]["name"] = "TRACK 3 DATA";
$aISODataElement["1987"][36]["format"] = "LLLVAR";
$aISODataElement["1987"][36]["type"] = "z";
$aISODataElement["1987"][36]["len"] = 104;
$aISODataElement["1987"][36]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][36]["cpad"] = " ";
$aISODataElement["1987"][36]["sde"] = NULL;

$aISODataElement["1987"][37] = array();
$aISODataElement["1987"][37]["name"] = "RETRIEVAL REFERENCE NUMBER";
$aISODataElement["1987"][37]["format"] = "";
$aISODataElement["1987"][37]["type"] = "ans";
$aISODataElement["1987"][37]["len"] = 12;
$aISODataElement["1987"][37]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][37]["cpad"] = " ";
$aISODataElement["1987"][37]["sde"] = NULL;

$aISODataElement["1987"][38] = array();
$aISODataElement["1987"][38]["name"] = "AUTHORIZATION IDENTIFICATION RESPONSE";
$aISODataElement["1987"][38]["format"] = "";
$aISODataElement["1987"][38]["type"] = "ans";
$aISODataElement["1987"][38]["len"] = 6;
$aISODataElement["1987"][38]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][38]["cpad"] = " ";
$aISODataElement["1987"][38]["sde"] = NULL;

$aISODataElement["1987"][39] = array();
$aISODataElement["1987"][39]["name"] = "RESPONSE CODE";
$aISODataElement["1987"][39]["format"] = "";
$aISODataElement["1987"][39]["type"] = "ans";
$aISODataElement["1987"][39]["len"] = 2;
$aISODataElement["1987"][39]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][39]["cpad"] = "0";
$aISODataElement["1987"][39]["sde"] = NULL;

$aISODataElement["1987"][40] = array();
$aISODataElement["1987"][40]["name"] = "SERVICE RESTRICTION CODE";
$aISODataElement["1987"][40]["format"] = "";
$aISODataElement["1987"][40]["type"] = "ans";
$aISODataElement["1987"][40]["len"] = 3;
$aISODataElement["1987"][40]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][40]["cpad"] = "0";
$aISODataElement["1987"][40]["sde"] = NULL;

$aISODataElement["1987"][41] = array();
$aISODataElement["1987"][41]["name"] = "CARD ACCEPTOR TERMINAL IDENTIFICATION";
$aISODataElement["1987"][41]["format"] = "";
$aISODataElement["1987"][41]["type"] = "ans";
$aISODataElement["1987"][41]["len"] = 8;
$aISODataElement["1987"][41]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][41]["cpad"] = "0";
$aISODataElement["1987"][41]["sde"] = NULL;

$aISODataElement["1987"][42] = array();
$aISODataElement["1987"][42]["name"] = "CARD ACCEPTOR IDENTIFICATION CODE";
$aISODataElement["1987"][42]["format"] = "LLVAR";
$aISODataElement["1987"][42]["type"] = "ans";
$aISODataElement["1987"][42]["len"] = 15;
$aISODataElement["1987"][42]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][42]["cpad"] = "0";
$aISODataElement["1987"][42]["sde"] = NULL;

$aISODataElement["1987"][43] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][43]["name"] = "CARD ACCEPTOR NAME/LOCATION";
$aISODataElement["1987"][43]["format"] = ""; // Tag 81
$aISODataElement["1987"][43]["type"] = "ansp";
$aISODataElement["1987"][43]["len"] = 40;
$aISODataElement["1987"][43]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][43]["cpad"] = " ";
$aISODataElement["1987"][43]["sde"] = NULL;

$aISODataElement["1987"][44] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][44]["name"] = "ADDITIONAL RESPONSE DATA";
$aISODataElement["1987"][44]["format"] = "LLVAR";
$aISODataElement["1987"][44]["type"] = "ansp";
$aISODataElement["1987"][44]["len"] = 25;
$aISODataElement["1987"][44]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][44]["cpad"] = " ";
$aISODataElement["1987"][44]["sde"] = NULL;

$aISODataElement["1987"][45] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][45]["name"] = "TRACK 1 DATA";
$aISODataElement["1987"][45]["format"] = "LLVAR";
$aISODataElement["1987"][45]["type"] = "ansp";
$aISODataElement["1987"][45]["len"] = 76;
$aISODataElement["1987"][45]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][45]["cpad"] = " ";
$aISODataElement["1987"][45]["sde"] = NULL;

$aISODataElement["1987"][46] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][46]["name"] = "ADDITIONAL DATA ISO";
$aISODataElement["1987"][46]["format"] = "LLLVAR";
$aISODataElement["1987"][46]["type"] = "ansp";
$aISODataElement["1987"][46]["len"] = 999;
$aISODataElement["1987"][46]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][46]["cpad"] = " ";
$aISODataElement["1987"][46]["sde"] = NULL;

$aISODataElement["1987"][47] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][47]["name"] = "ADDITIONAL DATA NATIONAL";
$aISODataElement["1987"][47]["format"] = "LLLVAR";
$aISODataElement["1987"][47]["type"] = "ansp";
$aISODataElement["1987"][47]["len"] = 999;
$aISODataElement["1987"][47]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][47]["cpad"] = " ";
$aISODataElement["1987"][47]["sde"] = NULL;

$aISODataElement["1987"][48] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][48]["name"] = "ADDITIONAL DATA PRIVATE";
$aISODataElement["1987"][48]["format"] = "LLLVAR";
$aISODataElement["1987"][48]["type"] = "ansp";
$aISODataElement["1987"][48]["len"] = 999;
$aISODataElement["1987"][48]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][48]["cpad"] = " ";
$aISODataElement["1987"][48]["sde"] = NULL;

$aISODataElement["1987"][49] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][49]["name"] = "CURRENCY CODE, TRANSACTION";
$aISODataElement["1987"][49]["format"] = "";
$aISODataElement["1987"][49]["type"] = "n";
$aISODataElement["1987"][49]["len"] = 3;
$aISODataElement["1987"][49]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][49]["cpad"] = "0";
$aISODataElement["1987"][49]["sde"] = NULL;

$aISODataElement["1987"][50] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][50]["name"] = "CURRENCY CODE, SETTLEMENT";
$aISODataElement["1987"][50]["format"] = "";
$aISODataElement["1987"][50]["type"] = "n";
$aISODataElement["1987"][50]["len"] = 3;
$aISODataElement["1987"][50]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][50]["cpad"] = "0";
$aISODataElement["1987"][50]["sde"] = NULL;

$aISODataElement["1987"][51] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][51]["name"] = "CURRENCY CODE, CARDHOLDER BILLING";
$aISODataElement["1987"][51]["format"] = "";
$aISODataElement["1987"][51]["type"] = "n";
$aISODataElement["1987"][51]["len"] = 3;
$aISODataElement["1987"][51]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][51]["cpad"] = "0";
$aISODataElement["1987"][51]["sde"] = NULL;

$aISODataElement["1987"][52] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][52]["name"] = "PIN DATA";
$aISODataElement["1987"][52]["format"] = "";
$aISODataElement["1987"][52]["type"] = "b";
$aISODataElement["1987"][52]["len"] = 8;
$aISODataElement["1987"][52]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][52]["cpad"] = " ";
$aISODataElement["1987"][52]["sde"] = NULL;

$aISODataElement["1987"][53] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][53]["name"] = "SECURITY RELATED CONTROL INFORMATION";
$aISODataElement["1987"][53]["format"] = "";
$aISODataElement["1987"][53]["type"] = "n";
$aISODataElement["1987"][53]["len"] = 16;
$aISODataElement["1987"][53]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][53]["cpad"] = "0";
$aISODataElement["1987"][53]["sde"] = NULL;

$aISODataElement["1987"][54] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][54]["name"] = "AMOUNTS ADDITIONAL";
$aISODataElement["1987"][54]["format"] = "LLLVAR";
$aISODataElement["1987"][54]["type"] = "ans";
$aISODataElement["1987"][54]["len"] = 120;
$aISODataElement["1987"][54]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][54]["cpad"] = "0";
$aISODataElement["1987"][54]["sde"] = NULL;

$aISODataElement["1987"][55] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][55]["name"] = "RESERVED ISO";
$aISODataElement["1987"][55]["format"] = "LLLVAR";
$aISODataElement["1987"][55]["type"] = "ans";
$aISODataElement["1987"][55]["len"] = 999;
$aISODataElement["1987"][55]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][55]["cpad"] = " ";
$aISODataElement["1987"][55]["sde"] = NULL;

$aISODataElement["1987"][56] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][56]["name"] = "RESERVED ISO";
$aISODataElement["1987"][56]["format"] = "LLLVAR";
$aISODataElement["1987"][56]["type"] = "ans";
$aISODataElement["1987"][56]["len"] = 999;
$aISODataElement["1987"][56]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][56]["cpad"] = " ";
$aISODataElement["1987"][56]["sde"] = NULL;

$aISODataElement["1987"][57] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][57]["name"] = "RESERVED NATIONAL";
$aISODataElement["1987"][57]["format"] = "LLLVAR";
$aISODataElement["1987"][57]["type"] = "ans";
$aISODataElement["1987"][57]["len"] = 999;
$aISODataElement["1987"][57]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][57]["cpad"] = " ";
$aISODataElement["1987"][57]["sde"] = NULL;

$aISODataElement["1987"][58] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][58]["name"] = "RESERVED NATIONAL";
$aISODataElement["1987"][58]["format"] = "LLLVAR";
$aISODataElement["1987"][58]["type"] = "ans";
$aISODataElement["1987"][58]["len"] = 999;
$aISODataElement["1987"][58]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][58]["cpad"] = " ";
$aISODataElement["1987"][58]["sde"] = NULL;

$aISODataElement["1987"][59] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][59]["name"] = "RESERVED NATIONAL";
$aISODataElement["1987"][59]["format"] = "LLLVAR";
$aISODataElement["1987"][59]["type"] = "ans";
$aISODataElement["1987"][59]["len"] = 999;
$aISODataElement["1987"][59]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][59]["cpad"] = " ";
$aISODataElement["1987"][59]["sde"] = NULL;

$aISODataElement["1987"][60] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][60]["name"] = "RESERVED PRIVATE";
$aISODataElement["1987"][60]["format"] = "LLLVAR";
$aISODataElement["1987"][60]["type"] = "ans";
$aISODataElement["1987"][60]["len"] = 999;
$aISODataElement["1987"][60]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][60]["cpad"] = " ";
$aISODataElement["1987"][60]["sde"] = NULL;

$aISODataElement["1987"][61] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][61]["name"] = "RESERVED PRIVATE";
$aISODataElement["1987"][61]["format"] = "LLLVAR";
$aISODataElement["1987"][61]["type"] = "ans";
$aISODataElement["1987"][61]["len"] = 999;
$aISODataElement["1987"][61]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][61]["cpad"] = " ";
$aISODataElement["1987"][61]["sde"] = NULL;

$aISODataElement["1987"][62] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][62]["name"] = "RESERVED PRIVATE";
$aISODataElement["1987"][62]["format"] = "LLLVAR";
$aISODataElement["1987"][62]["type"] = "ans";
$aISODataElement["1987"][62]["len"] = 999;
$aISODataElement["1987"][62]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][62]["cpad"] = " ";
$aISODataElement["1987"][62]["sde"] = NULL;

$aISODataElement["1987"][63] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][63]["name"] = "RESERVED PRIVATE";
$aISODataElement["1987"][63]["format"] = "LLLVAR";
$aISODataElement["1987"][63]["type"] = "ans";
$aISODataElement["1987"][63]["len"] = 999;
$aISODataElement["1987"][63]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][63]["cpad"] = " ";
$aISODataElement["1987"][63]["sde"] = NULL;

$aISODataElement["1987"][64] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][64]["name"] = "MAC FIELD";
$aISODataElement["1987"][64]["format"] = "";
$aISODataElement["1987"][64]["type"] = "b";
$aISODataElement["1987"][64]["len"] = 8;
$aISODataElement["1987"][64]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][64]["cpad"] = " ";
$aISODataElement["1987"][64]["sde"] = NULL;

$aISODataElement["1987"][65] = array();
$aISODataElement["1987"][65]["name"] = "RESERVED FOR ISO USE"; // tertiary bitmap
$aISODataElement["1987"][65]["format"] = "";
$aISODataElement["1987"][65]["type"] = "an";
$aISODataElement["1987"][65]["len"] = 16;
$aISODataElement["1987"][65]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][65]["cpad"] = "0";
$aISODataElement["1987"][65]["sde"] = NULL;

$aISODataElement["1987"][66] = array();
$aISODataElement["1987"][66]["name"] = "SETTLEMENT CODE";
$aISODataElement["1987"][66]["format"] = "";
$aISODataElement["1987"][66]["type"] = "n";
$aISODataElement["1987"][66]["len"] = 1;
$aISODataElement["1987"][66]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][66]["cpad"] = "0";
$aISODataElement["1987"][66]["sde"] = NULL;

$aISODataElement["1987"][67] = array();
$aISODataElement["1987"][67]["name"] = "EXTENDED PAYMENT DATA";
$aISODataElement["1987"][67]["format"] = "";
$aISODataElement["1987"][67]["type"] = "n";
$aISODataElement["1987"][67]["len"] = 2;
$aISODataElement["1987"][67]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][67]["cpad"] = "0";
$aISODataElement["1987"][67]["sde"] = NULL;

$aISODataElement["1987"][68] = array();
$aISODataElement["1987"][68]["name"] = "COUNTRY CODE, RECEIVING INSTITUTION";
$aISODataElement["1987"][68]["format"] = "";
$aISODataElement["1987"][68]["type"] = "n";
$aISODataElement["1987"][68]["len"] = 3;
$aISODataElement["1987"][68]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][68]["cpad"] = "0";
$aISODataElement["1987"][68]["sde"] = NULL;

$aISODataElement["1987"][69] = array();
$aISODataElement["1987"][69]["name"] = "COUNTRY CODE, SETTLEMENT INSTITUTION";
$aISODataElement["1987"][69]["format"] = "";
$aISODataElement["1987"][69]["type"] = "n";
$aISODataElement["1987"][69]["len"] = 3;
$aISODataElement["1987"][69]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][69]["cpad"] = "0";
$aISODataElement["1987"][69]["sde"] = NULL;

$aISODataElement["1987"][70] = array();
$aISODataElement["1987"][70]["name"] = "INFORMATION CODE, NETWORK MANAGEMENT";
$aISODataElement["1987"][70]["format"] = "";
$aISODataElement["1987"][70]["type"] = "n";
$aISODataElement["1987"][70]["len"] = 3;
$aISODataElement["1987"][70]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][70]["cpad"] = "0";
$aISODataElement["1987"][70]["sde"] = NULL;

$aISODataElement["1987"][71] = array();
$aISODataElement["1987"][71]["name"] = "MESSAGE NUMBER";
$aISODataElement["1987"][71]["format"] = "";
$aISODataElement["1987"][71]["type"] = "n";
$aISODataElement["1987"][71]["len"] = 4;
$aISODataElement["1987"][71]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][71]["cpad"] = "0";
$aISODataElement["1987"][71]["sde"] = NULL;

$aISODataElement["1987"][72] = array();
$aISODataElement["1987"][72]["name"] = "MESSAGE NUMBER LAST";
$aISODataElement["1987"][72]["format"] = "";
$aISODataElement["1987"][72]["type"] = "n";
$aISODataElement["1987"][72]["len"] = 4;
$aISODataElement["1987"][72]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][72]["cpad"] = "0";
$aISODataElement["1987"][72]["sde"] = NULL;

$aISODataElement["1987"][73] = array();
$aISODataElement["1987"][73]["name"] = "DATE ACTION";
$aISODataElement["1987"][73]["format"] = "YYMMDD";
$aISODataElement["1987"][73]["type"] = "n";
$aISODataElement["1987"][73]["len"] = 6;
$aISODataElement["1987"][73]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][73]["cpad"] = "0";
$aISODataElement["1987"][73]["sde"] = NULL;

$aISODataElement["1987"][74] = array();
$aISODataElement["1987"][74]["name"] = "CREDITS, NUMBER";
$aISODataElement["1987"][74]["format"] = "";
$aISODataElement["1987"][74]["type"] = "n";
$aISODataElement["1987"][74]["len"] = 10;
$aISODataElement["1987"][74]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][74]["cpad"] = "0";
$aISODataElement["1987"][74]["sde"] = NULL;

$aISODataElement["1987"][75] = array();
$aISODataElement["1987"][75]["name"] = "CREDITS, REVERSAL NUMBER";
$aISODataElement["1987"][75]["format"] = "";
$aISODataElement["1987"][75]["type"] = "n";
$aISODataElement["1987"][75]["len"] = 10;
$aISODataElement["1987"][75]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][75]["cpad"] = "0";
$aISODataElement["1987"][75]["sde"] = NULL;

$aISODataElement["1987"][76] = array();
$aISODataElement["1987"][76]["name"] = "DEBITS, NUMBER";
$aISODataElement["1987"][76]["format"] = "";
$aISODataElement["1987"][76]["type"] = "n";
$aISODataElement["1987"][76]["len"] = 10;
$aISODataElement["1987"][76]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][76]["cpad"] = "0";
$aISODataElement["1987"][76]["sde"] = NULL;

$aISODataElement["1987"][77] = array();
$aISODataElement["1987"][77]["name"] = "DEBITS, REVERSAL NUMBER";
$aISODataElement["1987"][77]["format"] = "";
$aISODataElement["1987"][77]["type"] = "n";
$aISODataElement["1987"][77]["len"] = 10;
$aISODataElement["1987"][77]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][77]["cpad"] = "0";
$aISODataElement["1987"][77]["sde"] = NULL;

$aISODataElement["1987"][78] = array();
$aISODataElement["1987"][78]["name"] = "TRANSFER, NUMBER";
$aISODataElement["1987"][78]["format"] = "";
$aISODataElement["1987"][78]["type"] = "n";
$aISODataElement["1987"][78]["len"] = 10;
$aISODataElement["1987"][78]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][78]["cpad"] = "0";
$aISODataElement["1987"][78]["sde"] = NULL;

$aISODataElement["1987"][79] = array();
$aISODataElement["1987"][79]["name"] = "TRANSFER, REVERSAL NUMBER";
$aISODataElement["1987"][79]["format"] = "";
$aISODataElement["1987"][79]["type"] = "n";
$aISODataElement["1987"][79]["len"] = 10;
$aISODataElement["1987"][79]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][79]["cpad"] = "0";
$aISODataElement["1987"][79]["sde"] = NULL;

$aISODataElement["1987"][80] = array();
$aISODataElement["1987"][80]["name"] = "INQUIRIES, NUMBER";
$aISODataElement["1987"][80]["format"] = "";
$aISODataElement["1987"][80]["type"] = "n";
$aISODataElement["1987"][80]["len"] = 10;
$aISODataElement["1987"][80]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][80]["cpad"] = "0";
$aISODataElement["1987"][80]["sde"] = NULL;

$aISODataElement["1987"][81] = array();
$aISODataElement["1987"][81]["name"] = "AUTHORIZATIONS, NUMBER";
$aISODataElement["1987"][81]["format"] = "";
$aISODataElement["1987"][81]["type"] = "n";
$aISODataElement["1987"][81]["len"] = 10;
$aISODataElement["1987"][81]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][81]["cpad"] = "0";
$aISODataElement["1987"][81]["sde"] = NULL;

$aISODataElement["1987"][82] = array();
$aISODataElement["1987"][82]["name"] = "CREDITS, PROCESSING FEE AMOUNT";
$aISODataElement["1987"][82]["format"] = "";
$aISODataElement["1987"][82]["type"] = "n";
$aISODataElement["1987"][82]["len"] = 12;
$aISODataElement["1987"][82]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][82]["cpad"] = "0";
$aISODataElement["1987"][82]["sde"] = NULL;

$aISODataElement["1987"][83] = array();
$aISODataElement["1987"][83]["name"] = "CREDITS, TRANSACTION FEE AMOUNT";
$aISODataElement["1987"][83]["format"] = "";
$aISODataElement["1987"][83]["type"] = "n";
$aISODataElement["1987"][83]["len"] = 12;
$aISODataElement["1987"][83]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][83]["cpad"] = "0";
$aISODataElement["1987"][83]["sde"] = NULL;

$aISODataElement["1987"][84] = array();
$aISODataElement["1987"][84]["name"] = "DEBITS, PROCESSING FEE AMOUNT";
$aISODataElement["1987"][84]["format"] = "";
$aISODataElement["1987"][84]["type"] = "n";
$aISODataElement["1987"][84]["len"] = 12;
$aISODataElement["1987"][84]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][84]["cpad"] = "0";
$aISODataElement["1987"][84]["sde"] = NULL;

$aISODataElement["1987"][85] = array();
$aISODataElement["1987"][85]["name"] = "DEBITS, TRANSACTION FEE AMOUNT";
$aISODataElement["1987"][85]["format"] = "";
$aISODataElement["1987"][85]["type"] = "n";
$aISODataElement["1987"][85]["len"] = 12;
$aISODataElement["1987"][85]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][85]["cpad"] = "0";
$aISODataElement["1987"][85]["sde"] = NULL;

$aISODataElement["1987"][86] = array();
$aISODataElement["1987"][86]["name"] = "CREDITS, AMOUNT";
$aISODataElement["1987"][86]["format"] = "";
$aISODataElement["1987"][86]["type"] = "n";
$aISODataElement["1987"][86]["len"] = 16;
$aISODataElement["1987"][86]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][86]["cpad"] = "0";
$aISODataElement["1987"][86]["sde"] = NULL;

$aISODataElement["1987"][87] = array();
$aISODataElement["1987"][87]["name"] = "CREDITS, REVERSAL AMOUNT";
$aISODataElement["1987"][87]["format"] = "";
$aISODataElement["1987"][87]["type"] = "n";
$aISODataElement["1987"][87]["len"] = 16;
$aISODataElement["1987"][87]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][87]["cpad"] = "0";
$aISODataElement["1987"][87]["sde"] = NULL;

$aISODataElement["1987"][88] = array();
$aISODataElement["1987"][88]["name"] = "DEBITS, AMOUNT";
$aISODataElement["1987"][88]["format"] = "";
$aISODataElement["1987"][88]["type"] = "n";
$aISODataElement["1987"][88]["len"] = 16;
$aISODataElement["1987"][88]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][88]["cpad"] = "0";
$aISODataElement["1987"][88]["sde"] = NULL;

$aISODataElement["1987"][89] = array();
$aISODataElement["1987"][89]["name"] = "DEBITS, REVERSAL AMOUNT";
$aISODataElement["1987"][89]["format"] = "";
$aISODataElement["1987"][89]["type"] = "n";
$aISODataElement["1987"][89]["len"] = 16;
$aISODataElement["1987"][89]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][89]["cpad"] = "0";
$aISODataElement["1987"][89]["sde"] = NULL;

$aISODataElement["1987"][90] = array();
$aISODataElement["1987"][90]["name"] = "ORIGINAL DATA ELEMENTS";
$aISODataElement["1987"][90]["format"] = "";
$aISODataElement["1987"][90]["type"] = "n";
$aISODataElement["1987"][90]["len"] = 42;
$aISODataElement["1987"][90]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][90]["cpad"] = "0";
$aISODataElement["1987"][90]["sde"] = NULL;

$aISODataElement["1987"][91] = array();
$aISODataElement["1987"][91]["name"] = "FILE UPDATE CODE";
$aISODataElement["1987"][91]["format"] = "";
$aISODataElement["1987"][91]["type"] = "an";
$aISODataElement["1987"][91]["len"] = 1;
$aISODataElement["1987"][91]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][91]["cpad"] = "0";
$aISODataElement["1987"][91]["sde"] = NULL;

$aISODataElement["1987"][92] = array();
$aISODataElement["1987"][92]["name"] = "FILE SECURITY CODE";
$aISODataElement["1987"][92]["format"] = "";
$aISODataElement["1987"][92]["type"] = "an";
$aISODataElement["1987"][92]["len"] = 2;
$aISODataElement["1987"][92]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][92]["cpad"] = "0";
$aISODataElement["1987"][92]["sde"] = NULL;

$aISODataElement["1987"][93] = array();
$aISODataElement["1987"][93]["name"] = "RESPONSE INDICATOR";
$aISODataElement["1987"][93]["format"] = "";
$aISODataElement["1987"][93]["type"] = "an";
$aISODataElement["1987"][93]["len"] = 6;
$aISODataElement["1987"][93]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][93]["cpad"] = "0";
$aISODataElement["1987"][93]["sde"] = NULL;

$aISODataElement["1987"][94] = array();
$aISODataElement["1987"][94]["name"] = "SERVICE INDICATOR";
$aISODataElement["1987"][94]["format"] = "";
$aISODataElement["1987"][94]["type"] = "an";
$aISODataElement["1987"][94]["len"] = 7;
$aISODataElement["1987"][94]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][94]["cpad"] = "0";
$aISODataElement["1987"][94]["sde"] = NULL;

$aISODataElement["1987"][95] = array();
$aISODataElement["1987"][95]["name"] = "REPLACEMENT AMOUNTS";
$aISODataElement["1987"][95]["format"] = "";
$aISODataElement["1987"][95]["type"] = "an";
$aISODataElement["1987"][95]["len"] = 42;
$aISODataElement["1987"][95]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][95]["cpad"] = " ";
$aISODataElement["1987"][95]["sde"] = NULL;

$aISODataElement["1987"][96] = array();
$aISODataElement["1987"][96]["name"] = "MESSAGE SECURITY CODE";
$aISODataElement["1987"][96]["format"] = "";
$aISODataElement["1987"][96]["type"] = "b";
$aISODataElement["1987"][96]["len"] = 16;
$aISODataElement["1987"][96]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][96]["cpad"] = " ";
$aISODataElement["1987"][96]["sde"] = NULL;

$aISODataElement["1987"][97] = array();
$aISODataElement["1987"][97]["name"] = "AMOUNT, NET SETTLEMENT";
$aISODataElement["1987"][97]["format"] = "";
$aISODataElement["1987"][97]["type"] = "n";
$aISODataElement["1987"][97]["len"] = 17;
$aISODataElement["1987"][97]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][97]["cpad"] = "0";
$aISODataElement["1987"][97]["sde"] = NULL;

$aISODataElement["1987"][98] = array();
$aISODataElement["1987"][98]["name"] = "PAYEE";
$aISODataElement["1987"][98]["format"] = "";
$aISODataElement["1987"][98]["type"] = "ansp";
$aISODataElement["1987"][98]["len"] = 25;
$aISODataElement["1987"][98]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][98]["cpad"] = " ";
$aISODataElement["1987"][98]["sde"] = NULL;

$aISODataElement["1987"][99] = array();
$aISODataElement["1987"][99]["name"] = "SETTLEMENT INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1987"][99]["format"] = "LLVAR";
$aISODataElement["1987"][99]["type"] = "n";
$aISODataElement["1987"][99]["len"] = 11;
$aISODataElement["1987"][99]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][99]["cpad"] = "0";
$aISODataElement["1987"][99]["sde"] = NULL;

$aISODataElement["1987"][100] = array();
$aISODataElement["1987"][100]["name"] = "RECEIVING INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1987"][100]["format"] = "LLVAR";
$aISODataElement["1987"][100]["type"] = "n";
$aISODataElement["1987"][100]["len"] = 11;
$aISODataElement["1987"][100]["pad"] = STR_PAD_LEFT;
$aISODataElement["1987"][100]["cpad"] = "0";
$aISODataElement["1987"][100]["sde"] = NULL;

$aISODataElement["1987"][101] = array();
$aISODataElement["1987"][101]["name"] = "File Name";
$aISODataElement["1987"][101]["format"] = "LLVAR";
$aISODataElement["1987"][101]["type"] = "ansp";
$aISODataElement["1987"][101]["len"] = 17;
$aISODataElement["1987"][101]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][101]["cpad"] = " ";
$aISODataElement["1987"][101]["sde"] = NULL;

$aISODataElement["1987"][102] = array();
$aISODataElement["1987"][102]["name"] = "ACCOUNT IDENTIFICATION 1";
$aISODataElement["1987"][102]["format"] = "LLVAR";
$aISODataElement["1987"][102]["type"] = "ansp";
$aISODataElement["1987"][102]["len"] = 28;
$aISODataElement["1987"][102]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][102]["cpad"] = " ";
$aISODataElement["1987"][102]["sde"] = NULL;

$aISODataElement["1987"][103] = array();
$aISODataElement["1987"][103]["name"] = "ACCOUNT IDENTIFICATION 2";
$aISODataElement["1987"][103]["format"] = "LLVAR";
$aISODataElement["1987"][103]["type"] = "ansp";
$aISODataElement["1987"][103]["len"] = 28;
$aISODataElement["1987"][103]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][103]["cpad"] = " ";
$aISODataElement["1987"][103]["sde"] = NULL;

$aISODataElement["1987"][104] = array();
$aISODataElement["1987"][104]["name"] = "TRANSACTION DESCRIPTION";
$aISODataElement["1987"][104]["format"] = "LLLVAR";
$aISODataElement["1987"][104]["type"] = "ansp";
$aISODataElement["1987"][104]["len"] = 100;
$aISODataElement["1987"][104]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][104]["cpad"] = " ";
$aISODataElement["1987"][104]["sde"] = NULL;

$aISODataElement["1987"][105] = array();
$aISODataElement["1987"][105]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1987"][105]["format"] = "LLLVAR";
$aISODataElement["1987"][105]["type"] = "ansp";
$aISODataElement["1987"][105]["len"] = 999;
$aISODataElement["1987"][105]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][105]["cpad"] = " ";
$aISODataElement["1987"][105]["sde"] = NULL;

$aISODataElement["1987"][106] = array();
$aISODataElement["1987"][106]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1987"][106]["format"] = "LLLVAR";
$aISODataElement["1987"][106]["type"] = "ansp";
$aISODataElement["1987"][106]["len"] = 999;
$aISODataElement["1987"][106]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][106]["cpad"] = " ";
$aISODataElement["1987"][106]["sde"] = NULL;

$aISODataElement["1987"][107] = array();
$aISODataElement["1987"][107]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1987"][107]["format"] = "LLLVAR";
$aISODataElement["1987"][107]["type"] = "n";
$aISODataElement["1987"][107]["len"] = 999;
$aISODataElement["1987"][107]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][107]["cpad"] = " ";
$aISODataElement["1987"][107]["sde"] = NULL;

$aISODataElement["1987"][108] = array();
$aISODataElement["1987"][108]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1987"][108]["format"] = "LLLVAR";
$aISODataElement["1987"][108]["type"] = "ansp";
$aISODataElement["1987"][108]["len"] = 999;
$aISODataElement["1987"][108]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][108]["cpad"] = " ";
$aISODataElement["1987"][108]["sde"] = NULL;

$aISODataElement["1987"][109] = array();
$aISODataElement["1987"][109]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1987"][109]["format"] = "LLLVAR";
$aISODataElement["1987"][109]["type"] = "ansp";
$aISODataElement["1987"][109]["len"] = 999;
$aISODataElement["1987"][109]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][109]["cpad"] = " ";
$aISODataElement["1987"][109]["sde"] = NULL;

$aISODataElement["1987"][110] = array();
$aISODataElement["1987"][110]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1987"][110]["format"] = "LLLVAR";
$aISODataElement["1987"][110]["type"] = "ansp";
$aISODataElement["1987"][110]["len"] = 999;
$aISODataElement["1987"][110]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][110]["cpad"] = " ";
$aISODataElement["1987"][110]["sde"] = NULL;

$aISODataElement["1987"][111] = array();
$aISODataElement["1987"][111]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1987"][111]["format"] = "LLLVAR";
$aISODataElement["1987"][111]["type"] = "ansp";
$aISODataElement["1987"][111]["len"] = 999;
$aISODataElement["1987"][111]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][111]["cpad"] = " ";
$aISODataElement["1987"][111]["sde"] = NULL;

$aISODataElement["1987"][112] = array();
$aISODataElement["1987"][112]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1987"][112]["format"] = "LLLVAR";
$aISODataElement["1987"][112]["type"] = "ansp";
$aISODataElement["1987"][112]["len"] = 999;
$aISODataElement["1987"][112]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][112]["cpad"] = " ";
$aISODataElement["1987"][112]["sde"] = NULL;

$aISODataElement["1987"][113] = array();
$aISODataElement["1987"][113]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1987"][113]["format"] = "LLLVAR";
$aISODataElement["1987"][113]["type"] = "ansp";
$aISODataElement["1987"][113]["len"] = 999;
$aISODataElement["1987"][113]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][113]["cpad"] = " ";
$aISODataElement["1987"][113]["sde"] = NULL;

$aISODataElement["1987"][114] = array();
$aISODataElement["1987"][114]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1987"][114]["format"] = "LLLVAR";
$aISODataElement["1987"][114]["type"] = "ansp";
$aISODataElement["1987"][114]["len"] = 999;
$aISODataElement["1987"][114]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][114]["cpad"] = " ";
$aISODataElement["1987"][114]["sde"] = NULL;

$aISODataElement["1987"][115] = array();
$aISODataElement["1987"][115]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1987"][115]["format"] = "LLLVAR";
$aISODataElement["1987"][115]["type"] = "ansp";
$aISODataElement["1987"][115]["len"] = 999;
$aISODataElement["1987"][115]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][115]["cpad"] = " ";
$aISODataElement["1987"][115]["sde"] = NULL;

$aISODataElement["1987"][116] = array();
$aISODataElement["1987"][116]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1987"][116]["format"] = "LLLVAR";
$aISODataElement["1987"][116]["type"] = "ansp";
$aISODataElement["1987"][116]["len"] = 999;
$aISODataElement["1987"][116]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][116]["cpad"] = " ";
$aISODataElement["1987"][116]["sde"] = NULL;

$aISODataElement["1987"][117] = array();
$aISODataElement["1987"][117]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1987"][117]["format"] = "LLLVAR";
$aISODataElement["1987"][117]["type"] = "ansp";
$aISODataElement["1987"][117]["len"] = 999;
$aISODataElement["1987"][117]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][117]["cpad"] = " ";
$aISODataElement["1987"][117]["sde"] = NULL;

$aISODataElement["1987"][118] = array();
$aISODataElement["1987"][118]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1987"][118]["format"] = "LLLVAR";
$aISODataElement["1987"][118]["type"] = "ansp";
$aISODataElement["1987"][118]["len"] = 999;
$aISODataElement["1987"][118]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][118]["cpad"] = " ";
$aISODataElement["1987"][118]["sde"] = NULL;

$aISODataElement["1987"][119] = array();
$aISODataElement["1987"][119]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1987"][119]["format"] = "LLLVAR";
$aISODataElement["1987"][119]["type"] = "ansp";
$aISODataElement["1987"][119]["len"] = 999;
$aISODataElement["1987"][119]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][119]["cpad"] = " ";
$aISODataElement["1987"][119]["sde"] = NULL;

$aISODataElement["1987"][120] = array();
$aISODataElement["1987"][120]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1987"][120]["format"] = "LLLVAR";
$aISODataElement["1987"][120]["type"] = "ansp";
$aISODataElement["1987"][120]["len"] = 999;
$aISODataElement["1987"][120]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][120]["cpad"] = " ";
$aISODataElement["1987"][120]["sde"] = NULL;

$aISODataElement["1987"][121] = array();
$aISODataElement["1987"][121]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1987"][121]["format"] = "LLLVAR";
$aISODataElement["1987"][121]["type"] = "ansp";
$aISODataElement["1987"][121]["len"] = 999;
$aISODataElement["1987"][121]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][121]["cpad"] = " ";
$aISODataElement["1987"][121]["sde"] = NULL;

$aISODataElement["1987"][122] = array();
$aISODataElement["1987"][122]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1987"][122]["format"] = "LLLVAR";
$aISODataElement["1987"][122]["type"] = "ansp";
$aISODataElement["1987"][122]["len"] = 999;
$aISODataElement["1987"][122]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][122]["cpad"] = " ";
$aISODataElement["1987"][122]["sde"] = NULL;

$aISODataElement["1987"][123] = array();
$aISODataElement["1987"][123]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1987"][123]["format"] = "LLLVAR";
$aISODataElement["1987"][123]["type"] = "ansp";
$aISODataElement["1987"][123]["len"] = 999;
$aISODataElement["1987"][123]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][123]["cpad"] = " ";
$aISODataElement["1987"][123]["sde"] = NULL;

$aISODataElement["1987"][124] = array();
$aISODataElement["1987"][124]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1987"][124]["format"] = "LLLVAR";
$aISODataElement["1987"][124]["type"] = "ansp";
$aISODataElement["1987"][124]["len"] = 999;
$aISODataElement["1987"][124]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][124]["cpad"] = " ";
$aISODataElement["1987"][124]["sde"] = NULL;

$aISODataElement["1987"][125] = array();
$aISODataElement["1987"][125]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1987"][125]["format"] = "LLLVAR";
$aISODataElement["1987"][125]["type"] = "ansp";
$aISODataElement["1987"][125]["len"] = 999;
$aISODataElement["1987"][125]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][125]["cpad"] = " ";
$aISODataElement["1987"][125]["sde"] = NULL;

$aISODataElement["1987"][126] = array();
$aISODataElement["1987"][126]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1987"][126]["format"] = "LLLVAR";
$aISODataElement["1987"][126]["type"] = "ansp";
$aISODataElement["1987"][126]["len"] = 999;
$aISODataElement["1987"][126]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][126]["cpad"] = " ";
$aISODataElement["1987"][126]["sde"] = NULL;

$aISODataElement["1987"][127] = array();
$aISODataElement["1987"][127]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1987"][127]["format"] = "LLLVAR";
$aISODataElement["1987"][127]["type"] = "ansp";
$aISODataElement["1987"][127]["len"] = 999;
$aISODataElement["1987"][127]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][127]["cpad"] = " ";
$aISODataElement["1987"][127]["sde"] = NULL;

$aISODataElement["1987"][128] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1987"][128]["name"] = "MAC FIELD";
$aISODataElement["1987"][128]["format"] = "";
$aISODataElement["1987"][128]["type"] = "b";
$aISODataElement["1987"][128]["len"] = 8;
$aISODataElement["1987"][128]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1987"][128]["cpad"] = " ";
$aISODataElement["1987"][128]["sde"] = NULL;

// ISO8583:1993

$aISODataElement["1993"] = array();

$aISODataElement["1993"][0] = array();
$aISODataElement["1993"][0]["name"] = "MESSAGE TYPE IDENTIFIER (MTI)";
$aISODataElement["1993"][0]["format"] = "";
$aISODataElement["1993"][0]["type"] = "n";
$aISODataElement["1993"][0]["len"] = 4;
$aISODataElement["1993"][0]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][0]["cpad"] = "0";
$aISODataElement["1993"][0]["sde"] = NULL;

$aISODataElement["1993"][1] = array();
$aISODataElement["1993"][1]["name"] = "BIT MAP";
$aISODataElement["1993"][1]["format"] = "LLVAR";
$aISODataElement["1993"][1]["type"] = "an";
$aISODataElement["1993"][1]["len"] = 16; // can be extended to secondary (32)
$aISODataElement["1993"][1]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][1]["cpad"] = "0";
$aISODataElement["1993"][1]["sde"] = NULL;

$aISODataElement["1993"][2] = array();
$aISODataElement["1993"][2]["name"] = "PRIMARY ACCOUNT NUMBER (PAN)";
$aISODataElement["1993"][2]["format"] = "LLVAR";
$aISODataElement["1993"][2]["type"] = "n";
$aISODataElement["1993"][2]["len"] = 19;
$aISODataElement["1993"][2]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][2]["cpad"] = " ";
$aISODataElement["1993"][2]["sde"] = NULL;

$aISODataElement["1993"][3] = array();
$aISODataElement["1993"][3]["name"] = "PROCESSING CODE";
$aISODataElement["1993"][3]["format"] = "";
$aISODataElement["1993"][3]["type"] = "n";
$aISODataElement["1993"][3]["len"] = 6;
$aISODataElement["1993"][3]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][3]["cpad"] = "0";
$aISODataElement["1993"][3]["sde"] = NULL;

$aISODataElement["1993"][4] = array();
$aISODataElement["1993"][4]["name"] = "AMOUNT TRANSACTION";
$aISODataElement["1993"][4]["format"] = "";
$aISODataElement["1993"][4]["type"] = "n";
$aISODataElement["1993"][4]["len"] = 12;
$aISODataElement["1993"][4]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][4]["cpad"] = "0";
$aISODataElement["1993"][4]["sde"] = NULL;

$aISODataElement["1993"][5] = array();
$aISODataElement["1993"][5]["name"] = "AMOUNT RECONCILIATION";
$aISODataElement["1993"][5]["format"] = "";
$aISODataElement["1993"][5]["type"] = "n";
$aISODataElement["1993"][5]["len"] = 12;
$aISODataElement["1993"][5]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][5]["cpad"] = "0";
$aISODataElement["1993"][5]["sde"] = NULL;

$aISODataElement["1993"][6] = array();
$aISODataElement["1993"][6]["name"] = "AMOUNT CARDHOLDER BILLING";
$aISODataElement["1993"][6]["format"] = "";
$aISODataElement["1993"][6]["type"] = "n";
$aISODataElement["1993"][6]["len"] = 12;
$aISODataElement["1993"][6]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][6]["cpad"] = "0";
$aISODataElement["1993"][6]["sde"] = array();

$aISODataElement["1993"][7] = array();
$aISODataElement["1993"][7]["name"] = "DATE AND TIME TRANSMISSION";
$aISODataElement["1993"][7]["format"] = "MMDDhhmmss";
$aISODataElement["1993"][7]["type"] = "n";
$aISODataElement["1993"][7]["len"] = 10;
$aISODataElement["1993"][7]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][7]["cpad"] = "0";
$aISODataElement["1993"][7]["sde"] = NULL;

$aISODataElement["1993"][8] = array();
$aISODataElement["1993"][8]["name"] = "AMOUNT CARDHOLDER BILLING FEE";
$aISODataElement["1993"][8]["format"] = "";
$aISODataElement["1993"][8]["type"] = "n";
$aISODataElement["1993"][8]["len"] = 8;
$aISODataElement["1993"][8]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][8]["cpad"] = "0";
$aISODataElement["1993"][8]["sde"] = NULL;

$aISODataElement["1993"][9] = array();
$aISODataElement["1993"][9]["name"] = "CONVERSION RATE RECONCILIATION";
$aISODataElement["1993"][9]["format"] = "";
$aISODataElement["1993"][9]["type"] = "n";
$aISODataElement["1993"][9]["len"] = 8;
$aISODataElement["1993"][9]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][9]["cpad"] = "0";
$aISODataElement["1993"][9]["sde"] = NULL;

$aISODataElement["1993"][10] = array();
$aISODataElement["1993"][10]["name"] = "CONVERSION RATE CARDHOLDER BILLING";
$aISODataElement["1993"][10]["format"] = "";
$aISODataElement["1993"][10]["type"] = "n";
$aISODataElement["1993"][10]["len"] = 8;
$aISODataElement["1993"][10]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][10]["cpad"] = "0";
$aISODataElement["1993"][10]["sde"] = NULL;

$aISODataElement["1993"][11] = array();
$aISODataElement["1993"][11]["name"] = "SYSTEM TRACE AUDIT NUMBER";
$aISODataElement["1993"][11]["format"] = "";
$aISODataElement["1993"][11]["type"] = "n";
$aISODataElement["1993"][11]["len"] = 6;
$aISODataElement["1993"][11]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][11]["cpad"] = "0";
$aISODataElement["1993"][11]["sde"] = NULL;

$aISODataElement["1993"][12] = array();
$aISODataElement["1993"][12]["name"] = "DATE AND TIME LOCAL TRANSACTION";
$aISODataElement["1993"][12]["format"] = "YYMMDDhhmmss";
$aISODataElement["1993"][12]["type"] = "n";
$aISODataElement["1993"][12]["len"] = 12;
$aISODataElement["1993"][12]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][12]["cpad"] = "0";
$aISODataElement["1993"][12]["sde"] = array();

$aISODataElement["1993"][13] = array();
$aISODataElement["1993"][13]["name"] = "DATE EFFECTIVE";
$aISODataElement["1993"][13]["format"] = "MMDD";
$aISODataElement["1993"][13]["type"] = "n";
$aISODataElement["1993"][13]["len"] = 4;
$aISODataElement["1993"][13]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][13]["cpad"] = "0";
$aISODataElement["1993"][13]["sde"] = NULL;

$aISODataElement["1993"][14] = array();
$aISODataElement["1993"][14]["name"] = "DATE EXPIRATION";
$aISODataElement["1993"][14]["format"] = "YYMM";
$aISODataElement["1993"][14]["type"] = "n";
$aISODataElement["1993"][14]["len"] = 4;
$aISODataElement["1993"][14]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][14]["cpad"] = "0";
$aISODataElement["1993"][14]["sde"] = NULL;

$aISODataElement["1993"][15] = array();
$aISODataElement["1993"][15]["name"] = "DATE SETTLEMENT";
$aISODataElement["1993"][15]["format"] = "MMDD";
$aISODataElement["1993"][15]["type"] = "n";
$aISODataElement["1993"][15]["len"] = 6;
$aISODataElement["1993"][15]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][15]["cpad"] = "0";
$aISODataElement["1993"][15]["sde"] = NULL;

$aISODataElement["1993"][16] = array();
$aISODataElement["1993"][16]["name"] = "DATE CONVERSION";
$aISODataElement["1993"][16]["format"] = "MMDD";
$aISODataElement["1993"][16]["type"] = "n";
$aISODataElement["1993"][16]["len"] = 4;
$aISODataElement["1993"][16]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][16]["cpad"] = "0";
$aISODataElement["1993"][16]["sde"] = NULL;

$aISODataElement["1993"][17] = array();
$aISODataElement["1993"][17]["name"] = "DATE CAPTURE";
$aISODataElement["1993"][17]["format"] = "MMDD";
$aISODataElement["1993"][17]["type"] = "n";
$aISODataElement["1993"][17]["len"] = 4;
$aISODataElement["1993"][17]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][17]["cpad"] = "0";
$aISODataElement["1993"][17]["sde"] = NULL;

$aISODataElement["1993"][18] = array();
$aISODataElement["1993"][18]["name"] = "MERCHANT CATEGORY CODE";
$aISODataElement["1993"][18]["format"] = "";
$aISODataElement["1993"][18]["type"] = "n";
$aISODataElement["1993"][18]["len"] = 4;
$aISODataElement["1993"][18]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][18]["cpad"] = "0";
$aISODataElement["1993"][18]["sde"] = NULL;

$aISODataElement["1993"][19] = array();
$aISODataElement["1993"][19]["name"] = "ACQUIRING INSTITUTION COUNTRY CODE";
$aISODataElement["1993"][19]["format"] = "";
$aISODataElement["1993"][19]["type"] = "n";
$aISODataElement["1993"][19]["len"] = 3;
$aISODataElement["1993"][19]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][19]["cpad"] = "0";
$aISODataElement["1993"][19]["sde"] = NULL;

$aISODataElement["1993"][20] = array();
$aISODataElement["1993"][20]["name"] = "PAN EXTENDED COUNTRY CODE";
$aISODataElement["1993"][20]["format"] = "";
$aISODataElement["1993"][20]["type"] = "n";
$aISODataElement["1993"][20]["len"] = 3;
$aISODataElement["1993"][20]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][20]["cpad"] = "0";
$aISODataElement["1993"][20]["sde"] = NULL;

$aISODataElement["1993"][21] = array();
$aISODataElement["1993"][21]["name"] = "FORWARDING INSTITUTION COUNTRY CODE";
$aISODataElement["1993"][21]["format"] = "";
$aISODataElement["1993"][21]["type"] = "n";
$aISODataElement["1993"][21]["len"] = 3;
$aISODataElement["1993"][21]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][21]["cpad"] = "0";
$aISODataElement["1993"][21]["sde"] = NULL;

$aISODataElement["1993"][22] = array();
$aISODataElement["1993"][22]["name"] = "POINT OF SERVICE ENTRY MODE";
$aISODataElement["1993"][22]["format"] = "";
$aISODataElement["1993"][22]["type"] = "n";
$aISODataElement["1993"][22]["len"] = 3;
$aISODataElement["1993"][22]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][22]["cpad"] = "0";
$aISODataElement["1993"][22]["sde"] = NULL;

$aISODataElement["1993"][23] = array();
$aISODataElement["1993"][23]["name"] = "CARD SEQUENCE NUMBER";
$aISODataElement["1993"][23]["format"] = "";
$aISODataElement["1993"][23]["type"] = "n";
$aISODataElement["1993"][23]["len"] = 3;
$aISODataElement["1993"][23]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][23]["cpad"] = "0";
$aISODataElement["1993"][23]["sde"] = NULL;

$aISODataElement["1993"][24] = array();
$aISODataElement["1993"][24]["name"] = "NETWORK INTERNATIONAL IDENTIFIER";
$aISODataElement["1993"][24]["format"] = "";
$aISODataElement["1993"][24]["type"] = "n";
$aISODataElement["1993"][24]["len"] = 3;
$aISODataElement["1993"][24]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][24]["cpad"] = "0";
$aISODataElement["1993"][24]["sde"] = NULL;

$aISODataElement["1993"][25] = array();
$aISODataElement["1993"][25]["name"] = "MESSAGE REASON CODE";
$aISODataElement["1993"][25]["format"] = "";
$aISODataElement["1993"][25]["type"] = "n";
$aISODataElement["1993"][25]["len"] = 4;
$aISODataElement["1993"][25]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][25]["cpad"] = "0";
$aISODataElement["1993"][25]["sde"] = NULL;

$aISODataElement["1993"][26] = array();
$aISODataElement["1993"][26]["name"] = "CARD ACCEPTOR BUSINESS CODE";
$aISODataElement["1993"][26]["format"] = "";
$aISODataElement["1993"][26]["type"] = "n";
$aISODataElement["1993"][26]["len"] = 4;
$aISODataElement["1993"][26]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][26]["cpad"] = "0";
$aISODataElement["1993"][26]["sde"] = NULL;

$aISODataElement["1993"][27] = array();
$aISODataElement["1993"][27]["name"] = "AUTHORIZATION IDENTIFICATION RESP LEN";
$aISODataElement["1993"][27]["format"] = "";
$aISODataElement["1993"][27]["type"] = "n";
$aISODataElement["1993"][27]["len"] = 1;
$aISODataElement["1993"][27]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][27]["cpad"] = "0";
$aISODataElement["1993"][27]["sde"] = NULL;

$aISODataElement["1993"][28] = array();
$aISODataElement["1993"][28]["name"] = "DATE, RECONCILIATION";
$aISODataElement["1993"][28]["format"] = "YYMMDD";
$aISODataElement["1993"][28]["type"] = "n";
$aISODataElement["1993"][28]["len"] = 6;
$aISODataElement["1993"][28]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][28]["cpad"] = "0";
$aISODataElement["1993"][28]["sde"] = NULL;

$aISODataElement["1993"][29] = array();
$aISODataElement["1993"][29]["name"] = "RECONCILIATION INDICATOR";
$aISODataElement["1993"][29]["format"] = "";
$aISODataElement["1993"][29]["type"] = "n";
$aISODataElement["1993"][29]["len"] = 3;
$aISODataElement["1993"][29]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][29]["cpad"] = "0";
$aISODataElement["1993"][29]["sde"] = NULL;

$aISODataElement["1993"][30] = array();
$aISODataElement["1993"][30]["name"] = "AMOUNTS, ORIGINAL";
$aISODataElement["1993"][30]["format"] = "";
$aISODataElement["1993"][30]["type"] = "n";
$aISODataElement["1993"][30]["len"] = 24;
$aISODataElement["1993"][30]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][30]["cpad"] = "0";
$aISODataElement["1993"][30]["sde"] = NULL;

$aISODataElement["1993"][31] = array();
$aISODataElement["1993"][31]["name"] = "ACQUIRER REFERENCE DATA";
$aISODataElement["1993"][31]["format"] = "LLVAR";
$aISODataElement["1993"][31]["type"] = "ansp";
$aISODataElement["1993"][31]["len"] = 99;
$aISODataElement["1993"][31]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][31]["cpad"] = "0";
$aISODataElement["1993"][31]["sde"] = NULL;

$aISODataElement["1993"][32] = array();
$aISODataElement["1993"][32]["name"] = "ACQUIRER INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1993"][32]["format"] = "LLVAR";
$aISODataElement["1993"][32]["type"] = "n";
$aISODataElement["1993"][32]["len"] = 11;
$aISODataElement["1993"][32]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][32]["cpad"] = "0";
$aISODataElement["1993"][32]["sde"] = NULL;

$aISODataElement["1993"][33] = array();
$aISODataElement["1993"][33]["name"] = "FORWARDING INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1993"][33]["format"] = "LLVAR";
$aISODataElement["1993"][33]["type"] = "n";
$aISODataElement["1993"][33]["len"] = 11;
$aISODataElement["1993"][33]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][33]["cpad"] = "0";
$aISODataElement["1993"][33]["sde"] = NULL;

$aISODataElement["1993"][34] = array();
$aISODataElement["1993"][34]["name"] = "PRIMARY ACCOUNT NUMBER, EXTENDED";
$aISODataElement["1993"][34]["format"] = "LLVAR";
$aISODataElement["1993"][34]["type"] = "an";
$aISODataElement["1993"][34]["len"] = 28;
$aISODataElement["1993"][34]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][34]["cpad"] = "0";
$aISODataElement["1993"][34]["sde"] = NULL;

$aISODataElement["1993"][35] = array();
$aISODataElement["1993"][35]["name"] = "TRACK 2 DATA";
$aISODataElement["1993"][35]["format"] = "LLVAR";
$aISODataElement["1993"][35]["type"] = "z";
$aISODataElement["1993"][35]["len"] = 37;
$aISODataElement["1993"][35]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][35]["cpad"] = " ";
$aISODataElement["1993"][35]["sde"] = NULL;

$aISODataElement["1993"][36] = array();
$aISODataElement["1993"][36]["name"] = "TRACK 3 DATA";
$aISODataElement["1993"][36]["format"] = "LLLVAR";
$aISODataElement["1993"][36]["type"] = "z";
$aISODataElement["1993"][36]["len"] = 104;
$aISODataElement["1993"][36]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][36]["cpad"] = " ";
$aISODataElement["1993"][36]["sde"] = NULL;

$aISODataElement["1993"][37] = array();
$aISODataElement["1993"][37]["name"] = "RETRIEVAL REFERENCE NUMBER";
$aISODataElement["1993"][37]["format"] = "";
$aISODataElement["1993"][37]["type"] = "anp";
$aISODataElement["1993"][37]["len"] = 12;
$aISODataElement["1993"][37]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][37]["cpad"] = " ";
$aISODataElement["1993"][37]["sde"] = NULL;

$aISODataElement["1993"][38] = array();
$aISODataElement["1993"][38]["name"] = "APPROVAL CODE";
$aISODataElement["1993"][38]["format"] = "";
$aISODataElement["1993"][38]["type"] = "an";
$aISODataElement["1993"][38]["len"] = 6;
$aISODataElement["1993"][38]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][38]["cpad"] = " ";
$aISODataElement["1993"][38]["sde"] = NULL;

$aISODataElement["1993"][39] = array();
$aISODataElement["1993"][39]["name"] = "ACTION CODE";
$aISODataElement["1993"][39]["format"] = "";
$aISODataElement["1993"][39]["type"] = "n";
$aISODataElement["1993"][39]["len"] = 3;
$aISODataElement["1993"][39]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][39]["cpad"] = "0";
$aISODataElement["1993"][39]["sde"] = NULL;

$aISODataElement["1993"][40] = array();
$aISODataElement["1993"][40]["name"] = "SERVICE CODE";
$aISODataElement["1993"][40]["format"] = "";
$aISODataElement["1993"][40]["type"] = "n";
$aISODataElement["1993"][40]["len"] = 3;
$aISODataElement["1993"][40]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][40]["cpad"] = "0";
$aISODataElement["1993"][40]["sde"] = NULL;

$aISODataElement["1993"][41] = array();
$aISODataElement["1993"][41]["name"] = "CARD ACCEPTOR TERMINAL IDENTIFICATION";
$aISODataElement["1993"][41]["format"] = "";
$aISODataElement["1993"][41]["type"] = "ans";
$aISODataElement["1993"][41]["len"] = 8;
$aISODataElement["1993"][41]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][41]["cpad"] = "0";
$aISODataElement["1993"][41]["sde"] = NULL;

$aISODataElement["1993"][42] = array();
$aISODataElement["1993"][42]["name"] = "CARD ACCEPTOR IDENTIFICATION CODE";
$aISODataElement["1993"][42]["format"] = "LLVAR";
$aISODataElement["1993"][42]["type"] = "ans";
$aISODataElement["1993"][42]["len"] = 15;
$aISODataElement["1993"][42]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][42]["cpad"] = "0";
$aISODataElement["1993"][42]["sde"] = NULL;

$aISODataElement["1993"][43] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][43]["name"] = "CARD ACCEPTOR NAME/LOCATION";
$aISODataElement["1993"][43]["format"] = "LLVAR";
$aISODataElement["1993"][43]["type"] = "an";
$aISODataElement["1993"][43]["len"] = 99;
$aISODataElement["1993"][43]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][43]["cpad"] = " ";
$aISODataElement["1993"][43]["sde"] = NULL;

$aISODataElement["1993"][44] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][44]["name"] = "ADDITIONAL RESPONSE DATA";
$aISODataElement["1993"][44]["format"] = "LLVAR";
$aISODataElement["1993"][44]["type"] = "ansb";
$aISODataElement["1993"][44]["len"] = 99;
$aISODataElement["1993"][44]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][44]["cpad"] = " ";
$aISODataElement["1993"][44]["sde"] = NULL;

$aISODataElement["1993"][45] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][45]["name"] = "TRACK 1 DATA";
$aISODataElement["1993"][45]["format"] = "LLVAR";
$aISODataElement["1993"][45]["type"] = "ans";
$aISODataElement["1993"][45]["len"] = 76;
$aISODataElement["1993"][45]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][45]["cpad"] = " ";
$aISODataElement["1993"][45]["sde"] = NULL;

$aISODataElement["1993"][46] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][46]["name"] = "AMOUNTS, FEES";
$aISODataElement["1993"][46]["format"] = "LLLVAR";
$aISODataElement["1993"][46]["type"] = "ans";
$aISODataElement["1993"][46]["len"] = 204;
$aISODataElement["1993"][46]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][46]["cpad"] = " ";
$aISODataElement["1993"][46]["sde"] = NULL;

$aISODataElement["1993"][47] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][47]["name"] = "ADDITIONAL DATA NATIONAL";
$aISODataElement["1993"][47]["format"] = "LLLVAR";
$aISODataElement["1993"][47]["type"] = "ans";
$aISODataElement["1993"][47]["len"] = 999;
$aISODataElement["1993"][47]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][47]["cpad"] = " ";
$aISODataElement["1993"][47]["sde"] = NULL;

$aISODataElement["1993"][48] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][48]["name"] = "ADDITIONAL DATA PRIVATE";
$aISODataElement["1993"][48]["format"] = "LLLVAR";
$aISODataElement["1993"][48]["type"] = "ans";
$aISODataElement["1993"][48]["len"] = 999;
$aISODataElement["1993"][48]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][48]["cpad"] = " ";
$aISODataElement["1993"][48]["sde"] = NULL;

$aISODataElement["1993"][49] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][49]["name"] = "CURRENCY CODE, TRANSACTION";
$aISODataElement["1993"][49]["format"] = "";
$aISODataElement["1993"][49]["type"] = "n";
$aISODataElement["1993"][49]["len"] = 3;
$aISODataElement["1993"][49]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][49]["cpad"] = "0";
$aISODataElement["1993"][49]["sde"] = NULL;

$aISODataElement["1993"][50] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][50]["name"] = "CURRENCY CODE, SETTLEMENT";
$aISODataElement["1993"][50]["format"] = "";
$aISODataElement["1993"][50]["type"] = "n";
$aISODataElement["1993"][50]["len"] = 3;
$aISODataElement["1993"][50]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][50]["cpad"] = "0";
$aISODataElement["1993"][50]["sde"] = NULL;

$aISODataElement["1993"][51] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][51]["name"] = "CURRENCY CODE, CARDHOLDER BILLING";
$aISODataElement["1993"][51]["format"] = "";
$aISODataElement["1993"][51]["type"] = "n";
$aISODataElement["1993"][51]["len"] = 3;
$aISODataElement["1993"][51]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][51]["cpad"] = "0";
$aISODataElement["1993"][51]["sde"] = NULL;

$aISODataElement["1993"][52] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][52]["name"] = "PIN DATA";
$aISODataElement["1993"][52]["format"] = "";
$aISODataElement["1993"][52]["type"] = "b";
$aISODataElement["1993"][52]["len"] = 8;
$aISODataElement["1993"][52]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][52]["cpad"] = " ";
$aISODataElement["1993"][52]["sde"] = NULL;

$aISODataElement["1993"][53] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][53]["name"] = "SECURITY RELATED CONTROL INFORMATION";
$aISODataElement["1993"][53]["format"] = "LLVAR";
$aISODataElement["1993"][53]["type"] = "b";
$aISODataElement["1993"][53]["len"] = 48;
$aISODataElement["1993"][53]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][53]["cpad"] = " ";
$aISODataElement["1993"][53]["sde"] = NULL;

$aISODataElement["1993"][54] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][54]["name"] = "AMOUNTS ADDITIONAL";
$aISODataElement["1993"][54]["format"] = "LLLVAR";
$aISODataElement["1993"][54]["type"] = "ans";
$aISODataElement["1993"][54]["len"] = 120;
$aISODataElement["1993"][54]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][54]["cpad"] = "0";
$aISODataElement["1993"][54]["sde"] = NULL;

$aISODataElement["1993"][55] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][55]["name"] = "IC CARD SYSTEM RELATED DATA";
$aISODataElement["1993"][55]["format"] = "LLLVAR";
$aISODataElement["1993"][55]["type"] = "b";
$aISODataElement["1993"][55]["len"] = 255;
$aISODataElement["1993"][55]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][55]["cpad"] = " ";
$aISODataElement["1993"][55]["sde"] = NULL;

$aISODataElement["1993"][56] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][56]["name"] = "ORIGINAL DATA ELEMENTS";
$aISODataElement["1993"][56]["format"] = "LLVAR";
$aISODataElement["1993"][56]["type"] = "n";
$aISODataElement["1993"][56]["len"] = 35;
$aISODataElement["1993"][56]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][56]["cpad"] = "0";
$aISODataElement["1993"][56]["sde"] = NULL;

$aISODataElement["1993"][57] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][57]["name"] = "AUTHORIZATION LIFE CYCLE CODE";
$aISODataElement["1993"][57]["format"] = "";
$aISODataElement["1993"][57]["type"] = "n";
$aISODataElement["1993"][57]["len"] = 3;
$aISODataElement["1993"][57]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][57]["cpad"] = "0";
$aISODataElement["1993"][57]["sde"] = NULL;

$aISODataElement["1993"][58] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][58]["name"] = "AUTHORIZING AGENT INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1993"][58]["format"] = "LLVAR";
$aISODataElement["1993"][58]["type"] = "n";
$aISODataElement["1993"][58]["len"] = 11;
$aISODataElement["1993"][58]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][58]["cpad"] = "0";
$aISODataElement["1993"][58]["sde"] = NULL;

$aISODataElement["1993"][59] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][59]["name"] = "TRANSPORT DATA";
$aISODataElement["1993"][59]["format"] = "LLLVAR";
$aISODataElement["1993"][59]["type"] = "ans";
$aISODataElement["1993"][59]["len"] = 999;
$aISODataElement["1993"][59]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][59]["cpad"] = " ";
$aISODataElement["1993"][59]["sde"] = NULL;

$aISODataElement["1993"][60] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][60]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1993"][60]["format"] = "LLLVAR";
$aISODataElement["1993"][60]["type"] = "ans";
$aISODataElement["1993"][60]["len"] = 999;
$aISODataElement["1993"][60]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][60]["cpad"] = " ";
$aISODataElement["1993"][60]["sde"] = NULL;

$aISODataElement["1993"][61] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][61]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1993"][61]["format"] = "LLLVAR";
$aISODataElement["1993"][61]["type"] = "ans";
$aISODataElement["1993"][61]["len"] = 999;
$aISODataElement["1993"][61]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][61]["cpad"] = " ";
$aISODataElement["1993"][61]["sde"] = NULL;

$aISODataElement["1993"][62] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][62]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1993"][62]["format"] = "LLLVAR";
$aISODataElement["1993"][62]["type"] = "ans";
$aISODataElement["1993"][62]["len"] = 999;
$aISODataElement["1993"][62]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][62]["cpad"] = " ";
$aISODataElement["1993"][62]["sde"] = NULL;

$aISODataElement["1993"][63] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][63]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1993"][63]["format"] = "LLLVAR";
$aISODataElement["1993"][63]["type"] = "ans";
$aISODataElement["1993"][63]["len"] = 999;
$aISODataElement["1993"][63]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][63]["cpad"] = " ";
$aISODataElement["1993"][63]["sde"] = NULL;

$aISODataElement["1993"][64] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][64]["name"] = "MAC FIELD";
$aISODataElement["1993"][64]["format"] = "";
$aISODataElement["1993"][64]["type"] = "b";
$aISODataElement["1993"][64]["len"] = 8;
$aISODataElement["1993"][64]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][64]["cpad"] = " ";
$aISODataElement["1993"][64]["sde"] = NULL;

$aISODataElement["1993"][65] = array();
$aISODataElement["1993"][65]["name"] = "RESERVED FOR ISO USE"; // tertiary bitmap
$aISODataElement["1993"][65]["format"] = "";
$aISODataElement["1993"][65]["type"] = "an";
$aISODataElement["1993"][65]["len"] = 16;
$aISODataElement["1993"][65]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][65]["cpad"] = "0";
$aISODataElement["1993"][65]["sde"] = NULL;

$aISODataElement["1993"][66] = array();
$aISODataElement["1993"][66]["name"] = "AMOUNT, ORIGINAL FEES";
$aISODataElement["1993"][66]["format"] = "LLLVAR";
$aISODataElement["1993"][66]["type"] = "ans";
$aISODataElement["1993"][66]["len"] = 204;
$aISODataElement["1993"][66]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][66]["cpad"] = "0";
$aISODataElement["1993"][66]["sde"] = NULL;

$aISODataElement["1993"][67] = array();
$aISODataElement["1993"][67]["name"] = "EXTENDED PAYMENT DATA";
$aISODataElement["1993"][67]["format"] = "";
$aISODataElement["1993"][67]["type"] = "n";
$aISODataElement["1993"][67]["len"] = 2;
$aISODataElement["1993"][67]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][67]["cpad"] = "0";
$aISODataElement["1993"][67]["sde"] = NULL;

$aISODataElement["1993"][68] = array();
$aISODataElement["1993"][68]["name"] = "COUNTRY CODE, RECEIVING INSTITUTION";
$aISODataElement["1993"][68]["format"] = "";
$aISODataElement["1993"][68]["type"] = "n";
$aISODataElement["1993"][68]["len"] = 3;
$aISODataElement["1993"][68]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][68]["cpad"] = "0";
$aISODataElement["1993"][68]["sde"] = NULL;

$aISODataElement["1993"][69] = array();
$aISODataElement["1993"][69]["name"] = "COUNTRY CODE, SETTLEMENT INSTITUTION";
$aISODataElement["1993"][69]["format"] = "";
$aISODataElement["1993"][69]["type"] = "n";
$aISODataElement["1993"][69]["len"] = 3;
$aISODataElement["1993"][69]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][69]["cpad"] = "0";
$aISODataElement["1993"][69]["sde"] = NULL;

$aISODataElement["1993"][70] = array();
$aISODataElement["1993"][70]["name"] = "COUNTRY CODE, AUTHORIZING AGENT INSTITUTION";
$aISODataElement["1993"][70]["format"] = "";
$aISODataElement["1993"][70]["type"] = "n";
$aISODataElement["1993"][70]["len"] = 3;
$aISODataElement["1993"][70]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][70]["cpad"] = "0";
$aISODataElement["1993"][70]["sde"] = NULL;

$aISODataElement["1993"][71] = array();
$aISODataElement["1993"][71]["name"] = "MESSAGE NUMBER";
$aISODataElement["1993"][71]["format"] = "";
$aISODataElement["1993"][71]["type"] = "n";
$aISODataElement["1993"][71]["len"] = 8;
$aISODataElement["1993"][71]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][71]["cpad"] = "0";
$aISODataElement["1993"][71]["sde"] = NULL;

$aISODataElement["1993"][72] = array();
$aISODataElement["1993"][72]["name"] = "DATA RECORD";
$aISODataElement["1993"][72]["format"] = "LLLVAR";
$aISODataElement["1993"][72]["type"] = "ans";
$aISODataElement["1993"][72]["len"] = 999;
$aISODataElement["1993"][72]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][72]["cpad"] = " ";
$aISODataElement["1993"][72]["sde"] = NULL;

$aISODataElement["1993"][73] = array();
$aISODataElement["1993"][73]["name"] = "DATE ACTION";
$aISODataElement["1993"][73]["format"] = "YYMMDD";
$aISODataElement["1993"][73]["type"] = "n";
$aISODataElement["1993"][73]["len"] = 6;
$aISODataElement["1993"][73]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][73]["cpad"] = "0";
$aISODataElement["1993"][73]["sde"] = NULL;

$aISODataElement["1993"][74] = array();
$aISODataElement["1993"][74]["name"] = "CREDITS, NUMBER";
$aISODataElement["1993"][74]["format"] = "";
$aISODataElement["1993"][74]["type"] = "n";
$aISODataElement["1993"][74]["len"] = 10;
$aISODataElement["1993"][74]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][74]["cpad"] = "0";
$aISODataElement["1993"][74]["sde"] = NULL;

$aISODataElement["1993"][75] = array();
$aISODataElement["1993"][75]["name"] = "CREDITS, REVERSAL NUMBER";
$aISODataElement["1993"][75]["format"] = "";
$aISODataElement["1993"][75]["type"] = "n";
$aISODataElement["1993"][75]["len"] = 10;
$aISODataElement["1993"][75]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][75]["cpad"] = "0";
$aISODataElement["1993"][75]["sde"] = NULL;

$aISODataElement["1993"][76] = array();
$aISODataElement["1993"][76]["name"] = "DEBITS, NUMBER";
$aISODataElement["1993"][76]["format"] = "";
$aISODataElement["1993"][76]["type"] = "n";
$aISODataElement["1993"][76]["len"] = 10;
$aISODataElement["1993"][76]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][76]["cpad"] = "0";
$aISODataElement["1993"][76]["sde"] = NULL;

$aISODataElement["1993"][77] = array();
$aISODataElement["1993"][77]["name"] = "DEBITS, REVERSAL NUMBER";
$aISODataElement["1993"][77]["format"] = "";
$aISODataElement["1993"][77]["type"] = "n";
$aISODataElement["1993"][77]["len"] = 10;
$aISODataElement["1993"][77]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][77]["cpad"] = "0";
$aISODataElement["1993"][77]["sde"] = NULL;

$aISODataElement["1993"][78] = array();
$aISODataElement["1993"][78]["name"] = "TRANSFER, NUMBER";
$aISODataElement["1993"][78]["format"] = "";
$aISODataElement["1993"][78]["type"] = "n";
$aISODataElement["1993"][78]["len"] = 10;
$aISODataElement["1993"][78]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][78]["cpad"] = "0";
$aISODataElement["1993"][78]["sde"] = NULL;

$aISODataElement["1993"][79] = array();
$aISODataElement["1993"][79]["name"] = "TRANSFER, REVERSAL NUMBER";
$aISODataElement["1993"][79]["format"] = "";
$aISODataElement["1993"][79]["type"] = "n";
$aISODataElement["1993"][79]["len"] = 10;
$aISODataElement["1993"][79]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][79]["cpad"] = "0";
$aISODataElement["1993"][79]["sde"] = NULL;

$aISODataElement["1993"][80] = array();
$aISODataElement["1993"][80]["name"] = "INQUIRIES, NUMBER";
$aISODataElement["1993"][80]["format"] = "";
$aISODataElement["1993"][80]["type"] = "n";
$aISODataElement["1993"][80]["len"] = 10;
$aISODataElement["1993"][80]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][80]["cpad"] = "0";
$aISODataElement["1993"][80]["sde"] = NULL;

$aISODataElement["1993"][81] = array();
$aISODataElement["1993"][81]["name"] = "AUTHORIZATIONS, NUMBER";
$aISODataElement["1993"][81]["format"] = "";
$aISODataElement["1993"][81]["type"] = "n";
$aISODataElement["1993"][81]["len"] = 10;
$aISODataElement["1993"][81]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][81]["cpad"] = "0";
$aISODataElement["1993"][81]["sde"] = NULL;

$aISODataElement["1993"][82] = array();
$aISODataElement["1993"][82]["name"] = "INQUIRIES, REVERSAL NUMBER";
$aISODataElement["1993"][82]["format"] = "";
$aISODataElement["1993"][82]["type"] = "n";
$aISODataElement["1993"][82]["len"] = 10;
$aISODataElement["1993"][82]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][82]["cpad"] = "0";
$aISODataElement["1993"][82]["sde"] = NULL;

$aISODataElement["1993"][83] = array();
$aISODataElement["1993"][83]["name"] = "PAYMENTS, NUMBER";
$aISODataElement["1993"][83]["format"] = "";
$aISODataElement["1993"][83]["type"] = "n";
$aISODataElement["1993"][83]["len"] = 10;
$aISODataElement["1993"][83]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][83]["cpad"] = "0";
$aISODataElement["1993"][83]["sde"] = NULL;

$aISODataElement["1993"][84] = array();
$aISODataElement["1993"][84]["name"] = "PAYMENTS, REVERSAL NUMBER";
$aISODataElement["1993"][84]["format"] = "";
$aISODataElement["1993"][84]["type"] = "n";
$aISODataElement["1993"][84]["len"] = 10;
$aISODataElement["1993"][84]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][84]["cpad"] = "0";
$aISODataElement["1993"][84]["sde"] = NULL;

$aISODataElement["1993"][85] = array();
$aISODataElement["1993"][85]["name"] = "FEE COLLECTIONS, NUMBER";
$aISODataElement["1993"][85]["format"] = "";
$aISODataElement["1993"][85]["type"] = "n";
$aISODataElement["1993"][85]["len"] = 10;
$aISODataElement["1993"][85]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][85]["cpad"] = "0";
$aISODataElement["1993"][85]["sde"] = NULL;

$aISODataElement["1993"][86] = array();
$aISODataElement["1993"][86]["name"] = "CREDITS, AMOUNT";
$aISODataElement["1993"][86]["format"] = "";
$aISODataElement["1993"][86]["type"] = "n";
$aISODataElement["1993"][86]["len"] = 16;
$aISODataElement["1993"][86]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][86]["cpad"] = "0";
$aISODataElement["1993"][86]["sde"] = NULL;

$aISODataElement["1993"][87] = array();
$aISODataElement["1993"][87]["name"] = "CREDITS, REVERSAL AMOUNT";
$aISODataElement["1993"][87]["format"] = "";
$aISODataElement["1993"][87]["type"] = "n";
$aISODataElement["1993"][87]["len"] = 16;
$aISODataElement["1993"][87]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][87]["cpad"] = "0";
$aISODataElement["1993"][87]["sde"] = NULL;

$aISODataElement["1993"][88] = array();
$aISODataElement["1993"][88]["name"] = "DEBITS, AMOUNT";
$aISODataElement["1993"][88]["format"] = "";
$aISODataElement["1993"][88]["type"] = "n";
$aISODataElement["1993"][88]["len"] = 16;
$aISODataElement["1993"][88]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][88]["cpad"] = "0";
$aISODataElement["1993"][88]["sde"] = NULL;

$aISODataElement["1993"][89] = array();
$aISODataElement["1993"][89]["name"] = "DEBITS, REVERSAL AMOUNT";
$aISODataElement["1993"][89]["format"] = "";
$aISODataElement["1993"][89]["type"] = "n";
$aISODataElement["1993"][89]["len"] = 16;
$aISODataElement["1993"][89]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][89]["cpad"] = "0";
$aISODataElement["1993"][89]["sde"] = NULL;

$aISODataElement["1993"][90] = array();
$aISODataElement["1993"][90]["name"] = "AUTHORIZATIONS, REVERSAL NUMBER";
$aISODataElement["1993"][90]["format"] = "";
$aISODataElement["1993"][90]["type"] = "n";
$aISODataElement["1993"][90]["len"] = 10;
$aISODataElement["1993"][90]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][90]["cpad"] = "0";
$aISODataElement["1993"][90]["sde"] = NULL;

$aISODataElement["1993"][91] = array();
$aISODataElement["1993"][91]["name"] = "COUNTRY CODE, TRANSACTION DESTINATION INSTITUTION";
$aISODataElement["1993"][91]["format"] = "";
$aISODataElement["1993"][91]["type"] = "n";
$aISODataElement["1993"][91]["len"] = 3;
$aISODataElement["1993"][91]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][91]["cpad"] = "0";
$aISODataElement["1993"][91]["sde"] = NULL;

$aISODataElement["1993"][92] = array();
$aISODataElement["1993"][92]["name"] = "COUNTRY CODE, TRANSACTION ORIGINAL INSTITUTION";
$aISODataElement["1993"][92]["format"] = "";
$aISODataElement["1993"][92]["type"] = "n";
$aISODataElement["1993"][92]["len"] = 3;
$aISODataElement["1993"][92]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][92]["cpad"] = "0";
$aISODataElement["1993"][92]["sde"] = NULL;

$aISODataElement["1993"][93] = array();
$aISODataElement["1993"][93]["name"] = "TRANSACTION DESTINATION INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1993"][93]["format"] = "LLVAR";
$aISODataElement["1993"][93]["type"] = "n";
$aISODataElement["1993"][93]["len"] = 11;
$aISODataElement["1993"][93]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][93]["cpad"] = "0";
$aISODataElement["1993"][93]["sde"] = NULL;

$aISODataElement["1993"][94] = array();
$aISODataElement["1993"][94]["name"] = "TRANSACTION ORIGINAL INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1993"][94]["format"] = "LLVAR";
$aISODataElement["1993"][94]["type"] = "n";
$aISODataElement["1993"][94]["len"] = 11;
$aISODataElement["1993"][94]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][94]["cpad"] = "0";
$aISODataElement["1993"][94]["sde"] = NULL;

$aISODataElement["1993"][95] = array();
$aISODataElement["1993"][95]["name"] = "CARD ISSUER REFERENCE DATA";
$aISODataElement["1993"][95]["format"] = "LLVAR";
$aISODataElement["1993"][95]["type"] = "ans";
$aISODataElement["1993"][95]["len"] = 99;
$aISODataElement["1993"][95]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][95]["cpad"] = " ";
$aISODataElement["1993"][95]["sde"] = NULL;

$aISODataElement["1993"][96] = array();
$aISODataElement["1993"][96]["name"] = "KEY MANAGEMENT DATA";
$aISODataElement["1993"][96]["format"] = "LLLVAR";
$aISODataElement["1993"][96]["type"] = "b";
$aISODataElement["1993"][96]["len"] = 999;
$aISODataElement["1993"][96]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][96]["cpad"] = " ";
$aISODataElement["1993"][96]["sde"] = NULL;

$aISODataElement["1993"][97] = array();
$aISODataElement["1993"][97]["name"] = "AMOUNT, NET RECONCILIATION";
$aISODataElement["1993"][97]["format"] = "";
$aISODataElement["1993"][97]["type"] = "n";
$aISODataElement["1993"][97]["len"] = 17;
$aISODataElement["1993"][97]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][97]["cpad"] = "0";
$aISODataElement["1993"][97]["sde"] = NULL;

$aISODataElement["1993"][98] = array();
$aISODataElement["1993"][98]["name"] = "PAYEE";
$aISODataElement["1993"][98]["format"] = "";
$aISODataElement["1993"][98]["type"] = "ansp";
$aISODataElement["1993"][98]["len"] = 25;
$aISODataElement["1993"][98]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][98]["cpad"] = " ";
$aISODataElement["1993"][98]["sde"] = NULL;

$aISODataElement["1993"][99] = array();
$aISODataElement["1993"][99]["name"] = "SETTLEMENT INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1993"][99]["format"] = "LLVAR";
$aISODataElement["1993"][99]["type"] = "an";
$aISODataElement["1993"][99]["len"] = 11;
$aISODataElement["1993"][99]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][99]["cpad"] = "0";
$aISODataElement["1993"][99]["sde"] = NULL;

$aISODataElement["1993"][100] = array();
$aISODataElement["1993"][100]["name"] = "RECEIVING INSTITUTION IDENTIFICATION CODE";
$aISODataElement["1993"][100]["format"] = "LLVAR";
$aISODataElement["1993"][100]["type"] = "n";
$aISODataElement["1993"][100]["len"] = 11;
$aISODataElement["1993"][100]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][100]["cpad"] = "0";
$aISODataElement["1993"][100]["sde"] = NULL;

$aISODataElement["1993"][101] = array();
$aISODataElement["1993"][101]["name"] = "File Name";
$aISODataElement["1993"][101]["format"] = "LLVAR";
$aISODataElement["1993"][101]["type"] = "ansp";
$aISODataElement["1993"][101]["len"] = 17;
$aISODataElement["1993"][101]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][101]["cpad"] = " ";
$aISODataElement["1993"][101]["sde"] = NULL;

$aISODataElement["1993"][102] = array();
$aISODataElement["1993"][102]["name"] = "ACCOUNT IDENTIFICATION 1";
$aISODataElement["1993"][102]["format"] = "LLVAR";
$aISODataElement["1993"][102]["type"] = "ansp";
$aISODataElement["1993"][102]["len"] = 28;
$aISODataElement["1993"][102]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][102]["cpad"] = " ";
$aISODataElement["1993"][102]["sde"] = NULL;

$aISODataElement["1993"][103] = array();
$aISODataElement["1993"][103]["name"] = "ACCOUNT IDENTIFICATION 2";
$aISODataElement["1993"][103]["format"] = "LLVAR";
$aISODataElement["1993"][103]["type"] = "ansp";
$aISODataElement["1993"][103]["len"] = 28;
$aISODataElement["1993"][103]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][103]["cpad"] = " ";
$aISODataElement["1993"][103]["sde"] = NULL;

$aISODataElement["1993"][104] = array();
$aISODataElement["1993"][104]["name"] = "TRANSACTION DESCRIPTION";
$aISODataElement["1993"][104]["format"] = "LLLVAR";
$aISODataElement["1993"][104]["type"] = "ansp";
$aISODataElement["1993"][104]["len"] = 100;
$aISODataElement["1993"][104]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][104]["cpad"] = " ";
$aISODataElement["1993"][104]["sde"] = NULL;

$aISODataElement["1993"][105] = array();
$aISODataElement["1993"][105]["name"] = "CREDITS, CHARGEBACK AMOUNT";
$aISODataElement["1993"][105]["format"] = "";
$aISODataElement["1993"][105]["type"] = "n";
$aISODataElement["1993"][105]["len"] = 16;
$aISODataElement["1993"][105]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][105]["cpad"] = "0";
$aISODataElement["1993"][105]["sde"] = NULL;

$aISODataElement["1993"][106] = array();
$aISODataElement["1993"][106]["name"] = "DEBITS, CHARGEBACK AMOUNT";
$aISODataElement["1993"][106]["format"] = "";
$aISODataElement["1993"][106]["type"] = "n";
$aISODataElement["1993"][106]["len"] = 16;
$aISODataElement["1993"][106]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][106]["cpad"] = "0";
$aISODataElement["1993"][106]["sde"] = NULL;

$aISODataElement["1993"][107] = array();
$aISODataElement["1993"][107]["name"] = "CREDITS, CHARGEBACK NUMBER";
$aISODataElement["1993"][107]["format"] = "";
$aISODataElement["1993"][107]["type"] = "n";
$aISODataElement["1993"][107]["len"] = 10;
$aISODataElement["1993"][107]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][107]["cpad"] = "0";
$aISODataElement["1993"][107]["sde"] = NULL;

$aISODataElement["1993"][108] = array();
$aISODataElement["1993"][108]["name"] = "DEBITS, CHARGEBACK NUMBER";
$aISODataElement["1993"][108]["format"] = "";
$aISODataElement["1993"][108]["type"] = "n";
$aISODataElement["1993"][108]["len"] = 10;
$aISODataElement["1993"][108]["pad"] = STR_PAD_LEFT;
$aISODataElement["1993"][108]["cpad"] = "0";
$aISODataElement["1993"][108]["sde"] = NULL;

$aISODataElement["1993"][109] = array();
$aISODataElement["1993"][109]["name"] = "CREDITS, FEE AMOUNTS";
$aISODataElement["1993"][109]["format"] = "LLVAR";
$aISODataElement["1993"][109]["type"] = "ans";
$aISODataElement["1993"][109]["len"] = 84;
$aISODataElement["1993"][109]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][109]["cpad"] = " ";
$aISODataElement["1993"][109]["sde"] = NULL;

$aISODataElement["1993"][110] = array();
$aISODataElement["1993"][110]["name"] = "DEBITS, FEE AMOUNTS";
$aISODataElement["1993"][110]["format"] = "LLVAR";
$aISODataElement["1993"][110]["type"] = "ans";
$aISODataElement["1993"][110]["len"] = 84;
$aISODataElement["1993"][110]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][110]["cpad"] = " ";
$aISODataElement["1993"][110]["sde"] = NULL;

$aISODataElement["1993"][111] = array();
$aISODataElement["1993"][111]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1993"][111]["format"] = "LLLVAR";
$aISODataElement["1993"][111]["type"] = "ansp";
$aISODataElement["1993"][111]["len"] = 999;
$aISODataElement["1993"][111]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][111]["cpad"] = " ";
$aISODataElement["1993"][111]["sde"] = NULL;

$aISODataElement["1993"][112] = array();
$aISODataElement["1993"][112]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1993"][112]["format"] = "LLLVAR";
$aISODataElement["1993"][112]["type"] = "ansp";
$aISODataElement["1993"][112]["len"] = 999;
$aISODataElement["1993"][112]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][112]["cpad"] = " ";
$aISODataElement["1993"][112]["sde"] = NULL;

$aISODataElement["1993"][113] = array();
$aISODataElement["1993"][113]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1993"][113]["format"] = "LLLVAR";
$aISODataElement["1993"][113]["type"] = "ansp";
$aISODataElement["1993"][113]["len"] = 999;
$aISODataElement["1993"][113]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][113]["cpad"] = " ";
$aISODataElement["1993"][113]["sde"] = NULL;

$aISODataElement["1993"][114] = array();
$aISODataElement["1993"][114]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1993"][114]["format"] = "LLLVAR";
$aISODataElement["1993"][114]["type"] = "ansp";
$aISODataElement["1993"][114]["len"] = 999;
$aISODataElement["1993"][114]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][114]["cpad"] = " ";
$aISODataElement["1993"][114]["sde"] = NULL;

$aISODataElement["1993"][115] = array();
$aISODataElement["1993"][115]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["1993"][115]["format"] = "LLLVAR";
$aISODataElement["1993"][115]["type"] = "ansp";
$aISODataElement["1993"][115]["len"] = 999;
$aISODataElement["1993"][115]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][115]["cpad"] = " ";
$aISODataElement["1993"][115]["sde"] = NULL;

$aISODataElement["1993"][116] = array();
$aISODataElement["1993"][116]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1993"][116]["format"] = "LLLVAR";
$aISODataElement["1993"][116]["type"] = "ansp";
$aISODataElement["1993"][116]["len"] = 999;
$aISODataElement["1993"][116]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][116]["cpad"] = " ";
$aISODataElement["1993"][116]["sde"] = NULL;

$aISODataElement["1993"][117] = array();
$aISODataElement["1993"][117]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1993"][117]["format"] = "LLLVAR";
$aISODataElement["1993"][117]["type"] = "ansp";
$aISODataElement["1993"][117]["len"] = 999;
$aISODataElement["1993"][117]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][117]["cpad"] = " ";
$aISODataElement["1993"][117]["sde"] = NULL;

$aISODataElement["1993"][118] = array();
$aISODataElement["1993"][118]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1993"][118]["format"] = "LLLVAR";
$aISODataElement["1993"][118]["type"] = "ansp";
$aISODataElement["1993"][118]["len"] = 999;
$aISODataElement["1993"][118]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][118]["cpad"] = " ";
$aISODataElement["1993"][118]["sde"] = NULL;

$aISODataElement["1993"][119] = array();
$aISODataElement["1993"][119]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1993"][119]["format"] = "LLLVAR";
$aISODataElement["1993"][119]["type"] = "ansp";
$aISODataElement["1993"][119]["len"] = 999;
$aISODataElement["1993"][119]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][119]["cpad"] = " ";
$aISODataElement["1993"][119]["sde"] = NULL;

$aISODataElement["1993"][120] = array();
$aISODataElement["1993"][120]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1993"][120]["format"] = "LLLVAR";
$aISODataElement["1993"][120]["type"] = "ansp";
$aISODataElement["1993"][120]["len"] = 999;
$aISODataElement["1993"][120]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][120]["cpad"] = " ";
$aISODataElement["1993"][120]["sde"] = NULL;

$aISODataElement["1993"][121] = array();
$aISODataElement["1993"][121]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1993"][121]["format"] = "LLLVAR";
$aISODataElement["1993"][121]["type"] = "ansp";
$aISODataElement["1993"][121]["len"] = 999;
$aISODataElement["1993"][121]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][121]["cpad"] = " ";
$aISODataElement["1993"][121]["sde"] = NULL;

$aISODataElement["1993"][122] = array();
$aISODataElement["1993"][122]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["1993"][122]["format"] = "LLLVAR";
$aISODataElement["1993"][122]["type"] = "ansp";
$aISODataElement["1993"][122]["len"] = 999;
$aISODataElement["1993"][122]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][122]["cpad"] = " ";
$aISODataElement["1993"][122]["sde"] = NULL;

$aISODataElement["1993"][123] = array();
$aISODataElement["1993"][123]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1993"][123]["format"] = "LLLVAR";
$aISODataElement["1993"][123]["type"] = "ansp";
$aISODataElement["1993"][123]["len"] = 999;
$aISODataElement["1993"][123]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][123]["cpad"] = " ";
$aISODataElement["1993"][123]["sde"] = NULL;

$aISODataElement["1993"][124] = array();
$aISODataElement["1993"][124]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1993"][124]["format"] = "LLLVAR";
$aISODataElement["1993"][124]["type"] = "ansp";
$aISODataElement["1993"][124]["len"] = 999;
$aISODataElement["1993"][124]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][124]["cpad"] = " ";
$aISODataElement["1993"][124]["sde"] = NULL;

$aISODataElement["1993"][125] = array();
$aISODataElement["1993"][125]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1993"][125]["format"] = "LLLVAR";
$aISODataElement["1993"][125]["type"] = "ansp";
$aISODataElement["1993"][125]["len"] = 999;
$aISODataElement["1993"][125]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][125]["cpad"] = " ";
$aISODataElement["1993"][125]["sde"] = NULL;

$aISODataElement["1993"][126] = array();
$aISODataElement["1993"][126]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1993"][126]["format"] = "LLLVAR";
$aISODataElement["1993"][126]["type"] = "ansp";
$aISODataElement["1993"][126]["len"] = 999;
$aISODataElement["1993"][126]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][126]["cpad"] = " ";
$aISODataElement["1993"][126]["sde"] = NULL;

$aISODataElement["1993"][127] = array();
$aISODataElement["1993"][127]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["1993"][127]["format"] = "LLLVAR";
$aISODataElement["1993"][127]["type"] = "ansp";
$aISODataElement["1993"][127]["len"] = 999;
$aISODataElement["1993"][127]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][127]["cpad"] = " ";
$aISODataElement["1993"][127]["sde"] = NULL;

$aISODataElement["1993"][128] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["1993"][128]["name"] = "MAC FIELD";
$aISODataElement["1993"][128]["format"] = "";
$aISODataElement["1993"][128]["type"] = "b";
$aISODataElement["1993"][128]["len"] = 8;
$aISODataElement["1993"][128]["pad"] = STR_PAD_RIGHT;
$aISODataElement["1993"][128]["cpad"] = " ";
$aISODataElement["1993"][128]["sde"] = NULL;

// ISO8583:2003

$aISODataElement["2003"] = array();

$aISODataElement["2003"][0] = array();
$aISODataElement["2003"][0]["name"] = "MESSAGE TYPE IDENTIFIER (MTI)";
$aISODataElement["2003"][0]["format"] = "";
$aISODataElement["2003"][0]["type"] = "n";
$aISODataElement["2003"][0]["len"] = 4;
$aISODataElement["2003"][0]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][0]["cpad"] = "0";
$aISODataElement["2003"][0]["sde"] = NULL;

$aISODataElement["2003"][1] = array();
$aISODataElement["2003"][1]["name"] = "BIT MAP";
$aISODataElement["2003"][1]["format"] = "LLVAR";
$aISODataElement["2003"][1]["type"] = "an";
$aISODataElement["2003"][1]["len"] = 16; // can be extended to secondary (32)
$aISODataElement["2003"][1]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][1]["cpad"] = "0";
$aISODataElement["2003"][1]["sde"] = NULL;

$aISODataElement["2003"][2] = array();
$aISODataElement["2003"][2]["name"] = "PRIMARY ACCOUNT NUMBER (PAN)";
$aISODataElement["2003"][2]["format"] = "LLVAR";
$aISODataElement["2003"][2]["type"] = "n";
$aISODataElement["2003"][2]["len"] = 19;
$aISODataElement["2003"][2]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][2]["cpad"] = " ";
$aISODataElement["2003"][2]["sde"] = NULL;

$aISODataElement["2003"][3] = array();
$aISODataElement["2003"][3]["name"] = "PROCESSING CODE";
$aISODataElement["2003"][3]["format"] = "";
$aISODataElement["2003"][3]["type"] = "an";
$aISODataElement["2003"][3]["len"] = 6;
$aISODataElement["2003"][3]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][3]["cpad"] = "0";
$aISODataElement["2003"][3]["sde"] = array();
$aISODataElement["2003"][3]["sde"][0] = array();
$aISODataElement["2003"][3]["sde"][0]["name"] = "TRANSACTION TYPE CODE";
$aISODataElement["2003"][3]["sde"][0]["format"] = "";
$aISODataElement["2003"][3]["sde"][0]["type"] = "an";
$aISODataElement["2003"][3]["sde"][0]["len"] = 2;
$aISODataElement["2003"][3]["sde"][1] = array();
$aISODataElement["2003"][3]["sde"][1]["name"] = "ACCOUNT TYPE CODE 1";
$aISODataElement["2003"][3]["sde"][1]["format"] = "";
$aISODataElement["2003"][3]["sde"][1]["type"] = "an";
$aISODataElement["2003"][3]["sde"][1]["len"] = 2;
$aISODataElement["2003"][3]["sde"][2] = array();
$aISODataElement["2003"][3]["sde"][2]["name"] = "ACCOUNT TYPE CODE 2";
$aISODataElement["2003"][3]["sde"][2]["format"] = "";
$aISODataElement["2003"][3]["sde"][2]["type"] = "an";
$aISODataElement["2003"][3]["sde"][2]["len"] = 2;

$aISODataElement["2003"][4] = array();
$aISODataElement["2003"][4]["name"] = "AMOUNT TRANSACTION";
$aISODataElement["2003"][4]["format"] = "";
$aISODataElement["2003"][4]["type"] = "n";
$aISODataElement["2003"][4]["len"] = 16;
$aISODataElement["2003"][4]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][4]["cpad"] = "0";
$aISODataElement["2003"][4]["sde"] = array();
$aISODataElement["2003"][4]["sde"][0] = array();
$aISODataElement["2003"][4]["sde"][0]["name"] = "CURRENCY CODE AMOUNT TRANSACTION";
$aISODataElement["2003"][4]["sde"][0]["format"] = "";
$aISODataElement["2003"][4]["sde"][0]["type"] = "n";
$aISODataElement["2003"][4]["sde"][0]["len"] = 3;
$aISODataElement["2003"][4]["sde"][1] = array();
$aISODataElement["2003"][4]["sde"][1]["name"] = "CURRENCY MINOR UNIT AMOUNT TRANSACTION";
$aISODataElement["2003"][4]["sde"][1]["format"] = "";
$aISODataElement["2003"][4]["sde"][1]["type"] = "n";
$aISODataElement["2003"][4]["sde"][1]["len"] = 1;
$aISODataElement["2003"][4]["sde"][2] = array();
$aISODataElement["2003"][4]["sde"][2]["name"] = "VALUE AMOUNT TRANSACTION";
$aISODataElement["2003"][4]["sde"][2]["format"] = "";
$aISODataElement["2003"][4]["sde"][2]["type"] = "n";
$aISODataElement["2003"][4]["sde"][2]["len"] = 12;

$aISODataElement["2003"][5] = array();
$aISODataElement["2003"][5]["name"] = "AMOUNT RECONCILIATION";
$aISODataElement["2003"][5]["format"] = "";
$aISODataElement["2003"][5]["type"] = "n";
$aISODataElement["2003"][5]["len"] = 16;
$aISODataElement["2003"][5]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][5]["cpad"] = "0";
$aISODataElement["2003"][5]["sde"] = array();
$aISODataElement["2003"][5]["sde"][0] = array();
$aISODataElement["2003"][5]["sde"][0]["name"] = "CURRENCY CODE AMOUNT RECONCILIATION";
$aISODataElement["2003"][5]["sde"][0]["format"] = "";
$aISODataElement["2003"][5]["sde"][0]["type"] = "n";
$aISODataElement["2003"][5]["sde"][0]["len"] = 3;
$aISODataElement["2003"][5]["sde"][1] = array();
$aISODataElement["2003"][5]["sde"][1]["name"] = "CURRENCY MINOR UNIT AMOUNT RECONCILIATION";
$aISODataElement["2003"][5]["sde"][1]["format"] = "";
$aISODataElement["2003"][5]["sde"][1]["type"] = "n";
$aISODataElement["2003"][5]["sde"][1]["len"] = 1;
$aISODataElement["2003"][5]["sde"][2] = array();
$aISODataElement["2003"][5]["sde"][2]["name"] = "VALUE AMOUNT RECONCILIATION";
$aISODataElement["2003"][5]["sde"][2]["format"] = "";
$aISODataElement["2003"][5]["sde"][2]["type"] = "n";
$aISODataElement["2003"][5]["sde"][2]["len"] = 12;

$aISODataElement["2003"][6] = array();
$aISODataElement["2003"][6]["name"] = "AMOUNT CARDHOLDER BILLING";
$aISODataElement["2003"][6]["format"] = "";
$aISODataElement["2003"][6]["type"] = "n";
$aISODataElement["2003"][6]["len"] = 16;
$aISODataElement["2003"][6]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][6]["cpad"] = "0";
$aISODataElement["2003"][6]["sde"] = array();
$aISODataElement["2003"][6]["sde"][0] = array();
$aISODataElement["2003"][6]["sde"][0]["name"] = "CURRENCY CODE AMOUNT CARDHOLDER BILLING";
$aISODataElement["2003"][6]["sde"][0]["format"] = "";
$aISODataElement["2003"][6]["sde"][0]["type"] = "n";
$aISODataElement["2003"][6]["sde"][0]["len"] = 3;
$aISODataElement["2003"][6]["sde"][1] = array();
$aISODataElement["2003"][6]["sde"][1]["name"] = "CURRENCY MINOR UNIT AMOUNT CARDHOLDER BILLING";
$aISODataElement["2003"][6]["sde"][1]["format"] = "";
$aISODataElement["2003"][6]["sde"][1]["type"] = "n";
$aISODataElement["2003"][6]["sde"][1]["len"] = 1;
$aISODataElement["2003"][6]["sde"][2] = array();
$aISODataElement["2003"][6]["sde"][2]["name"] = "VALUE AMOUNT CARDHOLDER BILLING";
$aISODataElement["2003"][6]["sde"][2]["format"] = "";
$aISODataElement["2003"][6]["sde"][2]["type"] = "n";
$aISODataElement["2003"][6]["sde"][2]["len"] = 12;

$aISODataElement["2003"][7] = array();
$aISODataElement["2003"][7]["name"] = "DATE AND TIME TRANSMISSION";
$aISODataElement["2003"][7]["format"] = "MMDDhhmmss";
$aISODataElement["2003"][7]["type"] = "n";
$aISODataElement["2003"][7]["len"] = 10;
$aISODataElement["2003"][7]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][7]["cpad"] = "0";
$aISODataElement["2003"][7]["sde"] = NULL;

$aISODataElement["2003"][8] = array();
$aISODataElement["2003"][8]["name"] = "AMOUNT CARDHOLDER BILLING FEE";
$aISODataElement["2003"][8]["format"] = "";
$aISODataElement["2003"][8]["type"] = "n";
$aISODataElement["2003"][8]["len"] = 12;
$aISODataElement["2003"][8]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][8]["cpad"] = "0";
$aISODataElement["2003"][8]["sde"] = array();
$aISODataElement["2003"][8]["sde"][0] = array();
$aISODataElement["2003"][8]["sde"][0]["name"] = "CURRENCY CODE AMOUNT CARDHOLDER BILLING FEE";
$aISODataElement["2003"][8]["sde"][0]["format"] = "";
$aISODataElement["2003"][8]["sde"][0]["type"] = "n";
$aISODataElement["2003"][8]["sde"][0]["len"] = 3;
$aISODataElement["2003"][8]["sde"][1] = array();
$aISODataElement["2003"][8]["sde"][1]["name"] = "CURRENCY MINOR UNIT AMOUNT CARDHOLDER BILLING FEE";
$aISODataElement["2003"][8]["sde"][1]["format"] = "";
$aISODataElement["2003"][8]["sde"][1]["type"] = "n";
$aISODataElement["2003"][8]["sde"][1]["len"] = 1;
$aISODataElement["2003"][8]["sde"][2] = array();
$aISODataElement["2003"][8]["sde"][2]["name"] = "VALUE AMOUNT CARDHOLDER BILLING FEE";
$aISODataElement["2003"][8]["sde"][2]["format"] = "";
$aISODataElement["2003"][8]["sde"][2]["type"] = "n";
$aISODataElement["2003"][8]["sde"][2]["len"] = 8;

$aISODataElement["2003"][9] = array();
$aISODataElement["2003"][9]["name"] = "CONVERSION RATE RECONCILIATION";
$aISODataElement["2003"][9]["format"] = "";
$aISODataElement["2003"][9]["type"] = "n";
$aISODataElement["2003"][9]["len"] = 8;
$aISODataElement["2003"][9]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][9]["cpad"] = "0";
$aISODataElement["2003"][9]["sde"] = NULL;

$aISODataElement["2003"][10] = array();
$aISODataElement["2003"][10]["name"] = "CONVERSION RATE CARDHOLDER BILLING";
$aISODataElement["2003"][10]["format"] = "";
$aISODataElement["2003"][10]["type"] = "n";
$aISODataElement["2003"][10]["len"] = 8;
$aISODataElement["2003"][10]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][10]["cpad"] = "0";
$aISODataElement["2003"][10]["sde"] = NULL;

$aISODataElement["2003"][11] = array();
$aISODataElement["2003"][11]["name"] = "SYSTEM TRACE AUDIT NUMBER";
$aISODataElement["2003"][11]["format"] = "";
$aISODataElement["2003"][11]["type"] = "n";
$aISODataElement["2003"][11]["len"] = 12;
$aISODataElement["2003"][11]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][11]["cpad"] = "0";
$aISODataElement["2003"][11]["sde"] = NULL;

$aISODataElement["2003"][12] = array();
$aISODataElement["2003"][12]["name"] = "DATE AND TIME LOCAL TRANSACTION";
$aISODataElement["2003"][12]["format"] = "CCYYMMDDhhmmss";
$aISODataElement["2003"][12]["type"] = "n";
$aISODataElement["2003"][12]["len"] = 14;
$aISODataElement["2003"][12]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][12]["cpad"] = "0";
$aISODataElement["2003"][12]["sde"] = array();
$aISODataElement["2003"][12]["sde"][0] = array();
$aISODataElement["2003"][12]["sde"][0]["name"] = "DATE LOCAL TRANSACTION";
$aISODataElement["2003"][12]["sde"][0]["format"] = "CCYYMMDD";
$aISODataElement["2003"][12]["sde"][0]["type"] = "n";
$aISODataElement["2003"][12]["sde"][0]["len"] = 8;
$aISODataElement["2003"][12]["sde"][1] = array();
$aISODataElement["2003"][12]["sde"][1]["name"] = "TIME LOCAL TRANSACTION";
$aISODataElement["2003"][12]["sde"][1]["format"] = "hhmmss";
$aISODataElement["2003"][12]["sde"][1]["type"] = "n";
$aISODataElement["2003"][12]["sde"][1]["len"] = 6;

$aISODataElement["2003"][13] = array();
$aISODataElement["2003"][13]["name"] = "DATE EFFECTIVE";
$aISODataElement["2003"][13]["format"] = "CCYYMM";
$aISODataElement["2003"][13]["type"] = "n";
$aISODataElement["2003"][13]["len"] = 6;
$aISODataElement["2003"][13]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][13]["cpad"] = "0";
$aISODataElement["2003"][13]["sde"] = NULL;

$aISODataElement["2003"][14] = array();
$aISODataElement["2003"][14]["name"] = "DATE EXPIRATION";
$aISODataElement["2003"][14]["format"] = "YYMM";
$aISODataElement["2003"][14]["type"] = "n";
$aISODataElement["2003"][14]["len"] = 4;
$aISODataElement["2003"][14]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][14]["cpad"] = "0";
$aISODataElement["2003"][14]["sde"] = NULL;

$aISODataElement["2003"][15] = array();
$aISODataElement["2003"][15]["name"] = "DATE SETTLEMENT";
$aISODataElement["2003"][15]["format"] = "CCYYMMDD";
$aISODataElement["2003"][15]["type"] = "n";
$aISODataElement["2003"][15]["len"] = 8;
$aISODataElement["2003"][15]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][15]["cpad"] = "0";
$aISODataElement["2003"][15]["sde"] = NULL;

$aISODataElement["2003"][16] = array();
$aISODataElement["2003"][16]["name"] = "DATE CONVERSION";
$aISODataElement["2003"][16]["format"] = "MMDD";
$aISODataElement["2003"][16]["type"] = "n";
$aISODataElement["2003"][16]["len"] = 4;
$aISODataElement["2003"][16]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][16]["cpad"] = "0";
$aISODataElement["2003"][16]["sde"] = NULL;

$aISODataElement["2003"][17] = array();
$aISODataElement["2003"][17]["name"] = "DATE CAPTURE";
$aISODataElement["2003"][17]["format"] = "MMDD";
$aISODataElement["2003"][17]["type"] = "n";
$aISODataElement["2003"][17]["len"] = 4;
$aISODataElement["2003"][17]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][17]["cpad"] = "0";
$aISODataElement["2003"][17]["sde"] = NULL;

$aISODataElement["2003"][18] = array();
$aISODataElement["2003"][18]["name"] = "MESSAGE ERROR INDICATOR";
$aISODataElement["2003"][18]["format"] = "LLLVAR";
$aISODataElement["2003"][18]["type"] = "ansb";
$aISODataElement["2003"][18]["len"] = 140;
$aISODataElement["2003"][18]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][18]["cpad"] = "0";
$aISODataElement["2003"][18]["sde"] = array();
$aISODataElement["2003"][18]["sde"][0] = array();
$aISODataElement["2003"][18]["sde"][0]["name"] = "ERROR SEVERITY CODE";
$aISODataElement["2003"][18]["sde"][0]["format"] = "";
$aISODataElement["2003"][18]["sde"][0]["type"] = "n";
$aISODataElement["2003"][18]["sde"][0]["len"] = 2;
$aISODataElement["2003"][18]["sde"][1] = array();
$aISODataElement["2003"][18]["sde"][1]["name"] = "MESSAGE ERROR CODE";
$aISODataElement["2003"][18]["sde"][1]["format"] = "";
$aISODataElement["2003"][18]["sde"][1]["type"] = "n";
$aISODataElement["2003"][18]["sde"][1]["len"] = 4;
$aISODataElement["2003"][18]["sde"][2] = array();
$aISODataElement["2003"][18]["sde"][2]["name"] = "DATA ELEMENT IN ERROR";
$aISODataElement["2003"][18]["sde"][2]["format"] = "";
$aISODataElement["2003"][18]["sde"][2]["type"] = "n";
$aISODataElement["2003"][18]["sde"][2]["len"] = 3;
$aISODataElement["2003"][18]["sde"][3] = array();
$aISODataElement["2003"][18]["sde"][3]["name"] = "DATA SUB-ELEMENT IN ERROR";
$aISODataElement["2003"][18]["sde"][3]["format"] = "";
$aISODataElement["2003"][18]["sde"][3]["type"] = "n";
$aISODataElement["2003"][18]["sde"][3]["len"] = 2;
$aISODataElement["2003"][18]["sde"][4] = array();
$aISODataElement["2003"][18]["sde"][4]["name"] = "DATASET IDENTIFIER IN ERROR";
$aISODataElement["2003"][18]["sde"][4]["format"] = "";
$aISODataElement["2003"][18]["sde"][4]["type"] = "b";
$aISODataElement["2003"][18]["sde"][4]["len"] = 1;
$aISODataElement["2003"][18]["sde"][5] = array();
$aISODataElement["2003"][18]["sde"][5]["name"] = "DATASET BIT OR TAG IN ERROR";
$aISODataElement["2003"][18]["sde"][5]["format"] = "";
$aISODataElement["2003"][18]["sde"][5]["type"] = "b";
$aISODataElement["2003"][18]["sde"][5]["len"] = 2;

$aISODataElement["2003"][19] = array();
$aISODataElement["2003"][19]["name"] = "COUNTRY CODE ACQUIRING INSTITUTION";
$aISODataElement["2003"][19]["format"] = "";
$aISODataElement["2003"][19]["type"] = "n";
$aISODataElement["2003"][19]["len"] = 3;
$aISODataElement["2003"][19]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][19]["cpad"] = "0";
$aISODataElement["2003"][19]["sde"] = NULL;

$aISODataElement["2003"][20] = array();
$aISODataElement["2003"][20]["name"] = "COUNTRY CODE PRIMARY ACCOUNT NUMBER (PAN)";
$aISODataElement["2003"][20]["format"] = "";
$aISODataElement["2003"][20]["type"] = "n";
$aISODataElement["2003"][20]["len"] = 3;
$aISODataElement["2003"][20]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][20]["cpad"] = "0";
$aISODataElement["2003"][20]["sde"] = NULL;

$aISODataElement["2003"][21] = array();
$aISODataElement["2003"][21]["name"] = "TRANSACTION LIFE CYCLE IDENTIFICATION DATA";
$aISODataElement["2003"][21]["format"] = "";
$aISODataElement["2003"][21]["type"] = "ans";
$aISODataElement["2003"][21]["len"] = 22;
$aISODataElement["2003"][21]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][21]["cpad"] = " ";
$aISODataElement["2003"][21]["sde"] = array();
$aISODataElement["2003"][21]["sde"][0] = array();
$aISODataElement["2003"][21]["sde"][0]["name"] = "LIFE CYCLE SUPPORT INDICATOR";
$aISODataElement["2003"][21]["sde"][0]["format"] = "";
$aISODataElement["2003"][21]["sde"][0]["type"] = "ans";
$aISODataElement["2003"][21]["sde"][0]["len"] = 1;
$aISODataElement["2003"][21]["sde"][1] = array();
$aISODataElement["2003"][21]["sde"][1]["name"] = "LIFE CYCLE TRACE IDENTIFIER";
$aISODataElement["2003"][21]["sde"][1]["format"] = "";
$aISODataElement["2003"][21]["sde"][1]["type"] = "ans";
$aISODataElement["2003"][21]["sde"][1]["len"] = 15;
$aISODataElement["2003"][21]["sde"][2] = array();
$aISODataElement["2003"][21]["sde"][2]["name"] = "LIFE CYCLE TRANSACTION SEQUENCE NUMBER";
$aISODataElement["2003"][21]["sde"][2]["format"] = "";
$aISODataElement["2003"][21]["sde"][2]["type"] = "n";
$aISODataElement["2003"][21]["sde"][2]["len"] = 2;
$aISODataElement["2003"][21]["sde"][3] = array();
$aISODataElement["2003"][21]["sde"][3]["name"] = "LIFE CYCLE AUTHENTICATION TOKEN";
$aISODataElement["2003"][21]["sde"][3]["format"] = "";
$aISODataElement["2003"][21]["sde"][3]["type"] = "n";
$aISODataElement["2003"][21]["sde"][3]["len"] = 4;

$aISODataElement["2003"][22] = array();
$aISODataElement["2003"][22]["name"] = "POS DATA CODE";
$aISODataElement["2003"][22]["format"] = "";
$aISODataElement["2003"][22]["type"] = "b";
$aISODataElement["2003"][22]["len"] = 16;
$aISODataElement["2003"][22]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][22]["cpad"] = " ";
$aISODataElement["2003"][22]["sde"] = array();
$aISODataElement["2003"][22]["sde"][0] = array();
$aISODataElement["2003"][22]["sde"][0]["name"] = "CARD READING METHOD USED AT POS";
$aISODataElement["2003"][22]["sde"][0]["format"] = "";
$aISODataElement["2003"][22]["sde"][0]["type"] = "b";
$aISODataElement["2003"][22]["sde"][0]["len"] = 4;
$aISODataElement["2003"][22]["sde"][1] = array();
$aISODataElement["2003"][22]["sde"][1]["name"] = "CARD HOLDER VERIFICATION METHOD USED AT POS";
$aISODataElement["2003"][22]["sde"][1]["format"] = "";
$aISODataElement["2003"][22]["sde"][1]["type"] = "b";
$aISODataElement["2003"][22]["sde"][1]["len"] = 4;
$aISODataElement["2003"][22]["sde"][2] = array();
$aISODataElement["2003"][22]["sde"][2]["name"] = "POS ENVIRONMENT";
$aISODataElement["2003"][22]["sde"][2]["format"] = "";
$aISODataElement["2003"][22]["sde"][2]["type"] = "b";
$aISODataElement["2003"][22]["sde"][2]["len"] = 4;
$aISODataElement["2003"][22]["sde"][3] = array();
$aISODataElement["2003"][22]["sde"][3]["name"] = "SECURITY CHARACTERISTICS";
$aISODataElement["2003"][22]["sde"][3]["format"] = "";
$aISODataElement["2003"][22]["sde"][3]["type"] = "b";
$aISODataElement["2003"][22]["sde"][3]["len"] = 4;

$aISODataElement["2003"][23] = array();
$aISODataElement["2003"][23]["name"] = "CARD SEQUENCE NUMBER";
$aISODataElement["2003"][23]["format"] = "";
$aISODataElement["2003"][23]["type"] = "n";
$aISODataElement["2003"][23]["len"] = 3;
$aISODataElement["2003"][23]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][23]["cpad"] = "0";
$aISODataElement["2003"][23]["sde"] = NULL;

$aISODataElement["2003"][24] = array();
$aISODataElement["2003"][24]["name"] = "FUNCTION CODE";
$aISODataElement["2003"][24]["format"] = "";
$aISODataElement["2003"][24]["type"] = "n";
$aISODataElement["2003"][24]["len"] = 3;
$aISODataElement["2003"][24]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][24]["cpad"] = "0";
$aISODataElement["2003"][24]["sde"] = NULL;

$aISODataElement["2003"][25] = array();
$aISODataElement["2003"][25]["name"] = "MESSAGE REASON CODE";
$aISODataElement["2003"][25]["format"] = "";
$aISODataElement["2003"][25]["type"] = "n";
$aISODataElement["2003"][25]["len"] = 4;
$aISODataElement["2003"][25]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][25]["cpad"] = "0";
$aISODataElement["2003"][25]["sde"] = NULL;

$aISODataElement["2003"][26] = array();
$aISODataElement["2003"][26]["name"] = "MERCHANT CATEGORY CODE";
$aISODataElement["2003"][26]["format"] = "";
$aISODataElement["2003"][26]["type"] = "n";
$aISODataElement["2003"][26]["len"] = 4;
$aISODataElement["2003"][26]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][26]["cpad"] = "0";
$aISODataElement["2003"][26]["sde"] = NULL;

$aISODataElement["2003"][27] = array();
$aISODataElement["2003"][27]["name"] = "POS CAPABILITY";
$aISODataElement["2003"][27]["format"] = "LLLVAR";
$aISODataElement["2003"][27]["type"] = "ansb";
$aISODataElement["2003"][27]["len"] = 140;
$aISODataElement["2003"][27]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][27]["cpad"] = " ";
$aISODataElement["2003"][27]["sde"] = array();
$aISODataElement["2003"][27]["sde"][0] = array();
$aISODataElement["2003"][27]["sde"][0]["name"] = "POS CARD READING CAPABILITY";
$aISODataElement["2003"][27]["sde"][0]["format"] = "";
$aISODataElement["2003"][27]["sde"][0]["type"] = "b";
$aISODataElement["2003"][27]["sde"][0]["len"] = 4;
$aISODataElement["2003"][27]["sde"][1] = array();
$aISODataElement["2003"][27]["sde"][1]["name"] = "POS CARDHOLDER READING CAPABILITY";
$aISODataElement["2003"][27]["sde"][1]["format"] = "";
$aISODataElement["2003"][27]["sde"][1]["type"] = "b";
$aISODataElement["2003"][27]["sde"][1]["len"] = 4;
$aISODataElement["2003"][27]["sde"][2] = array();
$aISODataElement["2003"][27]["sde"][2]["name"] = "APPROVAL CODE LENGTH";
$aISODataElement["2003"][27]["sde"][2]["format"] = "";
$aISODataElement["2003"][27]["sde"][2]["type"] = "n";
$aISODataElement["2003"][27]["sde"][2]["len"] = 1;
$aISODataElement["2003"][27]["sde"][3] = array();
$aISODataElement["2003"][27]["sde"][3]["name"] = "CARDHOLDER RECEIPT DATA LENGTH";
$aISODataElement["2003"][27]["sde"][3]["format"] = "";
$aISODataElement["2003"][27]["sde"][3]["type"] = "n";
$aISODataElement["2003"][27]["sde"][3]["len"] = 3;
$aISODataElement["2003"][27]["sde"][4] = array();
$aISODataElement["2003"][27]["sde"][4]["name"] = "CARD ACCEPTOR RECEIPT DATA LENGTH";
$aISODataElement["2003"][27]["sde"][4]["format"] = "";
$aISODataElement["2003"][27]["sde"][4]["type"] = "n";
$aISODataElement["2003"][27]["sde"][4]["len"] = 3;
$aISODataElement["2003"][27]["sde"][5] = array();
$aISODataElement["2003"][27]["sde"][5]["name"] = "CARDHOLDER DISPLAY DATA LENGTH";
$aISODataElement["2003"][27]["sde"][5]["format"] = "";
$aISODataElement["2003"][27]["sde"][5]["type"] = "n";
$aISODataElement["2003"][27]["sde"][5]["len"] = 3;
$aISODataElement["2003"][27]["sde"][6] = array();
$aISODataElement["2003"][27]["sde"][6]["name"] = "CARD ACCEPTOR DISPLAY DATA LENGTH";
$aISODataElement["2003"][27]["sde"][6]["format"] = "";
$aISODataElement["2003"][27]["sde"][6]["type"] = "n";
$aISODataElement["2003"][27]["sde"][6]["len"] = 3;
$aISODataElement["2003"][27]["sde"][7] = array();
$aISODataElement["2003"][27]["sde"][7]["name"] = "ICC SCRIPTS DATA LENGTH";
$aISODataElement["2003"][27]["sde"][7]["format"] = "";
$aISODataElement["2003"][27]["sde"][7]["type"] = "n";
$aISODataElement["2003"][27]["sde"][7]["len"] = 3;
$aISODataElement["2003"][27]["sde"][8] = array();
$aISODataElement["2003"][27]["sde"][8]["name"] = "MAGNETIC STRIPE TRACK 3 REWRITE CAPABILITY";
$aISODataElement["2003"][27]["sde"][8]["format"] = "";
$aISODataElement["2003"][27]["sde"][8]["type"] = "a";
$aISODataElement["2003"][27]["sde"][8]["len"] = 1;
$aISODataElement["2003"][27]["sde"][9] = array();
$aISODataElement["2003"][27]["sde"][9]["name"] = "CARD CAPTURE CAPABILITY";
$aISODataElement["2003"][27]["sde"][9]["format"] = "";
$aISODataElement["2003"][27]["sde"][9]["type"] = "a";
$aISODataElement["2003"][27]["sde"][9]["len"] = 1;
$aISODataElement["2003"][27]["sde"][10] = array();
$aISODataElement["2003"][27]["sde"][10]["name"] = "PIN INPUT LENGTH CAPABILITY";
$aISODataElement["2003"][27]["sde"][10]["format"] = "";
$aISODataElement["2003"][27]["sde"][10]["type"] = "b";
$aISODataElement["2003"][27]["sde"][10]["len"] = 1;

$aISODataElement["2003"][28] = array();
$aISODataElement["2003"][28]["name"] = "DATE RECONCILIATION";
$aISODataElement["2003"][28]["format"] = "CCYYMMDD";
$aISODataElement["2003"][28]["type"] = "n";
$aISODataElement["2003"][28]["len"] = 8;
$aISODataElement["2003"][28]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][28]["cpad"] = "0";
$aISODataElement["2003"][28]["sde"] = NULL;

$aISODataElement["2003"][29] = array();
$aISODataElement["2003"][29]["name"] = "RECONCILIATION INDICATOR";
$aISODataElement["2003"][29]["format"] = "";
$aISODataElement["2003"][29]["type"] = "n";
$aISODataElement["2003"][29]["len"] = 3;
$aISODataElement["2003"][29]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][29]["cpad"] = "0";
$aISODataElement["2003"][29]["sde"] = NULL;

$aISODataElement["2003"][30] = array();
$aISODataElement["2003"][30]["name"] = "AMOUNTS ORIGINAL";
$aISODataElement["2003"][30]["format"] = "";
$aISODataElement["2003"][30]["type"] = "n";
$aISODataElement["2003"][30]["len"] = 32;
$aISODataElement["2003"][30]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][30]["cpad"] = "0";
$aISODataElement["2003"][30]["sde"] = array();
$aISODataElement["2003"][30]["sde"][0] = array();
$aISODataElement["2003"][30]["sde"][0]["name"] = "ORIGINAL AMOUNT TRANSACTION";
$aISODataElement["2003"][30]["sde"][0]["format"] = "";
$aISODataElement["2003"][30]["sde"][0]["type"] = "n";
$aISODataElement["2003"][30]["sde"][0]["len"] = 16;
$aISODataElement["2003"][30]["sde"][1] = array();
$aISODataElement["2003"][30]["sde"][1]["name"] = "CURRENCY CODE ORIGINAL AMOUNT TRANSACTION";
$aISODataElement["2003"][30]["sde"][1]["format"] = "";
$aISODataElement["2003"][30]["sde"][1]["type"] = "n";
$aISODataElement["2003"][30]["sde"][1]["len"] = 3;
$aISODataElement["2003"][30]["sde"][2] = array();
$aISODataElement["2003"][30]["sde"][2]["name"] = "CURRENCY MINOR UNIT ORIGINAL AMOUNT TRANSACTION";
$aISODataElement["2003"][30]["sde"][2]["format"] = "";
$aISODataElement["2003"][30]["sde"][2]["type"] = "n";
$aISODataElement["2003"][30]["sde"][2]["len"] = 1;
$aISODataElement["2003"][30]["sde"][3] = array();
$aISODataElement["2003"][30]["sde"][3]["name"] = "VALUE ORIGINAL AMOUNT TRANSACTION";
$aISODataElement["2003"][30]["sde"][3]["format"] = "";
$aISODataElement["2003"][30]["sde"][3]["type"] = "n";
$aISODataElement["2003"][30]["sde"][3]["len"] = 12;
$aISODataElement["2003"][30]["sde"][4] = array();
$aISODataElement["2003"][30]["sde"][4]["name"] = "ORIGINAL AMOUNT RECONCILIATION";
$aISODataElement["2003"][30]["sde"][4]["format"] = "";
$aISODataElement["2003"][30]["sde"][4]["type"] = "n";
$aISODataElement["2003"][30]["sde"][4]["len"] = 16;
$aISODataElement["2003"][30]["sde"][5] = array();
$aISODataElement["2003"][30]["sde"][5]["name"] = "CURRENCY CODE ORIGINAL AMOUNT RECONCILIATION";
$aISODataElement["2003"][30]["sde"][5]["format"] = "";
$aISODataElement["2003"][30]["sde"][5]["type"] = "n";
$aISODataElement["2003"][30]["sde"][5]["len"] = 3;
$aISODataElement["2003"][30]["sde"][6] = array();
$aISODataElement["2003"][30]["sde"][6]["name"] = "CURRENCY MINOR UNIT ORIGINAL AMOUNT RECONCILIATION";
$aISODataElement["2003"][30]["sde"][6]["format"] = "";
$aISODataElement["2003"][30]["sde"][6]["type"] = "n";
$aISODataElement["2003"][30]["sde"][6]["len"] = 1;
$aISODataElement["2003"][30]["sde"][7] = array();
$aISODataElement["2003"][30]["sde"][7]["name"] = "VALUE ORIGINAL AMOUNT RECONCILIATION";
$aISODataElement["2003"][30]["sde"][7]["format"] = "";
$aISODataElement["2003"][30]["sde"][7]["type"] = "n";
$aISODataElement["2003"][30]["sde"][7]["len"] = 16;

$aISODataElement["2003"][31] = array();
$aISODataElement["2003"][31]["name"] = "ACQUIRER REFERENCE NUMBER";
$aISODataElement["2003"][31]["format"] = "";
$aISODataElement["2003"][31]["type"] = "n";
$aISODataElement["2003"][31]["len"] = 23;
$aISODataElement["2003"][31]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][31]["cpad"] = "0";
$aISODataElement["2003"][31]["sde"] = array();
$aISODataElement["2003"][31]["sde"][0] = array();
$aISODataElement["2003"][31]["sde"][0]["name"] = "USER FORMAT IDENTIFIER";
$aISODataElement["2003"][31]["sde"][0]["format"] = "";
$aISODataElement["2003"][31]["sde"][0]["type"] = "n";
$aISODataElement["2003"][31]["sde"][0]["len"] = 1;
$aISODataElement["2003"][31]["sde"][1] = array();
$aISODataElement["2003"][31]["sde"][1]["name"] = "ACQUIRER NUMBER";
$aISODataElement["2003"][31]["sde"][1]["format"] = "";
$aISODataElement["2003"][31]["sde"][1]["type"] = "n";
$aISODataElement["2003"][31]["sde"][1]["len"] = 6;
$aISODataElement["2003"][31]["sde"][2] = array();
$aISODataElement["2003"][31]["sde"][2]["name"] = "JULIAN PROCESSING DATE";
$aISODataElement["2003"][31]["sde"][2]["format"] = "YDDD";
$aISODataElement["2003"][31]["sde"][2]["type"] = "n";
$aISODataElement["2003"][31]["sde"][2]["len"] = 4;
$aISODataElement["2003"][31]["sde"][3] = array();
$aISODataElement["2003"][31]["sde"][3]["name"] = "SEQUENCE NUMBER";
$aISODataElement["2003"][31]["sde"][3]["format"] = "";
$aISODataElement["2003"][31]["sde"][3]["type"] = "n";
$aISODataElement["2003"][31]["sde"][3]["len"] = 11;
$aISODataElement["2003"][31]["sde"][4] = array();
$aISODataElement["2003"][31]["sde"][4]["name"] = "LUHN CHECK DIGIT";
$aISODataElement["2003"][31]["sde"][4]["format"] = "";
$aISODataElement["2003"][31]["sde"][4]["type"] = "n";
$aISODataElement["2003"][31]["sde"][4]["len"] = 1;

$aISODataElement["2003"][32] = array();
$aISODataElement["2003"][32]["name"] = "ACQUIRING INSTITUTION IDENTIFICATION CODE";
$aISODataElement["2003"][32]["format"] = "LLVAR";
$aISODataElement["2003"][32]["type"] = "n";
$aISODataElement["2003"][32]["len"] = 11;
$aISODataElement["2003"][32]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][32]["cpad"] = "0";
$aISODataElement["2003"][32]["sde"] = NULL;

$aISODataElement["2003"][33] = array();
$aISODataElement["2003"][33]["name"] = "FORWARDING INSTITUTION IDENTIFICATION CODE";
$aISODataElement["2003"][33]["format"] = "LLVAR";
$aISODataElement["2003"][33]["type"] = "n";
$aISODataElement["2003"][33]["len"] = 11;
$aISODataElement["2003"][33]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][33]["cpad"] = "0";
$aISODataElement["2003"][33]["sde"] = NULL;

$aISODataElement["2003"][34] = array();
$aISODataElement["2003"][34]["name"] = "ELECTRONIC COMMERCE DATA";
$aISODataElement["2003"][34]["format"] = "LLLLVAR";
$aISODataElement["2003"][34]["type"] = "b";
$aISODataElement["2003"][34]["len"] = 9999;
$aISODataElement["2003"][34]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][34]["cpad"] = " ";
$aISODataElement["2003"][34]["sde"] = array();
$aISODataElement["2003"][34]["sde"][0] = array();
$aISODataElement["2003"][34]["sde"][0]["name"] = "ACCOUNT BASED DIGITAL SIGNATURE";
$aISODataElement["2003"][34]["sde"][0]["format"] = "LLVAR";
$aISODataElement["2003"][34]["sde"][0]["type"] = "b";
$aISODataElement["2003"][34]["sde"][0]["len"] = 90;
$aISODataElement["2003"][34]["sde"][1] = array();
$aISODataElement["2003"][34]["sde"][1]["name"] = "AUTHENTICATION CODE";
$aISODataElement["2003"][34]["sde"][1]["format"] = "Tag 80";
$aISODataElement["2003"][34]["sde"][1]["type"] = "ansb";
$aISODataElement["2003"][34]["sde"][1]["len"] = 50;
$aISODataElement["2003"][34]["sde"][2] = array();
$aISODataElement["2003"][34]["sde"][2]["name"] = "CARD ACCEPTOR CERTIFICATE SERIAL NUMBER";
$aISODataElement["2003"][34]["sde"][2]["format"] = "LLVAR";
$aISODataElement["2003"][34]["sde"][2]["type"] = "b";
$aISODataElement["2003"][34]["sde"][2]["len"] = 16;
$aISODataElement["2003"][34]["sde"][3] = array();
$aISODataElement["2003"][34]["sde"][3]["name"] = "CARDHOLDER CERTIFICATE SERIAL NUMBER";
$aISODataElement["2003"][34]["sde"][3]["format"] = "";
$aISODataElement["2003"][34]["sde"][3]["type"] = "b";
$aISODataElement["2003"][34]["sde"][3]["len"] = 16;
$aISODataElement["2003"][34]["sde"][4] = array();
$aISODataElement["2003"][34]["sde"][4]["name"] = "TRANSSTAIN";
$aISODataElement["2003"][34]["sde"][4]["format"] = "";
$aISODataElement["2003"][34]["sde"][4]["type"] = "b";
$aISODataElement["2003"][34]["sde"][4]["len"] = 20;
$aISODataElement["2003"][34]["sde"][5] = array();
$aISODataElement["2003"][34]["sde"][5]["name"] = "XID";
$aISODataElement["2003"][34]["sde"][5]["format"] = "";
$aISODataElement["2003"][34]["sde"][5]["type"] = "b";
$aISODataElement["2003"][34]["sde"][5]["len"] = 20;

$aISODataElement["2003"][35] = array();
$aISODataElement["2003"][35]["name"] = "TRACK 2 DATA";
$aISODataElement["2003"][35]["format"] = "LLVAR";
$aISODataElement["2003"][35]["type"] = "z";
$aISODataElement["2003"][35]["len"] = 37;
$aISODataElement["2003"][35]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][35]["cpad"] = " ";
$aISODataElement["2003"][35]["sde"] = NULL;

$aISODataElement["2003"][36] = array();
$aISODataElement["2003"][36]["name"] = "TRACK 3 DATA";
$aISODataElement["2003"][36]["format"] = "LLLVAR";
$aISODataElement["2003"][36]["type"] = "z";
$aISODataElement["2003"][36]["len"] = 104;
$aISODataElement["2003"][36]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][36]["cpad"] = " ";
$aISODataElement["2003"][36]["sde"] = NULL;

$aISODataElement["2003"][37] = array();
$aISODataElement["2003"][37]["name"] = "RETRIEVAL REFERENCE NUMBER";
$aISODataElement["2003"][37]["format"] = "";
$aISODataElement["2003"][37]["type"] = "anp";
$aISODataElement["2003"][37]["len"] = 12;
$aISODataElement["2003"][37]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][37]["cpad"] = " ";
$aISODataElement["2003"][37]["sde"] = NULL;

$aISODataElement["2003"][38] = array();
$aISODataElement["2003"][38]["name"] = "APPROVAL CODE";
$aISODataElement["2003"][38]["format"] = "";
$aISODataElement["2003"][38]["type"] = "anp";
$aISODataElement["2003"][38]["len"] = 6;
$aISODataElement["2003"][38]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][38]["cpad"] = " ";
$aISODataElement["2003"][38]["sde"] = NULL;

$aISODataElement["2003"][39] = array();
$aISODataElement["2003"][39]["name"] = "ACTION CODE";
$aISODataElement["2003"][39]["format"] = "";
$aISODataElement["2003"][39]["type"] = "n";
$aISODataElement["2003"][39]["len"] = 4;
$aISODataElement["2003"][39]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][39]["cpad"] = "0";
$aISODataElement["2003"][39]["sde"] = NULL;

$aISODataElement["2003"][40] = array();
$aISODataElement["2003"][40]["name"] = "SERVICE CODE";
$aISODataElement["2003"][40]["format"] = "";
$aISODataElement["2003"][40]["type"] = "n";
$aISODataElement["2003"][40]["len"] = 3;
$aISODataElement["2003"][40]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][40]["cpad"] = "0";
$aISODataElement["2003"][40]["sde"] = NULL;

$aISODataElement["2003"][41] = array();
$aISODataElement["2003"][41]["name"] = "CARD ACCEPTOR TERMINAL IDENTIFICATION";
$aISODataElement["2003"][41]["format"] = "";
$aISODataElement["2003"][41]["type"] = "ans";
$aISODataElement["2003"][41]["len"] = 16;
$aISODataElement["2003"][41]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][41]["cpad"] = "0";
$aISODataElement["2003"][41]["sde"] = NULL;

$aISODataElement["2003"][42] = array();
$aISODataElement["2003"][42]["name"] = "CARD ACCEPTOR IDENTIFICATION CODE";
$aISODataElement["2003"][42]["format"] = "LLVAR";
$aISODataElement["2003"][42]["type"] = "ans";
$aISODataElement["2003"][42]["len"] = 35;
$aISODataElement["2003"][42]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][42]["cpad"] = "0";
$aISODataElement["2003"][42]["sde"] = NULL;

$aISODataElement["2003"][43] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][43]["name"] = "CARD ACCEPTOR NAME/LOCATION";
$aISODataElement["2003"][43]["format"] = "";
$aISODataElement["2003"][43]["type"] = "ansb";
$aISODataElement["2003"][43]["len"] = 9999;
$aISODataElement["2003"][43]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][43]["cpad"] = " ";
$aISODataElement["2003"][43]["sde"] = NULL;

$aISODataElement["2003"][44] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][44]["name"] = "ADDITIONAL RESPONSE DATA";
$aISODataElement["2003"][44]["format"] = "LLLLVAR";
$aISODataElement["2003"][44]["type"] = "ansb";
$aISODataElement["2003"][44]["len"] = 9999;
$aISODataElement["2003"][44]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][44]["cpad"] = " ";
$aISODataElement["2003"][44]["sde"] = NULL;

$aISODataElement["2003"][45] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][45]["name"] = "TRACK 1 DATA";
$aISODataElement["2003"][45]["format"] = "LLVAR";
$aISODataElement["2003"][45]["type"] = "ans";
$aISODataElement["2003"][45]["len"] = 76;
$aISODataElement["2003"][45]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][45]["cpad"] = " ";
$aISODataElement["2003"][45]["sde"] = NULL;

$aISODataElement["2003"][46] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][46]["name"] = "AMOUNTS FEES";
$aISODataElement["2003"][46]["format"] = "LLLVAR";
$aISODataElement["2003"][46]["type"] = "ans";
$aISODataElement["2003"][46]["len"] = 216;
$aISODataElement["2003"][46]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][46]["cpad"] = "0";
$aISODataElement["2003"][46]["sde"] = NULL;

$aISODataElement["2003"][47] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][47]["name"] = "ADDITIONAL DATA NATIONAL";
$aISODataElement["2003"][47]["format"] = "LLLVAR";
$aISODataElement["2003"][47]["type"] = "ans";
$aISODataElement["2003"][47]["len"] = 999;
$aISODataElement["2003"][47]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][47]["cpad"] = " ";
$aISODataElement["2003"][47]["sde"] = NULL;

$aISODataElement["2003"][48] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][48]["name"] = "ADDITIONAL DATA PRIVATE";
$aISODataElement["2003"][48]["format"] = "LLLVAR";
$aISODataElement["2003"][48]["type"] = "ans";
$aISODataElement["2003"][48]["len"] = 999;
$aISODataElement["2003"][48]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][48]["cpad"] = " ";
$aISODataElement["2003"][48]["sde"] = NULL;

$aISODataElement["2003"][49] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][49]["name"] = "VERIFICATION DATA";
$aISODataElement["2003"][49]["format"] = "LLLLVAR";
$aISODataElement["2003"][49]["type"] = "ans";
$aISODataElement["2003"][49]["len"] = 9999;
$aISODataElement["2003"][49]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][49]["cpad"] = " ";
$aISODataElement["2003"][49]["sde"] = NULL;

$aISODataElement["2003"][50] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][50]["name"] = "RESERVED FOR ISO";
$aISODataElement["2003"][50]["format"] = "LLLLVAR";
$aISODataElement["2003"][50]["type"] = "ansb";
$aISODataElement["2003"][50]["len"] = 9999;
$aISODataElement["2003"][50]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][50]["cpad"] = " ";
$aISODataElement["2003"][50]["sde"] = NULL;

$aISODataElement["2003"][51] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][51]["name"] = "RESERVED FOR ISO";
$aISODataElement["2003"][51]["format"] = "LLLLVAR";
$aISODataElement["2003"][51]["type"] = "ansb";
$aISODataElement["2003"][51]["len"] = 9999;
$aISODataElement["2003"][51]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][51]["cpad"] = " ";
$aISODataElement["2003"][51]["sde"] = NULL;

$aISODataElement["2003"][52] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][52]["name"] = "PIN DATA";
$aISODataElement["2003"][52]["format"] = "";
$aISODataElement["2003"][52]["type"] = "b";
$aISODataElement["2003"][52]["len"] = 8;
$aISODataElement["2003"][52]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][52]["cpad"] = " ";
$aISODataElement["2003"][52]["sde"] = NULL;

$aISODataElement["2003"][53] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][53]["name"] = "SECURITY RELATED CONTROL INFORMATION";
$aISODataElement["2003"][53]["format"] = "LLVAR";
$aISODataElement["2003"][53]["type"] = "b";
$aISODataElement["2003"][53]["len"] = 48;
$aISODataElement["2003"][53]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][53]["cpad"] = " ";
$aISODataElement["2003"][53]["sde"] = NULL;

$aISODataElement["2003"][54] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][54]["name"] = "AMOUNTS ADDITIONAL";
$aISODataElement["2003"][54]["format"] = "LLLVAR";
$aISODataElement["2003"][54]["type"] = "ans";
$aISODataElement["2003"][54]["len"] = 126;
$aISODataElement["2003"][54]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][54]["cpad"] = "0";
$aISODataElement["2003"][54]["sde"] = NULL;

$aISODataElement["2003"][55] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][55]["name"] = "ICC SYSTEM RELATED DATA";
$aISODataElement["2003"][55]["format"] = "LLLLVAR";
$aISODataElement["2003"][55]["type"] = "b";
$aISODataElement["2003"][55]["len"] = 9999;
$aISODataElement["2003"][55]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][55]["cpad"] = " ";
$aISODataElement["2003"][55]["sde"] = NULL;

$aISODataElement["2003"][56] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][56]["name"] = "ORIGINAL DATA ELEMENTS";
$aISODataElement["2003"][56]["format"] = "LLVAR";
$aISODataElement["2003"][56]["type"] = "ans";
$aISODataElement["2003"][56]["len"] = 41;
$aISODataElement["2003"][56]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][56]["cpad"] = " ";
$aISODataElement["2003"][56]["sde"] = NULL;

$aISODataElement["2003"][57] = array();
$aISODataElement["2003"][57]["name"] = "AUTHORIZATION LIFE CYCLE CODE";
$aISODataElement["2003"][57]["format"] = "";
$aISODataElement["2003"][57]["type"] = "n";
$aISODataElement["2003"][57]["len"] = 3;
$aISODataElement["2003"][57]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][57]["cpad"] = "0";
$aISODataElement["2003"][57]["sde"] = NULL;

$aISODataElement["2003"][58] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][58]["name"] = "AUTHORIZING AGENT INSTITUTION IDENTIFICATION CODE";
$aISODataElement["2003"][58]["format"] = "LLVAR";
$aISODataElement["2003"][58]["type"] = "n";
$aISODataElement["2003"][58]["len"] = 11;
$aISODataElement["2003"][58]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][58]["cpad"] = "0";
$aISODataElement["2003"][58]["sde"] = NULL;

$aISODataElement["2003"][59] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][59]["name"] = "TRANSPORT DATA";
$aISODataElement["2003"][59]["format"] = "LLLVAR";
$aISODataElement["2003"][59]["type"] = "ans";
$aISODataElement["2003"][59]["len"] = 999;
$aISODataElement["2003"][59]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][59]["cpad"] = " ";
$aISODataElement["2003"][59]["sde"] = NULL;

$aISODataElement["2003"][60] = array();
$aISODataElement["2003"][60]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["2003"][60]["format"] = "LLLVAR";
$aISODataElement["2003"][60]["type"] = "ans";
$aISODataElement["2003"][60]["len"] = 999;
$aISODataElement["2003"][60]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][60]["cpad"] = " ";
$aISODataElement["2003"][60]["sde"] = NULL;

$aISODataElement["2003"][61] = array();
$aISODataElement["2003"][61]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["2003"][61]["format"] = "LLLVAR";
$aISODataElement["2003"][61]["type"] = "ans";
$aISODataElement["2003"][61]["len"] = 999;
$aISODataElement["2003"][61]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][61]["cpad"] = " ";
$aISODataElement["2003"][61]["sde"] = NULL;

$aISODataElement["2003"][62] = array();
$aISODataElement["2003"][62]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][62]["format"] = "LLLVAR";
$aISODataElement["2003"][62]["type"] = "ans";
$aISODataElement["2003"][62]["len"] = 999;
$aISODataElement["2003"][62]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][62]["cpad"] = " ";
$aISODataElement["2003"][62]["sde"] = NULL;

$aISODataElement["2003"][63] = array();
$aISODataElement["2003"][63]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][63]["format"] = "LLLVAR";
$aISODataElement["2003"][63]["type"] = "ans";
$aISODataElement["2003"][63]["len"] = 999;
$aISODataElement["2003"][63]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][63]["cpad"] = " ";
$aISODataElement["2003"][63]["sde"] = NULL;

$aISODataElement["2003"][64] = array();
$aISODataElement["2003"][64]["name"] = "MAC FIELD";
$aISODataElement["2003"][64]["format"] = "";
$aISODataElement["2003"][64]["type"] = "b";
$aISODataElement["2003"][64]["len"] = 4;
$aISODataElement["2003"][64]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][64]["cpad"] = " ";
$aISODataElement["2003"][64]["sde"] = NULL;

$aISODataElement["2003"][65] = array();
$aISODataElement["2003"][65]["name"] = "RESERVED FOR ISO USE"; // tertiary bitmap
$aISODataElement["2003"][65]["format"] = "";
$aISODataElement["2003"][65]["type"] = "an";
$aISODataElement["2003"][65]["len"] = 16;
$aISODataElement["2003"][65]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][65]["cpad"] = "0";
$aISODataElement["2003"][65]["sde"] = NULL;

$aISODataElement["2003"][66] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][66]["name"] = "AMOUNTS ORIGINAL FEES";
$aISODataElement["2003"][66]["format"] = "LLLVAR";
$aISODataElement["2003"][66]["type"] = "ans";
$aISODataElement["2003"][66]["len"] = 216;
$aISODataElement["2003"][66]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][66]["cpad"] = "0";
$aISODataElement["2003"][66]["sde"] = NULL;

$aISODataElement["2003"][67] = array();
$aISODataElement["2003"][67]["name"] = "EXTENDED PAYMENT DATA";
$aISODataElement["2003"][67]["format"] = "";
$aISODataElement["2003"][67]["type"] = "n";
$aISODataElement["2003"][67]["len"] = 2;
$aISODataElement["2003"][67]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][67]["cpad"] = "0";
$aISODataElement["2003"][67]["sde"] = NULL;

$aISODataElement["2003"][68] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][68]["name"] = "BATCH/FILE TRANSFER MESSAGE CONTROL";
$aISODataElement["2003"][68]["format"] = "";
$aISODataElement["2003"][68]["type"] = "an";
$aISODataElement["2003"][68]["len"] = 9;
$aISODataElement["2003"][68]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][68]["cpad"] = " ";
$aISODataElement["2003"][68]["sde"] = NULL;

$aISODataElement["2003"][69] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][69]["name"] = "BATCH/FILE TRANSFER CONTROL DATA";
$aISODataElement["2003"][69]["format"] = "";
$aISODataElement["2003"][69]["type"] = "ans";
$aISODataElement["2003"][69]["len"] = 40;
$aISODataElement["2003"][69]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][69]["cpad"] = " ";
$aISODataElement["2003"][69]["sde"] = NULL;

$aISODataElement["2003"][70] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][70]["name"] = "FILE TRANSFER DESCRIPTION DATA";
$aISODataElement["2003"][70]["format"] = "";
$aISODataElement["2003"][70]["type"] = "n";
$aISODataElement["2003"][70]["len"] = 18;
$aISODataElement["2003"][70]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][70]["cpad"] = "0";
$aISODataElement["2003"][70]["sde"] = NULL;

$aISODataElement["2003"][71] = array();
$aISODataElement["2003"][71]["name"] = "RESEVED FOR ISO USE";
$aISODataElement["2003"][71]["format"] = "LLLLVAR";
$aISODataElement["2003"][71]["type"] = "ansb";
$aISODataElement["2003"][71]["len"] = 9999;
$aISODataElement["2003"][71]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][71]["cpad"] = " ";
$aISODataElement["2003"][71]["sde"] = NULL;

$aISODataElement["2003"][72] = array();
$aISODataElement["2003"][72]["name"] = "DATA RECORD";
$aISODataElement["2003"][72]["format"] = "LLLLVAR";
$aISODataElement["2003"][72]["type"] = "ansb";
$aISODataElement["2003"][72]["len"] = 9999;
$aISODataElement["2003"][72]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][72]["cpad"] = " ";
$aISODataElement["2003"][72]["sde"] = NULL;

$aISODataElement["2003"][73] = array();
$aISODataElement["2003"][73]["name"] = "DATA ACTION";
$aISODataElement["2003"][73]["format"] = "CCYYMMDD";
$aISODataElement["2003"][73]["type"] = "n";
$aISODataElement["2003"][73]["len"] = 8;
$aISODataElement["2003"][73]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][73]["cpad"] = "0";
$aISODataElement["2003"][73]["sde"] = NULL;

$aISODataElement["2003"][74] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][74]["name"] = "RECONCILIATION DATA PRIMARY";
$aISODataElement["2003"][74]["format"] = "";
$aISODataElement["2003"][74]["type"] = "n";
$aISODataElement["2003"][74]["len"] = 156;
$aISODataElement["2003"][74]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][74]["cpad"] = "0";
$aISODataElement["2003"][74]["sde"] = NULL;

$aISODataElement["2003"][75] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][75]["name"] = "RECONCILIATION DATA SECONDARY";
$aISODataElement["2003"][75]["format"] = "";
$aISODataElement["2003"][75]["type"] = "n";
$aISODataElement["2003"][75]["len"] = 90;
$aISODataElement["2003"][75]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][75]["cpad"] = "0";
$aISODataElement["2003"][75]["sde"] = NULL;

$aISODataElement["2003"][76] = array();
$aISODataElement["2003"][76]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][76]["format"] = "";
$aISODataElement["2003"][76]["type"] = "ansb";
$aISODataElement["2003"][76]["len"] = 9999;
$aISODataElement["2003"][76]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][76]["cpad"] = " ";
$aISODataElement["2003"][76]["sde"] = NULL;

$aISODataElement["2003"][77] = array();
$aISODataElement["2003"][77]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][77]["format"] = "";
$aISODataElement["2003"][77]["type"] = "ansb";
$aISODataElement["2003"][77]["len"] = 9999;
$aISODataElement["2003"][77]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][77]["cpad"] = " ";
$aISODataElement["2003"][77]["sde"] = NULL;

$aISODataElement["2003"][78] = array();
$aISODataElement["2003"][78]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][78]["format"] = "";
$aISODataElement["2003"][78]["type"] = "ansb";
$aISODataElement["2003"][78]["len"] = 9999;
$aISODataElement["2003"][78]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][78]["cpad"] = " ";
$aISODataElement["2003"][78]["sde"] = NULL;

$aISODataElement["2003"][79] = array();
$aISODataElement["2003"][79]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][79]["format"] = "";
$aISODataElement["2003"][79]["type"] = "ansb";
$aISODataElement["2003"][79]["len"] = 9999;
$aISODataElement["2003"][79]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][79]["cpad"] = " ";
$aISODataElement["2003"][79]["sde"] = NULL;

$aISODataElement["2003"][80] = array();
$aISODataElement["2003"][80]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][80]["format"] = "";
$aISODataElement["2003"][80]["type"] = "ansb";
$aISODataElement["2003"][80]["len"] = 9999;
$aISODataElement["2003"][80]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][80]["cpad"] = " ";
$aISODataElement["2003"][80]["sde"] = NULL;

$aISODataElement["2003"][81] = array();
$aISODataElement["2003"][81]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][81]["format"] = "";
$aISODataElement["2003"][81]["type"] = "ansb";
$aISODataElement["2003"][81]["len"] = 9999;
$aISODataElement["2003"][81]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][81]["cpad"] = " ";
$aISODataElement["2003"][81]["sde"] = NULL;

$aISODataElement["2003"][82] = array();
$aISODataElement["2003"][82]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][82]["format"] = "";
$aISODataElement["2003"][82]["type"] = "ansb";
$aISODataElement["2003"][82]["len"] = 9999;
$aISODataElement["2003"][82]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][82]["cpad"] = " ";
$aISODataElement["2003"][82]["sde"] = NULL;

$aISODataElement["2003"][83] = array();
$aISODataElement["2003"][83]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][83]["format"] = "";
$aISODataElement["2003"][83]["type"] = "ansb";
$aISODataElement["2003"][83]["len"] = 9999;
$aISODataElement["2003"][83]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][83]["cpad"] = " ";
$aISODataElement["2003"][83]["sde"] = NULL;

$aISODataElement["2003"][84] = array();
$aISODataElement["2003"][84]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][84]["format"] = "";
$aISODataElement["2003"][84]["type"] = "ansb";
$aISODataElement["2003"][84]["len"] = 9999;
$aISODataElement["2003"][84]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][84]["cpad"] = " ";
$aISODataElement["2003"][84]["sde"] = NULL;

$aISODataElement["2003"][85] = array();
$aISODataElement["2003"][85]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][85]["format"] = "";
$aISODataElement["2003"][85]["type"] = "ansb";
$aISODataElement["2003"][85]["len"] = 9999;
$aISODataElement["2003"][85]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][85]["cpad"] = " ";
$aISODataElement["2003"][85]["sde"] = NULL;

$aISODataElement["2003"][86] = array();
$aISODataElement["2003"][86]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][86]["format"] = "";
$aISODataElement["2003"][86]["type"] = "ansb";
$aISODataElement["2003"][86]["len"] = 9999;
$aISODataElement["2003"][86]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][86]["cpad"] = " ";
$aISODataElement["2003"][86]["sde"] = NULL;

$aISODataElement["2003"][87] = array();
$aISODataElement["2003"][87]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][87]["format"] = "";
$aISODataElement["2003"][87]["type"] = "ansb";
$aISODataElement["2003"][87]["len"] = 9999;
$aISODataElement["2003"][87]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][87]["cpad"] = " ";
$aISODataElement["2003"][87]["sde"] = NULL;

$aISODataElement["2003"][88] = array();
$aISODataElement["2003"][88]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][88]["format"] = "";
$aISODataElement["2003"][88]["type"] = "ansb";
$aISODataElement["2003"][88]["len"] = 9999;
$aISODataElement["2003"][88]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][88]["cpad"] = " ";
$aISODataElement["2003"][88]["sde"] = NULL;

$aISODataElement["2003"][89] = array();
$aISODataElement["2003"][89]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][89]["format"] = "";
$aISODataElement["2003"][89]["type"] = "ansb";
$aISODataElement["2003"][89]["len"] = 9999;
$aISODataElement["2003"][89]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][89]["cpad"] = " ";
$aISODataElement["2003"][89]["sde"] = NULL;

$aISODataElement["2003"][90] = array();
$aISODataElement["2003"][90]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][90]["format"] = "";
$aISODataElement["2003"][90]["type"] = "ansb";
$aISODataElement["2003"][90]["len"] = 9999;
$aISODataElement["2003"][90]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][90]["cpad"] = " ";
$aISODataElement["2003"][90]["sde"] = NULL;

$aISODataElement["2003"][91] = array();
$aISODataElement["2003"][91]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][91]["format"] = "";
$aISODataElement["2003"][91]["type"] = "ansb";
$aISODataElement["2003"][91]["len"] = 9999;
$aISODataElement["2003"][91]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][91]["cpad"] = " ";
$aISODataElement["2003"][91]["sde"] = NULL;

$aISODataElement["2003"][92] = array();
$aISODataElement["2003"][92]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][92]["format"] = "";
$aISODataElement["2003"][92]["type"] = "ansb";
$aISODataElement["2003"][92]["len"] = 9999;
$aISODataElement["2003"][92]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][92]["cpad"] = " ";
$aISODataElement["2003"][92]["sde"] = NULL;

$aISODataElement["2003"][93] = array();
$aISODataElement["2003"][93]["name"] = "TRANSACTION DESTINATION INSTITUTION IDENTIFICATION CODE";
$aISODataElement["2003"][93]["format"] = "LLVAR";
$aISODataElement["2003"][93]["type"] = "n";
$aISODataElement["2003"][93]["len"] = 11;
$aISODataElement["2003"][93]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][93]["cpad"] = "0";
$aISODataElement["2003"][93]["sde"] = NULL;

$aISODataElement["2003"][94] = array();
$aISODataElement["2003"][94]["name"] = "TRANSACTION ORIGINATION INSTITUTION IDENTIFICATION CODE";
$aISODataElement["2003"][94]["format"] = "LLVAR";
$aISODataElement["2003"][94]["type"] = "n";
$aISODataElement["2003"][94]["len"] = 11;
$aISODataElement["2003"][94]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][94]["cpad"] = "0";
$aISODataElement["2003"][94]["sde"] = NULL;

$aISODataElement["2003"][95] = array();
$aISODataElement["2003"][95]["name"] = "CARD ISSUER REFERENCE DATA";
$aISODataElement["2003"][95]["format"] = "";
$aISODataElement["2003"][95]["type"] = "ans";
$aISODataElement["2003"][95]["len"] = 99;
$aISODataElement["2003"][95]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][95]["cpad"] = " ";
$aISODataElement["2003"][95]["sde"] = NULL;

$aISODataElement["2003"][96] = array();
$aISODataElement["2003"][96]["name"] = "KEY MANAGEMENT DATA";
$aISODataElement["2003"][96]["format"] = "LLLVAR";
$aISODataElement["2003"][96]["type"] = "b";
$aISODataElement["2003"][96]["len"] = 999;
$aISODataElement["2003"][96]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][96]["cpad"] = " ";
$aISODataElement["2003"][96]["sde"] = NULL;

$aISODataElement["2003"][97] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][97]["name"] = "AMOUNT NET RECONCILIAION";
$aISODataElement["2003"][97]["format"] = "";
$aISODataElement["2003"][97]["type"] = "xn";
$aISODataElement["2003"][97]["len"] = 21;
$aISODataElement["2003"][97]["pad"] = STR_PAD_LEFT;
$aISODataElement["2003"][97]["cpad"] = " ";
$aISODataElement["2003"][97]["sde"] = NULL;

$aISODataElement["2003"][98] = array();
$aISODataElement["2003"][98]["name"] = "PAYEE";
$aISODataElement["2003"][98]["format"] = "";
$aISODataElement["2003"][98]["type"] = "ans";
$aISODataElement["2003"][98]["len"] = 25;
$aISODataElement["2003"][98]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][98]["cpad"] = " ";
$aISODataElement["2003"][98]["sde"] = NULL;

$aISODataElement["2003"][99] = array();
$aISODataElement["2003"][99]["name"] = "SETTLEMENT INSTITUTION IDENTIFICATION CODE";
$aISODataElement["2003"][99]["format"] = "LLVAR";
$aISODataElement["2003"][99]["type"] = "an";
$aISODataElement["2003"][99]["len"] = 11;
$aISODataElement["2003"][99]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][99]["cpad"] = " ";
$aISODataElement["2003"][99]["sde"] = NULL;

$aISODataElement["2003"][100] = array();
$aISODataElement["2003"][100]["name"] = "RECEIVING INSTITUTION IDENTIFICATION CODE";
$aISODataElement["2003"][100]["format"] = "LLVAR";
$aISODataElement["2003"][100]["type"] = "an";
$aISODataElement["2003"][100]["len"] = 11;
$aISODataElement["2003"][100]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][100]["cpad"] = " ";
$aISODataElement["2003"][100]["sde"] = NULL;

$aISODataElement["2003"][101] = array();
$aISODataElement["2003"][101]["name"] = "FILENAME";
$aISODataElement["2003"][101]["format"] = "LLVAR";
$aISODataElement["2003"][101]["type"] = "ans";
$aISODataElement["2003"][101]["len"] = 99;
$aISODataElement["2003"][101]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][101]["cpad"] = " ";
$aISODataElement["2003"][101]["sde"] = NULL;

$aISODataElement["2003"][102] = array();
$aISODataElement["2003"][102]["name"] = "ACCOUNT IDENTIFICATION 1";
$aISODataElement["2003"][102]["format"] = "LLVAR";
$aISODataElement["2003"][102]["type"] = "ans";
$aISODataElement["2003"][102]["len"] = 28;
$aISODataElement["2003"][102]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][102]["cpad"] = " ";
$aISODataElement["2003"][102]["sde"] = NULL;

$aISODataElement["2003"][103] = array();
$aISODataElement["2003"][103]["name"] = "ACCOUNT IDENTIFICATION 2";
$aISODataElement["2003"][103]["format"] = "LLVAR";
$aISODataElement["2003"][103]["type"] = "ans";
$aISODataElement["2003"][103]["len"] = 28;
$aISODataElement["2003"][103]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][103]["cpad"] = " ";
$aISODataElement["2003"][103]["sde"] = NULL;

$aISODataElement["2003"][104] = array(); // WITH SUB-DATA ELEMENT
$aISODataElement["2003"][104]["name"] = "TRANSACTION SPECIFIC DATA";
$aISODataElement["2003"][104]["format"] = "LLLLVAR";
$aISODataElement["2003"][104]["type"] = "ansb";
$aISODataElement["2003"][104]["len"] = 9999;
$aISODataElement["2003"][104]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][104]["cpad"] = " ";
$aISODataElement["2003"][104]["sde"] = NULL;

$aISODataElement["2003"][105] = array();
$aISODataElement["2003"][105]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][105]["format"] = "LLLLVAR";
$aISODataElement["2003"][105]["type"] = "ansb";
$aISODataElement["2003"][105]["len"] = 9999;
$aISODataElement["2003"][105]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][105]["cpad"] = " ";
$aISODataElement["2003"][105]["sde"] = NULL;

$aISODataElement["2003"][106] = array();
$aISODataElement["2003"][106]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][106]["format"] = "LLLLVAR";
$aISODataElement["2003"][106]["type"] = "ansb";
$aISODataElement["2003"][106]["len"] = 9999;
$aISODataElement["2003"][106]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][106]["cpad"] = " ";
$aISODataElement["2003"][106]["sde"] = NULL;

$aISODataElement["2003"][107] = array();
$aISODataElement["2003"][107]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][107]["format"] = "LLLLVAR";
$aISODataElement["2003"][107]["type"] = "ansb";
$aISODataElement["2003"][107]["len"] = 9999;
$aISODataElement["2003"][107]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][107]["cpad"] = " ";
$aISODataElement["2003"][107]["sde"] = NULL;

$aISODataElement["2003"][108] = array();
$aISODataElement["2003"][108]["name"] = "RESERVED FOR ISO USE";
$aISODataElement["2003"][108]["format"] = "LLLLVAR";
$aISODataElement["2003"][108]["type"] = "ansb";
$aISODataElement["2003"][108]["len"] = 9999;
$aISODataElement["2003"][108]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][108]["cpad"] = " ";
$aISODataElement["2003"][108]["sde"] = NULL;

$aISODataElement["2003"][109] = array();
$aISODataElement["2003"][109]["name"] = "RECONCILIATION FEE AMOUNT CREDITS";
$aISODataElement["2003"][109]["format"] = "LLLVAR";
$aISODataElement["2003"][109]["type"] = "ans";
$aISODataElement["2003"][109]["len"] = 144;
$aISODataElement["2003"][109]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][109]["cpad"] = " ";
$aISODataElement["2003"][109]["sde"] = NULL;

$aISODataElement["2003"][110] = array();
$aISODataElement["2003"][110]["name"] = "RECONCILIATION FEE AMOUNT DEBITS";
$aISODataElement["2003"][110]["format"] = "LLLVAR";
$aISODataElement["2003"][110]["type"] = "ans";
$aISODataElement["2003"][110]["len"] = 144;
$aISODataElement["2003"][110]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][110]["cpad"] = " ";
$aISODataElement["2003"][110]["sde"] = NULL;

$aISODataElement["2003"][111] = array();
$aISODataElement["2003"][111]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][111]["format"] = "LLLLVAR";
$aISODataElement["2003"][111]["type"] = "ansb";
$aISODataElement["2003"][111]["len"] = 9999;
$aISODataElement["2003"][111]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][111]["cpad"] = " ";
$aISODataElement["2003"][111]["sde"] = NULL;

$aISODataElement["2003"][112] = array();
$aISODataElement["2003"][112]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][112]["format"] = "LLLLVAR";
$aISODataElement["2003"][112]["type"] = "ansb";
$aISODataElement["2003"][112]["len"] = 9999;
$aISODataElement["2003"][112]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][112]["cpad"] = " ";
$aISODataElement["2003"][112]["sde"] = NULL;

$aISODataElement["2003"][113] = array();
$aISODataElement["2003"][113]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][113]["format"] = "LLLLVAR";
$aISODataElement["2003"][113]["type"] = "ansb";
$aISODataElement["2003"][113]["len"] = 9999;
$aISODataElement["2003"][113]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][113]["cpad"] = " ";
$aISODataElement["2003"][113]["sde"] = NULL;

$aISODataElement["2003"][114] = array();
$aISODataElement["2003"][114]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][114]["format"] = "LLLLVAR";
$aISODataElement["2003"][114]["type"] = "ansb";
$aISODataElement["2003"][114]["len"] = 9999;
$aISODataElement["2003"][114]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][114]["cpad"] = " ";
$aISODataElement["2003"][114]["sde"] = NULL;

$aISODataElement["2003"][115] = array();
$aISODataElement["2003"][115]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][115]["format"] = "LLLLVAR";
$aISODataElement["2003"][115]["type"] = "ansb";
$aISODataElement["2003"][115]["len"] = 9999;
$aISODataElement["2003"][115]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][115]["cpad"] = " ";
$aISODataElement["2003"][115]["sde"] = NULL;

$aISODataElement["2003"][116] = array();
$aISODataElement["2003"][116]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["2003"][116]["format"] = "LLLLVAR";
$aISODataElement["2003"][116]["type"] = "ansb";
$aISODataElement["2003"][116]["len"] = 9999;
$aISODataElement["2003"][116]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][116]["cpad"] = " ";
$aISODataElement["2003"][116]["sde"] = NULL;

$aISODataElement["2003"][117] = array();
$aISODataElement["2003"][117]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["2003"][117]["format"] = "LLLLVAR";
$aISODataElement["2003"][117]["type"] = "ansb";
$aISODataElement["2003"][117]["len"] = 9999;
$aISODataElement["2003"][117]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][117]["cpad"] = " ";
$aISODataElement["2003"][117]["sde"] = NULL;

$aISODataElement["2003"][118] = array();
$aISODataElement["2003"][118]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["2003"][118]["format"] = "LLLLVAR";
$aISODataElement["2003"][118]["type"] = "ansb";
$aISODataElement["2003"][118]["len"] = 9999;
$aISODataElement["2003"][118]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][118]["cpad"] = " ";
$aISODataElement["2003"][118]["sde"] = NULL;

$aISODataElement["2003"][119] = array();
$aISODataElement["2003"][119]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["2003"][119]["format"] = "LLLLVAR";
$aISODataElement["2003"][119]["type"] = "ansb";
$aISODataElement["2003"][119]["len"] = 9999;
$aISODataElement["2003"][119]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][119]["cpad"] = " ";
$aISODataElement["2003"][119]["sde"] = NULL;

$aISODataElement["2003"][120] = array();
$aISODataElement["2003"][120]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["2003"][120]["format"] = "LLLLVAR";
$aISODataElement["2003"][120]["type"] = "ansb";
$aISODataElement["2003"][120]["len"] = 9999;
$aISODataElement["2003"][120]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][120]["cpad"] = " ";
$aISODataElement["2003"][120]["sde"] = NULL;

$aISODataElement["2003"][121] = array();
$aISODataElement["2003"][121]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["2003"][121]["format"] = "LLLLVAR";
$aISODataElement["2003"][121]["type"] = "ansb";
$aISODataElement["2003"][121]["len"] = 9999;
$aISODataElement["2003"][121]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][121]["cpad"] = " ";
$aISODataElement["2003"][121]["sde"] = NULL;

$aISODataElement["2003"][122] = array();
$aISODataElement["2003"][122]["name"] = "RESERVED FOR NATIONAL USE";
$aISODataElement["2003"][122]["format"] = "LLLLVAR";
$aISODataElement["2003"][122]["type"] = "ansb";
$aISODataElement["2003"][122]["len"] = 9999;
$aISODataElement["2003"][122]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][122]["cpad"] = " ";
$aISODataElement["2003"][122]["sde"] = NULL;

$aISODataElement["2003"][123] = array();
$aISODataElement["2003"][123]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][123]["format"] = "LLLLVAR";
$aISODataElement["2003"][123]["type"] = "ansb";
$aISODataElement["2003"][123]["len"] = 9999;
$aISODataElement["2003"][123]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][123]["cpad"] = " ";
$aISODataElement["2003"][123]["sde"] = NULL;

$aISODataElement["2003"][124] = array();
$aISODataElement["2003"][124]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][124]["format"] = "LLLLVAR";
$aISODataElement["2003"][124]["type"] = "ansb";
$aISODataElement["2003"][124]["len"] = 9999;
$aISODataElement["2003"][124]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][124]["cpad"] = " ";
$aISODataElement["2003"][124]["sde"] = NULL;

$aISODataElement["2003"][125] = array();
$aISODataElement["2003"][125]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][125]["format"] = "LLLLVAR";
$aISODataElement["2003"][125]["type"] = "ansb";
$aISODataElement["2003"][125]["len"] = 9999;
$aISODataElement["2003"][125]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][125]["cpad"] = " ";
$aISODataElement["2003"][125]["sde"] = NULL;

$aISODataElement["2003"][126] = array();
$aISODataElement["2003"][126]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][126]["format"] = "LLLLVAR";
$aISODataElement["2003"][126]["type"] = "ansb";
$aISODataElement["2003"][126]["len"] = 9999;
$aISODataElement["2003"][126]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][126]["cpad"] = " ";
$aISODataElement["2003"][126]["sde"] = NULL;

$aISODataElement["2003"][127] = array();
$aISODataElement["2003"][127]["name"] = "RESERVED FOR PRIVATE USE";
$aISODataElement["2003"][127]["format"] = "LLLLVAR";
$aISODataElement["2003"][127]["type"] = "ansb";
$aISODataElement["2003"][127]["len"] = 9999;
$aISODataElement["2003"][127]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][127]["cpad"] = " ";
$aISODataElement["2003"][127]["sde"] = NULL;

$aISODataElement["2003"][128] = array();
$aISODataElement["2003"][128]["name"] = "MAC FIELD";
$aISODataElement["2003"][128]["format"] = "";
$aISODataElement["2003"][128]["type"] = "b";
$aISODataElement["2003"][128]["len"] = 4;
$aISODataElement["2003"][128]["pad"] = STR_PAD_RIGHT;
$aISODataElement["2003"][128]["cpad"] = " ";
$aISODataElement["2003"][128]["sde"] = NULL;

class CBaseConverter
{
  static function _ascii_hexa_to_bin($sHexa = "")
  {
    $sBin = "";
    $n = strlen($sHexa);
    $nVarLen = 2;
    if ($n % $nVarLen == 0) // length must be even
    {
      $iStart = 0; $i = 0;
      while ($iStart + $nVarLen <= $n)
      {
        $sBin .= sprintf("%08s", decbin(hexdec(substr($sHexa, $iStart, $nVarLen))));
        $iStart += $nVarLen;
      }
    }

    return $sBin;
  } // end of _ascii_hexa_to_bin

  static function _ascii_bin_to_hexa($sBin = "")
  {
    $sBin = str_replace(" ", "", $sBin); // delete some probably spaces
    $inhex = "";

    // BINARY to HEX list
    $binary['0000'] = "0";
    $binary['0001'] = "1";
    $binary['0010'] = "2";
    $binary['0011'] = "3";
    $binary['0100'] = "4";
    $binary['0101'] = "5";
    $binary['0110'] = "6";
    $binary['0111'] = "7";
    $binary['1000'] = "8";
    $binary['1001'] = "9";
    $binary['1010'] = "A";
    $binary['1011'] = "B";
    $binary['1100'] = "C";
    $binary['1101'] = "D";
    $binary['1110'] = "E";
    $binary['1111'] = "F";

    //make sets of 4
    for( ; ; )
    {
      $calc = strlen($sBin) / 4;
      if (is_numeric($calc) && (intval($calc) == floatval($calc)))
      {
        break;
      }
      else
      {
        $sBin .= 0;
      }
    }

    // translate binary to hex
    for($i = 0 ; $i < strlen($sBin) ; $i = $i + 4)
    {
      $set = substr($sBin, $i, 4);
      $inhex .= $binary[$set];
    }

    return $inhex;
  } // end of _ascii_bin_to_hexa
} // end of CBaseConverter

class CISO8583Parser
{
  // internal vars
  private $sISOMsgStream = "";

  private $sMTI = "";
  private $sPrimaryBitMap = "";
  private $sSecondaryBitMap = "";
  private $sTertiaryBitMap = "";
  private $sISOVersion = "2003";
  private $aISODataElements = NULL;

  // debug
  private $bDebug = false;
  private $sLogFileName = "";

  // last error
  private $iErrCode = 0;
  private $sErrMsg = "";

  function __construct($sISOMsgStream = "")
  {
    $this->AssignVars($sISOMsgStream);
  } // end of construct

  function AssignVars($sISOMsgStream = "")
  {
    $this->sISOMsgStream = $sISOMsgStream;
  } // end of AssignVars

  function Debug($bDebug = false, $sLogFileName = "")
  {
    $this->bDebug = $bDebug;
    $this->sLogFileName = $sLogFileName;
  } // end of Debug

  function GetUsedDataElement($sBitMap = "", $iWhichBitMap = 0, &$aUsed)
  {
    //echo "[INFO] >> here 1\n";
    $sBitMapBin = CBaseConverter::_ascii_hexa_to_bin($sBitMap);
    //echo "[INFO] >> here 2 -> sBitMapBin [$sBitMapBin]\n";
    $n = strlen($sBitMapBin);
    $iIdx = 0;
    for($i=0; $i<$n; $i++)
    {
      if ($sBitMapBin[$i] == "1")
      {
        $aUsed[$iIdx++] = $i + 1 + ($iWhichBitMap * 64);
      }
    }
    //echo "[INFO] >> here 3 -> aUsed [".print_r($aUsed, true)."]\n";

    return $aUsed;
  } // end of GetUsedDataElement

  function Parse()
  {
    global $aISODataElement;

    $bParsed = false;
    $nStreamLen = strlen($this->sISOMsgStream);
    if ($nStreamLen >= 20) // minimal length of shortest ISO8583 message stream (MTI + PRIMARY BITMAP)
    {
      // determine ISO8583 version
      $sMTIVersion = substr($this->sISOMsgStream, 0, 1);
      switch ($sMTIVersion)
      {
        case "0": $this->sISOVersion = "1987";
          break;
        case "1": $this->sISOVersion = "1993";
          break;
        case "2": $this->sISOVersion = "2003";
          break;
      }
      //echo "[INFO] sISOVersion [".$this->sISOVersion."]\n";

      if (@isset($aISODataElement[$this->sISOVersion])) // there is data element definition of parsed ISO8583 message stream
      {
        //echo "[INFO] here\n";
        $this->aISODataElements = array();
        // get MTI
        $this->sMTI = substr($this->sISOMsgStream, 0, 4);
        $this->aISODataElements[0] = $this->sMTI;
        // get bit map
        $this->sPrimaryBitMap = substr($this->sISOMsgStream, 4, 16);
        $this->aISODataElements[1] = $this->sPrimaryBitMap;

        //echo "[INFO] this->aISODataElements [".print_r($this->aISODataElements, true)."]\n";

        $aUsedPrimaryDataElement = array();
        $aUsedPrimaryDataElement = $this->GetUsedDataElement($this->sPrimaryBitMap, 0, $aUsedPrimaryDataElement); // 0 = check primary bitmap

        $nPrimaryDELen = sizeof($aUsedPrimaryDataElement);
        $nSecondaryDELen = 0;
        $nTertiaryDELen = 0;

        //echo "[INFO] aUsedPrimaryDataElement [".print_r($aUsedPrimaryDataElement, true)."]\n";

        if ($nPrimaryDELen > 0)
        {
          $iPrimaryStart = 0;
          $iSecondaryStart = 0;
          $iTertiaryStart = 0;

          $iDEStartIdx = 4 + 16;

          // update bitmap if there is secondary bitmap is defined
          if ($aUsedPrimaryDataElement[0] == 1) // de #1 (first bit of primary bitmap) = 1 => also use secondary bitmap
          {
            $iPrimaryStart = 1;

            $this->sSecondaryBitMap = substr($this->sISOMsgStream, 20, 16);
            $this->aISODataElements[1] .= $this->sSecondaryBitMap;

            $aUsedSecondaryDataElement = array();
            $aUsedSecondaryDataElement = $this->GetUsedDataElement($this->sSecondaryBitMap, 1, $aUsedSecondaryDataElement); // 1 = check secondary bitmap

            $nSecondaryDELen = sizeof($aUsedSecondaryDataElement);
            if ($nSecondaryDELen > 0)
            {
              $iDEStartIdx += 16;

              // update bitmap if there is tertiary bitmap is defined
              if ($aUsedSecondaryDataElement[0] == 65) // de #1 (first bit of secondary bitmap) = 1 => also use tertiary bitmap
              {
                $iSecondaryStart = 1;

                /*$this->sTertiaryBitMap = substr($this->sISOMsgStream, 36, 16);
                $this->aISODataElements[1] .= $this->sTertiaryBitMap;

                $aUsedTertiaryDataElement = array();
                $aUsedTertiaryDataElement = $this->GetUsedDataElement($this->sTertiaryBitMap, 2, $aUsedTertiaryDataElement); // 2 = check tertiary bitmap

                $nTertiaryDELen = sizeof($aUsedTertiaryDataElement);
                if ($nTertiaryDELen > 0)
                {
                  $iDEStartIdx += 16;
                }*/
              }
            }
          }

          // process primary data element
          if ($nPrimaryDELen > 0)
          {
            for($i=$iPrimaryStart; $i<$nPrimaryDELen; $i++)
            {
              $sFieldType = $aISODataElement[$this->sISOVersion][$aUsedPrimaryDataElement[$i]]["format"];
              $nFieldLen = 0;
              if ($sFieldType == "LLVAR") // LLVAR
              {
                $nVarLen = 2;
                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                $iDEStartIdx += $nVarLen;
              }
              else if ($sFieldType == "LLLVAR") // LLLVAR
              {
                $nVarLen = 3;
                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                $iDEStartIdx += $nVarLen;
              }
              else if ($sFieldType == "LLLLVAR") // LLLLVAR
              {
                $nVarLen = 4;
                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                $iDEStartIdx += $nVarLen;
              }
              else // fixed length
              {
                $nFieldLen = $aISODataElement[$this->sISOVersion][$aUsedPrimaryDataElement[$i]]["len"];
              }

              //echo "[INFO] >> i [$i] aUsedPrimaryDataElement[i] [".$aUsedPrimaryDataElement[$i]."] iDEStartIdx [$iDEStartIdx] nFieldLen [$nFieldLen]\n";
              $this->aISODataElements[$aUsedPrimaryDataElement[$i]] = substr($this->sISOMsgStream, $iDEStartIdx, $nFieldLen);
              $iDEStartIdx += $nFieldLen; // next element index
            }
          }

          // process secondary data element
          if ($nSecondaryDELen > 0)
          {
            for($i=$iSecondaryStart; $i<$nSecondaryDELen; $i++)
            {
              $sFieldType = $aISODataElement[$this->sISOVersion][$aUsedSecondaryDataElement[$i]]["format"];
              $nFieldLen = 0;
              if ($sFieldType == "LLVAR") // LLVAR
              {
                $nVarLen = 2;
                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                $iDEStartIdx += $nVarLen;
              }
              else if ($sFieldType == "LLLVAR") // LLLVAR
              {
                $nVarLen = 3;
                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                $iDEStartIdx += $nVarLen;
              }
              else if ($sFieldType == "LLLLVAR") // LLLLVAR
              {
                $nVarLen = 4;
                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                $iDEStartIdx += $nVarLen;
              }
              else // fixed length
              {
                $nFieldLen = $aISODataElement[$this->sISOVersion][$aUsedSecondaryDataElement[$i]]["len"];
              }

              //echo "[INFO] >> i [$i] aUsedSecondaryDataElement[i] [".$aUsedSecondaryDataElement[$i]."] iDEStartIdx [$iDEStartIdx] nFieldLen [$nFieldLen]\n";
              $this->aISODataElements[$aUsedSecondaryDataElement[$i]] = substr($this->sISOMsgStream, $iDEStartIdx, $nFieldLen);
              $iDEStartIdx += $nFieldLen; // next element index
            }
          }

          // process primary data element
          if ($nTertiaryDELen > 0)
          {
            for($i=$iTertiaryStart; $i<$nTertiaryDELen; $i++)
            {
              $sFieldType = $aISODataElement[$this->sISOVersion][$aUsedTertiaryDataElement[$i]]["format"];
              $nFieldLen = 0;
              if ($sFieldType == "LLVAR") // LLVAR
              {
                $nVarLen = 2;
                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                $iDEStartIdx += $nVarLen;
              }
              else if ($sFieldType == "LLLVAR") // LLLVAR
              {
                $nVarLen = 3;
                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                $iDEStartIdx += $nVarLen;
              }
              else if ($sFieldType == "LLLLVAR") // LLLLVAR
              {
                $nVarLen = 4;
                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                $iDEStartIdx += $nVarLen;
              }
              else // fixed length
              {
                $nFieldLen = $aISODataElement[$this->sISOVersion][$aUsedTertiaryDataElement[$i]]["len"];
              }

              //echo "[INFO] >> i [$i] aUsedTertiaryDataElement[i] [".$aUsedTertiaryDataElement[$i]."] iDEStartIdx [$iDEStartIdx] nFieldLen [$nFieldLen]\n";
              $this->aISODataElements[$aUsedTertiaryDataElement[$i]] = substr($this->sISOMsgStream, $iDEStartIdx, $nFieldLen);
              $iDEStartIdx += $nFieldLen; // next element index
            }
          }

          $bParsed = true;
        }
        else
        {
          // no data elements are defined -> shortest ISO8583 message (MTI + PRIMARY BITMAP ONLY)
          // so, NOTHING TO DO
          $bParsed = true;
        }
      }
      else
      {
        $this->iErrCode = -101;
        $this->sErrMsg = "Undefined ISO8583:".$this->sISOVersion." data element definition";
        //echo "[ERROR] ".$this->sErrMsg."\n";
      }
    }
    else
    {
      $this->iErrCode = -102;
      $this->sErrMsg = "length is less than minimal (20)";
      //echo "[ERROR] ".$this->sErrMsg."\n";
    }

    return $bParsed;
  } // end of Parse

  function GetParsedDataElement()
  {
    return $this->aISODataElements;
  } // end of GetParsedDataElement

  // LAST ERROR
  function GetLastErrorCode()
  {
    return $this->iErrCode;
  } // end of GetLastErrorCode

  function GetLastErrorMsg()
  {
    return $this->sErrMsg;
  } // end of GetLastErrorMsg
} // end of CISO8583Parser

class CISO8583Message
{
  // constants
  const SUPPORTED_VERSION = "1987;1993;2003";

  // internal vars
  private $aDataElement = array();
  private $sVersion = "2003";
  private $sConstructedStream = "";

  // debug
  private $bDebug = false;
  private $sLogFileName = "";

  // last error
  private $iErrCode = 0;
  private $sErrMsg = "";

  function __construct()
  {
  } // end of __construct

  function SetVersion($sVersion = "2003")
  {
    $this->sVersion = $sVersion;
    if (strpos(self::SUPPORTED_VERSION, $this->sVersion) === FALSE)
      $this->sVersion = "2003";
  } // end of SetVersion

  function SetValueForDataElement($iIdx = 2, $sValue = "")
  {
    $this->aDataElement[$iIdx] = $sValue;
  } // end of SetValueForDataElement

  function GetValueForDataElement($iIdx = 0)
  {
    $Val = NULL;
    if (@isset($this->aDataElement[$iIdx]))
    {
      $Val = $this->aDataElement[$iIdx];
    }
    return $Val;
  } // end of GetValueForDataElement

  function GetProperVersionBit()
  {
    $sBit = "2"; // 2003

    switch ($this->sVersion)
    {
      case "1987": $sBit = "0"; break;
      case "1993": $sBit = "1"; break;
    }

    return $sBit;
  } // end of GetProperVersionBit

  function GenerateBitMap()
  {
    $aBitMap = array();
    $aBitMap[0] = "";
    $aBitMap[1] = "";
    $aBitMap[2] = "";

    $iPrev = 1;
    foreach($this->aDataElement as $key=>$val)
    {
	
      if ($key > 1)
      {
        if ($key != 65)
        {
          $iBitMapIdx = ($key < 65 ? 0 : ($key > 128 ? 2 : 1)); // if $keyidx < 65 masuk primary bitmap, 65-126 secondari > 128 tertiary
		 
          if ($iBitMapIdx == 1)
          {
            if ($iPrev <= 64) $iPrev = 65;
          }
          else if ($iBitMapIdx == 2)
          {
            if ($iPrev <= 128) $iPrev = 129;
          }

          $n = $key - $iPrev - 1;
          for($i=0; $i<$n; $i++)
          {
            $aBitMap[$iBitMapIdx] .= "0";
          }
          $iPrev = $key;

          $aBitMap[$iBitMapIdx] .= "1";
        }
      }
    }

    // check bitmap existence & update related bitmap
    $sDEEmpty = str_pad("", "64", "0", STR_PAD_RIGHT);

    $aBitMap[2] = "0".$aBitMap[2];
    $aBitMap[2] = str_pad($aBitMap[2], 64, "0", STR_PAD_RIGHT);
    if ($aBitMap[2] != $sDEEmpty)
    {
      $aBitMap[1] = "1".$aBitMap[1];
    }
    else
    {
      $aBitMap[2] = "";
      $aBitMap[1] = "0".$aBitMap[1];
    }

    $aBitMap[1] = str_pad($aBitMap[1], 64, "0", STR_PAD_RIGHT);
    if ($aBitMap[1] != $sDEEmpty)
    {
      $aBitMap[0] = "1".$aBitMap[0];
    }
    else
    {
      $aBitMap[1] = "";
      $aBitMap[0] = "0".$aBitMap[0];
    }

    //echo "[DEBUG] bitmap bin [".print_r($aBitMap, true)."]\n";

    for($i=0; $i<3; $i++)
    {
      if ($aBitMap[$i] != "")
        $aBitMap[$i] = str_pad($aBitMap[$i], 64, "0", STR_PAD_RIGHT);
      //echo "[DEBUG] >> bitmap bin $i [".$aBitMap[$i]."] [".strlen($aBitMap[$i])."]\n";
      $aBitMap[$i] = CBaseConverter::_ascii_bin_to_hexa($aBitMap[$i]);
    }

    //echo "[DEBUG] bitmap hexa [".print_r($aBitMap, true)."]\n";

    return $aBitMap;
  } // end of GenerateBitMap

  function ConstructStream()
  {
    global $aISODataElement;
    $bOK = false;

    if (@ksort($this->aDataElement))
    {
      // array aDataElement is sorted by its index

      // construct stream
      $aBitMap = $this->GenerateBitMap();
      $sMTI = (@isset($this->aDataElement[0]) ? sprintf("%04s", substr($this->aDataElement[0], 0, 4)) : NULL);
      if ($sMTI != NULL)
      {
        if (strlen($sMTI) > 3) $sMTI = substr($sMTI, 1, 3);
      }

      if ($sMTI != NULL)
      {
        $sStream = $sMTI.$aBitMap[0].($aBitMap[1] != "" ? $aBitMap[1] : "");

        // construct data element
        foreach($this->aDataElement as $key=>$val)
        {
          if (($key != 65) && ($key >= 2) && ($key <= 128)) // 65 is tertiary bitmap
          {
            // determine padding character and orientation
            $sType = $aISODataElement[$this->sVersion][$key]["type"];
            $cPadChar = " ";
            $bLeftPadding = false;
            if (strtolower($sType) == "n")
            {
              $cPadChar = "0";
              $bLeftPadding = true;
            }

            // validate value
            $sValue = (@isset($val) ? $val  : "");
            $sValue = @substr($sValue, 0, $aISODataElement[$this->sVersion][$key]["len"]); // ensure assigned value is not longer than its maximum length

            // check format
            $bFixedLength = false;
            $sFormat = strtoupper($aISODataElement[$this->sVersion][$key]["format"]);
            if ($sFormat == "LLLLVAR")
              $sStream .= sprintf("%04d", strlen($sValue));
            else if ($sFormat == "LLLVAR")
              $sStream .= sprintf("%03d", strlen($sValue));
            else if ($sFormat == "LLVAR")
              $sStream .= sprintf("%02d", strlen($sValue));
            else
              $bFixedLength = true;

            // append value to stream
            if ($bFixedLength)
              $sStream .= str_pad($sValue, $aISODataElement[$this->sVersion][$key]["len"], $cPadChar, ($bLeftPadding ? STR_PAD_LEFT : STR_PAD_RIGHT));
            else
              $sStream .= $sValue;
          }
          else // 65 = tertiary bitmap
          {
            $sStream .= $aBitMap[2];
          }
        }

        $sStream = $this->GetProperVersionBit().$sStream;
        $this->sConstructedStream = $sStream;

        $bOK = true;
      }
      else
      {
        $this->iErrCode = -011;
        $this->sErrMsg = "MTI is not defined";
      }
    }
    else
    {
      $this->iErrCode = -001;
      $this->sErrMsg = "Unable to sort internal arrays";
    }

    return $bOK;
  } // end of ConstructStream

  function GetConstructedStream()
  {
    return $this->sConstructedStream;
  } // end of GetConstructedStream

  // LAST ERROR
  function GetLastErrorCode()
  {
    return $this->iErrCode;
  } // end of GetLastErrorCode

  function GetLastErrorMsg()
  {
    return $this->sErrMsg;
  } // end of GetLastErrorMsg
} // end of CISO8583Message


// DRIVER
$iTimeStart = microtime(true);

//print_r($aISODataElement);

// ----------------------
// DRIVER CISO8583Message
// ----------------------
/*
$sStream = "";
$m = new CISO8583Message();
$m->SetValueForDataElement(0, "1410");
$m->SetValueForDataElement(2, "53501");
$m->SetValueForDataElement(39, "00112"); // will be truncated to max length (max = 4 char)
$m->SetValueForDataElement(48, "10000V3");
$m->SetValueForDataElement(41, "1234567890123456");
$m->SetValueForDataElement(68, "999999999");
$m->SetValueForDataElement(128, "8888");
$m->SetValueForDataElement(64, "4444");
$m->SetValueForDataElement(32, "2222");
//$m->SetVersion("1987");
date_default_timezone_set("Asia/Jakarta");
$m->SetValueForDataElement(12, strftime("%Y%m%d%H%M%S", time()));
//$m->SetVersion("1987");
if ($m->ConstructStream())
{
  $sStream = $m->GetConstructedStream();
  echo "[INFO] Stream [$sStream] [".strlen($sStream)."]\n";
}
else
{
  echo "[ERROR] Unable to construct message. (".sprintf("#%d:%s", $m->GetLastErrorCode(), $m->GetLastErrorMsg()).")\n";
}

// ---------------------
// DRIVER CISO8583Parser
// ---------------------
$sStream = $sStream;
//$sStream = "280000100000010100002008050207230000200710000D3";
//$sStream = "2810001000000301000020080502072300000000100710000D3";
//$sStream = "21004030004100010000055350110000000000120080502072300601507011000001910000D3530000000006";
//$sStream = "2110503000410201000005535013600000000100000100000000001200805020723006015070110000000023210000D3530000000001101E5BA37AC52083904FDEF185BE297009ASUBSCRIBER NAME          53585021-3754678    R1  000000450000000000200804200804202008032100000100000D00000000000000000000000000000001745000017550000000000000000000000000000000000";
//$sStream = "220050300041000100000553501360000000010000010000000000120080502072300601507011000026510000D35300000000011101E5BA37AC52083904FDEF185BE297009A874CC544BF2663E46D738268518F8F62SUBSCRIBER NAME          53585021-3754678    R1  000000450000000000200804200804202008032100000100000D00000000000000000000000000000001745000017550000000000000000000000000000000000";
//$sStream = "221050320041020100000553501360000000010000010000000000120080502072300200805026015070110000000026510000D35300000000011101E5BA37AC52083904FDEF185BE297009A874CC544BF2663E46D738268518F8F62SUBSCRIBER NAME          53585021-3754678    R1  000000450000000000200804200804202008032100000100000D00000000000000000000000000000001745000017550000000000000000000000000000000000";
//$sStream = "240050300041000101000553501360000000010000010000000000120080502072300601507011000026510000D35300000000011101E5BA37AC52083904FDEF185BE297009A874CC544BF2663E46D738268518F8F62SUBSCRIBER NAME          53585021-3754678    R1  000000450000000000200804200804202008032100000100000D00000000000000000000000000000001745000017550000000000000000000000000000000000372200100000000001200805020723000110000";
//$sStream = "2410503000410201010005535013600000000100000100000000001200805020723006015070110000000026510000D35300000000011101E5BA37AC52083904FDEF185BE297009A874CC544BF2663E46D738268518F8F62SUBSCRIBER NAME          53585021-3754678    R1  000000450000000000200804200804202008032100000100000D00000000000000000000000000000001745000017550000000000000000000000000000000000372200100000000001200805020723000110000";
$o = new CISO8583Parser($sStream);
if ($o->Parse())
{
  $a = $o->GetParsedDataElement();
  echo "<br>[INFO] RESULT:\n";
  print_r($a);

  // check bitmap
  if (@isset($a[1]))
    echo "<br>ascii binary bitmap [".CBaseConverter::_ascii_hexa_to_bin($a[1])."]\n";
}
else
{
  echo "[ERROR] Unable to parse message stream. It might not a valid ISO8583 message stream. (".sprintf("#%d:%s", $o->GetLastErrorCode(), $o->GetLastErrorMsg()).")\n";
}

// TIMER

$iTimeEnd = microtime(true);
$iTimeExec = $iTimeEnd - $iTimeStart;

echo sprintf("<br>Executed in %.8f s = %.8f ms\n", $iTimeExec, $iTimeExec * 1000);*/

?>
