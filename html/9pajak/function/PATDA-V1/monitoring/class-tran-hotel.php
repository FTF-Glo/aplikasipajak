<?php

class TransaksiHotel extends Pajak {

    public function grid_data_pembanding_detail() {		
		try {
            
            $where = " 1=1 ";
            $where.= (isset($_SESSION['npwpd']) && $_SESSION['npwpd'] != "") ? " AND npwpd = \"{$_SESSION['npwpd']}\" " : "";
            $where.= (isset($_REQUEST['NO_TRAN']) && $_REQUEST['NO_TRAN'] != "") ? " AND tran_code = \"{$_REQUEST['NO_TRAN']}\" " : "";            
            $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " 
			AND tran_date between 
			STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
			STR_TO_DATE(CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";

            $query = "select *
                        FROM TRANSACTION_HOTEL
                        WHERE {$where} ";
            $result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));            
            $recordCount = mysqli_num_rows($result);            
            
            $q = $query .=" ORDER BY tran_date DESC LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
            $rows = array();
            
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];                
            while ($data = mysqli_fetch_array($result)) {
                $dataTapbox = array();
                $dataTapbox = $data;
                $dataTapbox = array_merge($dataTapbox, array("NO" => ++$no));                
                //$dataTapbox['tran_type'] = $data['tran_type'] == 0? 'tidak kena pajak' : 'kena pajak';
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

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
    
	//--- Add : function | insertRecord [OST - 25/05/2018]  	
	public function insertRecord($npwpd, $billNumber, $tranDesc, $tranCode,$tranDate, $tranAmount){		
		try {
			// insert record
			$masaPajak = substr($tranDate,4,2).substr($tranDate,0,4);
			$query = "INSERT INTO TRANSACTION_HOTEL
					  (tran_code, npwpd, masa_pajak, bill_number,
					  quantity, tax_type, tax_code, tran_type, tran_desc,
					  tran_amount, tran_date)
					  VALUES(
					  '{$tranCode}','{$npwpd}','{$masaPajak}',
					  '{$billNumber}',1,'01','30000','1','{$tranDesc}',
					  {$tranAmount},'{$tranDate}')";	
			$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));     
			
			if($result){
				if(TransaksiHotel::getRecordTran($tranCode,$this->Conn,"insert")){
					//Return result to jTable
					$jTableResult = array();
					$jTableResult['Result'] = "OK";
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
			if(TransaksiHotel::getRecordTran($tranCode,$this->Conn,"update")){
				// delete record
				$query = "UPDATE TRANSACTION_HOTEL SET tran_amount = {$tranAmount} WHERE tran_code = '{$tranCode}' ";	
				$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));     												
				
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
			if(TransaksiHotel::getRecordTran($tranCode,$this->Conn,"delete")){
				// delete record
				$query = "DELETE FROM TRANSACTION_HOTEL WHERE tran_code = '{$tranCode}' ";		
				$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));     												
				
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
		
		$query = "SELECT * FROM TRANSACTION_HOTEL WHERE tran_code = '{$tranCode}' ";
		$result = mysqli_query($conn, $query) or die(mysqli_error($conn));     
		
		if($result){
			while($obj = mysqli_fetch_object($result)){
				$values = "('".$obj->tran_code."',".
						   "'".$obj->npwpd."',".
						   "'".$obj->branch_code."',".
						   "'".$obj->masa_pajak."',".
						   "'".$obj->bill_number."',".
						   "".$obj->quantity.",".
						   "'".$obj->tax_type."',".
						   "'".$obj->tax_code."',".
						   "'".$obj->tran_type."',".
						   "'".$obj->tran_desc."',".
						   "".$obj->tran_amount.",".
						   "'".$obj->tran_date."',".
						   "'{$action}',".
						   "'".date('Y-m-d H:i:s')."',".
						   "'".$_SESSION['uname']."')";
			}
			
			$resultLog = TransaksiHotel::addLog($values,$conn);
		}		
		
		return $resultLog; 
	}
		
	//--- Add : function | addLog [OST - 28/05/2018]     
	static public function addLog($values, $conn){
		$result = false;
		// insert to log table
		$query = "INSERT INTO TRANSACTION_HOTEL_LOG (
					tran_code, npwpd, branch_code, masa_pajak,
					bill_number, quantity, tax_type, tax_code,
					tran_type, tran_desc, tran_amount, tran_date,
					action, timestamp, user) VALUES ". $values;
		$result = mysqli_query($conn, $query) or die(mysqli_error($conn));     	
		
		return $result;					
	}
			
    public function filtering_pembanding_detail($id) {
		$DIR = 'PATDA-V1';
        $url = "view/{$DIR}/monitoring/tran-hotel/svc-download-tapbox.xls.php";
        $html = "<div class=\"filtering\">
                    <form>
                        <table>
                            <tr>
                                <td style='background:transparent;padding:2px'>No. Transaksi</td>
                                <td style='background:transparent;padding:2px'>: <input type=\"text\" name=\"NO_TRAN-{$id}\" id=\"NO_TRAN-{$id}\" >
                                    <input type='hidden' name=\"CPM_DEVICE_ID-{$id}\" id=\"CPM_DEVICE_ID-{$id}\">
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
					  <input class=\"field\" type=\"text\" placeholder=\"Kode Transaksi\" name=\"tran_code\" id=\"tran_code\" maxlength=\"12\" required>						
					  <input class=\"field\" type=\"text\" placeholder=\"NPWPD\" name=\"npwpd\" id=\"npwpd\" maxlength=\"18\" required>
					  <input class=\"field\" type=\"text\" placeholder=\"No. Bill\" name=\"bill_number\" id=\"bill_number\" maxlength=\"8\" required>						
					  <input class=\"field\" type=\"text\" placeholder=\"Deskripsi\" name=\"tran_desc\" id=\"tran_desc\" maxlength=\"80\" required>						
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
        $modul = "monitoring/tran-hotel";
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
                                npwpd: {title: 'NPWPD',width: '5%', edit:false},
                                bill_number: {title: 'Nomor Bill',width: '5%', edit:false},
                                tran_desc: {title: 'Deskripsi',width: '15%', edit:false},
                                tran_code: {title: 'Kode Transaksi',width: '7%',key:true},
                                tran_date: {title: 'Tanggal Transaksi',width: '10%', edit:false},
                                tran_type: {title: 'Jenis Transaksi',width: '7%', edit:false},
                                tran_amount: {title: 'Nilai Transaksi',width: '7%'}
                                
                            }
                        });     
                        //$('#tapboxPajak-{$this->_i}').jtable('load');               
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#tapboxPajak-{$this->_i}').jtable('load', {
                                NO_TRAN : $('#NO_TRAN-{$this->_i}').val(),
                                CPM_DEVICE_ID : $('#CPM_DEVICE_ID-{$this->_i}').val(),
                                TRAN_DATE1 : $('#TRAN_DATE1-{$this->_i}').val(),
                                TRAN_DATE2 : $('#TRAN_DATE2-{$this->_i}').val()                                    
                            });
                            get_resume($('#TRAN_DATE1-{$this->_i}').val(),$('#TRAN_DATE2-{$this->_i}').val(),$('#NO_TRAN-{$this->_i}').val());
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
					FROM TRANSACTION_HOTEL
					WHERE {$where}";	
			#echo $query;exit;
			$result = mysqli_query($this->Conn, $query);	            
			$data = mysqli_fetch_array($result);
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

		$query = "select * FROM TRANSACTION_HOTEL
					WHERE {$where} ORDER BY tran_date DESC LIMIT {$offset}, {$total}";
		
		$res = mysqli_query($this->Conn, $query);
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
                ->setCellValue('B1', 'NPWPD')
                ->setCellValue('C1', 'Nomor Bill')
                ->setCellValue('D1', 'Deskripsi')
                ->setCellValue('E1', 'Kode Transaksi')
                ->setCellValue('F1', 'Tanggal Transaksi')
                ->setCellValue('G1', 'Jenis Transaksi')
                ->setCellValue('H1', 'Total Pajak');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 2;
        $sumRows = mysqli_num_rows($res);
		$no = $offset+1;
        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['tran_amount'] = (int) str_replace(",","",$rowData['tran_amount']);
			$rowData['tran_code'] = preg_replace("/[^A-Za-z0-9]/","",$rowData['tran_code']);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $no);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['npwpd'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['bill_number'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['tran_desc']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $rowData['tran_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['tran_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['tran_type']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['tran_amount']);
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
	public function get_resume($dateStart,$dateEnd,$noTran){
		$where = " 1=1 ";
		$thn = date('Y');
		$bln = date('m');
		$tgl = date('d');
		
		$where.= (isset($_SESSION['npwpd']) && $_SESSION['npwpd'] != "") ? " AND npwpd = \"{$_SESSION['npwpd']}\" " : "";
		
		$query = "select SUM(tran_amount) as total 
					FROM TRANSACTION_HOTEL
					WHERE {$where} 
					AND MONTH(tran_date) = '{$bln}' 
					AND YEAR(tran_date) = '{$thn}'";
		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));            
		$row_bln = mysqli_fetch_assoc($result);
		
		$bulan_ini = isset($row_bln['total'])? $row_bln['total'] : 0;
		
		//--- Add & modify : current day to previous day [OST - 24/04/2018] 
		$thnKemarin = date("Y", (time() - 60 * 60 * 24));
		$blnKemarin = date("m", (time() - 60 * 60 * 24));
		$tglKemarin = date("d", (time() - 60 * 60 * 24));
		//--------
		
		$query = "select SUM(tran_amount) as total 
					FROM TRANSACTION_HOTEL
					WHERE {$where} 
					AND MONTH(tran_date) = '{$blnKemarin}' 
					AND YEAR(tran_date) = '{$thnKemarin}'
					AND DAY(tran_date) = '{$tglKemarin}'
					";
		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));            
		$row_tgl = mysqli_fetch_assoc($result);
		$hari_ini = isset($row_tgl['total'])? $row_tgl['total'] : 0;	
			
			
		//--------- Add : Total Amount [OST - 14/05/2018]    
		$totalAmount = 0;	
		$where = " 1=1 ";
		$where.= (isset($_SESSION['npwpd']) && $_SESSION['npwpd'] != "") ? " AND npwpd = \"{$_SESSION['npwpd']}\" " : "";
		$where.= (isset($noTran) && $noTran != "") ? " AND tran_code = \"{$noTran}\" " : "";            
		$where.= (isset($dateStart) && $dateStart != "") ? " 
		AND tran_date between 
		STR_TO_DATE(CONCAT(\"{$dateStart}\",\" 00:00:00\"),\"%d-%m-%Y %H:%i:%s\") and 
		STR_TO_DATE(CONCAT(\"{$dateEnd}\",\" 23:59:59\"),\"%d-%m-%Y %H:%i:%s\")  " : "";

		$query = "select sum(tran_amount) as TOTAL_AMOUNT
					FROM TRANSACTION_HOTEL
					WHERE {$where} ";
		
		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));            
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

