<?php
error_reporting(0);
class MonitoringPajak extends Pajak {

    private $type = '';

    public function __construct() {
        parent::__construct();
        if(isset($_REQUEST['CPM_NPWPD']))$_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
    }

    public function filtering_sptpd($id) {
        $html = "<div class=\"filtering\">
                    <form>
                        NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >  
                        TAHUN : <select name=\"CPM_TAHUN_PAJAK-{$id}\" id=\"CPM_TAHUN_PAJAK-{$id}\">";
        $html.= "<option value=''>All</option>";
        for ($th = date("Y") - 5; $th <= date("Y"); $th++) {
            $html.= "<option value='{$th}'>{$th}</option>";
        }
        $html.= "</select> MASA PAJAK : <select name=\"CPM_MASA_PAJAK-{$id}\" id=\"CPM_MASA_PAJAK-{$id}\">";
        $html.= "<option value=''>All</option>";
        foreach ($this->arr_bulan as $x => $y) {
            $html.= "<option value='{$x}'>{$y}</option>";
        }
        $html.= "</select>    
                 NO. STPPD : <input type=\"text\" name=\"CPM_NO-{$id}\" id=\"CPM_NO-{$id}\" >
                        <button type=\"submit\" id=\"cari-{$id}\" class=\"btn btn-primary lm-btn\" style=\"font-size: 0.7rem !important;\" ><i class=\"fa fa-search\"></i> Cari</button>
                    </form>
                </div> ";
        return $html;
    }
	
    public function filtering_log_batal($id) {
        $html = "<div class=\"filtering\">
                    <form>
						TANGGAL :
						<input type=\"text\" name=\"START_DATE-{$id}\" id=\"START_DATE-{$id}\" class=\"start-date\" placeholder=\"Tanggal awal\" size=\"15\">
						s/d
						<input type=\"text\" name=\"END_DATE-{$id}\" id=\"END_DATE-{$id}\" class=\"end-date\" placeholder=\"Tanggal akhir\" size=\"15\">                        
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
						<button id=\"export-xls\" type=\"button\">Export to xls</button>
                    </form>
                </div> ";
        return $html;
    }	

    public function grid_table_sptpd() {
        global $a, $m;
        $DIR = "PATDA-V1";
        $modul = "pembatalan";
        $submodul = "sptpd";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering_sptpd($this->_i)}
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
                            defaultSorting: 'CPM_NPWPD ASC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false}, 
                                CPM_TGL_LAPOR: {title:'Tanggal Lapor',width: '10%'}, 
                                CPM_NO: {title: 'Nomor Laporan',width: '10%'},
                                CPM_NPWPD: {title: 'NPWPD',width: '10%'},
                                CPM_TAHUN_PAJAK: {title: 'Tahun Pajak',width: '8%'},
                                CPM_MASA_PAJAK: {title: 'Masa Pajak',width: '13%'},
                                CPM_TOTAL_PAJAK: {title: 'Total Pajak',width: '10%'},
                                CPM_VERSION: {title: 'Versi Dok',width: '6%'},
                                CPM_AUTHOR: {title: 'User Input',width: '10%'},
                                CPM_TRAN_STATUS: {title: 'Status',width: '10%'},
                                Action : {title:'',width:'3%',
                                display : function(data){
                                    var id = data.record.CPM_ID;
                                    var status = data.record.CPM_TRAN_STATUS;
                                    var hasil = $('<a href=#><img src=inc/{$DIR}/jtable/themes/notes.png title=\'Batalkan dokumen\' /></a>');
                                    if(status =='Disetujui'){                    
                                      hasil.click(function(){
                                           var ok = prompt('Apakah anda yakin untuk membatalkan persetujuan dokumen '+data.record.CPM_NO_SPTPD+' ini ?\\nSilakan isi keterangan pembatalan dibawah ini!');
                                           if(ok!=null){
                                             $.post('view/{$DIR}/pembatalan/sptpd/svc-list-Update.php',{ket:ok,a:'{$this->_a}',patdaId:id,Itab:{$this->_i}},function(hasil){                                                     
                                                 if(hasil == 1){
                                                     alert('Maaf anda tidak bisa membatalkan persetujuan karena subah di bayar !!'); 
                                                 }else{
                                                    alert('Persetujuan sudah dibatalkan.');
                                                     $('#laporanPajak-{$this->_i}').jtable('load');                                                         
                                                  }   
                                             });
                                           }          
                                      });
                                      return hasil; 
                                    }else{
                                      return '--';
                                    }                                     
                                  }
                                }
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_NO : $('#CPM_NO-{$this->_i}').val(),
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_TAHUN_PAJAK : $('#CPM_TAHUN_PAJAK-{$this->_i}').val(),
                                CPM_MASA_PAJAK : $('#CPM_MASA_PAJAK-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();                        
                           
                    });
                </script>";

        echo $html;
    }

    public function grid_table_log() {
        global $a, $m;
        $DIR = "PATDA-V1";
        $modul = "pembatalan";
        $submodul = "sptpd";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>				
				{$this->filtering_log_batal($this->_i)}
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
                            defaultSorting: 'jenis ASC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/{$submodul}/svc-list-data-log.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&f={$this->_f}&i={$this->_i}&mod={$this->_mod}',                                
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                id_switching: {key: true,list: false},
                                jenis : {title:'Pajak',width: '10%'}, 
								sptpd : {title:'No. Dokumen',width: '10%'}, 
                                log_keterangan: {title: 'Keterangan',width: '10%'},
                                log_timestamp: {title: 'Waktu',width: '10%'},
                                log_actor: {title: 'Petugas',width: '8%'}
                           },
                        });
                        $('#laporanPajak-{$this->_i}').jtable('load');
						$('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_START_DATE : $('#START_DATE-{$this->_i}').val(),
                                CPM_END_DATE : $('#END_DATE-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
						$('.start-date').datepicker({
							dateFormat: 'dd/mm/yy',
							changeYear: true,
							changeMonth: true,
							showOn: 'button',
							buttonImageOnly: false,
							buttonText: '...',
							onSelect: function(dateText) {
								$(this).change();
							}
						});
						$('.end-date').datepicker({
							dateFormat: 'dd/mm/yy',
							changeYear: true,
							changeMonth: true,
							showOn: 'button',
							buttonImageOnly: false,
							buttonText: '...',
							onSelect: function(dateText) {
								$(this).change();
							}
						});	
						$('#export-xls').click(function(){
							var start = $('#START_DATE-{$this->_i}').val();
							var end = $('#END_DATE-{$this->_i}').val();
							window.open(\"./view/PATDA-V1/pembatalan/sptpd/svc-list-data-log.php?q=".base64_encode("{'a':'$a'}")."&MOD=export_xls&CPM_START_DATE=\"+start+\"&CPM_END_DATE=\"+end+\"\", \"_newtab\");
						});
                    });
                </script>";
        echo $html;
    }

    public function grid_data_log() {
        
        try {
            
            $arr_config = $this->get_config_value($this->_a);
            $dbName = $arr_config['PATDA_DBNAME'];
            $dbHost = $arr_config['PATDA_HOSTPORT'];
            $dbPwd = $arr_config['PATDA_PASSWORD'];
            $dbUser = $arr_config['PATDA_USERNAME'];
            
            $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
            //mysql_select_db($dbName);
			
			$where = '';
			
			if((isset($_REQUEST['CPM_START_DATE']) && $_REQUEST['CPM_START_DATE'] != "") && (isset($_REQUEST['CPM_END_DATE']) && $_REQUEST['CPM_END_DATE'] != "")){
				$d = explode("/",$_REQUEST['CPM_START_DATE']);
				$start = "{$d[2]}-{$d[1]}-{$d[0]}";
				unset($d);
				
				$d = explode("/",$_REQUEST['CPM_END_DATE']);
				$end = "{$d[2]}-{$d[1]}-{$d[0]}";
				unset($d);
			}
			
            $where.= ((isset($_REQUEST['CPM_START_DATE']) && $_REQUEST['CPM_START_DATE'] != "") && (isset($_REQUEST['CPM_END_DATE']) && $_REQUEST['CPM_END_DATE'] != "")) ? " DATE_FORMAT(log_timestamp,'%Y-%m-%d') >= '{$start}' and DATE_FORMAT(log_timestamp,'%Y-%m-%d') <= '{$end}'" : "1=1";
			
            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM SIMPATDA_GW_BATAL WHERE {$where}";
            $result = mysqli_query($Conn_gw, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT G.id_switching,G.sptpd, T.jenis, G.log_keterangan, G.log_timestamp, G.log_actor FROM SIMPATDA_GW_BATAL G
                        INNER JOIN SIMPATDA_TYPE T ON G.simpatda_type = T.id
                        WHERE {$where} 
                        ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";              
            $result = mysqli_query($Conn_gw, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
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

    public function grid_data_sptpd() {
        if ($this->_i == 10) {
            exit;
        }

        $DIR = "PATDA-V1";
        try {
            $PAJAK = strtoupper($this->arr_pajak_table[$this->_i]);

            $where = "(";
            $where.= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
            $where.= " AND (tr.CPM_TRAN_STATUS in (1,2,3,4,5) AND tr.CPM_TRAN_FLAG ='0' ) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4')) ";

            $where.= (isset($_REQUEST['CPM_NO']) && $_REQUEST['CPM_NO'] != "") ? " AND CPM_NO like \"{$_REQUEST['CPM_NO']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";
            $where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND CPM_MASA_PAJAK = \"{$_REQUEST['CPM_MASA_PAJAK']}\" " : "";

            $sql = "SELECT * FROM PATDA_JENIS_PAJAK";
            $res = mysqli_query($this->Conn, $sql);

            while ($row = mysqli_fetch_assoc($res)) {
                $arrFunction[$row["CPM_NO"]] = "fPatdaPelayanan" . $row["CPM_NO"];
            }

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM PATDA_{$PAJAK}_DOC pj 
                            INNER JOIN PATDA_{$PAJAK}_PROFIL pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN PATDA_{$PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$PAJAK}_ID
                            WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT pj.CPM_ID, pj.CPM_NO, pj.CPM_TAHUN_PAJAK, 
                            CONCAT(DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y'),'%d/%m/%Y'),' - ', DATE_FORMAT(STR_TO_DATE(pj.CPM_MASA_PAJAK2,'%d/%m/%Y'),'%d/%m/%Y')) AS CPM_MASA_PAJAK, 
                            pj.CPM_TGL_LAPOR, pj.CPM_AUTHOR, pj.CPM_VERSION,
                            pj.CPM_TOTAL_PAJAK, pr.CPM_NPWPD, pr.CPM_NAMA_WP, tr.CPM_TRAN_STATUS, tr.CPM_TRAN_INFO, tr.CPM_TRAN_FLAG
                            FROM PATDA_{$PAJAK}_DOC pj INNER JOIN PATDA_{$PAJAK}_PROFIL pr ON pj.CPM_ID_PROFIL = pr.CPM_ID
                            INNER JOIN PATDA_{$PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$PAJAK}_ID                            
                            WHERE {$where}
                            ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$arrFunction[$this->_i]}&id={$row['CPM_ID']}&s={$row['CPM_TRAN_STATUS']}&i={$this->_i}&flg={$row['CPM_TRAN_FLAG']}&info={$row['CPM_TRAN_INFO']}";
                $url = "main.php?param=" . base64_encode($base64);
                $row['CPM_NO_SPTPD'] = $row['CPM_NO'];
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

    public function grid_data_delete($patdaId, $type, $a, $ket = "") {

        $arr_config = $this->get_config_value($a);
        $dbName = $arr_config['PATDA_DBNAME'];
        $dbHost = $arr_config['PATDA_HOSTPORT'];
        $dbPwd = $arr_config['PATDA_PASSWORD'];
        $dbUser = $arr_config['PATDA_USERNAME'];

        $Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysql_select_db($dbName);

        try {
            $query = "select * from SIMPATDA_GW WHERE id_switching='" . $patdaId . "'";
            $result = mysqli_query($Conn_gw, $query);
            $row = mysqli_fetch_assoc($result);
            $tmp = 0;
            if ($row['payment_flag']) {
                $tmp = 1;
            } else {
                $query = "insert into SIMPATDA_GW_BATAL(id_switching,npwpd,wp_nama,wp_alamat,simpatda_dibayar,simpatda_tahun_pajak,
                    simpatda_bulan_pajak,simpatda_type,simpatda_collectible,sptpd,sspd,payment_flag,isdraft,saved_date,expired_date,payment_paid,
                    payment_ref_number,payment_bank_code,payment_sw_refnum,payment_gw_refnum,payment_sw_id,payment_merchant_code,
                    operator,kode_bayar,op_nama,op_alamat,PAYMENT_SETTLEMENT_DATE,masa_pajak_awal,masa_pajak_akhir,patda_collectible,
                    patda_denda,patda_admin_gw,patda_misc_fee,patda_total_bayar,
                    periode,cicilan,simpatda_denda,simpatda_keterangan, log_keterangan, log_actor )VALUES(
                    '{$row['id_switching']}','{$row['npwpd']}','{$row['wp_nama']}','{$row['wp_alamat']}','{$row['simpatda_dibayar']}',
                    '{$row['simpatda_tahun_pajak']}','{$row['simpatda_bulan_pajak']}','{$row['simpatda_type']}','{$row['simpatda_collectible']}',
                    '{$row['sptpd']}','{$row['sspd']}','{$row['payment_flag']}','{$row['isdraft']}','{$row['saved_date']}','{$row['expired_date']}',
                    '{$row['payment_paid']}','{$row['payment_ref_number']}','{$row['payment_bank_code']}','{$row['payment_sw_refnum']}',
                    '{$row['payment_gw_refnum']}','{$row['payment_sw_id']}','{$row['payment_merchant_code']}','{$row['operator']}',
                    '{$row['kode_bayar']}','{$row['op_nama']}','{$row['op_alamat']}','{$row['PAYMENT_SETTLEMENT_DATE']}','{$row['masa_pajak_awal']}',
                    '{$row['masa_pajak_akhir']}','{$row['patda_collectible']}','{$row['patda_denda']}','{$row['patda_admin_gw']}','{$row['patda_misc_fee']}',
                    '{$row['patda_total_bayar']}','{$row['periode']}','{$row['cicilan']}','{$row['simpatda_denda']}','{$row['simpatda_keterangan']}',
                    '" . mysqli_escape_string($Conn_gw, $ket) . "','{$_SESSION['uname']}')";
                $result = mysqli_query($Conn_gw, $query);
                $query = "delete from SIMPATDA_GW WHERE id_switching='" . $patdaId . "'";
                $result = mysqli_query($Conn_gw, $query);
                $tmp = 0;
            }
            return $tmp;
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    public function grid_data_update($patdaId, $type, $tmp) {
        try {
            if ($tmp == 0) {
                $query = "select CPM_TABLE from PATDA_JENIS_PAJAK WHERE CPM_NO ='" . $type . "'";
                $result = mysqli_query($this->Conn, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $this->type = $row['CPM_TABLE'];
                    $tmp = strtoupper(str_replace(' ', '', $this->type));

                    $query = "update PATDA_" . $tmp . "_DOC_TRANMAIN set CPM_TRAN_STATUS = '2' WHERE CPM_TRAN_" . $tmp . "_ID='" . $patdaId . "' and CPM_TRAN_STATUS='5'";
                    #echo $query;exit;
                    $result = mysqli_query($this->Conn, $query);
                    echo 0;
                }
            } else {
                echo 1;
            }
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
	
	public function export_xls(){
				
        $arr_config = $this->get_config_value('aPatda');

		$dbName = $arr_config['PATDA_DBNAME'];
		$dbHost = $arr_config['PATDA_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_PASSWORD'];
		$dbUser = $arr_config['PATDA_USERNAME'];


		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysql_select_db($dbName, $Conn_gw);
		//print_r($_REQUEST);exit;
		if((isset($_REQUEST['CPM_START_DATE']) && $_REQUEST['CPM_START_DATE'] != "") && (isset($_REQUEST['CPM_END_DATE']) && $_REQUEST['CPM_END_DATE'] != "")){
			$d = explode("/",$_REQUEST['CPM_START_DATE']);
			$start = "{$d[2]}-{$d[1]}-{$d[0]}";
			unset($d);
			
			$d = explode("/",$_REQUEST['CPM_END_DATE']);
			$end = "{$d[2]}-{$d[1]}-{$d[0]}";
			unset($d);
		}
		$where = '';
		$where.= ((isset($_REQUEST['CPM_START_DATE']) && $_REQUEST['CPM_START_DATE'] != "") && (isset($_REQUEST['CPM_END_DATE']) && $_REQUEST['CPM_END_DATE'] != "")) ? " DATE_FORMAT(log_timestamp,'%Y-%m-%d') >= '{$start}' and DATE_FORMAT(log_timestamp,'%Y-%m-%d') <= '{$end}'" : "1=1";
		
		#query select list data
		$query = "SELECT G.id_switching, T.jenis, G.log_keterangan, G.log_timestamp, G.log_actor FROM SIMPATDA_GW_BATAL G
					INNER JOIN SIMPATDA_TYPE T ON G.simpatda_type = T.id
					WHERE {$where} 
					";              
		$result = mysqli_query($Conn_gw, $query);	
		$dataRaw = array();
        
		$a = 1;
	    while ($row = mysqli_fetch_assoc($result)) {
			$dataRaw[$a]['id_switching'] = $row['id_switching'];
			$dataRaw[$a]['jenis'] = $row['jenis'];
			$dataRaw[$a]['log_keterangan'] = $row['log_keterangan'];
			$dataRaw[$a]['log_timestamp'] = $row['log_timestamp'];
			$dataRaw[$a]['log_actor'] = $row['log_actor'];
			$a++;
		}
 		
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
                ->setCellValue('C1', 'REKAP PEMBATALAN PAJAK')
                ->setCellValue('C2', 'PER '.$_REQUEST['CPM_START_DATE'].' s/d '.$_REQUEST['CPM_END_DATE']);
        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A4', 'No.')
                ->setCellValue('B4', 'Pajak')
                ->setCellValue('C4', 'Keterangan')
                ->setCellValue('D4', 'Waktu')
                ->setCellValue('E4', 'Petugas');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 5;

	    for($x=1;$x<$a;$x++){			
		    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $x);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $dataRaw[$x]['jenis']);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $dataRaw[$x]['log_keterangan']);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $dataRaw[$x]['log_timestamp']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $dataRaw[$x]['log_actor']);
			$row++;
	    }	
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Pembatalan Pajak');
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

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');

        header('Content-Disposition: attachment;filename="rekap_pembatalan_' . date('yymdhmi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');		
		
	}

}

?>
