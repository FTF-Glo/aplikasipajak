<?php
date_default_timezone_set("Asia/Jakarta");

$DIR = "PATDA-V1";
$modul = "payment_pembatalan";
$submodul = "";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

class Pembatalan 
{
    public $db = ONPAYS_DBNAME;
    public $user = ONPAYS_DBUSER;
    public $pass = ONPAYS_DBPWD;
    public $host = ONPAYS_DBHOST;
    public $conn;

    function __construct(){
		$this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
        
		if($this->conn){
		    // echo "Koneksi database mysql dan php berhasil.";
		}else{
			echo "Koneksi database mysql dan php GAGAL !";
		}
	}


	public function check_kodebayar($kodebayar){
        $tgl = date("Y-m-d");

        $query = "SELECT (simpatda_dibayar+simpatda_denda) as total, wp_nama as nama, payment_code as kodebayar, IF(payment_flag=1, 'LUNAS', 'BELUM LUNAS') AS status_bayar, wp_alamat as alamat_wp, op_alamat as alamat_op, payment_bank_code as bank FROM simpatda_gw where payment_code='$kodebayar'";
        $result = mysqli_query($this->conn, $query);
        $total = mysqli_num_rows($result);
        
        $data = (object) $_POST;

        if($total > 0){
            $data =mysqli_fetch_object($result);
            
            if($data->bank == '1' || $data->bank == '2' || $data->bank == '3'){
                $data->RC = '02';
            }else{

                if($data->status_bayar == 'BELUM LUNAS'){
                    $data->RC = '03';
                }else{
                    $kdbelakang = substr($data->kodebayar,-2);
                    $jenis_pajak = array(
                        "10" => "Air Bawah Tanah",
                        "05" => "Hiburan",
                        "03" => "Hotel",
                        "08" => "Mineral",
                        "09" => "Parkir",
                        "07" => "Jalan",
                        "06" => "Reklame",
                        "04" => "Restoran",
                        "11" => "Sarang Burung wallet",
                    );
                        
                    $data->jp = "Pajak ".$jenis_pajak[$kdbelakang];
                    $data->total = number_format($data->total,2,',','.');
                    $data->RC = '00';
                }
            }

        }else{
            $data->RC = '01';
        }

        $data = json_encode($data, true);
        return $data;  
    }


    public function batalkan_pembayaran($kodebayar){
        $data = (object) $_POST;

        if($kodebayar != ''){
            $query = "UPDATE simpatda_gw SET payment_flag = '0', 
            payment_bank_code = '',
                    operator = null,
                    patda_collectible = 0,
                    patda_total_bayar = 0,
                    patda_denda = 0,
                    payment_paid = null,
                    PAYMENT_SETTLEMENT_DATE = null,
                    payment_ref_number = null,
                    payment_merchant_code = null
                    WHERE payment_code = '$kodebayar'";
            $result = mysqli_query($this->conn, $query);

            if($result){
                $data->RC = '00';
            }else{
                $data->RC = '01';
            }
        }else{
            $data->RC = '01';
        }

        $data = json_encode($data, true);
        return $data;  
    }

}