<?php
// $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB/print', '', dirname(__FILE__))) . '/';
require_once('printDoubleClass.php');

class printSingle extends printDouble
{
    public $skipBreakline = 0;
    
    public function newApplyTemplateValue($value1, $value2)
    {
        $this->ApplyTemplateValue($value1, true);
        $this->firstSide = $this->ReportObject;
        $this->secondSide = [];
        
        return $this->newOutput();
    }

    public function newOutput()
    {
        // $strHTML = $this->toHtml ? $this->getHtmlHead() : "";
        // $strHTML .= $this->toHtml ? "<pre style=\"font-family: 'Courier New', Courier, monospace\">" : "";
        $strHTML = '';
        $breakLine =  ($this->toHtml ? '<br />' : chr(13) . chr(10));

        $lineIndex = 0;
        foreach ($this->firstSide->line as $line) {
            $loopItems = 0;
            $itemIndex = 0;
            foreach ($line->item as $item) {
                if ($item["text"] != "") {
                    $loopItems++;
                    $strtext = $this->getStrtext($item);
                    $strtextLen = strlen($strtext);
                    if ($strtextLen > $this->maxCharsPerSide) {
                        if (isset($item["cutable"]) && $item["cutable"] == true) {
                            $strtext = $this->potongString($strtext, $breakLine);
                        }else{
                            $strtext = substr($strtext,0, $this->maxCharsPerSide);
                        }
                    }

                    $strHTML .= $this->toHtml ? "<span>" : "";
                    $strHTML .= $strtext;
                    $strHTML .= $this->toHtml ? "</span>" : "";
                }
                $itemIndex++;
            }


            if ($this->skipBreakline < 1) {
                $strHTML .=  $breakLine;
                $this->skipBreakline = 0;
            } else {
                $this->skipBreakline--;
            }

            $lineIndex++;
        }

        return $this->toBase64 ? base64_encode($strHTML) : $strHTML;
    }

    public function potongString($str, $breakLine)
    {
        $max = $this->maxCharsPerSide;
        $_str = '';

        if (strlen($str) > $max) {
            $_str = substr($str, 0, $max);
            if (substr($str, ($max - 1), 1) != ' ') {
                $lastSpacePos = strrpos($_str, ' ');
                $_str = substr($_str, 0, $lastSpacePos);
            }
            $_str_sisa = trim(substr($str, strlen($_str)));

            if ($_str_sisa) {
                $_str .= ($breakLine . $this->potongString($_str_sisa, $breakLine));
            }

            $this->skipBreakline++;
        } else {
            $_str = $str;
        }

        return $_str;
    }
}
