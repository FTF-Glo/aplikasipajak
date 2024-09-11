<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB/print', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/report/eng-report.php");

class printDouble extends ReportEngine
{
    public $firstSide = null;
    public $secondSide = null;

    public $toHtml = false;
    public $toBase64 = false;

    public $totalChars = 136;
    public $totalCharsPerSide = (136 / 2);
    public $extraMiddleSpace = 2;
    public $maxCharsPerSide = 65;

    public $skipBreakline = 0;

    public function newApplyTemplateValue($value1, $value2)
    {
        $this->ApplyTemplateValue($value1, true);
        $this->firstSide = $this->ReportObject;
        if ($value2) {
            $this->ApplyTemplateValue($value2, true);
            $this->secondSide = $this->ReportObject;
        } else {
            $this->secondSide = [];
        }

        return $this->newOutput();
    }

    public function returnBase64()
    {
        $this->toHtml = false;
        $this->toBase64 = true;
    }

    public function returnHtml()
    {
        $this->toBase64 = false;
        $this->toHtml = true;
    }

    public function returnText()
    {
        $this->toBase64 = false;
        $this->toHtml = false;
    }


    public function newOutput()
    {
        // $strHTML = $this->toHtml ? $this->getHtmlHead() : "";
        // $strHTML .= $this->toHtml ? "<pre style=\"font-family: 'Courier New', Courier, monospace\">" : "";
        $strHTML = '';
        $breakLine =  ($this->toHtml ? '<br />' : chr(13) . chr(10));

        $lineIndex = 0;
        foreach ($this->firstSide->line as $line) {
            $charCountPerLine = 0;
            $totalItems = count($line->item);
            $loopItems = 0;
            $itemIndex = 0;
            $strsisaFirst = '';
            $strsisaSecond = '';

            foreach ($line->item as $item) {
                if ($item["text"] != "") {
                    $loopItems++;
                    $strtext = $this->getStrtext($item);
                    $strtextLen = strlen($strtext);


                    $strHTML .= $this->toHtml ? "<span>" : "";

                    $charCountPerLine += $strtextLen;
                    if ($loopItems == $totalItems) {
                        // $_strtext = $charCountPerLine > $this->maxCharsPerSide ? substr($strtext, 0, $this->maxCharsPerSide) : $strtext;

                        $_strtext = $strtext;

                        if (isset($item["cutable"]) && $item["cutable"] == true) {
                            $strtextCutted = $this->potongString($_strtext, '');
                            $strsisaFirst = $strtextCutted['sisa'];
                            if ($strsisaFirst != '') {
                                $_strtext = $strtextCutted['str'];
                            }
                        }

                        $strtext = $_strtext;

                        $strtext = str_pad($strtext, ($this->totalCharsPerSide - $charCountPerLine + $strtextLen + $this->extraMiddleSpace), " ", STR_PAD_RIGHT);
                        if ($this->secondSide) foreach ($this->secondSide->line[$lineIndex] as $secondSideItem) {
                            // $strtext .= $this->getStrtext($secondSideItem);

                            $_strtext = $this->getStrtext($secondSideItem);

                            if (isset($item["cutable"]) && $item["cutable"] == true) {
                                $strtextCutted = $this->potongString($_strtext, '');
                                $strsisaSecond = $strtextCutted['sisa'];
                                if ($strsisaSecond != '') {
                                    $_strtext = $strtextCutted['str'];
                                }
                            }

                            $strtext .= $_strtext;
                        }
                    }


                    $strHTML .= $strtext;
                    $strHTML .= $this->toHtml ? "</span>" : "";
                }
                $itemIndex++;
            }

            if (!isset($line['item']) && ($strsisaFirst != '' || $strsisaSecond != '')) {
                $strtext = '';
                $strtextLenFirst = strlen($strsisaFirst);

                $strtext = $breakLine . $strsisaFirst;

                $strtext .= str_pad('', ($this->totalCharsPerSide - $strtextLenFirst + $this->extraMiddleSpace), " ", STR_PAD_RIGHT);
                $strtext .= $strsisaSecond;

                if ($strtext != '') {
                    $strHTML .= $this->toHtml ? "<span>" : "";
                    $strHTML .= $strtext;
                    $strHTML .= $this->toHtml ? "</span>" : "";
                }

                $strsisaFirst = '';
                $strsisaSecond = '';
            }

            if ($this->skipBreakline < 1) {
                $strHTML .=  $breakLine;
                $this->skipBreakline = 0;
            } else {
                $this->skipBreakline--;
            }

            // $strHTML .= $this->toHtml ? '<br />' : chr(13) . chr(10);

            $lineIndex++;
        }

        return $this->toBase64 ? base64_encode($strHTML) : $strHTML;
    }

    public function getStrtext($item)
    {
        $itemtext = "";
        if (intval($item["repeat"]) > 1) {
            $itemtext = str_repeat($item["text"], intval($item["repeat"]));
        } else {
            $itemtext = $item["text"];
        }

        if (isset($item["max"]) && intval($item["max"]) > 0) {
            $itemtext = substr($itemtext, 0, intval($item["max"]));
        }

        if (intval($item["center"]) > 0) {
            $tlength = strlen($itemtext);
            if ($tlength >= intval($item["center"])) {
                $strtext = $itemtext;
            } else {
                $lpad = floor((intval($item["center"]) - $tlength) / 2) + $tlength;
                $rpad = ceil((intval($item["center"]) - $tlength) / 2) + $tlength;
                $strtext = str_pad(str_pad($itemtext, $lpad, " ", STR_PAD_LEFT), $rpad, " ", STR_PAD_RIGHT);
            }
        } else if (intval($item["lpad"]) <= 0 && intval($item["rpad"]) <= 0) {
            $strtext = $itemtext;
        } else if (intval($item["lpad"]) > 0 && intval($item["rpad"]) <= 0) {
            $strtext = str_pad($itemtext, intval($item["lpad"]), " ", STR_PAD_LEFT);
        } else if (intval($item["lpad"]) <= 0 && intval($item["rpad"]) > 0) {
            $strtext = str_pad($itemtext, intval($item["rpad"]), " ", STR_PAD_RIGHT);
        } else if (intval($item["lpad"]) < intval($item["rpad"])) {
            $strtext = str_pad(str_pad($itemtext, intval($item["lpad"]), " ", STR_PAD_LEFT), intval($item["rpad"]), " ", STR_PAD_RIGHT);
        } else {
            $strtext = str_pad(str_pad($itemtext, intval($item["rpad"]), " ", STR_PAD_RIGHT), intval($item["lpad"]), " ", STR_PAD_LEFT);
        }

        return $strtext;
    }

    public function getHtmlHead()
    {
        $html = "";
        $html .= "<!DOCTYPE html>";
        $html .= "<html>";
        $html .= "<head>";
        $html .= "<meta charset=\"UTF-8\">";;
        $html .= "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">";
        $html .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";
        $html .= "<title>SPPT</title>";
        $html .= "<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css\" integrity=\"sha512-NhSC1YmyruXifcj/KFRWoC561YpHpc5Jtzgvbuzx5VozKpWvQ+4nXhPdFgmx8xqexRcpAglTj9sIBWINXa8x5w==\" crossorigin=\"anonymous\" />";
        $html .= "<style>";
        $html .= $this->getHtmlStyles();
        $html .= "</style>";
        $html .= "</head>";
        $html .= "<body>";
        $html .= "<pre style=\"font-family: 'Courier New', Courier, monospace\">";

        return $html;
    }

    public function getHtmlFoot()
    {
        $html = "</pre>";
        $html .= "</body>";
        $html .= "</html>";

        return $html;
    }

    public function getHtmlStyles()
    {
        $styles[] = '@page { size: 37cm 28.5cm; }';

        return implode("\r\n", $styles);
    }

    public function potongString($str, $breakLine)
    {
        $max = $this->maxCharsPerSide;
        $_str = '';
        $_str_sisa = '';

        if (strlen($str) > $max) {
            $_str = substr($str, 0, $max);
            if (substr($str, ($max - 1), 1) != ' ') {
                $lastSpacePos = strrpos($_str, ' ');
                $_str = substr($_str, 0, $lastSpacePos);
            }
            $_str_sisa = substr(trim(substr($str, strlen($_str))), 0, $max);

            $this->skipBreakline = 1;
        } else {
            $_str = $str;
        }

        return [
            'str' => $_str,
            'sisa' => $_str_sisa
        ];
    }
}
