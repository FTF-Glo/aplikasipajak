<?

require_once("eng-report.php");

$engReport = new ReportEngine("template/elpost/electricity-receipt.xml");
$engReport->Print2File("bbb.txt");

?>
