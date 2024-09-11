var modeChart = '';

let getData = (params, $success, $error = null, $done = null) => {
    $.ajax({
        url: '/view/PBB/dashboardPBB/svc-get-data-new.php',
        type: 'POST',
        dataType: 'json',
        data: params,
        success: $success,
        error: function (resp) {
            if ($done !== null) {
                $error(resp)
            }

            console.log(resp);
        },
        done: function (resp) {
            if ($done !== null) {
                $done(resp);
            }
        }
    })
}

let getTableReasisasi = (params, $success, $error = null, $done = null) => {
    $.ajax({
        url: '/view/PBB/monitoring/svc-monitoring-realisasi-pp.php?q='+params.q,
        type: 'POST',
        data: { 
                th: params.tahun, 
                st:1,
                n: '',
                eperiode: 0
            },
        success: $success,
        error: function (resp) {
            if ($done !== null) {
                $error(resp)
            }

            console.log('ERROR:svc-monitoring-realisasi-pp.php');
            console.log(resp);
        },
        done: function (resp) {
            if ($done !== null) {
                $done(resp);
            }
        }
    })
}

let makeKecamatan = resp => {
    let kEl = $('#dash_kecamatan');
    kEl.find('option:not(:first-child)').remove();

    resp.forEach(data => {
        kEl.append(`<option value="${data.CPC_TKC_ID}">${data.CPC_TKC_KECAMATAN}</option>`);
    });
}

let getKelurahan = kcid => {
    getData({ getKelurahan: 1, kcid: kcid }, function (resp) {
        let kEl = $('#dash_kelurahan');
        kEl.find('option:not(:first-child)').remove();

        resp.forEach(data => {
            kEl.append(`<option value="${data.CPC_TKL_ID}">${data.CPC_TKL_KELURAHAN}</option>`);
        });

    })
}

let makeTapping = resp => {
    let tap = $('#tapping');
    resp.forEach(data => {
        tap.append(`
            <div class="col-xl-2 col-md-3 col-sm-6 col-xs-12">
                <div class="bg-${data.BG} box box-${data.TOP}">
                    <div style="padding:0.5em 1em">
                        <span class="info-box-text text-center"><b>${data.KECAMATAN}&nbsp;</b></span>
                        <div class="info-box-number">
                            <div class="row">
                                <div class="col-sm-5">Target</div>
                                <div class="col-sm-7 text-right">${data.TARGET}</div>
                                <div class="col-sm-5">Realisasi</div>
                                <div class="col-sm-7 text-right">${data.REALISASI}</div>
                                <div class="col-sm-5">Persentase</div>
                                <div class="col-sm-7 text-right">${data.PERSEN}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    });
    $('#loadingtapping').hide();
}

let parseTheTable = resp => {
    $('#tableRealisasiperkotaanpedesaan').html(resp);
    $('.spinner.dpx').hide();
}

let getChart = () => {
    $('.spinner.spinner-make-chart').show();
    $('#formFilter [type="submit"], #formFilter button.hitung-denda').prop('disabled', true);
    let formData = new FormData($('#formFilter')[0]);

    let params = {
        getChart: 1,
        tanggalBayarStart: formData.get('tanggal_bayar_start'),
        tanggalBayarEnd: formData.get('tanggal_bayar_end'),
        kecamatan: formData.get('kecamatan'),
        kelurahan: formData.get('kelurahan'),
        tahun: formData.get('tahun'),
        buku: formData.get('buku'),
        mode: modeChart
    }
    getData(params, makeChart);
}

let getRealisasiPerkotaanPedesaan = () => {
    $('.spinner.dpx').show();
    let tahun = $('#dash_real').val()
    if(tahun==''){
        tahun = $('#tx').val();
    }
    let params = {
        q:$('#qx').val(),
        tahun:tahun
    };
    getTableReasisasi(params, parseTheTable);
}

let getTappingRealisasi = () => {
    $('#loadingtapping').show();
    let params = {getTappingRealisasi:1,tahun:$('#dash_real').val()}
    getData(params, makeTapping);
}

let _chartPenerimaanRealisasiPBB = null;

let makeChart = resp => {
    let year = [];
    let ketetapan = [];
    let realisasi = [];
    let tunggakan = [];

    resp.forEach(data => {
        let tahunPajak = data.TAHUN_PAJAK;
        let sumKetetapan = data.SUM_KETETAPAN;
        let sumRealisasi = data.SUM_REALISASI;
        let sumDenda = data.SUM_DENDA;

        let _ketetapan = sumKetetapan + sumDenda;
        let _tunggakan = _ketetapan - sumRealisasi;

        year.push(tahunPajak);
        ketetapan.push(_ketetapan);
        realisasi.push(sumRealisasi);
        tunggakan.push(_tunggakan);
    });

    const green = '#00a65a';
    const red = '#dd4b39';
    const blue = '#3c8dbc';
    const labels = year;
    const data = {
        labels: labels,
        datasets: [
            {
                label: 'Tunggakan',
                data: tunggakan,
                backgroundColor: red,
                stack: 'Stack 2',
            },
            {
                label: 'Realisasi',
                data: realisasi,
                backgroundColor: green,
                stack: 'Stack 1',
            },
            {
                label: 'Ketetapan',
                data: ketetapan,
                backgroundColor: blue,
                stack: 'Stack 0',
            },
        ]
    };
    const config = {
        type: 'bar',
        data: data,
        options: {
            maintainAspectRatio: false,
            responsive: true,
            interaction: {
                intersect: false,
            },
            scales: {
                x: {
                    stacked: true,
                },
                y: {
                    stacked: true,
                    beginAtZero: !0,
                    min: 0,
                    ticks: {
                        callback: function(value) {
                            var tt = value.toString();
                            var len = tt.length;
                            if(len>=10){
                                return Math.round(value / 1000000000) + "T";
                            }else if(len>=7){
                                return Math.round(value / 1000000) + "Jt";
                            }else if(len>=4){
                                return Math.round(value / 1000) + "Rb";
                            }
                            return value;
                        }
                    }
                }
            }
        }
    };

    if (_chartPenerimaanRealisasiPBB === null) {
        _chartPenerimaanRealisasiPBB = new Chart('chartPenerimaanRealisasiPBB', config);
    } else {
        _chartPenerimaanRealisasiPBB.data = data;
        _chartPenerimaanRealisasiPBB.update();
    }

    $('#formFilter [type="submit"], #formFilter .reset, #formFilter button.hitung-denda').removeAttr('disabled');
    $('.spinner.spinner-make-chart').hide();
    if(modeChart=='' || modeChart=='updateJson'){
        modeChart = 'updateJson';
        getChart();
        modeChart = 'getJson';
    }
}

let getCounter = () => {
    $('.box-counter').each(function(i, el) {
        makeCounter($(el));
    })
}

let makeCounter = counter => {
    let counterName = counter.attr('data-counter-name');
    if (!counterName) {
        return;
    }

    counter.find('.spinner').show();
    let params = {};

    let period     = counter.find('[name="period"]');
    let status     = counter.find('[name="status"]');
    let targetText = counter.find('.info-box-number');

    if (period.length) {
        params.period = period.val();
        period.prop('disabled', true);
    }
    if (status.length) params.status = status.val();

    params.counter = counterName;

    getData(params, resp => {
        targetText.html('<h1 style="border:unset">'+resp.display+'</h1>');
        targetText.attr('title', resp.formatted);

        counter.find('.spinner').hide();
        period.removeAttr('disabled');
    })
}

$(function () {
    getData({ getKecamatan: 1 }, makeKecamatan);
    getChart();
    getCounter();
    // getRealisasiPerkotaanPedesaan();

    /* getData({ hitungDendaMassal: 1 }, function (resp) {
        console.log('Denda berhasil dihitung');
    });
    */
    
    $("#dash_real").change(function(){
        $('#tapping').html('');
        if($('#dash_real').val()!='') {
            getTappingRealisasi();
        }
    }); 

    /** events */
    $('body').on('change', '#dash_kecamatan', function () {
        getKelurahan(this.value)
    }).on('submit', '#formFilter', function (e) {
        e.preventDefault();
        getChart();
    }).on('click', '#formFilter .reset', function () {
        $(this).prop('disabled', true);
        $('#formFilter')[0].reset();
        $('#formFilter').trigger('submit');
    }).on('click', '#formFilter .hitung-denda', function () {
        let v = $(this);

        if (!confirm('Anda yakin ingin menghitung denda ? proses ini akan memakan waktu kurang lebih 2 menit')) {
            return false;
        }
        v.prop('disabled', true);
        $('.spinner.spinner-hitung-denda').show();

        getData({ hitungDendaMassal: 1 }, function (resp) {
            alert('Denda berhasil dihitung');
            $('.spinner.spinner-hitung-denda').hide();
            getChart();
            v.prev().find('strong').html(resp.lastDenda ? `Denda terakhir dihitung pada: ${resp.lastDenda}` : 'Denda kosong / belum pernah dihitung');
        });
    }).on('change', '.box-counter [name="period"]', function() {
        let parents = $(this).parents('[data-counter-name]');
        makeCounter(parents);
    });

    $('[data-toggle="tooltip"]').tooltip()
})