<?php
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'notaris', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 014');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 014', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetLeftMargin(30);
//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// IMPORTANT: disable font subsetting to allow users editing the document
$pdf->setFontSubsetting(false);

// set font
$pdf->SetFont('helvetica', '', 10, '', false);

// add a page
$pdf->AddPage();

/*
It is possible to create text fields, combo boxes, check boxes and buttons.
Fields are created at the current position and are given a name.
This name allows to manipulate them via JavaScript in order to perform some validation for instance.
*/

// set default form properties
$pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 255), 'strokeColor'=>array(100,100,100)));

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 5, 'Form Surat Setoran Pajak Daerah', 0, 1, 'C');
$pdf->Cell(0, 5, 'Perolehan Hak Atas Tanah dan Bangunan (SSPD-BPHTB)', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('helvetica', '', 12);

// Nama Wajib Pajak
$pdf->Cell(45, 5, 'Nama Wajib Pajak');
$pdf->TextField('nama', 60, 5);
$pdf->Ln(6);

// NPWP
$pdf->Cell(45, 5, 'NPWP');
$pdf->TextField('npwp', 60, 5);
$pdf->Ln(6);

// Alamat
$pdf->Cell(45, 5, 'Alamat');
$pdf->TextField('alamat', 60, 18, array('multiline'=>true));
$pdf->Ln(19);

// Kelurahan/Desa
$pdf->Cell(45, 5, 'Kelurahan/Desa');
$pdf->TextField('kelurahan', 60, 5);
$pdf->Ln(6);

// RT/RW
$pdf->Cell(45, 5, 'RT/RW');
$pdf->TextField('rtrw', 60, 5);
$pdf->Ln(6);

// Kecamatan
$pdf->Cell(45, 5, 'Kecamatan');
$pdf->TextField('kecamatan', 60, 5);
$pdf->Ln(6);

// Kabupaten
$pdf->Cell(45, 5, 'Kabupaten/Kota');
$pdf->TextField('kabupaten', 60, 5);
$pdf->Ln(6);

// kode pos
$pdf->Cell(45, 5, 'Kode POS');
$pdf->TextField('kodepos', 60, 5);
$pdf->Ln(10);

// Nama Wajib Pajak
$pdf->Cell(45, 5, 'Nama Wajib Pajak');
$pdf->TextField('nama', 60, 5);
$pdf->Ln(6);

// NPWP
$pdf->Cell(45, 5, 'NPWP');
$pdf->TextField('npwp', 60, 5);
$pdf->Ln(6);

// Alamat
$pdf->Cell(45, 5, 'Alamat');
$pdf->TextField('alamat', 60, 18, array('multiline'=>true));
$pdf->Ln(19);

// Kelurahan/Desa
$pdf->Cell(45, 5, 'Kelurahan/Desa');
$pdf->TextField('kelurahan', 60, 5);
$pdf->Ln(6);

// RT/RW
$pdf->Cell(45, 5, 'RT/RW');
$pdf->TextField('rtrw', 60, 5);
$pdf->Ln(6);

// Kecamatan
$pdf->Cell(45, 5, 'Kecamatan');
$pdf->TextField('kecamatan', 60, 5);
$pdf->Ln(6);

// Kabupaten
$pdf->Cell(45, 5, 'Kabupaten/Kota');
$pdf->TextField('kabupaten', 60, 5);
$pdf->Ln(6);

// kode pos
$pdf->Cell(45, 5, 'Kode POS');
$pdf->TextField('kodepos', 60, 5);
$pdf->Ln(10);
// Drink
$pdf->Cell(35, 5, 'Drink:');
$pdf->RadioButton('drink', 5, array(), array(), 'Water');
$pdf->Cell(35, 5, 'Water');
$pdf->Ln(6);
$pdf->Cell(35, 5, '');
$pdf->RadioButton('drink', 5, array(), array(), 'Beer', true);
$pdf->Cell(35, 5, 'Beer');
$pdf->Ln(6);
$pdf->Cell(35, 5, '');
$pdf->RadioButton('drink', 5, array(), array(), 'Wine');
$pdf->Cell(35, 5, 'Wine');
$pdf->Ln(10);

// Listbox
$pdf->Cell(35, 5, 'List:');
$pdf->ListBox('listbox', 60, 15, array('', 'item1', 'item2', 'item3', 'item4', 'item5', 'item6', 'item7'), array('multipleSelection'=>'true'));
$pdf->Ln(20);

// Adress
$pdf->Cell(35, 5, 'Address:');
$pdf->TextField('address', 60, 18, array('multiline'=>true));
$pdf->Ln(19);

// E-mail
$pdf->Cell(35, 5, 'E-mail:');
$pdf->TextField('email', 50, 5);
$pdf->Ln(6);

// Newsletter
$pdf->Cell(35, 5, 'Newsletter:');
$pdf->CheckBox('newsletter', 5, true, array(), array(), 'OK');
$pdf->Ln(10);

// Date of the day
$pdf->Cell(35, 5, 'Date:');
$pdf->TextField('date', 30, 5, array(), array('v'=>date('Y-m-d'), 'dv'=>date('Y-m-d')));
$pdf->Ln(10);

$pdf->SetX(50);

// Button to validate and print
$pdf->Button('print', 30, 10, 'Print', 'Print()', array('lineWidth'=>2, 'borderStyle'=>'beveled', 'fillColor'=>array(128, 196, 255), 'strokeColor'=>array(64, 64, 64)));

// Reset Button
$pdf->Button('reset', 30, 10, 'Reset', array('S'=>'ResetForm'), array('lineWidth'=>2, 'borderStyle'=>'beveled', 'fillColor'=>array(128, 196, 255), 'strokeColor'=>array(64, 64, 64)));

// Submit Button
$pdf->Button('submit', 30, 10, 'Submit', array('S'=>'SubmitForm', 'F'=>'http://localhost/printvars.php', 'Flags'=>array('ExportFormat')), array('lineWidth'=>2, 'borderStyle'=>'beveled', 'fillColor'=>array(128, 196, 255), 'strokeColor'=>array(64, 64, 64)));


// Form validation functions
$js = <<<EOD
function CheckField(name,message) {
    var f = getField(name);
    if(f.value == '') {
        app.alert(message);
        f.setFocus();
        return false;
    }
    return true;
}
function Print() {
    if(!CheckField('firstname','First name is mandatory')) {return;}
    if(!CheckField('lastname','Last name is mandatory')) {return;}
    if(!CheckField('gender','Gender is mandatory')) {return;}
    if(!CheckField('address','Address is mandatory')) {return;}
    print();
}
EOD;

// Add Javascript code
$pdf->IncludeJS($js);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_014.pdf', 'I');
?>
