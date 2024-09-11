<?php
include 'eng-report-table.php';
$HValues = array(
	'OPERATOR' => 'tesOperator',
	'PP_NAME' => 'tesPPName',
	'PP_ID' => 'tesPPID'
	);
$BValues = array(
	array (
		'NO_REC' => 1,
		'AREA_ID' => 52,
		'AREA_REC' => 100
		),
	array (
		'NO_REC' => 2,
		'AREA_ID' => 53,
		'AREA_REC' => 200
		)
);
$FValues = array(
	'CUSTOM_NOTES' => 'tesNotes',
	'RP_TAG' => 'tesRpTagihan'	
);

//file not found
echo "coba membuat dari template yang tidak ada<br />\n";
$ret0 = new ReportEngineTable("template/elpost/electricity-report-daily.xm", $HValues, $BValues, $FValues);
echo "LastErrorMsg :".$ret0->LastErrorMsg."<br />\n";

echo "<br />\n";

echo "coba membuat dari template yang ada<br />\n";
$ret1 = new ReportEngineTable("template/elpost/electricity-report-daily.xml", $HValues, $BValues, $FValues);
echo "LastErrorMsg :".$ret1->LastErrorMsg."<br />\n";
$ret1->PrintHTML($HTML);
echo "Isi HTML:".$HTML."<br />\n";

$ret1->Print2TXT($TXT);
echo "Isi TXT:".$TXT."<br />\n";

echo "Simon Ganteng!";

?>