<?php

class TransaksiSurveillance extends Pajak {
	
	private $connSv;
	
	public function __construct(){
		parent::__construct();
		
		$this->getConnection();
	}
	
	private function getConnection() {
		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_TB_DBNAME'];
		$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
		$dbTable = $arr_config['PATDA_TB_TABLE'];
		$dbUser = $arr_config['PATDA_TB_USERNAME'];
	
		$this->connSv = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName);
	}
	
	public function grid_data_pembanding_detail() {
		try {
			$where = " 1=1 ";
			
			$where.= (isset($_REQUEST['CPM_SURVEILLANCE']) && !empty($_REQUEST['CPM_SURVEILLANCE'])) ? " AND A.TransactionSource = \"{$_REQUEST['CPM_SURVEILLANCE']}\" " : "";	
			$where.= (isset($_REQUEST['CPM_JENIS']) && !empty($_REQUEST['CPM_JENIS'])) ? " AND A.TaxType = \"{$_REQUEST['CPM_JENIS']}\" " : "";
			$where.= (isset($_REQUEST['CPM_NPWPD']) && !empty($_REQUEST['CPM_NPWPD'])) ? " AND A.NPWPD = \"{$_REQUEST['CPM_NPWPD']}\" " : "";
			$where.= (isset($_REQUEST['CPM_NOP']) && !empty($_REQUEST['CPM_NOP'])) ? " AND A.NOP = \"{$_REQUEST['CPM_NOP']}\" " : "";
			$where.= (isset($_REQUEST['NO_TRAN']) && !empty($_REQUEST['NO_TRAN'])) ? " AND A.TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
			
			if($_REQUEST['i'] == 1){
				$where .= "  AND B.Status = '0' ";
			}elseif($_REQUEST['i'] == 2){
				$where .= "  AND B.Status = '1' ";
			}
						
            $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " 
			AND A.TransactionDate between 
			STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
			STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";

			$query = "select *
                        FROM TRANSACTION A INNER JOIN TRANSACTION_ATR_RESTORAN B ON A.TransactionNumber = B.TransactionNumber
                        WHERE {$where} ";
            $result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));            
            $recordCount = mysqli_num_rows($result);            
            
            $q = $query .=" ORDER BY A.TransactionDate DESC LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));
            $rows = array();
            
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];                
            while ($data = mysqli_fetch_assoc($result)) {
                $dataTapbox = array();
                $dataTapbox = $data;
                $dataTapbox = array_merge($dataTapbox, array("NO" => ++$no));                
                $dataTapbox['TaxType'] = $this->arr_pajak[(int) $dataTapbox['TaxType']];
                $dataTapbox['TransactionAmount'] = number_format($data['TransactionAmount'],2);
                $dataTapbox['TaxAmount'] = number_format($data['TaxAmount'],2);
                $dataTapbox['JumlahMeja'] = number_format($data['JumlahMeja'],2);
                $dataTapbox['JumlahKursi'] = number_format($data['JumlahKursi'],2);
                $dataTapbox['JumlahPengunjung'] = number_format($data['JumlahPengunjung'],2);
                $json = base64_encode(json_encode($dataTapbox));
                $dataTapbox['Act'] = "<a href='javascript:void(0)' onclick='javascript:viewData(\"{$json}\")'><i class=\"fa fa-search fa-fw\"></i></a>";
                $rows[] = $dataTapbox;
            }
            #query select list data

            $jTableResult = array();
            $jTableResult['q'] = $q;
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = $recordCount;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysqli_close($this->connSv);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
    
	//--- Add : function | updateRecord, only 1 field (TransactionAmount) [OST - 17/05/2018]  
	public function updateRecord($POST){
		extract($POST);
		
		$TransactionAmount = str_replace(",","",$TransactionAmount);
		$TaxAmount = str_replace(",","",$TaxAmount);
		
		try {
			if($this->getRecordTran($TransactionNumber,"update")){
				// delete record
				$query = "UPDATE TRANSACTION SET 
					TransactionAmount = '{$TransactionAmount}',
					TaxAmount = '{$TaxAmount}'
					WHERE TransactionNumber = '{$TransactionNumber}' ";	
				$result = mysqli_query($this->connSv, $query) or die($this->connSv);     												
				
				if($result){
					$query = "UPDATE TRANSACTION_ATR_RESTORAN SET 
					TransactionAmount = '{$TransactionAmount}',
					TaxAmount = '{$TaxAmount}'
					WHERE TransactionNumber = '{$TransactionNumber}' ";	
					$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));
				}
				
				//Return result to jTable
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				print json_encode($jTableResult);				
			}
		} catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);		
		}						
	}
        
	//--- Add : function | deleteRecord [OST - 17/05/2018]     
	public function deleteRecord($TransactionNumber){		
		try {
			if($this->getRecordTran($TransactionNumber,"delete")){
				// delete record
				$query = "DELETE FROM TRANSACTION WHERE TransactionNumber = '{$TransactionNumber}' ";		
				$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));     												
				
				if($result){
					$query = "DELETE FROM TRANSACTION_ATR_RESTORAN WHERE TransactionNumber = '{$TransactionNumber}' ";		
					$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));
				}
				
				//Return result to jTable
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				print json_encode($jTableResult);				
			}
		} catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);		
		}						
	}
	
	public function getRecordTran($TransactionNumber,$action){
		$values = "";
		$resultLog = false;
		
		$query = "SELECT * FROM TRANSACTION WHERE TransactionNumber = '{$TransactionNumber}' ";
		$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));     
		
		return $result; 
	}
	
	private $arr_surveillance = array(
		1 => 'Tapbox',
		2 => 'Cash Register',
		3 => 'File',
		4 => 'Manual',
	);
			
    public function filtering_pembanding_detail($id) {
		$DIR = 'PATDA-V1';
		
		$NPWPD = (isset($_SESSION['NPWPD']) && $_SESSION['NPWPD'] != "") ? $_SESSION['NPWPD'] : '';
		
		$opt_jenis_pajak = "";
		$opt_jenis_pajak_input = "";
		foreach ($this->arr_pajak as $x => $y) {
			if($x == 8){//restoran
				$opt_jenis_pajak .= "<option value='{$x}'>{$y}</option>";
				$opt_jenis_pajak_input .= "<option value='{$x}'>{$y}</option>";
			}
		}
		
		$url = "view/{$DIR}/laporan-harian/restoran/svc-download-tapbox.xls.php";
        $html = "<div class=\"filtering\">
                    <form>
						<input type=\"hidden\" id=\"HIDDEN-{$id}\" a=\"{$this->_a}\" ><!-- manual -->
						<input type=\"hidden\" id=\"CPM_SURVEILLANCE-{$id}\" name=\"CPM_SURVEILLANCE-{$id}\" value=\"4\" ><!-- manual -->
						<table border=\"0\">
							<tr>
                                <td style='background:transparent;padding:2px;width:200px'>
									Jenis Pajak :<br/>
									<select id=\"CPM_JENIS-{$id}\" name=\"CPM_JENIS-{$id}\" style=\"width:185px\">{$opt_jenis_pajak}</select>
								</td>
								<td style='background:transparent;padding:2px;width:180px'>
									NPWPD :<br/>
									<input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" placeholder=\"NPWPD\" value=\"{$NPWPD}\" ".(empty($NPWPD)?'':'readonly').">
                                </td>
                                <td style='background:transparent;padding:2px;width:180px'>
									NOP :<br/>
									<input type=\"text\" name=\"CPM_NOP-{$id}\" id=\"CPM_NOP-{$id}\" placeholder=\"NOP\">
                                </td>
                                <td style='background:transparent;padding:2px;width:180px'>
									No. Transaksi :<br/>
									<input type=\"text\" name=\"NO_TRAN-{$id}\" id=\"NO_TRAN-{$id}\" placeholder=\"No. Transaksi\">
                                </td>
                            </tr>
                            <tr>
                                <td style='background:transparent;padding:2px' colspan=\"2\">
									Tanggal Transaksi :<br/>
									<input type=\"text\" name=\"TRAN_DATE1-{$id}\" id=\"TRAN_DATE1-{$id}\" readonly onclick=\"javascript:openDate(this);\" placeholder=\"Tanggal awal\"><input type=\"button\" value=\"x\" onclick=\"javascriopt:$('#TRAN_DATE1-{$id}').val('');\">
                                    s.d
									<input type=\"text\" style='width:143px' name=\"TRAN_DATE2-{$id}\" readonly id=\"TRAN_DATE2-{$id}\" onclick=\"javascript:openDate(this);\" placeholder=\"Tanggal akhir\"><input type=\"button\" value=\"x\" onclick=\"javascriopt:$('#TRAN_DATE2-{$id}').val('');\">
								</td>
                                <td style='background:transparent;padding:2px'></td>
                                <td style='background:transparent;padding:2px'><br/>
                                    <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                                    <button type=\"button\" id=\"cetak-{$id}\" onclick=\"javascript:download_excel('{$id}','$url');\">Cetak Excel</button>
									<span id=\"loadlink-{$id}\" style=\"font-size: 10px; display: none;\">Loading...</span>
                                </td>    
                            </tr>
                        </table>
                    </form>
                </div>
				
				<!--------------- Add : [OST - 25/05/2018]	------------->
				
				<table width='100%' border='0'>
					<tr>
						<td style='background:transparent;padding:2px' align='left'>
							<strong id='titleSurveillance'>Data Input Manual</strong>
						</td>    
						<td style='background:transparent;padding:2px' align='right'>
							
						</td>    
					</tr>
				</table>
				
				<div id=\"id{$this->_i}\" class=\"modal\">  
				  <form class=\"modal-content animate\" id=\"tambahTransaksi\" autocomplete=\"off\">
					<br/>
					<center><b>DETAIL TRANSAKSI</b></center>
					<div class=\"contentcontainer\">
						<table border=\"0\" width=\"500\">
							<tr>
								<td colspan=\"2\" style=\"background-color:#E9E9E9;font-weight:bold\" align='center'>Indentitas WP dan OP</td>
							</tr>
							<tr>
								<td width=\"120\">NPWPD</td>
								<td width=\"380\">: <span id=\"label-NPWPD-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td width=\"120\">Nama Wajib Pajak</td>
								<td width=\"380\">: <span id=\"label-NamaWP-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td width=\"120\">NOP</td>
								<td width=\"380\">: <span id=\"label-NOP-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td width=\"120\">Nama Objek Pajak</td>
								<td width=\"380\">: <span id=\"label-NamaOP-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td width=\"120\">Rekening Pajak</td>
								<td width=\"380\">: <span id=\"label-Golongan-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td colspan=\"2\" style=\"background-color:#E9E9E9;font-weight:bold\" align='center'>Transaksi</td>
							</tr>
							<tr>
								<td width=\"120\">Jumlah Meja</td>
								<td width=\"380\">: <span id=\"label-JumlahMeja-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td width=\"120\">Jumlah Kursi</td>
								<td width=\"380\">: <span id=\"label-JumlahKursi-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td width=\"120\">Jumlah Pengunjung</td>
								<td width=\"380\">: <span id=\"label-JumlahPengunjung-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td width=\"120\">Nominal Transaksi</td>
								<td width=\"380\">: <span id=\"label-TransactionAmount-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td width=\"120\">Tarif Pajak</td>
								<td width=\"380\">: <span id=\"label-TarifPajak-{$this->_i}\"></span></td>
							</tr>
							<tr>
								<td width=\"120\">Nominal Pajak</td>
								<td width=\"380\">: <span id=\"label-TaxAmount-{$this->_i}\"></span></td>
							</tr>
						</table>
						<br/>
					  <p align=\"right\">
						<button type=\"button\" onclick=\"document.getElementById('id{$this->_i}').style.display='none'\">Close</button>
					  </p>
					</div>					
				  </form>
				</div>
                ";
        return $html;
    }
    
	// Modify : Fields name "Total Transaksi" to "Transaksi" [OST - 11/05/2018]	
	// Add : parameter dateStart, dateEnd, noTran on getResume() function [OST - 14/05/2018] 
    public function grid_table_pembanding_detail() {
        $DIR = "PATDA-V1";
        $modul = "laporan-harian/restoran";
        $html = "
				<link href=\"inc/{$DIR}/datetimepicker/jquery.datetimepicker.css\" rel=\"stylesheet\" type=\"text/css\" />
				<link href=\"inc/{$DIR}/addRecordTransaksi.css\" rel=\"stylesheet\" type=\"text/css\" />
				<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <script src=\"inc/{$DIR}/datetimepicker/php-date-formatter.min.js\" type=\"text/javascript\"></script>
                <script src=\"inc/{$DIR}/datetimepicker/jquery.mousewheel.js\" type=\"text/javascript\"></script>
                <script src=\"inc/{$DIR}/datetimepicker/jquery.datetimepicker.js\" type=\"text/javascript\"></script>
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
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=list&a={$this->_a}&m={$this->_m}&i={$this->_i}',
                                updateAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=update&a={$this->_a}&m={$this->_m}&i={$this->_i}',
                                deleteAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=delete&a={$this->_a}&m={$this->_m}&i={$this->_i}',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%', edit:false},
                                NPWPD: {title: 'NPWPD',width: '5%', edit:false},
                                NOP: {title: 'NOP',width: '5%', edit:false},
                                TransactionNumber: {title: 'No. Transaksi',width: '7%',key:true},
                                TransactionDate: {title: 'Tanggal Transaksi',width: '10%', edit:false},
                                TransactionAmount: {title: 'Transaksi',width: '7%'},
                                TaxAmount: {title: 'Pajak',width: '7%'},
                                Act: {title: '',width: '1%',edit:false,sorting:false}
                            }
                        });     
                        
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            var parameter = {
                                CPM_JENIS : $('#CPM_JENIS-{$this->_i}').val(),
                                NO_TRAN : $('#NO_TRAN-{$this->_i}').val(),
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NOP : $('#CPM_NOP-{$this->_i}').val(),
                                TRAN_DATE1 : $('#TRAN_DATE1-{$this->_i}').val(),
                                TRAN_DATE2 : $('#TRAN_DATE2-{$this->_i}').val(),
                                CPM_SURVEILLANCE : $('#CPM_SURVEILLANCE-{$this->_i}').val()
                            };
                            $('#tapboxPajak-{$this->_i}').jtable('load', parameter);
                            get_resume(parameter, '{$this->_i}');
                            
                        });
                        $('#cari-{$this->_i}').click();
					});
					
					function viewData(json){
						var a = base64_decode(json);
						var data = JSON.parse(a);
						
						document.getElementById('id{$this->_i}').style.display='block';  
						
						$('#label-NPWPD-{$this->_i}').html(data.NPWPD);
						$('#label-NOP-{$this->_i}').html(data.NOP);
						$('#label-Golongan-{$this->_i}').html(data.Golongan);
						$('#label-NamaWP-{$this->_i}').html(data.NamaWP);
						$('#label-NamaOP-{$this->_i}').html(data.NamaOP);
						$('#label-JenisKamar-{$this->_i}').html(data.JenisKamar);
						
						$('#label-TarifPajak-{$this->_i}').html(data.TarifPajak+' %');
						$('#label-JumlahMeja-{$this->_i}').html(data.JumlahMeja+' buah');
						$('#label-JumlahKursi-{$this->_i}').html(data.JumlahKursi+' buah');
						$('#label-JumlahPengunjung-{$this->_i}').html(data.JumlahPengunjung+' orang');
						$('#label-TransactionAmount-{$this->_i}').html(data.TransactionAmount);
						$('#label-TaxAmount-{$this->_i}').html(data.TaxAmount);
                    }
			        
                </script>";
        echo $html;
    }
    
    function download_tapbox_xls() {
		
		$limit = 2000;
        $where = "1=1 ";
        
        $where.= (isset($_REQUEST['CPM_SURVEILLANCE']) && !empty($_REQUEST['CPM_SURVEILLANCE'])) ? " AND A.TransactionSource = \"{$_REQUEST['CPM_SURVEILLANCE']}\" " : "";	
		$where.= (isset($_REQUEST['CPM_JENIS']) && !empty($_REQUEST['CPM_JENIS'])) ? " AND A.TaxType = \"{$_REQUEST['CPM_JENIS']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NPWPD']) && !empty($_REQUEST['CPM_NPWPD'])) ? " AND A.NPWPD = \"{$_REQUEST['CPM_NPWPD']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NOP']) && !empty($_REQUEST['CPM_NOP'])) ? " AND A.NOP = \"{$_REQUEST['CPM_NOP']}\" " : "";
		$where.= (isset($_REQUEST['NO_TRAN']) && !empty($_REQUEST['NO_TRAN'])) ? " AND A.TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";

        $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " 
		AND A.TransactionDate between 
		STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
		STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";


		if(isset($_REQUEST['count'])){
			$query = "select 
			COUNT(*) AS RecordCount
					FROM TRANSACTION A INNER JOIN TRANSACTION_ATR_RESTORAN B ON A.TransactionNumber = B.TransactionNumber
					WHERE {$where}";	
			#echo $query;exit;
			$result = mysqli_query($this->connSv, $query);
			$data = mysqli_fetch_assoc($result);
			$arr['total_row'] = $data['RecordCount'];
			$arr['limit'] = $limit;
			echo $this->Json->encode($arr);exit;
		}	

		$p = $_REQUEST['page'];
		$total = $limit;
        if ($p == 'all') {
            $offset = 0;
        } else {	   
            $offset = ($p-1) * $total;
        }

		$query = "select * FROM TRANSACTION A INNER JOIN TRANSACTION_ATR_RESTORAN B ON A.TransactionNumber = B.TransactionNumber
					WHERE {$where} ORDER BY TransactionDate DESC LIMIT {$offset}, {$total}";
		
		$res = mysqli_query($this->connSv, $query);
		#echo mysqli_num_rows($res);exit;
			
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
                ->setCellValue('B1', 'Jenis Pajak')
                ->setCellValue('C1', 'NPWPD')
                ->setCellValue('D1', 'NOP')
                ->setCellValue('E1', 'No. Transaksi')
                ->setCellValue('F1', 'Tanggal Transaksi')
                ->setCellValue('G1', 'Transaksi')
                ->setCellValue('H1', 'Pajak');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);
		$no = $offset+1;
        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['TransactionAmount'] = (int) str_replace(",","",$rowData['TransactionAmount']);
			$rowData['TransactionNumber'] = preg_replace("/[^A-Za-z0-9]/","",$rowData['TransactionNumber']);
			$jenis_pajak = $this->arr_pajak[(int) $rowData['TaxType']];
			
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $no);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $jenis_pajak);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['NOP'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $rowData['TransactionNumber'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['TransactionDate']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['TransactionAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['TaxAmount']);
            $row++;
			$no++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Transaksi Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "H"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }

		ob_clean();
		header('Content-Type: application/vnd.ms-excel');
        if ($p != 'all')
            header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '-part-' . $p . '.xls"');
        else
            header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');

        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    
	// Add : parameter dateStart, dateEnd, noTran [OST - 14/05/2018]    
	public function get_resume(){
		
		$dateStart = $_REQUEST['TRAN_DATE1'];
		$dateEnd = $_REQUEST['TRAN_DATE2'];
		
		$where = " 1=1 ";
		$thn = date('Y');
		$bln = date('m');
		$tgl = date('d');
		
		$where.= (isset($_REQUEST['CPM_SURVEILLANCE']) && !empty($_REQUEST['CPM_SURVEILLANCE'])) ? " AND A.TransactionSource = \"{$_REQUEST['CPM_SURVEILLANCE']}\" " : "";	
		$where.= (isset($_REQUEST['CPM_JENIS']) && !empty($_REQUEST['CPM_JENIS'])) ? " AND A.TaxType = \"{$_REQUEST['CPM_JENIS']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NPWPD']) && !empty($_REQUEST['CPM_NPWPD'])) ? " AND A.NPWPD = \"{$_REQUEST['CPM_NPWPD']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NOP']) && !empty($_REQUEST['CPM_NOP'])) ? " AND A.NOP = \"{$_REQUEST['CPM_NOP']}\" " : "";
		$where.= (isset($_REQUEST['NO_TRAN']) && !empty($_REQUEST['NO_TRAN'])) ? " AND A.TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
		
		
		if($_REQUEST['i'] == 1){
			$where .= "  AND B.Status = '0' ";
		}elseif($_REQUEST['i'] == 2){
			$where .= "  AND B.Status = '1' ";
		}
			
		$query = "select SUM(A.TransactionAmount) as total 
					FROM TRANSACTION A INNER JOIN TRANSACTION_ATR_RESTORAN B ON A.TransactionNumber = B.TransactionNumber 
					WHERE {$where}
					AND MONTH(A.TransactionDate) = '{$bln}' 
					AND YEAR(A.TransactionDate) = '{$thn}'";
		$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));            
		$row_bln = mysqli_fetch_assoc($result);
		
		$bulan_ini = isset($row_bln['total'])? $row_bln['total'] : 0;
		
		//--- Add & modify : current day to previous day [OST - 24/04/2018] 
		$thnKemarin = date("Y", (time() - 60 * 60 * 24));
		$blnKemarin = date("m", (time() - 60 * 60 * 24));
		$tglKemarin = date("d", (time() - 60 * 60 * 24));
		//--------
		
		$query = "select SUM(A.TransactionAmount) as total 
					FROM TRANSACTION A INNER JOIN TRANSACTION_ATR_RESTORAN B ON A.TransactionNumber = B.TransactionNumber 
					WHERE {$where}
					AND MONTH(A.TransactionDate) = '{$blnKemarin}' 
					AND YEAR(A.TransactionDate) = '{$thnKemarin}'
					AND DAY(A.TransactionDate) = '{$tglKemarin}'
					";
		$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));            
		$row_tgl = mysqli_fetch_assoc($result);
		$hari_ini = isset($row_tgl['total'])? $row_tgl['total'] : 0;	
			
			
		//--------- Add : Total Amount [OST - 14/05/2018]    
		$totalAmount = 0;	
		$where.= (isset($dateStart) && $dateStart != "") ? " 
		AND A.TransactionDate between 
		STR_TO_DATE(CONCAT(\"{$dateStart}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
		STR_TO_DATE(CONCAT(\"{$dateEnd}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";

		$query = "select sum(A.TransactionAmount) as TOTAL_AMOUNT
					FROM TRANSACTION A INNER JOIN TRANSACTION_ATR_RESTORAN B ON A.TransactionNumber = B.TransactionNumber 
					WHERE {$where} ";
		
		$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));            
		if($result){
			while($obj = mysqli_fetch_object($result)){
				$totalAmount = $obj->TOTAL_AMOUNT;
			}
		}
		mysqli_free_result($result);	
		//--------
			
		ob_clean();
		
		echo json_encode(array(
			'transaksi_kemarin' => 'Rp. '.number_format($hari_ini,0),
			'transaksi_bulan_ini' => 'Rp. '.number_format($bulan_ini,0),
			'total_transaksi' => 'Rp. '.number_format($totalAmount,0)
		));
	}
}
?>

