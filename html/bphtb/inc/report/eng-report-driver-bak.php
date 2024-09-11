<?php
include 'eng-report.php';
//file not found
echo "coba membuat dari template yang tidak ada<br />\n";
$re0 = new ReportEngine("template/elpost/electricity-receipt.xm");
echo "LastErrorMsg :".$re0->LastErrorMsg."<br />\n";

echo "<br />\n";
echo "coba membuat dari template yang ada<br />\n";
$re1 = new ReportEngine("template/elpost/electricity-receipt.xml");
echo "LastErrorMsg :".$re1->LastErrorMsg."<br />\n";
$re1->PrintHTML($HTML);
echo "Isi :".$HTML."<br />\n";

echo "Isi dolo";
$values = array (
	"BANK" => "tesBanks",
	"TGLBY" => "tesTanggal"
);
$re1->ApplyTemplateValue($values);
$re1->PrintHTML($HTML);
echo "Isi :".$HTML."<br />\n";

echo "Simon Ganteng!";

?>