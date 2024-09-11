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
            
            $where.= (isset($_REQUEST['CPM_SURVEILLANCE']) && !empty($_REQUEST['CPM_SURVEILLANCE'])) ? " AND TransactionSource = \"{$_REQUEST['CPM_SURVEILLANCE']}\" " : "";	
			$where.= (isset($_REQUEST['CPM_JENIS']) && !empty($_REQUEST['CPM_JENIS'])) ? " AND TaxType = \"{$_REQUEST['CPM_JENIS']}\" " : "";
			$where.= (isset($_REQUEST['CPM_NPWPD']) && !empty($_REQUEST['CPM_NPWPD'])) ? " AND NPWPD = \"{$_REQUEST['CPM_NPWPD']}\" " : "";
			$where.= (isset($_REQUEST['CPM_NOP']) && !empty($_REQUEST['CPM_NOP'])) ? " AND NOP = \"{$_REQUEST['CPM_NOP']}\" " : "";
			$where.= (isset($_REQUEST['NO_TRAN']) && !empty($_REQUEST['NO_TRAN'])) ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
							
            $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " 
			AND TransactionDate between 
			STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
			STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";

            $query = "select *
                        FROM TRANSACTION
                        WHERE {$where} ";
            $result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));            
            $recordCount = mysqli_num_rows($result);            
            
            $q = $query .=" ORDER BY TransactionDate DESC LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));
            $rows = array();
            
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];                
            while ($data = mysqli_fetch_assoc($result)) {
                $dataTapbox = array();
                $dataTapbox = $data;
                $dataTapbox = array_merge($dataTapbox, array("NO" => ++$no));                
                $dataTapbox['TaxType'] = $this->arr_pajak[(int) $dataTapbox['TaxType']];
                $dataTapbox['TransactionAmount'] = empty($data['TransactionAmount'])? 0 : number_format($data['TransactionAmount'],2);
                $dataTapbox['TaxAmount'] = empty($data['TaxAmount'])? 0 : number_format($data['TaxAmount'],2);
                
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
    
	//--- Add : function | insertRecord [OST - 25/05/2018]  	
	public function insertRecord($POST){
		
		extract($POST);
		
		$TransactionDescription = 'manual input';
		try {
			// insert record
			$masaPajak = substr($tranDate,4,2).substr($tranDate,0,4);
			$query = sprintf("INSERT INTO TRANSACTION
					(TransactionNumber, TransactionDate, TransactionAmount, TransactionSource, TransactionDescription, 
					TaxAmount, TaxInfo, TaxType, DeviceId, NPWPD, NOP, NotAdmitReason)
					VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
					$TransactionNumber, $TransactionDate, $TransactionAmount, $TransactionSource, $TransactionDescription, 
					$TaxAmount, $TaxInfo, $TaxType, $DeviceId, $NPWPD, $NOP, ''
					);
					
			$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));     
			
			if($result){
				if($this->getRecordTran($TransactionNumber,"insert")){
					//Return result to jTable
					$jTableResult = array();
					$jTableResult['Result'] = "Simpan berhasil";
					print json_encode($jTableResult);								
				}			
			}			
										
		} catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);		
		}						
	}
	
	
	//--- Add : function | updateRecord, only 1 field (TransactionAmount) [OST - 17/05/2018]  
	public function updateRecord($post){
		$TransactionNumber = $post['TransactionNumber'];
		$TransactionAmount = str_replace(",", "", $post['TransactionAmount']);
		$TaxAmount = str_replace(",", "", $post['TaxAmount']);
		
		try {
			if($tran = $this->getRecordTran($TransactionNumber,"update")){
				// delete record
				$query = "UPDATE TRANSACTION SET 
				TransactionAmount = '{$TransactionAmount}', 
				TaxAmount = '{$TaxAmount}'
				WHERE TransactionNumber = '{$TransactionNumber}' ";	
				$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));     												
				
				
				if($result){
					if($tran->TaxType == '3'){
						$query = "UPDATE TRANSACTION_ATR_HOTEL SET 
						TransactionAmount = '{$TransactionAmount}',
						TaxAmount = '{$TaxAmount}'
						WHERE TransactionNumber = '{$TransactionNumber}' ";	
						$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));
					
					}elseif($tran->TaxType == '8'){
						$query = "UPDATE TRANSACTION_ATR_RESTORAN SET 
						TransactionAmount = '{$TransactionAmount}',
						TaxAmount = '{$TaxAmount}'
						WHERE TransactionNumber = '{$TransactionNumber}' ";	
						$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));
					}
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
		$obj = false;
		
		if($result){
			if($obj = mysqli_fetch_object($result)){
				$values = "('".$obj->TransactionNumber."',".
				"'".$obj->TransactionDate."',".
				"'".$obj->TransactionAmount."',".
				"'".$obj->TransactionSource."',".
				"'".$obj->TransactionDescription."',".
				"'".$obj->TaxAmount."',".
				"'".$obj->TaxInfo."',".
				"'".$obj->TaxType."',".
				"'".$obj->DeviceId."',".
				"'".$obj->NPWPD."',".
				"'".$obj->NOP."',".
				"'".$obj->NotAdmitReason."',".
				"'".$obj->ServerTimeStamp."',".
				"'{$action}',".
				"'".date('Y-m-d H:i:s')."',".
				"'".$_SESSION['uname']."')";
			}
			
			//$resultLog = $this->addLog($values);
		}		
		
		return $obj; 
	}
		
	static public function addLog($values){
		$result = false;
		// insert to log table
		$query = "INSERT INTO TRANSACTION
				(TransactionNumber, TransactionDate, TransactionAmount, TransactionSource, TransactionDescription, 
				TaxAmount, TaxInfo, TaxType, DeviceId, NPWPD, NOP, NotAdmitReason, ServerTimeStamp,
				action, timestamp, user)
				VALUES ". $values;
		$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));
		
		return $result;					
	}
	
	private $arr_surveillance = array(
		1 => 'Tapbox',
		2 => 'Cash Register',
		3 => 'File',
		4 => 'Input Manual',
		5 => 'Web Service',
	);
			
    public function filtering_pembanding_detail($id) {
		$DIR = 'PATDA-V1';
		
		$NPWPD = (isset($_SESSION['NPWPD']) && $_SESSION['NPWPD'] != "") ? $_SESSION['NPWPD'] : '';
		
		$opt_jenis_pajak = "<option value=\"\">Semua</option>";
		$opt_jenis_pajak_input = "<option value=\"\">Pilih Jenis Pajak</option>";
		foreach ($this->arr_pajak as $x => $y) {
			$opt_jenis_pajak .= "<option value='{$x}'>{$y}</option>";
			$opt_jenis_pajak_input .= "<option value='{$x}'>{$y}</option>";
		}
		
		$opt_surveillance = "<option value=\"\">Semua</option>";
		foreach ($this->arr_surveillance as $x => $y) {
			$opt_surveillance .= "<option value='{$x}'>{$y}</option>";
		}
		
        $url = "view/{$DIR}/monitoring/trans-surveillance/svc-download-tapbox.xls.php";
        $html = "<div class=\"filtering\">
                    <form>
						<input type=\"hidden\" id=\"HIDDEN-{$id}\" a=\"{$this->_a}\" ><!-- manual -->
						<table border=\"0\">
							<tr>
                                <td style='background:transparent;padding:2px;width:200px'>
									Jenis Surveillance :<br/>
									<select id=\"CPM_SURVEILLANCE-{$id}\" name=\"CPM_SURVEILLANCE-{$id}\" style=\"width:175px\">{$opt_surveillance}</select>
								</td>
								<td style='background:transparent;padding:2px;width:180px'></td>
                                <td style='background:transparent;padding:2px;width:180px'></td>
                                <td style='background:transparent;padding:2px;width:180px'></td>
                            </tr>
                            
                        	<tr>
                                <td style='background:transparent;padding:2px;width:200px'>
									Jenis Pajak :<br/>
									<select id=\"CPM_JENIS-{$id}\" name=\"CPM_JENIS-{$id}\">{$opt_jenis_pajak}</select>
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
							<strong id='titleSurveillance'>Cash Register</strong>
						</td>    
						<td style='background:transparent;padding:2px' align='right'>
							<!--<button type=\"button\" id=\"tambah-{$id}\" onclick=\"javascript:openAddRecordForm()\">Tambah Transaksi</button>-->
						</td>    
					</tr>
				</table>
                
				<div id=\"id{$this->_i}\" class=\"modal\">  
				  <form class=\"modal-content animate\" id=\"tambahTransaksi\" autocomplete=\"off\">
					<br/>
					<center><b>DETAIL TRANSAKSI</b></center>
					<div class=\"contentcontainer\">
						<div class=\"area\"></div>
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
        $modul = "monitoring/trans-surveillance";
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
                                TaxType: {title: 'Jenis Pajak',width: '10%', edit:false},
                                NPWPD: {title: 'NPWPD',width: '5%', edit:false},
                                NOP: {title: 'NOP',width: '5%', edit:false},
                                TransactionNumber: {title: 'No. Transaksi',width: '7%',key:true},
                                TransactionDate: {title: 'Tanggal Transaksi',width: '10%', edit:false},
                                TransactionAmount: {title: 'Transaksi',width: '7%'},
                                TaxAmount: {title: 'Pajak',width: '7%'},
                                Act: {title: '',width: '1%',edit:false,sorting:false}
                            }
                        });     
                        //$('#tapboxPajak-{$this->_i}').jtable('load');               
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            setTitleSurveillance();
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
                            get_resume(parameter);
                            
                        });
                        $('#cari-{$this->_i}').click();
						
						// ------------- Add : [OST - 25/05/2018] 
																						
						// Get the modal
						var modal = document.getElementById('id01');

						// When the user clicks anywhere outside of the modal, close it
						window.onclick = function(event) {
							if (event.target == modal) {
								modal.style.display = 'none';
							}
						}
											
						$('#TransactionDate').datetimepicker({format: 'Y-m-d h:i:s'});
							
						$('#tambahTransaksi').submit(function(e) {						
							var url = 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=insert'; // the script where you handle the form input.
							$.ajax({
								   type: 'POST',
								   url: url,
								   data: $('#tambahTransaksi').serialize(), // serializes the form's elements.
								   success: function(data)
								   {
									   var response = JSON.parse(data);
									   alert(response.Result);
									   
									   modal.style.display = 'none';
									   $('#cari-{$this->_i}').click();
								   }
								 });
							e.preventDefault(); // avoid to execute the actual submit of the form.							
						});											
						
						$('#CPM_SURVEILLANCE-{$this->_i}').change(function(){
							setTitleSurveillance();
							$('#cari-{$this->_i}').click();
						});
						
                    });
                                        
					function openAddRecordForm(){
						document.getElementById('id01').style.display='block';  
						$('#TransactionNumber').val('');
						$('#TransactionNumber').attr('placeholder','No. Transaksi');
						$('#NPWPD').val('');
						$('#NPWPD').attr('placeholder','NPWPD');
						$('#TransactionDescription').val('');
						$('#TransactionDescription').attr('placeholder','Deskripsi');
						$('#TransactionDate').val('');
						$('#TransactionDate').attr('placeholder','Tanggal Transaksi');
						$('#TransactionAmount').val('');
						$('#TransactionAmount').attr('placeholder','Transaksi');												                  
                    }
                                        
					function openDate(obj) {
						$(obj).datepicker({dateFormat: 'dd-mm-yy'});
						$(obj).datepicker('show');
					}
					
					function setTitleSurveillance(){
						var title = $('#CPM_SURVEILLANCE-{$this->_i} option:selected').text();
						$('#titleSurveillance').html(title)
					}
					
					function viewData(json){
						var a = base64_decode(json);
						var data = JSON.parse(a);
						data.a = '{$this->_a}';
						
						console.log(data);
						var url = 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=detail';
						$.ajax({
						   type: 'POST',
						   url: url,
						   data: data,
						   success: function(data)
						   {
							   
							   document.getElementById('id{$this->_i}').style.display='block';  
							   $('#id{$this->_i} .contentcontainer .area').html(data);
						   }
						 });
								 
						
						
                    }
                    
                </script>";
        echo $html;
    }
    
    function download_tapbox_xls() {
		
		$limit = 2000;
        $where = "1=1 ";
        
        $where.= (isset($_REQUEST['CPM_SURVEILLANCE']) && !empty($_REQUEST['CPM_SURVEILLANCE'])) ? " AND TransactionSource = \"{$_REQUEST['CPM_SURVEILLANCE']}\" " : "";	
		$where.= (isset($_REQUEST['CPM_JENIS']) && !empty($_REQUEST['CPM_JENIS'])) ? " AND TaxType = \"{$_REQUEST['CPM_JENIS']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NPWPD']) && !empty($_REQUEST['CPM_NPWPD'])) ? " AND NPWPD = \"{$_REQUEST['CPM_NPWPD']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NOP']) && !empty($_REQUEST['CPM_NOP'])) ? " AND NOP = \"{$_REQUEST['CPM_NOP']}\" " : "";
		$where.= (isset($_REQUEST['NO_TRAN']) && !empty($_REQUEST['NO_TRAN'])) ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";

        $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " 
		AND TransactionDate between 
		STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
		STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";

		if(isset($_REQUEST['count'])){
			$query = "select 
			COUNT(*) AS RecordCount
					FROM TRANSACTION
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

		$query = "select * FROM TRANSACTION
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
                ->setCellValue('G1', 'Total Pajak');

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
            $row++;
			$no++;
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
		
		$where.= (isset($_REQUEST['CPM_SURVEILLANCE']) && !empty($_REQUEST['CPM_SURVEILLANCE'])) ? " AND TransactionSource = \"{$_REQUEST['CPM_SURVEILLANCE']}\" " : "";	
		$where.= (isset($_REQUEST['CPM_JENIS']) && !empty($_REQUEST['CPM_JENIS'])) ? " AND TaxType = \"{$_REQUEST['CPM_JENIS']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NPWPD']) && !empty($_REQUEST['CPM_NPWPD'])) ? " AND NPWPD = \"{$_REQUEST['CPM_NPWPD']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NOP']) && !empty($_REQUEST['CPM_NOP'])) ? " AND NOP = \"{$_REQUEST['CPM_NOP']}\" " : "";
		$where.= (isset($_REQUEST['NO_TRAN']) && !empty($_REQUEST['NO_TRAN'])) ? " AND TransactionNumber = \"{$_REQUEST['NO_TRAN']}\" " : "";
		
		$query = "select SUM(TransactionAmount) as total 
					FROM TRANSACTION
					WHERE {$where} 
					AND MONTH(TransactionDate) = '{$bln}' 
					AND YEAR(TransactionDate) = '{$thn}'";
		$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));            
		$row_bln = mysqli_fetch_assoc($result);
		
		$bulan_ini = isset($row_bln['total'])? $row_bln['total'] : 0;
		
		//--- Add & modify : current day to previous day [OST - 24/04/2018] 
		$thnKemarin = date("Y", (time() - 60 * 60 * 24));
		$blnKemarin = date("m", (time() - 60 * 60 * 24));
		$tglKemarin = date("d", (time() - 60 * 60 * 24));
		//--------
		
		$query = "select SUM(TransactionAmount) as total 
					FROM TRANSACTION
					WHERE {$where} 
					AND MONTH(TransactionDate) = '{$blnKemarin}' 
					AND YEAR(TransactionDate) = '{$thnKemarin}'
					AND DAY(TransactionDate) = '{$tglKemarin}'
					";
		$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));            
		$row_tgl = mysqli_fetch_assoc($result);
		$hari_ini = isset($row_tgl['total'])? $row_tgl['total'] : 0;	
			
			
		//--------- Add : Total Amount [OST - 14/05/2018]    
		$totalAmount = 0;	
		$where.= (isset($dateStart) && $dateStart != "") ? " 
		AND TransactionDate between 
		STR_TO_DATE(CONCAT(\"{$dateStart}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
		STR_TO_DATE(CONCAT(\"{$dateEnd}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";

		$query = "select sum(TransactionAmount) as TOTAL_AMOUNT
					FROM TRANSACTION
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
	
	public function getDetail(){
		
		$where = (isset($_REQUEST['TransactionNumber']) && !empty($_REQUEST['TransactionNumber'])) ? " A.TransactionNumber = \"{$_REQUEST['TransactionNumber']}\" " : "";
		$query = "select * FROM TRANSACTION A WHERE {$where} ";

		if($_REQUEST['TransactionSource'] == 4){ //manual input
			if($_REQUEST['TaxType'] == 'Hotel'){
				$query = "select *
					FROM TRANSACTION A INNER JOIN TRANSACTION_ATR_HOTEL B ON A.TransactionNumber = B.TransactionNumber
					WHERE {$where} ";
			}elseif($_REQUEST['TaxType'] == 'Restoran'){
				$query = "select *
					FROM TRANSACTION A INNER JOIN TRANSACTION_ATR_RESTORAN B ON A.TransactionNumber = B.TransactionNumber
					WHERE {$where} ";
			}
		}
		
		$result = mysqli_query($this->connSv, $query) or die(mysqli_error($this->connSv));            
		$data = mysqli_fetch_object($result);
		
		$html = "<table border=\"0\" width=\"500\">
			<tr>
				<td colspan=\"2\" style=\"background-color:#E9E9E9;font-weight:bold\" align='center'>Indentitas WP dan OP</td>
			</tr>";
		if($_REQUEST['TransactionSource'] == 1){ //tapbox
			$html.="
			<tr>
				<td width=\"120\">Device ID</td>
				<td width=\"380\">: {$data->DeviceId}</td>
			</tr>";
		}
		$html.="
			<tr>
				<td width=\"120\">NPWPD</td>
				<td width=\"380\">: {$data->NPWPD}</td>
			</tr>
			<tr>
				<td width=\"120\">NOP</td>
				<td width=\"380\">: {$data->NOP}</td>
			</tr>
			<tr>
				<td colspan=\"2\" style=\"background-color:#E9E9E9;font-weight:bold\" align='center'>Transaksi</td>
			</tr>
			<tr>
				<td width=\"120\">Nominal Transaksi</td>
				<td width=\"380\">: ".(empty($data->TransactionAmount)? 0 : number_format($data->TransactionAmount,2))."</td>
			</tr>
			<tr>
				<td width=\"120\">Nominal Pajak</td>
				<td width=\"380\">: ".(empty($data->TaxAmount)? 0 :number_format($data->TaxAmount,2))."</td>
			</tr>
			<tr>
				<td width=\"120\">Deskripsi</td>
				<td width=\"380\">: ".$data->TransactionDescription."</td>
			</tr>
		</table>";
		
		if($_REQUEST['TransactionSource'] == 4){
			if($_REQUEST['TaxType'] == 'Hotel'){
				$html = "<table border=\"0\" width=\"500\">
							<tr>
								<td colspan=\"2\" style=\"background-color:#E9E9E9;font-weight:bold\" align='center'>Indentitas WP dan OP</td>
							</tr>
							<tr>
								<td width=\"120\">NPWPD</td>
								<td width=\"380\">: {$data->NPWPD}</td>
							</tr>
							<tr>
								<td width=\"120\">Nama Wajib Pajak</td>
								<td width=\"380\">: {$data->NamaWP}</td>
							</tr>
							<tr>
								<td width=\"120\">NOP</td>
								<td width=\"380\">: {$data->NOP}</td>
							</tr>
							<tr>
								<td width=\"120\">Nama Objek Pajak</td>
								<td width=\"380\">: {$data->NamaOP}</td>
							</tr>
							<tr>
								<td width=\"120\">Rekening Pajak</td>
								<td width=\"380\">: {$data->Golongan}</td>
							</tr>
							<tr>
								<td colspan=\"2\" style=\"background-color:#E9E9E9;font-weight:bold\" align='center'>Transaksi</td>
							</tr>
							<tr>
								<td width=\"120\">Jenis Kamar</td>
								<td width=\"380\">: {$data->JenisKamar}</td>
							</tr>
							<tr>
								<td width=\"120\">Tarif Kamar</td>
								<td width=\"380\">: ".number_format($data->TarifKamar,2)."</td>
							</tr>
							<tr>
								<td width=\"120\">Jumlah Kamar</td>
								<td width=\"380\">: ".number_format($data->JumlahKamar,2)."</td>
							</tr>
							<tr>
								<td width=\"120\">Jumlah Hari</td>
								<td width=\"380\">: ".number_format($data->JumlahHari,2)."</td>
							</tr>
							<tr>
								<td width=\"120\">Nominal Transaksi</td>
								<td width=\"380\">: ".number_format($data->TransactionAmount,2)."</td>
							</tr>
							<tr>
								<td width=\"120\">Tarif Pajak</td>
								<td width=\"380\">: ".number_format($data->TarifPajak,2)."%</td>
							</tr>
							<tr>
								<td width=\"120\">Nominal Pajak</td>
								<td width=\"380\">: ".number_format($data->TaxAmount,2)."</td>
							</tr>
						</table>";
			}elseif($_REQUEST['TaxType'] == 'Restoran'){
				$html = "<table border=\"0\" width=\"500\">
							<tr>
								<td colspan=\"2\" style=\"background-color:#E9E9E9;font-weight:bold\" align='center'>Indentitas WP dan OP</td>
							</tr>
							<tr>
								<td width=\"120\">NPWPD</td>
								<td width=\"380\">: {$data->NPWPD}</td>
							</tr>
							<tr>
								<td width=\"120\">Nama Wajib Pajak</td>
								<td width=\"380\">: {$data->NamaWP}</td>
							</tr>
							<tr>
								<td width=\"120\">NOP</td>
								<td width=\"380\">: {$data->NOP}</td>
							</tr>
							<tr>
								<td width=\"120\">Nama Objek Pajak</td>
								<td width=\"380\">: {$data->NamaOP}</td>
							</tr>
							<tr>
								<td width=\"120\">Rekening Pajak</td>
								<td width=\"380\">: {$data->Golongan}</td>
							</tr>
							<tr>
								<td colspan=\"2\" style=\"background-color:#E9E9E9;font-weight:bold\" align='center'>Transaksi</td>
							</tr>
							<tr>
								<td width=\"120\">Jumlah Meja</td>
								<td width=\"380\">: ".number_format($data->JumlahMeja,2)."</td>
							</tr>
							<tr>
								<td width=\"120\">Jumlah Kursi</td>
								<td width=\"380\">: ".number_format($data->JumlahKursi,2)."</td>
							</tr>
							<tr>
								<td width=\"120\">Jumlah Pengunjung</td>
								<td width=\"380\">: ".number_format($data->JumlahPengunjung,2)."</td>
							</tr>
							<tr>
								<td width=\"120\">Nominal Transaksi</td>
								<td width=\"380\">: ".number_format($data->TransactionAmount,2)."</td>
							</tr>
							<tr>
								<td width=\"120\">Tarif Pajak</td>
								<td width=\"380\">: ".number_format($data->TarifPajak,2)."%</td>
							</tr>
							<tr>
								<td width=\"120\">Nominal Pajak</td>
								<td width=\"380\">: ".number_format($data->TaxAmount,2)."</td>
							</tr>
						</table>"; 
			}
		}
		echo $html;
	}
}
?>

