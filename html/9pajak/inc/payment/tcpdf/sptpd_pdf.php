<?php
require_once 'tcpdf.php';

class SPTPD_PDF extends TCPDF {
	
	// Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        $this->SetAlpha(0.6);
        // Page number
        $this->Cell(0, 10, 'Lembar '.$this->getAliasNumPage().' dari '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>
