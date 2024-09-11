<?php

class Paksa extends Pajak {

    function __construct() {
        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
    }

    public function get_pajak() {
        #inisialisasi data kosong
        $respon['pajak'] = array(
            "CPM_ID" => "",
            "CPM_NPWPD" => "",
            "CPM_NAMA_OP" => "",
            "CPM_ALAMAT_OP" => "",
            "CPM_KECAMATAN_OP" => "",
			"CPM_NAMA_WP" => "",
            "CPM_NO_SURAT" => "",
            "CPM_JENIS_PAJAK" => "",
            "CPM_MASA_PAJAK" => "",
            "CPM_TAHUN_PAJAK" => "",
            "CPM_NO_SKPD" => "",
            "CPM_TGL_SKPD" => "",
            "CPM_JATUH_TEMPO" => "",
            "CPM_JUMLAH_TUNGGAKAN" => "",
            "CPM_TERBILANG" => "",
            "CPM_TGL_INPUT" => "",
            "CPM_AUTHOR" => ""
        );

        #query untuk mengambil data pajak
        $query = "SELECT * FROM PATDA_PAKSA WHERE CPM_ID = '{$this->_id}'";

        $result = mysqli_query($this->Conn, $query);

        #jika ada data 
        if (mysqli_num_rows($result) > 0) {
            $respon['pajak'] = mysqli_fetch_assoc($result);
        }
        return $respon;
    }

    public function filtering($id) {
        $html = "<div class=\"filtering\">
                    <form>
                        <label>NPWPD :</label> <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" class=\"form-control\" style=\"height: 32px; width: 144px; display: inline-block\" >
                        <label style=\"margin-left: 5px\">No. Surat Paksa :</label> <input type=\"text\" name=\"CPM_NO_SURAT-{$id}\" id=\"CPM_NO_SURAT-{$id}\" class=\"form-control\" style=\"height: 32px; width: 144px; display: inline-block\" >    
                        <label style=\"margin-left: 5px\">TAHUN :</label> <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\" class=\"form-control\" style=\"height: 32px; width: 144px; display: inline-block\" >";
        $html.= "<option value=''>All</option>";
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $html.= "<option value='{$th}'>{$th}</option>";
        }
        $html.= "</select> <label style=\"margin-left: 5px\">MASA PAJAK :</label> <select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\" class=\"form-control\" style=\"height: 32px; width: 144px; display: inline-block\" >";
        $html.= "<option value=''>All</option>";
        foreach ($this->arr_bulan as $x => $y) {
            $html.= "<option value='{$x}'>{$y}</option>";
        }
        $html.= "</select>    
                        <button type=\"submit\" id=\"cari-{$id}\" class=\"btn btn-primary lm-btn\" style=\"font-size: 0.7rem !important; margin-left: 10px\" ><i class=\"fa fa-search\"></i> Cari</button>
                    </form>
                </div> ";
        return $html;
    }

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "surat-paksa-real";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting: 'CPM_TGL_INPUT DESC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false}, 
                                CPM_NO_SURAT: {title: 'No. Surat Teguran',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_TGL_INPUT: {title:'Tanggal Input',width: '10%'}, 
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
				CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '10%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '10%'},
                                CPM_JUMLAH_TUNGGAKAN: {title: 'Tunggakan Pajak',width: '10%'},
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
                                CPM_NO_SURAT : $('#CPM_NO_SURAT-{$this->_i}').val(),    
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });
                </script>";
        echo $html;
    }

    public function grid_data() {
        try {
            $where = "1=1";
            
            $_REQUEST['CPM_MASA_PAJAK'] = (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? str_pad($_REQUEST['CPM_MASA_PAJAK'],2,0,STR_PAD_LEFT) : ""; 
                
            $where.= (isset($_REQUEST['CPM_NO_SURAT']) && $_REQUEST['CPM_NO_SURAT'] != "") ? " AND CPM_NO_SURAT like \"{$_REQUEST['CPM_NO_SURAT']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM PATDA_PAKSA WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT *
                        FROM PATDA_PAKSA t
                        WHERE {$where}
                        ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {

                $row = array_merge($row, array("NO" => ++$no));

                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_ID']}&i={$this->_i}";
                $url = "main.php?param=" . base64_encode($base64);

		$row['CPM_JENIS_PAJAK'] = $this->arr_pajak[(int) $row['CPM_JENIS_PAJAK']];
                $row['CPM_MASA_PAJAK'] = $this->arr_bulan[(int) $row['CPM_MASA_PAJAK']];
                $row['CPM_NO_SURAT'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NO_SURAT']}</a>";
                $row['CPM_NPWPD'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_NPWPD']}</a>";

                $rows[] = $row;
            }

            $jTableResult = array();
            $jTableResult['q'] = $query;
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


    public function save() {
        $validasi = $this->validasi_save();

        if ($validasi['result'] == true) {
            $this->Message->clearMessage();

            #insert pajak baru
            $this->CPM_ID = c_uuid();
            $this->CPM_TGL_INPUT = date("d-m-Y");
            $this->CPM_JUMLAH_TUNGGAKAN = str_replace(",", "", $this->CPM_JUMLAH_TUNGGAKAN);

            $query = sprintf("INSERT INTO PATDA_PAKSA
                    (CPM_ID,
                    CPM_NPWPD,
                    CPM_NAMA_OP,
                    CPM_ALAMAT_OP,
					CPM_KECAMATAN_OP,
                    CPM_NAMA_WP,
                    CPM_NO_SURAT,
                    CPM_JENIS_PAJAK,
                    CPM_MASA_PAJAK,
                    CPM_TAHUN_PAJAK,
                    CPM_NO_SKPD,
                    CPM_TGL_SKPD,
                    CPM_JATUH_TEMPO,
                    CPM_JUMLAH_TUNGGAKAN,
                    CPM_TERBILANG,
                    CPM_TGL_INPUT,
                    CPM_AUTHOR)
                    VALUES ( '%s','%s','%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s',
                             '%s','%s','%s','%s','%s')", $this->CPM_ID, $this->CPM_NPWPD, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP, $this->CPM_KECAMATAN_OP, $this->CPM_NAMA_WP, $this->CPM_NO_SURAT, $this->CPM_JENIS_PAJAK, $this->CPM_MASA_PAJAK, $this->CPM_TAHUN_PAJAK, $this->CPM_NO_SKPD, $this->CPM_TGL_SKPD, $this->CPM_JATUH_TEMPO, $this->CPM_JUMLAH_TUNGGAKAN, $this->CPM_TERBILANG, $this->CPM_TGL_INPUT, $this->CPM_AUTHOR
            );
            $res = mysqli_query($this->Conn, $query);
            
            if($res){
				$_SESSION['_success'] = 'Dokumen berhasil disimpan';
			}else{
				$_SESSION['_error'] = 'Dokumen gagal disimpan';
			}
			
			return $res;
        }
        return false;
    }

    private function validasi_save() {
        $query = "SELECT * FROM PATDA_PAKSA WHERE CPM_NO_SURAT = '{$this->CPM_NO_SURAT}'";
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        $hasil = true;
        if (mysqli_num_rows($res) > 0) {
			$msg = "Gagal disimpan, Pajak dengan No. Paksa <b>{$this->CPM_NO}</b> sudah dilaporkan sebelumnya!";
            $this->Message->setMessage($msg);
            $_SESSION['_error'] = $msg;
            $hasil = false;
        }

        $respon['result'] = $hasil;
        $respon['data'] = $data;

        return $respon;
    }
    
    private function validasi_update() {
        $query = "SELECT * FROM PATDA_PAKSA WHERE CPM_NO_SURAT = '{$this->CPM_NO_SURAT}'";
        $res = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($res);

        $hasil = true;
        if (mysqli_num_rows($res) == 0) {
            $this->Message->setMessage("Gagal disimpan, Pajak dengan No. Paksa <b>{$this->CPM_NO}</b> sudah belum tersedia!");
            $hasil = false;
        }

        $respon['result'] = $hasil;
        $respon['data'] = $data;

        return $respon;
    }

    public function update() {
        $validasi = $this->validasi_update();

        if ($validasi['result'] == true) {
            $this->Message->clearMessage();
            $this->CPM_JUMLAH_TUNGGAKAN = str_replace(",", "", $this->CPM_JUMLAH_TUNGGAKAN);

            $query = sprintf("UPDATE PATDA_PAKSA set                
                    CPM_NPWPD = '%s',
                    CPM_NAMA_OP = '%s',
                    CPM_ALAMAT_OP = '%s',
                    CPM_KECAMATAN_OP = '%s',	
                    CPM_NAMA_WP = '%s',			
                    CPM_NO_SURAT = '%s',
                    CPM_JENIS_PAJAK = '%s',
                    CPM_MASA_PAJAK = '%s',
                    CPM_TAHUN_PAJAK = '%s',
                    CPM_NO_SKPD = '%s',
                    CPM_TGL_SKPD = '%s',
                    CPM_JATUH_TEMPO = '%s',
                    CPM_JUMLAH_TUNGGAKAN = '%s',
                    CPM_TERBILANG = '%s'
                    WHERE CPM_ID = '%s'", $this->CPM_NPWPD, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP, $this->CPM_KECAMATAN_OP, $this->CPM_NAMA_WP, $this->CPM_NO_SURAT, $this->CPM_JENIS_PAJAK, $this->CPM_MASA_PAJAK, $this->CPM_TAHUN_PAJAK, $this->CPM_NO_SKPD, $this->CPM_TGL_SKPD, $this->CPM_JATUH_TEMPO, $this->CPM_JUMLAH_TUNGGAKAN, $this->CPM_TERBILANG, $this->CPM_ID
            );
            $res = mysqli_query($this->Conn, $query);
            
            if($res){
				$_SESSION['_success'] = 'Dokumen berhasil diupdate';
			}else{
				$_SESSION['_error'] = 'Dokumen gagal diupdate';
			}
			
			return $res;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM PATDA_PAKSA WHERE CPM_ID ='{$this->CPM_ID}'";
        $res = mysqli_query($this->Conn, $query);
    }

    public function print_paksa() {
        global $sRootPath;
        $this->_id = $this->CPM_ID;
        $DATA = $this->get_pajak();

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];
        $KEPALA_DINAS_NAMA = $config['KEPALA_DINAS_NAMA'];
        $NIP = $config['KEPALA_DINAS_NIP'];        

        $html = "<table width=\"710\" class=\"main\" cellpadding=\"0\" border=\"1\" style=\"border:1px #000 solid\" cellspacing=\"0\">
                <tr>
                    <td><table width=\"700\" border=\"0\">
                            <tr>
                                <th valign=\"top\" align=\"center\">                                   
                                    ".strtoupper($JENIS_PEMERINTAHAN)." " . strtoupper($NAMA_PEMERINTAHAN) . "<br />      
									DINAS PENDAPATAN DAERAH<br /><br />        
									<font class=\"normal\">{$JALAN}<br/>{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                </th>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr><td><table width=\"710\" class=\"main\" cellpadding=\"5\" border=\"0\" cellspacing=\"0\">                                                
                        <tr class=\"first\">
                            <td valign=\"top\" align=\"center\" colspan=\"2\"><b>SURAT PAKSA</b><br/>Nomor : {$DATA['pajak']['CPM_NO_SURAT']}<br/></td>
                        </tr>                        
                        <tr>
                            <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\">
                                    <tr>
                                        <th align=\"left\" colspan=\"2\">DEMI KEADILAN BERDASARKAN KETUHANAN YANG MAHA ESA<br/>KEPALA DINAS PENDAPATAN {$JENIS_PEMERINTAHAN} {$NAMA_PEMERINTAHAN}</th>
                                    </tr>
                                </table>            
                            </td>
                        </tr>
                        <tr>
                            <td width=\"710\" colspan=\"2\" align=\"center\"><table width=\"100%\" border=\"0\" align=\"left\">
                                    <tr>
                                        <td colspan=\"2\">Menimbang bahwa : </td>
                                    </tr>
                                    <tr>
                                        <td>Nama Wajib Pajak / Penanggung Pajak</td>
                                        <td>: {$DATA['pajak']['CPM_NAMA_OP']} / {$DATA['pajak']['CPM_NAMA_WP']}</td>
                                    </tr>
                                    <tr>
                                        <td>Nomor Pokok Wajib Pajak (NPWP)</td>
                                        <td>: {$DATA['pajak']['CPM_NPWPD']}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat / tempat tinggal</td>
                                        <td>: {$DATA['pajak']['CPM_ALAMAT_OP']}</td>
                                    </tr>
                                    <tr>
                                        <td>Kecamatan</td>
                                        <td>: {$DATA['pajak']['CPM_KECAMATAN_OP']}</td>
                                    </tr>
                                    <tr>
                                        <td>Menunggak pajak sebagaimana tercantum di bawah ini</td>
                                    </tr>
                                </table>            
                            </td>
                        </tr>
                        
                        <tr>
                            <td width=\"710\" colspan=\"2\"><table width=\"700\" align=\"center\" cellpadding=\"2\" border=\"1\" cellspacing=\"0\">                                            
                                    <tr>
                                        <td width=\"140\"><b>Jenis Pajak</b></td>
                                        <td width=\"120\"><b>Tahun Pajak</b></td>
                                        <td width=\"300\"><b>Nomor dan tanggal SKPD / Kohir / SK. Pembetulan / SK. Keberatan / Putusan Banding</b></td>
                                        <td width=\"140\"><b>Jumlah Tunggakan (Rp. )</b></td>
                                    </tr>
                                    <tr>
                                        <td align=\"left\">{$this->arr_pajak[(int) $DATA['pajak']['CPM_JENIS_PAJAK']]}</td>
                                        <td>{$DATA['pajak']['CPM_TAHUN_PAJAK']}</td>
                                        <td>{$DATA['pajak']['CPM_NO_SKPD']} & {$DATA['pajak']['CPM_TGL_SKPD']}</td>
                                        <td align=\"right\">".number_format($DATA['pajak']['CPM_JUMLAH_TUNGGAKAN'],0)."</td>
                                    </tr>
                                </table> 
                            </td>  
                        </tr>
                        <tr>
                            <td width=\"710\" colspan=\"2\"><table width=\"100%\" border=\"0\">
                                    <tr>
                                        <td align=\"left\" colspan=\"3\">( {$DATA['pajak']['CPM_TERBILANG']} )</td>
                                    </tr>
                                    <tr>
                                        <td colspan=\"3\">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan=\"3\">&nbsp;</td>
                                    </tr>                                 
                                    <tr>
                                        <td colspan=\"3\" align=\"left\" width=\"700\">Dengan ini :<br/>
1. Memerintahkan Wajib Pajak / Penanggung pajak, untuk membayar jumlah tunggakan pajak tersebut ke kasir Penerima ( KP)
Kantor Dinas Pendapatan {$JENIS_PEMERINTAHAN} {$NAMA_PEMERINTAHAN}, ditambah dengan biaya penagihan dalam waktu 2 (dua) kali 24 (dua puluh empat) jam
sesudah surat paksa ini.<br/>
2. Memerintahkan kepada Jurusita Pajak yang melaksanakan Surat Paksa ini atau Jurusita Pajak lain yang ditunjuk untuk melaksanakan
surat paksa melakukan penyitaan atas barang barang milik Wajib Pajak / Penanggung Pajak Apabila dalam waktu 2 (dua) kali 24
(dua puluh empat) jam Surat Paksa ini tidak dipenuhi.<br/><br/>
                                        </td>                            
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
					<td align=\"left\" width=\"200\">&nbsp;</td>
                                        <td align=\"left\">Ditetapkan di {$PROVINSI}<br/>Pada Tanggal {$DATA['pajak']['CPM_TGL_INPUT']}<br/><br/>Kepala Dinas Pendapatan<br/>Kabupaten {$KOTA}<br/><br/><br/></td>
                                    </tr>
                                    <tr>
                                        <td align=\"left\"></td>
                                        <td align=\"left\"></td>
                                        <td align=\"left\">{$KEPALA_DINAS_NAMA}<br/>pembina Tk. 1<br/>NIP. {$NIP}</td>
                                    </tr>
                                </table>
                                </td>
                            </tr>                            
                        </table>    
                        </td>
                    </tr>
                </table>";

        require_once("{$sRootPath}inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('-');
        $pdf->SetSubject('-');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5, 14, 5);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image("{$sRootPath}view/Registrasi/configure/logo/{$LOGO_CETAK_PDF}", 45, 15, 25, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('sptpd-hotel.pdf', 'I');
    }

    function download_excel() {

        $arr_config = $this->get_config_value($this->_a);
        $dbName = $arr_config['PATDA_TB_DBNAME'];
        $dbHost = $arr_config['PATDA_TB_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_TB_PASSWORD'];
        $dbTable = $arr_config['PATDA_TB_TABLE'];
        $dbUser = $arr_config['PATDA_TB_USERNAME'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysqli_select_db();
        $devid = explode(";", $_REQUEST['alldevice']);
        $deviceId = "'" . implode("','", $devid) . "'";
        $where = "DeviceId in ({$deviceId}) ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%Y') = \"{$_REQUEST['TAHUN_PAJAK']}\" ";
        $where.= "AND DATE_FORMAT(TransactionDate,'%m') = \"{$_REQUEST['MASA_PAJAK']}\" ";
        $where.= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
        $where.= (isset($_REQUEST['CPM_DEVICE_ID']) && $_REQUEST['CPM_DEVICE_ID'] != "") ? " AND DeviceId = \"{$_REQUEST['CPM_DEVICE_ID']}\" " : "";

        $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " AND DATE_FORMAT(TransactionDate,\"%d-%m-%Y %h:%i:%s\") between 
                    CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\") and 
                    CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\")  " : "";

        $query = "select 
                        DeviceId, 
                        NotAdmitReason,
                        '{$_REQUEST['CPM_NPWPD']}' as CPM_NPWPD,
                        TransactionNumber,
                        TransactionDate,
                        TransactionAmount as total
                        from {$dbTable} 
                        WHERE {$where} ";


        $res = mysqli_query($Conn_gw, $query);

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("bphtb")
                ->setKeywords("-");

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'No.')
                ->setCellValue('B1', 'NPWPD')
                ->setCellValue('C1', 'Device Id')
                ->setCellValue('D1', 'Nomor Transaksi')
                ->setCellValue('E1', 'Tanggal Transaksi')
                ->setCellValue('F1', 'Total Pajak')
                ->setCellValue('G1', 'Alasan Tidak diakui');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);

        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['DeviceId'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['TransactionNumber']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['TransactionDate']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['total']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['NotAdmitReason']);
            $row++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Transaksi Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "G"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}

?>
