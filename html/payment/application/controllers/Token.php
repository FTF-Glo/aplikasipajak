
<?php
defined('BASEPATH') or exit('No direct script access allowed');

date_default_timezone_set("Asia/Jakarta");

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/HitungDenda.php';

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/JWT.php';
require APPPATH . '/libraries/ExpiredException.php';
require APPPATH . '/libraries/BeforeValidException.php';
require APPPATH . '/libraries/SignatureInvalidException.php';




use Restserver\Libraries\REST_Controller;

use chriskacerguis\RestServer\RestController;
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;



class Token extends REST_Controller
{
    protected $time;
    private $counter = 1;
    private $current_month;
    protected $responseCode;
    protected $banks;
    protected $username;
    protected $password;

    const PBB_LUNAS = 'LUNAS';
    const PBB_BELUM_LUNAS = 'BELUM LUNAS';
    const PBB_CODE_BANK_LAMPUNG = '1';

    public function __construct()
    {
        parent::__construct();

        $this->username = $this->input->server('PHP_AUTH_USER');
        $this->password = $this->input->server('PHP_AUTH_PW');

        $this->time = date('Y-m-d H:i:s');
        $this->responseCode = require_once(APPPATH . '/config/response_code.php');
        $this->banks = require_once(APPPATH . '/config/banks.php');

        $this->spajak = $this->load->database('default', TRUE);
        $this->gw_ssb = $this->load->database('dbphtb', TRUE);
        $this->sw_ssb = $this->load->database('sw_ssb', TRUE);
    }


    protected function logins()
    {
        if (empty($this->username) || empty($this->password)) {
            header('Content-Type: application/json');
            $this->forbidden('05');
            die(json_encode(array('code' => '05', 'desc' => $this->getResponseDesc('05'))));
        }

        if (!$this->checkLogin($this->username, $this->password)) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('Content-Type: application/json');
            $this->forbidden('04');
            die(json_encode(array('code' => '04', 'desc' => $this->getResponseDesc('04'))));
        }
    }


    function configToken()
    {
        $cnf['exp'] = 300; //milisecond
        $cnf['secretkey'] = 'MySecRetTOkEnFtfgLOBAlindO';
        return $cnf;
    }

    public function validate_user($username, $password)
    {
        $query = $this->db->get_where('user_api', array('username' => $username, 'password' => $password));
        // echo $this->db->last_query(); 
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getToken_post()
    {
        $username = $this->post('username');
        $password = $this->post('password');

        if ($this->validate_user($username, $password)) {
            $ip_address = $this->input->ip_address();
            $bearer_token = $this->input->get_request_header('Authorization');
            $exp = time() + 300;
            // var_dump(time());exit;
            $token = array(
                "iss" => 'apprestservice',
                "aud" => 'user',
                "iat" => time(),
                "nbf" => time(),
                "exp" => $exp,
                "timestamp" => time(),
                "response_code" => '00',
                "response_message" => 'Login success',
                "data" => array(
                    "username" => $username,
                )
            );
            $jwt = JWT::encode($token, $this->configToken()['secretkey']);
            // var_dump($jwt);exit;
            $output = [
                'status' => 200,
                'message' => 'Berhasil login',
                "token" => $jwt,
                "expireAt" => $token['exp']
            ];
            $expired_time = date("Y-m-d H:i:s", $token['exp']);
            $data = array('data' => array('username' => $username, 'current_token' => $jwt), 'response_code' => '00', 'response_message' => '“Login success', 'expired' => $expired_time,);
            log_message('info', 'API Call from IP: '.$ip_address.' with Create Bearer Token: '.$jwt);
            $this->response($data, 200);
        } else {
            $data = array('response_code' => '01', 'response_message' => 'Invalid username or password');
            $this->response($data, 05);
        }
    }

    public function authtoken(){
        $secret_key = $this->configToken()['secretkey']; 
        $token = null; 
        $authHeader = $this->input->request_headers()['Authorization'];  
        $arr = explode(" ", $authHeader); 
        $token = $arr[1];     
        // var_dump($arr);exit;  
        if ($token){
            try{
                $decoded = JWT::decode($token, $this->configToken()['secretkey'], array('HS256'));     
                if ($decoded){
                    return 'benar';
                }
            } catch (\Exception $e) {
                // var_dump($e);
                // echo 'Caught exception: ',  $e->getMessage(), "\n";
                // echo 'Trace: ' . $e->getTraceAsString();
                $result = array('pesan'=>'Kode Signature Tidak Sesuai');
                return 'salah';
                
            }
        }       
    }


    protected function getResponseDesc($code)
    {
        return isset($this->responseCode[$code]) ? $this->responseCode[$code]['description'] : $this->responseCode['default']['description'];
    }

    protected function getBank($code)
    {
        return isset($this->banks[$code]) ? $this->banks[$code] : $this->banks['default'];
    }

    protected function checkLogin($username, $password)
    {
        $validLogins = $this->config->item('rest_valid_logins');
        return isset($validLogins[$username]) && $validLogins[$username] == $password;
    }

    protected function simple($code, $description = null, $rc = null)
    {
        return $this->response(array(
            'code' => $code,
            'description' => $description !== null ? $description : $this->getResponseDesc($code)
        ), $rc !== null ? $rc : self::HTTP_OK);
    }

    protected function withData($data, $code, $description = null, $rc = null)
    {
        return $this->withDataRaw(array('data' => $data), $code, $description, $rc);
    }

    protected function withDataRaw($data, $code, $description = null, $rc = null)
    {
        return $this->response(
            array_merge(array(
                'code'        => $code,
                'description' => $description !== null ? $description : $this->getResponseDesc($code),
            ), $data),
            $rc !== null ? $rc : self::HTTP_OK
        );
    }

    protected function withDataND($data, $code, $description = null, $rc = null)
    {
        return $this->withDataNoDescription(array('data' => $data), $code, $description, $rc);
    }

    protected function withDataNoDescription($data, $code, $description = null, $rc = null)
    {
        return $this->response(
            array_merge(array(
                'code'        => $code,
            ), $data),
            $rc !== null ? $rc : self::HTTP_OK
        );
    }

    public function unauthorized($code, $description = null)
    {
        return $this->simple($code, $description, self::HTTP_UNAUTHORIZED);
    }

    public function forbidden($code, $description = null, $rc = null)
    {
        return $this->simple($code, $description, self::HTTP_FORBIDDEN);
    }

    protected function maintenance($code = 'MT', $description = 'Sistem sedang maintenance.')
    {
        return $this->forbidden($code, $description);
    }

    /**
     * Mata pajak methods
     */

    protected function isInputValidPBB($nop, $tahun = '')
    {
        if ($tahun == '' || empty($tahun)) {
            return (!is_numeric($nop) || strlen($nop) !== 18);
        } else {
            return (!is_numeric($nop) || strlen($nop) !== 18) || (!is_numeric($tahun) || strlen($tahun) !== 4);
        }
    }


    protected function inquirybniSPajak($area_code, $tax_type, $id)
    {

        if ($this->authtoken() == 'salah') {
            return $this->response(array('kode' => '05', 'pesan' => 'Token tidak sesuai atau sudah expired/expired', 'data' => []), '05');
            die();
        }

        $kdbelakang = substr($id, -2);


        switch ($tax_type) {
            case "05":
                $pjk = 'hiburan'; // hiburan 05;
                $type = '02';
                break;
            case "03":
                $pjk = 'hotel'; // hotel 03;
                $type = '03';
                break;
            case "08":
                $pjk = 'mineral'; // mineral 08;
                $type = '04';
                break;
            case "09":
                $pjk = 'parkir'; // parkir 09;
                $type = '05';
                break;
            case "07":
                $pjk = 'jalan'; //jalan 07;
                $type = '06';
                break;
            case "06":
                $pjk = 'reklame'; // reklame 06;
                break;
            case "04":
                $pjk = 'restoran'; // restoran 04;
                $type = '08';
                break;
            case "10":
                $pjk = 'airbawahtanah'; // airbawahtanah 10;
                $type = '01';
                break;
            case "11":
                $pjk = 'walet'; // wallet 11;
                $type = '09';
                break;
        }


        // var_dump($kdbelakang,$pjk,$type );exit;
        $this->spajak->select('gw.area_code,gw.tax_type,gw.payment_code as billing_code,
            gw.npwpd as npwpd,
            gw.op_nomor as no_objek_pajak,
            gw.wp_nama as nama_wajib_pajak,
            gw.wp_alamat as alamat_wajib_pajak,
            gw.op_nama as nama_objek_pajak,
            gw.op_alamat As alamat_objek_pajak,
            gw.simpatda_dibayar as nilai,gw.patda_total_bayar,
            gw.simpatda_denda as denda,payment_ref_number,
            gw.simpatda_tahun_pajak as simpatda_tahun_pajak,
            (gw.simpatda_dibayar+gw.simpatda_denda) as jumlah_pajak_dibayar,
            gw.masa_pajak_awal as pajak_awal,
            gw.masa_pajak_akhir as pajak_akhir,
            gw.simpatda_rek as kode_rek, gw.operator,
            gw.expired_date ,
            gw.patda_misc_fee ,
            pr.CPM_KODEPOS_OP ,
            IF(gw.payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar,
            
            gw.id_switching as id_switching,
            wp.CPM_RTRW_WP as rt_rw,
            type.nmheader3 as jenis,
            type.nmrek as nama_rek,
            kecc.CPM_KECAMATAN as kecamatan_wp,
            kell.CPM_KELURAHAN as kelurahan_wp,

            pr.CPM_RT_OP as CPM_RT_OP,
            pr.CPM_RW_OP as CPM_RW_OP,
            kec.CPM_KECAMATAN as kecamatan_op,
            kel.CPM_KELURAHAN as kelurahan_op,
            ');
        $this->spajak->from('simpatda_gw gw');
        $this->spajak->join('patda_wp wp', 'gw.npwpd = wp.CPM_NPWPD');
        $this->spajak->join('patda_mst_kecamatan kec', 'gw.kecamatan_op = kec.CPM_KEC_ID', 'left');
        $this->spajak->join('patda_mst_kelurahan kel', 'gw.kelurahan_op = kel.CPM_KEL_ID', 'left');

        $this->spajak->join('patda_mst_kecamatan kecc', 'gw.kecamatan_wp = kec.CPM_KEC_ID', 'left');
        $this->spajak->join('patda_mst_kelurahan kell', 'gw.kelurahan_wp = kel.CPM_KEL_ID', 'left');

        $this->spajak->join('patda_rek_permen13 type', 'gw.simpatda_rek = type.kdrek');
        // $this->spajak->join('patda_rek_permen13 rek', 'gw.simpatda_rek = rek.kdrek');
        $this->spajak->join("patda_{$pjk}_doc_tranmain tr", "gw.id_switching = tr.CPM_TRAN_{$pjk}_ID", 'left');
        $this->spajak->join("patda_{$pjk}_doc doc", "tr.CPM_TRAN_{$pjk}_ID = doc.CPM_ID", 'left');
        $this->spajak->join("patda_{$pjk}_profil pr", "doc.CPM_ID_PROFIL = pr.CPM_ID", 'left');

        $this->spajak->where('gw.payment_code', $id);
        $this->spajak->where('gw.area_code', $area_code);  // where kedua
        $this->spajak->where('type.id_sw', $tax_type);  // where kedua
        $rows = $this->spajak->get()->result();
        // $this->response($data, 200 );
        // $rows = $this->spajak->where('payment_code', $id)->get()->result();
        // echo $this->spajak->last_query(); 
        if (empty($rows)) {
            return false;
        }
        $id_switching = $rows[0]->id_switching;

        if ($kdbelakang == '06') {
            $this->spajak->select('B.CPM_NOP as nop, A.CPM_ATR_JUDUL as judul, A.CPM_ATR_LOKASI as lokasi, B.CPM_NAMA_OP as nama_op_reklame, B.CPM_ALAMAT_OP as alamat_op_reklame');
            $this->spajak->from('patda_reklame_doc_atr A');
            $this->spajak->join('patda_reklame_profil B', 'A.CPM_ATR_ID_PROFIL = B.CPM_ID');
            $rows_profil = $this->spajak->where('A.CPM_ATR_REKLAME_ID', $id_switching)->get()->result();

            foreach ($rows_profil as $rek) {
                $details[] = array(
                    "no_objek_pajak" => $rek->nop,
                    "nama_objek_pajak" => $rek->nama_op_reklame,
                    "alamat_objek_pajak" => $rek->alamat_op_reklame,
                    "judul_reklame" => $rek->judul,
                    "lokasi_reklame" => $rek->lokasi,
                );
            }
        } else {

            foreach ($rows as $row) {
                $details[] = array(
                    "no_objek_pajak" => $row->no_objek_pajak,
                    "nama_objek_pajak" => $row->nama_objek_pajak,
                    "alamat_objek_pajak" => $row->alamat_objek_pajak,
                );
            }
        }


        foreach ($rows as $row) {
            $date_awal = date_create($row->pajak_awal);
            $date_awal = date_format($date_awal, "d-m-Y");
            $date_akhir = date_create($row->pajak_akhir);
            $date_akhir = date_format($date_akhir, "d-m-Y");

            $hasil = array(
                "area_code" => $row->area_code,
                "tax_type" => '00' .  $tax_type,
                "billing_code" => $row->billing_code,
                "refnum" => $row->payment_ref_number,
                "total" => $row->jumlah_pajak_dibayar,
                "bill_amount" => $row->nilai,
                "penalty" => $row->denda,
                "name" => $row->nama_wajib_pajak,
                "address" => $row->alamat_wajib_pajak,
                "op_address" => $row->alamat_objek_pajak,
                "nop" => $row->no_objek_pajak,
                "op_name" => $row->nama_objek_pajak,
                "pajak_awal" => $date_awal,
                "pajak_akhir" => $date_akhir,
                "kode_rek" => $row->kode_rek,
                "nama_rek" => $row->nama_rek,
                "jenis" => $row->jenis,
                "due_date" => $row->expired_date,
                "misc_fee" => $row->patda_misc_fee ?: 0,

                "rt_rw" => $row->rt_rw,
                "kelurahan_wp" => $row->kelurahan_wp,
                "kecamatan_wp" => $row->kecamatan_wp,
                "kabupaten" => 'Way Kanan',
                "zip_code" => $row->CPM_KODEPOS_OP,
                "op_rt_rw" => $row->CPM_RT_OP . '/' . $row->CPM_RW_OP,
                "op_kelurahan" => $row->kelurahan_op,
                "op_kecamatan" => $row->kecamatan_op,
                "op_kabupaten" => 'Way Kanan',
                "discount" => 0,
                "tax_year" => $row->simpatda_tahun_pajak,
                "status_bayar" => $row->status_bayar

            );
        }

        return $hasil;
    }

    protected function paymentbniSPajak2($area_code, $tax_type, $id, $payment_amount)
    {

        $kdbelakang = substr($id, -2);
        switch ($tax_type) {
            case "05":
                $pjk = 'hiburan'; // hiburan 05;
                $type = '02';
                break;
            case "03":
                $pjk = 'hotel'; // hotel 03;
                $type = '03';
                break;
            case "08":
                $pjk = 'mineral'; // mineral 08;
                $type = '04';
                break;
            case "09":
                $pjk = 'parkir'; // parkir 09;
                $type = '05';
                break;
            case "07":
                $pjk = 'jalan'; //jalan 07;
                $type = '06';
                break;
            case "06":
                $pjk = 'reklame'; // reklame 06;
                break;
            case "04":
                $pjk = 'restoran'; // restoran 04;
                $type = '08';
                break;
            case "10":
                $pjk = 'airbawahtanah'; // airbawahtanah 10;
                $type = '01';
                break;
            case "11":
                $pjk = 'walet'; // wallet 11;
                $type = '09';
                break;
        }
        // var_dump($payment_amount,$pjk );exit;
        $this->spajak->select('gw.area_code,gw.tax_type,gw.payment_code as billing_code,
            gw.npwpd as npwpd,
            gw.op_nomor as no_objek_pajak,
            gw.wp_nama as nama_wajib_pajak,
            gw.wp_alamat as alamat_wajib_pajak,
            gw.op_nama as nama_objek_pajak,
            gw.op_alamat As alamat_objek_pajak,
            gw.simpatda_dibayar as nilai,
            gw.patda_total_bayar,
            gw.simpatda_denda as denda,
            payment_ref_number,
            gw.simpatda_tahun_pajak as simpatda_tahun_pajak,
            (gw.simpatda_dibayar+gw.simpatda_denda) as payment_amount,
            gw.masa_pajak_awal as pajak_awal,
            gw.masa_pajak_akhir as pajak_akhir,
            gw.simpatda_rek as kode_rek, gw.operator,
            gw.expired_date ,
            gw.patda_misc_fee ,
            
            pr.CPM_KODEPOS_OP ,
            IF(gw.payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar,
            gw.id_switching as id_switching,
            wp.CPM_RTRW_WP as rt_rw,
            rek.nmheader3 as jenis,
            rek.nmrek as nama_rek,
            kecc.CPM_KECAMATAN as kecamatan_wp,
            kell.CPM_KELURAHAN as kelurahan_wp,

            pr.CPM_RT_OP as CPM_RT_OP,
            pr.CPM_RW_OP as CPM_RW_OP,
            kec.CPM_KECAMATAN as kecamatan_op,
            kel.CPM_KELURAHAN as kelurahan_op,
            ');
        $this->spajak->from('simpatda_gw gw');
        $this->spajak->join('patda_wp wp', 'gw.npwpd = wp.CPM_NPWPD');
        $this->spajak->join('patda_mst_kecamatan kec', 'gw.kecamatan_op = kec.CPM_KEC_ID', 'left');
        $this->spajak->join('patda_mst_kelurahan kel', 'gw.kelurahan_op = kel.CPM_KEL_ID', 'left');

        $this->spajak->join('patda_mst_kecamatan kecc', 'gw.kecamatan_wp = kec.CPM_KEC_ID', 'left');
        $this->spajak->join('patda_mst_kelurahan kell', 'gw.kelurahan_wp = kel.CPM_KEL_ID', 'left');

        $this->spajak->join('simpatda_type type', 'gw.simpatda_type = type.id');
        $this->spajak->join('patda_rek_permen13 rek', 'gw.simpatda_rek = rek.kdrek');
        $this->spajak->join("patda_{$pjk}_doc_tranmain tr", "gw.id_switching = tr.CPM_TRAN_{$pjk}_ID", 'left');
        $this->spajak->join("patda_{$pjk}_doc doc", "tr.CPM_TRAN_{$pjk}_ID = doc.CPM_ID", 'left');
        $this->spajak->join("patda_{$pjk}_profil pr", "doc.CPM_ID_PROFIL = pr.CPM_ID", 'left');

        $this->spajak->where('gw.payment_code', $id);
        $this->spajak->where('gw.area_code', $area_code);
        $this->spajak->where('rek.id_sw', $tax_type);
        // $this->spajak->where('gw.simpatda_dibayar', $payment_amount);
        $this->spajak->where('(gw.simpatda_dibayar + gw.simpatda_denda) = ', $payment_amount);
        $rows = $this->spajak->get()->result();
        // $rows = $this->spajak->where('payment_code', $id)->get()->result();
        // echo $this->db->last_query();
        if (empty($rows)) {
            return false;
        }



        foreach ($rows as $row) {
            $date_awal = date_create($row->pajak_awal);
            $date_awal = date_format($date_awal, "d-m-Y");
            $date_akhir = date_create($row->pajak_akhir);
            $date_akhir = date_format($date_akhir, "d-m-Y");

            $hasil = array(
                "area_code" => $row->area_code,
                "tax_type" => $row->tax_type,
                "billing_code" => $row->billing_code,
                "refnum" => $row->payment_ref_number,
                "total" => $row->patda_total_bayar,
                "bill_amount" => $row->payment_amount,
                "penalty" => $row->denda,
                "tagihan_pajak" => $row->nilai,
                "name" => $row->nama_wajib_pajak,
                "address" => $row->alamat_wajib_pajak,
                "op_address" => $row->alamat_objek_pajak,
                "nop" => $row->no_objek_pajak,
                "op_name" => $row->nama_objek_pajak,
                "payment_refnum" => $row->payment_ref_number,
                "pajak_awal" => $date_awal,
                "pajak_akhir" => $date_akhir,
                "kode_rek" => $row->kode_rek,
                "nama_rek" => $row->nama_rek,
                "jenis" => $row->jenis,
                "due_date" => $row->expired_date,
                "misc_fee" => $row->patda_misc_fee ?:0,

                "rt_rw" => $row->rt_rw,
                "kelurahan" => $row->kelurahan_wp,
                "kecamatan" => $row->kecamatan_wp,
                "kabupaten" => 'Way Kanan',
                "zip_code" => $row->CPM_KODEPOS_OP,
                "op_rt_rw" => $row->CPM_RT_OP . '/' . $row->CPM_RW_OP,
                "op_kelurahan" => $row->kelurahan_op,
                "op_kecamatan" => $row->kecamatan_op,
                "op_kabupaten" => 'Way Kanan',
                "discount" => 0,
                "tax_year" => $row->simpatda_tahun_pajak,
                "status_bayar" => $row->status_bayar

            );
        }

        return $hasil;
    }

    protected function paymentbniSPajak($area_code, $tax_type, $id, $payment_amount)
    {

        $kdbelakang = substr($id, -2);
        switch ($tax_type) {
            case "05":
                $pjk = 'hiburan'; // hiburan 05;
                $type = '02';
                break;
            case "03":
                $pjk = 'hotel'; // hotel 03;
                $type = '03';
                break;
            case "08":
                $pjk = 'mineral'; // mineral 08;
                $type = '04';
                break;
            case "09":
                $pjk = 'parkir'; // parkir 09;
                $type = '05';
                break;
            case "07":
                $pjk = 'jalan'; //jalan 07;
                $type = '06';
                break;
            case "06":
                $pjk = 'reklame'; // reklame 06;
                break;
            case "04":
                $pjk = 'restoran'; // restoran 04;
                $type = '08';
                break;
            case "10":
                $pjk = 'airbawahtanah'; // airbawahtanah 10;
                $type = '01';
                break;
            case "11":
                $pjk = 'walet'; // wallet 11;
                $type = '09';
                break;
        }

        $current_year = date('y');
        $current_month = date('m');
        $current_day = date('d');

        // $last_no_urut = $this->spajak->select_max('gw.payment_ref_number ', 'A')
        //     ->select_max('rv.ref_number', 'B')
        //     ->from('simpatda_gw gw')
        //     ->join('bni_reversal rv', 'gw.payment_code = rv.payment_code', 'left')
        //     ->where('gw.payment_ref_number LIKE', $current_year . $current_month . $current_day . '%')
        //     ->or_where('rv.ref_number LIKE', $current_year . $current_month . $current_day . '%')
        //     ->order_by('gw.payment_ref_number', 'desc')
        //     ->limit(1)
        //     ->get()
        //     ->row();
        $last_no_urut = $this->spajak->select_max('gw.payment_ref_number ', 'A')
            ->select_max('rv.ref_number', 'B')
            ->from('simpatda_gw gw')
            ->join('bni_reversal rv', 'gw.payment_code = rv.payment_code', 'left')
            ->where("(gw.payment_ref_number LIKE '" . $current_year . $current_month . $current_day . "%' OR rv.ref_number LIKE '" . $current_year . $current_month . $current_day . "%')")
            ->order_by('gw.payment_ref_number', 'desc')
            ->limit(1)
            ->get()
            ->row();
        if (empty($last_no_urut)) {
            $data['payment_ref_number'] = $current_year . $current_month . $current_day . '00001';
        } else {
            $payment_ref_numbera = substr($last_no_urut->A, 6, 5);
            $payment_ref_numberb = substr($last_no_urut->B, 6, 5);
            if ($payment_ref_numbera > $payment_ref_numberb) {
                $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT) . $tax_type;
            } else {
                $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT) . $tax_type;
            }
        }
        // echo $this->spajak->last_query();
        // var_dump($payment_ref_numbera,payment_ref_numberb );exit;
        $this->spajak->select('gw.area_code,gw.tax_type,gw.payment_code as billing_code,
            gw.npwpd as npwpd,
            gw.op_nomor as no_objek_pajak,
            gw.wp_nama as nama_wajib_pajak,
            gw.wp_alamat as alamat_wajib_pajak,
            gw.op_nama as nama_objek_pajak,
            gw.op_alamat As alamat_objek_pajak,
            gw.simpatda_dibayar as nilai,
            gw.patda_total_bayar,
            gw.simpatda_denda as denda,
            payment_ref_number,
            gw.simpatda_tahun_pajak as simpatda_tahun_pajak,
            (gw.simpatda_dibayar+gw.simpatda_denda) as payment_amount,
            gw.masa_pajak_awal as pajak_awal,
            gw.masa_pajak_akhir as pajak_akhir,
            gw.simpatda_rek as kode_rek, gw.operator,
            gw.expired_date ,
            gw.patda_misc_fee ,
            
            pr.CPM_KODEPOS_OP ,
            IF(gw.payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar,
            gw.id_switching as id_switching,
            wp.CPM_RTRW_WP as rt_rw,
            rek.nmheader3 as jenis,
            rek.nmrek as nama_rek,
            kecc.CPM_KECAMATAN as kecamatan_wp,
            kell.CPM_KELURAHAN as kelurahan_wp,

            pr.CPM_RT_OP as CPM_RT_OP,
            pr.CPM_RW_OP as CPM_RW_OP,
            kec.CPM_KECAMATAN as kecamatan_op,
            kel.CPM_KELURAHAN as kelurahan_op,
            ');
        $this->spajak->from('simpatda_gw gw');
        $this->spajak->join('patda_wp wp', 'gw.npwpd = wp.CPM_NPWPD');
        $this->spajak->join('patda_mst_kecamatan kec', 'gw.kecamatan_op = kec.CPM_KEC_ID', 'left');
        $this->spajak->join('patda_mst_kelurahan kel', 'gw.kelurahan_op = kel.CPM_KEL_ID', 'left');

        $this->spajak->join('patda_mst_kecamatan kecc', 'gw.kecamatan_wp = kec.CPM_KEC_ID', 'left');
        $this->spajak->join('patda_mst_kelurahan kell', 'gw.kelurahan_wp = kel.CPM_KEL_ID', 'left');

        $this->spajak->join('simpatda_type type', 'gw.simpatda_type = type.id');
        $this->spajak->join('patda_rek_permen13 rek', 'gw.simpatda_rek = rek.kdrek');
        $this->spajak->join("patda_{$pjk}_doc_tranmain tr", "gw.id_switching = tr.CPM_TRAN_{$pjk}_ID", 'left');
        $this->spajak->join("patda_{$pjk}_doc doc", "tr.CPM_TRAN_{$pjk}_ID = doc.CPM_ID", 'left');
        $this->spajak->join("patda_{$pjk}_profil pr", "doc.CPM_ID_PROFIL = pr.CPM_ID", 'left');

        $this->spajak->where('gw.payment_code', $id);
        $this->spajak->where('gw.area_code', $area_code);
        $this->spajak->where('rek.id_sw', $tax_type);
        // $this->spajak->where('gw.simpatda_dibayar', $payment_amount);
        $this->spajak->where('(gw.simpatda_dibayar + gw.simpatda_denda) = ', $payment_amount);
        $rows = $this->spajak->get()->result();
        // $rows = $this->spajak->where('payment_code', $id)->get()->result();
        // echo $this->db->last_query();
        if (empty($rows)) {
            return false;
        }



        foreach ($rows as $row) {
            $date_awal = date_create($row->pajak_awal);
            $date_awal = date_format($date_awal, "d-m-Y");
            $date_akhir = date_create($row->pajak_akhir);
            $date_akhir = date_format($date_akhir, "d-m-Y");

            $hasil = array(
                "area_code" => $row->area_code,
                "tax_type" => '00'.$tax_type,
                "billing_code" => $row->billing_code,
                "refnum" =>  $data['payment_ref_number'],
                "total" => $row->payment_amount,
                "bill_amount" => $row->nilai,
                "penalty" => $row->denda,
                "tagihan_pajak" => $row->nilai,
                "name" => $row->nama_wajib_pajak,
                "address" => $row->alamat_wajib_pajak,
                "op_address" => $row->alamat_objek_pajak,
                "nop" => $row->no_objek_pajak,
                "op_name" => $row->nama_objek_pajak,
                "payment_refnum" => $data['payment_ref_number'],
                "pajak_awal" => $date_awal,
                "pajak_akhir" => $date_akhir,
                "kode_rek" => $row->kode_rek,
                "nama_rek" => $row->nama_rek,
                "jenis" => $row->jenis,
                "due_date" => $row->expired_date,
                "misc_fee" => $row->patda_misc_fee ?: 0,

                "rt_rw" => $row->rt_rw,
                "kelurahan" => $row->kelurahan_wp,
                "kecamatan" => $row->kecamatan_wp,
                "kabupaten" => 'Way Kanan',
                "zip_code" => $row->CPM_KODEPOS_OP,
                "op_rt_rw" => $row->CPM_RT_OP . '/' . $row->CPM_RW_OP,
                "op_kelurahan" => $row->kelurahan_op,
                "op_kecamatan" => $row->kecamatan_op,
                "op_kabupaten" => 'Way Kanan',
                "discount" => 0,
                "tax_year" => $row->simpatda_tahun_pajak,
                "status_bayar" => $row->status_bayar

            );
        }
        // $response->message = 'success';
        if ($hasil['status_bayar'] == 'BELUM LUNAS') {
            $response = ['response_code' => '00', 'data' => $hasil];
            $response = array_merge($response, ['message' => 'success']);

            header('Content-Type: application/json');
            echo json_encode($response);
        }
        return $hasil;
    }
    protected function datareversalbniSPajak($area_code, $tax_type, $id, $refnum)
    {

        $kdbelakang = substr($id, -2);
        switch ($tax_type) {
            case "05":
                $pjk = 'hiburan'; // hiburan 05;
                $type = '02';
                break;
            case "03":
                $pjk = 'hotel'; // hotel 03;
                $type = '03';
                break;
            case "08":
                $pjk = 'mineral'; // mineral 08;
                $type = '04';
                break;
            case "09":
                $pjk = 'parkir'; // parkir 09;
                $type = '05';
                break;
            case "07":
                $pjk = 'jalan'; //jalan 07;
                $type = '06';
                break;
            case "06":
                $pjk = 'reklame'; // reklame 06;
                break;
            case "04":
                $pjk = 'restoran'; // restoran 04;
                $type = '08';
                break;
            case "10":
                $pjk = 'airbawahtanah'; // airbawahtanah 10;
                $type = '01';
                break;
            case "11":
                $pjk = 'walet'; // wallet 11;
                $type = '09';
                break;
        }
        // var_dump($refnum,$pjk );exit;
        $this->spajak->select('gw.area_code,gw.tax_type,gw.payment_code as billing_code,
            gw.npwpd as npwpd,
            gw.op_nomor as no_objek_pajak,
            gw.wp_nama as nama_wajib_pajak,
            gw.wp_alamat as alamat_wajib_pajak,
            gw.op_nama as nama_objek_pajak,
            gw.op_alamat As alamat_objek_pajak,
            gw.simpatda_dibayar as nilai,
            gw.patda_total_bayar,
            gw.simpatda_denda as denda,
            payment_ref_number,
            (gw.simpatda_dibayar+gw.simpatda_denda) as payment_amount,
            IF(payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar
            
       
            ');
        $this->spajak->from('simpatda_gw gw');
        $this->spajak->join('patda_wp wp', 'gw.npwpd = wp.CPM_NPWPD');
        $this->spajak->join('patda_mst_kecamatan kec', 'gw.kecamatan_op = kec.CPM_KEC_ID', 'left');
        $this->spajak->join('patda_mst_kelurahan kel', 'gw.kelurahan_op = kel.CPM_KEL_ID', 'left');

        $this->spajak->join('patda_mst_kecamatan kecc', 'gw.kecamatan_wp = kec.CPM_KEC_ID', 'left');
        $this->spajak->join('patda_mst_kelurahan kell', 'gw.kelurahan_wp = kel.CPM_KEL_ID', 'left');

        $this->spajak->join('simpatda_type type', 'gw.simpatda_type = type.id');
        $this->spajak->join('patda_rek_permen13 rek', 'gw.simpatda_rek = rek.kdrek');
        $this->spajak->join("patda_{$pjk}_doc_tranmain tr", "gw.id_switching = tr.CPM_TRAN_{$pjk}_ID", 'left');
        $this->spajak->join("patda_{$pjk}_doc doc", "tr.CPM_TRAN_{$pjk}_ID = doc.CPM_ID", 'left');
        $this->spajak->join("patda_{$pjk}_profil pr", "doc.CPM_ID_PROFIL = pr.CPM_ID", 'left');

        $this->spajak->where('gw.payment_code', $id);
        $this->spajak->where('gw.area_code', $area_code);
        $this->spajak->where('gw.payment_ref_number', $refnum);
        $this->spajak->where('rek.id_sw', $tax_type);
        $rows = $this->spajak->get()->result();
        // echo $this->spajak->last_query();exit; 
        // $rows = $this->spajak->where('payment_code', $id)->get()->result();
        if (empty($rows)) {
            return false;
        }


        foreach ($rows as $row) {


            $hasil = array(
                "area_code" => $row->area_code,
                "tax_type" => $row->tax_type,
                "billing_code" => $row->billing_code,
                "refnum" => $row->payment_ref_number,
                "total" => $row->patda_total_bayar,
                "bill_amount" => $row->payment_amount,
                "penalty" => $row->denda,
                "tagihan_pajak" => $row->nilai,
                "name" => $row->nama_wajib_pajak,
                "address" => $row->alamat_wajib_pajak,
                "op_address" => $row->alamat_objek_pajak,
                "nop" => $row->no_objek_pajak,
                "op_name" => $row->nama_objek_pajak,
                "payment_refnum" => $row->payment_ref_number,



                "status_bayar" => $row->status_bayar

            );
        }

        return $hasil;
    }


    protected function updatebniSPajak($inquiry, $params)
    {
        $data = $inquiry;
        // var_dump($data);exit;
        $uniqueNumber = uniqid();

        // $current_year = date('Y');
        // $current_month = date('m');

        // $last_no_urut = $this->spajak->select_max('gw.payment_ref_number ','A')
        //                 ->select_max('rv.ref_number', 'B')
        //                 ->from('simpatda_gw gw')
        //                 ->join('bni_reversal rv','gw.payment_code = rv.payment_code','left')
        //                 ->where('gw.payment_ref_number LIKE', $current_year . $current_month . '%')
        //                 ->or_where('rv.ref_number LIKE', $current_year . $current_month . '%')
        //                 ->order_by('gw.payment_ref_number', 'desc')
        //                 ->limit(1)
        //                 ->get()
        //                 ->row();
        // if (empty($last_no_urut)) {
        //     $data['payment_ref_number'] = $current_year . $current_month . '00001';
        // } else {
        //     $payment_ref_numbera = substr($last_no_urut->A, -5);
        //     $payment_ref_numberb = substr($last_no_urut->B, -5);


        //     if($payment_ref_numbera > $payment_ref_numberb){
        //         $data['payment_ref_number'] = $current_year . $current_month . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT);
        //     }else{
        //         $data['payment_ref_number'] = $current_year . $current_month . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT);
        //     }

        // }





        // echo $last_ref_number; exit;
        // echo $this->spajak->last_query(); exit;
        $where = array(
            "payment_code" => $data['billing_code'],
            "simpatda_dibayar" => $data['bill_amount'],

        );

        $data = array(
            "payment_flag" => '1',
            "payment_bank_code" => $params['payment_bank_code'],
            "operator" => $params['operator'],
            "patda_collectible" => $data['tagihan_pajak'],
            "patda_total_bayar" => $data['bill_amount'],
            "patda_denda" => $data['penalty'],
            "payment_paid" => $params['new_payment_paid'],
            "PAYMENT_SETTLEMENT_DATE" => $params['payment_settlement_date'],
            "payment_merchant_code" => $params['new_channel'],
            "payment_ref_number" => $data['refnum'],
            "payment_gw_refnum" => $data['refnum'],

        );

        $update_bayar = $this->spajak->update("simpatda_gw", $data, $where);

        if ($update_bayar) {
            // $this->datareversalbniSPajak( $data['payment_ref_number']);

            return true;
        } else {
            return false;
        }
    }

    protected function insertreversalSpajak($inquiry, $params)
    {
        $data = $inquiry;
        // var_dump($data);exit;
        $data = array(
            "payment_code" => $inquiry['billing_code'],
            "ref_number" => $inquiry['payment_refnum'],
            "tipe_pajak" => $inquiry['tax_type'],
            "created_at" => date('Y-m-d H:i:s'),

        );

        $insert_bayar = $this->spajak->insert("bni_reversal", $data);
        if ($insert_bayar) {
            return $this->reversalSPajak($inquiry, $params);
        } else {
            return false;
        }
    }

    protected function reversalSPajak($inquiry, $params)
    {
        $data = $inquiry;

        $where = array(
            "area_code" => $data['area_code'],
            "tax_type" => $data['tax_type'],
            "payment_code" => $data['billing_code'],
            "payment_ref_number" => $data['refnum'],
        );

        $data = array(
            "payment_flag" => '0',
            "payment_bank_code" => null,
            "operator" => null,
            "patda_collectible" => null,
            "patda_total_bayar" => null,
            "patda_denda" => null,
            "payment_paid" => null,
            "PAYMENT_SETTLEMENT_DATE" => null,
            "payment_merchant_code" => null,
            "payment_ref_number" => null,
            "payment_sw_refnum" => '1',

        );

        $update_bayar = $this->spajak->update("simpatda_gw", $data, $where);
        // echo $this->spajak->last_query(); 
        if ($update_bayar) {
            return true;
        } else {
            return false;
        }
    }

    protected function inquirybniBPHTB($area_code, $tax_type, $id)
    {

        $kdbelakang = substr($id, -2);
        // SELECT a.name, b.product, b.quantity
        // FROM database1.users a
        // LEFT JOIN database2.orders b
        // ON a.id = b.user_id;
        $this->gw_ssb->select('id_switching,
        payment_code as billing_code,
        payment_ref_number,
        op_luas_tanah,
        op_luas_bangunan,
        expired_date AS due_date, bphtb_notaris,
        wp_rt, wp_rw, wp_kelurahan, wp_kecamatan, wp_kabupaten, wp_kodepos,
        op_rt, op_rw, op_kelurahan, op_kecamatan, op_kabupaten,
        wp_noktp,wp_npwp,
        op_nomor as no_objek_pajak,
        wp_nama as nama_wajib_pajak,
        wp_alamat as alamat_wajib_pajak,
        op_letak As alamat_objek_pajak,
        bphtb_dibayar as jumlah_pajak_dibayar,
        IF(payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar,
        saved_date as masa_pajak');
        $this->gw_ssb->from('ssb');
        $rows = $this->gw_ssb->where('payment_code', $id)->get()->result();
        // print_r($this->gw_ssb->last_query());

        if (empty($rows)) {
            return false;
        }

        $id_switching = $rows[0]->id_switching;

        $this->sw_ssb->select('CPM_DENDA,CPM_OP_NPOP,CPM_PAYMENT_TIPE_OTHER,CPM_OP_THN_PEROLEH');
        $this->sw_ssb->from('cppmod_ssb_doc');
        $row_denda = $this->sw_ssb->where('CPM_SSB_ID', $id_switching)->get()->result();
        if ($row_denda) {
            $denda_bphtb = $row_denda[0]->CPM_DENDA;
            $CPM_OP_NPOP = $row_denda[0]->CPM_OP_NPOP;
            $CPM_PAYMENT_TIPE_OTHER = $row_denda[0]->CPM_PAYMENT_TIPE_OTHER;
            $CPM_OP_THN_PEROLEH = $row_denda[0]->CPM_OP_THN_PEROLEH;
        } else {
            $denda_bphtb = 0;
            $CPM_OP_NPOP = $row_denda->CPM_OP_NPOP;
            $CPM_PAYMENT_TIPE_OTHER = $row_denda->CPM_PAYMENT_TIPE_OTHER;
            $CPM_OP_THN_PEROLEH = $row_denda->CPM_OP_THN_PEROLEH;
        }

        foreach ($rows as $row) {
            // var_dump($CPM_OP_NPOP);exit;
            $nilai = $row->jumlah_pajak_dibayar +  $denda_bphtb;
            $nilai = (string) $nilai;

            $date_awal = date_create($row->masa_pajak);
            $date_awal = date_format($date_awal, "d-m-Y");

            $details[] = array(
                "no_objek_pajak" => $row->no_objek_pajak,

                "alamat_objek_pajak" => $row->alamat_objek_pajak,
            );

            $hasil = array(
                "area_code" => '1808',
                "tax_type" => '00'.$tax_type,
                "billing_code" => $row->billing_code,
                "refnum" => $row->payment_ref_number ?: "-",
                "total" => $nilai,
                "bill_amount" => $row->jumlah_pajak_dibayar,
                "penalty" => $denda_bphtb,
                "name" => $row->nama_wajib_pajak,
                "address" => $row->alamat_wajib_pajak,
                "op_address" => $row->alamat_objek_pajak,
                "nop" => $row->no_objek_pajak,
                "payment_refnum" => '-',

                "op_luas_bumi" => $row->op_luas_tanah,
                "op_luas_bangunan" => $row->op_luas_bangunan,
                "op_npop" => $CPM_OP_NPOP ?: "-",
                "jenis_perolehan_hak" => $CPM_PAYMENT_TIPE_OTHER ?: "-",
                "notaris" => $row->bphtb_notaris ?: "-",
                "wp_npwp" => $row->wp_npwp ?: "-",
                "wp_noktp" => $row->wp_noktp ?: "-",
                "due_date" => $row->due_date ?: "-",
                "misc_fee" => 0,
                "rt_rw" => $row->wp_rt . "/" . $row->wp_rw,
                "kelurahan" => $row->wp_kelurahan,
                "kecamatan" => $row->wp_kecamatan,
                "kabupaten" => $row->wp_kabupaten,
                "zip_code" => $row->wp_kodepos ?: "-",
                "op_rt_rw" => $row->op_rt . '/' . $row->op_rw,
                "op_kelurahan" => $row->op_kelurahan,
                "op_kecamatan" => $row->op_kecamatan,
                "op_kabupaten" => $row->op_kabupaten,
                "discount" => 0,
                "tax_year" => $CPM_OP_THN_PEROLEH ?: "-",
                "status_bayar" => $row->status_bayar


            );
        }

        return $hasil;
    }

    protected function paymentbniBPHTB($area_code, $tax_type, $id, $payment_amount)
    {

        $kdbelakang = substr($id, -2);
        // SELECT a.name, b.product, b.quantity
        // FROM database1.users a
        // LEFT JOIN database2.orders b
        // ON a.id = b.user_id;

        $current_year = date('y');
        $current_month = date('m');
        $current_day = date('d');

        $last_no_urut = $this->gw_ssb->select_max('ssb.payment_ref_number ', 'A')
            ->select_max('rv.ref_number', 'B')
            ->from('ssb')
            ->join('bni_reversal rv', 'ssb.payment_code = rv.payment_code', 'left')
            ->where('ssb.payment_ref_number LIKE', $current_year . $current_month . $current_day . '%')
            ->or_where('rv.ref_number LIKE', $current_year . $current_month . $current_day . '%')
            ->order_by('ssb.payment_ref_number', 'desc')
            ->limit(1)
            ->get()
            ->row();
        if (empty($last_no_urut)) {
            $data['payment_ref_number'] = $current_year . $current_month . $current_day . '00001';
        } else {
            $payment_ref_numbera = substr($last_no_urut->A, 6, 5);
            $payment_ref_numberb = substr($last_no_urut->B, 6, 5);
            if ($payment_ref_numbera > $payment_ref_numberb) {
                $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT) . $tax_type;
            } else {
                $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT) . $tax_type;
            }
        }
        // } else {
        //     $payment_ref_numbera = substr($last_no_urut->A, -5);
        //     $payment_ref_numberb = substr($last_no_urut->B, -5);

        //     if ($payment_ref_numbera > $payment_ref_numberb) {
        //         $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT);
        //     } else {
        //         $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT);
        //     }
        // }

        $this->gw_ssb->select('ssb.id_switching,
        ssb.payment_code as billing_code,
        ssb.payment_ref_number,
        ssb.op_luas_tanah,
        ssb.op_luas_bangunan,
        expired_date AS due_date, bphtb_notaris,
        ssb.wp_rt, ssb.wp_rw, ssb.wp_kelurahan, ssb.wp_kecamatan, ssb.wp_kabupaten, ssb.wp_kodepos,
        ssb.op_rt, ssb.op_rw, ssb.op_kelurahan, ssb.op_kecamatan, ssb.op_kabupaten,
        ssb.wp_noktp,ssb.wp_npwp,
        ssb.op_nomor as no_objek_pajak,
        ssb.wp_nama as nama_wajib_pajak,
        ssb.wp_alamat as alamat_wajib_pajak,
        ssb.op_letak As alamat_objek_pajak,
        ssb.bphtb_dibayar as jumlah_pajak_dibayar,ssb.bphtb_collectible,
        IF(ssb.payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar,
        ssb.saved_date as masa_pajak');
        $this->gw_ssb->from('gw_ssb.ssb ssb');
        $this->gw_ssb->join('sw_ssb.cppmod_ssb_doc doc', 'ssb.id_switching = doc.CPM_SSB_ID');
        $rows = $this->gw_ssb->where('ssb.payment_code', $id);
        // $rows = $this->gw_ssb->where('doc.CPM_DENDA', $payment_amount)
        $rows = $this->gw_ssb->where('(ssb.bphtb_dibayar + doc.CPM_DENDA) = ', $payment_amount)
            ->get()->result();

        // print_r($this->gw_ssb->last_query());exit;

        if (empty($rows)) {
            return false;
        }

        $id_switching = $rows[0]->id_switching;

        $this->sw_ssb->select('CPM_DENDA,CPM_OP_NPOP,CPM_PAYMENT_TIPE_OTHER,CPM_OP_THN_PEROLEH');
        $this->sw_ssb->from('cppmod_ssb_doc');
        $row_denda = $this->sw_ssb->where('CPM_SSB_ID', $id_switching)->get()->result();

        if ($row_denda) {
            $denda_bphtb = $row_denda[0]->CPM_DENDA;
            $CPM_OP_NPOP = $row_denda[0]->CPM_OP_NPOP;
            $CPM_PAYMENT_TIPE_OTHER = $row_denda[0]->CPM_PAYMENT_TIPE_OTHER;
            $CPM_OP_THN_PEROLEH = $row_denda[0]->CPM_OP_THN_PEROLEH;
        } else {
            $denda_bphtb = 0;
            $CPM_OP_NPOP = $row_denda->CPM_OP_NPOP;
            $CPM_PAYMENT_TIPE_OTHER = $row_denda->CPM_PAYMENT_TIPE_OTHER;
            $CPM_OP_THN_PEROLEH = $row_denda->CPM_OP_THN_PEROLEH;
        }

        foreach ($rows as $row) {
            $nilai = $row->jumlah_pajak_dibayar +  $denda_bphtb;
            $nilai = (string) $nilai;

            $date_awal = date_create($row->masa_pajak);
            $date_awal = date_format($date_awal, "d-m-Y");

            $details[] = array(
                "no_objek_pajak" => $row->no_objek_pajak,

                "alamat_objek_pajak" => $row->alamat_objek_pajak,
            );

            $hasil = array(
                "area_code" => '1808',
                "tax_type" => '0001',
                "billing_code" => $row->billing_code,
                "refnum" =>  $data['payment_ref_number'] ?: "-",
                "total" => $nilai,
                "bill_amount" => $row->jumlah_pajak_dibayar,
                "penalty" => $denda_bphtb,
                "name" => $row->nama_wajib_pajak,
                "address" => $row->alamat_wajib_pajak,
                "op_address" => $row->alamat_objek_pajak,
                "nop" => $row->no_objek_pajak,
                "payment_refnum" => $data['payment_ref_number'],

                "op_luas_bumi" => $row->op_luas_tanah,
                "op_luas_bangunan" => $row->op_luas_bangunan,
                "op_npop" => $CPM_OP_NPOP ?: "-",
                "jenis_perolehan_hak" => $CPM_PAYMENT_TIPE_OTHER ?: "-",
                "notaris" => $row->bphtb_notaris ?: "-",
                "wp_npwp" => $row->wp_npwp ?: "-",
                "wp_noktp" => $row->wp_noktp ?: "-",
                "due_date" => $row->due_date ?: "-",
                "misc_fee" => 0,
                "rt_rw" => $row->wp_rt . "/" . $row->wp_rw,
                "kelurahan" => $row->wp_kelurahan,
                "kecamatan" => $row->wp_kecamatan,
                "kabupaten" => $row->wp_kabupaten,
                "zip_code" => $row->wp_kodepos ?: "-",
                "op_rt_rw" => $row->op_rt . '/' . $row->op_rw,
                "op_kelurahan" => $row->op_kelurahan,
                "op_kecamatan" => $row->op_kecamatan,
                "op_kabupaten" => $row->op_kabupaten,
                "discount" => 0,
                "tax_year" => $CPM_OP_THN_PEROLEH ?: "-",
                "status_bayar" => $row->status_bayar
            );
        }

        if ($hasil['status_bayar'] == 'BELUM LUNAS') {
            $response = ['response_code' => '00', 'data' => $hasil];
            $response = array_merge($response, ['message' => 'success']);

            header('Content-Type: application/json');
            echo json_encode($response);
        }
        return $hasil;
    }



    protected function updatebniBPHTB($inquiry, $params)
    {
        $data = $inquiry;
        // var_dump($inquiry);exit;
        $uniqueNumber = uniqid();

        // $current_year = date('Y');
        // $current_month = date('m');

        // $last_no_urut = $this->gw_ssb->select_max('ssb.payment_ref_number ','A')
        //                 ->select_max('rv.ref_number', 'B')
        //                 ->from('ssb')
        //                 ->join('bni_reversal rv','ssb.payment_code = rv.payment_code','left')
        //                 ->where('ssb.payment_ref_number LIKE', $current_year . $current_month . '%')
        //                 ->or_where('rv.ref_number LIKE', $current_year . $current_month . '%')
        //                 ->order_by('ssb.payment_ref_number', 'desc')
        //                 ->limit(1)
        //                 ->get()
        //                 ->row();
        // if (empty($last_no_urut)) {
        //     $data['payment_ref_number'] = $current_year . $current_month . '00001';
        // } else {
        //     $payment_ref_numbera = substr($last_no_urut->A, -5);
        //     $payment_ref_numberb = substr($last_no_urut->B, -5);


        //     if($payment_ref_numbera > $payment_ref_numberb){
        //         $data['payment_ref_number'] = $current_year . $current_month . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT);
        //     }else{
        //         $data['payment_ref_number'] = $current_year . $current_month . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT);
        //     }

        // }



        $where = array(
            "payment_code" => $data['billing_code'],
            "bphtb_dibayar" => $data['bill_amount']
        );

        $data = array(
            "payment_flag" => '1',
            "payment_paid" => $params['new_payment_paid'],
            "payment_ref_number" => $data['refnum'],
            "payment_gw_refnum" => $data['refnum'],
            "payment_bank_code" => $params['payment_bank_code'],
            "payment_merchant_code" => $params['new_channel'],
            "payment_settlement_date" => $params['payment_settlement_date'],
            "payment_offline_user_id" => $params['operator'],
            "payment_offline_paid" => $params['new_payment_paid'],
            "bphtb_collectible" => $data['bill_amount'],
        );

        $update_bayar = $this->gw_ssb->update("ssb", $data, $where);
        // print_r($this->gw_ssb->last_query());
        if ($update_bayar) {
            return true;
        } else {
            return false;
        }
    }


    protected function datareversalbniBPHTB($area_code, $tax_type, $id, $refnum)
    {

        $kdbelakang = substr($id, -2);

        $this->gw_ssb->select('id_switching,
        payment_code as billing_code,
        IF(payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar,
        wp_nama as nama_wajib_pajak,
        payment_ref_number');
        $this->gw_ssb->from('ssb');
        $rows = $this->gw_ssb->where('payment_code', $id);
        $rows = $this->gw_ssb->where('payment_ref_number', $refnum)->get()->result();

        if (empty($rows)) {
            return false;
        }


        foreach ($rows as $row) {
            $hasil = array(
                "area_code" => '1808',
                "tax_type" => '0002',
                "billing_code" => $row->billing_code,
                "refnum" => $row->payment_ref_number ?: "-",
                "name" => $row->nama_wajib_pajak ?: "-",
                "payment_refnum" => $row->payment_ref_number,

                "status_bayar" => $row->status_bayar

            );
        }

        return $hasil;
    }

    protected function insertreversalBPHTB($inquiry, $params)
    {
        $data = $inquiry;
        // var_dump($data);exit;
        $data = array(
            "payment_code" => $inquiry['billing_code'],
            "ref_number" => $inquiry['payment_refnum'],
            "tipe_pajak" => $inquiry['tax_type'],
            "created_at" => date('Y-m-d H:i:s'),

        );

        $insert_bayar = $this->gw_ssb->insert("bni_reversal", $data);
        if ($insert_bayar) {
            return $this->reversalBPHTB($inquiry, $params);
        } else {
            return false;
        }
    }

    protected function reversalBPHTB($inquiry, $params)
    {
        $data = $inquiry;

        $where = array(
            "payment_code" => $data['billing_code'],
            "payment_ref_number" => $data['refnum'],
        );

        $data = array(
            "payment_flag" => '0',
            "payment_paid" => null,
            "payment_ref_number" => null,
            "payment_bank_code" => null,
            "payment_merchant_code" => null,
            "payment_settlement_date" => null,
            "payment_offline_user_id" => null,
            "payment_offline_paid" => null,
            "bphtb_collectible" => null,
            "payment_sw_refnum" => '1',

        );

        $update_bayar = $this->gw_ssb->update("ssb", $data, $where);
        if ($update_bayar) {
            return true;
        } else {
            return false;
        }
    }

    protected function inquirybniPBB($tax_year, $tax_type, $nop, $now = null)
    {
        $this->pbb->select('*');
        $this->pbb->from('pbb_sppt A');
        $this->pbb->where('A.NOP', $nop);
        $this->pbb->where('A.SPPT_TAHUN_PAJAK', $tax_year);
        $query = $this->pbb->get();
        $rows = $query->result();
        echo $this->db->last_query();
        if (empty($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            $getDenda = (new HitungDenda(($now !== null ? $now : $this->time)))->get($row->SPPT_TANGGAL_JATUH_TEMPO, $row->SPPT_PBB_HARUS_DIBAYAR);
            //$denda = $getDenda - $row->NILAI_PENGURANGAN; /** $row->NILAI_PENGURANGAN | PENGURANGAN */
            $denda = $getDenda;
            /** $row->NILAI_PENGURANGAN | PENGURANGAN */
            $denda = $denda < 0 ? 0 : $denda;


            $hasil = array(
                "area_code" => '1808',
                "tax_type" => '00'.$tax_type,
                // "billing_code" =>$row->billing_code,
                "refnum" => $row->PAYMENT_REF_NUMBER ?: "-",

                "total" => (string) (($row->SPPT_PBB_HARUS_DIBAYAR + $denda) + 0),
                "bill_amount" => $row->SPPT_PBB_HARUS_DIBAYAR ?: "-",
                "penalty"  => (string) ($denda + 0),
                "name" => $row->WP_NAMA,
                "address" => $row->WP_ALAMAT,
                "op_address" => $row->OP_ALAMAT,
                "nop" => $row->NOP,

                "sppt_terbit" => $row->SPPT_TANGGAL_TERBIT,
                "sppt_cetak"  => $row->SPPT_TANGGAL_CETAK,
                "op_luas_bumi" => $row->OP_LUAS_BUMI,
                "op_luas_bangunan" => $row->OP_LUAS_BANGUNAN,
                "op_kelas_bumi" => $row->OP_KELAS_BUMI,
                "op_kelas_bangunan" => $row->OP_KELAS_BANGUNAN,
                "op_njop_bumi" => $row->OP_NJOP_BUMI,
                "op_njop_bangunan" => $row->OP_NJOP_BANGUNAN,
                "op_njop" => $row->OP_NJOP,
                "op_njoptkp" => $row->OP_NJOPTKP,
                "op_njkp" => $row->OP_NJKP,
                "due_date" => $row->SPPT_TANGGAL_JATUH_TEMPO,
                "misc_fee" => $row->PBB_MISC_FEE ?: 0,
                "rt_rw" => $row->WP_RT . "/" . $row->WP_RW,
                "kelurahan" => $row->OP_KELURAHAN,
                "kecamatan" => $row->OP_KECAMATAN,
                "kabupaten" => $row->WP_KOTAKAB,
                "zip_code" => $row->WP_KODEPOS,
                "op_rt_rw" => $row->OP_RT . '/' . $row->OP_RW,
                "op_kelurahan" => $row->OP_KELURAHAN,
                "op_kecamatan" => $row->OP_KECAMATAN,
                "op_kabupaten" => $row->OP_KOTAKAB,
                "discount" => 0,
                "tax_year" => $row->SPPT_TAHUN_PAJAK,
                "status_bayar" => $row->PAYMENT_FLAG == "1" ? self::PBB_LUNAS : self::PBB_BELUM_LUNAS

            );
        }
        return $hasil;
    }


    protected function paymentbniPBB($tax_year, $tax_type, $nop, $payment_amount, $now = null)
    {


        $current_year = date('y');
        $current_month = date('m');
        $current_day = date('d');

        $last_no_urut = $this->pbb->select_max('pbb.payment_ref_number ', 'A')
            ->select_max('rv.ref_number', 'B')
            ->from('pbb_sppt pbb')
            ->join('bni_reversal rv', 'pbb.nop = rv.nop', 'left')
            ->where('pbb.payment_ref_number LIKE', $current_year . $current_month . $current_day . '%')
            ->or_where('rv.ref_number LIKE', $current_year . $current_month . $current_day . '%')
            ->order_by('pbb.payment_ref_number', 'desc')
            ->limit(1)
            ->get()
            ->row();
        if (empty($last_no_urut)) {
            $data['payment_ref_number'] = $current_year . $current_month . $current_day . '00001';
            // var_dump( $data['payment_ref_number']);exit;
        } else {
            $payment_ref_numbera = substr($last_no_urut->A, 6, 5);
            $payment_ref_numberb = substr($last_no_urut->B, 6, 5);
            if ($payment_ref_numbera > $payment_ref_numberb) {
                $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT) . $tax_type;
            } else {
                $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT) . $tax_type;
            }
        }
        // } else {
        //     $payment_ref_numbera = substr($last_no_urut->A, -5);
        //     $payment_ref_numberb = substr($last_no_urut->B, -5);

        //     if ($payment_ref_numbera > $payment_ref_numberb) {
        //         $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT);
        //     } else {
        //         $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT);
        //     }
        // }

        $this->pbb->select('*');
        $this->pbb->from('pbb_sppt A');
        $this->pbb->where('A.NOP', $nop);
        $this->pbb->where('A.SPPT_TAHUN_PAJAK', $tax_year);
        // $this->pbb->where('A.SPPT_PBB_HARUS_DIBAYAR', $payment_amount);
        $query = $this->pbb->get();
        $rows = $query->result();

        if (empty($rows)) {
            return false;
        }

        foreach ($rows as $row) {

            $getDenda = (new HitungDenda(($now !== null ? $now : $this->time)))->get($row->SPPT_TANGGAL_JATUH_TEMPO, $row->SPPT_PBB_HARUS_DIBAYAR);
            //$denda = $getDenda - $row->NILAI_PENGURANGAN; /** $row->NILAI_PENGURANGAN | PENGURANGAN */
            $denda = $getDenda;
            /** $row->NILAI_PENGURANGAN | PENGURANGAN */
            $denda = $denda < 0 ? 0 : $denda;
            $hasil = array(
                "area_code" => '1808',
                "tax_type" => '0002',
                "refnum" => $data['payment_ref_number'] ?: "-",
                "total" => (string) (($row->SPPT_PBB_HARUS_DIBAYAR + $denda) + 0),
                "bill_amount" => $row->SPPT_PBB_HARUS_DIBAYAR ?: "-",
                "penalty"  => (string) ($denda + 0),
                "name" => $row->WP_NAMA,
                "address" => $row->WP_ALAMAT,
                "op_address" => $row->OP_ALAMAT,
                "nop" => $row->NOP,
                "payment_refnum" => $data['payment_ref_number'],

                "sppt_terbit" => $row->SPPT_TANGGAL_TERBIT,
                "sppt_cetak"  => $row->SPPT_TANGGAL_CETAK,
                "op_luas_bumi" => $row->OP_LUAS_BUMI,
                "op_luas_bangunan" => $row->OP_LUAS_BANGUNAN,
                "op_kelas_bumi" => $row->OP_KELAS_BUMI,
                "op_kelas_bangunan" => $row->OP_KELAS_BANGUNAN,
                "op_njop_bumi" => $row->OP_NJOP_BUMI,
                "op_njop_bangunan" => $row->OP_NJOP_BANGUNAN,
                "op_njop" => $row->OP_NJOP,
                "op_njoptkp" => $row->OP_NJOPTKP,
                "op_njkp" => $row->OP_NJKP,
                "due_date" => $row->SPPT_TANGGAL_JATUH_TEMPO,
                "misc_fee" => $row->PBB_MISC_FEE ?: 0,
                "rt_rw" => $row->WP_RT . "/" . $row->WP_RW,
                "kelurahan" => $row->OP_KELURAHAN,
                "kecamatan" => $row->OP_KECAMATAN,
                "kabupaten" => $row->WP_KOTAKAB,
                "zip_code" => $row->WP_KODEPOS,
                "op_rt_rw" => $row->OP_RT . '/' . $row->OP_RW,
                "op_kelurahan" => $row->OP_KELURAHAN,
                "op_kecamatan" => $row->OP_KECAMATAN,
                "op_kabupaten" => $row->OP_KOTAKAB,
                "discount" => 0,
                "tax_year" => $row->SPPT_TAHUN_PAJAK,
                "status_bayar" => $row->PAYMENT_FLAG == "1" ? self::PBB_LUNAS : self::PBB_BELUM_LUNAS

            );
        }


        if ($hasil['status_bayar'] == 'BELUM LUNAS') {
            $response = ['response_code' => '00', 'data' => $hasil];
            $response = array_merge($response, ['message' => 'success']);

            header('Content-Type: application/json');
            echo json_encode($response);
        }
        return $hasil;
    }
    protected function paymentbniPBB_copy($tax_year, $tax_type, $nop, $payment_amount, $now = null)
    {


        $current_year = date('y');
        $current_month = date('m');
        $current_day = date('d');

        $last_no_urut = $this->pbb->select_max('pbb.payment_ref_number ', 'A')
            ->select_max('rv.ref_number', 'B')
            ->from('pbb_sppt pbb')
            ->join('bni_reversal rv', 'pbb.nop = rv.nop', 'left')
            ->where('pbb.payment_ref_number LIKE', $current_year . $current_month . $current_day . '%')
            ->or_where('rv.ref_number LIKE', $current_year . $current_month . $current_day . '%')
            ->order_by('pbb.payment_ref_number', 'desc')
            ->limit(1)
            ->get()
            ->row();
        if (empty($last_no_urut)) {
            $data['payment_ref_number'] = $current_year . $current_month . $current_day . '00001';
            // var_dump( $data['payment_ref_number']);exit;
        } else {
            $payment_ref_numbera = substr($last_no_urut->A, 6, 5);
            $payment_ref_numberb = substr($last_no_urut->B, 6, 5);
            if ($payment_ref_numbera > $payment_ref_numberb) {
                $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT) . $tax_type;
            } else {
                $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT) . $tax_type;
            }
        }
        // } else {
        //     $payment_ref_numbera = substr($last_no_urut->A, -5);
        //     $payment_ref_numberb = substr($last_no_urut->B, -5);

        //     if ($payment_ref_numbera > $payment_ref_numberb) {
        //         $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT);
        //     } else {
        //         $data['payment_ref_number'] = $current_year . $current_month . $current_day . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT);
        //     }
        // }

        $this->pbb->select('*');
        $this->pbb->from('pbb_sppt A');
        $this->pbb->where('A.NOP', $nop);
        $this->pbb->where('A.SPPT_TAHUN_PAJAK', $tax_year);
        // $this->pbb->where('A.SPPT_PBB_HARUS_DIBAYAR', $payment_amount);
        $query = $this->pbb->get();
        $rows = $query->result();

        if (empty($rows)) {
            return false;
        }

        foreach ($rows as $row) {

            $getDenda = (new HitungDenda(($now !== null ? $now : $this->time)))->get($row->SPPT_TANGGAL_JATUH_TEMPO, $row->SPPT_PBB_HARUS_DIBAYAR);
            //$denda = $getDenda - $row->NILAI_PENGURANGAN; /** $row->NILAI_PENGURANGAN | PENGURANGAN */
            $denda = $getDenda;
            /** $row->NILAI_PENGURANGAN | PENGURANGAN */
            $denda = $denda < 0 ? 0 : $denda;
            $hasil = array(
                "area_code" => '1808',
                "tax_type" => '0001',
                "refnum" => $data['payment_ref_number'] ?: "-",
                "total" => (string) (($row->SPPT_PBB_HARUS_DIBAYAR + $denda) + 0),
                "bill_amount" => $row->SPPT_PBB_HARUS_DIBAYAR ?: "-",
                "penalty"  => (string) ($denda + 0),
                "name" => $row->WP_NAMA,
                "address" => $row->WP_ALAMAT,

            );
        }



        return $hasil;
    }

    protected function updatebniPBB($inquiry, $params)
    {
        $data = $inquiry;
        // var_dump($inquiry);exit;
        $uniqueNumber = uniqid();

        // $current_year = date('Y');
        // $current_month = date('m');

        // $last_no_urut = $this->pbb->select_max('pbb.payment_ref_number ','A')
        // ->select_max('rv.ref_number', 'B')
        // ->from('pbb_sppt pbb')
        // ->join('bni_reversal rv','pbb.nop = rv.nop','left')
        // ->where('pbb.payment_ref_number LIKE', $current_year . $current_month . '%')
        // ->or_where('rv.ref_number LIKE', $current_year . $current_month . '%')
        // ->order_by('pbb.payment_ref_number', 'desc')
        // ->limit(1)
        // ->get()
        // ->row();
        // if (empty($last_no_urut)) {
        //     $data['payment_ref_number'] = $current_year . $current_month . '00001';
        // } else {
        //     $payment_ref_numbera = substr($last_no_urut->A, -5);
        //     $payment_ref_numberb = substr($last_no_urut->B, -5);


        //     if($payment_ref_numbera > $payment_ref_numberb){
        //         $data['payment_ref_number'] = $current_year . $current_month . str_pad($payment_ref_numbera + 1, 5, '0', STR_PAD_LEFT);
        //     }else{
        //         $data['payment_ref_number'] = $current_year . $current_month . str_pad($payment_ref_numberb + 1, 5, '0', STR_PAD_LEFT);
        //     }

        // }


        $where = array(
            "nop" => $data['nop'],
            "SPPT_TAHUN_PAJAK" => $data['tax_year'],
            "SPPT_PBB_HARUS_DIBAYAR" => $data['bill_amount']
        );

        $data = array(
            "payment_flag" => '1',
            "payment_paid" => $params['new_payment_paid'],
            "PAYMENT_REF_NUMBER" => $data['refnum'],
            "PAYMENT_GW_REFNUM" => $data['refnum'],
            "payment_bank_code" => $params['payment_bank_code'],
            "PAYMENT_OFFLINE_PAID" => $params['new_payment_paid'],
            "PBB_DENDA" => $data['penalty'],
            "PBB_TOTAL_BAYAR" => $data['bill_amount'],
            'PAYMENT_OFFLINE_USER_ID' => $params['operator'],
            "payment_merchant_code" => $params['new_channel'],
            "PBB_collectible" => $data['bill_amount'],
            "PAYMENT_SETTLEMENT_DATE" => $params['payment_settlement_date'],
        );
        // 'PAYMENT_FLAG'            => 1,
        // 'PAYMENT_BANK_CODE'       => $users['payment_bank_code'],
        // 'PAYMENT_PAID'            => $tanggalBayar,
        // 'PAYMENT_OFFLINE_PAID'    => $tanggalBayar,
        // 'PBB_DENDA'               => $inquiry['penalty'],
        // 'PBB_TOTAL_BAYAR'         => $jumlahBayar,
        // 'PAYMENT_REF_NUMBER'      => $paymentRefNum,
        // 'PAYMENT_OFFLINE_USER_ID' => $users['operator'],
        // 'COLL_PAYMENT_CODE'       => $collPaymentCode,
        // 'PAYMENT_MERCHANT_CODE'   => $channel,
        $update_bayar = $this->pbb->update("pbb_sppt", $data, $where);

        if ($update_bayar) {
            return true;
        } else {
            return false;
        }
    }


    protected function datareversalbniPBB($tax_year, $tax_type, $id, $renfum)
    {
        $this->pbb->select('*');
        $this->pbb->from('pbb_sppt A');
        $this->pbb->where('A.NOP', $id);
        $this->pbb->where('A.SPPT_TAHUN_PAJAK', $tax_year);
        $this->pbb->where('A.payment_ref_number', $renfum);
        $query = $this->pbb->get();
        $rows = $query->result();
        echo $this->db->last_query();
        if (empty($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            $hasil = array(
                "area_code" => '1808',
                "tax_type" => '0001',
                "refnum" => $row->PAYMENT_REF_NUMBER ?: "-",
                "name" => $row->WP_NAMA,
                "nop" => $row->NOP,
                "tax_year" => $row->SPPT_TAHUN_PAJAK,
                "status_bayar" => $row->PAYMENT_FLAG == "1" ? self::PBB_LUNAS : self::PBB_BELUM_LUNAS

            );
        }


        return $hasil;
    }

    protected function insertreversalPBB($inquiry, $params)
    {
        $data = $inquiry;
        //    var_dump($inquiry);exit;
        $data = array(
            "nop" => $inquiry['nop'],
            "ref_number" => $inquiry['refnum'],
            "tipe_pajak" => $inquiry['tax_type'],
            "created_at" => date('Y-m-d H:i:s'),

        );

        $insert_bayar = $this->pbb->insert("bni_reversal", $data);
        if ($insert_bayar) {
            return $this->reversalPBB($inquiry, $params);
        } else {
            return false;
        }
    }

    protected function reversalPBB($inquiry, $params)
    {
        $data = $inquiry;
        $where = array(
            "nop" => $data['nop'],
            "SPPT_TAHUN_PAJAK" => $data['tax_year'],
            "payment_ref_number" => $data['refnum']
        );

        $data = array(
            "PAYMENT_FLAG" => null,
            "PAYMENT_REF_NUMBER" => null,
            "payment_paid" => null,
            "payment_bank_code" => null,
            "PAYMENT_OFFLINE_PAID" => null,
            "PBB_DENDA" => null,
            "PBB_TOTAL_BAYAR" => null,
            'PAYMENT_OFFLINE_USER_ID' => null,
            "payment_merchant_code" => null,
            "PBB_collectible" => null,
            "PAYMENT_SETTLEMENT_DATE" => null,
            "payment_sw_refnum" => '1',
        );

        $update_bayar = $this->pbb->update("pbb_sppt", $data, $where);
        echo $this->db->last_query();
        if ($update_bayar) {
            return true;
        } else {
            return false;
        }
    }
}

/** REFACTORED BY RIDWAN */
