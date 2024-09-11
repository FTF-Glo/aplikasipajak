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
    $html = '<table class="table table-sm table-bordered">
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

$filename = "REALISASI PBB PERSEN";
if (!empty($thn)) $filename .= " [THN {$thn}]";
if (!empty($kecamatan)) $filename .= " [KEC {$kecamatan}]";
if (!empty($sPeriode)) $filename .= " [{$sPeriode}]";
if (!empty($buku)) $filename .= " [BUKU ". implode(', ', explode('', $buku)) ."]";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> -->
    <title><?= $filename ?> <?= date('YmdHis') ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
</head>

<body>
    <div class="row w-100 export-sppt-area">
        <div class="col-sm-12 d-flex justify-content-center">
            <img src="/style/default/logo.png" alt="" srcset="" style="width: 5em;">
        </div>
        <div class="col-sm-12 text-center my-1">
            <strong style="display: block;">REALISASI PENERIMAAN PBB-P2 PERKECAMATAN</strong>
            <strong style="display: block;"><?= $appConfig['C_KABKOT'] ?> <?= $appConfig['NAMA_KOTA'] ?></strong>
            <strong style="display: block;"><?= (!empty($thn) ? 'TAHUN ' . $thn : 'SEMUA TAHUN') ?></strong>
            <strong style="display: block;"><?= ($sPeriode ? 'PERIODE ' . $sPeriode : 'SEMUA PERIODE') ?></strong>
        </div>
        <div class="col-sm-12">
            <div class="table-responsive mt-4">
                <?= showTableWithPercentage() ?>
            </div>
            
            <div class="w-100">
                <table class="table table-sm table-borderless w-100 float-right" style="max-width: 20em;">
                    <tr>
                        <td class="text-center font-weight-bold"><?= $appConfig['NAMA_KOTA_PENGESAHAN'] . ", " . $txtNow ?></td>
                    </tr>
                    <tr>
                        <td class="text-center font-weight-bold"><?= $appConfig['KABID_JABATAN'] ?></td>
                    </tr>
                    <tr style="border-bottom: 2px solid black;height: 7em;">
                        <td class="text-center font-weight-bold" style="vertical-align: bottom;"><?= $appConfig['KABID_NAMA'] ?></td>
                    </tr>
                    <tr>
                        <td class="text-center font-weight-bold"><?= $appConfig['KABID_NIP'] ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <script>
            $(function() {
                window.print();
                setTimeout(window.close, 0);
            })
        </script>
    </div>
</body>

</html>