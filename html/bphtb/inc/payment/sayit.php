<?php

function Intg2Str($iNumber) {
    $sBuf = "";
    switch ($iNumber) {
        case 0 : $sBuf = "nol";
            break;
        case 1 : $sBuf = "satu";
            break;
        case 2 : $sBuf = "dua";
            break;
        case 3 : $sBuf = "tiga";
            break;
        case 4 : $sBuf = "empat";
            break;
        case 5 : $sBuf = "lima";
            break;
        case 6 : $sBuf = "enam";
            break;
        case 7 : $sBuf = "tujuh";
            break;
        case 8 : $sBuf = "delapan";
            break;
        case 9 : $sBuf = "sembilan";
            break;
        case 10 : $sBuf = "sepuluh";
            break;
        case 11 : $sBuf = "sebelas";
            break;
        case 12 : $sBuf = "dua belas";
            break;
        case 13 : $sBuf = "tiga belas";
            break;
        case 14 : $sBuf = "empat belas";
            break;
        case 15 : $sBuf = "lima belas";
            break;
        case 16 : $sBuf = "enam belas";
            break;
        case 17 : $sBuf = "tujuh belas";
            break;
        case 18 : $sBuf = "delapan belas";
            break;
        case 19 : $sBuf = "sembilan belas";
            break;
    }

    return $sBuf;
}

// end of Intg2Str

function SayTens($iNumber) {
    $sBuf = '';

    $iResult = intval($iNumber / 10);
    if ($iNumber >= 20) {
        $sBuf .= sprintf("%s puluh", Intg2Str($iResult));
        $iNumber %= 10;

        if (($iNumber >= 1) && ($iNumber <= 9))
            $sBuf .= sprintf(" %s", Intg2Str($iNumber));
    }
    else if (($iNumber >= 0) && ($iNumber <= 19))
        $sBuf .= Intg2Str($iNumber);

    return trim($sBuf);
}

// end of SayTens

function SayHundreds($iNumber) {
    $sBuf = '';
    $iResult = 0;

    $iResult = intval($iNumber / 100);
    if (($iResult > 0) && ($iResult != 1))
        $sBuf .= sprintf("%s ratus ", Intg2Str($iResult));
    else if ($iResult == 1)
        $sBuf = "seratus ";
    $iNumber %= 100;

    if ($iNumber > 0)
        $sBuf .= SayTens($iNumber);

    return trim($sBuf);
}

// end of SayHundreds

function SayInIndonesian($iNumber) {
    $iResult = 0;
    $sBuf = '';

    if ($iNumber == 0)
        $sBuf = 'nol';
    else {
        // handling large number > 2 milyar
        $sBufL = '';
        $sNumber = strval($iNumber);
        $nNumberLen = strlen($sNumber);
        if ($nNumberLen > 9) { // large number
            $sNewNumber = substr($sNumber, $nNumberLen - 9, 9);
            //echo "sNewNumber [$sNewNumber]\n";
            $iNumber = intval($sNewNumber);
            //echo "iNumber [$iNumber]\n";
            // trilyun
            $iLargeNumber = intval(substr($sNumber, 0, $nNumberLen - 9));
            //echo "iLargeNumber [$iLargeNumber]\n";
            $iResult = intval($iLargeNumber / 1000);
            //echo "iResult [$iResult]\n";
            if ($iResult > 0)
                $sBufL = sprintf("%s trilyun ", SayHundreds($iResult));

            // milyar
            $iLargeNumber %= 1000;
            $iResult = $iLargeNumber;
            if ($iResult > 0)
                $sBufL .= sprintf("%s milyar ", SayHundreds($iResult));
        }
        //echo "[$sBufL]\n";
        // miliar
        $iResult = intval($iNumber / 1000000000);
        if ($iResult > 0)
            $sBuf .= sprintf("%s miliar ", SayHundreds($iResult));
        $iNumber %= 1000000000;
        // juta
        $iResult = intval($iNumber / 1000000);
        if ($iResult > 0)
            $sBuf .= sprintf("%s juta ", SayHundreds($iResult));
        $iNumber %= 1000000;
        // ribu
        $iResult = intval($iNumber / 1000);
        if (($iResult > 0) && ($iResult != 1))
            $sBuf .= sprintf("%s ribu ", SayHundreds($iResult));
        else if ($iResult >= 1)
            $sBuf .= "seribu ";
        $iNumber %= 1000;
        // ratus
        if ($iNumber > 0)
            $sBuf .= SayHundreds($iNumber);

        // final
        //echo "[$sBufL] [$sBuf]\n";
        $sBuf = $sBufL . $sBuf;
    }

    return trim($sBuf);
}

// end of SayInIndonesian

function SayInIndonesianKoma($iNumber) {
    $nomor = explode(",", $iNumber);
    $iNumber = $nomor[0];
    $koma = $nomor[1];
    $iResult = 0;
    $sBuf = '';

    if ($iNumber == 0)
        $sBuf = 'nol';
    else {
        // handling large number > 2 milyar
        $sBufL = '';
        $sNumber = strval($iNumber);
        $nNumberLen = strlen($sNumber);
        if ($nNumberLen > 9) { // large number
            $sNewNumber = substr($sNumber, $nNumberLen - 9, 9);
            //echo "sNewNumber [$sNewNumber]\n";
            $iNumber = intval($sNewNumber);
            //echo "iNumber [$iNumber]\n";
            // trilyun
            $iLargeNumber = intval(substr($sNumber, 0, $nNumberLen - 9));
            //echo "iLargeNumber [$iLargeNumber]\n";
            $iResult = intval($iLargeNumber / 1000);
            //echo "iResult [$iResult]\n";
            if ($iResult > 0)
                $sBufL = sprintf("%s trilyun ", SayHundreds($iResult));

            // milyar
            $iLargeNumber %= 1000;
            $iResult = $iLargeNumber;
            if ($iResult > 0)
                $sBufL .= sprintf("%s milyar ", SayHundreds($iResult));
        }
        //echo "[$sBufL]\n";
        // miliar
        $iResult = intval($iNumber / 1000000000);
        if ($iResult > 0)
            $sBuf .= sprintf("%s miliar ", SayHundreds($iResult));
        $iNumber %= 1000000000;
        // juta
        $iResult = intval($iNumber / 1000000);
        if ($iResult > 0)
            $sBuf .= sprintf("%s juta ", SayHundreds($iResult));
        $iNumber %= 1000000;
        // ribu
        $iResult = intval($iNumber / 1000);
        if (($iResult > 0) && ($iResult != 1))
            $sBuf .= sprintf("%s ribu ", SayHundreds($iResult));
        else if ($iResult >= 1)
            $sBuf .= "seribu ";
        $iNumber %= 1000;
        // ratus
        if ($iNumber > 0)
            $sBuf .= SayHundreds($iNumber);

        // final
        $sKoma = "";
        if ($koma > 0) {
            $sKoma = " koma ";
            $sKoma .= Intg2Str($koma[0]) . " ";
            $sKoma .= Intg2Str($koma[1]) . " ";
        }
        $sBuf = $sBufL . $sBuf . $sKoma;
    }

    return trim($sBuf);
}

/*
  echo "*** SayInIndonesian Driver ***\n";
  echo "> terbilang [".SayInIndonesian(0)."]\n";
  echo "> terbilang [".SayInIndonesian(1)."]\n";
  echo "> terbilang [".SayInIndonesian(9)."]\n";
  echo "> terbilang [".SayInIndonesian(10)."]\n";
  echo "> terbilang [".SayInIndonesian(11)."]\n";
  echo "> terbilang [".SayInIndonesian(13)."]\n";
  echo "> terbilang [".SayInIndonesian(100)."]\n";
  echo "> terbilang [".SayInIndonesian(101)."]\n";
  echo "> terbilang [".SayInIndonesian(150)."]\n";
  echo "> terbilang [".SayInIndonesian(200)."]\n";
  echo "> terbilang [".SayInIndonesian(500)."]\n";
  echo "> terbilang [".SayInIndonesian(978)."]\n";
  echo "> terbilang [".SayInIndonesian(1000)."]\n";
  echo "> terbilang [".SayInIndonesian(1001)."]\n";
  echo "> terbilang [".SayInIndonesian(1080)."]\n";
  echo "> terbilang [".SayInIndonesian(1100)."]\n";
  echo "> terbilang [".SayInIndonesian(1999)."]\n";
  echo "> terbilang [".SayInIndonesian(235678)."]\n";
  echo "> terbilang [".SayInIndonesian(2356789)."]\n";
  echo "> terbilang [".SayInIndonesian(10060001)."]\n";
  echo "> terbilang [".SayInIndonesian(11060001)."]\n";
  echo "> terbilang [".SayInIndonesian(17060001)."]\n";
  echo "> terbilang [".SayInIndonesian(22060001)."]\n";
  echo "> terbilang [".SayInIndonesian('999911345678901')."]\n";
  echo "*** end of SayInIndonesian Driver ***\n";
 */
#echo "> terbilang [".SayInIndonesian('118392388')."]\n"; #error
?>
