<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
$actualLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pengurangan_denda', '', dirname(__FILE__))) . '/';
require_once 'penguranganDendaClass.php';
require_once $sRootPath . 'inc/PBB/dbUtils.php';

// prevent direct access
if (!isset($data)) {
    return 'Not Found';
}

$uid       = $data->uid;
$uname     = strtoupper($data->uname);
$param     = $_REQUEST['param'];
$User      = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$core      = new penguranganDenda($appConfig);
$dbUtils   = new DbUtils($dbSpec);

$alert = null;
$alertMsg = null;

$sid                   = isset($_POST['sid']) ? $_POST['sid'] : '';
$nop                   = isset($_POST['nop']) ? $_POST['nop'] : '';
$year                  = isset($_POST['year']) ? $_POST['year'] : ($appConfig['tahun_tagihan']-1);
$pengurangan           = isset($_POST['pengurangan']) ? $_POST['pengurangan'] : null;
$penguranganPersentase = isset($_POST['pengurangan_persentase']) ? $_POST['pengurangan_persentase'] : null;
$deskripsi             = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : null;
$searchNOP             = isset($_POST['searchNOP']) ? true : false;
$prosesDenda           = isset($_POST['prosesDenda']) ? true : false;

$delete                = isset($_POST['delete']) ? $_POST['delete'] : '';

$findNOP = array();
$denda = 0;

if ($searchNOP) {
    $findNOP = $core->findNOP($nop, $year);

    if (empty($findNOP)) {
        $alert = 'danger';
        $alertMsg = 'NOP tidak ditemukan.';
    } else if ($findNOP[0]['PAYMENT_FLAG'] == "1") {
        $alert = 'danger';
        $alertMsg = 'NOP sudah lunas.';
    } else {
        // portlet/inc-config.php
        $daysInMonth = 0;
        $maxPenaltyMonth = 24;
        // if($findNOP[0]['SPPT_TAHUN_PAJAK']>=2024){
            $penaltyPercentagePerMonth = 1;
        // }else{
        //     $penaltyPercentagePerMonth = 2;
        // }
            
        
        $denda = $dbUtils->getDenda($findNOP[0]['SPPT_TANGGAL_JATUH_TEMPO'], $findNOP[0]['SPPT_PBB_HARUS_DIBAYAR'], $daysInMonth, $maxPenaltyMonth, $penaltyPercentagePerMonth);
    }
}

if ($prosesDenda) {
    $data = array(
        'NOP'         => $nop,
        'TAHUN'       => $year,
        'NILAI'       => $pengurangan,
        'PERSENTASE'  => $penguranganPersentase,
        'CREATED_BY'  => $uname,
        'CREATED_UID' => $uid,
        'DESKRIPSI'   => $deskripsi,
    );

    $result = $core->insert($data);
    
    if (! $result) {
        $alert = 'danger';
        $alertMsg = 'Terjadi kesalahan saat menyimpan data.';
    } else {
        if($sid!=''){
            $core->editServices($sid,$uname);
        }
        $alert = 'success';
        $alertMsg = 'Data berhasil disimpan.';
    }
}

if ($delete) {
    if (! $core->delete($delete, $uname)) {
        $alert = 'danger';
        $alertMsg = 'Terjadi kesalahan saat menghapus data.';
    } else {
        $alert = 'success';
        $alertMsg = 'data berhasil dihapus.';
    }

}

$optionselect = '';
for ($i=2018; $i <=($appConfig['tahun_tagihan']-1) ; $i++) { 
    $selectit = ($i==$year) ? 'selected':'';
    $optionselect .= "<option $selectit value=$i>$i</option>";
}

?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/jq-3.6.0/dt-1.11.3/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs/jq-3.6.0/dt-1.11.3/datatables.min.js"></script>

<div class="col-sm-12">
    <h2>Pengurangan Denda</h2>
    <p>User saat ini: <strong><?= $uname ?></strong></p>
    <?php if ($alert !== null && $alertMsg !== null) : ?>
        <div class="alert <?= 'alert-' . $alert ?>"><?= $alertMsg ?></div>
    <?php endif; ?>
</div>

<div class="col-sm-12">
    <div class="box box-primary">
        <div class="box-header with-border">
            <p class="box-title">Permohonan Pengurangan Denda</p>
        </div>
        <table class="table table-striped" id="tableRequest" style="width:100%;margin-top:0!important">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Kecamatan</th>
                    <th>Desa</th>
                    <th>NOP</th>
                    <th>Tahun_Pajak</th>
                    <th>Nama_WP</th>
                    <th>Oleh_Kuasa</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="col-sm-12 col-md-4">
    <div class="box box-primary">
        <form role="form" method="post" action="?param=<?= $param ?>" id="formNOP">
            <div class="box-body">
                <div class="form-group">
                    <label for="nop">NOP</label>
                    <input type="number" class="form-control" id="nop" name="nop" placeholder="Cari NOP" value="<?= $nop ?>" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="year">Tahun</label>
                    <select class="form-control" id="year" name="year" required>
                        <?=$optionselect?>
                    </select>
                </div>
            </div>
            <div class="box-footer">
                <input type="hidden" id="sid" name="sid" value="<?= $sid ?>">
                <button type="submit" id="searchNOP" class="btn btn-primary btn-flat btn-orange" name="searchNOP">Cari</button>
            </div>
        </form>
    </div>
</div>
<div class="col-sm-12 col-md-8">
    <?php if (!empty($findNOP)) : ?>
        <div class="box box-primary">
            <form role="form" method="post" action="" id="formDenda">
                <div class="box-body">
                    <table class="table">
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Nama WP</td>
                            <td><?= $findNOP[0]['WP_NAMA'] ?></td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Alamat WP</td>
                            <td>
                                RT <?= $findNOP[0]['WP_RT'] ?>/<?= $findNOP[0]['WP_RW'] ?>,
                                <?= $findNOP[0]['WP_ALAMAT'] ?>,
                                KEL.<?= $findNOP[0]['WP_KELURAHAN'] ?>,
                                KEC.<?= $findNOP[0]['WP_KECAMATAN'] ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">NOP</td>
                            <td><?= $findNOP[0]['NOP'] ?></td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Tahun Pajak</td>
                            <td><?= $findNOP[0]['SPPT_TAHUN_PAJAK'] ?></td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Alamat OP</td>
                            <td>
                                RT <?= $findNOP[0]['OP_RT'] ?>/<?= $findNOP[0]['OP_RW'] ?>,
                                <?= $findNOP[0]['OP_ALAMAT'] ?>,
                                KEL.<?= $findNOP[0]['OP_KELURAHAN'] ?>,
                                KEC.<?= $findNOP[0]['OP_KECAMATAN'] ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Tagihan</td>
                            <td><?= number_format($findNOP[0]['SPPT_PBB_HARUS_DIBAYAR']) ?></td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Denda</td>
                            <td>
                                <?= number_format($denda) ?>
                                <input type="hidden" name="denda" id="denda" value="<?= $denda ?>">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Jatuh Tempo</td>
                            <td>
                                <span class="<?= time() > strtotime($findNOP[0]['SPPT_TANGGAL_JATUH_TEMPO']) ? 'text-danger' : '' ?>">
                                    <?= $findNOP[0]['SPPT_TANGGAL_JATUH_TEMPO'] ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Pengurangan</td>
                            <td>
                                <div style="width: 100%;display: flex;align-items: center;justify-content: center;">
                                    <div class="input-group" style="width: 60%;">
                                        <span class="input-group-addon">Rp</span>
                                        <input class="form-control" type="number" name="pengurangan" id="pengurangan" min="1" max="<?= $denda ?>" value="<?= $findNOP[0]['NILAI_PENGURANGAN'] ?>" <?= !$denda ? 'disabled' : 'required' ?> autocomplete="off">
                                    </div>
                                    <span style="margin: 0 1em 0 1em;width: 5%;display: flex;justify-content: center;">/</span>
                                    <div class="input-group" style="width: 35%;">
                                        <input class="form-control" type="number" name="pengurangan_persentase" id="pengurangan_persentase" min="1" max="100" step="0.01" value="<?= $findNOP[0]['PERSENTASE_PENGURANGAN'] ?>" <?= !$denda ? 'disabled' : 'required' ?> autocomplete="off">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Denda Baru</td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input class="form-control" type="number" name="dendaBaru" id="dendaBaru" <?= !$denda ? 'disabled' : 'readonly' ?>>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 20%;font-weight: 900;">Deskripsi</td>
                            <td>
                                <input class="form-control" type="text" name="deskripsi" id="deskripsi" <?= !$denda ? 'disabled' : 'required' ?> autocomplete="off">
                                <input type="hidden" name="sid" value="<?= $sid ?>">
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-orange" name="prosesDenda" <?= !$denda ? 'disabled' : '' ?>>Proses</button>
                    <small style="font-weight: 900;">
                    <?php if (!$denda) : ?> NOP tidak memiliki denda. <br /><?php endif; ?>
                    <?php if ($findNOP[0]['ID_PENGURANGAN'] != null) : ?> Denda NOP ini sudah pernah dikurangi. <?php endif; ?>
                    </small>
                    <input type="hidden" name="nop" value="<?= $nop ?>">
                    <input type="hidden" name="year" value="<?= $year ?>">
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="col-sm-12">
    <div class="box box-primary collapsed-box">
        <div class="box-header with-border">
            <p class="box-title">History</p>
            <div class="pull-right">
                <form action="" method="POST" id="searchHistoryForm" style="display: flex;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="term" class="form-control" autocomplete="off">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary btn-orange btn-flat">Cari</button>
                        </span>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm btn-blue btn-flat" style="margin-left: 1em" id="toggleBox">Buka/Tutup</button>
                </form>
            </div>
            <div class="btn-group" style="margin-left: 1em">
                <button type="button" class="btn btn-default btn-sm btn-flat" data-deleted="0" disabled>Semua</button>
                <button type="button" class="btn btn-danger btn-sm btn-flat" data-deleted="1">Terhapus</button>
            </div>
        </div>
        <table class="table table-striped" id="tableHistory" style="width: 100%;margin-top: 0!important">
            <thead>
                <tr>
                    <th>NOP</th>
                    <th>Nama WP</th>
                    <th>Alamat WP</th>
                    <th>Alamat OP</th>
                    <th>Tahun Pajak</th>
                    <th>Pengurangan</th>
                    <th>Pengurangan (%)</th>
                    <th>Tanggal</th>
                    <th>Oleh</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
    let minNumber = function(el) {
        if (parseInt(el.value) < parseInt(el.getAttribute('min'))) el.value = el.getAttribute('min');
    }
    let maxNumber = function(el) {
        if (parseInt(el.value) > parseInt(el.getAttribute('max'))) el.value = el.getAttribute('max');
    }
    let caridaritable = function(sid, nop, thn) {
        $('#sid').val(sid);
        $('#nop').val(nop);
        $('#year').val(thn);
        $('#searchNOP').click();
    }

    $(function() {
        let denda = $('#formDenda #denda').val() || 0;
        let hasilPengurangan = $('#formDenda #dendaBaru');

        $('#formDenda').on('input change', '#pengurangan', function() {
            minNumber(this);
            maxNumber(this);
            let pengurangan = parseInt(this.value) || 0;
            let newDenda = denda - pengurangan;
            let persentase = (pengurangan / denda) * 100;

            hasilPengurangan.val(newDenda);
            $('#formDenda #pengurangan_persentase').val(persentase.toFixed(2));
        })
        $('#formDenda').on('input change', '#pengurangan_persentase', function() {
            minNumber(this);
            maxNumber(this);
            let persentase = parseInt(this.value) || 0;
            
            if (persentase === 0) {
                hasilPengurangan.val(denda);
                return;
            }

            let penguranganPersen = persentase / 100;
            let pengurangan = (denda * penguranganPersen)
            let newDenda = denda - pengurangan;

            hasilPengurangan.val(newDenda);
            $('#formDenda #pengurangan').val(pengurangan);
        });

        let tableRequest = $('#tableRequest').DataTable({
            // dom: 'rtip',
            dom: '<"box-body no-padding"rti><"box-footer"<"pull-left"l>p>',
            processing: true,
            serverSide: true,
            paging: true,
            info: false,
            ajax: {
                url: 'view/PBB/pengurangan_denda/svc-permohonan.php',
                type: 'POST',
                data: function(data) { }
            },
            columnDefs: [
                {
                    targets: 4,
                    searchable: false,
                    orderable: false
                },
                {
                    targets: 7,
                    searchable: false,
                    orderable: false
                }
            ],
            order: [[7, 'desc']],
            // lengthChange: false,
            responsive: false,
            ordering: true,
            searching: false,
        });

        let tableHistory = $('#tableHistory').DataTable({
            // dom: 'rtip',
            dom: '<"box-body no-padding"rti><"box-footer"<"pull-left"l>p>',
            processing: true,
            serverSide: true,
            paging: true,
            info: false,
            ajax: {
                url: 'view/PBB/pengurangan_denda/datatables.php',
                type: 'POST',
                data: function(data) {
                    data.term = $('#searchHistoryForm [name="term"]').val();
                    data.deleted = $('[data-deleted]:disabled').attr('data-deleted');
                }
            },
            columnDefs: [
                {
                    targets: 10,
                    searchable: false,
                    orderable: false
                }
            ],
            order: [[7, 'desc']],
            // lengthChange: false,
            responsive: false,
            ordering: true,
            searching: true,
        });

        $('body').on('submit', '#searchHistoryForm', function (e) {
            e.preventDefault();

            tableHistory.ajax.reload();
        }).on('click', '[data-deleted]:not(:disabled)', function() {
            let v = $(this);
            $('[data-deleted]').removeAttr('disabled');
            v.prop('disabled', true);
            tableHistory.ajax.reload();
        }).on('click', '#toggleBox', function() {
            let box = $(this).parents('.box');
            if (box.hasClass('collapsed-box')) {
                box.removeClass('collapsed-box');
            } else {
                box.addClass('collapsed-box');
            }
        });
    })
</script>