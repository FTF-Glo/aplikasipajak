<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

require_once $sRootPath . "inc/PBB/SimpleDB.php";
require_once $sRootPath . "inc/phpexcel/Classes/PHPExcel.php";

$core = new SimpleDB();
$appConfig = $core->get('appConfig');

/** FUNCTIONS */
function showTableWithPercentage($returnRaw = false)
{
    global $core, $kecamatan, $thn, $eperiode, $eperiode2, $buku;

    $core->dbOpen('gw');

    $where = array('1=1');
    if ($thn) {
        $where[] = "A.SPPT_TAHUN_PAJAK = '" . $core->dbEscape($thn) . "'";
    }
    if ($kecamatan) {
        $where[] = "A.OP_KECAMATAN_KODE = '" . $core->dbEscape($kecamatan) . "'";
    }
    if ($eperiode || $eperiode2) {
        $wherePeriode = array('A.PAYMENT_FLAG = 1');
        if ($eperiode) $wherePeriode[] = "DATE(A.PAYMENT_PAID) >= '" . $core->dbEscape($eperiode) . "'";
        if ($eperiode2) $wherePeriode[] = "DATE(A.PAYMENT_PAID) <= '" . $core->dbEscape($eperiode2) . "'";
        $where[] = sprintf("((A.PAYMENT_FLAG IS NULL OR A.PAYMENT_FLAG <> 1) OR (%s))", $core->flatten($wherePeriode, ' AND '));
    }
    if ($buku && isset($core->buku[$buku]) && $core->buku[$buku]) {
        $where[] = "A.SPPT_PBB_HARUS_DIBAYAR BETWEEN '" . $core->buku[$buku]['min'] . "' AND '" . $core->buku[$buku]['max'] . "'";
    }

    $select = array(
        'SUM(A.SPPT_PBB_HARUS_DIBAYAR)                      AS KETETAPAN',
        'SUM(IF(A.PAYMENT_FLAG = 1, A.PBB_TOTAL_BAYAR, 0))  AS REALISASI',
        'SUM(IFNULL(B.PBB_DENDA, 0))                        AS DENDA',
        'IFNULL(KEC.CPC_TKC_KECAMATAN, A.OP_KECAMATAN)      AS KECAMATAN'
    );
    $joinDenda = "LEFT JOIN pbb_denda B ON A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK";
    $joinKecamatan = " LEFT JOIN cppmod_tax_kecamatan KEC ON A.OP_KECAMATAN_KODE = KEC.CPC_TKC_ID";

    $sql = sprintf(
        "SELECT %s FROM pbb_sppt A %s WHERE %s GROUP BY A.OP_KECAMATAN_KODE",
        $core->flatten($select, ', '),
        $joinDenda . $joinKecamatan,
        $core->flatten($where, ' AND ')
    );

    $rows = $core->dbQuery($sql)->fetchAll();
    $html = '<table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Kecamatan</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Realisasi</th>
                        <th class="text-center">Persentase %%</th>
                    </tr>
                </thead>
                <tbody>%s</tbody>
                <tfoot>%s</tfoot>
            </table>';

    $rowCount = count($rows);
    $total    = array('target' => 0, 'realisasi' => 0);
    $data     = array();

    $tbody = '';
    $tfoot = '';
    foreach ($rows as $key => $row) {
        $target    = $row['KETETAPAN'] + $row['DENDA'];
        $realisasi = $row['REALISASI'];
        $persen    = $realisasi / $target * 100;

        $data[] = array(
            'kecamatan' => $row['KECAMATAN'],
            'target'    => $target,
            'realisasi' => $realisasi,
            'persen'    => $persen
        );

        $tbody .= "<tr>
                <td class='text-center'>" . ($key + 1) . "</td>
                <td>" . $row['KECAMATAN'] . "</td>
                <td>Rp<span style='float: right'>" . number_format($target) . "</span></td>
                <td>Rp<span style='float: right'>" . number_format($realisasi) . "</span></td>
                <td class='text-center'>" . number_format($persen) . "</td>
            </tr>";

        $total['target'] += $target;
        $total['realisasi'] += $realisasi;

        if (($key + 1) == $rowCount) {
            $persen = $total['realisasi'] / $total['target'] * 100;
            $tfoot = "<tr>
                <th colspan='2'>Jumlah</th>
                <th>Rp<span style='float: right'>" . number_format($total['target']) . "</span></th>
                <th>Rp<span style='float: right'>" . number_format($total['realisasi']) . "</span></th>
                <th class='text-center'>" . number_format($persen) . "</th>
            </tr>";
        }
    }

    if ($returnRaw) {
        return array(
            'rows'    => $data,
            'total'   => $total,
            'numRows' => count($data)
        );
    }
    return sprintf($html, $tbody, $tfoot);
}

/** GET REQUEST */
$kecamatan        = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$thn              = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$eperiode         = @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$eperiode2        = @isset($_REQUEST['eperiode2']) ? $_REQUEST['eperiode2'] : "";
$buku             = @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : "";
$tampilpersen     = isset($_REQUEST['displaypersen']) && $_REQUEST['displaypersen'] == "1" ? true : false;

$data = showTableWithPercentage(true);

$txtNow = sprintf('%s %s %s', date('d'), strtoupper($core->bulan[date('m')]), date('Y'));
$txtPeriode = '';
$txtPeriode2 = '';

if (!empty($eperiode)) {
    $timePeriode = strtotime($eperiode);
    $txtPeriode  = sprintf('%s %s %s', date('d', $timePeriode), strtoupper($core->bulan[date('m', $timePeriode)]), date('Y', $timePeriode));
}
if (!empty($eperiode2)) {
    $timePeriode2 = strtotime($eperiode2);
    $txtPeriode2  = sprintf('%s %s %s', date('d', $timePeriode2), strtoupper($core->bulan[date('m', $timePeriode2)]), date('Y', $timePeriode2));
}

$sPeriode = !empty($eperiode) && !empty($eperiode2) ? ($eperiode == $eperiode2 ? $txtPeriode : $txtPeriode . ' s/d ' . $txtPeriode2) : (!empty($eperiode) ? $txtPeriode . ' KEATAS' : (!empty($eperiode2) ? $txtPeriode2 . ' KEBAWAH' : false));


$fontSizeHeader = 10;
$fontSizeDefault = 9;

$PHPExcel = new PHPExcel();
$PHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$PHPExcel->getActiveSheet()->getPageMargins()->setTop(0.8);
$PHPExcel->getActiveSheet()->getPageMargins()->setRight(0);
$PHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.5);
$PHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.3);

// $PHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
// $PHPExcel->getDefaultStyle()->getFont()->setSize($fontSizeDefault)->setBold(true);

// Set properties
$PHPExcel->getProperties()
    ->setCreator('Alfa System')
    ->setLastModifiedBy('Alfa System')
    ->setTitle('Data Realisasi Kecamatan')
    ->setSubject('Data Realisasi Kecamatan')
    ->setDescription('Data Realisasi Kecamatan')
    ->setKeywords('Alfa System, Data Realisasi Kecamatan');

$bold = array('font' => array('bold' => true));
$center = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
));
$centerBottom = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_BOTTOM
));

$PHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'REALISASI PENERIMAAN PBB-P2 PERKECAMATAN')
    ->setCellValue('A2', $appConfig['C_KABKOT'] . ' ' . $appConfig['NAMA_KOTA'])
    ->setCellValue('A3', !empty($thn) ? 'TAHUN ' . $thn : 'SEMUA TAHUN')
    ->setCellValue('A4', $sPeriode ? 'PERIODE ' . $sPeriode : 'SEMUA PERIODE');

$PHPExcel->getActiveSheet()->getStyle('A1:A4')->applyFromArray($center);
$PHPExcel->getActiveSheet()->getStyle('A1:A4')->applyFromArray($bold);
$PHPExcel->getActiveSheet()
    ->mergeCells('A1:E1')
    ->mergeCells('A2:E2')
    ->mergeCells('A3:E3')
    ->mergeCells('A4:E4');

$startRow = 6;
$PHPExcel->getActiveSheet()
    ->setCellValue('A' . $startRow, 'No')
    ->setCellValue('B' . $startRow, 'Kecamatan')
    ->setCellValue('C' . $startRow, 'Target')
    ->setCellValue('D' . $startRow, 'Realisasi')
    ->setCellValue('E' . $startRow, 'Persentase %');

$PHPExcel->getActiveSheet()->getStyle('A' . $startRow . ':E' . $startRow)->applyFromArray($center);
$PHPExcel->getActiveSheet()->getStyle('A' . $startRow . ':E' . $startRow)->applyFromArray($bold);

$PHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$PHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$PHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$PHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$PHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);

$contentRow = $startRow + 1; // 16
foreach ($data['rows'] as $key => $row) {
    $PHPExcel->getActiveSheet()->setCellValue('A' . $contentRow, ($key + 1));
    $PHPExcel->getActiveSheet()->setCellValue('B' . $contentRow, $row['kecamatan']);
    $PHPExcel->getActiveSheet()->setCellValue('C' . $contentRow, round($row['target']));
    $PHPExcel->getActiveSheet()->setCellValue('D' . $contentRow, round($row['realisasi']));
    $PHPExcel->getActiveSheet()->setCellValue('E' . $contentRow, round($row['persen']));
    $contentRow++;
}

$PHPExcel->getActiveSheet()->setCellValue('A' . $contentRow, 'JUMLAH');
$PHPExcel->getActiveSheet()->setCellValue('C' . $contentRow, round($data['total']['target']));
$PHPExcel->getActiveSheet()->setCellValue('D' . $contentRow, round($data['total']['realisasi']));
$PHPExcel->getActiveSheet()->setCellValue('E' . $contentRow, round($data['total']['realisasi'] / $data['total']['target'] * 100));

$PHPExcel->getActiveSheet()->mergeCells('A' . $contentRow . ':B' . $contentRow);
$PHPExcel->getActiveSheet()->getStyle('A' . $startRow . ':A' . ($contentRow - 1))->applyFromArray($center);
$PHPExcel->getActiveSheet()->getStyle('E' . $startRow . ':E' . $contentRow)->applyFromArray($center);

$PHPExcel->getActiveSheet()->getStyle('A' . $startRow . ':E' . $contentRow)->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$ttdRow = $contentRow + 1;
$PHPExcel->getActiveSheet()->setCellValue('D' . ($ttdRow + 1), $appConfig['NAMA_KOTA_PENGESAHAN'] . ", " . $txtNow);
$PHPExcel->getActiveSheet()->setCellValue('D' . ($ttdRow + 2), $appConfig['KABID_JABATAN']);
$PHPExcel->getActiveSheet()->setCellValue('D' . ($ttdRow + 3), $appConfig['KABID_NAMA']);
$PHPExcel->getActiveSheet()->setCellValue('D' . ($ttdRow + 4), $appConfig['KABID_NIP']);

$PHPExcel->getActiveSheet()->mergeCells('D' . ($ttdRow + 1) . ':E' . ($ttdRow + 1));
$PHPExcel->getActiveSheet()->mergeCells('D' . ($ttdRow + 2) . ':E' . ($ttdRow + 2));
$PHPExcel->getActiveSheet()->mergeCells('D' . ($ttdRow + 3) . ':E' . ($ttdRow + 3));
$PHPExcel->getActiveSheet()->mergeCells('D' . ($ttdRow + 4) . ':E' . ($ttdRow + 4));

$PHPExcel->getActiveSheet()->getStyle('D' . ($ttdRow + 1) . ':E' . ($ttdRow + 4))->applyFromArray($center);
$PHPExcel->getActiveSheet()->getStyle('D' . ($ttdRow + 1) . ':E' . ($ttdRow + 4))->applyFromArray($bold);

$PHPExcel->getActiveSheet()->getRowDimension(($ttdRow + 3))->setRowHeight(80);
$PHPExcel->getActiveSheet()->getStyle('D' . ($ttdRow + 3) . ':E' . ($ttdRow + 3))->applyFromArray($centerBottom);

$PHPExcel->getActiveSheet()->getStyle('D' . ($ttdRow + 3) . ':E' . ($ttdRow + 3))->applyFromArray(
    array(
        'borders' => array(
            'bottom' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM
            )
        )
    )
);


$filename = "REALISASI PBB PERSEN";
if (!empty($thn)) $filename .= " [THN {$thn}]";
if (!empty($kecamatan)) $filename .= " [KEC {$kecamatan}]";
if (!empty($sPeriode)) $filename .= " [{$sPeriode}]";
if (!empty($buku)) $filename .= " [BUKU ". implode(', ', explode('', $buku)) ."]";

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'. $filename .' ' . date('YmdHis') . '.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0


$PHPExceLWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
$PHPExceLWriter->save('php://output');
exit;
