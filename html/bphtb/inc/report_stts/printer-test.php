<?php

require_once("eng-report.php");

$engReport = new ReportEngine("template/pbb/pbb-receipt.xml");
$engReport->Print2File("/tmp/bbb.txt");

?>
