<?php

class Tracking extends Pajak {
    
    function __construct() {       
        
        parent::__construct();
        $PAJAK = isset($_POST) ? $_POST : array();
        $PAJAK = isset($_GET) ? array_merge($_GET, $PAJAK) : $PAJAK;
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        $this->_a = "aPatda";
    }

    public function data_table_angkutan() {
		
		try {
            $query = "SELECT count(*) as jml FROM PATDA_MINERAL_AUDITTRAIL_ANGKUTAN";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $jumlah = $row['jml'];
            
            $query = "SELECT * FROM PATDA_MINERAL_AUDITTRAIL_ANGKUTAN
            LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
            
            $result = mysqli_query($this->Conn, $query);
            $rows = array();
            $no = ($_GET["jtStartIndex"]/$_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $rows[] = $row;
            }
            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = $jumlah;
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
    
    public function crud_angkutan(){
		if($this->f == 'create') $this->create_angkutan();
		elseif($this->f == 'update') $this->update_angkutan();
		elseif($this->f == 'delete') $this->delete_angkutan();
	}
	
	private function delete_angkutan(){
		$resp = array();
		
		try {
            $query = sprintf("
				DELETE FROM PATDA_MINERAL_AUDITTRAIL_ANGKUTAN WHERE CPM_TRUCK_ID = '%s'", $this->CPM_TRUCK_ID
			);
			
            $result = mysqli_query($this->Conn, $query);
            $resp['Result'] = "OK";
            print $this->Json->encode($resp);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            
            $resp['Result'] = "ERROR";
            $resp['Message'] = $ex->getMessage();
            print $this->Json->encode($resp);
        }
	}

	private function create_angkutan() {
		
		$resp = array();
        try {
            $query = sprintf("
				INSERT INTO PATDA_MINERAL_AUDITTRAIL_ANGKUTAN
				(CPM_TRUCK_ID, CPM_NOPOL, CPM_KAPASITAS_ANGKUT, CPM_DATE_CREATED)
				VALUES('%s', '%s', %s, '%s')",
				$this->CPM_TRUCK_ID,
				$this->CPM_NOPOL,
				$this->CPM_KAPASITAS_ANGKUT,
				date('Y-m-d H:i:s')
			);
			
            $result = mysqli_query($this->Conn, $query);
            $resp['Result'] = "OK";
            print $this->Json->encode($resp);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            
            $resp['Result'] = "ERROR";
            $resp['Message'] = $ex->getMessage();
            print $this->Json->encode($resp);
        }
        ob_clean();
        header('location:../../../../main.php?param='.base64_encode('a=aPatda&m=mPatdaTracking4'));
    }
    
	private function update_angkutan() {
		
		$resp = array();
        try {
            $query = sprintf("
				UPDATE PATDA_MINERAL_AUDITTRAIL_ANGKUTAN SET 
				CPM_NOPOL='%s',
				CPM_KAPASITAS_ANGKUT = '%s'
				WHERE CPM_TRUCK_ID ='%s'",
				$this->CPM_NOPOL,
				$this->CPM_KAPASITAS_ANGKUT,
				$this->CPM_TRUCK_ID
			);
			
            $result = mysqli_query($this->Conn, $query);
            $resp['Result'] = "OK";
            print $this->Json->encode($resp);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            
            $resp['Result'] = "ERROR";
            $resp['Message'] = $ex->getMessage();
            print $this->Json->encode($resp);
        }
    }
    
    public function grid_table_angkutan() {
        $DIR = "PATDA-V1";
        $modul = "tracking/mineral";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                <div id=\"angkutan\" style=\"width:50%;\"></div>
                <script type=\"text/javascript\">
					$(document).ready(function() {
                        $('#angkutan').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            selecting:true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-angkutan.php',
                                createAction: 'function/{$DIR}/{$modul}/svc-crud-angkutan.php?f=create',
                                updateAction: 'function/{$DIR}/{$modul}/svc-crud-angkutan.php?f=update',
                                deleteAction: 'function/{$DIR}/{$modul}/svc-crud-angkutan.php?f=delete'
                            },
                            fields: {
                                NO : {
									title: 'No',
									width: '3%',
									edit :false
								},
                                CPM_TRUCK_ID: { 
									key: true,
									list:true,
									title:'ID Angkutan',
									width: '15%',
									edit :false,
								}, 
                                CPM_NOPOL: {
									title:'Nomor Polisi',
									width: '50%'
                                },
                                CPM_KAPASITAS_ANGKUT : {
									title:'Kapasitas Angkut (m<sup>3</sup>)',
									width:'40%'
								}
                            }
                        });
                        $('#angkutan').jtable('load');
                    });
                </script>";
        echo $html;
    }
 
    public function filtering_tracking($id) {
        $p = $this->Json->decode(base64_decode($_REQUEST['p']));
        $q = $this->Json->decode(base64_decode($_REQUEST['q']));
        $arr = explode(";", $p->CPM_TRUCK_ID);
        $truck_id = "<option value='' selected>Semua</option>";
        foreach ($arr as $val) {
            $truck_id .= "<option value='{$val}'>{$val}</option>";
        }

        $html = "<div class=\"filtering\">
                    <form>
                        <input type='hidden' id=\"HIDDEN-{$id}\" tahun=\"{$p->TAHUN_PAJAK}\" npwpd=\"{$p->CPM_NPWPD}\" bulan=\"{$p->MASA_PAJAK}\" truck_id=\"{$p->CPM_TRUCK_ID}\" a=\"{$q->a}\">
                        <table>
                            <tr>
                                <td style='background:transparent;padding:2px'>No. Transaksi</td>
                                <td style='background:transparent;padding:2px'>: <input type=\"text\" name=\"CPM_TRAN_ID-{$id}\" id=\"CPM_TRAN_ID-{$id}\" >
                                    ID Angkutan : <select name=\"CPM_TRUCK_ID-{$id}\" id=\"CPM_TRUCK_ID-{$id}\" all=\"{$p->CPM_TRUCK_ID}\" >{$truck_id}</select>
                                </td>
                                <td style='background:transparent;padding:2px'></td>
                                <td style='background:transparent;padding:2px'></td>
                                <td style='background:transparent;padding:2px'></td>
                                <td style='background:transparent;padding:2px'></td>
                            </tr>
                            <tr>
                                <td style='background:transparent;padding:2px'>Tanggal Pengiriman</td>
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
                                <td style='background:transparent;padding:2px'>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</td>
                                <td style='background:transparent;padding:2px;font-weight:bold'>Total Volume : </td>
                                <td style='background:transparent;padding:2px;font-weight:bold' id='total_volume'></td>
                            </tr>
                        </table>
                    </form>
                </div> ";
        return $html;
    }
        
    public function grid_table_tracking(){
		$DIR = "PATDA-V1";
        $modul = "parkir";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering_tracking($this->_i)}
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
                                listAction: 'view/{$DIR}/tracking/mineral/svc-list-data-tracking.php?action=list&a={$this->_a}&m={$this->_m}&p={$_REQUEST['p']}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '1%'},
                                CPM_TRAN_ID: {title: 'ID Transaksi',width: '3%'},
                                CPM_TRUCK_ID: {title: 'ID Angkutan',width: '3%'},
                                CPM_NOPOL: {title: 'Nomor Polisi',width: '4%'},
                                CPM_KAPASITAS_ANGKUT: {title: 'Kapasitas Angkut (m<sup>3</sup>)',width: '8%'},
                                CPM_JENIS: {title: 'Golongan',width: '5%'},
                                CPM_VOLUME: {title: 'Volume (m<sup>3</sup>)',width: '6%'},
                                CPM_DATE_SENT: {title: 'Tanggal Pengiriman',width: '5%'}
                            },
                            
							recordsLoaded: function (event, data) {
								var res = data.serverResponse;
								$('#total_volume').html(res.TotalRecordSum);
							}
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#tapboxPajak-{$this->_i}').jtable('load', {
                                CPM_TRAN_ID : $('#CPM_TRAN_ID-{$this->_i}').val(),
                                CPM_TRUCK_ID : $('#CPM_TRUCK_ID-{$this->_i}').val(),
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
    
	public function grid_data_tracking(){
		try {
            $p = $this->Json->decode(base64_decode($_REQUEST['p']));
            $jenis = $this->get_list_jenis();

            $trckid = explode(";", $p->CPM_TRUCK_ID);
            $truck_id = "'" . implode("','", $trckid) . "'";
            $where = "A.CPM_TRUCK_ID in ({$truck_id}) ";
            $where.= "AND DATE_FORMAT(STR_TO_DATE(A.CPM_DATE_SENT,'%d-%m-%Y'),'%Y') = \"{$p->TAHUN_PAJAK}\" ";
            $where.= "AND DATE_FORMAT(STR_TO_DATE(A.CPM_DATE_SENT,'%d-%m-%Y'),'%m') = \"{$p->MASA_PAJAK}\" ";
            $where.= (isset($_REQUEST['CPM_TRAN_ID']) && $_REQUEST['CPM_TRAN_ID'] != "") ? " AND A.CPM_TRAN_ID = \"{$_REQUEST['CPM_TRAN_ID']}\" " : "";
            $where.= (isset($_REQUEST['CPM_TRUCK_ID']) && $_REQUEST['CPM_TRUCK_ID'] != "") ? " AND A.CPM_TRUCK_ID = \"{$_REQUEST['CPM_TRUCK_ID']}\" " : "";

            $where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " AND DATE_FORMAT(STR_TO_DATE(A.CPM_DATE_SENT,'%d-%m-%Y'),\"%d-%m-%Y %h:%i:%s\") between 
                    CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\") and 
                    CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\")  " : "";

            $query = "SELECT
                        A.CPM_TRUCK_ID, 
                        '{$p->CPM_NPWPD}' as CPM_NPWPD,
                        A.CPM_TRAN_ID,
						A.CPM_DATE_SENT,
                        A.CPM_VOLUME,
                        A.CPM_JENIS,
                        B.CPM_NOPOL,
                        B.CPM_KAPASITAS_ANGKUT
                        FROM PATDA_MINERAL_AUDITTRAIL A 
                        LEFT JOIN PATDA_MINERAL_AUDITTRAIL_ANGKUTAN B ON A.CPM_TRUCK_ID = B.CPM_TRUCK_ID 
                        WHERE {$where} ORDER BY CPM_DATE_SENT LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
            $rows = array();

            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($data = mysqli_fetch_assoc($result)) {
                $data_tracking = $data;
                $data_tracking['CPM_JENIS'] = $jenis[$data['CPM_JENIS']];
                $data_tracking = array_merge($data_tracking, array("NO" => ++$no));
                $rows[] = $data_tracking;
            }
            
            $query = "SELECT COUNT(*) AS JUMLAH, SUM(CPM_VOLUME) AS TOTAL FROM PATDA_MINERAL_AUDITTRAIL	A WHERE {$where}";
            $result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
            $TotalRecordCount = 0;
            $TotalRecordSum = 0;
            if($data = mysqli_fetch_assoc($result)) {
				$TotalRecordCount = $data['JUMLAH'];
				$TotalRecordSum = $data['TOTAL'];
			}

            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = $TotalRecordCount;
            $jTableResult['TotalRecordSum'] = $TotalRecordSum. ' m<sup>3</sup>';
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

	function download_tracking_xls() {
		$limit = 2000;
		$trckid = explode(";", $_REQUEST['alltruckid']);
        $truck_id = "'" . implode("','", $trckid) . "'";
		$where = "A.CPM_TRUCK_ID in ({$truck_id}) ";
		$where.= "AND DATE_FORMAT(STR_TO_DATE(A.CPM_DATE_SENT,'%d-%m-%Y'),'%Y') = \"{$_REQUEST['TAHUN_PAJAK']}\" ";
		$where.= "AND DATE_FORMAT(STR_TO_DATE(A.CPM_DATE_SENT,'%d-%m-%Y'),'%m') = \"{$_REQUEST['MASA_PAJAK']}\" ";
		$where.= (isset($_REQUEST['CPM_TRAN_ID']) && $_REQUEST['CPM_TRAN_ID'] != "") ? " AND A.CPM_TRAN_ID = \"{$_REQUEST['CPM_TRAN_ID']}\" " : "";
		$where.= (isset($_REQUEST['CPM_TRUCK_ID']) && $_REQUEST['CPM_TRUCK_ID'] != "") ? " AND A.CPM_TRUCK_ID = \"{$_REQUEST['CPM_TRUCK_ID']}\" " : "";

		$where.= (isset($_REQUEST['TRAN_DATE1']) && $_REQUEST['TRAN_DATE1'] != "") ? " AND DATE_FORMAT(STR_TO_DATE(A.CPM_DATE_SENT,'%d-%m-%Y'),\"%d-%m-%Y %h:%i:%s\") between 
				CONCAT(\"{$_REQUEST['TRAN_DATE1']}\",\" 00:00:00\") and 
				CONCAT(\"{$_REQUEST['TRAN_DATE2']}\",\" 23:59:59\")  " : "";

		if(isset($_REQUEST['count'])){
			$query = "SELECT COUNT(*) AS RecordCount
					from PATDA_MINERAL_AUDITTRAIL A
					WHERE {$where}";
			//echo $query;exit;
			$result = mysqli_query($this->Conn, $query);
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
		
		$query = "SELECT
			A.CPM_TRUCK_ID, 
			'{$_REQUEST['CPM_NPWPD']}' as CPM_NPWPD,
			A.CPM_TRAN_ID,
			A.CPM_DATE_SENT,
			A.CPM_VOLUME,
			A.CPM_JENIS,
			B.CPM_NOPOL,
			B.CPM_KAPASITAS_ANGKUT
			FROM PATDA_MINERAL_AUDITTRAIL A LEFT JOIN PATDA_MINERAL_AUDITTRAIL_ANGKUTAN B ON A.CPM_TRUCK_ID = B.CPM_TRUCK_ID 
			WHERE {$where} ORDER BY CPM_DATE_SENT LIMIT {$offset}, {$total}";
		
		$jenis = $this->get_list_jenis();
		
		//echo $query;exit;
		$res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
		
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
                ->setCellValue('C1', 'ID Angkutan')
                ->setCellValue('D1', 'Nomor Polisi')
                ->setCellValue('E1', 'Kapasitas Angkut (m3)')
                ->setCellValue('F1', 'Golongan')
                ->setCellValue('G1', 'Volume (m3)')
                ->setCellValue('H1', 'Tanggal Pengiriman');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);

		//print_r($_REQUEST);exit;
        $row = 2;
        $sumRows = mysqli_num_rows($res);
		$no = $offset+1;
        while ($rowData = mysqli_fetch_assoc($res)) {
			$rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
			$rowData['CPM_TRAN_ID'] = preg_replace("/[^A-Za-z0-9]/","",$rowData['CPM_TRAN_ID']);
			$rowData['CPM_JENIS'] = $jenis[$rowData['CPM_JENIS']];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $no);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_TRUCK_ID'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_NOPOL'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_KAPASITAS_ANGKUT']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_JENIS']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_VOLUME']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_DATE_SENT']);
            $row++;
			$no++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Pengimriman Data MBLB');

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
        $objPHPExcel->getActiveSheet()->getStyle('A1:H' . ($row-1))->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );

        for ($x = "A"; $x <= "H"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }

		header('Content-Type: application/vnd.ms-excel');
		$nmfile = $_REQUEST['nmfile'];
        header('Content-Disposition: attachment;filename="' . $nmfile.'-'.date('yymdhmi').'.xls"');

        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    
    public function get_list_jenis(){
		$query = "SELECT A.CPM_JENIS as KODE, B.nmrek AS NAME FROM PATDA_MINERAL_AUDITTRAIL_JENIS A INNER JOIN PATDA_REK_PERMEN13 B ON A.CPM_KDREK=B.kdrek";
		$res = mysqli_query($this->Conn, $query);
		
		$list = array();
		while($row = mysqli_fetch_object($res)){
			$list[$row->KODE] = $row->NAME;
		}
		
		return $list;
	}
}

?>
