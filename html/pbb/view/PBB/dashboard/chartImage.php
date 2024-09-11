<?php
require_once "phplot.php";//inc/PBB/dashboard/
$data = array(array($_REQUEST['label_1'],$_REQUEST['value_1']), array($_REQUEST['label_2'],$_REQUEST['value_2']));
$plot2 = new PHPlot(160,170);
$plot2->SetTitle($_REQUEST['title']);
$plot2->SetPlotType('pie');
$plot2->SetDataType('text-data-single');
$plot2->SetFontTTF('legend', 'arial.ttf', 8);
$plot2->SetFontTTF('title', 'arial.ttf', 8);
$plot2->SetDataValues($data);
foreach ($data as $row) $plot2->SetLegend($row[0]); 
$plot2->DrawGraph();
?>