<?php
error_reporting(E_ALL ^ E_NOTICE);
class RekapPajak extends Pajak {

    function __construct() {
        parent::__construct();
    }

    public function filtering_rekap($id) {
        $opt_jenis_pajak = '<option value="">All</option>';
        foreach ($this->arr_pajak as $x => $y) {
            $opt_jenis_pajak .= "<option value=\"{$x}\">{$y}</option>";
        }

        $opt_tahun = "<option value=''>Pilih Tahun</option>";
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $opt_tahun .= "<option value='{$th}'>{$th}</option>";
        }

        $opt_rekening = '<option value="">All</option>';
        $reks = $this->getDataRekening();
        foreach($reks as $header=>$rek){
            $aRek = array_values($rek);
            if(count($aRek)>1){
                $opt_rekening .= "<option value=\"{$header}\">{$aRek[0]['nmheader3']}</option>";
                foreach($rek as $k=>$v){
                    $opt_rekening .= "<option value=\"{$k}\">&nbsp; $k - {$v['nmrek']}</option>";
                }
            }else{
                // $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']} ({$aRek[0]['kdrek']})</option>";
                $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']}</option>";
            }
        }

        $opt_kecamatan = '<option value="">All</option>';
        $query = "SELECT * FROM PATDA_MST_KECAMATAN order by CPM_KECAMATAN";
        $res = mysqli_query($this->Conn, $query);
        while($list = mysqli_fetch_object($res)){
            $opt_kecamatan .= "<option value=\"{$list->CPM_KEC_ID}\">{$list->CPM_KECAMATAN}</option>";
        }


        $html = "<div class=\"filtering\">
                    <form><table><tr valign=\"bottom\">
                        <td>
                            <input type='hidden' id=\"HIDDEN-{$id}\" a=\"{$this->_a}\">
                            <label>Jenis Pajak :</label><br><select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" class=\"form-control\" style=\"height: 37.2px; width: 168px; display: inline-block\">{$opt_jenis_pajak}</select>
                        </td>
                        <td><label>Tahun :</label><br><select name=\"simpatda_tahun_pajak-{$id}\" id=\"simpatda_tahun_pajak-{$id}\" class=\"form-control\" style=\"height: 37.2px; width: 120px; display: inline-block\">{$opt_tahun}</select></td>
                        <td><label>Kode Rekening :</label><br><select name=\"CPM_KODE_REKENING-{$id}\" style=\"max-width:200px\" id=\"CPM_KODE_REKENING-{$id}\" class=\"form-control\" style=\"height: 32px; width: 168px; display: inline-block\">{$opt_rekening}</select></td>
                        <td><label>Kecamatan :</label><br><select id=\"CPM_KECAMATAN-{$id}\" class=\"form-control\" style=\"height: 37.2px; width: 96px; display: inline-block\">{$opt_kecamatan}</select></td>
                        <td><label>Kelurahan :</label><br><select id=\"CPM_KELURAHAN-{$id}\" class=\"form-control\" style=\"height: 37.2px; width: 96px; display: inline-block\"><option value=\"\">All</option></select></td>
                        <td>
                            <button type=\"submit\" id=\"cari-{$id}\" class=\"btn btn-primary lm-btn\" style=\"font-size: 0.7rem !important; margin-left: 10px\" ><i class=\"fa fa-search\"></i> Cari</button>
                            <button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/rekap/svc-download.rekap.xls.php')\" class=\"btn btn-success lm-btn\" style=\"font-size: 0.7rem !important\"><i class=\"fa fa-download\"></i> Export to xls</button>
                            <!-- tambahan -->
                            <button type=\"button\" title=\"Rekap tahunan per mata pajak\" onclick=\"javascript:download_excel_new('function/PATDA-V1/rekap/svc-download.rekap.new.xls.php')\" class=\"btn btn-success lm-btn\" style=\"font-size: 0.7rem !important\" ><i class=\"fa fa-download\"></i> Export to xls (Format BPPRD)</button>
                        </td>
                    </tr></table></form>
                </div>";
        return $html;
    }

	public function grid_table_rekap() {
        // edit untuk format baru
        $DIR = "PATDA-V1";
        $modul = "rekap";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.filtering td{background:transparent}.filtering input,.filtering select{height:23px}.number{text-align:right}.text-center{text-align:center}</style>
                {$this->filtering_rekap($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"><div id='title-rekap'></div></div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        $(\".date\").datepicker({
                            dateFormat: \"yy-mm-dd\",
                            showOn: \"button\",
                            buttonImageOnly: false,
                            buttonText: \"..\"
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
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-rekap.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},                                 
                                CPM_KETETAPAN: {title: 'Jumlah Ketetapan (Rp)',listClass:'number', width: '10%'},
                                CPM_PENERIMAAN: {title: 'Jumlah Penerimaan (Rp)',listClass:'number', width: '10%'},
                                CPM_PIUTANG: {title: 'Sisa / Piutang (Rp)',listClass:'number', width: '10%'}
                            },
							recordsLoaded: function (event, data) {
								var res = data.serverResponse;
								for (var i in data.records) {
									if(data.records[i].NO==''){
										$('#laporanPajak-{$this->_i}').find('.jtable tbody tr:eq('+i+') td').css({'background-color':'#7cadc5','border':'1px #CCC solid','font-weight':'bold'});
									}
								}
							}
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            if($('#simpatda_tahun_pajak-{$this->_i}').val()==''){
                                $('#simpatda_tahun_pajak-{$this->_i}').focus();
                                $('tr.jtable-no-data-row td').html('Silakan pilih Tahun Pajak!');
                                return false;
                            }
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                jenis : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),                                    
                                simpatda_tahun_pajak : $('#simpatda_tahun_pajak-{$this->_i}').val(),                                    
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),                                    
                                CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),                                    
                                CPM_KODE_REKENING : $('#CPM_KODE_REKENING-{$this->_i}').val()                                    
                            });
			                $('#title-rekap').html('<div class=\"well container-fluid\"><div class=\"row text-center\"><div class=\"col-xs-12\"><b>REKAP PENERIMAAN DAN PIUTANG <br/>TAHUN '+$('#simpatda_tahun_pajak-{$this->_i}').val()+'</b></div></div></div>');	
                        });
                        $('tr.jtable-no-data-row td').html('Silakan lakukan pencarian!');

                        $(\"select#CPM_KECAMATAN-{$this->_i}\").change(function () {
                            showKelurahan({$this->_i});
                        });
            
            
                        function showKelurahan(sts) {
                            var id = $('select#CPM_KECAMATAN-{$this->_i}').val()
                            if(id==''){
                                $(\"select#CPM_KELURAHAN-{$this->_i}\").html('<option value=\"\">All</option>');
                                return false
                            }
                
                            var request = $.ajax({
                            url: \"function/PATDA-V1/pelayanan/svc-kecamatan.php\",
                                type: \"POST\",
                                data: {id: id, kel: 1},
                                dataType: \"json\",
                                beforeSend : function(d){
                                    $(\"select#CPM_KELURAHAN-{$this->_i}\").html('<option value=\"\">Loading...</option>');
                                },
                                success: function (data) {
                                    var c = data.msg.length;
                                    var options = \"\";
                                    if (parseInt(c)>=0){
                                        options += \"<option value=''>All</option>\";
                                        for (var i = 0; i < c; i++) {
                                            options += \"<option value='\" + data.msg[i].id + \"'>\" + data.msg[i].name + \"</option>\";
                                        }
                                        $(\"select#CPM_KELURAHAN-{$this->_i}\").html(options);
                                    }else{
                                        $(\"select#CPM_KELURAHAN-{$this->_i}\").html('<option value=\"\">All</option>');
                                    }
                
                                },
                                error : function(msg){
                                    $(\"select#CPM_KELURAHAN-{$this->_i}\").html('<option value=\"\">All</option>');
                                }  
                            })   
                        }
                    });
                </script>";
        echo $html;
    }

    public function grid_data_rekap() {
        try {
			if (!isset($_REQUEST['simpatda_tahun_pajak']) && $_REQUEST['simpatda_tahun_pajak']=="") {
                $jTableResult = array();
                $jTableResult['Result'] = "OK";
                print $this->Json->encode($jTableResult);
                exit;
            }
            $arr_config = $this->get_config_value($this->_a);

            $dbName = $arr_config['PATDA_DBNAME'];
            $dbHost = $arr_config['PATDA_HOSTPORT'];
            $dbPwd = $arr_config['PATDA_PASSWORD'];
            $dbUser = $arr_config['PATDA_USERNAME'];

            $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
            //mysqli_select_db(, $Conn_gw);

            $recordCount = 9;

            $where = "";
            // $jenis = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
            switch($_REQUEST['jenis']){
                case 1: $where .= "AND simpatda_type in ('11','31')";break;
                case 2 : $where .= "AND simpatda_type in ('6','26')";break;
                case 3 : $where .= "AND simpatda_type in ('4','24')";break;
                case 4 : $where .= "AND simpatda_type in ('9','29')";break;
                case 5 : $where .= "AND simpatda_type in ('10','30')";break;
                case 6 : $where .= "AND simpatda_type in ('8','28')";break;
                case 7 : $where .= "AND simpatda_type in ('7','27')";break;
                case 8 : $where .= "AND simpatda_type in ('5','25')";break;
                case 9: $where .= "AND simpatda_type in ('12','32')";break;
            }

            $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op like '%{$_REQUEST['CPM_KECAMATAN']}%'" : "";
            $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op like '%{$_REQUEST['CPM_KELURAHAN']}%' " : "";
            // $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND  CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";
            if(isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != ""){
                if(strlen($_REQUEST['CPM_KODE_REKENING'])==9)
                    $where.= " AND simpatda_rek like '{$_REQUEST['CPM_KODE_REKENING']}%' ";
                elseif(strlen($_REQUEST['CPM_KODE_REKENING'])>9)
                    $where.= " AND simpatda_rek = '{$_REQUEST['CPM_KODE_REKENING']}' ";
            }

            #query select list data        
            $query = "SELECT b.id_sw, b.jenis_sw as JENIS, 
			sum(a.CPM_KETETAPAN) as CPM_KETETAPAN, 
			sum(a.CPM_PENERIMAAN) as CPM_PENERIMAAN, 
			sum(a.CPM_PIUTANG) as CPM_PIUTANG 
			FROM
			((SELECT simpatda_type, simpatda_dibayar AS CPM_KETETAPAN, 0 AS CPM_PENERIMAAN, 0 AS CPM_PIUTANG FROM SIMPATDA_GW
			WHERE simpatda_tahun_pajak = '{$_REQUEST['simpatda_tahun_pajak']}' $where)	UNION 
			(SELECT simpatda_type, 0 AS CPM_KETETAPAN, simpatda_dibayar AS CPM_PENERIMAAN, 0 AS CPM_PIUTANG FROM SIMPATDA_GW
			WHERE simpatda_tahun_pajak = '{$_REQUEST['simpatda_tahun_pajak']}' and payment_flag = 1  $where) UNION 
			(SELECT simpatda_type, 0 AS CPM_KETETAPAN, 0 AS CPM_PENERIMAAN, simpatda_dibayar AS CPM_PIUTANG FROM SIMPATDA_GW
			WHERE simpatda_tahun_pajak = '{$_REQUEST['simpatda_tahun_pajak']}' and payment_flag = 0  $where)
			) AS a INNER JOIN SIMPATDA_TYPE b on a.simpatda_type = b.id GROUP BY b.id_sw;";
			
            $result = mysqli_query($this->Conn, $query);
		
			//echo mysqli_error($this->Conn);exit();
			$array_total = array("CPM_KETETAPAN"=>0,"CPM_PENERIMAAN"=>0,"CPM_PIUTANG"=>0);
			$rows = array();
			$no = 0;
			while($row = mysqli_fetch_assoc($result)){
				$array_total['CPM_KETETAPAN'] += $row['CPM_KETETAPAN'];
				$array_total['CPM_PENERIMAAN'] += $row['CPM_PENERIMAAN'];
				$array_total['CPM_PIUTANG'] += $row['CPM_PIUTANG'];
				
				$ketetapan = base64_encode($this->Json->encode(array("jenis"=>$row['id_sw'],"tahun"=>$_REQUEST['simpatda_tahun_pajak'],"detailtype"=>'ketetapan')));
				$penerimaan = base64_encode($this->Json->encode(array("jenis"=>$row['id_sw'],"tahun"=>$_REQUEST['simpatda_tahun_pajak'],"detailtype"=>'penerimaan')));
				$piutang = base64_encode($this->Json->encode(array("jenis"=>$row['id_sw'],"tahun"=>$_REQUEST['simpatda_tahun_pajak'],"detailtype"=>'piutang')));
				
				$row['NO'] = ++$no;
				$row['CPM_JENIS_PAJAK'] = $row['JENIS'];
				$row['CPM_KETETAPAN'] = (int) $row['CPM_KETETAPAN']>0? "<a href='javascript:void(0)' onclick='javascript:getDetail(\"{$ketetapan}\")'>".number_format($row['CPM_KETETAPAN'])."</a>" : 0;
				$row['CPM_PENERIMAAN'] = (int) $row['CPM_PENERIMAAN']>0? "<a href='javascript:void(0)' onclick='javascript:getDetail(\"{$penerimaan}\")'>".number_format($row['CPM_PENERIMAAN'])."</a>" : 0;
				$row['CPM_PIUTANG'] = (int) $row['CPM_PIUTANG']>0? "<a href='javascript:void(0)' onclick='javascript:getDetail(\"{$piutang}\")'>".number_format($row['CPM_PIUTANG'])."</a>" : 0;
				
				$rows[] = $row;
			}	
			
			$row = array();
			$row['NO'] = '';
			$row['CPM_JENIS_PAJAK'] = '<b>Total Keseluruhan</b>';
			$row['CPM_KETETAPAN'] = "<b>".number_format($array_total['CPM_KETETAPAN'])."</b>";
			$row['CPM_PENERIMAAN'] = "<b>".number_format($array_total['CPM_PENERIMAAN'])."</b>";
			$row['CPM_PIUTANG'] = "<b>".number_format($array_total['CPM_PIUTANG'])."</b>";
			
			$rows[] = $row;

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

    function download_excel_rekap_new() {

        $arr_config = $this->get_config_value($this->_a);

        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbUser = $arr_config['PATDA_USERNAME'];
        $patdaTb = $arr_config['PATDA_TABLE'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysqli_select_db($dbName, $Conn_gw);

        $recordCount = 9;

        $jPajak = $_REQUEST['simpatda_jenis_pajak']; // jenis pajak SW
        $thnPajak = $_REQUEST['simpatda_tahun_pajak']; // tahun pajak
        
        $query = "
            SELECT
                gw.op_nomor AS KOHIR,
                gw.op_nama AS NAMA_OP,
                gw.op_alamat AS ALAMAT_OP,
                gw.patda_total_bayar AS TOTAL_BAYAR,
                gw.simpatda_denda AS DENDA,
                MONTH ( gw.saved_date ) AS BULAN_BAYAR,
                gw.kecamatan_op AS KEC_ID,
                kec.cpm_kecamatan AS KECAMATAN,
                DATE_FORMAT( gw.saved_date, '%d-%m-%Y' ) AS TANGGAL_BAYAR,
                gw.simpatda_keterangan AS KETERANGAN,
                gw.payment_code AS KODE_BAYAR,
                t.id_sw AS KODE_JP,
                t.jenis AS NAMA_JP 
            FROM
                {$patdaTb} gw
                INNER JOIN PATDA_MST_KECAMATAN kec ON gw.kecamatan_op = kec.cpm_kec_id
                INNER JOIN SIMPATDA_TYPE t ON gw.simpatda_type = t.id 
            WHERE
                gw.payment_flag = 1 
                AND MONTH ( gw.saved_date ) <= MONTH (
                now()) 
                AND YEAR ( gw.saved_date ) = '{$thnPajak}' 
                AND t.id_sw = '{$jPajak}' 
            ORDER BY
                MONTH ( gw.saved_date ),
                gw.kecamatan_op,
                gw.saved_date
        ";

        $result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
        if(! mysqli_num_rows($result)) {
            echo "data tidak ditemukan, silahkan pilih mata pajak lain atau tahun yang lain, ";
            echo '<a href="/main.php?param='. base64_encode('a=aPatda&m=mPatdaRekap') .'">kembali</a>';
            exit;
        }

        // echo "<pre>";print_r(mysqli_fetch_assoc($result));exit;


        $total_semua_perbulan = array(
            1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0
        );
        $total_op_perbulan = array();
        $total_op_perbulan_perkecamatan = array();
        $list_op = array();
        
        $arr_bulan = $this->arr_bulan;
        $arr_pajak = $this->arr_pajak;

        $objPHPExcel = new PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpos")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("patda")
                ->setKeywords("-");

        $tambahSheetTotal = new PHPExcel_Worksheet($objPHPExcel, 'Rekap');
        $objPHPExcel->addSheet($tambahSheetTotal, 1);

        $objPHPExcel->getSheet(0)->setTitle('Total');

        $sheetTotal = $objPHPExcel->getSheet(0);
        $sheetRekap = $objPHPExcel->getSheet(1);
        $sheetPerbulan = null;
        // ROW COUNTING (looping) -- START
        $sheetTotalStartRow = 4;
        $sheetRekapStartRow = 7;
        $sheetPerbulanStartRow = 4;

        $sheetRekapEndsRow = 0;
        // ROW COUNTING END

        // SETTING PHP EXCEL -- START
        $horizontalAlignPHPExcel = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $verticalAlignPHPExcel = array(
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $rightAlignPHPExcel = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );
        $boldPHPExcel = array(
            'font' => array(
                'bold' => true
            )
        );
        $fontSize16PHPExcel = array(
            'font' => array(
                'size' => 16
            )
        );
        $allBordesPHPExcel = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        // END SETTING PHP EXCEL

        // MANUAL ROW -- START
        // Total
        // Total - styles
        $sheetTotal->getStyle('A1:D1')
            ->applyFromArray(array_merge_recursive($horizontalAlignPHPExcel, $boldPHPExcel, $fontSize16PHPExcel));
        $sheetTotal->getStyle('A3:D3')
            ->applyFromArray(array_merge_recursive($boldPHPExcel, $horizontalAlignPHPExcel));
        $sheetTotal->getStyle('A3:D16')
            ->applyFromArray($allBordesPHPExcel);
        $sheetTotal->getStyle('A20:D28')
            ->applyFromArray(array_merge_recursive($horizontalAlignPHPExcel, $boldPHPExcel));
        $sheetTotal->getStyle('A18')
            ->applyFromArray($boldPHPExcel);
        $sheetTotal->getStyle('A16:B16')
            ->applyFromArray(array_merge_recursive($boldPHPExcel, $horizontalAlignPHPExcel));

        foreach (range('A', 'D') as $key => $column) {
            $sheetTotal->getColumnDimension($column)->setAutoSize(false);
        }
        $sheetTotal->getColumnDimension('A')->setWidth(6);
        $sheetTotal->getColumnDimension('B')->setWidth(40);
        $sheetTotal->getColumnDimension('C')->setWidth(44);
        $sheetTotal->getColumnDimension('D')->setWidth(52);

        // Total - merger
        $sheetTotal->mergeCells('A1:D1');

        $sheetTotal->mergeCells('A16:B16');

        $sheetTotal->mergeCells('A18:B18');

        $sheetTotal->mergeCells('A20:B20');
        $sheetTotal->mergeCells('A21:B21');
        $sheetTotal->mergeCells('A22:B22');
        $sheetTotal->mergeCells('A23:B23');

        $sheetTotal->mergeCells('C20:D20');
        $sheetTotal->mergeCells('C21:D21');
        $sheetTotal->mergeCells('C22:D22');
        
        $sheetTotal->mergeCells('A27:B27');
        $sheetTotal->mergeCells('A28:B28');

        $sheetTotal->mergeCells('C27:D27');
        $sheetTotal->mergeCells('C28:D28');

        // Total - set values
        $sheetTotal->setCellValue('A1', 'REKAPITULASI TAGIHAN OBJEK PAJAK ' . strtoupper($arr_pajak[$jPajak]));

        $sheetTotal->setCellValue('A3', 'NO');
        $sheetTotal->setCellValue('B3', 'BULAN');
        $sheetTotal->setCellValue('C3', 'JUMLAH');
        $sheetTotal->setCellValue('D3', 'KETERANGAN');

        $sheetTotal->setCellValue('A16', 'TOTAL');

        $sheetTotal->setCellValue('A18', 'Dibuat tanggal: ' . date('d') . ' ' . $arr_bulan[date('n')] . ' ' . date('Y'));
        
        $sheetTotal->setCellValue('A20', 'Mengetahui,');
        $sheetTotal->setCellValue('A21', 'Kepala Badan Pengelolaan Pajak');
        $sheetTotal->setCellValue('A22', 'Dan Retribusi Daerah');
        $sheetTotal->setCellValue('A23', 'Kabupaten Lampung Tengah');

        $sheetTotal->setCellValue('C20', 'Dibuat Oleh,');
        $sheetTotal->setCellValue('C21', 'Bendahara Penerimaan/');
        $sheetTotal->setCellValue('C22', 'Bendahara Penerimaan Pembantu');

        $sheetTotal->setCellValue('A27', 'MADANI, SE, MM');
        $sheetTotal->setCellValue('A28', '19630813 199003 1 003');

        $sheetTotal->setCellValue('C27', 'PATIMAH, S.SOS');
        $sheetTotal->setCellValue('C28', '19630720 198703 2 002');

        // Rekap
        // Rekap - styles 
        $sheetRekap->getStyle('A1:Q3')->applyFromArray(array_merge_recursive($horizontalAlignPHPExcel, $boldPHPExcel));
        $sheetRekap->getStyle('A5:Q6')->applyFromArray(array_merge_recursive($horizontalAlignPHPExcel, $boldPHPExcel, $verticalAlignPHPExcel));
        foreach (range('D', 'Q') as $key => $column) {
            $sheetRekap->getColumnDimension($column)->setAutoSize(true);
        }
        $sheetRekap->getColumnDimension('A')->setAutoSize(false);
        $sheetRekap->getColumnDimension('B')->setAutoSize(false);
        $sheetRekap->getColumnDimension('C')->setAutoSize(false);
        $sheetRekap->getColumnDimension('A')->setWidth(14);
        $sheetRekap->getColumnDimension('B')->setWidth(27);
        $sheetRekap->getColumnDimension('C')->setWidth(32);

        // Rekap - merger
        $sheetRekap->mergeCells('A1:Q1');
        $sheetRekap->mergeCells('A2:Q2');
        $sheetRekap->mergeCells('A3:Q3');
        $sheetRekap->mergeCells('A5:A6');
        $sheetRekap->mergeCells('B5:B6');
        $sheetRekap->mergeCells('C5:C6');
        $sheetRekap->mergeCells('P5:P6');
        $sheetRekap->mergeCells('Q5:Q6');
        $sheetRekap->mergeCells('D5:O5');
        // Rekap - set values
        $sheetRekap->setCellValue('A1', 'REKAPITULASI TAGIHAN OBJEK PAJAK ' . strtoupper($arr_pajak[$jPajak]));
        $sheetRekap->setCellValue('A2', 'KABUPATEN LAMPUNG TENGAH');
        $sheetRekap->setCellValue('A3', $thnPajak);
        $sheetRekap->setCellValue('A5', 'KOHIR');
        $sheetRekap->setCellValue('B5', 'Nama Objek Pajak');
        $sheetRekap->setCellValue('C5', 'Alamat');
        $sheetRekap->setCellValue('D5', 'BULAN');
        $sheetRekap->setCellValue('P5', 'Total (tahun)');
        $sheetRekap->setCellValue('Q5', 'Rata-rata per bulan');

        foreach (range('D', 'O') as $key => $column) {
            $sheetRekap->setCellValue($column.'6', $arr_bulan[$key+1]);
        }
        
        
        // MANUAL ROW END 

        // LOOP -- START
        $createdSheets = array();
        $listKecamatan = array();
        $arr_totalPerKecamatan = array();
        $lastKecamatan = null;
        $lastBulan = null;
        $totalPerKecamatan = 0;
        $totalDendaPerKecamatan = 0;
        $counter = 0;
        $lastSheet = 2;
        $firstRowPerKecamatan = ($sheetPerbulanStartRow+1);
        while ($row = mysqli_fetch_assoc($result)) {
            // die(print_r($row));
            $total_semua_perbulan[$row['BULAN_BAYAR']] += $row['TOTAL_BAYAR'];
            
            if(isset($total_op_perbulan[$row['KOHIR']][$row['BULAN_BAYAR']])) {
                $total_op_perbulan[$row['KOHIR']][$row['BULAN_BAYAR']] += $row['TOTAL_BAYAR'];
            }else {
                $total_op_perbulan[$row['KOHIR']][$row['BULAN_BAYAR']] = $row['TOTAL_BAYAR'];                 
            }

            if(!isset($list_op[$row['KOHIR']])) {
                $list_op[$row['KOHIR']] = $row;
            }

            if(!isset($listKecamatan[$row['KEC_ID']])) {
                $listKecamatan[$row['KEC_ID']] = $row['KECAMATAN'];
                $arr_totalPerKecamatan[$row['BULAN_BAYAR']][$row['KEC_ID']] = $row['TOTAL_BAYAR'];
            }else {
                $arr_totalPerKecamatan[$row['BULAN_BAYAR']][$row['KEC_ID']] += $row['TOTAL_BAYAR'];
            }

            // GAK KEPAKE
            // if(isset($op_perbulan_perkecamatan[$row['BULAN_BAYAR']][$row['KEC_ID']][$row['KOHIR']])) {
            //     $total_op_perbulan_perkecamatan[$row['BULAN_BAYAR']][$row['KEC_ID']][$row['KOHIR']] += $row['TOTAL_BAYAR'];
            // }else {
            //     $total_op_perbulan_perkecamatan[$row['BULAN_BAYAR']][$row['KEC_ID']][$row['KOHIR']] = $row['TOTAL_BAYAR'];
            // }

            // tambah sheet perbulan per kecamatan
            if (!in_array($row['BULAN_BAYAR'], $createdSheets)) { // kalo ga ada, bikin sheet baru
                if($counter && $lastBulan != $row['BULAN_BAYAR']) { // ngatur style sama merge jumlah diakhir sheet
                    $sheetPerbulan = $objPHPExcel->getSheet($lastSheet-1);
                    $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+2).':C'.($sheetPerbulanStartRow+2)); // merge jumlah
                    $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+2))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));
                    $sheetPerbulan->getStyle('D'.($sheetPerbulanStartRow+2))->applyFromArray($boldPHPExcel);

                    // rekap perbulan per kecamatan
                    $counterFrom = 7;
                    $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+5), 'Rekapitulasi Seluruh Kecamatan Untuk Bulan '.$arr_bulan[$row['BULAN_BAYAR']].' '. $thnPajak);
                    $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+6), 'No');
                    $sheetPerbulan->setCellValue('B'.($sheetPerbulanStartRow+6), 'Kecamatan');
                    $sheetPerbulan->setCellValue('C'.($sheetPerbulanStartRow+6), 'Jumlah');
                    $sheetPerbulan->setCellValue('D'.($sheetPerbulanStartRow+6), 'Keterangan');

                    $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+5).':E'.($sheetPerbulanStartRow+5));
                    $sheetPerbulan->mergeCells('D'.($sheetPerbulanStartRow+6).':E'.($sheetPerbulanStartRow+6));

                    $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+5).':E'.($sheetPerbulanStartRow+6))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));

                    $temp_total_per_kecamatan = 0;
                    foreach ($arr_totalPerKecamatan[$lastBulan] as $kec_id => $total) {
                        $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom),$counterFrom-6);
                        $sheetPerbulan->setCellValue('B'.($sheetPerbulanStartRow+$counterFrom),$listKecamatan[$kec_id]);
                        $sheetPerbulan->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom),'Rp.'.number_format($total,2));
                        $sheetPerbulan->getStyle('C'.($sheetPerbulanStartRow+$counterFrom))->applyFromArray($rightAlignPHPExcel);
                        $sheetPerbulan->mergeCells('D'.($sheetPerbulanStartRow+$counterFrom).':E'.($sheetPerbulanStartRow+$counterFrom));

                        $counterFrom++;
                        $temp_total_per_kecamatan += $total;
                    }

                    $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom), 'TOTAL');
                    $sheetPerbulan->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom), 'Rp.'.number_format($temp_total_per_kecamatan,2));
                    $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom).':B'.($sheetPerbulanStartRow+$counterFrom));
                    $sheetPerbulan->mergeCells('D'.($sheetPerbulanStartRow+$counterFrom).':E'.($sheetPerbulanStartRow+$counterFrom));
                    $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+$counterFrom).':B'.($sheetPerbulanStartRow+$counterFrom))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));
                    $sheetPerbulan->getStyle('C'.($sheetPerbulanStartRow+$counterFrom))->applyFromArray(array_merge($boldPHPExcel, $rightAlignPHPExcel));
                    $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+6).':E'.($sheetPerbulanStartRow+$counterFrom))->applyFromArray($allBordesPHPExcel);

                    // bagian bawah
                    $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+2), 'Dibuat tanggal: ' . date('d') . ' ' . $arr_bulan[date('n')] . ' ' . date('Y'));
                    $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+2).':B'.($sheetPerbulanStartRow+$counterFrom+2));
                    $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+$counterFrom+2))->applyFromArray($boldPHPExcel);

                    $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+4), 'Mengetahui,')
                                ->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+5), 'Kepala Dinas Pendapatan Daerah')
                                ->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+6), 'Kabupaten Lampung Tengah')
                                ->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+10), 'MADANI, SE, MM')
                                ->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+11), '19630813 199003 1 003')
                                ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+4).':B'.($sheetPerbulanStartRow+$counterFrom+4))
                                ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+5).':B'.($sheetPerbulanStartRow+$counterFrom+5))
                                ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+6).':B'.($sheetPerbulanStartRow+$counterFrom+6))
                                ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+10).':B'.($sheetPerbulanStartRow+$counterFrom+10))
                                ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+11).':B'.($sheetPerbulanStartRow+$counterFrom+11));

                    $sheetPerbulan->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+4), 'Dibuat Oleh,')
                                ->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+5), 'Bendahara Penerimaan/')
                                ->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+6), 'Bendahara Penerimaan Pembantu')
                                ->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+10), 'PATIMAH, S.SOS')
                                ->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+11), '19630720 198703 2 002')
                                ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+4).':E'.($sheetPerbulanStartRow+$counterFrom+4))
                                ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+5).':E'.($sheetPerbulanStartRow+$counterFrom+5))
                                ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+6).':E'.($sheetPerbulanStartRow+$counterFrom+6))
                                ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+10).':E'.($sheetPerbulanStartRow+$counterFrom+10))
                                ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+11).':E'.($sheetPerbulanStartRow+$counterFrom+11));

                    $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+$counterFrom+4).':C'.($sheetPerbulanStartRow+$counterFrom+11))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));

                    // rekap perbulan per kecamatan -- end


                }

                $createdSheets = array();
                $lastKecamatan = null;
                $lastBulan = null;
                $totalPerKecamatan = 0;
                $totalDendaPerKecamatan = 0;
                $counter = 0;
                $sheetPerbulanStartRow = 4;

                $tambahSheetPerbulan = new PHPExcel_Worksheet($objPHPExcel, $arr_bulan[$row['BULAN_BAYAR']]);
                $objPHPExcel->addSheet($tambahSheetPerbulan, $lastSheet);
                $createdSheets[] = $row['BULAN_BAYAR'];

                $sheetPerbulan = $objPHPExcel->getSheet($lastSheet);

                $sheetPerbulan->mergeCells('A1:F1');
                $sheetPerbulan->mergeCells('A2:F2');

                $sheetPerbulan->setCellValue('A1', 'REKAPITULASI TAGIHAN OBJEK PAJAK ' . strtoupper($arr_pajak[$jPajak]));
                $sheetPerbulan->setCellValue('A2', 'BULAN: '.$arr_bulan[$row['BULAN_BAYAR']].' '.$thnPajak);

                $sheetPerbulan->getStyle('A1:A2')->applyFromArray(array_merge_recursive($boldPHPExcel, $horizontalAlignPHPExcel));

                $lastSheet++;
            }

            if($counter && $lastKecamatan != $row['KEC_ID']) {
                $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+2).':C'.($sheetPerbulanStartRow+2)); // merge jumlah
                $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+2))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));
                $sheetPerbulan->getStyle('D'.($sheetPerbulanStartRow+2))->applyFromArray($boldPHPExcel);
                $sheetPerbulanStartRow += 5;
                $firstRowPerKecamatan = ($sheetPerbulanStartRow + 1);
            }

            if(!$counter || ($counter && $lastKecamatan != $row['KEC_ID'])) {
                $sheetPerbulan->mergeCells("A{$sheetPerbulanStartRow}:B{$sheetPerbulanStartRow}"); //merge kecamatan
                // kecamatan
                $sheetPerbulan->setCellValue("A". $sheetPerbulanStartRow, 'KECAMATAN: '. $row['KECAMATAN']);
                $sheetPerbulan->setCellValue("A". ($sheetPerbulanStartRow+1), 'KOHIR');
                $sheetPerbulan->setCellValue("B". ($sheetPerbulanStartRow+1), 'Nama Objek Pajak');
                $sheetPerbulan->setCellValue("C". ($sheetPerbulanStartRow+1), 'Alamat');
                $sheetPerbulan->setCellValue("D". ($sheetPerbulanStartRow+1), 'Jumlah');
                $sheetPerbulan->setCellValue("E". ($sheetPerbulanStartRow+1), 'No Nota');
                $sheetPerbulan->setCellValue("F". ($sheetPerbulanStartRow+1), 'Tanggal');
                $sheetPerbulan->setCellValue("G". ($sheetPerbulanStartRow+1), 'Keterangan');
                
                foreach (range('A', 'G') as $key => $column) {
                    $sheetPerbulan->getColumnDimension($column)->setAutoSize(false);
                }

                $sheetPerbulan->getColumnDimension("A")->setWidth(14);
                $sheetPerbulan->getColumnDimension("B")->setWidth(39.29);
                $sheetPerbulan->getColumnDimension("C")->setWidth(39.29);
                $sheetPerbulan->getColumnDimension("D")->setWidth(19.29);
                $sheetPerbulan->getColumnDimension("E")->setWidth(19.29);
                $sheetPerbulan->getColumnDimension("F")->setWidth(19.29);
                $sheetPerbulan->getColumnDimension("G")->setWidth(19.29);

                $sheetPerbulan->getStyle('A'. $sheetPerbulanStartRow)->applyFromArray($boldPHPExcel);
                $sheetPerbulan->getStyle('A'. ($sheetPerbulanStartRow+1).':G'. ($sheetPerbulanStartRow+1))->applyFromArray(array_merge_recursive($boldPHPExcel, $horizontalAlignPHPExcel));
                $sheetPerbulan->getStyle("D". ($sheetPerbulanStartRow+2))->applyFromArray($rightAlignPHPExcel);
            }

            
            $sheetPerbulan->setCellValueExplicit("A". ($sheetPerbulanStartRow+2), (string) $row['KOHIR'], PHPExcel_Cell_DataType::TYPE_STRING);
            if($row['DENDA'] > 0) {
                $sheetPerbulan->getStyle("A". ($sheetPerbulanStartRow+2))->applyFromArray($boldPHPExcel);
                $sheetPerbulan->getStyle("G". ($sheetPerbulanStartRow+2))->applyFromArray(array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFFF00')
                    )
                ));
            }
            $sheetPerbulan->setCellValue("B". ($sheetPerbulanStartRow+2), $row['NAMA_OP']);
            $sheetPerbulan->setCellValue("C". ($sheetPerbulanStartRow+2), $row['ALAMAT_OP']);
            $sheetPerbulan->setCellValue("D". ($sheetPerbulanStartRow+2), 'Rp.' . number_format($row['TOTAL_BAYAR'],2));
            $sheetPerbulan->setCellValue("E". ($sheetPerbulanStartRow+2), $row['KODE_BAYAR']);
            $sheetPerbulan->setCellValue("F". ($sheetPerbulanStartRow+2), $row['TANGGAL_BAYAR']);
            $sheetPerbulan->setCellValue("G". ($sheetPerbulanStartRow+2), ($row['DENDA'] > 0 ? 'Denda= Rp.'.number_format($row['DENDA'],2):''));
            
            if($counter && $lastKecamatan != $row['KEC_ID']) {
                $totalPerKecamatan = 0;
                $totalDendaPerkecamatan = 0;
            }
            $totalPerKecamatan += $row['TOTAL_BAYAR'];
            $totalDendaPerkecamatan += ($row['DENDA']? $row['DENDA']: 0);

            // if($counter && $lastKecamatan != $row['KEC_ID']) {
            $sheetPerbulan->getStyle("A{$firstRowPerKecamatan}:G".($sheetPerbulanStartRow+3))->applyFromArray($allBordesPHPExcel);
                // $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+3).':C'.($sheetPerbulanStartRow+3)); // merge jumlah
            // }
            $sheetPerbulan->setCellValue("A".($sheetPerbulanStartRow+3), 'JUMLAH');
            $sheetPerbulan->setCellValue("D".($sheetPerbulanStartRow+3), 'Rp.' . number_format($totalPerKecamatan,2));
            $sheetPerbulan->getStyle("D". ($sheetPerbulanStartRow+3))->applyFromArray($rightAlignPHPExcel);


            $lastKecamatan = $row['KEC_ID'];
            $lastBulan = $row['BULAN_BAYAR'];
            $sheetPerbulanStartRow++;
            
            $counter++;
        }
        // END LOOP
        // echo "<pre>";print_r($arr_totalPerKecamatan);print_r($listKecamatan);exit;
        // SETTING ROW TERAKHIR
        $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+2).':C'.($sheetPerbulanStartRow+2)); // merge jumlah
        $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+2))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));
        $sheetPerbulan->getStyle('D'.($sheetPerbulanStartRow+2))->applyFromArray($boldPHPExcel);

        // rekap perbulan per kecamatan
        $counterFrom = 7;
        $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+5), 'Rekapitulasi Seluruh Kecamatan Untuk Bulan '.$arr_bulan[$lastBulan].' '. $thnPajak);
        $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+6), 'No');
        $sheetPerbulan->setCellValue('B'.($sheetPerbulanStartRow+6), 'Kecamatan');
        $sheetPerbulan->setCellValue('C'.($sheetPerbulanStartRow+6), 'Jumlah');
        $sheetPerbulan->setCellValue('D'.($sheetPerbulanStartRow+6), 'Keterangan');

        $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+5).':E'.($sheetPerbulanStartRow+5));
        $sheetPerbulan->mergeCells('D'.($sheetPerbulanStartRow+6).':E'.($sheetPerbulanStartRow+6));

        $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+5).':E'.($sheetPerbulanStartRow+6))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));

        $temp_total_per_kecamatan = 0;
        foreach ($arr_totalPerKecamatan[$lastBulan] as $kec_id => $total) {
            $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom),$counterFrom-6);
            $sheetPerbulan->setCellValue('B'.($sheetPerbulanStartRow+$counterFrom),$listKecamatan[$kec_id]);
            $sheetPerbulan->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom),'Rp.'.number_format($total,2));
            $sheetPerbulan->getStyle('C'.($sheetPerbulanStartRow+$counterFrom))->applyFromArray($rightAlignPHPExcel);
            $sheetPerbulan->mergeCells('D'.($sheetPerbulanStartRow+$counterFrom).':E'.($sheetPerbulanStartRow+$counterFrom));

            $counterFrom++;
            $temp_total_per_kecamatan += $total;
        }

        $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom), 'TOTAL');
        $sheetPerbulan->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom), 'Rp.'.number_format($temp_total_per_kecamatan,2));
        $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom).':B'.($sheetPerbulanStartRow+$counterFrom));
        $sheetPerbulan->mergeCells('D'.($sheetPerbulanStartRow+$counterFrom).':E'.($sheetPerbulanStartRow+$counterFrom));
        $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+$counterFrom).':B'.($sheetPerbulanStartRow+$counterFrom))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));
        $sheetPerbulan->getStyle('C'.($sheetPerbulanStartRow+$counterFrom))->applyFromArray(array_merge($boldPHPExcel, $rightAlignPHPExcel));
        $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+6).':E'.($sheetPerbulanStartRow+$counterFrom))->applyFromArray($allBordesPHPExcel);

        // bagian bawah
        $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+2), 'Dibuat tanggal: ' . date('d') . ' ' . $arr_bulan[date('n')] . ' ' . date('Y'));
        $sheetPerbulan->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+2).':B'.($sheetPerbulanStartRow+$counterFrom+2));
        $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+$counterFrom+2))->applyFromArray($boldPHPExcel);

        $sheetPerbulan->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+4), 'Mengetahui,')
                    ->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+5), 'Kepala Dinas Pendapatan Daerah')
                    ->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+6), 'Kabupaten Lampung Tengah')
                    ->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+10), 'MADANI, SE, MM')
                    ->setCellValue('A'.($sheetPerbulanStartRow+$counterFrom+11), '19630813 199003 1 003')
                    ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+4).':B'.($sheetPerbulanStartRow+$counterFrom+4))
                    ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+5).':B'.($sheetPerbulanStartRow+$counterFrom+5))
                    ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+6).':B'.($sheetPerbulanStartRow+$counterFrom+6))
                    ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+10).':B'.($sheetPerbulanStartRow+$counterFrom+10))
                    ->mergeCells('A'.($sheetPerbulanStartRow+$counterFrom+11).':B'.($sheetPerbulanStartRow+$counterFrom+11));

        $sheetPerbulan->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+4), 'Dibuat Oleh,')
                    ->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+5), 'Bendahara Penerimaan/')
                    ->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+6), 'Bendahara Penerimaan Pembantu')
                    ->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+10), 'PATIMAH, S.SOS')
                    ->setCellValue('C'.($sheetPerbulanStartRow+$counterFrom+11), '19630720 198703 2 002')
                    ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+4).':E'.($sheetPerbulanStartRow+$counterFrom+4))
                    ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+5).':E'.($sheetPerbulanStartRow+$counterFrom+5))
                    ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+6).':E'.($sheetPerbulanStartRow+$counterFrom+6))
                    ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+10).':E'.($sheetPerbulanStartRow+$counterFrom+10))
                    ->mergeCells('C'.($sheetPerbulanStartRow+$counterFrom+11).':E'.($sheetPerbulanStartRow+$counterFrom+11));

        $sheetPerbulan->getStyle('A'.($sheetPerbulanStartRow+$counterFrom+4).':C'.($sheetPerbulanStartRow+$counterFrom+11))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));

        // rekap perbulan per kecamatan -- end

        // SETTING ROW TERAKHIR -- END

        // LOOPING REKAP
        $alpha_rekap = range('D', 'O');
        foreach ($total_op_perbulan as $no_op => $arr_total_perbulan) {
            $sheetRekap->setCellValue('A'. $sheetRekapStartRow, $list_op[$no_op]['KOHIR']);
            $sheetRekap->setCellValue('B'. $sheetRekapStartRow, $list_op[$no_op]['NAMA_OP']);
            $sheetRekap->setCellValue('C'. $sheetRekapStartRow, $list_op[$no_op]['ALAMAT_OP']);
            $total_per_op = 0;
            foreach ($arr_bulan as $bulan => $nama_bulan) {
                $total_perbulan = isset($arr_total_perbulan[$bulan]) ? $arr_total_perbulan[$bulan] : 0;
                $sheetRekap->setCellValue($alpha_rekap[$bulan-1].$sheetRekapStartRow, 'Rp.' . number_format($total_perbulan, 2));
                $sheetRekap->getStyle($alpha_rekap[$bulan-1].$sheetRekapStartRow)->applyFromArray($rightAlignPHPExcel);
                $total_per_op += $total_perbulan;
            }
            $sheetRekap->setCellValue('P'. $sheetRekapStartRow, 'Rp.'.number_format($total_per_op, 2));
            $sheetRekap->setCellValue('Q'. $sheetRekapStartRow, 'Rp.'.number_format($total_per_op/12, 2));
            $sheetRekap->getStyle('P'. $sheetRekapStartRow)->applyFromArray($rightAlignPHPExcel);
            $sheetRekap->getStyle('Q'. $sheetRekapStartRow)->applyFromArray($rightAlignPHPExcel);

            $sheetRekapStartRow++;
        }
        $sheetRekapEndsRow = $sheetRekapStartRow;
        $sheetRekap->setCellValue('A'.$sheetRekapEndsRow, 'JUMLAH');

        foreach ($arr_bulan as $bulan => $nama_bulan) {
            $sheetRekap->setCellValue($alpha_rekap[$bulan-1].$sheetRekapEndsRow, 'Rp.' . number_format($total_semua_perbulan[$bulan], 2));
        }

        $sheetRekap->mergeCells('A'.$sheetRekapEndsRow.':C'.$sheetRekapEndsRow);

        $sheetRekap->getStyle('A'.$sheetRekapEndsRow.':C'.$sheetRekapEndsRow)->applyFromArray(array_merge($boldPHPExcel,$horizontalAlignPHPExcel));
        $sheetRekap->getStyle('D'.$sheetRekapEndsRow.':Q'.$sheetRekapEndsRow)->applyFromArray(array_merge($boldPHPExcel,$rightAlignPHPExcel));
        $sheetRekap->getStyle('A5:Q'. $sheetRekapEndsRow)->applyFromArray($allBordesPHPExcel);
        $sheetRekap->getStyle('A'.($sheetRekapEndsRow+2))->applyFromArray($boldPHPExcel);
        $sheetRekap->getStyle('A'.($sheetRekapEndsRow+3).':P'.($sheetRekapEndsRow+11))->applyFromArray(array_merge($boldPHPExcel, $horizontalAlignPHPExcel));
        // BAGIAN BAWAH
        $sheetRekap->setCellValue('A'.($sheetRekapEndsRow+2), 'Dibuat tanggal: ' . date('d') . ' ' . $arr_bulan[date('n')] . ' ' . date('Y'));

        $sheetRekap->setCellValue('B'.($sheetRekapEndsRow+4), 'Mengatahui,');
        $sheetRekap->setCellValue('B'.($sheetRekapEndsRow+5), 'Kepala Badan Pengelolaan Pajak Dan Retribusi Daerah');
        $sheetRekap->setCellValue('B'.($sheetRekapEndsRow+6), 'Kabupaten Lampung Tengah');

        $sheetRekap->setCellValue('L'.($sheetRekapEndsRow+4), 'Dibuat Oleh,');
        $sheetRekap->setCellValue('L'.($sheetRekapEndsRow+5), 'Bendahara Penerimaan/');
        $sheetRekap->setCellValue('L'.($sheetRekapEndsRow+6), 'Bendahara Penerimaan Pembantu');

        $sheetRekap->setCellValue('B'.($sheetRekapEndsRow+10), 'MADANI, SE, MM');
        $sheetRekap->setCellValue('B'.($sheetRekapEndsRow+11), '19630813 199003 1 003');
        $sheetRekap->setCellValue('L'.($sheetRekapEndsRow+10), 'PATIMAH, S.SOS');
        $sheetRekap->setCellValue('L'.($sheetRekapEndsRow+11), '19630720 198703 2 002');

        // MERGE
        $sheetRekap->mergeCells('A'.($sheetRekapEndsRow+2).':B'.($sheetRekapEndsRow+2))
                ->mergeCells('B'.($sheetRekapEndsRow+4).':D'.($sheetRekapEndsRow+4))
                ->mergeCells('B'.($sheetRekapEndsRow+5).':D'.($sheetRekapEndsRow+5))
                ->mergeCells('B'.($sheetRekapEndsRow+6).':D'.($sheetRekapEndsRow+6))
                ->mergeCells('L'.($sheetRekapEndsRow+4).':P'.($sheetRekapEndsRow+4))
                ->mergeCells('L'.($sheetRekapEndsRow+5).':P'.($sheetRekapEndsRow+5))
                ->mergeCells('L'.($sheetRekapEndsRow+6).':P'.($sheetRekapEndsRow+6))
                ->mergeCells('B'.($sheetRekapEndsRow+10).':D'.($sheetRekapEndsRow+10))
                ->mergeCells('B'.($sheetRekapEndsRow+11).':D'.($sheetRekapEndsRow+11))
                ->mergeCells('L'.($sheetRekapEndsRow+10).':P'.($sheetRekapEndsRow+10))
                ->mergeCells('L'.($sheetRekapEndsRow+11).':P'.($sheetRekapEndsRow+11));


        // LOOPING REKAP -- END

        // LOOPING TOTAL
        foreach ($arr_bulan as $bulan => $nama_bulan) {
            $sheetTotal->setCellValue('A' . $sheetTotalStartRow, $bulan);
            $sheetTotal->setCellValue('B' . $sheetTotalStartRow, $nama_bulan);
            $sheetTotal->setCellValue('C' . $sheetTotalStartRow, 'Rp.'.number_format($total_semua_perbulan[$bulan]));
            $sheetTotal->getStyle("C". $sheetTotalStartRow)->applyFromArray($rightAlignPHPExcel);
            $sheetTotalStartRow++;
        }
        $sheetTotal->setCellValueExplicit('C16', 'Rp.'.number_format(array_sum($total_semua_perbulan)));
        $sheetTotal->getStyle('C16')->applyFromArray(array_merge($boldPHPExcel, $rightAlignPHPExcel));
        // LOOPING TOTAL -- END

        // echo "<pre>";print_r($total_op_perbulan);exit;



        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="rekap_transaksi_'. str_replace(' ', '_', strtolower($arr_pajak[$jPajak])). '_' .$thnPajak . '_' . date('YmdHis') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        ob_clean();
        flush();

        $objWriter->save('php://output');
    }

    function download_excel_rekap() {

        $arr_config = $this->get_config_value($this->_a);

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];


		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName, $Conn_gw);

		$recordCount = 9;
		
        $where = "";
        $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
        $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}' " : "";
        // $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND  CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";
        if(isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != ""){
            if(strlen($_REQUEST['CPM_KODE_REKENING'])==9)
                $where.= " AND simpatda_rek like '{$_REQUEST['CPM_KODE_REKENING']}%' ";
            elseif(strlen($_REQUEST['CPM_KODE_REKENING'])>9)
                $where.= " AND simpatda_rek = '{$_REQUEST['CPM_KODE_REKENING']}' ";
        }

		#query select list data        
		$query = "SELECT b.id_sw, b.jenis_sw as JENIS, 
		sum(a.CPM_KETETAPAN) as CPM_KETETAPAN, 
		sum(a.CPM_PENERIMAAN) as CPM_PENERIMAAN, 
		sum(a.CPM_PIUTANG) as CPM_PIUTANG 
		FROM
		((SELECT simpatda_type, simpatda_dibayar AS CPM_KETETAPAN, 0 AS CPM_PENERIMAAN, 0 AS CPM_PIUTANG FROM SIMPATDA_GW
		WHERE simpatda_tahun_pajak = '{$_REQUEST['simpatda_tahun_pajak']}')	UNION 
		(SELECT simpatda_type, 0 AS CPM_KETETAPAN, simpatda_dibayar AS CPM_PENERIMAAN, 0 AS CPM_PIUTANG FROM SIMPATDA_GW
		WHERE simpatda_tahun_pajak = '{$_REQUEST['simpatda_tahun_pajak']}' and payment_flag = 1) UNION 
		(SELECT simpatda_type, 0 AS CPM_KETETAPAN, 0 AS CPM_PENERIMAAN, simpatda_dibayar AS CPM_PIUTANG FROM SIMPATDA_GW
		WHERE simpatda_tahun_pajak = '{$_REQUEST['simpatda_tahun_pajak']}' and payment_flag = 0)
		) AS a INNER JOIN SIMPATDA_TYPE b on a.simpatda_type = b.id GROUP BY b.id_sw;";
		
		$result = mysqli_query($this->Conn, $query);
		
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpos")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("patda")
                ->setKeywords("-");


		$objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C1', 'REKAP PENERIMAAN DAN PIUTANG ')
                ->setCellValue('C2', 'TAHUN '.$_REQUEST['simpatda_tahun_pajak']);
        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A4', 'No.')
                ->setCellValue('B4', 'Jenis Pajak')
                ->setCellValue('C4', 'Jumlah Ketetapan (Rp)')
                ->setCellValue('D4', 'Jumlah Penerimaan (Rp)')
                ->setCellValue('E4', 'Sisa/Piutang (Rp)');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 5;
		$array_total = array("CPM_KETETAPAN"=>0,"CPM_PENERIMAAN"=>0,"CPM_PIUTANG"=>0);

		$no = 0;
	    while($rowsData = mysqli_fetch_assoc($result)){
			
			$array_total['CPM_KETETAPAN'] += $rowsData['CPM_KETETAPAN'];
			$array_total['CPM_PENERIMAAN'] += $rowsData['CPM_PENERIMAAN'];
			$array_total['CPM_PIUTANG'] += $rowsData['CPM_PIUTANG'];
							
			$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ++$no);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $rowsData['JENIS']);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, number_format($rowsData['CPM_KETETAPAN']));
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, number_format($rowsData['CPM_PENERIMAAN']));
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, number_format($rowsData['CPM_PIUTANG']));
			$row++;
	    }	

	    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, '');
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, 'Total Keseluruhan');
		$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, number_format($array_total['CPM_KETETAPAN']));
		$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, number_format($array_total['CPM_PENERIMAAN']));
		$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, number_format($array_total['CPM_PIUTANG']));


		$objPHPExcel->getActiveSheet()->getStyle('C5:E' . ($row+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Wajib Pajak');
        //style header
		$objPHPExcel->getActiveSheet()->getStyle('C1:C2')->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A4:E4')->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A4:E4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E4')->getFill()->getStartColor()->setRGB('E4E4E4');

		$objPHPExcel->getActiveSheet()->getStyle("A{$row}:E{$row}")->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    )
                )
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A4:E' . $row)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle("A{$row}:E{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle("A{$row}:E{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "E"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="rekap' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    public function filtering_kendali($id) {
        $opt_jenis_pajak = '<option value="">Pilih Jenis Pajak</option>';
        foreach ($this->arr_pajak as $x => $y) {
            $opt_jenis_pajak .= "<option value=\"{$x}\">{$y}</option>";
        }

        $opt_rekening = '<option value="">All</option>';
        $reks = $this->getDataRekening();
        foreach($reks as $header=>$rek){
            $aRek = array_values($rek);
            if(count($aRek)>1){
                $opt_rekening .= "<option value=\"{$header}\">{$aRek[0]['nmheader3']}</option>";
                foreach($rek as $k=>$v){
                    $opt_rekening .= "<option value=\"{$k}\">&nbsp; $k - {$v['nmrek']}</option>";
                }
            }else{
                // $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']} ({$aRek[0]['kdrek']})</option>";
                $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']}</option>";
            }
        }

        $opt_kecamatan = '<option value="">All</option>';
        $query = "SELECT * FROM PATDA_MST_KECAMATAN order by CPM_KECAMATAN";
        $res = mysqli_query($this->Conn, $query);
        while($list = mysqli_fetch_object($res)){
            $opt_kecamatan .= "<option value=\"{$list->CPM_KEC_ID}\">{$list->CPM_KECAMATAN}</option>";
        }

        $html = "<div class=\"filtering\">
                    <form id='2'><table><tr valign='bottom'>
                        <td><input type='hidden' id=\"HIDDEN-{$id}\" a=\"{$this->_a}\">
                            Jenis Pajak :<br><select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\">{$opt_jenis_pajak}</select></td>
                        
                        <td>Tanggal Ketetapan :<br><input type=\"text\" name=\"CPM_TGL_LAPOR1-{$id}\" size=\"10\" id=\"CPM_TGL_LAPOR1-{$id}\" class=\"date\" > s.d
                        <input type=\"text\" name=\"CPM_TGL_LAPOR2-{$id}\" size=\"10\" id=\"CPM_TGL_LAPOR2-{$id}\" class=\"date\" ></td> 

                        <td>Kode Rekening :<br><select name=\"CPM_KODE_REKENING-{$id}\" style=\"max-width:200px\" id=\"CPM_KODE_REKENING-{$id}\">{$opt_rekening}</select></td>
                        
                        <td>Kecamatan :<br><select id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select></td>
                        <td>Kelurahan :<br><select id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">All</option></select></td>

                        <td><button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                        <button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/rekap/svc-download.kendali.xls.php')\">Export to xls</button></td>
                        </tr></table></form>
                </div>";
        return $html;
    }

    public function grid_table_kendali() {
        $DIR = "PATDA-V1";
        $modul = "rekap";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.number{text-align:right}.text-center{text-align:center}.filtering td{background:transparent}.filtering input,.filtering select{height:23px}</style>
                {$this->filtering_kendali($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"><div id='title-kendali'></div></div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        $(\".date\").datepicker({
                            dateFormat: \"yy-mm-dd\",
                            showOn: \"button\",
                            buttonImageOnly: false,
                            buttonText: \"..\",
                            changeMonth: true,
                            changeYear: true,
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
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-kendali.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                sptpd: {title: 'Nomor',width: '10%'},
                                saved_date: {title: 'Tgl Ketetapan',width: '10%'},
                                op_nama: {title: 'Nama',width: '10%'},
                                op_alamat: {title: 'Alamat',width: '10%'},
                                npwpd: {title: 'NPWPD',width: '10%'},
                                payment_paid: {title: 'Tgl Setor',width: '10%'},
                                simpatda_dibayar: {title: 'Jumlah Setor (Rp)', listClass:'number', width: '10%'},
                                sisa: {title: 'Sisa (Rp)', listClass:'number', width: '10%'},
                                masa: {title: 'Masa',width: '10%'},
                            },
							recordsLoaded: function (event, data) {
								var res = data.serverResponse;
								for (var i in data.records) {
									if(data.records[i].NO==''){
										$('#laporanPajak-{$this->_i}').find('.jtable tbody tr:eq('+i+') td').css({'background-color':'#7cadc5','border':'1px #CCC solid','font-weight':'bold'});
									}
								}
							}
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            if($('#CPM_JENIS_PAJAK-{$this->_i}').val()=='' || $('#CPM_TGL_LAPOR1-{$this->_i}').val()=='' || $('#CPM_TGL_LAPOR2-{$this->_i}').val()==''){
                                $('tr.jtable-no-data-row td').html('Silakan pilih Jenis Pajak dan Tanggal Lapor!');
                                if($('#CPM_JENIS_PAJAK-{$this->_i}').val()==''){
                                    $('#CPM_JENIS_PAJAK-{$this->_i}').focus();
                                }else if($('#CPM_TGL_LAPOR1-{$this->_i}').val()==''){
                                    $('#CPM_TGL_LAPOR1-{$this->_i}').datepicker('show');
                                }else if($('#CPM_TGL_LAPOR2-{$this->_i}').val()==''){
                                    $('#CPM_TGL_LAPOR2-{$this->_i}').datepicker('show');
                                }
                                return false;
                            }
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                jenis : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
                                date1 : $('#CPM_TGL_LAPOR1-{$this->_i}').val(),
                                date2 : $('#CPM_TGL_LAPOR2-{$this->_i}').val(),
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),                                    
                                CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),                                    
                                CPM_KODE_REKENING : $('#CPM_KODE_REKENING-{$this->_i}').val()     
                            });
                            $('#title-kendali').html('<div class=\"well container-fluid\"><div class=\"row text-center\"><div class=\"col-xs-12\"><b>BUKU KENDALI <br/>PAJAK '+$('#CPM_JENIS_PAJAK-{$this->_i} :selected').html().toUpperCase()+'</b></div><div class=\"col-xs-2 text-center\">Tanggal Ketetapan : '+$('#CPM_TGL_LAPOR1-{$this->_i}').val()+' s.d '+$('#CPM_TGL_LAPOR2-{$this->_i}').val()+'</div></div><div class=\"row\"><div class=\"col-xs-2 text-center\">Tanggal Setor : '+$('#CPM_TGL_LAPOR1-{$this->_i}').val()+' s.d '+$('#CPM_TGL_LAPOR2-{$this->_i}').val()+' </div></div></div>');	
                        });
                        $('tr.jtable-no-data-row td').html('Silakan lakukan pencarian!');

                        $(\"select#CPM_KECAMATAN-{$this->_i}\").change(function () {
                            showKelurahan({$this->_i});
                        });
            
            
                        function showKelurahan(sts) {
                            var id = $('select#CPM_KECAMATAN-{$this->_i}').val()
                            if(id==''){
                                $(\"select#CPM_KELURAHAN-{$this->_i}\").html('<option value=\"\">All</option>');
                                return false
                            }
                
                            var request = $.ajax({
                            url: \"function/PATDA-V1/pelayanan/svc-kecamatan.php\",
                                type: \"POST\",
                                data: {id: id, kel: 1},
                                dataType: \"json\",
                                beforeSend : function(d){
                                    $(\"select#CPM_KELURAHAN-{$this->_i}\").html('<option value=\"\">Loading...</option>');
                                },
                                success: function (data) {
                                    var c = data.msg.length;
                                    var options = \"\";
                                    if (parseInt(c)>=0){
                                        options += \"<option value=''>All</option>\";
                                        for (var i = 0; i < c; i++) {
                                            options += \"<option value='\" + data.msg[i].id + \"'>\" + data.msg[i].name + \"</option>\";
                                        }
                                        $(\"select#CPM_KELURAHAN-{$this->_i}\").html(options);
                                    }else{
                                        $(\"select#CPM_KELURAHAN-{$this->_i}\").html('<option value=\"\">All</option>');
                                    }
                
                                },
                                error : function(msg){
                                    $(\"select#CPM_KELURAHAN-{$this->_i}\").html('<option value=\"\">All</option>');
                                }  
                            })   
                        }
                    });
                </script>";
        echo $html;
    }

    public function grid_data_kendali() {
        try {
	    if ($_REQUEST['jenis']=="") {
                $jTableResult = array();
                $jTableResult['Result'] = "OK";
                print $this->Json->encode($jTableResult);
                exit;
            }
            $arr_config = $this->get_config_value($this->_a);

            $dbName = $arr_config['PATDA_DBNAME'];
            $dbHost = $arr_config['PATDA_HOSTPORT'];
            $dbPwd = $arr_config['PATDA_PASSWORD'];
            $dbUser = $arr_config['PATDA_USERNAME'];

            $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
            //mysqli_select_db($dbName, $Conn_gw);

			$where = "";
			// $jenis = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
			switch($_REQUEST['jenis']){
                case 1: $where .= " simpatda_type in ('11','31')";break;
                case 2 : $where .= " simpatda_type in ('6','26')";break;
                case 3 : $where .= " simpatda_type in ('4','24')";break;
                case 4 : $where .= " simpatda_type in ('9','29')";break;
                case 5 : $where .= " simpatda_type in ('10','30')";break;
                case 6 : $where .= " simpatda_type in ('8','28')";break;
                case 7 : $where .= " simpatda_type in ('7','27')";break;
                case 8 : $where .= " simpatda_type in ('5','25')";break;
                case 9: $where .= " simpatda_type in ('12','32')";break;
            }
            $where .=" AND ((str_to_date(saved_date,'%Y-%m-%d') BETWEEN '{$_REQUEST['date1']}' AND '{$_REQUEST['date2']}') OR (str_to_date(payment_paid,'%Y-%m-%d') BETWEEN '{$_REQUEST['date1']}' AND '{$_REQUEST['date2']}'))";

            $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op like '%{$_REQUEST['CPM_KECAMATAN']}%'" : "";
            $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op like '%{$_REQUEST['CPM_KELURAHAN']}%' " : "";
            // $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND  CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";
            if(isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != ""){
                if(strlen($_REQUEST['CPM_KODE_REKENING'])==9)
                    $where.= " AND simpatda_rek like '{$_REQUEST['CPM_KODE_REKENING']}%' ";
                elseif(strlen($_REQUEST['CPM_KODE_REKENING'])>9)
                    $where.= " AND simpatda_rek = '{$_REQUEST['CPM_KODE_REKENING']}' ";
            }
            

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id WHERE {$where}";

            $result = mysqli_query($Conn_gw, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data        
            $query = "select jenis,op_nama, npwpd, op_alamat, sptpd, simpatda_tahun_pajak, simpatda_bulan_pajak, payment_flag, date_format(masa_pajak_awal,'%m') as masa_pajak_awal,
                    date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, 
                    date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid 
                    FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
                    WHERE {$where} ORDER BY saved_date DESC ";
            $result = mysqli_query($Conn_gw, $query);

            $rows = array();
            $no = 0;

			$array_total = array("simpatda_dibayar"=>0,"sisa"=>0);	
			while ($row = mysqli_fetch_assoc($result)) {
				$row = array_merge($row, array("NO" => ++$no));
				$row['sisa'] = 0;
				if($row['payment_flag'] == 0){ 
					$row['sisa'] = $row['simpatda_dibayar'];
				}
				$array_total['simpatda_dibayar'] += $row['simpatda_dibayar'];
				$array_total['sisa'] += $row['sisa'];

				$row['sisa'] = number_format($row['sisa']);
				$row['simpatda_dibayar'] = number_format($row['simpatda_dibayar']);

				if(isset($this->arr_bulan[(int) $row['simpatda_bulan_pajak']])){
					$bulan = $this->arr_bulan[(int) $row['simpatda_bulan_pajak']];
				}else{
					$bulan = isset($this->arr_bulan[(int) $row['masa_pajak_awal']])? $this->arr_bulan[(int) $row['masa_pajak_awal']] : $row['masa_pajak_awal'];
				}

				$row['masa'] = $bulan." ".$row['simpatda_tahun_pajak'];
                $rows[] = $row;
            }

			$row = array();	
			$row['NO'] = '';
			$row['sptpd'] = '';
			$row['saved_date'] = '';
			$row['op_nama'] = '';
			$row['op_alamat'] = '';
			$row['npwpd'] = '';
			$row['payment_paid'] = '<b>Total Keseluruhan</b>';
			$row['simpatda_dibayar'] = "<b>".number_format($array_total['simpatda_dibayar'])."</b>";
			$row['sisa'] = "<b>".number_format($array_total['sisa'])."</b>";
			$row['masa'] = '';
			$rows[] = $row;

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

    function download_excel_kendali() {

        $arr_config = $this->get_config_value($this->_a);

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];

		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName, $Conn_gw);
		
		$JENIS_PEMERINTAHAN = $arr_config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $arr_config['PEMERINTAHAN_NAMA'];
        $JALAN = $arr_config['ALAMAT_JALAN'];
        $KOTA = $arr_config['ALAMAT_KOTA'];
        $PROVINSI = $arr_config['ALAMAT_PROVINSI'];
        $KODE_POS = $arr_config['ALAMAT_KODE_POS'];
		
		$PENYETUJU_NAMA = $arr_config['BAG_MENYETUJUI_BUKU_KENDALI_NAMA'];
		$PENYETUJU_JABATAN = $arr_config['BAG_MENYETUJUI_BUKU_KENDALI_JABATAN'];
		$PENYETUJU_NIP = $arr_config['BAG_MENYETUJUI_BUKU_KENDALI_NIP'];
		$MENGTAHUI_NAMA = $arr_config['BAG_MENGETAHUI_BUKU_KENDALI_NAMA'];
		$MENGTAHUI_JABATAN = $arr_config['BAG_MENGETAHUI_BUKU_KENDALI_JABATAN'];
		$MENGTAHUI_NIP = $arr_config['BAG_MENGETAHUI_BUKU_KENDALI_NIP'];

	    $where = " 1=1 ";
	    $jenis = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
		switch($jenis){
			case 4 : $where .= "AND simpatda_type in ('4','24')";break;
			case 5 : $where .= "AND simpatda_type in ('5','25')";break;
			case 6 : $where .= "AND simpatda_type in ('6','26')";break;
			case 7 : $where .= "AND simpatda_type in ('7','27')";break;
			case 8 : $where .= "AND simpatda_type in ('8','28')";break;
			case 9 : $where .= "AND simpatda_type in ('9','29')";break;
			case 10 : $where .= "AND simpatda_type in ('10','30')";break;
			case 11: $where .= "AND simpatda_type in ('11','31')";break;
			case 12: $where .= "AND simpatda_type in ('12','32')";break;
		}
            $where .=" AND str_to_date(substr(saved_date,1,10),'%Y-%m-%d') BETWEEN '{$_REQUEST['date1']}' AND '{$_REQUEST['date2']}'";

            $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op like '%{$_REQUEST['CPM_KECAMATAN']}%'" : "";
            $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op like '%{$_REQUEST['CPM_KELURAHAN']}%' " : "";
            // $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND  CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";
            if(isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != ""){
                if(strlen($_REQUEST['CPM_KODE_REKENING'])==9)
                    $where.= " AND simpatda_rek like '{$_REQUEST['CPM_KODE_REKENING']}%' ";
                elseif(strlen($_REQUEST['CPM_KODE_REKENING'])>9)
                    $where.= " AND simpatda_rek = '{$_REQUEST['CPM_KODE_REKENING']}' ";
            }
            

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id WHERE {$where}";

            $result = mysqli_query($Conn_gw, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data        
            $query = "select jenis,op_nama, npwpd, op_alamat, sptpd, simpatda_tahun_pajak, simpatda_bulan_pajak, payment_flag, date_format(masa_pajak_awal,'%m') as masa_pajak_awal,
                    date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, 
                    date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid 
                    FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
                    WHERE {$where} ORDER BY saved_date DESC ";
            $result = mysqli_query($Conn_gw, $query);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpos")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("patda")
                ->setKeywords("-");


	$objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('E1', 'BUKU KENDALI ')
                ->setCellValue('E2', 'PAJAK '.strtoupper($_REQUEST['jenis_nm']))
                ->setCellValue('A3', 'Tanggal Ketetapan ')->setCellValue('B3', ": {$_REQUEST['date1']} s.d {$_REQUEST['date2']}")
                ->setCellValue('A4', 'Tanggal Setor')->setCellValue('B4',": {$_REQUEST['date1']} s.d {$_REQUEST['date2']}");


        // Add some data

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A6', 'No.')
                ->setCellValue('B6', 'Nomor SPTPD')
                ->setCellValue('C6', 'Tanggal')
                ->setCellValue('D6', 'Nama')
                ->setCellValue('E6', 'Alamat')
                ->setCellValue('F6', 'NPWPD')
                ->setCellValue('G6', 'Tanggal')
                ->setCellValue('H6', 'Jumlah Setor (Rp)')
                ->setCellValue('I6', 'Sisa')
                ->setCellValue('J6', 'Masa');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 7;
        $sumRows = mysqli_num_rows($result);
        $totalPayment = 0;
	$no=1;

	$array_total = array("simpatda_dibayar"=>0,"sisa"=>0);	
        while ($rowData = mysqli_fetch_assoc($result)) {

		$rowData['sisa'] = 0;
		if($rowData['payment_flag'] == 0){ 
			$rowData['sisa'] = $rowData['simpatda_dibayar'];
		}

		if(isset($this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']])){
			$bulan = $this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']];
		}else{
			$bulan = isset($this->arr_bulan[(int) $rowData['masa_pajak_awal']])? $this->arr_bulan[(int) $rowData['masa_pajak_awal']] : $rowData['masa_pajak_awal'];
		}

		$rowData['masa'] = $bulan." ".$rowData['simpatda_tahun_pajak'];

		$array_total['simpatda_dibayar'] += $rowData['simpatda_dibayar'];
		$array_total['sisa'] += $rowData['sisa'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $no);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['sptpd'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($rowData['saved_date']));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['op_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $rowData['op_alamat'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['npwpd']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['payment_paid']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, number_format($rowData['simpatda_dibayar']));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, number_format($rowData['sisa']));
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['masa']);

		
            $row++;$no++;
        }

            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, 'Total Keseluruhan');
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, number_format($array_total['simpatda_dibayar']));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, number_format($array_total['sisa']));


	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:J{$row}")->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle("A{$row}:J{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle("A{$row}:J{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');

	$row +=3;

	$objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B'.($row), 'Menyetujui')
		->setCellValue('B'.($row+1), "PENDAPATAN {$JENIS_PEMERINTAHAN} {$NAMA_PEMERINTAHAN}")
		->setCellValue('B'.($row+6), $PENYETUJU_NAMA)
		->setCellValue('B'.($row+7), $PENYETUJU_JABATAN)
		->setCellValue('B'.($row+8), $PENYETUJU_NIP)

		->setCellValue('E'.($row), 'Mengetahui')
		->setCellValue('E'.($row+1), 'Kabid Penagihan dan Pembukuan')
		->setCellValue('E'.($row+6), $MENGTAHUI_NAMA)
		->setCellValue('E'.($row+7), $MENGTAHUI_JABATAN)
		->setCellValue('E'.($row+8), $MENGTAHUI_NIP)

		->setCellValue('H'.($row), 'Diperiksa Oleh')
		->setCellValue('H'.($row+1), 'Kasi Pembukuan dan Verifikasi')
		->setCellValue('H'.($row+6), $arr_config['BAG_DIPERIKSA_OLEH_NAMA'])->setCellValue('H'.($row+7),  $arr_config['BAG_DIPERIKSA_OLEH_JABATAN'])->setCellValue('H'.($row+8),  $arr_config['BAG_DIPERIKSA_OLEH_NIP']);


	$objPHPExcel->getActiveSheet()->getStyle('H7:I' . ($row+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Wajib Pajak');
	$objPHPExcel->getActiveSheet()->getStyle('E1:E2')->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A6:J6')->applyFromArray(
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
	$objPHPExcel->getActiveSheet()->getStyle("B{$row}:H".($row+8))->applyFromArray(
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
        
        $objPHPExcel->getActiveSheet()->getStyle("A6:J".($row-3))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A6:J6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A6:J6')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "J"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="kendali' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    
    public function filtering_rekap_bulan($id) {
        $opt_jenis_pajak = '<option value="">All</option>';
        foreach ($this->arr_pajak as $x => $y) {
            $opt_jenis_pajak .= "<option value=\"{$x}\">{$y}</option>";
        }

        $opt_tahun = "<option value=''>Pilih Tahun</option>";
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $opt_tahun .= "<option value='{$th}'>{$th}</option>";
        }

        $opt_rekening = '<option value="">All</option>';
        $reks = $this->getDataRekening();
        foreach($reks as $header=>$rek){
            $aRek = array_values($rek);
            if(count($aRek)>1){
                $opt_rekening .= "<option value=\"{$header}\">{$aRek[0]['nmheader3']}</option>";
                foreach($rek as $k=>$v){
                    $opt_rekening .= "<option value=\"{$k}\">&nbsp; $k - {$v['nmrek']}</option>";
                }
            }else{
                // $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']} ({$aRek[0]['kdrek']})</option>";
                $opt_rekening .= "<option value=\"{$aRek[0]['kdrek']}\">{$aRek[0]['nmrek']}</option>";
            }
        }

        $opt_kecamatan = '<option value="">All</option>';
        $query = "SELECT * FROM PATDA_MST_KECAMATAN order by CPM_KECAMATAN";
        $res = mysqli_query($this->Conn, $query);
        while($list = mysqli_fetch_object($res)){
            $opt_kecamatan .= "<option value=\"{$list->CPM_KEC_ID}\">{$list->CPM_KECAMATAN}</option>";
        }

        $html = "<div class=\"filtering\">
                    <form><table><tr valign=\"bottom\">
                        <td>
                            <input type='hidden' id=\"HIDDEN-{$id}\" a=\"{$this->_a}\">
                            Jenis Pajak :<br><select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\" >{$opt_jenis_pajak}</select> 
                        </td>
                        <td>Tahun :<br><select name=\"simpatda_tahun_pajak-{$id}\" id=\"simpatda_tahun_pajak-{$id}\">{$opt_tahun}</select></td>
                        <td>Kode Rekening :<br><select name=\"CPM_KODE_REKENING-{$id}\" style=\"max-width:200px\" id=\"CPM_KODE_REKENING-{$id}\">{$opt_rekening}</select></td>
                        <td>Kecamatan :<br><select id=\"CPM_KECAMATAN-{$id}\">{$opt_kecamatan}</select></td>
                        <td>Kelurahan :<br><select id=\"CPM_KELURAHAN-{$id}\"><option value=\"\">All</option></select></td>
                        <td>
                            <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                            <button type=\"button\" onclick=\"javascript:download_excel('{$id}','function/PATDA-V1/rekap/svc-download.rekap-bulan.xls.php')\">Export to xls</button>
                        </td>
                        </tr></table></form>
                </div>";
        return $html;
    }

    
    public function grid_table_rekap_bulan() {
		$arr_config = $this->get_config_value($this->_a);

		$JENIS_PEMERINTAHAN = $arr_config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $arr_config['PEMERINTAHAN_NAMA'];
        
		$DIR = "PATDA-V1";
        $modul = "rekap";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <style>.filtering input,.filtering select{height:23px}.number{text-align:right}</style>
                {$this->filtering_rekap_bulan($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"><div id='title-rekap-bulan'></div></div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        $(\".date\").datepicker({
                            dateFormat: \"yy-mm-dd\",
                            showOn: \"button\",
                            buttonImageOnly: false,
                            buttonText: \"..\"
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
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-rekap-bulan.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_JENIS_PAJAK: {title: 'Uraian',width: '30%'},                                 
                                CPM_BLN01: {title: 'Januari', listClass:'number', width: '10%'},
                                CPM_BLN02: {title: 'Februari', listClass:'number', width: '10%'},
                                CPM_BLN03: {title: 'Maret', listClass:'number', width: '10%'},
                                CPM_BLN04: {title: 'April', listClass:'number', width: '10%'},
                                CPM_BLN05: {title: 'Mei', listClass:'number', width: '10%'},
                                CPM_BLN06: {title: 'Juni', listClass:'number', width: '10%'},
                                CPM_BLN07: {title: 'Juli', listClass:'number', width: '10%'},
                                CPM_BLN08: {title: 'Agustus', listClass:'number', width: '10%'},
                                CPM_BLN09: {title: 'September', listClass:'number', width: '10%'},
                                CPM_BLN10: {title: 'Oktober', listClass:'number', width: '10%'},
                                CPM_BLN11: {title: 'November', listClass:'number', width: '10%'},
                                CPM_BLN12: {title: 'Desember', listClass:'number', width: '10%'},
                                CPM_JUMLAH: {title: 'Jumlah', listClass:'number', width: '10%'}
                            },
							recordsLoaded: function (event, data) {
								var res = data.serverResponse;
								for (var i in data.records) {
									if(data.records[i].NO==''){
										$('#laporanPajak-{$this->_i}').find('.jtable tbody tr:eq('+i+') td').css({'background-color':'#7cadc5','border':'1px #CCC solid','font-weight':'bold'});
									}
								}
							}
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            if($('#simpatda_tahun_pajak-{$this->_i}').val()==''){
                                $('#simpatda_tahun_pajak-{$this->_i}').focus();
                                $('tr.jtable-no-data-row td').html('Silakan pilih Tahun!');
                                return false;
                            }
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                jenis : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),                                    
                                simpatda_tahun_pajak : $('#simpatda_tahun_pajak-{$this->_i}').val(),                                    
                                CPM_KELURAHAN : $('#CPM_KELURAHAN-{$this->_i}').val(),                                    
                                CPM_KECAMATAN : $('#CPM_KECAMATAN-{$this->_i}').val(),                                    
                                CPM_KODE_REKENING : $('#CPM_KODE_REKENING-{$this->_i}').val()                                    
                            });
							$('#title-rekap-bulan').html('<div class=\"well container-fluid\"><div class=\"row text-center\"><div class=\"col-xs-12\"><b>LAPORAN PENERIMAAN 9 PAJAK DAERAH<br/>".$JENIS_PEMERINTAHAN." ".$NAMA_PEMERINTAHAN."<br/>TAHUN '+$('#simpatda_tahun_pajak-{$this->_i}').val()+'</b></div></div></div>');	
                        });
						$('tr.jtable-no-data-row td').html('Silakan lakukan pencarian!');
                    });
                </script>";
        echo $html;
    }

    public function grid_data_rekap_bulan() {
        try {
			if (!isset($_REQUEST['simpatda_tahun_pajak']) || $_REQUEST['simpatda_tahun_pajak']=="") {
                $jTableResult = array();
                $jTableResult['Result'] = "OK";
                print $this->Json->encode($jTableResult);
                exit;
            }
            $arr_config = $this->get_config_value($this->_a);

            $dbName = $arr_config['PATDA_DBNAME'];
            $dbHost = $arr_config['PATDA_HOSTPORT'];
            $dbPwd = $arr_config['PATDA_PASSWORD'];
            $dbUser = $arr_config['PATDA_USERNAME'];

            $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
            //mysqli_select_db($dbName, $Conn_gw);

            $recordCount = 9;


            $where = "";

            $jenis = $this->idpajak_sw_to_gw[$_REQUEST['jenis']];
            switch($jenis){
                case 4 : $where .= "AND simpatda_type in ('4','24')";break;
                case 5 : $where .= "AND simpatda_type in ('5','25')";break;
                case 6 : $where .= "AND simpatda_type in ('6','26')";break;
                case 7 : $where .= "AND simpatda_type in ('7','27')";break;
                case 8 : $where .= "AND simpatda_type in ('8','28')";break;
                case 9 : $where .= "AND simpatda_type in ('9','29')";break;
                case 10 : $where .= "AND simpatda_type in ('10','30')";break;
                case 11: $where .= "AND simpatda_type in ('11','31')";break;
                case 12: $where .= "AND simpatda_type in ('12','32')";break;
            }
            
            $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op = '{$_REQUEST['CPM_KECAMATAN']}'" : "";
            $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op = '{$_REQUEST['CPM_KELURAHAN']}' " : "";
            // $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND  CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";
            if(isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != ""){
                if(strlen($_REQUEST['CPM_KODE_REKENING'])==9)
                    $where.= " AND simpatda_rek like '{$_REQUEST['CPM_KODE_REKENING']}%' ";
                elseif(strlen($_REQUEST['CPM_KODE_REKENING'])>9)
                    $where.= " AND simpatda_rek = '{$_REQUEST['CPM_KODE_REKENING']}' ";
            }

            #query select list data        
            $query = "select simpatda_type, SUBSTR(payment_paid, 6,2) as bulan,sum(simpatda_dibayar) as simpatda_dibayar FROM SIMPATDA_GW 
						WHERE payment_flag=1 and YEAR(payment_paid) = '{$_REQUEST['simpatda_tahun_pajak']}' $where
						GROUP BY simpatda_type, SUBSTR(payment_paid, 1,7) ORDER BY simpatda_type asc";
			$result = mysqli_query($Conn_gw, $query);
			
            $data = array();
			while ($dt = mysqli_fetch_assoc($result)) {
				$data[$dt['simpatda_type']]["CPM_BLN{$dt['bulan']}"] = $dt['simpatda_dibayar'];
            }
            //print_r($data);exit;
            $rows = array();
            $rows_total = array();
            $rows_total["NO"] = "";
            $rows_total["CPM_JENIS_PAJAK"] = "Jumlah";
            $total = 0;
            
            $nomor = 1;
            foreach($data as $z => $dt){
				$row = array();
				$subtotal = 0;
				$row['NO'] = $nomor;
				$row['CPM_JENIS_PAJAK'] = $this->arr_pajak_gw[$z];
				for($x=1;$x<=12;$x++){
					$no = str_pad($x,2,'0',STR_PAD_LEFT);
					$row["CPM_BLN{$no}"] = isset($dt["CPM_BLN{$no}"])? $dt["CPM_BLN{$no}"] : '0';
					$subtotal += $row["CPM_BLN{$no}"];
					
					$rows_total["CPM_BLN{$no}"] = isset($rows_total["CPM_BLN{$no}"])? ($rows_total["CPM_BLN{$no}"] + $row["CPM_BLN{$no}"]) : $row["CPM_BLN{$no}"];
					$row["CPM_BLN{$no}"] = ($row["CPM_BLN{$no}"] == 0)? "0" : "<a href='javascript:void(0)' onclick=\"javascript:download_excel_rekap_bulan('{$z}','{$_REQUEST['simpatda_tahun_pajak']}','{$no}')\">".number_format($row["CPM_BLN{$no}"],0)."</a>";
				}
				$row['CPM_JUMLAH'] = number_format($subtotal);
				$total+= $subtotal;
				$rows[$z] = $row;
				$nomor++;
			}
			
			/*foreach($this->arr_pajak as $key=>$val){
				if(!isset($rows[$key])){
					$row = array();
					$row['NO'] = $nomor;
					$row['CPM_JENIS_PAJAK'] = $val;
					for($x=1;$x<=12;$x++){
						$no = str_pad($x,2,'0',STR_PAD_LEFT);
						$row["CPM_BLN{$no}"] = '0';
					}
					$row['CPM_JUMLAH'] = '0';
					$rows[$key] = $row;
				}else{
					$row = $rows[$key];
					$row['NO'] = $nomor;
					$rows[$key] = $row;
				}
				$nomor++;
			}*/
			
			$rows_total["NO"] = "";
			$rows_total["CPM_JUMLAH"] = number_format($total);
			foreach($rows_total as $key=>$val){
				$rows_total[$key] = is_numeric($val)? "<b>".number_format($val,0)."</b>": "<b>{$val}</b>";
			}
			//sort($rows);
            $rows[] = $rows_total;
            
            #print_r($rows);
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

    function download_excel_rekap_bulan() {

        $arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];
		
		$JENIS_PEMERINTAHAN = $arr_config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $arr_config['PEMERINTAHAN_NAMA'];
		
		$kabid_jabatan = $arr_config['REKAP_BULANAN_JABATAN'];
		$kabid_nama = $arr_config['REKAP_BULANAN_NAMA'];
		$kabid_nip = $arr_config['REKAP_BULANAN_NIP'];


		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName, $Conn_gw);

		$recordCount = 9;


            $where = "";
            $where.= (isset($_REQUEST['CPM_KECAMATAN']) && $_REQUEST['CPM_KECAMATAN'] != "") ? " AND kecamatan_op like '%{$_REQUEST['CPM_KECAMATAN']}%'" : "";
            $where.= (isset($_REQUEST['CPM_KELURAHAN']) && $_REQUEST['CPM_KELURAHAN'] != "") ? " AND kelurahan_op like '%{$_REQUEST['CPM_KELURAHAN']}%' " : "";
            // $where.= (isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != "") ? " AND  CPM_REKENING like \"{$_REQUEST['CPM_KODE_REKENING']}%\" " : "";
            if(isset($_REQUEST['CPM_KODE_REKENING']) && $_REQUEST['CPM_KODE_REKENING'] != ""){
                if(strlen($_REQUEST['CPM_KODE_REKENING'])==9)
                    $where.= " AND simpatda_rek like '{$_REQUEST['CPM_KODE_REKENING']}%' ";
                elseif(strlen($_REQUEST['CPM_KODE_REKENING'])>9)
                    $where.= " AND simpatda_rek = '{$_REQUEST['CPM_KODE_REKENING']}' ";
            }

		#query select list data        
		$query = "select simpatda_type, SUBSTR(payment_paid, 6,2) as bulan,sum(simpatda_dibayar) as simpatda_dibayar FROM SIMPATDA_GW 
					WHERE payment_flag=1 and SUBSTR(payment_paid, 1,4) = '{$_REQUEST['simpatda_tahun_pajak']}' $where
					GROUP BY simpatda_type, SUBSTR(payment_paid, 1,7) ORDER BY simpatda_type asc";
		$result = mysqli_query($Conn_gw, $query);
		
		$data = array();
		while ($dt = mysqli_fetch_assoc($result)) {
			$data[$dt['simpatda_type']]["CPM_BLN{$dt['bulan']}"] = $dt['simpatda_dibayar'];
		}
		
		$lists = array();
		$lists_total = array();
		$lists_total["NO"] = "";
		$lists_total["CPM_JENIS_PAJAK"] = "Jumlah";
		$total = 0;
		
		$nomor = 1;
		foreach($data as $z => $dt){
			$list = array();
			$subtotal = 0;
			$list['NO'] = $nomor;
			$list['CPM_JENIS_PAJAK'] = $this->arr_pajak_gw[$z];
			for($x=1;$x<=12;$x++){
				$no = str_pad($x,2,'0',STR_PAD_LEFT);
				$list["CPM_BLN{$no}"] = isset($dt["CPM_BLN{$no}"])? $dt["CPM_BLN{$no}"] : '0';
				$subtotal += $list["CPM_BLN{$no}"];
				
				$lists_total["CPM_BLN{$no}"] = isset($lists_total["CPM_BLN{$no}"])? ($lists_total["CPM_BLN{$no}"] + $list["CPM_BLN{$no}"]) : $list["CPM_BLN{$no}"];
				$list["CPM_BLN{$no}"] = number_format($list["CPM_BLN{$no}"],0);
			}
			$list['CPM_JUMLAH'] = number_format($subtotal);
			$total+= $subtotal;
			$lists[$z] = $list;
			$nomor++;
		}
		
		/*foreach($this->arr_pajak as $key=>$val){
			
			if(!isset($lists[$key])){
				$list = array();
				$list['NO'] = $nomor;
				$list['CPM_JENIS_PAJAK'] = $val;
				for($x=1;$x<=12;$x++){
					$no = str_pad($x,2,'0',STR_PAD_LEFT);
					$list["CPM_BLN{$no}"] = '0';
				}
				$list['CPM_JUMLAH'] = '0';
				$lists[$key] = $list;
			}
			
			$list = $lists[$key];
			$list['NO'] = $nomor;
			$lists[$key] = $list;
			$nomor++;
		}*/
		
		$lists_total["NO"] = "";
		$lists_total["CPM_JUMLAH"] = number_format($total);
		foreach($lists_total as $key=>$val){
			$lists_total[$key] = is_numeric($val)? number_format($val,0): $val;
		}
		
		sort($lists);
		$lists[] = $lists_total;
		
		#print_r($lists);exit;
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpos")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("patda")
                ->setKeywords("-");

		
		$objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C1', 'LAPORAN PENERIMAAN 9 PAJAK DAREAH ')
                ->setCellValue('C2', $JENIS_PEMERINTAHAN.' '.$NAMA_PEMERINTAHAN)
                ->setCellValue('C3', 'TAHUN '.$_REQUEST['simpatda_tahun_pajak']);
        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A5', 'No.')
                ->setCellValue('B5', 'Uraian')
                ->setCellValue('C5', 'BULAN')
                ->setCellValue('C6', 'Januari')
                ->setCellValue('D6', 'Februari')
                ->setCellValue('E6', 'Maret')
                ->setCellValue('F6', 'April')
                ->setCellValue('G6', 'Mei')
                ->setCellValue('H6', 'Juni')
                ->setCellValue('I6', 'Juli')
                ->setCellValue('J6', 'Agustus')
                ->setCellValue('K6', 'September')
                ->setCellValue('L6', 'Oktober')
                ->setCellValue('M6', 'November')
                ->setCellValue('N6', 'Desember')
                ->setCellValue('O5', 'Jumlah');

		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:A6');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B5:B6');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('C5:N5');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('O5:O6');
		
        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 7;
		foreach($lists as $data){
			$col = "A";
			foreach($data as $d){
				$objPHPExcel->getActiveSheet()->setCellValue($col . $row, $d);
				$col++;
			}
			$row++;
		}
		
		$objPHPExcel->getActiveSheet()->getStyle('A5:O' . ($row - 1))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
        );
	
		$objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("L".($row+3), "Indralaya ".date("d")." ".$this->arr_bulan[(int) date("m")]." ".date("Y"))
                ->setCellValue("L".($row+4), $kabid_jabatan)
                ->setCellValue("L".($row+8), $kabid_nama)
                ->setCellValue("L".($row+9), "NIP. ".$kabid_nip);
                
		$objPHPExcel->getActiveSheet()->getStyle('C7:O' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->setTitle("Laporan Penerimaan {$_REQUEST['simpatda_tahun_pajak']}");
        $objPHPExcel->getActiveSheet()->getStyle('C1:C3')->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A5:O6')->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A5:O6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A5:O6')->getFill()->getStartColor()->setRGB('E4E4E4');
        
        $objPHPExcel->getActiveSheet()->getStyle("A".($row-1).":O".($row-1)."")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle("A".($row-1).":O".($row-1)."")->getFill()->getStartColor()->setRGB('E4E4E4');


		$objPHPExcel->getActiveSheet()->getStyle("A".($row-1).":O".($row-1))->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    )
                )
        );

        for ($x = "A"; $x <= "O"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="rekap-bulanan-' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    
    
    public function download_excel_rekap_bulan_detail() {
		$arr_config = $this->get_config_value($this->_a);

        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbUser = $arr_config['PATDA_USERNAME'];
		
		$JENIS_PEMERINTAHAN = $arr_config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $arr_config['PEMERINTAHAN_NAMA'];
        
        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysqli_select_db($dbName, $Conn_gw);

        $where = " 1=1 ";
        $where .= $_REQUEST['jenis'] == "" ? "" : " AND simpatda_type='{$_REQUEST['jenis']}'";

        $where .= $_REQUEST['simpatda_tahun_pajak'] == "" ? "" : " AND SUBSTR(payment_paid, 1,4)='{$_REQUEST['simpatda_tahun_pajak']}'";
        $where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND SUBSTR(payment_paid, 6,2)='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";

        $where .= "AND payment_flag = 1";

        $query = "select jenis,op_nama, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,
                    substr(masa_pajak_awal,6,2) as masa_pajak_awal, simpatda_bulan_pajak,
                    date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, date_format(saved_date,'%d-%m-%Y') as saved_date, 
                    date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid 
                    FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
                    WHERE {$where} ORDER BY 1";
		#echo $query;exit;
        $result = mysqli_query($Conn_gw, $query);

		// Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

		// Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
                ->setLastModifiedBy("vpost")
                ->setTitle("-")
                ->setSubject("-")
                ->setDescription("pbb")
                ->setKeywords("-");

		$objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C1', 'LAPORAN PENERIMAAN 9 PAJAK DAREAH ')
                ->setCellValue('C2', $JENIS_PEMERINTAHAN.' '.$NAMA_PEMERINTAHAN)
                ->setCellValue('C3', 'Pajak '.$this->arr_pajak[$_REQUEST['jenis']])
                ->setCellValue('C4', $this->arr_bulan[(int) $_REQUEST['simpatda_bulan_pajak']].' '.$_REQUEST['simpatda_tahun_pajak']);
                
		// Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A6', 'No.')
                ->setCellValue('B6', 'Jenis Pajak')
                ->setCellValue('C6', 'Nama Pajak')
                ->setCellValue('D6', 'No. SPTPD')
                ->setCellValue('E6', 'NPWPD')
                ->setCellValue('F6', 'Nama WP')
                ->setCellValue('G6', 'Alamat WP')
                ->setCellValue('H6', 'Tahun Pajak')
                ->setCellValue('I6', 'Bulan Pajak')
                ->setCellValue('J6', 'Tgl Jatuh Tempo')
                ->setCellValue('K6', 'Tagihan (Rp)')
                ->setCellValue('L6', 'Status')
                ->setCellValue('M6', 'Tanggal Lapor')
                ->setCellValue('N6', 'Tanggal Bayar');

// Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 7;
        $sumRows = mysqli_num_rows($result);
        $totalPayment = 0;
        $no = 0;
        while ($rowData = mysqli_fetch_assoc($result)) {
            $tgl_jth_tempo = explode('-', $rowData['expired_date']);
            if (count($tgl_jth_tempo) == 3)
                $tgl_jth_tempo = $tgl_jth_tempo[2] . '-' . $tgl_jth_tempo[1] . '-' . $tgl_jth_tempo[0];

            $totalPayment += $rowData['simpatda_dibayar'];
            $bulan = isset($this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']]) ? $this->arr_bulan[(int) $rowData['simpatda_bulan_pajak']] : $this->arr_bulan[(int) $rowData['masa_pajak_awal']];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, (++$no));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['jenis']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($rowData['op_nama']));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['sptpd'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $rowData['npwpd'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['wp_nama']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['wp_alamat']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['simpatda_tahun_pajak']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $bulan);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $tgl_jth_tempo);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['simpatda_dibayar']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, ($rowData['payment_flag'] == '1') ? 'Lunas' : '');
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['saved_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['payment_paid']);
            $row++;
        }

        $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:J{$row}");
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Total");
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, "=SUM(K2:K" . ($row - 1) . ")");
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, "");
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, "");
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, "");
        $sumRows++;

		// Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Data Pajak Sudah Bayar');

		//----set style cell
		//style header
        $objPHPExcel->getActiveSheet()->getStyle('A6:N6')->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle("A{$row}:N{$row}")->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle("A6:N{$row}")->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle("I2:N{$row}")->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A6:N6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A6:N6')->getFill()->getStartColor()->setRGB('E4E4E4');
        $objPHPExcel->getActiveSheet()->getStyle('A7:A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B7:G'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle('H7:J'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('K7:K'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle('L7:L'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle('M7:M'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('N7:N'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);

        ob_clean();

// Redirect output to a client’s web browser (Excel5)
        $nmfile = $_REQUEST['nmfile'];
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nmfile . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    
    public function filtering_rekap_detail($id) {
        $bln = date("m");
        $opt_bulan = "<option value=\"\">Semua</option>";
        for ($b = 1; $b <= 12; $b++) {
            $opt_bulan .= "<option value=\"{$b}\">{$this->arr_bulan[$b]}</option>";
        }

        $html = "<div class=\"filtering\">
                    <style> .monitoring td{background:transparent}</style>
                    <form>
						<input type=\"hidden\" id=\"detailtype-{$id}\" name=\"detailtype-{$id}\" value=\"{$_REQUEST['detailtype']}\">
                        <table width=\"850\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" class=\"monitoring\">
                                <tr>
                                    <td width=\"150\">Tahun Pajak </td>
                                    <td width=\"3\">:</td>
                                    <td width=\"180\"><input type=\"text\" style=\"width:50px;\" readonly name=\"simpatda_tahun_pajak-{$id}\" id=\"simpatda_tahun_pajak-{$id}\" value=\"{$_REQUEST['tahun']}\"> Bulan : <select name=\"simpatda_bulan_pajak-{$id}\" id=\"simpatda_bulan_pajak-{$id}\">{$opt_bulan}</select></td>
                                    <td width=\"130\">No. SPTPD / NPWPD </td>
                                    <td width=\"3\">:</td>
                                    <td width=\"120\"><input type=\"text\" name=\"sptpd-{$id}\" id=\"sptpd-{$id}\" /></td>                                    
                                    <td width=\"\">Alamat : <input type=\"text\" name=\"wp_alamat\" id=\"wp_alamat-{$id}\"/> <input type=\"submit\" name=\"button2\" id=\"cari-{$id}\" value=\"Tampilkan\"/></td>
                                </tr>                                
                                <tr>
                                    <td width=\"150\">Nama WP / Tempat Usaha</td>
                                    <td width=\"3\">:</td>
                                    <td width=\"180\"><input type=\"text\" name=\"wp_nama\" id=\"wp_nama-{$id}\" /></td>                                    
                                    <td width=\"130\">Jenis Pajak </td>
                                    <td width=\"3\">:</td>
                                    <td width=\"120\"> <input type=\"hidden\" id=\"jenis-{$id}\" name=\"jenis-{$id}\" value=\"{$_REQUEST['jenis']}\"/> {$this->arr_pajak[$_REQUEST['jenis']]}</td>
                                    <td width=\"130\">                                        
                                        <!--<input type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor ke xls\" onClick=\"toExcel({$id})\"/>
                                        <input type=\"button\" name=\"buttonToExcel\" id=\"buttonToExcel\" value=\"Ekspor ke pdf\" onClick=\"toPdf({$id})\"/>    
                                        <span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span>-->
                                    </td>
                                </tr>
                            </table>
                    </form>
                </div> ";
        return $html;
    }

    public function grid_table_rekap_detail() {
        $DIR = "PATDA-V1";
        $modul = "rekap";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering_rekap_detail($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        $('.datepicker').datepicker({
                            dateFormat: 'yy-mm-dd',
                            changeYear: true,
                            showOn: \"button\",
                            buttonImageOnly: false,
                            buttonText: \"...\"});
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting: 'saved_date ASC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-rekap-detail.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                jenis: {title: 'Jenis Pajak',width: '10%'},
                                sptpd: {title: 'No. SPTPD',width: '7%'},
                                npwpd: {title: 'NPWPD',width: '5%'},
                                op_nama: {title: 'Nama Pajak',width: '10%'},
                                wp_nama: {title: 'Nama WP',width: '10%'},
                                wp_alamat: {title: 'Alamat WP',width: '10%'},
                                simpatda_tahun_pajak: {title: 'Tahun Pajak',width: '5%'},
                                simpatda_bulan_pajak: {title: 'Masa Pajak',width: '8%'},
                                saved_date: {title: 'Tgl Disetujui',width: '7%'},
                                expired_date: {title: 'Jatuh Tempo',width: '7%'},
                                simpatda_dibayar: {title: 'Tagihan',width: '7%'},
                                payment_paid: {title: 'Tanggal Bayar',width: '10%'}
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {                                
                                sptpd : $('#sptpd-{$this->_i}').val(),
                                wp_alamat : $('#wp_alamat-{$this->_i}').val(),                                
                                wp_nama : $('#wp_nama-{$this->_i}').val(),
                                simpatda_dibayar : $('#simpatda_dibayar-{$this->_i}').val(),
                                jenis : $('#jenis-{$this->_i}').val(),
                                expired_date1 : $('#expired_date1-{$this->_i}').val(),
                                expired_date2 : $('#expired_date2-{$this->_i}').val(),    
                                simpatda_tahun_pajak : $('#simpatda_tahun_pajak-{$this->_i}').val(),
                                simpatda_bulan_pajak : $('#simpatda_bulan_pajak-{$this->_i}').val(),
                                detailtype : $('#detailtype-{$this->_i}').val(),
                            });
                        });
                        $('#cari-{$this->_i}').click();                        
                    });
                </script>";

        echo $html;
    }

    public function grid_data_rekap_detail() {
        try {
            $arr_config = $this->get_config_value($this->_a);

            $dbName = $arr_config['PATDA_DBNAME'];
            $dbHost = $arr_config['PATDA_HOSTPORT'];
            $dbPwd = $arr_config['PATDA_PASSWORD'];
            $dbUser = $arr_config['PATDA_USERNAME'];

            $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
            //mysqli_select_db($dbName, $Conn_gw);

            $where = " 1=1 ";
            $where .= $_REQUEST['sptpd'] == "" ? "" : " AND (sptpd like '%{$_REQUEST['sptpd']}%' OR npwpd like '%{$_REQUEST['sptpd']}%')";
            $where .= $_REQUEST['wp_alamat'] == "" ? "" : " AND wp_alamat like '%{$_REQUEST['wp_alamat']}%'";
            $where .= $_REQUEST['wp_nama'] == "" ? "" : " AND (wp_nama like '%{$_REQUEST['wp_nama']}%' or op_nama like '%{$_REQUEST['wp_nama']}%')";
            $where .= " AND b.id_sw='{$_REQUEST['jenis']}'";

            $where .= " AND simpatda_tahun_pajak='{$_REQUEST['simpatda_tahun_pajak']}'";
            $where .= $_REQUEST['simpatda_bulan_pajak'] == "" ? "" : " AND simpatda_bulan_pajak='" . str_pad($_REQUEST['simpatda_bulan_pajak'], 2, "0", STR_PAD_LEFT) . "'";
            
            // ketetapan / penerimaan / piutang
            if($_REQUEST['detailtype'] == "penerimaan"){ 
				$where .= " AND payment_flag = 1 ";
			}elseif($_REQUEST['detailtype'] == "piutang"){
				$where .= " AND (payment_flag != 1 OR payment_flag IS NULL) ";
			}else{
				$where .= "";
			}

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id WHERE {$where}";

            $result = mysqli_query($Conn_gw, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data        
            $query = "select jenis,op_nama, sptpd, npwpd,wp_nama, wp_alamat, simpatda_tahun_pajak,simpatda_bulan_pajak,
                    date_format(expired_date,'%d-%m-%Y') as expired_date , simpatda_dibayar, IFNULL(payment_flag,0) as payment_flag, 
                    date_format(saved_date,'%d-%m-%Y') as saved_date, 
                    date_format(payment_paid,'%d-%m-%Y %h:%i:%s') as payment_paid,
                    a.masa_pajak_awal, a.masa_pajak_akhir
                    FROM SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id 
                    WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($Conn_gw, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                if($row['simpatda_bulan_pajak'] == "00"){
					$row['simpatda_bulan_pajak'] = $row['masa_pajak_awal']." s.d ".$row['masa_pajak_akhir'];
				}else{
					$row['simpatda_bulan_pajak'] = isset($this->arr_bulan[(int) $row['simpatda_bulan_pajak']]) ? $this->arr_bulan[(int) $row['simpatda_bulan_pajak']] : $row['simpatda_bulan_pajak'];
				}
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
}

?>
