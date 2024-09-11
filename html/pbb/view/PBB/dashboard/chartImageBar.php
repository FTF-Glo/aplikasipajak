<?php
# PHPlot Example: Bar chart, 3 data sets, unshaded
require_once 'phplot.php';
$data = array(
array('Jan', 40, 2, 4), 
array('Feb', 30, 3, 4), 
array('Mar', 20, 4, 4),
array('Apr', 10, 5, 4), 
array('May', 03, 6, 4), 
array('Jun', 07, 7, 4),
array('Jul', 10, 8, 4), 
array('Aug', 15, 9, 4), 
array('Sep', 20, 5, 4),
array('Oct', 18, 4, 4), 
array('Nov', 16, 7, 4), 
array('Dec', 14, 3, 4),
);
$legend = json_decode($_REQUEST['thn_label']);
$data = json_decode($_REQUEST['data']);
$plot = new PHPlot(1000, $_REQUEST['height']);
$plot->SetImageBorderType('plain');
$plot->SetPlotType('bars');
$plot->SetDataType('text-data-yx');
$plot->SetDataValues($data);
$plot->SetTitle($_REQUEST['title']);
$plot->SetShading(0);
$plot->SetLegend($legend);

$plot->SetXDataLabelPos('plotin');
$plot->SetXTickPos('both');
$plot->SetXTickLabelPos('both');

$plot->SetYDataLabelPos('plotleft');

$plot->DrawGraph();
?>