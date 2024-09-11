<?php

class TransaksiCashRegister extends Pajak {

    public function grid_data_pembanding_detail() {		
		try {
            
            $where = " 1=1 ";
            
            $where.= (isset($_REQUEST['CPM_JENIS']) && !empty($_REQUEST['CPM_JENIS'])) ? " AND tax_code = \"{$_REQUEST['CPM_JENIS']}\" " : "";
			$where.= (isset($_REQUEST['CPM_NPWPD']) && !empty($_REQUEST['CPM_NPWPD'])) ? " AND npwpd = \"{$_REQUEST['CPM_NPWPD']}\" " : "";
			$where.= (isset($_REQUEST['CPM_NOP']) && !empty($_REQUEST['CPM_NOP'])) ? " AND nop = \"{$_REQUEST['CPM_NOP']}\" " : "";
			$where.= (isset($_REQUEST['NO_TRAN']) && !empty($_REQUEST['NO_TRAN'])) ? " AND tran_code = \"{$_REQUEST['NO_TRAN']}\" " : "";
				
            $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " 
			AND tran_date between 
			STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
			STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";

            $query = "select *
                        FROM TRANSACTION_CASH_REGISTER
                        WHERE {$where} ";
            $result = mysql_query($query, $this->Conn) or die(mysql_error());            
            $recordCount = mysql_num_rows($result);            
            
            $q = $query .=" ORDER BY tran_date DESC LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysql_query($query, $this->Conn) or die(mysql_error());
            $rows = array();
            
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];                
            while ($data = mysql_fetch_assoc($result)) {
                $dataTapbox = array();
                $dataTapbox = $data;
                $dataTapbox = array_merge($dataTapbox, array("NO" => ++$no));                
                //$dataTapbox['tran_type'] = $data['tran_type'] == 0? 'tidak kena pajak' : 'kena pajak';
                $dataTapbox['tax_code'] = $this->arr_pajak[(int) $dataTapbox['tax_code']];
                $dataTapbox['tran_amount'] = number_format($data['tran_amount'],2);
                
                $rows[] = $dataTapbox;
            }
            #query select list data

            $jTableResult = array();
            $jTableResult['q'] = $q;
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = $recordCount;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysql_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
    
	//--- Add : function | insertRecord [OST - 25/05/2018]  	
	public function insertRecord($npwpd, $nop, $tranCode, $taxCode, $tranDate, $tranAmount){		
		$tranDesc = 'manual input';
		try {
			// insert record
			$masaPajak = substr($tranDate,4,2).substr($tranDate,0,4);
			$query = "INSERT INTO TRANSACTION_CASH_REGISTER
					  (tran_code, npwpd, masa_pajak, nop,
					  quantity, tax_code, tran_type, tran_desc,
					  tran_amount, tran_date)
					  VALUES(
					  '{$tranCode}','{$npwpd}','{$masaPajak}',
					  '{$nop}',1,'{$taxCode}','1','{$tranDesc}',
					  {$tranAmount},'{$tranDate}')";	
			$result = mysql_query($query, $this->Conn) or die(mysql_error());     
			
			if($result){
				if(TransaksiCashRegister::getRecordTran($tranCode,$this->Conn,"insert")){
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
	
	
	//--- Add : function | updateRecord, only 1 field (tran_amount) [OST - 17/05/2018]  
	public function updateRecord($tranCode,$tranAmount){		
		try {
			if(TransaksiCashRegister::getRecordTran($tranCode,$this->Conn,"update")){
				// delete record
				$query = "UPDATE TRANSACTION_CASH_REGISTER SET tran_amount = {$tranAmount} WHERE tran_code = '{$tranCode}' ";	
				$result = mysql_query($query, $this->Conn) or die(mysql_error());     												
				
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
	public function deleteRecord($tranCode){		
		try {
			if(TransaksiCashRegister::getRecordTran($tranCode,$this->Conn,"delete")){
				// delete record
				$query = "DELETE FROM TRANSACTION_CASH_REGISTER WHERE tran_code = '{$tranCode}' ";		
				$result = mysql_query($query, $this->Conn) or die(mysql_error());     												
				
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
	
	//--- Add : function | backupRecordToLog [OST - 17/05/2018]     
	static public function getRecordTran($tranCode,$conn,$action){
		$values = "";
		$resultLog = false;
		
		$query = "SELECT * FROM TRANSACTION_CASH_REGISTER WHERE tran_code = '{$tranCode}' ";
		$result = mysql_query($query, $conn) or die(mysql_error());     
		
		if($result){
			while($obj = mysql_fetch_object($result)){
				$values = "('".$obj->tran_code."',".
						   "'".$obj->npwpd."',".
						   "'".$obj->nop."',".
						   "'".$obj->branch_code."',".
						   "'".$obj->masa_pajak."',".
						   "'".$obj->bill_number."',".
						   "".(empty($obj->quantity)? 0 : $obj->quantity).",".
						   "'".$obj->tax_code."',".
						   "'".$obj->tran_type."',".
						   "'".$obj->tran_desc."',".
						   "".$obj->tran_amount.",".
						   "'".$obj->tran_date."',".
						   "'{$action}',".
						   "'".date('Y-m-d H:i:s')."',".
						   "'".$_SESSION['uname']."')";
			}
			
			$resultLog = TransaksiCashRegister::addLog($values,$conn);
		}		
		
		return $resultLog; 
	}
		
	//--- Add : function | addLog [OST - 28/05/2018]     
	static public function addLog($values, $conn){
		$result = false;
		// insert to log table
		$query = "INSERT INTO TRANSACTION_CASH_REGISTER_LOG (
					tran_code, npwpd, nop, branch_code, masa_pajak,
					bill_number, quantity, tax_code,
					tran_type, tran_desc, tran_amount, tran_date,
					action, timestamp, user) VALUES ". $values;
		$result = mysql_query($query, $conn) or die(mysql_error());
		
		return $result;					
	}
			
    public function filtering_pembanding_detail($id) {
		$DIR = 'PATDA-V1';
		
		$npwpd = (isset($_SESSION['npwpd']) && $_SESSION['npwpd'] != "") ? $_SESSION['npwpd'] : '';
		
		$opt_jenis_pajak = "<option value=\"\">Semua</option>";
		$opt_jenis_pajak_input = "<option value=\"\">Pilih Jenis Pajak</option>";
		foreach ($this->arr_pajak as $x => $y) {
			$x = str_pad($x, 2,'0',STR_PAD_LEFT);
			$opt_jenis_pajak .= "<option value='{$x}'>{$y}</option>";
			$opt_jenis_pajak_input .= "<option value='{$x}'>{$y}</option>";
		}
		
        $url = "view/{$DIR}/monitoring/trans-cash-register/svc-download-tapbox.xls.php";
        $html = "<div class=\"filtering\">
                    <form>
						<table border=\"0\">
                        	<tr>
                                <td style='background:transparent;padding:2px;width:200px'>
									Jenis Pajak :<br/>
									<select id=\"CPM_JENIS-{$id}\" name=\"CPM_JENIS-{$id}\">{$opt_jenis_pajak}</select>
								</td>
								<td style='background:transparent;padding:2px;width:180px'>
									NPWPD :<br/>
									<input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" placeholder=\"NPWPD\" value=\"{$npwpd}\" ".(empty($npwpd)?'':'readonly').">
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
				
				<table width='100%'>
					<tr>
						<td style='background:transparent;padding:2px' align='right'>
							<button type=\"button\" id=\"tambah-{$id}\" onclick=\"javascript:openAddRecordForm()\">Tambah Transaksi</button>
						</td>    
					</tr>
				</table>
                
				<div id=\"id01\" class=\"modal\">  
				  <form class=\"modal-content animate\" id=\"tambahTransaksi\" autocomplete=\"off\">
					<div class=\"imgcontainer\">
					  <label for=\"uname\"><b>Tambah Transaksi</b></label>
					  <span onclick=\"document.getElementById('id01').style.display='none'\" class=\"close\" title=\"Close Modal\">&times;</span>
					</div>
					<div class=\"contentcontainer\">
					  <select class=\"field\" id=\"tax_code\" name=\"tax_code\" required>{$opt_jenis_pajak_input}</select>
					  <input class=\"field\" type=\"text\" placeholder=\"Kode Transaksi\" name=\"tran_code\" id=\"tran_code\" maxlength=\"12\" required>						
					  <input class=\"field\" type=\"text\" placeholder=\"NPWPD\" name=\"npwpd\" id=\"npwpd\" maxlength=\"18\" required>
					  <input class=\"field\" type=\"text\" placeholder=\"NOP\" name=\"nop\" id=\"nop\" maxlength=\"8\" required>						
					  <input class=\"field\" type=\"text\" placeholder=\"Tanggal Transaksi\" name=\"tran_date\" required id=\"tran_date\";\">						
					  <input class=\"field\" type=\"text\" placeholder=\"Nilai Transaksi\" name=\"tran_amount\" required id=\"tran_amount\">						
					  <p align=\"right\">
					  <button type=\"submit\">Save</button>
					  </p>
					</div>					
				  </form>
				</div>    
				 
				<!--------------- 	------------->
								         
                ";
        return $html;
    }
    
	// Modify : Fields name "Total Transaksi" to "Nilai Transaksi" [OST - 11/05/2018]	
	// Add : parameter dateStart, dateEnd, noTran on getResume() function [OST - 14/05/2018] 
    public function grid_table_pembanding_detail() {
        $DIR = "PATDA-V1";
        $modul = "monitoring/trans-cash-register";
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
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=list&a={$this->_a}&m={$this->_m}',
                                updateAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=update',
                                deleteAction: 'view/{$DIR}/{$modul}/svc-list-data-pembanding-detail.php?action=delete',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%', edit:false},
                                tax_code: {title: 'Jenis Pajak',width: '10%', edit:false},
                                npwpd: {title: 'NPWPD',width: '5%', edit:false},
                                nop: {title: 'NOP',width: '5%', edit:false},
                                tran_code: {title: 'No. Transaksi',width: '7%',key:true},
                                tran_date: {title: 'Tanggal Transaksi',width: '10%', edit:false},
                                tran_amount: {title: 'Nilai Transaksi',width: '7%'}
                            }
                        });     
                        //$('#tapboxPajak-{$this->_i}').jtable('load');               
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            var parameter = {
                                CPM_JENIS : $('#CPM_JENIS-{$this->_i}').val(),
                                NO_TRAN : $('#NO_TRAN-{$this->_i}').val(),
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_NOP : $('#CPM_NOP-{$this->_i}').val(),
                                TRAN_DATE1 : $('#TRAN_DATE1-{$this->_i}').val(),
                                TRAN_DATE2 : $('#TRAN_DATE2-{$this->_i}').val()
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
											
						$('#tran_date').datetimepicker({format: 'Y-m-d h:i:s'});
							
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
						// -------------                                                             
                    });
                                        
					function openAddRecordForm(){
						document.getElementById('id01').style.display='block';  
						$('#tran_code').val('');
						$('#tran_code').attr('placeholder','Kode Transaksi');
						$('#npwpd').val('');
						$('#npwpd').attr('placeholder','NPWPD');
						$('#bill_number').val('');
						$('#bill_number').attr('placeholder','No. Bill');
						$('#tran_desc').val('');
						$('#tran_desc').attr('placeholder','Deskripsi');
						$('#tran_date').val('');
						$('#tran_date').attr('placeholder','Tanggal Transaksi');
						$('#tran_amount').val('');
						$('#tran_amount').attr('placeholder','Nilai Transaksi');												                  
                    }
                                        
					function openDate(obj) {
						$(obj).datepicker({dateFormat: 'dd-mm-yy'});
						$(obj).datepicker('show');
					}

                    
                </script>";
        echo $html;
    }
    
    function download_tapbox_xls() {
		
		$limit = 2000;
        $where = "1=1 ";
        $where.= (isset($_SESSION['npwpd']) && $_SESSION['npwpd'] != "") ? " AND npwpd = \"{$_SESSION['npwpd']}\" " : "";
        $where.= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND tran_code = \"{$_REQUEST['NO_TRAN']}\" " : "";
        $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " 
		AND tran_date between 
		STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
		STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";


		if(isset($_REQUEST['count'])){
			$query = "select 
			COUNT(*) AS RecordCount
					FROM TRANSACTION_CASH_REGISTER
					WHERE {$where}";	
			#echo $query;exit;
			$result = mysql_query($query, $this->Conn);	            
			$data = mysql_fetch_assoc($result);
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

		$query = "select * FROM TRANSACTION_CASH_REGISTER
					WHERE {$where} ORDER BY tran_date DESC LIMIT {$offset}, {$total}";
		
		$res = mysql_query($query, $this->Conn);
		#echo mysql_num_rows($res);exit;
			
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
        $sumRows = mysql_num_rows($res);
		$no = $offset+1;
        while ($rowData = mysql_fetch_assoc($res)) {
			$rowData['tran_amount'] = (int) str_replace(",","",$rowData['tran_amount']);
			$rowData['tran_code'] = preg_replace("/[^A-Za-z0-9]/","",$rowData['tran_code']);
			$jenis_pajak = $this->arr_pajak[(int) $rowData['tax_code']];
			
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $no);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $jenis_pajak);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['npwpd'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['nop'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $rowData['tran_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['tran_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['tran_amount']);
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
		
		$where.= (isset($_REQUEST['CPM_JENIS']) && !empty($_REQUEST['CPM_JENIS'])) ? " AND tax_code = \"{$_REQUEST['CPM_JENIS']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NPWPD']) && !empty($_REQUEST['CPM_NPWPD'])) ? " AND npwpd = \"{$_REQUEST['CPM_NPWPD']}\" " : "";
		$where.= (isset($_REQUEST['CPM_NOP']) && !empty($_REQUEST['CPM_NOP'])) ? " AND nop = \"{$_REQUEST['CPM_NOP']}\" " : "";
		$where.= (isset($_REQUEST['NO_TRAN']) && !empty($_REQUEST['NO_TRAN'])) ? " AND tran_code = \"{$_REQUEST['NO_TRAN']}\" " : "";
		
		$query = "select SUM(tran_amount) as total 
					FROM TRANSACTION_CASH_REGISTER
					WHERE {$where} 
					AND MONTH(tran_date) = '{$bln}' 
					AND YEAR(tran_date) = '{$thn}'";
		$result = mysql_query($query, $this->Conn) or die(mysql_error());            
		$row_bln = mysql_fetch_assoc($result);
		
		$bulan_ini = isset($row_bln['total'])? $row_bln['total'] : 0;
		
		//--- Add & modify : current day to previous day [OST - 24/04/2018] 
		$thnKemarin = date("Y", (time() - 60 * 60 * 24));
		$blnKemarin = date("m", (time() - 60 * 60 * 24));
		$tglKemarin = date("d", (time() - 60 * 60 * 24));
		//--------
		
		$query = "select SUM(tran_amount) as total 
					FROM TRANSACTION_CASH_REGISTER
					WHERE {$where} 
					AND MONTH(tran_date) = '{$blnKemarin}' 
					AND YEAR(tran_date) = '{$thnKemarin}'
					AND DAY(tran_date) = '{$tglKemarin}'
					";
		$result = mysql_query($query, $this->Conn) or die(mysql_error());            
		$row_tgl = mysql_fetch_assoc($result);
		$hari_ini = isset($row_tgl['total'])? $row_tgl['total'] : 0;	
			
			
		//--------- Add : Total Amount [OST - 14/05/2018]    
		$totalAmount = 0;	
		$where.= (isset($dateStart) && $dateStart != "") ? " 
		AND tran_date between 
		STR_TO_DATE(CONCAT(\"{$dateStart}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
		STR_TO_DATE(CONCAT(\"{$dateEnd}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";

		$query = "select sum(tran_amount) as TOTAL_AMOUNT
					FROM TRANSACTION_CASH_REGISTER
					WHERE {$where} ";
		
		$result = mysql_query($query, $this->Conn) or die(mysql_error());            
		if($result){
			while($obj = mysql_fetch_object($result)){
				$totalAmount = $obj->TOTAL_AMOUNT;
			}
		}
		mysql_free_result($result);	
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

