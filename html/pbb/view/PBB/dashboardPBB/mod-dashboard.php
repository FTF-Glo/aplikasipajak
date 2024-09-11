<?php

require_once 'Dashboard.php';

$dashboard = new Dashboard();
$hitungDendaMassal = new HitungDendaMassal();
$appConfig = $dashboard->get('appConfig');
$lastHitungDenda = $hitungDendaMassal->getLastDenda();

?>
<style>
    .chart-container {
        position: relative;
        margin: auto;
        width: 100%;
        height: 80vh;
    }

    .spinner {
        margin-left: 5px;
    }
    .ui-widget-content {
        color: #fff
    }
    .ui-widget-shadow {
        opacity: 1
    }
    .tright {
        text-align: right !important;
    }
    .tcenter {
        text-align: center !important;
    }
</style>
<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box box box-danger box-counter" id="counter_tunggakan" data-counter-name="getCounterTunggakan" style="background-image:linear-gradient(to right bottom, #fffefe, #dd4b39)">
        <div style="padding: 0.5em 1em;">
            <span class="info-box-text">
                <h3 style="border:unset">Tunggakan</h3>
            </span>
            <span class="info-box-number mb5" data-toggle="tooltip" data-placement="top" title="0"><h1 style="border:unset">0</h1></span>
        </div>
        <div style="position: absolute;bottom: 10px;right: 10px">
            <i class="fa fa-cog fa-spin spinner spinner-counter-tunggakan" style="display: none;"></i>
        </div>
    </div>
</div>
<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box box box-success box-counter" id="counter_realisasi" data-counter-name="getCounterRealisasi" style="background-image:linear-gradient(to right bottom, #e5fbf1, #00a65a)">
        <div style="padding: 0.5em 1em;">
            <span class="info-box-text">
                <h3 style="border:unset">Realisasi</h3>
            </span>
            <span class="info-box-number mb5" data-toggle="tooltip" data-placement="top" title="0"><h1 style="border:unset">0</h1></span>
        </div>
        <div style="position: absolute;bottom: 10px;right: 10px">
            <select name="period">
                <option value="today">Hari ini</option>
                <option value="this month">Bulan ini</option>
                <option value="this year">Tahun ini</option>
                <option value="all">Semua</option>
            </select>
            <i class="fa fa-cog fa-spin spinner spinner-counter-realisasi" style="display: none;"></i>
        </div>
    </div>
</div>
<div class="clearfix visible-sm-block"></div>
<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box box box-primary box-counter" id="counter_nop_sudah_bayar" data-counter-name="getCounterNOP" style="background-image:linear-gradient(to right bottom, #e4f2fb, #3c8dbc)">
        <input type="hidden" name="status" value="sudah bayar">
        <div style="padding: 0.5em 1em;">
            <span class="info-box-text">
                <h3 style="border:unset">NOP Sudah Bayar</h3>
            </span>
            <span class="info-box-number mb5" data-toggle="tooltip" data-placement="top" title="0"><h1 style="border:unset">0</h1></span>
        </div>
        <div style="position: absolute;bottom: 10px;right: 10px">
            <select name="period">
                <option value="today">Hari ini</option>
                <option value="this month">Bulan ini</option>
                <option value="this year">Tahun ini</option>
                <option value="all">Semua</option>
            </select>
            <i class="fa fa-cog fa-spin spinner spinner-counter-nop-sudah-bayar" style="display: none;"></i>
        </div>
    </div>
</div>
<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box box box-warning box-counter" id="counter_nop_belum_bayar" data-counter-name="getCounterNOP" style="background-image:linear-gradient(to right bottom, #ffeed4, #f39c12)">
        <input type="hidden" name="status" value="belum bayar">
        <div style="padding: 0.5em 1em;">
            <span class="info-box-text">
                <h3 style="border:unset">NOP Belum Bayar</h3>
            </span>
            <span class="info-box-number mb5" data-toggle="tooltip" data-placement="top" title="0"><h1 style="border:unset">0</h1></span>
        </div>
        <div style="position: absolute;bottom: 10px;right: 10px">
            <i class="fa fa-cog fa-spin spinner spinner-counter-nop-belum-bayar" style="display: none;"></i>
        </div>
    </div>
</div>

<div class="col-sm-12">
    <div class="box box-info" style="background-image:linear-gradient(to right bottom, #f6fcfd, #96bbc4)">
        <div class="box-header with-border">
            <p class="box-title">Filter Chart Tunggakan dan Realisasi PBB</p>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <form action="" method="POST" id="formFilter">
                <div class="row">
                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="dash_kecamatan">Kecamatan</label>
                            <select id="dash_kecamatan" name="kecamatan" class="form-control">
                                <option value="" selected>Semua Kecamatan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="dash_kelurahan">Kelurahan</label>
                            <select id="dash_kelurahan" name="kelurahan" class="form-control">
                                <option value="" selected>Semua Kelurahan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label for="">Tanggal Bayar</label>
                            <div style="display: flex;">
                                <input type="date" id="dash_tanggal_bayar_start" name="tanggal_bayar_start" class="form-control">
                                <input type="date" id="dash_tanggal_bayar_end" name="tanggal_bayar_end" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="dash_tahun">Tahun Pajak</label>
                            <select id="dash_tahun" name="tahun" class="form-control">
                                <option value="" selected>Semua Tahun Pajak</option>
                                <?php for ($i = $appConfig['tahun_tagihan']; $i >= $dashboard::MIN_TAHUN; $i--) : ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="dash_buku">Buku</label>
                            <select id="dash_buku" name="buku" class="form-control">
                                <option value="" selected>Semua Buku</option>
                                <?php foreach ($dashboard->buku as $buku => $attr) : ?>
                                    <option value="<?= $buku ?>">Buku <?php echo implode(', ', (array) str_split($buku)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary btn-orange btn-flat btn-sm">Filter</button>
                        <button type="button" class="btn btn-primary btn-blue btn-flat btn-sm reset" disabled>Reset</button>
                        <div class="pull-right">
                            <small><strong><?= $lastHitungDenda ? "Denda terakhir dihitung pada: {$lastHitungDenda}" : 'Denda kosong / belum pernah dihitung' ?></strong></small>
                            <button type="button" class="btn btn-danger btn-flat btn-sm hitung-denda">Hitung Denda Massal<i class="fa fa-cog fa-spin spinner spinner-hitung-denda" style="display: none;"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="col-sm-12">
    <div class="box box-primary" style="background-image:linear-gradient(to right bottom, #f4f3da, #cad6d9)">
        <div class="box-header with-border text-center">
            <p class="box-title">Tunggakan dan Realisasi PBB <i class="fa fa-cog fa-spin spinner spinner-make-chart"></i></p>
        </div>
        <div class="box-body">
            <div class="chart-container">
                <canvas id="chartPenerimaanRealisasiPBB"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="col-sm-12">
    <div class="box box-success" style="background-image:linear-gradient(to right bottom, #fff, #9ad9bc)">
        <div class="box-header with-border">
            <p class="box-title"><b>Realisasi Per Kecamatan</b>
                <button type="button" class="btn btn-secondary btn-sm" title="Edit Angka Target Kelurahan"><i class="fa fa-gear"></i></button>
                <select id="dash_real" class="form-control" style="position:absolute;top:4px;left:250px;width:150px">
                    <option value="" selected>Pilih Tahun Pajak</option>
                    <?php for ($i = $appConfig['tahun_tagihan']; $i >= $dashboard::MIN_TAHUN; $i--) echo '<option value="'.$i.'">'.$i.'</option>' ;?>
                </select>
            </p>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
            
        </div>
        <div class="box-body row">
            <div id="loadingtapping" style="text-align:center;margin:30px;display:none">
                <i class="fa fa-cog fa-spin fa-3x"></i>
            </div>
            <div id="tapping"></div>
        </div>
    </div>
</div>

<!-- <div class="col-sm-12">
    <div class="box box-primary">
        <div class="box-header with-border text-center">
            <p class="box-title">Realisasi Pedesaan & Perkotaan Tahun <?=$appConfig['tahun_tagihan']?><i class="fa fa-cog fa-spin spinner dpx"></i></p>
        </div>
        <div class="box-body">
            <input type="hidden" id="qx" value="<?=base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}")?>">
            <input type="hidden" id="tx" value="<?=$appConfig['tahun_tagihan']?>">
            <div id="tableRealisasiperkotaanpedesaan"></div>
        </div>
    </div>
</div> -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js" integrity="sha512-Wt1bJGtlnMtGP0dqNFH1xlkLBNpEodaiQ8ZN5JLA5wpc1sUlk/O5uuOMNgvzddzkpvZ9GLyYNa8w2s7rqiTk5Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="/view/PBB/dashboardPBB/dashboard.js?v0003"></script>