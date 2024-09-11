<?php
/**
Modified :
1. Penambahan konfigurasi nama badan pengelola :
	- modified by : RDN
	- date : 2016/01/03
	- function : print_sptpd, print_sspd
*/
class LaporPajak extends Pajak {
    #field
    #restoran

    public $id_pajak = 3;

    function __construct() {
        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysql_escape_string(trim($b));
        }
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
        if(isset($_REQUEST['CPM_NPWPD']))$_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
    }

    public function save() {
        #insert pajak baru
        $arr_config = $this->get_config_value($this->_a);
		$dbName = $arr_config['PATDA_TB_DBNAME'];
		$dbHost = $arr_config['PATDA_TB_HOSTPORT'];
		$dbPwd = $arr_config['PATDA_TB_PASSWORD'];
		$dbTable = $arr_config['PATDA_TB_TABLE'];
		$dbUser = $arr_config['PATDA_TB_USERNAME'];
	
		$conn = mysql_connect($dbHost, $dbUser, $dbPwd, true);
		mysql_select_db($dbName, $conn);
		
		$arr_rekening = $this->getRekening("4.1.1.2");
		$arr_jenis_kamar = $this->get_list_jenis_kamar();
		
		//echo '<pre>',print_r($arr_jenis_kamar,true),'</pre>';exit;
		
		$TransactionNumber = c_uuid();
		$TransactionDate = $this->TransactionDate;
		$TransactionAmount = str_replace(",", "", $this->TransactionAmount);
		$TransactionSource = 4;
		$TaxAmount = str_replace(",", "", $this->TaxAmount);
		$TaxInfo = 'PPN';
		$TaxType = 8;
		$DeviceId = '';
		$NPWPD = $this->CPM_NPWPD;
		$NOP = $this->CPM_NOP;
		$NotAdmitReason = '';
		$Status = 0;
		$Golongan = $this->CPM_REKENING. ' - '.$arr_rekening['ARR_REKENING'][$this->CPM_REKENING]['nmrek'];
		$JumlahMeja = str_replace(",", "", $this->JumlahMeja); 
		$JumlahKursi = str_replace(",", "", $this->JumlahKursi); 
		$JumlahPengunjung = str_replace(",", "", $this->JumlahPengunjung); 
		$TransactionDescription = '';
		$TarifPajak = $this->CPM_TARIF_PAJAK;
		$NamaWP = $this->CPM_NAMA_WP;
		$NamaOP = $this->CPM_NAMA_OP;
		
		$query = sprintf("INSERT INTO TRANSACTION
			(TransactionNumber, TransactionDate, TransactionAmount, TransactionSource, TransactionDescription, TaxAmount, TaxInfo, TaxType, DeviceId, NPWPD, NOP, NotAdmitReason)
			VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
			$TransactionNumber, $TransactionDate, $TransactionAmount, $TransactionSource, $TransactionDescription, $TaxAmount, $TaxInfo, $TaxType, $DeviceId, $NPWPD, $NOP, $NotAdmitReason
		);
		$res = mysql_query($query, $conn) or die(mysql_error());

		if($res){
			$ID = c_uuid();
			$query = sprintf("INSERT INTO TRANSACTION_ATR_RESTORAN
				(ID, TransactionNumber, Golongan, NamaWP, NamaOP, JumlahMeja, JumlahKursi, JumlahPengunjung, TransactionAmount, TarifPajak, TaxAmount, Status)
				VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
				$ID, $TransactionNumber, $Golongan, $NamaWP, $NamaOP, $JumlahMeja, $JumlahKursi, $JumlahPengunjung, $TransactionAmount, $TarifPajak, $TaxAmount, $Status
			);
			$res = mysql_query($query, $conn) or die(mysql_error());
		}
		
		return $res;
        
    }


}

?>
