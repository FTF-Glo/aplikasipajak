<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pemeliharaan-data-piutang', '', dirname(__FILE__))) . '/';

require_once $sRootPath . 'inc/PBB/SimpleDB.php';
require_once $sRootPath . 'inc/PBB/dbUtils.php';
require_once $sRootPath . 'inc/datatables/ssp.class.php';
require_once $sRootPath . 'portlet-new/Portlet.php';

$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

if (!$q) {
    die('Just no !');
}
$_q    = $q;
$q     = base64_decode($q);
$q     = json_decode($q);
$a     = isset($q->a) ? $q->a : '';
$m     = isset($q->m) ? $q->m : '';
$n     = isset($q->n) ? $q->n : '';
$tab   = isset($q->tab) ? $q->tab : '';
$uname = isset($q->u) ? $q->u : '';
$uid   = isset($q->uid) ? $q->uid : '';

$simpleDB  = new SimpleDB();
$dbUtils   = new DbUtils(null);
$portlet   = new Portlet($dbUtils);
$appConfig = $simpleDB->get('appConfig');
$minTahun  = $portlet::MIN_TAHUN;
$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);

$gwLink = $simpleDB->dbOpen('gw', true);
$swLink = $simpleDB->dbOpen('sw', true);
$sql_details = array(
    'host' => $simpleDB->get('dbhost'),
    'user' => $simpleDB->get('dbuser'),
    'pass' => $simpleDB->get('dbpwd'),
    'db'   => $simpleDB->get('dbname'),
    'port' => $simpleDB->get('dbport')
);

function exit_response($data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if (isset($_POST['getAjax'])) {

    if (isset($_POST['getKecamatan'])) {
        $data = $simpleDB->dbQuery("SELECT * FROM cppmod_tax_kecamatan ORDER BY CPC_TKC_KECAMATAN ASC")->fetchAll();
    }

    if (isset($_POST['getKelurahan'])) {
        $kcid = isset($_POST['kcid']) ? 'CPC_TKL_KCID = "' . $simpleDB->dbEscape($_POST['kcid']) . '"' : '1=1';
        $data = $simpleDB->dbQuery("SELECT * FROM cppmod_tax_kelurahan WHERE {$kcid} ORDER BY CPC_TKL_KELURAHAN ASC")->fetchAll();
    }

    if (isset($_POST['getDataTable'])) {
        $filter_kecamatan = isset($_POST['kecamatan']) ? $_POST['kecamatan'] : null;
        $filter_kelurahan = isset($_POST['kelurahan']) ? $_POST['kelurahan'] : null;
        $filter_nop       = isset($_POST['nop']) ? $_POST['nop'] : null;
        $filter_tahun     = isset($_POST['tahun']) ? $_POST['tahun'] : null;
        $filter_buku      = isset($_POST['buku']) ? $_POST['buku'] : null;
        $filter_page      = isset($_POST['page']) ? $_POST['page'] : 1;

        $table = 'cppmod_dafnom_op';

        $tableFinal = 'cppmod_pbb_sppt_final';
		$tableSusulan = 'cppmod_pbb_sppt_susulan';
		
        $joinFinal = " LEFT JOIN {$tableFinal} ON {$tableFinal}.CPM_NOP = {$table}.NOP";
		$joinSusulan = " LEFT JOIN {$tableSusulan} ON {$tableSusulan}.CPM_NOP = {$table}.NOP";
		
		if ($filter_wilayah = $filter_kelurahan ?: $filter_kecamatan) {
			$column_wilayah = $filter_kelurahan ? 'CPM_OP_KELURAHAN' : 'CPM_OP_KECAMATAN';
			$filter_wilayah = $simpleDB->dbEscape($filter_wilayah);
			
            $joinFinal .= " AND {$tableFinal}.{$column_wilayah} = '{$filter_wilayah}'";
			$joinSusulan .= " AND {$tableSusulan}.{$column_wilayah} = '{$filter_wilayah}'";
        }

        $select = "IFNULL({$tableSusulan}.CPM_WP_NAMA, {$tableFinal}.CPM_WP_NAMA) AS CPM_WP_NAMA, {$table}.*";

        $where = '1=1';
		
		$where .= " AND ({$tableFinal}.CPM_NOP IS NOT NULL OR {$tableSusulan}.CPM_NOP IS NOT NULL)";

        if ($filter_nop) {
            $where .= " AND {$table}.NOP LIKE '%" . $simpleDB->dbEscape($filter_nop) . "%'";
        }

        if ($filter_tahun) {
            $where .= " AND {$table}.TAHUN_KEGIATAN = '" . $simpleDB->dbEscape($filter_tahun) . "'";
        }

        $dafnomSql = "SELECT {$select} FROM {$table} {$joinFinal} {$joinSusulan} WHERE {$where}";
		
        $dafnomList = $simpleDB->dbQuery("{$dafnomSql} " . SSP::limit($_POST, null), $swLink)->fetchAll();
        $dafnomNumRows = $simpleDB->dbGetNumRows($dafnomSql, $swLink);

        if (empty($dafnomList)) {
            exit_response(
                array(
                    "draw"            => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
                    "recordsTotal"    => 0,
                    "recordsFiltered" => 0,
                    "data"            => []
                )
            );
        }

        $data = [];
        foreach ($dafnomList as $row) {
            $data[] = [
                $row['NOP'],
                $row['CPM_WP_NAMA'],
                $row['TAHUN_KEGIATAN'],
                $row['ALAMAT_OP'],
                $row['KATEGORI'],
                $row['KETERANGAN']
            ];
        }

        exit_response(
            array(
                "draw"            => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
                "recordsTotal"    => $dafnomNumRows,
                "recordsFiltered" => $dafnomNumRows,
                "data"            => $data
            )
        );
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
?>
<div class="row tab3">
    <div class="col-sm-12">
        <form action="#" method="POST" id="tab3_formFilter">
            <div class="row">
                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label for="tab3_kecamatan">Kecamatan</label>
                        <select id="tab3_kecamatan" name="kecamatan" class="form-control">
                            <option value="" selected>Semua Kecamatan</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label for="tab3_kelurahan">Kelurahan</label>
                        <select id="tab3_kelurahan" name="kelurahan" class="form-control">
                            <option value="" selected>Semua Kelurahan</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label for="tab3_nop">NOP</label>
                        <input type="text" id="tab3_nop" name="nop" class="form-control">
                    </div>
                </div>
                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label for="tab3_tahun">Tahun Kegiatan</label>
                        <select id="tab3_tahun" name="tahun" class="form-control">
                            <option value="" selected>Semua Tahun Kegiatan</option>
                            <?php for ($i = $minTahun; $i <= $appConfig['tahun_tagihan']; $i++) : ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary btn-orange btn-flat btn-sm">Filter</button>
                    <button type="button" class="btn btn-primary btn-blue btn-flat btn-sm reset" disabled>Reset</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-sm-12">
        <table class="table" style="width: 100%;" id="tab3_tableListNOP">
            <thead>
                <tr>
                    <th>NOP</th>
                    <th>NAMA WP</th>
                    <th>TAHUN KEGIATAN</th>
                    <th>ALAMAT OP</th>
                    <th>KATEGORI</th>
                    <th>KETERANGAN</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    $(function() {
        let q = `<?= $_q ?>`;

        let getData = (params, $success, $error = null, $done = null) => {

            params.getAjax = 1;

            $.ajax({
                url: '/view/PBB/pemeliharaan-data-piutang/svc-list-nop.php?q=' + q,
                type: 'POST',
                dataType: 'json',
                data: params,
                success: $success,
                error: function(resp) {
                    if ($done !== null) {
                        $error(resp)
                    }

                    console.log(resp);
                },
                done: function(resp) {
                    if ($done !== null) {
                        $done(resp);
                    }
                }
            })
        }

        let makeKecamatan = resp => {
            let kEl = $('#tab3_kecamatan');
            kEl.find('option:not(:first-child)').remove();

            resp.forEach(data => {
                kEl.append(`<option value="${data.CPC_TKC_ID}">${data.CPC_TKC_KECAMATAN}</option>`);
            });
        }

        let getKelurahan = kcid => {
            getData({
                getKelurahan: 1,
                kcid: kcid
            }, function(resp) {
                let kEl = $('#tab3_kelurahan');
                kEl.find('option:not(:first-child)').remove();

                resp.forEach(data => {
                    kEl.append(`<option value="${data.CPC_TKL_ID}">${data.CPC_TKL_KELURAHAN}</option>`);
                });

            })
        }
		
		let getFormData = () => {
			let formFilter = new FormData($('#tab3_formFilter')[0]);
			
			return {
				kecamatan: formFilter.get('kecamatan'),
				kelurahan: formFilter.get('kelurahan'),
				nop: formFilter.get('nop'),
				tahun: formFilter.get('tahun')
			}
		}

        let tableListNOP = $('#tab3_tableListNOP').DataTable({
            dom: 'rtip',
            processing: true,
            serverSide: true,
            paging: true,
            pagingType: 'simple',
            info: false,
            ajax: {
                url: '/view/PBB/pemeliharaan-data-piutang/svc-list-nop.php?q=' + q,
                type: 'POST',
                data: function(data) {
                    Object.assign(data, getFormData());
                    data.getAjax = 1;
                    data.getDataTable = 1;
                }
            },
            columnDefs: [],
            responsive: false,
            ordering: false,
            searching: false,
        });

        getData({
            getKecamatan: 1
        }, makeKecamatan);

        /** events */
        $('body')
		.on('change', '#tab3_kecamatan', function() {
            getKelurahan(this.value);
        })
		.on('submit', '#tab3_formFilter', function(e) {
            e.preventDefault();
            tableListNOP.ajax.reload();
			
			$(this).find('button.reset').removeAttr('disabled');
        })
		.on('click', '#tab3_formFilter button.reset:not(:disabled)', function(e) {
            e.preventDefault();
            
			$('#tab3_formFilter')[0].reset();
			
			$(this).prop('disabled', true);
			
			tableListNOP.ajax.reload();
        })
    })
</script>