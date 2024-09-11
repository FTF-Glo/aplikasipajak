<?php

class TapboxPajak extends Pajak {

	private $conn;
	private $kode;
	public function __construct() {
		parent::__construct();
		$this->kode = array ( 0 => 'Unknown Error', 1 =>"Printer Cable Unplugged",2 =>"Tamper 1: Shake/Freefall",3 =>"Tamper 2: Orientation Changed",4 =>"Temperature Upper Limit",5 =>"Power Cable Unplugged",6 =>"Battery Undetected",7 =>"Failed to Access MicroSD",8 =>"Power Management Sensor Failed",9 =>"Push Button Pressed",10 =>"OS Failed",129 =>"Printer Cable Unplugged Release",130 =>"Tamper 1: Shake/Freefall Release",131 =>"Tamper 2: Orientation Changed Release",132 =>"Temperature Upper Limit Release",133 =>"Power Cable Unplugged Release",134 =>"Battery Undetected Release",135 =>"Failed to Access MicroSD Release",136 =>"Power Management Sensor Failed Release",137 =>"Push Button Pressed Release",138 =>"OS Failed Release",11=>'Sending data to server');
	}

	private function getConnection() {
		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_TB_DBNAME'];
		$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
		$dbTable = $arr_config['PATDA_TB_TABLE'];
		$dbUser = $arr_config['PATDA_TB_USERNAME'];

		/*$dbName = "simpatda_1";
		 $dbHost = "127.0.0.1";
		 $dbPwd = "sw_pwd";
		 $dbUser = "sw_user";*/

		$this->conn = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
		//mysqli_select_db($dbName, $this->conn);
	}

	public function getTotalAlarm() {
		$this->getConnection();
		$total = 0;
		
		$res = mysqli_query($this->conn, "SELECT TOTAL FROM VIEW_ALARM_TOTAL");
		if($d = mysqli_fetch_assoc($res)){
			$total = $d['TOTAL'];
		}
		
		$data['totalDevice'] = $this->getTotalDevice();
		$data['totalAlarm'] = $total;
		ob_clean();
		echo $this->Json->encode($data);
	}
	
	private function shortString($str){
		$arr = explode('.', $str );
		$name = $arr[0];
		$ext = end($arr);
		return strlen($name)>8? substr($name,0,4)."..".substr($name,-2).".".$ext : $str;
	}
	
	public function getDeviceAlarm() {
		try {
			$this->getConnection();
			$listAlarm = $this->kode;
			$query = "SELECT * FROM VIEW_ALARM ORDER BY NPWPD,DEVICEID,STATUS "; 
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$recordCount = mysqli_num_rows($result);

			$query .= " LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$rows = array();
				
			$no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($d = mysqli_fetch_assoc($result)) {
				$dt = array();
				
				$json = $this->Json->encode($d);
				$alarm = ($d['STATUS'] ==1)? "<span class='text-success'><i class=\"fa fa-check\"></i> {$listAlarm[$d["ALARM"]]}</span>" : "<span class='text-danger'><i class=\"fa fa-warning\"></i> {$listAlarm[$d["ALARM"]]}</span>";
				$indikator = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='".($d['STATUS'] ==1? 'text-success text-hijau' : 'text-danger text-merah')."'><i class=\"fa fa-3x fa-circle\"></i> &nbsp;</span>";
				
				$file_prev_arr = base64_encode($this->Json->encode(array("filename"=>$d['FILE_PREV'],"time"=>$d['LAST_PARSED'])));

				$dt['NO'] = (++$no);
				$dt["NPWPD"]= $d["NPWPD"];
				$dt["DEVICEID"]= "<a style='cursor:pointer' onclick='javascript:openModal({$json})'>{$d["DEVICEID"]}</a>";
				$dt["COMPANY"]= $d["COMPANY"];
				$dt["ALARM"]= $alarm;
				$dt["LAST_CHUNKS"]= empty($d['LAST_CHUNKS'])? '' : $d['LAST_CHUNKS'].$this->humanTiming($d['LAST_CHUNKS']);
				
				$dt["LAST_FILES"]= "<span title='{$d['LAST_FILE']}'>".$this->shortString($d['LAST_FILE'])."</span>";
				$dt["FILES_PREV"]= "<a href='javascript:void(0)' onclick='javascript:filePreview(\"{$file_prev_arr}\")' title=\"{$d['FILE_PREV']}\">".$this->shortString($d['FILE_PREV'])."</a>";
				$dt["INDIKATOR"]= $indikator;
				
				$date = strtotime($d['LAST_PARSED']);
				$now = strtotime(date('d-m-Y h:i:s'));
				$interval = abs($now - $date);
				$hours = round($interval / 3600);
		
				$last_parsed = $d['LAST_PARSED'].$this->humanTiming($d['LAST_PARSED']);
				$dt["LAST_PARSED"]= ($hours < 25)  ? "<span class='text-primary' title='terjadi 12 s.d 24 jam terakhir'><i class=\"fa fa-clock-o\"></i> {$last_parsed}</span>" : (empty($d['LAST_PARSED'])? '' : "<span class='text-danger' title='lebih dari 24 jam!'><i class=\"fa fa-clock-o\"></i> {$last_parsed}</span>" );
				$rows[] = $dt;
				
			}
			mysqli_close($this->conn);
			$jTableResult = array();
			$jTableResult['Result'] = "OK";
			$jTableResult['TotalRecordCount'] = $recordCount;
			$jTableResult['Records'] = $rows;
			ob_clean();
			print $this->Json->encode($jTableResult);
		} catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
	}
	
	public function filePreview(){
		$arr_config = $this->get_config_value($this->_a);
		$ftpHost = $arr_config['PATDA_TB_FTVHOSTPORT'];
		$ftpUser = $arr_config['PATDA_TB_FTVUSERNAME'];
		$ftpPwd = $arr_config['PATDA_TB_FTVPASSWORD'];
		
	    $file = $this->Json->decode(base64_decode($_REQUEST['file']));
	    $time = str_replace("-","",substr($file->time, 0, 10));
	    
		$ftp_server = $ftpHost;
		
		$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
		if(@ftp_login($ftp_conn, $ftpPwd, $ftpPwd)){
			$filename = $file->filename;
			$arrfile = explode(".",$file->filename);
			$type = $arrfile[1];
			
			$local_file = '/var/www/html/function/PATDA-V1/monitoring/tapbox_file_preview/'.$filename;
			$server_file = "/home/prasimax/{$type}/completed/{$time}/{$filename}";

			if (ftp_get($ftp_conn, $local_file, $server_file, FTP_BINARY)) {
				$data = file_get_contents($local_file);
			} else {
				$data = "There was a problem on get data.\n";
			}
		}else{
			$data = "Connection failed.\n";
		}
		
		ftp_close($ftp_conn); 
		
		$res['data'] = $data;
		echo $this->Json->encode($res);
	}
	
	public function getTotalDevice(){
		
		$this->getConnection();
		$total = 0;
		$query = "SELECT COUNT(DeviceId) as TOTAL FROM DEVICE";
		$res = mysqli_query($this->conn, $query);
		if($d = mysqli_fetch_assoc($res)){
			$total = $d['TOTAL'];
		}
		return $total;
	}
	
	public function getDevice(){
		try{
			$this->getConnection();
			$query = "SELECT * FROM DEVICE";
			$res = mysqli_query($this->conn, $query);

			$no = 0;
			$rows = array();
			while($d = mysqli_fetch_assoc($res)){
				$dt = array();
				
				$dt['NO'] = (++$no);
				$dt["DEVICEID"]= $d["DeviceId"];
				$dt["COMPANYNAME"]= $d["CompanyName"];
				$dt["ADDRESS"]= $d["Address"];
				$rows[] = $dt;
			}
			
			mysqli_close($this->conn);
			$jTableResult = array();
			$jTableResult['Result'] = "OK";
			$jTableResult['TotalRecordCount'] = 0;
			$jTableResult['Records'] = $rows;
			ob_clean();
			print $this->Json->encode($jTableResult);
		} catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
	}
	
	public function filtering_device_dashboard($id) {
        $DIR = "PATDA-V1";
        $arr_pajak = $this->arr_pajak_tapbox;

        $html = "<script>
				jQuery(document).ready(function(){                      
						$('#CPM_NPWPD-{$id}').autocomplete({                    
							source : function(request, response, url) {
										$.ajax({
											url: 'view/{$DIR}/monitoring/trans-tapbox/svc-list-npwpd.php',
											dataType: \"json\",
											data : {jns :$('#CPM_JENIS_PAJAK-{$id}').val(), term:request.term },
											type: \"POST\",
											success: function (data) {
												response($.map(data, function(item) {
													return { label: item.label, value: item.value};
												}));
											}
										});
									},
							autoFocus: true,
					minLength: 0
					}).data( \"ui-autocomplete\" )._renderItem = function( ul, item ) {
						return $( \"<li></li>\" )
					.data( \"item.autocomplete\", item )
					.append( $(\"<a></a>\").html(item.label))
					.appendTo( ul );
				};
            });
            </script>
            <div class=\"filtering\">
                    <form>
                        Jenis Pajak : <select name=\"CPM_JENIS_PAJAK-{$id}\" id=\"CPM_JENIS_PAJAK-{$id}\">";
        foreach ($arr_pajak as $key => $val) {
            $html.= "<option value='{$key}'>{$val}</option>";
        }
        $html .= "</select> NPWPD / Device ID : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >
                        <button type=\"submit\" id=\"cari-table-status\">Cari</button>
                    </form>
                </div> ";
        return $html;
    }
    
	# dashboard monitoring
	public function getDeviceDashboard() {
		try {
			$JENIS_PAJAK = (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? $_REQUEST['CPM_JENIS_PAJAK'] : "PARKIR";
			
			$TAHUN_PAJAK = date('Y');
			$MASA_PAJAK = date('m');
			
			$where = "WHERE CPM_AKTIF='1' AND (CPM_DEVICE_ID IS NOT NULL AND CPM_DEVICE_ID !='') ";
            $where.= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? "AND (CPM_NPWPD = '{$_REQUEST['CPM_NPWPD']}' OR CPM_DEVICE_ID LIKE '%{$_REQUEST['CPM_NPWPD']}%')" : "";
            $query = "SELECT CPM_NPWPD, CPM_NAMA_OP, CPM_DEVICE_ID from PATDA_{$JENIS_PAJAK}_PROFIL {$where}
					LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            
			$rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            $result = mysqli_query($this->Conn, $query);
			
			$result_count = mysqli_query($this->Conn, "SELECT COUNT(CPM_NPWPD) AS RecordCount from PATDA_{$JENIS_PAJAK}_PROFIL {$where} ");
			$row = mysqli_fetch_assoc($result_count);
            $recordCount = $row['RecordCount'];
            
            $img_loading = "<img src='inc/PATDA-V1/img/ui-anim_basic_16x16.gif'>";
            
            while ($row = mysqli_fetch_assoc($result)) {
			 $row['TAHUN_PAJAK'] = $TAHUN_PAJAK;
                $row['MASA_PAJAK'] = $MASA_PAJAK;

                $json = base64_encode($this->Json->encode($row));
                $arr_deviceid = explode(";",$row['CPM_DEVICE_ID']);
                $str_deviceid = "";
                foreach($arr_deviceid as $device){
					$device = trim($device);
					$str_deviceid .= "<span class='deviceidstr id_{$device}' deviceid='{$device}'>{$device}</span> <span class='loadsign'></span><br/>";
                }

                $row = array_merge($row, array("NO" => ++$no));
                $row['CPM_NPWPD'] = $row['CPM_NPWPD'];
                $row['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
                $row['CPM_DEVICE_ID'] = $str_deviceid;
                $row['CPM_PERHARI'] = "<input type='button' class='btn btn-default btn-xs' onclick=\"javascript:getDetHarianTranTapbox('{$json}')\" value='Transaksi Hari ini'>";
                $row['CPM_PERBULAN'] = "<input type='button' class='btn btn-default btn-xs' onclick=\"javascript:getTranBulanIniTapbox('{$json}')\" value='Transaksi Bulan ini'>";
                $rows[] = $row;
            }

            $jTableResult = array();
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
	
     public function getDeviceStatus(){
		$arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_TB_DBNAME'];
		$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
		$dbTable = $arr_config['PATDA_TB_TABLE'];
		$dbUser = $arr_config['PATDA_TB_USERNAME'];
		$warningMinutes = $arr_config['PATDA_TB_WARNING_MINUTES'];
		 
		$Conn_gw = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
        //mysqli_select_db($dbName, $Conn_gw);
          
		$allid = isset($_POST['allid'])? substr($_POST['allid'],0,strlen($_POST['allid'])-1) : ""; #substr menghilangkan tanda '|' di akhir 
		
		$arr_id = explode("|",$allid); #memecah semua device id menjadi array untuk keperluan query
		$where_id = "'".implode("','",$arr_id)."'"; #untuk dipakai di query DeviceId in ('123213','123213','13213')
				
		$data['id'] = $allid;
		$where = "DeviceId in ({$where_id})";
		
		$query = "select 
					DeviceId,
					ServerTimeStamp
					from LAST_TRANSACTION
					WHERE {$where}";
          #echo $query;exit;
		$result = mysqli_query($Conn_gw, $query);
		$data = array();
		while($row = mysqli_fetch_assoc($result)){
			$data[$row['DeviceId']] = $this->getDeviceIsWarning($warningMinutes, $row['DeviceId'], $row['ServerTimeStamp']); 
		}
		
		$data_before_update = array();
		foreach($arr_id as $key=>$val){
			$data_before_update[$val] = "<i class=\"fa fa-remove text-primary\" title=\"deviceid tidak terdaftar\"></i> {$val}";
		}
		$res['data'] = array_merge($data_before_update,$data);
		
		$waktu = "";
		if($warningMinutes>60){
			$waktu = round($warningMinutes/60);
			if($waktu > 24){
				$waktu = round($waktu/24);
				$waktu = "{$waktu} hari";
			}else{
				$waktu = "{$waktu} jam";
			}
		}else{
			$waktu = "{$warningMinutes} menit";
		}
		
		$res['warningMinutes'] = $waktu;
		print $this->Json->encode($res);
	}
	
	private function getDeviceIsWarning($warningMinutes, $deviceid, $ServerTimeStamp){
		$date = strtotime($ServerTimeStamp);
		$now = strtotime(date('d-m-Y h:i:s'));
		$interval  = abs($now - $date);
		$minutes   = round($interval / 60);
		$res = $minutes > $warningMinutes? 1:0;
		return $res? "<i class=\"fa fa-warning text-danger\" title=\"tanggal transaksi terakhir {$ServerTimeStamp}\"></i> {$deviceid}" : "<i class=\"fa fa-check text-success\" title=\"tanggal transaksi terakhir {$ServerTimeStamp}\"></i> {$deviceid}";
	}

}
?>

