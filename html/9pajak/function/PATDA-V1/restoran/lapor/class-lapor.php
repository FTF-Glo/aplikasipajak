<?php

/**
Modified :
1. Penambahan konfigurasi nama badan pengelola :
	- modified by : RDN
	- date : 2016/01/03
	- function : print_sptpd, print_sspd
 */
class LaporPajak extends Pajak
{
    #field
    #restoran

    public $id_pajak = 8;

    function __construct()
    {
        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
        if (isset($_REQUEST['CPM_NPWPD'])) $_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
    }

    // public function get_kegiatan()
    // {
    //     $query = "select * from pelaksana_kegiatan order by nama_kegiatan ASC limit 0,10";
    //     $res = mysqli_query($this->Conn, $query);
    //     $kegiatan = array();
    //     while ($keg = mysqli_fetch_object($res)) {
    //         $kegiatan[] = $keg;
    //     }
    //     return $kegiatan;
    // }
    public function get_kegiatan($cari)
    {
        if ($cari == null) {
            echo "data kosong";

            //jika ada data yang dicari
        } else {
            //cari sesuai kata yang diketik
            $data   = mysqli_query($this->Conn, "select * from pelaksana_kegiatan where nama_kegiatan like '%$cari%'");

            $list = array();
            $key = 0;

            //lakukan looping untuk menampilkan data yang sesuai
            while ($row = mysqli_fetch_assoc($data)) {
                $list[$key]['text'] =  utf8_encode($row['nama_kegiatan']);
                $list[$key]['id'] = $row['kode_kegiatan'];
                $key++;
            }

            //data ditampilkan dalam bentuk json
            echo json_encode($list);
            // var_dump($list);
            // die;
        }
    }
    public function get_pajak($npwpd = '', $nop = '')
    {
        $Op = new ObjekPajak();
        $arr_rekening = $this->getRekening("4.1.01.07");
        $list_nop = array();

        $query = "
			SELECT DOC.*, DATE_FORMAT(DOC.CPM_TGL_JATUH_TEMPO,'%d-%m-%Y') as CPM_TGL_JATUH_TEMPO
			FROM PATDA_RESTORAN_DOC AS DOC WHERE DOC.CPM_ID = '{$this->_id}' LIMIT 0,1";

        $result = mysqli_query($this->Conn, $query);
        $pajak = $this->get_field_array($result);

        //if new entry
        if (empty($pajak['CPM_ID'])) {
            $ms = $this->inisialisasi_masa_pajak();

            $pajak['CPM_TAHUN_PAJAK'] = $ms['tahun_pajak'];
            $pajak['CPM_MASA_PAJAK'] = $ms['masa_pajak'];
            $pajak['CPM_MASA_PAJAK1'] = $ms['masa_pajak1'];
            $pajak['CPM_MASA_PAJAK2'] = $ms['masa_pajak2'];
            $pajak['CPM_HARGA'] = 0;
            $pajak['CPM_METODE_HITUNG'] = 'Non DPP';

            $profil = $Op->get_last_profil($npwpd, $nop);
            $tarif = ($profil['CPM_REKENING'] == '') ? 0 : $arr_rekening['ARR_REKENING'][$profil['CPM_REKENING']]['tarif'];
            $list_nop = $Op->get_list_nop($npwpd);
        } else { //if data available
            $profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);
            $tarif = $pajak['CPM_TARIF_PAJAK'];
        }

        $pajak['CPM_TERBILANG'] = $this->SayInIndonesian($pajak['CPM_TOTAL_PAJAK']);
        $pajak['CPM_JENIS_PAJAK'] = $this->id_pajak;
        $pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak;
        $pajak = array_merge($pajak, $arr_rekening);

        return array(
            'pajak' => $pajak,
            'tarif' => $tarif,
            'profil' => $profil,
            'list_nop' => $list_nop
        );
    }

    public function filtering($id)
    {
        // $opt_tahun = "<option value=''>All</option>";
        $opt_tahun = '<option value="">All</option>';
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $opt_tahun .= "<option value='{$th}'>{$th}</option>";
        }

        $opt_bulan = "<option value=''>All</option>";
        foreach ($this->arr_bulan as $x => $y) {
            $opt_bulan .= "<option value='{$x}'>{$y}</option>";
        }

        $kec = $this->get_list_kecamatan();
        $opt_kecamatan = "<option value=\"\">All</option>";
        foreach ($kec as $k => $v) {
            $opt_kecamatan .= "<option value=\"{$k}\">{$v->CPM_KECAMATAN}</option>";
        }

        $reks = $this->getRekening("4.1.01.07");
        $opt_rekening = '<option value="">All</option>' . json_encode($reks);
        foreach ($reks['ARR_REKENING'] as $k => $v) {
            $opt_rekening .= "<option value=\"{$k}\">$k - {$v['nmrek']}</option>";
        }

        $opt_pilih = "<option value=\"\">All</option>";
        foreach ($this->arr_jenis as $k => $v) {
            $opt_pilih .= "<option value=\"{$k}\">{$v}</option>";
        }

        // $reks = $this->getRekening("4.1.01.07");
        // $opt_jenis = ' <option value="">All</option>';
        // foreach ($reks['ARR_REKENING'] as $k => $v) {
        // 	$opt_jenis .= "<option value=\"{$k}\">$k - {$v['nmrek']}</option>";
        // }

        $opt_jenis = '<option value="">All</option><option value="1">Restoran</option><option value="2">Jasa Boga/Katering dan Sejenisnya</option>';

        // $html = "<div class=\"filtering\">
        //             <form>
        //                 <input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" id_pajak=\"{$this->id_pajak}\" s=\"{$this->_s}\">
        //                 <table>
        //                     <tr valign=\"bottom\">

        //                         <td>NPWPD :<br><input type=\"text\" style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" placeholder=\"NPWPD\" /> <br></td>
        //                         <td>Nama/No Laporan :<br><input type=\"text\" style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\" placeholder=\"Nama WP\" /><br></td>
        //                         <td>NOP  :<br><input type=\"text\" style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_NOP-{$id}\" id=\"CPM_NOP-{$id}\" placeholder=\"NOP\" /><br></td>
        //                         <td>Tahun :<br><select style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">{$opt_tahun}</select><br></td>
        //                         <td>Masa Pajak :<br><select style=\"width:100px; height:30px;\" class=\"form-control\"  name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">{$opt_bulan}</select><br></td>
        //                         <td>Tanggal Lapor :<br>
        //                             <input type=\"text\" style=\"width:100px;  height:30px;\"  name=\"CPM_TGL_LAPOR1-{$id}\" size=\"10\" id=\"CPM_TGL_LAPOR1-{$id}\" readonly class=\"date\" placeholder=\"Tgl Awal\" /><input type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR1-{$id}').val('');\" /> s.d
        //                             <input type=\"text\" style=\"width:100px;  height:30px;\"  name=\"CPM_TGL_LAPOR2-{$id}\" size=\"10\" id=\"CPM_TGL_LAPOR2-{$id}\" readonly class=\"date\" placeholder=\"Tgl Akhir\" /><input type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR2-{$id}').val('');\" /><br><br>
        //                         </td>";
        // if ($this->_i == 4) {
        //     $html .= "<td>Total Pajak :<br><input type=\"number\" name=\"TOTAL_PAJAK-{$id}\" id=\"TOTAL_PAJAK-{$id}\" onkeypress=\"return isNumberKey(event)\"> </td>";
        // }
        // $html .= "<td>Kecamatan :<br><select style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select><br></td>
        //                         <td>Kelurahan :<br><select style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_KELURAHAN-{$id}\" id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">All</option></select><br></td>
        //                         <td>Rekening :<br><select style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_KODE_REKENING-{$id}\" style=\"max-width:200px\" id=\"CPM_KODE_REKENING-{$id}\">{$opt_rekening}</select><br></td>
        //                     </tr>
        //                 </table>
        //                 <table>
        //                     <tr valign=\"bottom\">
        //                          <td>Pilih Jenis :<br><select style=\"width:90px; height:30px;\" class=\"form-control\" name=\"CPM_PIUTANG-{$id}\" id=\"CPM_PIUTANG-{$id}\">{$opt_pilih}</select></td>
        //                         <td><b>Jenis Pajak</b> : <select style=\"width:100px; height:30px;\" class=\"form-control\" name=\"CPM_JENIS_PJK-{$id}\" id=\"CPM_JENIS_PJK-{$id}\">{$opt_jenis}</select></td>
        //                         <td><br><button type=\"submit\" style=\"width:50px; height:30px;\" class=\"btn btn-sm btn-secondary\" id=\"cari-{$id}\">Cari</button></td>
        //                         <td><br><button type=\"button\" style=\"width:90px; height:30px;\" class=\"btn btn-sm btn-secondary\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak.xls.php')\">Export to xls</button></td>
        //                         <td><br><button type=\"button\" style=\"width:150px; height:30px;\" class=\"btn btn-sm btn-secondary\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/cetakpanjang/svc-download-bentang-panjang-res.xls.php')\">Cetak Bentang Panjang</button> </td>             
        //                     </tr>
        //                 </table>
        //             </form>
        //         </div> ";

        $html = "
        <style>
        .form-filtering {
            background-color: #fff;
            padding: 20px 40px;
            
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    
        }
        </style>
        <div  class=\"p-2\">
            <div class=\"row\"> 
                <div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\"> 
                    <button class=\"btn btn-success\" type=\"button\" style=\"border-radius:8px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$id}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-{$id}\">
                        <i class=\"fa fa-filter\"></i> Filter Data
                    </button>
                </div>
                <div class=\"col-12\"> 
                    <div class=\"collapse\" id=\"collapsFilter-{$id}\">
    
                        <div class=\"form-filtering\">
                        
                            <form>
                            
                                    <div class=\"row\">
                                        <div class=\"form-group col-md-3\" >
                                            <input type=\"hidden\" id=\"hidden-{$id}\" mod=\"{$this->_mod}\" id_pajak=\"{$this->id_pajak}\" s=\"{$this->_s}\">
                                            <label>NPWPD</label>
                                            <input class=\"form-control\" type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >
                                        </div>
                                            
                                        <div class=\" form-group col-md-3\"> 
                                            <label>Nama/No Laporan</label>
                                            <input class=\"form-control\" type=\"text\" name=\"CPM_NAMA_WP-{$id}\" id=\"CPM_NAMA_WP-{$id}\" >
                                        </div>

                                        <div class=\" form-group col-md-3\"> 
                                            <label>NOP</label>
                                            <input type=\"text\" class=\"form-control\" name=\"CPM_NOP-{$id}\" id=\"CPM_NOP-{$id}\" placeholder=\"NOP\" />
                                        </div>
                    
                                        <div class=\" form-group col-md-3\"> 
                                            <label>Tahun Pajak</label>
                                            <select class=\"form-control\" name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">{$opt_tahun}</select>
                                        </div>
                    
                                        <div class=\" form-group col-md-3\"> 
                                            <label>Masa Pajak</label>
                                            <select class=\"form-control\" name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">{$opt_bulan}</select>
                                        </div>

                                        <div class=\" form-group col-md-3\"> 
                                            <label>Pilih Jenis Pajak</label>
                                            <select class=\"form-control\" name=\"CPM_JENIS_PJK-{$id}\" id=\"CPM_JENIS_PJK-{$id}\">{$opt_jenis}</select>
                                        </div>
                    
                                        <div class=\"form-group col-md-3\">
                                            <label>Tanggal Lapor </label>
                                            <div style=\"display: flex; align-items: center;\">
                                                <input type=\"text\" name=\"CPM_TGL_LAPOR1-{$id}\" id=\"CPM_TGL_LAPOR1-{$id}\" readonly class=\"form-control date\" style=\"flex-grow: 1; margin-right: 10px;\">
                                                <button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR1-{$id}').val('');\" class=\"btn btn-secondary\">x</button>
                                            </div>
                                        </div>
                                        <div class=\"form-group col-md-3\">
                                            <label>Tanggal Lapor</label>
                                            <div style=\"display: flex; align-items: center;\">
                                                <input type=\"text\" name=\"CPM_TGL_LAPOR2-{$id}\" id=\"CPM_TGL_LAPOR2-{$id}\" readonly class=\"form-control date\" style=\"flex-grow: 1; margin-right: 10px;\">
                                                <button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR2-{$id}').val('');\" class=\"btn btn-secondary\">x</button>
                                            </div>
                                        </div>
                                    
                                    </div>

                                    <div class=\"row\">
                                    
                                    <div class=\" form-group col-md-3\"> 
                                        <label>Kecamatan</label>
                                        <select class=\"form-control\" name=\"CPM_KECAMATAN-{$id}\" id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select>
                                    </div>
                                
                                    <div class=\" form-group col-md-3\"> 
                                        <label>kelurahan</label>
                                        <select class=\"form-control\" name=\"CPM_KELURAHAN-{$id}\" id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">All</option></select>
                                    </div>";

        if ($this->_i == 4) {
            $html .= "<div class=\" form-group col-md-3\"> 
                                            <label>Total Pajak</label>
                                            <input class=\"form-control\" type=\"number\" name=\"TOTAL_PAJAK-{$id}\" id=\"TOTAL_PAJAK-{$id}\" onkeypress=\"return isNumberKey(event)\">
                                        </div>";
        }

        $html .=  "
                                        <div class=\" form-group col-md-3\"> 
                                            <label>Rekekning</label>
                                            <select class=\"form-control\" name=\"CPM_KODE_REKENING-{$id}\" id=\"CPM_KODE_REKENING-{$id}\">{$opt_rekening}</select>
                                        </div>

                                        <div class=\" form-group col-md-3\"> 
                                            <label>Pilih Jenis</label>
                                            <select class=\"form-control\" name=\"CPM_PIUTANG-{$id}\" id=\"CPM_PIUTANG-{$id}\">{$opt_pilih}</select>
                                        </div>
                                        <div class=\" form-group col-md-12\">    
                                            <button type=\"submit\" class=\"btn btn-success\" id=\"cari-{$id}\"><i class=\"fa fa-search\"></i> Cari</button>                       
                                            <button type=\"button\" class=\"btn btn-success\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/svc-download-pajak.xls.php')\"><i class=\"fa fa-download\"></i> Export to xls</button>
                                            <button type=\"button\" class=\"btn btn-success\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/cetakpanjang/svc-download-bentang-panjang-atb.xls.php')\"><i class=\"fa fa-print\"></i> Cetak Bentang Panjang</button>
                                        </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>";

        return $html;
    }

    public function grid_table()
    {
        $DIR = "PATDA-V1";
        $modul = "restoran";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.filtering td{background:transparent!important}.filtering input,.filtering select{height:23px}</style>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
						$('.date').datepicker({
							dateFormat: 'yy-mm-dd',
							changeYear: true,
							changeMonth: true
						});
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting: 'CPM_TGL_LAPOR DESC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false},
                                CPM_TGL_LAPOR: {title:'Tanggal Lapor',width: '10%', type: 'date', displayFormat: 'dd-mm-yy'},
                                CPM_NO: {title: 'Nomor Laporan',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_NAMA_WP: {title: 'Wajib Pajak',width: '10%'},
                                CPM_NAMA_OP: {title: 'Objek Pajak',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_TOTAL_PAJAK: {title: 'Total Pajak',width: '10%'},
                                payment_flag: {title: 'Status',width: '10%'},
                             
                                CPM_AUTHOR: {title: 'User Input',width: '10%'},

                                CPM_VERSION: {title: 'Versi Dok',width: '5%'},
                                " . ($this->_s == 0 ? "CPM_TRAN_STATUS: {title: 'Status',width: '10%'}," : "") . "
                                " . ($this->_s == 4 ? "CPM_TRAN_INFO: {title: 'Keterangan',width: '10%'}," : "") . "
                                CPM_AUTHOR: {title: 'User Input',width: '10%'}
                            },
                            recordsLoaded: function (event, data) {
                                for (var i in data.records) {
                                    if (data.records[i].READ == '0') {
                                        $('#laporanPajak-{$this->_i}').find('.jtable tbody tr:eq('+i+') td').css({'background-color':'#a0a0a0','border':'1px #CCC solid'});
                                    }
                                }
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NAMA_WP : $('#CPM_NAMA_WP-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
                                CPM_TGL_LAPOR1 : $('#CPM_TGL_LAPOR1-{$this->_i}').val(),
                                CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val(),
                                CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),
                                CPM_KODE_REKENING : $('#CPM_KODE_REKENING-{$this->_i}').val(),
								CPM_JENIS_PJK : $('#CPM_JENIS_PJK-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        $('#CPM_KECAMATAN-{$this->_i}').change(function(){
                            if($(this).val()==''){
                                $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>All</option>');
                                return false;
                            }
							$.ajax({
								type: \"POST\",
								url: \"function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php\",
								data: {'function' : 'get_list_kelurahan', 'CPM_KEC_ID' : $(this).val()},
                                cache:false,
                                beforeSend:function(){
                                    $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>Loading...</option>');
                                },
								success: function(html){
									$('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>All</option>'+html);
								}
							});
						});
                    });
                </script>";
        echo $html;
    }

    public function grid_data()
    {
        try {
            $where = "(";
            $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

            if ($this->_mod == "pel") { #pelaporan
                if ($this->_s == 0) { #semua data
                    $where = " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
                } elseif ($this->_s == 2) { #tab proses
                    $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
                } else {
                    $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
                }
            } elseif ($this->_mod == "ver") { #verifikasi
                if ($this->_s == 0) { #semua data
                    $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
                } else {
                    $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
                }
            } elseif ($this->_mod == "per") { #persetujuan
                if ($this->_s == 0) { #semua data
                    $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
                } else {
                    $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
                }
            } elseif ($this->_mod == "ply") { #pelayanan
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }

            $where .= ") ";
            $where .= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD = '{$_SESSION['npwpd']}' " : "";
            $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            // $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
            $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND (CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NO like \"%{$_REQUEST['CPM_NAMA_WP']}%\" )" : "";

            $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
            $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
            $where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP='{$_REQUEST['CPM_KECAMATAN']}' " : "";
            $where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP='{$_REQUEST['CPM_KELURAHAN']}' " : "";
            $where .= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND pr.CPM_REKENING='{$_REQUEST['CPM_KODE_REKENING']}' " : "";
            // $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND pr.CPM_REKENING='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                if ($_REQUEST['CPM_JENIS_PJK'] == 1)
                    $where .= " AND pr.CPM_REKENING!='4.1.01.07.07'";
                elseif ($_REQUEST['CPM_JENIS_PJK'] == 2)
                    $where .= " AND pr.CPM_REKENING='4.1.01.07.07'";
            }


            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_RESTORAN_DOC} pj
                            INNER JOIN {$this->PATDA_RESTORAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_RESTORAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_RESTORAN_ID
                            WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK,pj.PELAKSANA_KEGIATAN, pr.CPM_NPWPD, pr.CPM_NAMA_WP, pr.CPM_NAMA_OP, pr.CPM_NOP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                            tr.CPM_TRAN_READ, tr.CPM_TRAN_ID, pj.CPM_PIUTANG
                            FROM {$this->PATDA_RESTORAN_DOC} pj INNER JOIN {$this->PATDA_RESTORAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_RESTORAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_RESTORAN_ID
                            
                            WHERE {$where}
                            ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            // var_dump($query);
            // exit;
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));

                $row['READ'] = 1;
                if ($this->_s != 0) { #untuk menandai dibaca atau belum
                    $row['READ'] = stripos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;
                }

                $func = $this->_f;
                if ($row['CPM_PIUTANG'] == 1) {
                    $func = 'fPatdaLaporPiutang8';
                }

                if ($row['payment_flag'] == 1) {
                    $row['payment_flag'] = '<div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="width:10px; height:10px; border-radius:50%; background-color:green;"></div>
                                                <span>Sudah Bayar</span>
                                            </div>';
                } else {
                    $row['payment_flag'] = '<div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="width:10px; height:10px; border-radius:50%; background-color:red;"></div>
                                                <span>Belum Bayar</span>
                                            </div>';
                }

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$func}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
                $url = "main.php?param=" . base64_encode($base64);

                $row['CPM_NO'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO']}</a>";
                $row['CPM_TRAN_STATUS'] = $this->arr_status[$row['CPM_TRAN_STATUS']];
                $row['CPM_TOTAL_PAJAK'] = number_format($row['CPM_TOTAL_PAJAK'], 2);
                $row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
                $rows[] = $row;
            }

            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            $jTableResult['q'] = $query;
            $jTableResult['TotalRecordCount'] = $recordCount;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    public function grid_table_pembanding()
    {
        $DIR = "PATDA-V1";
        $modul = "restoran";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.filtering td{background:transparent!important}.filtering input,.filtering select{height:23px}</style>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                    	$('.date').datepicker({
							dateFormat: 'yy-mm-dd',
							changeYear: true,
							changeMonth: true
						});
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: false,
                            pageSize: {$this->pageSize},
                            sorting: false,
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding.php?action=list&a={$this->_a}&m={$this->_m}',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                AMOUNT: {title: 'Total Pajak',width: '10%'}
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        $('tr.jtable-no-data-row td').html('Silakan lakukan pencarian!');
                    });
                </script>";
        echo $html;
    }

    public function grid_table_pelayanan()
    {
        $DIR = "PATDA-V1";
        $modul = "restoran";
        if ($this->_i == 4) {
            $tambahanfield = "payment_code:{title: 'Kode Bayar',width: '10%'},";
        }
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.filtering td{background:transparent}.filtering select,.filtering button{height:28px}</style>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                    	$('.date').datepicker({
							dateFormat: 'yy-mm-dd',
							changeYear: true,
							changeMonth: true
						});
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting: 'CPM_TGL_LAPOR DESC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/pelayanan/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false},
                                CPM_TGL_INPUT: {title:'Tanggal Input',width: '10%', type: 'datetime', displayFormat: 'dd-mm-yy HH:MM:SS'}, 
                                CPM_TGL_LAPOR: {title:'Tanggal Lapor',width: '10%', type: 'date', displayFormat: 'dd-mm-yy'}, 
                                CPM_TRAN_DATE: {title: 'Tgl Verifikasi',width: '10%'},
                                CPM_NAMA_WP: {title: 'Wajib Pajak',width: '10%'},
                                CPM_NAMA_OP: {title: 'Objek Pajak',width: '10%'},
                                CPM_NO: {title: 'Nomor Laporan',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_NOP: {title: 'NOP',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_TOTAL_PAJAK: {title: 'Total Pajak',width: '10%'},
                                CPM_VERSION: {title: 'Versi Dok',width: '7%'},
                                $tambahanfield
                                PELAKSANA_KEGIATAN: {title: 'Pelaksana Kegiatan', width: '10%'},
                                " . ($this->_s == 0 ? "CPM_TRAN_STATUS: {title: 'Status',width: '7%'}," : "") . "
                                " . ($this->_s == 4 ? "CPM_TRAN_INFO: {title: 'Keterangan',width: '10%'}," : "") . "
                                CPM_AUTHOR: {title: 'User Input',width: '10%'}
                            },
                            recordsLoaded: function (event, data) {
                                for (var i in data.records) {
                                    if (data.records[i].READ == '0') {
                                        $('#laporanPajak-{$this->_i}').find('.jtable tbody tr:eq('+i+') td').css({'background-color':'#a0a0a0','border':'1px #CCC solid'});
                                    }
                                }
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_JENIS_RESTORAN : $('#CPM_JENIS_RESTORAN-{$this->_i}').val(),
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NAMA_WP : $('#CPM_NAMA_WP-{$this->_i}').val(),
                                CPM_NOP : $('#CPM_NOP-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val(),
                                CPM_TGL_LAPOR1 : $('#CPM_TGL_LAPOR1-{$this->_i}').val(),
                                CPM_TGL_LAPOR2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val(),
                                CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),
                                CPM_KODE_REKENING : $('#CPM_KODE_REKENING-{$this->_i}').val(),
								CPM_JENIS_PJK : $('#CPM_JENIS_PJK-{$this->_i}').val(),
                                CPM_PIUTANG : $('#CPM_PIUTANG-{$this->_i}').val(),
                                TOTAL_PAJAK : $('#TOTAL_PAJAK-{$this->_i}').val(),

                            });
                        });
                        $('#cari-{$this->_i}').click();
                        $('#CPM_KECAMATAN-{$this->_i}').change(function(){
                            if($(this).val()==''){
                                $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>All</option>');
                                return false;
                            }
							$.ajax({
								type: \"POST\",
								url: \"function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php\",
								data: {'function' : 'get_list_kelurahan', 'CPM_KEC_ID' : $(this).val()},
                                cache:false,
                                beforeSend:function(){
                                    $('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>Loading...</option>');
                                },
								success: function(html){
									$('#CPM_KELURAHAN-{$this->_i}').html('<option value=\'\'>All</option>'+html);
								}
							});
						});
                    });
                </script>";
        echo $html;
    }

    public function grid_data_pembanding()
    {
        try {
            $arr_config = $this->get_config_value($this->_a);
            $dbName = $arr_config['PATDA_TB_DBNAME'];
            $dbHost = $arr_config['PATDA_TB_HOSTPORT'];
            $dbPwd = $arr_config['PATDA_TB_PASSWORD'];
            $dbTable = $arr_config['PATDA_TB_TABLE'];
            $dbUser = $arr_config['PATDA_TB_USERNAME'];

            $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
            //mysqli_select_db($dbName);

            $where = "1=1";
            $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND DATE_FORMAT(TransactionDate,'%Y') = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND DATE_FORMAT(TransactionDate,'%m') = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";

            #sum(TransactionAmount) as total
            $q = $query = "select
                        DeviceId,
                        DATE_FORMAT(TransactionDate,'%m') as bln,
                        DATE_FORMAT(TransactionDate,'%Y') as thn,
                        sum(REPLACE(REPLACE(TransactionAmount,'.',''),',','')) as total
                        from {$dbTable}
                        WHERE {$where}
                        group by DeviceId, DATE_FORMAT(TransactionDate,'%m'), DATE_FORMAT(TransactionDate,'%Y')";
            $result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
            $dataTapbox = array();
            while ($data = mysqli_fetch_assoc($result)) {
                $dataTapbox[$data['DeviceId']][] = $data;
            }

            $where = (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? "AND CPM_NPWPD = '{$_REQUEST['CPM_NPWPD']}'" : "AND CPM_NPWPD = ''";


            #count utk pagging
            $query = "SELECT CPM_DEVICE_ID, CPM_NPWPD from PATDA_RESTORAN_PROFIL WHERE CPM_AKTIF='1' {$where}";
            $result = mysqli_query($this->Conn, $query);
            $dataAll = array();
            $x = 0;
            while ($data = mysqli_fetch_assoc($result)) {
                $arr_device = explode(";", $data['CPM_DEVICE_ID']);
                $periode = array();

                foreach ($arr_device as $id) {
                    if (isset($dataTapbox[$id])) {
                        foreach ($dataTapbox[$id] as $tb) {
                            $per = $tb['thn'] . $tb['bln'];
                            if (isset($periode[$per])) {
                                $periode[$per]['total'] += $tb['total'];
                                $dataAll[$periode[$per]['index']]['CPM_DEVICE_ID'] = $data['CPM_DEVICE_ID'];
                                $dataAll[$periode[$per]['index']]['CPM_NPWPD'] = $data['CPM_NPWPD'];
                                $dataAll[$periode[$per]['index']]['MASA_PAJAK'] = $tb['bln'];
                                $dataAll[$periode[$per]['index']]['TAHUN_PAJAK'] = $tb['thn'];
                                $dataAll[$periode[$per]['index']]['AMOUNT'] = $periode[$per]['total'];
                            } else {
                                $periode[$per]['total'] = $tb['total'];
                                $periode[$per]['index'] = $x;
                                $dataAll[$x]['CPM_DEVICE_ID'] = $data['CPM_DEVICE_ID'];
                                $dataAll[$x]['CPM_NPWPD'] = $data['CPM_NPWPD'];
                                $dataAll[$x]['MASA_PAJAK'] = $tb['bln'];
                                $dataAll[$x]['TAHUN_PAJAK'] = $tb['thn'];
                                $dataAll[$x]['AMOUNT'] = $tb['total'];
                            }
                            $x++;
                        }
                    }
                }
            }
            $recordCount = count($dataAll);
            #query select list data

            $rows = array();
            $no = 0;
            foreach ($dataAll as $row) {
                $json = base64_encode($this->Json->encode($row));

                $row = array_merge($row, array("NO" => ++$no));
                $row['CPM_NPWPD'] = "<a href='javascript:void(0)' onclick=\"javascript:getDetTranTapbox('{$json}')\">" . Pajak::formatNPWPD($row['CPM_NPWPD']) . "</a>";
                $row['MASA_PAJAK'] = isset($this->arr_bulan[(int) $row['MASA_PAJAK']]) ? $this->arr_bulan[(int) $row['MASA_PAJAK']] : "";
                $row['AMOUNT'] = number_format($row['AMOUNT'], 2);
                $rows[] = $row;
            }

            $jTableResult = array();
            $jTableResult['q'] = $q;
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = $recordCount;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    public function grid_data_pelayanan()
    {
        try {
            $where = "(";
            $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }

            $where .= ") ";
            $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            // $where.= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
            $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND (CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" OR CPM_NO like \"%{$_REQUEST['CPM_NAMA_WP']}%\" )" : "";
            $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            //$where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";
            $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
            $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";
            // $where.= (isset($_REQUEST['CPM_JENIS_RESTORAN']) && $_REQUEST['CPM_JENIS_RESTORAN'] != "") ? " AND pr.CPM_JENIS_RESTORAN = \"{$_REQUEST['CPM_JENIS_RESTORAN']}\" " : "";
            $where .= (isset($_REQUEST['CPM_NOP']) && $_REQUEST['CPM_NOP'] != "") ? " AND pr.CPM_NOP like \"%{$_REQUEST['CPM_NOP']}%\" " : "";
            $where .= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND pr.CPM_KECAMATAN_OP='{$_REQUEST['CPM_KECAMATAN']}' " : "";
            $where .= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND pr.CPM_KELURAHAN_OP='{$_REQUEST['CPM_KELURAHAN']}' " : "";
            $where .= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND pr.CPM_REKENING='{$_REQUEST['CPM_KODE_REKENING']}' " : "";
            // $where .= (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? " AND pr.CPM_REKENING='{$_REQUEST['CPM_JENIS_PJK']}' " : "";
            $where .= (isset($_REQUEST['CPM_PIUTANG']) && $_REQUEST['CPM_PIUTANG'] != "") ? " AND CPM_PIUTANG='{$_REQUEST['CPM_PIUTANG']}' " : "";
            $where .= (isset($_REQUEST['TOTAL_PAJAK']) && $_REQUEST['TOTAL_PAJAK'] != "") ? " AND CPM_TOTAL_PAJAK = \"{$_REQUEST['TOTAL_PAJAK']}\" " : "";

            if (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") {
                if ($_REQUEST['CPM_JENIS_PJK'] == 1)
                    // $where .= " AND pr.CPM_REKENING!='4.1.01.07.07'";
                    $where .= " AND pj.CPM_TIPE_PAJAK='1'";
                elseif ($_REQUEST['CPM_JENIS_PJK'] == 2)
                    // $where .= " AND pr.CPM_REKENING='4.1.01.07.07'";
                    $where .= " AND pj.CPM_TIPE_PAJAK='2'";
            }

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_RESTORAN_DOC} pj
                            INNER JOIN {$this->PATDA_RESTORAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_RESTORAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_RESTORAN_ID
                            WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                            STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP,pr.CPM_NAMA_OP, pr.CPM_NOP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                            tr.CPM_TRAN_READ, tr.CPM_TRAN_ID, pj.CPM_TGL_INPUT, tr.CPM_TRAN_DATE,pj.PELAKSANA_KEGIATAN,gw.payment_code, pj.CPM_PIUTANG
                            FROM {$this->PATDA_RESTORAN_DOC} pj INNER JOIN {$this->PATDA_RESTORAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN {$this->PATDA_RESTORAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_RESTORAN_ID
                            LEFT JOIN simpatda_gw gw ON gw.id_switching = pj.CPM_ID
                            WHERE {$where}
                            GROUP BY
                              pj.CPM_NO
                            ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            // echo $query;die;
            // if($_SERVER['HTTP_USER_AGENT']=='Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0'){
            //     echo "<pre>";
            //     print_r($query);
            //     die;
            // }

            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));

                $row['READ'] = stripos($row['CPM_TRAN_READ'], ";{$_SESSION['uname']};") === false ? 0 : 1;

                $func = $this->_f;
                if ($row['CPM_PIUTANG'] == 1) {
                    $func = 'fPatdaLaporPiutang8';
                }

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$func}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}&idtran={$row['CPM_TRAN_ID']}&read={$row['READ']}";
                $url = "main.php?param=" . base64_encode($base64);

                if ($row['CPM_TRAN_STATUS'] != '5') {
                    $row['CPM_TRAN_DATE'] = '-';
                }

                $row['CPM_NO'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO']}</a>";
                $row['CPM_TRAN_STATUS'] = $this->arr_status[$row['CPM_TRAN_STATUS']];
                $row['CPM_TOTAL_PAJAK'] = number_format($row['CPM_TOTAL_PAJAK'], 2);
                $row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
                $rows[] = $row;
            }

            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            $jTableResult['q'] = $query;
            $jTableResult['TotalRecordCount'] = $recordCount;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    private function last_version()
    {
        $query = "SELECT * FROM {$this->PATDA_RESTORAN_DOC_TRANMAIN} WHERE CPM_TRAN_RESTORAN_ID='{$this->CPM_ID}' AND CPM_TRAN_FLAG='0'";
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        return $data['CPM_TRAN_RESTORAN_VERSION'];
    }

    private function validasi_save()
    {
        return $this->validasi_pajak(1);
    }

    private function validasi_update()
    {
        return $this->validasi_pajak(0);
    }

    // private function validasi_pajak($input = 1)
    // {
    //     $where = ($input == 1) ? "OR pj.CPM_NO='{$this->CPM_NO}'" : "AND pj.CPM_NO!='{$this->CPM_NO}'";



    //     #cek apakah sudah ada pajak pada npwpd, tahun dan bulan atau no sptpd yang dimaksud
    //     $query = "SELECT pj.CPM_NO, pj.CPM_TAHUN_PAJAK,pj.CPM_MASA_PAJAK, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_FLAG
    //             FROM {$this->PATDA_RESTORAN_DOC} pj
    //             INNER JOIN {$this->PATDA_RESTORAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_RESTORAN_ID
    //             INNER JOIN {$this->PATDA_RESTORAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
    //             WHERE
    //             (
    //             pr.CPM_NPWPD = '{$this->CPM_NPWPD}' AND
    //             pr.CPM_NOP = '{$this->CPM_NOP}' AND
    //             pj.CPM_MASA_PAJAK='{$this->CPM_MASA_PAJAK}' AND
    //             pj.CPM_TAHUN_PAJAK='{$this->CPM_TAHUN_PAJAK}') {$where}
    //             ORDER BY tr.CPM_TRAN_STATUS DESC, pj.CPM_VERSION DESC  LIMIT 0,1";

    //     $res = mysqli_query($this->Conn, $query);
    //     $data = mysqli_fetch_assoc($res);
    //     // var_dump($this->CPM_TAHUN_PAJAK == $data['CPM_TAHUN_PAJAK'] && $this->CPM_MASA_PAJAK == $data['CPM_MASA_PAJAK'] && $this->CPM_TIPE_PAJAK == 1);
    //     // die;
    //     if ($this->notif == true) {
    //         if ($this->CPM_TAHUN_PAJAK == $data['CPM_TAHUN_PAJAK'] && $this->CPM_MASA_PAJAK == $data['CPM_MASA_PAJAK'] && $this->CPM_TIPE_PAJAK == 1) {
    //           $a=  $this->Message->setMessage("Gagal disimpan, Pajak untuk tahun <b>{$this->CPM_TAHUN_PAJAK}</b> dan bulan <b>{$this->arr_bulan[$this->CPM_MASA_PAJAK]}</b> sudah dilaporkan sebelumnya!");
    //         } elseif ($this->CPM_NO == $data['CPM_NO']) {
    //             $a=  $this->Message->setMessage("Gagal disimpan, Pajak dengan No. Pelaporan <b>{$this->CPM_NO}</b> sudah dilaporkan sebelumnya!");
    //         }
    //     }
    //     // var_dump($a);
    //     // die;
    //     $respon['result'] = mysqli_num_rows($res) > 0 ? false : true;
    //     $respon['result'] = ($this->CPM_TIPE_PAJAK == 2) ? true : $respon['result'];
    //     $respon['data'] = $data;

    //     // echo $respon;
    //     // exit;
    //     return $respon;
    // }

    private function validasi_pajak($input = 1)
    {
        $where = ($input == 1) ? "OR pj.CPM_NO='{$this->CPM_NO}'" : "AND pj.CPM_NO!='{$this->CPM_NO}'";

        #cek apakah sudah ada pajak pada npwpd, tahun dan bulan atau no sptpd yang dimaksud
        $query = "SELECT pj.CPM_NO, pj.CPM_TAHUN_PAJAK,pj.CPM_MASA_PAJAK, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_FLAG
                FROM {$this->PATDA_RESTORAN_DOC} pj
                INNER JOIN {$this->PATDA_RESTORAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_RESTORAN_ID
                INNER JOIN {$this->PATDA_RESTORAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                WHERE
                (
                pr.CPM_NPWPD = '{$this->CPM_NPWPD}' AND
                pr.CPM_NOP = '{$this->CPM_NOP}' AND
                pj.CPM_MASA_PAJAK='{$this->CPM_MASA_PAJAK}' AND
                pj.CPM_TAHUN_PAJAK='{$this->CPM_TAHUN_PAJAK}') {$where}
                ORDER BY tr.CPM_TRAN_STATUS DESC, pj.CPM_VERSION DESC  LIMIT 0,1";
        #echo $query;exit;
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        if ($this->notif == true) {
            if ($this->CPM_TAHUN_PAJAK == $data['CPM_TAHUN_PAJAK'] && $this->CPM_MASA_PAJAK == $data['CPM_MASA_PAJAK'] && $this->CPM_TIPE_PAJAK == 1) {
                //    $a= "asa";
                $this->Message->setMessage("Gagal disimpan, Pajak untuk tahun <b>{$this->CPM_TAHUN_PAJAK}</b> dan bulan <b>{$this->arr_bulan[$this->CPM_MASA_PAJAK]}</b> sudah dilaporkan sebelumnya!");
            } elseif ($this->CPM_NO == $data['CPM_NO']) {
                // $a= "b";
                $this->Message->setMessage("Gagal disimpan, Pajak dengan No. Pelaporan <b>{$this->CPM_NO}</b> sudah dilaporkan sebelumnya!");
            }
        }

        $respon['result'] = mysqli_num_rows($res) > 0 ? false : true;
        $respon['result'] = ($this->CPM_TIPE_PAJAK == 2) ? true : $respon['result'];
        $respon['data'] = $data;
        // var_dump( $respon);die;
        return $respon;
    }

    private function save_pajak($cpm_no = '')
    {

        $validasi = $this->validasi_save();
        // var_dump( $validasi);die;
        $typepajak = $this->CPM_TIPE_PAJAK == null ? $_POST['PAJAK']['CPM_TIPE_PAJAK'] : $this->CPM_TIPE_PAJAK;
        // $typepajak = $_POST['PAJAK']['CPM_TIPE_PAJAK'];

        // var_dump($typepajak);
        // die;
        if ($validasi['result'] == true || ($validasi['data']['CPM_TRAN_STATUS'] == '4')) {
            $this->Message->clearMessage();

            #update profil baru
            $query = "UPDATE {$this->PATDA_RESTORAN_PROFIL} SET CPM_APPROVE ='1' WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_AKTIF='1'";
            mysqli_query($this->Conn, $query);

            if (empty($cpm_no)) {
                #query untuk mengambil no urut pajak
                $no = $this->get_config_value($this->_a, "PATDA_TAX{$this->id_pajak}_COUNTER");
                $this->CPM_NO = $this->get_config_value($this->_a, "KODE_SPTPD") . str_pad($no, 8, "0", STR_PAD_LEFT) . "/" . $this->arr_kdpajak[$this->id_pajak] . "/" . date("y");
                $this->update_counter($this->_a, "PATDA_TAX{$this->id_pajak}_COUNTER");
            } else {
                $this->CPM_NO = $cpm_no;
            }

            #insert pajak baru
            $this->CPM_ID = c_uuid();
            $this->CPM_TGL_LAPOR = date("d-m-Y");
            $this->CPM_TOTAL_OMZET = str_replace(",", "", $this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = str_replace(",", "", $this->CPM_TARIF_PAJAK);


            $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
            $this->CPM_DPP = str_replace(",", "", $this->CPM_DPP);
            $this->CPM_BAYAR_TERUTANG = str_replace(",", "", $this->CPM_BAYAR_TERUTANG);
            $this->CPM_DENDA_TERLAMBAT_LAP = str_replace(",", "", $this->CPM_DENDA_TERLAMBAT_LAP);

            $this->CPM_PIUTANG =  isset($this->CPM_PIUTANG) ? $this->CPM_PIUTANG : 0;

            /* membuat tanggal jatuh tempo */

            list($day, $month, $year) = explode('/', $this->CPM_MASA_PAJAK2);
            $newTimestamp = mktime(0, 0, 0, $month, $day + 10, $year);
            $jatuh_tempo = date('Y-m-d', $newTimestamp);

            if ($this->CPM_TGL_JATUH_TEMPO == NULL || $this->CPM_TGL_JATUH_TEMPO == "") {
                $this->CPM_TGL_JATUH_TEMPO = $jatuh_tempo;
            } else {
                $this->CPM_TGL_JATUH_TEMPO =   $this->CPM_TGL_JATUH_TEMPO;
            }
            /* end membuat tanggal jatuh tempo */
            #$this->CPM_NO_SSPD = substr($this->CPM_NOP, 0, 11) . "" . substr($this->CPM_NO, 0, 9);
            $this->CPM_NO_SSPD = $this->CPM_NO;
            $arr_config = $this->get_config_value($this->_a);
            $query = sprintf(
                "INSERT INTO {$this->PATDA_RESTORAN_DOC}
                    (CPM_ID,CPM_ID_PROFIL,CPM_NO,
                    CPM_MASA_PAJAK, CPM_TAHUN_PAJAK,CPM_TOTAL_OMZET,
                    CPM_TOTAL_PAJAK, CPM_TARIF_PAJAK, CPM_TGL_JATUH_TEMPO,
                    CPM_METODE_HITUNG, CPM_KETERANGAN,CPM_VERSION,
                    CPM_AUTHOR,CPM_ID_TARIF,CPM_BAYAR_LAINNYA,
                    CPM_DPP, CPM_BAYAR_TERUTANG, CPM_NO_SSPD,
                    CPM_MASA_PAJAK1,CPM_MASA_PAJAK2,CPM_TIPE_PAJAK,CPM_DENDA_TERLAMBAT_LAP, PELAKSANA_KEGIATAN, CPM_PIUTANG)
                    VALUES ( '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s','%s','%s','%s')",
                $this->CPM_ID,
                $this->CPM_ID_PROFIL,
                $this->CPM_NO,
                $this->CPM_MASA_PAJAK,
                $this->CPM_TAHUN_PAJAK,
                $this->CPM_TOTAL_OMZET,
                $this->CPM_TOTAL_PAJAK,
                $this->CPM_TARIF_PAJAK,
                $this->CPM_TGL_JATUH_TEMPO,
                $this->CPM_METODE_HITUNG,
                $this->CPM_KETERANGAN,
                $this->CPM_VERSION,
                $this->CPM_AUTHOR,
                $this->CPM_ID_TARIF,
                $this->CPM_BAYAR_LAINNYA,
                $this->CPM_DPP,
                $this->CPM_BAYAR_TERUTANG,
                $this->CPM_NO_SSPD,
                $this->CPM_MASA_PAJAK1,
                $this->CPM_MASA_PAJAK2,
                $typepajak,
                $this->CPM_DENDA_TERLAMBAT_LAP,
                $this->PELAKSANA_KEGIATAN,
                $this->CPM_PIUTANG
            );

            return mysqli_query($this->Conn, $query);
        }
        // var_dump("nggak");
        // die;
        return false;
    }

    private function save_tranmain($param)
    {
        #insert tranmain
        $CPM_TRAN_ID = c_uuid();
        $CPM_TRAN_RESTORAN_ID = $this->CPM_ID;

        $query = "UPDATE {$this->PATDA_RESTORAN_DOC_TRANMAIN} SET CPM_TRAN_FLAG = '1' WHERE CPM_TRAN_RESTORAN_ID = '{$CPM_TRAN_RESTORAN_ID}'";
        $res = mysqli_query($this->Conn, $query);

        $query = sprintf(
            "INSERT INTO {$this->PATDA_RESTORAN_DOC_TRANMAIN}
                    (CPM_TRAN_ID, CPM_TRAN_RESTORAN_ID, CPM_TRAN_RESTORAN_VERSION, CPM_TRAN_STATUS, CPM_TRAN_FLAG, CPM_TRAN_DATE,
                    CPM_TRAN_OPR, CPM_TRAN_OPR_DISPENDA, CPM_TRAN_READ,CPM_TRAN_INFO)
                    VALUES ( '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s')",
            $CPM_TRAN_ID,
            $CPM_TRAN_RESTORAN_ID,
            $param['CPM_TRAN_RESTORAN_VERSION'],
            $param['CPM_TRAN_STATUS'],
            $param['CPM_TRAN_FLAG'],
            $param['CPM_TRAN_DATE'],
            $param['CPM_TRAN_OPR'],
            $param['CPM_TRAN_OPR_DISPENDA'],
            $param['CPM_TRAN_READ'],
            $param['CPM_TRAN_INFO']
        );
        return mysqli_query($this->Conn, $query);
    }

    private function update_tgl_input()
    {
        $tgl_input = date("Y-m-d h:i:s");
        $query = "UPDATE {$this->PATDA_RESTORAN_DOC} SET CPM_TGL_INPUT = '{$tgl_input}'
                  WHERE CPM_ID ='{$this->CPM_ID}'";

        return mysqli_query($this->Conn, $query);
    }

    private function update_tgl_lapor()
    {
        $tgl_input = date("d-m-Y");
        $query = "UPDATE {$this->PATDA_RESTORAN_DOC} SET CPM_TGL_LAPOR = '{$tgl_input}'
                  WHERE CPM_ID ='{$this->CPM_ID}'";

        return mysqli_query($this->Conn, $query);
    }

    private function update_tgl_lapor_ditolak($cpm_no, $tgl_lapor, $tgl_input)
    {
        $tgl_input = $tgl_input != '' ? $tgl_input : 'NULL';

        if ($tgl_input == 'NULL') {
            $query = "UPDATE {$this->PATDA_RESTORAN_DOC} SET CPM_TGL_LAPOR = '{$tgl_lapor}'
                  WHERE CPM_NO ='{$cpm_no}'";
        } else {
            $query = "UPDATE {$this->PATDA_RESTORAN_DOC} SET CPM_TGL_LAPOR = '{$tgl_lapor}', CPM_TGL_INPUT = '{$tgl_input}'
                  WHERE CPM_NO ='{$cpm_no}'";
        }

        return mysqli_query($this->Conn, $query);
    }

    public function save()
    {
        if ($this->CPM_PIUTANG == 1) {
            if ($this->validasi_piutang() == false) {
                return false;
            }
        }
        // var_dump($this->save_pajak($this->CPM_NO));
        // die;
        $this->CPM_VERSION = "1";
        if ($this->save_pajak($this->CPM_NO)) {

            $param = array();
            $param['CPM_TRAN_RESTORAN_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";


            if ($this->update_tgl_input()) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            if ($res = $this->save_tranmain($param)) {
                $_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Data Pajak gagal disimpan';
            }
        }
    }

    public function save_final()
    {

        if ($this->CPM_PIUTANG == 1) {
            if ($this->validasi_piutang() == false) {
                return false;
            }
        }

        $this->CPM_VERSION = "1";
        if ($this->save_pajak($this->CPM_NO)) {
            $param['CPM_TRAN_RESTORAN_VERSION'] = "1";
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $this->save_tranmain($param);

            if ($this->update_tgl_lapor()) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            $res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
            $berkass = $this->getIDBerkas($this->CPM_NO);
            $base644 = "a=aPatda&m=mPatdaPelayanan&f=fPatdaBerkas&id={$berkass}&sts=0&read=1";
            $urlberkas = "main.php?param=" . base64_encode($base644);

            if ($res) {
                $_SESSION['_success'] = 'Data Pajak berhasil difinalkan';
                header("Location: ../../../../{$urlberkas}");
                exit();
            } else {
                $_SESSION['_error'] = 'Data Pajak gagal difinalkan';
            }
        }
    }

    public function new_version()
    {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save_pajak($this->CPM_NO)) {

            $query = "UPDATE {$this->PATDA_RESTORAN_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_RESTORAN_ID='{$id}'";
            mysqli_query($this->Conn, $query);

            $param['CPM_TRAN_RESTORAN_VERSION'] = $new_version;
            $param['CPM_TRAN_STATUS'] = "1";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";

            if ($this->update_tgl_lapor_ditolak($this->CPM_NO, $this->DITOLAK_TGL_LAPOR, $this->DITOLAK_TGL_INPUT)) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            if ($res = $this->save_tranmain($param)) {
                $_SESSION['_success'] = 'Data Pajak versi ' . $new_version . ' berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Data Pajak ' . $new_version . ' gagal disimpan';
            }
        }
    }

    public function new_version_final()
    {
        $new_version = $this->last_version() + 1;
        $this->CPM_VERSION = $new_version;
        $id = $this->CPM_ID;

        $this->notif = false;
        if ($this->save_pajak($this->CPM_NO)) {

            $query = "UPDATE {$this->PATDA_RESTORAN_DOC_TRANMAIN} SET CPM_TRAN_FLAG ='1' WHERE CPM_TRAN_RESTORAN_ID='{$id}'";
            mysqli_query($this->Conn, $query);

            $param['CPM_TRAN_RESTORAN_VERSION'] = $new_version;
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $param['CPM_TRAN_INFO'] = "";
            $this->save_tranmain($param);

            if ($this->update_tgl_lapor_ditolak($this->CPM_NO, $this->DITOLAK_TGL_LAPOR, $this->DITOLAK_TGL_INPUT)) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            $res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
            if ($res) {
                $_SESSION['_success'] = 'Data Pajak versi ' . $new_version . ' berhasil difinalkan';
            } else {
                $_SESSION['_error'] = 'Data Pajak ' . $new_version . ' gagal difinalkan';
            }
        }
    }

    public function update_final()
    {
        $this->CPM_VERSION = $this->last_version();
        if ($this->update()) {
            $param['CPM_TRAN_RESTORAN_VERSION'] = $this->CPM_VERSION;
            $param['CPM_TRAN_STATUS'] = "2";
            $param['CPM_TRAN_FLAG'] = "0";
            $param['CPM_TRAN_DATE'] = date("d-m-Y");
            $param['CPM_TRAN_OPR'] = $this->CPM_AUTHOR;
            $param['CPM_TRAN_OPR_DISPENDA'] = "";
            $param['CPM_TRAN_READ'] = "";
            $this->save_tranmain($param);

            if ($this->update_tgl_lapor()) {
                //$_SESSION['_success'] = 'Data Pajak berhasil disimpan';
            } else {
                $_SESSION['_error'] = 'Tgl input gagal disimpan';
            }

            $res = $this->save_berkas_masuk($this->id_pajak, "CPM_SPTPD");
            if ($res) {
                $_SESSION['_success'] = 'Data Pajak berhasil difinalkan';
            } else {
                $_SESSION['_error'] = 'Data Pajak gagal difinalkan';
            }
        }
    }

    public function update()
    {
        $validasi = $this->validasi_update();
        if ($validasi['result'] == true) {

            $this->CPM_TGL_LAPOR = date("d-m-Y");
            $this->CPM_TOTAL_OMZET = str_replace(",", "", $this->CPM_TOTAL_OMZET);
            $this->CPM_TOTAL_PAJAK = str_replace(",", "", $this->CPM_TOTAL_PAJAK);
            $this->CPM_TARIF_PAJAK = str_replace(",", "", $this->CPM_TARIF_PAJAK);

            $this->CPM_BAYAR_LAINNYA = str_replace(",", "", $this->CPM_BAYAR_LAINNYA);
            $this->CPM_DPP = str_replace(",", "", $this->CPM_DPP);
            $this->CPM_BAYAR_TERUTANG = str_replace(",", "", $this->CPM_BAYAR_TERUTANG);
            $this->CPM_DENDA_TERLAMBAT_LAP = str_replace(",", "", $this->CPM_DENDA_TERLAMBAT_LAP);
            $this->CPM_PIUTANG =  isset($this->CPM_PIUTANG) ? $this->CPM_PIUTANG : 0;

            $query = sprintf(
                "UPDATE {$this->PATDA_RESTORAN_DOC} SET
                    CPM_TOTAL_OMZET = '%s',
                    CPM_TOTAL_PAJAK = '%s',
                    CPM_TARIF_PAJAK = '%s',
                    CPM_BAYAR_LAINNYA = '%s',
                    CPM_METODE_HITUNG = '%s',
                    CPM_DPP = '%s',
                    CPM_BAYAR_TERUTANG = '%s',
                    CPM_TGL_LAPOR = '%s',
                    CPM_KETERANGAN = '%s',
                    CPM_MASA_PAJAK1 = '%s',
                    CPM_MASA_PAJAK2 = '%s',
                    CPM_TIPE_PAJAK = '%s',
                    CPM_TAHUN_PAJAK = '%s',
                    CPM_MASA_PAJAK = '%s',
                    CPM_DENDA_TERLAMBAT_LAP = '%s',
					CPM_PIUTANG = '%s'
                    WHERE
                    CPM_ID ='{$this->CPM_ID}'",
                $this->CPM_TOTAL_OMZET,
                $this->CPM_TOTAL_PAJAK,
                $this->CPM_TARIF_PAJAK,
                $this->CPM_BAYAR_LAINNYA,
                $this->CPM_METODE_HITUNG,
                $this->CPM_DPP,
                $this->CPM_BAYAR_TERUTANG,
                $this->CPM_TGL_LAPOR,
                $this->CPM_KETERANGAN,
                $this->CPM_MASA_PAJAK1,
                $this->CPM_MASA_PAJAK2,
                $this->CPM_TIPE_PAJAK,
                $this->CPM_TAHUN_PAJAK,
                $this->CPM_MASA_PAJAK,
                $this->CPM_DENDA_TERLAMBAT_LAP,
                $this->CPM_PIUTANG
            );

            return mysqli_query($this->Conn, $query);
        }
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM {$this->PATDA_RESTORAN_DOC} WHERE CPM_ID ='{$this->CPM_ID}'";
        $res = mysqli_query($this->Conn, $query);
        if ($res) {
            $query = "DELETE FROM {$this->PATDA_RESTORAN_DOC_TRANMAIN} WHERE CPM_TRAN_RESTORAN_ID ='{$this->CPM_ID}'";
            mysqli_query($this->Conn, $query);
        }
    }

    public function verifikasi()
    {
        // var_dump("sini");
        // die;
        if ($this->AUTHORITY == 1) {
            $query = "SELECT * FROM {$this->PATDA_BERKAS} WHERE CPM_NO_SPTPD = '{$this->CPM_NO}' AND CPM_STATUS='1'";
            $res = mysqli_query($this->Conn, $query);
            if (mysqli_num_rows($res) == 0) {
                $msg = "Gagal disetujui, <b>berkas-berkas laporan pajak tidak lengkap</b>, silakan untuk dilengkapi dahulu di bagian Pelayanan!";
                $this->Message->setMessage($msg);
                $_SESSION['_error'] = $msg;
                return false;
            }
        }
        $this->persetujuan();

        #validasi hanya satu tahap yaitu verifikasi saja
        /* $status = ($this->AUTHORITY == 1) ? 3 : 4;
          $param['CPM_TRAN_RESTORAN_VERSION'] = $this->CPM_VERSION;
          $param['CPM_TRAN_STATUS'] = $status;
          $param['CPM_TRAN_FLAG'] = "0";
          $param['CPM_TRAN_DATE'] = date("d-m-Y");
          $param['CPM_TRAN_OPR'] = "";
          $param['CPM_TRAN_OPR_DISPENDA'] = $this->CPM_AUTHOR;
          $param['CPM_TRAN_READ'] = "";
          $param['CPM_TRAN_INFO'] = $this->CPM_TRAN_INFO;
          $this->save_tranmain($param); */
    }

    public function persetujuan()
    {
        $new_operator = $_SESSION['uname'];

        $status = ($this->AUTHORITY == 1) ? 5 : 4;
        $param['CPM_TRAN_RESTORAN_VERSION'] = $this->CPM_VERSION;
        $param['CPM_TRAN_STATUS'] = $status;
        $param['CPM_TRAN_FLAG'] = "0";
        $param['CPM_TRAN_DATE'] = date("d-m-Y");
        $param['CPM_TRAN_OPR'] = "";
        $param['CPM_TRAN_OPR_DISPENDA'] = $new_operator;
        $param['CPM_TRAN_INFO'] = $this->CPM_TRAN_INFO;
        $param['CPM_TRAN_READ'] = "";


        $res = $this->save_tranmain($param);
        if ($this->AUTHORITY == 1 && $res == true) {
            $arr_config = $this->get_config_value($this->_a);
            $res = $this->save_gateway($this->id_pajak, $arr_config);
            if ($res) {
                // $this->update_jatuh_tempo($this->EXPIRED_DATE, $this->CPM_TGL_JATUH_TEMPO);
                $this->update_validasi($this->CPM_ID);
                $_SESSION['_success'] = 'Data Pajak berhasil disetujui';
            } else {
                $_SESSION['_error'] = 'Data Pajak gagal disetujui';
            }
        }
    }

    private function update_jatuh_tempo($expired_date, $tgl_jatuh_tempo = NULL)
    {

        if ($tgl_jatuh_tempo == NULL || $tgl_jatuh_tempo == '') {
            $expired_date = $expired_date;
        } else {
            $expired_date = "'" . $tgl_jatuh_tempo . "'";
        }
        $query = "UPDATE {$this->PATDA_RESTORAN_DOC} SET CPM_TGL_JATUH_TEMPO = {$expired_date}
                  WHERE CPM_ID ='{$this->CPM_ID}'";
        return mysqli_query($this->Conn, $query);
    }

    function tgl_indo_full($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
            'Febuari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);

        // variabel pecahkan 0 = tahun
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tanggal

        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }


    public function print_sptpd_base()
    {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();

        $radio_gol_restoran[1] = $DATA['profil']['CPM_REKENING'] == 1 ? "[x]" : "[_]";
        $radio_gol_restoran[2] = $DATA['profil']['CPM_REKENING'] == 2 ? "[x]" : "[_]";
        $radio_gol_restoran[3] = $DATA['profil']['CPM_REKENING'] == 3 ? "[x]" : "[_]";
        $radio_gol_restoran[4] = $DATA['profil']['CPM_REKENING'] == 4 ? "[x]" : "[_]";
        $radio_gol_restoran[5] = $DATA['profil']['CPM_REKENING'] == 5 ? "[x]" : "[_]";
        $radio_gol_restoran[6] = $DATA['profil']['CPM_REKENING'] == 6 ? "[x]" : "[_]";
        $radio_gol_restoran[7] = $DATA['profil']['CPM_REKENING'] == 7 ? "[x]" : "[_]";

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $BAG_VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
        $NIP = $config['BAG_VERIFIKASI_NIP'];

        $config_terlambat_lap = $this->get_config_terlambat_lap($this->id_pajak);
        $persen_terlambat_lap = $config_terlambat_lap->persen;
        $editable_terlambat_lap = $config_terlambat_lap->editable;

        $html = "<table width=\"710\" class=\"main\" cellpadding=\"5\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"700\" border=\"0\">
                                <tr>
                                    <th valign=\"top\" align=\"center\">
                                        " . strtoupper($JENIS_PEMERINTAHAN) . " " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        " . strtoupper($NAMA_PENGELOLA) . "<br /><br />
                                        <font class=\"normal\">{$JALAN}<br/>{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                    </th>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"450\"><table width=\"440\" class=\"header\" border=\"0\">
                                <tr class=\"first\">
                                    <td width=\"440\" valign=\"top\" align=\"center\" colspan=\"2\">
                                        <b>
                                            SURAT PEMBERITAHUAN PAJAK DAERAH (SPTPD)<br />
                                            PAJAK RESTORAN
                                        </b><br/>
                                    </td>
                                </tr>
                                <tr>
                                    <td width=\"130\">No. SPTPD</td>
                                    <td width=\"310\" class=\"first\">: {$DATA['pajak']['CPM_NO']}</td>
                                </tr>
                                <tr>
                                    <td>Masa Pajak</td>
                                    <td class=\"first\">: {$DATA['pajak']['CPM_MASA_PAJAK1']} - {$DATA['pajak']['CPM_MASA_PAJAK2']}</td>
                                </tr>
                                <tr>
                                    <td>Tahun Pajak</td>
                                    <td class=\"first\">: {$DATA['pajak']['CPM_TAHUN_PAJAK']}</td>
                                </tr>
                            </table>
                        </td>
                        <td width=\"260\">Kepada : <br/>
                            Yth. Kepala Badan Pengelolaan Keuangan Daerah<br/>
                            {$JENIS_PEMERINTAHAN} {$NAMA_PEMERINTAHAN}<br/>
                            di - {$KOTA}
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\">
                                <tr>
                                    <td>Perhatian : </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;1. Harap diisi dalam rangkap 3 (tiga) ditulis dengan huruf CETAK atau diketik. </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;2. Beri nomor pada kotak yang tersedia untuk jawaban yang diberikan.</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;3. Setelah diisi dan ditandatangani harap diserahkan kembali kepada Dinas Pendapatan Daerah.</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kota {$KOTA} paling lambat tanggal 30 bulan berikutnya.</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;4. Keterlambatan penyerahan SPTPD akan dikenakan sanksi sesuai ketentuan berlaku.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\">
                                <tr>
                                    <th align=\"left\" colspan=\"2\"><strong>I. IDENTITAS WAJIB PAJAK</strong></th>
                                </tr>
                                <tr>
                                    <td width=\"150\">&nbsp;&nbsp;&nbsp;Nama Wajib Pajak</td>
                                    <td width=\"550\">: {$DATA['profil']['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Alamat Wajib Pajak</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Nama Restoran</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;Alamat Restoran</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_OP']}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;&nbsp;NPWPD</td>
                                    <td>: " . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"left\"><strong>II. DIISI OLEH PENGUSAHA RESTORAN</strong></td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\"><table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"1\" cellspacing=\"0\">
                                <tr>
                                    <td align=\"left\" colspan=\"2\">&nbsp;&nbsp;&nbsp;a. Klasifikasi Usaha : {$DATA['profil']['CPM_REKENING']} - {$DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['nmrek']}
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;b. Pembayaran makanan / minuman</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;c. Pembayaran lain-lain</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_BAYAR_LAINNYA'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;d. Dasar Pengenaan Pajak (DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_DPP'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;e. Pembayaran Terutang ({$DATA['pajak']['CPM_TARIF_PAJAK']}% x DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_BAYAR_TERUTANG'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;f. Pajak Kurang atau Lebih Bayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> 0</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;g. Sanksi Administrasi Telat Lapor ({$persen_terlambat_lap}%) x " . round($persen_sanksi / 2) . " Bulan</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> " . number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;h. Jumlah Pajak yang dibayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"400\"> <strong>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</strong></td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;i. Data Pendukung</td>
                                    <td align=\"left\" width=\"430\"> </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a). Surat Setoran Pajak Daerah (SSPD)</td>
                                    <td align=\"left\" width=\"430\"> [_] 1. Ada / [_] 2. Tidak ada</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b). Rekapitulasi Penjualan / Omzet</td>
                                    <td align=\"left\" width=\"430\"> [_] 1. Ada / [_] 2. Tidak ada</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"270\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c). Rekapitulasi Penggunaan Bill / Bonbill</td>
                                    <td align=\"left\" width=\"430\"> [_] 1. Ada / [_] 2. Tidak ada</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\"><table width=\"100%\" border=\"0\">
                                <tr>
                                    <td align=\"left\" colspan=\"2\">&nbsp;&nbsp;&nbsp;Dengan menyadari sepenuhnya akan segala akibatnya termasuk sanksi-sanksi sesuai ketentuan perundang-undangan yang berlaku, saya memberitahukan bahwa apa yang telah saya beritahukan diatas beserta lampiran-lampirannya adalah benar, lengkap, jelas dan bersyarat
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" width=\"350\">&nbsp;&nbsp;&nbsp;Diterima oleh Petugas,</td>
                                    <td align=\"left\" width=\"350\">{$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "</td>
                                </tr>
                                <tr>
                                    <td align=\"left\">&nbsp;&nbsp;&nbsp;Tanggal :</td>
                                    <td align=\"left\">WP/Penanggung Pajak/Kuasa</td>
                                </tr>
                                <tr>
                                    <td align=\"left\"></td>
                                    <td align=\"left\"></td>
                                </tr>
                                <tr>
                                    <td align=\"left\"></td>
                                    <td align=\"left\"></td>
                                </tr>
                                <tr>
                                    <td align=\"left\"></td>
                                    <td align=\"left\"></td>
                                </tr>
                                <tr>
                                    <td align=\"left\">&nbsp;&nbsp;&nbsp;<u>{$BAG_VERIFIKASI_NAMA}</u></td>
                                    <td align=\"left\"></td>
                                </tr>
                                <tr>
                                    <td align=\"left\">&nbsp;&nbsp;&nbsp;NIP. {$NIP}</td>
                                    <td align=\"left\">Nama jelas/Cap/Stempel</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>";

        require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('9 PAJAK ONLINE');
        $pdf->SetSubject('9 PAJAK ONLINE spppd');
        $pdf->SetKeywords('9 PAJAK ONLINE');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 14, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 45, 18, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sptpd-restoran.pdf', 'I');
    }

    public function print_sptpd()
    {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();

        $data_input = $this->check_status(2, $this->CPM_ID, $this->id_pajak);
        $petugas_input = $data_input->operator_input;
        $role = $this->check_role($petugas_input);
        $data_verifikasi = $this->check_status(5, $this->CPM_ID, $this->id_pajak);
        $petugas_verifikasi = $role == 'rmPatdaWp' ? $data_verifikasi->operator_verifikasi : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $tanggal_verifikasi = $role == 'rmPatdaWp' ? $data_verifikasi->tanggal_verifikasi : '';

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $BAG_VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
        $NIP = $config['BAG_VERIFIKASI_NIP'];

        $TGL_PENGESAHAN = $_POST['PAJAK']['tgl_cetak'];
        $tgl_pengesahans = $this->tgl_indo_full($TGL_PENGESAHAN);

        $config_terlambat_lap = $this->get_config_terlambat_lap($this->id_pajak);
        $persen_terlambat_lap = $config_terlambat_lap->persen;
        $editable_terlambat_lap = $config_terlambat_lap->editable;

        $persen_sanksi = 0;
        if (($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'] + 0) > 0) {
            //$persen_terlambat_lap = round($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK'], 1)*100;
            // $persen_sanksi = round(($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP']/$DATA['pajak']['CPM_TOTAL_PAJAK']*100)/2,0)*2;
            //tamabahan
            $tes3 = $DATA['pajak']['CPM_TOTAL_PAJAK'] - $DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'];
            $tes4 = $DATA['pajak']['CPM_TOTAL_PAJAK'] - $tes3;
            // var_dump($tes3);die;
            $persen_sanksi = round(($tes4 / $tes3) * 100);
        }
        $pemerintah = explode(' ', $JENIS_PEMERINTAHAN);
        $pemerintah_label = strtoupper($pemerintah[0]);
        $pemerintah_jenis = strtoupper($pemerintah[1]);

        function tgl_indo($tglcetak)
        {
            $bulan = array(
                1 =>   'Jan',
                'Feb',
                'Mar',
                'Apr',
                'Mei',
                'Jun',
                'Jul',
                'Agu',
                'Sep',
                'Okt',
                'Nov',
                'Des'
            );
            $pecahkan = explode('-', $tglcetak);

            // variabel pecahkan 0 = tahun
            // variabel pecahkan 1 = bulan
            // variabel pecahkan 2 = tanggal

            return $pecahkan[2] . '/' . $bulan[(int)$pecahkan[1]] . '/' . $pecahkan[0];
        }
        $tglcetak = date('Y-m-d');
        $tgl_cetak = tgl_indo($tglcetak);

        $page1 = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" cellspacing=\"0\">
                    <tr>
                        <td colspan=\"2\"><table width=\"710\" border=\"1\" cellpadding=\"3\">
                                <tr>
                                    <td width=\"200\" valign=\"top\" align=\"center\">                                   
										<br/>
										<br/><br/>
										<br/>
                                        <b>" . $pemerintah_label . "<br/>" . $pemerintah_jenis . ' ' . strtoupper($NAMA_PEMERINTAHAN) . "</b>
                                    </td>
                                    <td width=\"310\" valign=\"top\" align=\"center\">
										<b style=\"font-size:55px\">S P T P D</b><br/>
                                        (SURAT PEMBERITAHUAN PAJAK DAERAH)
                                        <b style=\"font-size:55px\">PAJAK RESTORAN</b><br/>
                                        <b>Tahun Pajak : {$DATA['pajak']['CPM_TAHUN_PAJAK']}</b>
                                    </td>
                                    <td width=\"200\" valign=\"top\" align=\"center\">                                   
										<br/>
										Nomor SPTPD : <br/>
										{$DATA['pajak']['CPM_NO']}<br/><br/>
										Masa Pajak : <br/>
										{$DATA['pajak']['CPM_MASA_PAJAK1']} - {$DATA['pajak']['CPM_MASA_PAJAK2']}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
						<td width=\"710\">
							<table width=\"710\" border=\"0\" cellpadding=\"5\">
								<tr>
									<td width=\"400\">
										<br/><br/><br/>&nbsp;
										<table width=\"380\" border=\"0\" cellpadding=\"0\">
											<tr>
												<td width=\"100\">NPWPD</td>
												<td width=\"280\">: " . Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD']) . "</td>
											</tr>
											<tr>
												<td>No. Telp.</td>
												<td>: {$DATA['profil']['CPM_TELEPON_WP']}</td>
											</tr>
										</table>
									</td>
									<td width=\"310\"><table width=\"310\" class=\"header\" border=\"0\">
										<tr class=\"first\">
											<td>
												Kepada Yth. <br/>
												Kepala Badan Pendapatan Daerah<br/>
												Kabupaten " . ucfirst(strtolower($NAMA_PEMERINTAHAN)) . "<br/>
												di <b style=\"font-size:40px\">{$KOTA}</b>
											</td>
										</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
                    </tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5\">
							<tr style=\"font-size:28px\">
								<td><table width=\"100%\" border=\"0\" align=\"left\">
										<tr>
											<td>PERHATIAN : </td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;1. Harap diisi dalam rangkap enam (6) ditulis dengan huruf <b>CETAK</b> </td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;2. Beri nomor pada kotak yang tersedia untuk jawaban yang diberikan.</td>
										</tr>
										<tr>
											<td>&nbsp;&nbsp;&nbsp;3. Formulir ini diterima oleh petugas setelah ditandatangani oleh Wajib Pajak atau Kuasanya.</td>
										</tr>
									</table>
									</td>
								</tr>
							</table>
                        </td>
                    </tr>
                    <tr>
						<td colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
							<b>A. IDENTITAS SUBJEK DAN OBJEK PAJAK</b>
						</td>
					</tr>
                    <tr>
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"7\">
							<tr style=\"font-size:32px\">
								<td><table width=\"100%\" border=\"0\" align=\"left\">
										<tr>
											<td width=\"200\">A. NAMA OBJEK PAJAK</td>
											<td width=\"500\">: {$DATA['profil']['CPM_NAMA_OP']}</td>
										</tr>
										<tr>
											<td>B. ALAMAT OBJEK PAJAK</td>
											<td>: {$DATA['profil']['CPM_ALAMAT_OP']}<br/>
											&nbsp;&nbsp;Kecamatan : {$DATA['profil']['CPM_NAMA_KECAMATAN_OP']}<br/>
											&nbsp;&nbsp;Kelurahan : {$DATA['profil']['CPM_NAMA_KELURAHAN_OP']}<br/>
											</td>
										</tr>
										<tr>
											<td>C. NAMA WAJIB PAJAK</td>
											<td>: {$DATA['profil']['CPM_NAMA_WP']}</td>
										</tr>
										<tr>
											<td>D. ALAMAT WAJIB PAJAK</td>
											<td>: {$DATA['profil']['CPM_ALAMAT_WP']}<br/>
											&nbsp;&nbsp;Kecamatan : {$DATA['profil']['CPM_KECAMATAN_WP']}<br/>
											&nbsp;&nbsp;Kelurahan : {$DATA['profil']['CPM_KELURAHAN_WP']}<br/>
											</td>
										</tr>
										
										                ";
        if ($DATA['profil']['CPM_REKENING'] == "4.1.01.07.07") {
            $page1 .= "
                                            <tr>
                                                <td>E. PELAKSANA KEGIATAN</td>
                                                <td>: {$DATA['pajak']['PELAKSANA_KEGIATAN']}
                                                </td>
                                            </tr>
                    ";
        }

        $page1 .= "
										
									</table>
									</td>
								</tr>
							</table>
                        </td>
                    </tr>
                    <tr>
						<td colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
							<b>B. INFORMASI UMUM OBJEK PAJAK</b>
						</td>
					</tr>
					<tr style=\"font-size:32px\">
                        <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"700\" align=\"center\" cellpadding=\"1\" border=\"0\" cellspacing=\"0\">
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;a.</td>
                                    <td align=\"left\" width=\"270\">Golongan Restoran</td>
                                    <td align=\"left\" width=\"420\" colspan=\"2\"> {$DATA['profil']['CPM_REKENING']} - {$DATA['pajak']['ARR_REKENING'][$DATA['profil']['CPM_REKENING']]['nmrek']}</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;b.</td>
                                    <td align=\"left\" width=\"270\">Pembayaran makanan / minuman </td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_TOTAL_OMZET'], 2) . "</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;c.</td>
                                    <td align=\"left\" width=\"270\">Pembayaran lain-lain</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_BAYAR_LAINNYA'], 2) . "</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;d.</td>
                                    <td align=\"left\" width=\"270\">Dasar Pengenaan Pajak (DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_DPP'], 2) . "</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;e.</td>
                                    <td align=\"left\" width=\"270\">Pembayaran Terutang ({$DATA['pajak']['CPM_TARIF_PAJAK']}% x DPP)</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_BAYAR_TERUTANG'], 2) . "</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;f.</td>
                                    <td align=\"left\" width=\"270\">Pajak Kurang atau Lebih Bayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> 0.00</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;g.</td>
                                    <td align=\"left\" width=\"270\">Sanksi Administrasi Telat Lapor </td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> " . number_format($DATA['pajak']['CPM_DENDA_TERLAMBAT_LAP'], 2) . "</td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;h.</td>
                                    <td align=\"left\" width=\"270\">Jumlah Pajak yang dibayar</td>
                                    <td width=\"30\" align=\"left\">Rp.</td>
                                    <td align=\"right\" width=\"150\"> <strong>" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</strong></td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;i.</td>
                                    <td align=\"left\" width=\"270\">Data Pendukung</td>
                                    <td align=\"left\" width=\"420\"> </td>
                                </tr>
                                <tr>
									<td align=\"right\" width=\"40\">a).</td>
                                    <td align=\"left\" width=\"250\">SPTPD</td>
                                    <td align=\"left\" width=\"420\">
										<table width=\"80\" border=\"0\">
											<tr>
												<td width=\"30\">
													<table cellpadding=\"0\" border=\"1\">
														<tr style=\"background-color:#EBEBEB;\">
															<td width=\"20\" align=\"center\">&nbsp;</td>
														</tr>
													</table>
												</td>
												<td width=\"50\">1. Ada / </td>
												<td width=\"30\">
													<table cellpadding=\"0\" border=\"1\">
														<tr style=\"background-color:#EBEBEB;\">
															<td width=\"20\" align=\"center\">&nbsp;</td>
														</tr>
													</table>
												</td>
												<td width=\"100\">2. Tidak Ada</td>
											</tr>
										</table>
                                    </td>
                                </tr>
                                <tr>
									<td align=\"right\" width=\"40\">b).</td>
                                    <td align=\"left\" width=\"250\">Rekapitulasi Penjualan / Omzet</td>
                                    <td align=\"left\" width=\"420\">
										<table width=\"80\" border=\"0\">
											<tr>
												<td width=\"30\">
													<table cellpadding=\"0\" border=\"1\">
														<tr style=\"background-color:#EBEBEB;\">
															<td width=\"20\" align=\"center\">&nbsp;</td>
														</tr>
													</table>
												</td>
												<td width=\"50\">1. Ada / </td>
												<td width=\"30\">
													<table cellpadding=\"0\" border=\"1\">
														<tr style=\"background-color:#EBEBEB;\">
															<td width=\"20\" align=\"center\">&nbsp;</td>
														</tr>
													</table>
												</td>
												<td width=\"100\">2. Tidak Ada</td>
											</tr>
										</table>
                                    </td>
                                </tr>
                                <tr>
									<td align=\"right\" width=\"40\">c).</td>
                                    <td align=\"left\" width=\"250\">Rekapitulasi Penggunaan Bill / Bonbill</td>
                                    <td align=\"left\" width=\"420\">
										<table width=\"80\" border=\"0\">
											<tr>
												<td width=\"30\">
													<table cellpadding=\"0\" border=\"1\">
														<tr style=\"background-color:#EBEBEB;\">
															<td width=\"20\" align=\"center\">&nbsp;</td>
														</tr>
													</table>
												</td>
												<td width=\"50\">1. Ada / </td>
												<td width=\"30\">
													<table cellpadding=\"0\" border=\"1\">
														<tr style=\"background-color:#EBEBEB;\">
															<td width=\"20\" align=\"center\">&nbsp;</td>
														</tr>
													</table>
												</td>
												<td width=\"100\">2. Tidak Ada</td>
											</tr>
										</table>
                                    </td>
                                </tr>
                                <tr>
									<td align=\"left\" width=\"20\">&nbsp;&nbsp;j.</td>
                                    <td align=\"left\" width=\"270\">Keterangan</td>
                                    <td align=\"left\" width=\"420\">{$DATA['pajak']['CPM_KETERANGAN']}</td>
                                </tr>
                            </table>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
							<b>C. PERNYATAAN</b>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" align=\"center\">
							<table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"5s\">
                                <tr>
                                    <td colspan=\"2\">Dengan menyadari sepenuhnya akan segala akibat termasuk sanksi-sanksi sesuai dengan ketentuan perundang-undangan yang berlaku, saya atau yang saya beri kuasa menyatakan bahwa apa yang telah kami beritahukan tersebut diatas berserta lampiran-lampirannya adalah benar, lengkap dan jelas.
                                    </td>
                                </tr>
								<tr>
									<td width=\"355\"></td>
									<td align=\"center\">
                                    Gedong Tataan, {$tgl_pengesahans}<br/>
										Wajib Pajak<br/><br/><br/><br/><br/>
										<u>{$DATA['profil']['CPM_NAMA_WP']}</u>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\" align=\"center\" style=\"background-color:#CCC\">
							<b>D. DIISI OLEH PETUGAS PENDATA</b>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\"><table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"10\">
								<tr>
									<td><table width=\"700\" cellpadding=\"0\" border=\"0\" cellspacing=\"0\">
											<tr>
												<td width=\"150\">Diterima Tanggal</td>
												<td width=\"260\" colspan=\"2\">: {$tanggal_verifikasi}</td>
											</tr>
											<tr>
												<td width=\"150\">Nama Petugas</td>
												<td width=\"260\" colspan=\"2\">: {$petugas_verifikasi}</td>
											</tr>
											<tr>
												<td width=\"150\">NIP.</td>
												<td width=\"260\" colspan=\"2\">: </td>
											</tr>
											<tr>
												<td colspan=\"3\"><br/></td>
											</tr>
											<tr>
												<td width=\"150\">Tanda Tangan</td>
												<td width=\"195\">:</td>
												<td width=\"345\" align=\"center\">
													<u>{$petugas_verifikasi}</u>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					
								<span style=\"font-size:24px\"><i>BAPENDA PESAWARAN {$tgl_cetak}</i></span>

                </table>";

        require_once("{$sRootPath}inc/payment/tcpdf/sptpd_pdf.php");
        $pdf = new SPTPD_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 5, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($page1, true, false, false, false, '');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 27, 7, 8, '', '', '', '', false, 300, '', false);


        $pdf->Output('sptpd-restoran.pdf', 'I');
    }

    /*public function print_sspd() {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
		$NAMA_PENGELOLA = $config['NAMA_BADAN_PENGELOLA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $KODE_AREA = $config['KODE_AREA'];
		$TGL_PENETAPAN = $this->getTanggalPenetapan($this->id_pajak, $this->CPM_ID);
        $bulan_pajak = str_pad($this->CPM_MASA_PAJAK, 2, "0", STR_PAD_LEFT);
        $PERIODE = "000000{$this->CPM_TAHUN_PAJAK}{$bulan_pajak}";

        $KODE_PAJAK = $this->idpajak_sw_to_gw[$this->id_pajak];
        if ($DATA['pajak']['CPM_TIPE_PAJAK'] == 2) {
            $KODE_PAJAK = $this->non_reguler[$this->id_pajak];
            #$PERIODE = "000" . substr($this->CPM_NO, 0, 9);
            $PERIODE = substr($this->CPM_NO, 14, 2)."0" . substr($this->CPM_NO, 0, 9);
        }
        $KODE_PAJAK = str_pad($KODE_PAJAK, 4, "0", STR_PAD_LEFT);

        $html = "<table width=\"710\" class=\"main\" border=\"1\">
                    <tr>
                        <td><table width=\"710\" border=\"1\" cellpadding=\"10\">
                                <tr>
                                    <th valign=\"top\" width=\"450\" align=\"center\">
                                        ".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />
                                        ".strtoupper($NAMA_PENGELOLA)."<br /><br />
                                        <font class=\"normal\">{$JALAN}<br>
                                        {$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                    </th>
                                    <th width=\"260\" align=\"center\">
                                        SURAT SETORAN<br/>
                                        PAJAK DAERAH
                                        (SSPD)<br/><br/>
                                        Tahun : {$DATA['pajak']['CPM_TAHUN_PAJAK']}
                                    </th>
                                 </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"960\" border=\"0\" cellpadding=\"5\">
                                <tr>
                                    <td width=\"230\">Nama Wajib Pajak</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr style=\"vertical-align: top\">
                                    <td>Alamat Wajib Pajak</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Nama Objek Pajak</td>
                                    <td>: {$DATA['profil']['CPM_NAMA_OP']}</td>
                                </tr>
                                <tr style=\"vertical-align: top\">
                                    <td>Alamat Objek Pajak</td>
                                    <td>: {$DATA['profil']['CPM_ALAMAT_OP']}</td>
                                </tr>
                                <tr>
                                    <td>Jenis Pajak</td>
                                    <td>: Pajak Restoran</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">NPWPD</td>
                                    <td>: ".Pajak::formatNPWPD($DATA['profil']['CPM_NPWPD'])."</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Kode Area</td>
                                    <td>: {$KODE_AREA}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Tipe Pajak</td>
                                    <td>: {$KODE_PAJAK}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Periode / Kode Bayar</td>
                                    <td>: {$PERIODE}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">NO SSPD</td>
                                    <td>: {$DATA['pajak']['CPM_NO_SSPD']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Masa Pajak</td>
                                    <td>: {$DATA['pajak']['CPM_MASA_PAJAK1']} - {$DATA['pajak']['CPM_MASA_PAJAK2']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Tanggal Lapor</td>
                                    <td>: {$DATA['pajak']['CPM_TGL_LAPOR']}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Tanggal Penetapan</td>
                                    <td>: {$TGL_PENETAPAN}</td>
                                </tr>
                                <tr>
                                    <td width=\"230\">Tanggal Jatuh Tempo</td>
                                    <td>: {$DATA['pajak']['CPM_TGL_JATUH_TEMPO']}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><table width=\"900\" border=\"0\" class=\"child\" cellpadding=\"0\" cellspacing=\"0\">
                                <tr>
                                    <td><table width=\"900\" border=\"1\" cellpadding=\"3\">
                                            <tr>
                                                <th width=\"50\" align=\"center\">No.</th>
                                                <th width=\"400\" align=\"center\">RINCIAN</th>
                                                <th width=\"260\" align=\"center\">JUMLAH</th>
                                            </tr>
                                            <tr>
                                                <td>1.</td>
                                                <td align=\"left\">Pembayaran pajak Restoran {$DATA['profil']['CPM_NAMA_OP']}
                                                    <br/>Keterangan : {$DATA['pajak']['CPM_KETERANGAN']}</td>
                                                <td align=\"right\">" . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td>2.</td>
                                                <td align=\"left\">Biaya lain</td>
                                                <td align=\"right\">0</td>
                                            </tr>
                                            <tr>
                                                <td>3.</td>
                                                <td align=\"left\">Biaya admin</td>
                                                <td align=\"right\">0</td>
                                            </tr>
                                            <tr>
                                                <td>4.</td>
                                                <td align=\"left\"></td>
                                                <td align=\"right\"></td>
                                            </tr>
                                            <tr>
                                                <td>5.</td>
                                                <td align=\"left\"></td>
                                                <td align=\"right\"></td>
                                            </tr>
                                            <tr>
                                                <td align=\"center\" colspan=\"2\"><i>JUMLAH</i></td>
                                                <td align=\"right\">Rp. " . number_format($DATA['pajak']['CPM_TOTAL_PAJAK'], 2) . "</td>
                                            </tr>
                                            <tr>
                                                <td colspan=\"3\">
                                                    Dengan Huruf : {$this->SayInIndonesian($DATA['pajak']['CPM_TOTAL_PAJAK'])}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align=\"right\"><table width=\"300\" border=\"0\" align=\"left\" class=\"header\" cellpadding=\"5\">
                                <tr>
                                    <td width=\"430\"></td>
                                    <td width=\"280\" align=\"center\">
                                    {$KOTA}, " . date("d") . " {$this->arr_bulan[(int) date("m")]} " . date("Y") . "<br/>
                                    Penyetor<br/><br/>
                                    <br/>
                                    (" . str_pad("", 50, "..", STR_PAD_RIGHT) . ")<br/>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align=\"right\"><table width=\"300\" border=\"1\" align=\"left\" class=\"header\" cellpadding=\"5\">
                                <tr>
                                    <td width=\"355\">SSPD ini berlaku setelah dilampiri dengan bukti pembayaran yang sah dari Bank</td>
                                    <td width=\"355\" align=\"left\">Pembayaran dapat dilakukan melalui teller dan ATM Bank Nagari terdekat</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
						<td>
							<font size='2' color=red>
							Jatuh tempo : {$DATA['pajak']['CPM_TGL_JATUH_TEMPO']}
							Denda 2% per bulan maksimal 24 bulan
							</font>
						</td>
					</tr>
                </table>";

        require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('9 PAJAK ONLINE');
        $pdf->SetSubject('9 PAJAK ONLINE spppd');
        $pdf->SetKeywords('9 PAJAK ONLINE');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 8, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 6, 18, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sspd-restoran.pdf', 'I');
    }*/

    public function read_dokumen()
    {
        if (isset($_REQUEST['read']) && $_REQUEST['read'] == 0) {
            $idtran = $_REQUEST['idtran'];
            $select = "SELECT CPM_TRAN_READ FROM {$this->PATDA_RESTORAN_DOC_TRANMAIN} WHERE CPM_TRAN_ID='{$idtran}'";
            $result = mysqli_query($this->Conn, $select);
            $data = mysqli_fetch_assoc($result);

            $read = $data['CPM_TRAN_READ'];
            $read = (trim($read) == "") ? ";{$_SESSION['uname']};" : "{$read};{$_SESSION['uname']};";
            $query = "UPDATE {$this->PATDA_RESTORAN_DOC_TRANMAIN} SET CPM_TRAN_READ = '{$read}' WHERE CPM_TRAN_ID='{$idtran}'";
            mysqli_query($this->Conn, $query);
        }
    }

    public function read_dokumen_notif()
    {
        $arr_tab = explode(";", $_POST['tab']);

        $notif = array();
        $notif['draf'] = 0;
        $notif['proses'] = 0;
        $notif['ditolak'] = 0;
        $notif['disetujui'] = 0;

        $notif['draf_ply'] = 0;
        $notif['proses_ply'] = 0;
        $notif['ditolak_ply'] = 0;
        $notif['disetujui_ply'] = 0;

        $notif['tertunda'] = 0;
        $notif['ditolak_ver'] = 0;
        $notif['disetujui_ver'] = 0;

        $where = " (tr.CPM_TRAN_READ not like '%;{$_SESSION['uname']};%' OR tr.CPM_TRAN_READ is null) AND ";
        $query = "SELECT count(pj.CPM_ID) as total
                    FROM {$this->PATDA_RESTORAN_DOC} pj INNER JOIN {$this->PATDA_RESTORAN_PROFIL} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                    INNER JOIN {$this->PATDA_RESTORAN_DOC_TRANMAIN} tr ON pj.CPM_ID = tr.CPM_TRAN_RESTORAN_ID
                    WHERE ";

        if (in_array("draf", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '1' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['draf'] = (int) $data['total'];
        }
        if (in_array("proses", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['proses'] = (int) $data['total'];
        }
        if (in_array("ditolak", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '4'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['ditolak'] = (int) $data['total'];
        }
        if (in_array("disetujui", $arr_tab)) {
            $w = $where . " pr.CPM_NPWPD = '{$_SESSION['npwpd']}' AND tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['disetujui'] = (int) $data['total'];
        }

        if (in_array("draf_ply", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '1' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['draf_ply'] = (int) $data['total'];
        }
        if (in_array("proses_ply", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['proses_ply'] = (int) $data['total'];
        }
        if (in_array("ditolak_ply", $arr_tab) || in_array("ditolak_ver", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '4'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result)) {
                $notif['ditolak_ply'] = (int) $data['total'];
                $notif['ditolak_ver'] = (int) $data['total'];
            }
        }
        if (in_array("disetujui_ply", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['disetujui_ply'] = (int) $data['total'];
        }

        if (in_array("tertunda", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '2' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['tertunda'] = $data['total'];
        }
        if (in_array("disetujui_ver", $arr_tab)) {
            $w = $where . " tr.CPM_TRAN_STATUS = '5' AND tr.CPM_TRAN_FLAG='0'";
            $q = $query . $w;
            $result = mysqli_query($this->Conn, $q);
            if ($data = mysqli_fetch_assoc($result))
                $notif['disetujui_ver'] = (int) $data['total'];
        }
        echo $this->Json->encode($notif);
    }

    public function grid_data_pembanding_detail()
    {
        try {
            $p = $this->Json->decode(base64_decode($_REQUEST['p']));

            $arr_config = $this->get_config_value($this->_a);
            $dbName = $arr_config['PATDA_TB_DBNAME'];
            $dbHost = $arr_config['PATDA_TB_HOSTPORT'];
            $dbPwd = $arr_config['PATDA_TB_PASSWORD'];
            $dbTable = $arr_config['PATDA_TB_TABLE'];
            $dbUser = $arr_config['PATDA_TB_USERNAME'];

            $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
            //mysqli_select_db($dbName);

            $where = "NOP = '{$p->CPM_NOP}' ";
            $where .= "AND NPWPD = '{$p->CPM_NPWPD}' ";
            $where .= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$p->TAHUN_PAJAK}\" ";
            $where .= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$p->MASA_PAJAK}\" ";
            $where .= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
            $where .= (isset($_REQUEST['CPM_DEVICE_ID']) && $_REQUEST['CPM_DEVICE_ID'] != "") ? " AND DeviceId = \"{$_REQUEST['CPM_DEVICE_ID']}\" " : "";

            $where .= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " AND DATE_FORMAT(TransactionDate,\"%d-%m-%Y %h:%i:%s\") between
                    CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\") and
                    CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\")  " : "";

            $query = "select
                        DeviceId,
                        NotAdmitReason,
                        NPWPD as CPM_NPWPD,
                        NOP as CPM_NOP,
                        TransactionNumber,
                        TransactionDate,
                        TransactionAmount as total
                        from {$dbTable}
                        WHERE {$where} ";
            $result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
            $recordCount = mysqli_num_rows($result);

            $q = $query .= "LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($Conn_gw, $query) or die(mysqli_error($Conn_gw));
            $rows = array();

            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($data = mysqli_fetch_assoc($result)) {
                $dataTapbox = array();
                $dataTapbox = array_merge($dataTapbox, array("NO" => ++$no));
                $dataTapbox['CPM_DEVICE_ID'] = $data['DeviceId'];
                $dataTapbox['CPM_NPWPD'] = $data['CPM_NPWPD'];
                $dataTapbox['CPM_NOP'] = $data['CPM_NOP'];
                $dataTapbox['TRAN_NUMBER'] = $data['TransactionNumber'];
                $dataTapbox['TRAN_DATE'] = $data['TransactionDate'];
                $dataTapbox['AMOUNT'] = number_format($data['total'], 2);
                $NotAdmitReason = $data['NotAdmitReason'] == "" ? "..." : $data['NotAdmitReason'];
                $dataTapbox['ADMIT'] = "<a href='javascript:void(0)' onclick='javascript:admit(\"{$dataTapbox['TRAN_NUMBER']}\",\"{$this->_a}\")' id='link-{$dataTapbox['TRAN_NUMBER']}'>{$NotAdmitReason}</a><div class='admit' ket='{$NotAdmitReason}' id='admit-{$dataTapbox['TRAN_NUMBER']}'></div>";

                $rows[] = $dataTapbox;
            }
            #query select list data

            $jTableResult = array();
            $jTableResult['q'] = $q;
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = $recordCount;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    public function filtering_pembanding_detail($id)
    {
        $p = $this->Json->decode(base64_decode($_REQUEST['p']));
        $q = $this->Json->decode(base64_decode($_REQUEST['q']));

        $html = "<div class=\"filtering\">
                    <form>
                        <input type='hidden' id=\"HIDDEN-{$id}\" tahun=\"{$p->TAHUN_PAJAK}\" a=\"{$this->_a}\" npwpd=\"{$p->CPM_NPWPD}\" nop=\"{$p->CPM_NOP}\" bulan=\"{$p->MASA_PAJAK}\">
                        <table>
                            <tr>
                                <td style='background:transparent;padding:2px'>No. Transaksi</td>
                                <td style='background:transparent;padding:2px'>: <input type=\"text\" name=\"NO_TRAN-{$id}\" id=\"NO_TRAN-{$id}\" >
                                </td>
                                <td style='background:transparent;padding:2px'></td>
                            </tr>
                            <tr>
                                <td style='background:transparent;padding:2px'>Tanggal Transaksi</td>
                                <td style='background:transparent;padding:2px'>:
                                <input type=\"text\" name=\"TRAN_DATE1-{$id}\" id=\"TRAN_DATE1-{$id}\" readonly onclick=\"javascript:openDate(this);\"><input type=\"button\" value=\"x\" onclick=\"javascriopt:$('#TRAN_DATE1-{$id}').val('');\">
                                    s.d
                                <input type=\"text\" style='width:143px' name=\"TRAN_DATE2-{$id}\" readonly id=\"TRAN_DATE2-{$id}\" onclick=\"javascript:openDate(this);\"><input type=\"button\" value=\"x\" onclick=\"javascriopt:$('#TRAN_DATE2-{$id}').val('');\">
                                </td>
                                <td style='background:transparent;padding:2px'>
                                    <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                                    <button type=\"button\" id=\"cetak-{$id}\" onclick=\"javascript:download_excel('{$id}','{$q->url}');\">Cetak Excel</button>
									<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div> ";
        return $html;
    }

    public function grid_table_pembanding_detail()
    {
        $DIR = "PATDA-V1";
        $modul = "restoran";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering_pembanding_detail($this->_i)}
                <div id=\"tapboxPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        $(\".date\").datepicker({
                            showOn: \"button\",
                            buttonImage: \"images/calendar.gif\",
                            buttonImageOnly: true,
                            buttonText: \"Select date\"
                        });
                        $('#tapboxPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: 15,
                            sorting: false,
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=list&a={$this->_a}&m={$this->_m}&p={$_REQUEST['p']}',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '5%'},
                                CPM_NOP: {title: 'NOP',width: '5%'},
                                TRAN_NUMBER: {title: 'Nomor Transaksi',width: '7%'},
                                TRAN_DATE: {title: 'Tanggal Transaksi',width: '10%'},
                                AMOUNT: {title: 'Total Pajak',width: '6%'},
                                ADMIT: {title: 'Alasan tidak diakui',width: '10%'}
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#tapboxPajak-{$this->_i}').jtable('load', {
                                NO_TRAN : $('#NO_TRAN-{$this->_i}').val(),
                                CPM_DEVICE_ID : $('#CPM_DEVICE_ID-{$this->_i}').val(),
                                TRAN_DATE1 : $('#TRAN_DATE1-{$this->_i}').val(),
                                TRAN_DATE2 : $('#TRAN_DATE2-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                    });
                    function openDate(obj) {
                        $(obj).datepicker({dateFormat: 'dd-mm-yy'});
                        $(obj).datepicker('show');
                    }
                </script>";
        echo $html;
    }

    public function get_previous_pajak($npwpd, $nop)
    {
        $Op = new ObjekPajak();
        $arr_rekening = $this->getRekening("4.1.01.07");
        $pajak_atr = array();
        $list_nop = array();

        $query = "
			SELECT DOC.*, DATE_FORMAT(DOC.CPM_TGL_JATUH_TEMPO,'%d-%m-%Y') as CPM_TGL_JATUH_TEMPO
			FROM PATDA_RESTORAN_DOC AS DOC
			INNER JOIN PATDA_RESTORAN_PROFIL PR ON DOC.CPM_ID_PROFIL = PR.CPM_ID
			WHERE PR.CPM_NPWPD = '{$npwpd}' AND PR.CPM_NOP = '{$nop}' AND
			str_to_date(DOC.CPM_TGL_LAPOR,'%d-%m-%Y') = (
				SELECT MAX(str_to_date(DOC.CPM_TGL_LAPOR,'%d-%m-%Y'))
				FROM PATDA_RESTORAN_DOC AS DOC
				INNER JOIN PATDA_RESTORAN_PROFIL PR ON DOC.CPM_ID_PROFIL = PR.CPM_ID
				WHERE PR.CPM_NPWPD = '{$npwpd}' AND PR.CPM_NOP = '{$nop}'
			)";
        // echo $query;
        $result = mysqli_query($this->Conn, $query);
        $pajak = $this->get_field_array($result);

        $ms = $this->inisialisasi_masa_pajak();

        if (empty($pajak['CPM_ID'])) {

            $pajak['CPM_TAHUN_PAJAK'] = $ms['tahun_pajak'];
            $pajak['CPM_MASA_PAJAK'] = $ms['masa_pajak'];
            $pajak['CPM_MASA_PAJAK1'] = $ms['masa_pajak1'];
            $pajak['CPM_MASA_PAJAK2'] = $ms['masa_pajak2'];
            $pajak['CPM_HARGA'] = 0;

            $profil = $Op->get_last_profil($npwpd, $nop);

            $tarif = ($profil['CPM_REKENING'] == '') ? 0 : $arr_rekening['ARR_REKENING'][$profil['CPM_REKENING']]['tarif'];
            $list_nop = $Op->get_list_nop($npwpd);
        } else { //if data available
            $profil = $Op->get_profil_byid($pajak['CPM_ID_PROFIL']);
            $tarif = $pajak['CPM_TARIF_PAJAK'];
            $list_nop = $Op->get_list_nop($npwpd);
        }

        $pajak['CPM_ID'] = '';
        $pajak['CPM_NO'] = '';
        $pajak['CPM_ID_PROFIL'] = '';
        $pajak['CPM_HARGA'] = 0;

        $pajak['CPM_TERBILANG'] = $this->SayInIndonesian($pajak['CPM_TOTAL_PAJAK']);
        $pajak['CPM_JENIS_PAJAK'] = $this->id_pajak;
        $pajak['ARR_TIPE_PAJAK'] = $this->arr_tipe_pajak_restoran;

        $pajak = array_merge($pajak, $arr_rekening);

        //echo '<pre>',print_r($pajak,true),'</pre>';exit;
        return array(
            'pajak' => $pajak,
            'tarif' => $tarif,
            'profil' => $profil,
            'list_nop' => $list_nop
        );
    }

    function download_pajak_xls()
    {
        exit;
        $where = "(";
        $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

        if ($this->_mod == "pel") { #pelaporan
            if ($this->_s == 0) { #semua data
                $where = " pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' AND ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ver") { #verifikasi
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "per") { #persetujuan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        } elseif ($this->_mod == "ply") { #pelayanan
            if ($this->_s == 0) { #semua data
                $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
            } elseif ($this->_s == 2) { #tab proses
                $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
            } else {
                $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
            }
        }
        $where .= ") ";
        //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
        $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
        $where .= (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") ? " AND STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and
                    STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\")  " : "";

        $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);

        #query select list data
        $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK,
                        CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK,
                        STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y') as CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                        pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG,
                        tr.CPM_TRAN_READ, tr.CPM_TRAN_ID
                        FROM PATDA_{$JENIS_PAJAK}_DOC{$this->SUFIKS} pj INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL{$this->SUFIKS} pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                        INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN{$this->SUFIKS} tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID
                        WHERE {$where}
                        ORDER BY 1";

        //echo "<pre>" . print_r($_REQUEST, true) . "</pre>"; echo $query;exit;
        $res = mysqli_query($this->Conn, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("9 PAJAK ONLINE")
            ->setSubject("9 PAJAK ONLINE bphtb")
            ->setDescription("bphtb")
            ->setKeywords("9 PAJAK ONLINE");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'No.')
            ->setCellValue('B1', 'Tanggal Lapor')
            ->setCellValue('C1', 'Nomor Laporan')
            ->setCellValue('D1', 'NPWPD')
            ->setCellValue('E1', 'Nama')
            ->setCellValue('F1', 'Tahun Pajak')
            ->setCellValue('G1', 'Masa Pajak')
            ->setCellValue('H1', 'Total Pajak')
            ->setCellValue('I1', 'Versi Dokumen')
            ->setCellValue('J1', 'User Input');

        if ($this->_s == 0) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K1', 'Status'); #"CPM_TRAN_STATUS
        }

        if ($this->_s == 4) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L1', 'Keterangan'); #CPM_TRAN_INFO
        }


        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);

        while ($rowData = mysqli_fetch_assoc($res)) {
            $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $rowData['CPM_TGL_LAPOR']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NO'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_NAMA_WP']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_TAHUN_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_MASA_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_TOTAL_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_VERSION']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['CPM_AUTHOR']);

            if ($this->_s == 0) {
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $this->arr_status[$rowData['CPM_TRAN_STATUS']]);
            }

            if ($this->_s == 4) {
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['CPM_TRAN_INFO']);
            }
            $row++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "L"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        ob_clean();
        // Redirect output to a client�s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}
