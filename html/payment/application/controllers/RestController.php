<?php

defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/HitungDenda.php';

use Restserver\Libraries\REST_Controller;

class RestController extends REST_Controller
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
    }


    protected function logins(){
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

    public function generateUniqueNumber() {
        $currentYear = date("Y");
        $currentMonth = date("m");
        if ($currentMonth != $this->current_month) {
            $this->counter = 1;
            $this->current_month = $currentMonth;
        }
        $uniqueNumber = $currentYear . $currentMonth . str_pad($this->counter, 6, "0", STR_PAD_LEFT);
        $this->counter++;
        return $uniqueNumber;
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
            $rc !== null ? $rc : self::HTTP_OK);
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
            $rc !== null ? $rc : self::HTTP_OK);
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
        if($tahun == '' || empty($tahun)){
            return (!is_numeric($nop) || strlen($nop) !== 18);
        }else{
            return (!is_numeric($nop) || strlen($nop) !== 18) || (!is_numeric($tahun) || strlen($tahun) !== 4);
        }

    }



    protected function inquiryPBB($nop, $tahun = '',$now = null, $returnRow = false)
    {
        $this->pbb->select('*');
        /** PENGURANGAN */
        //$this->pbb->select('IFNULL(B.NILAI, 0) AS NILAI_PENGURANGAN, IFNULL(B.PERSENTASE, 0) AS PERSENTASE_PENGURANGAN, B.ID AS ID_PENGURANGAN, A.*');
        //$this->pbb->join('(SELECT MAX(ID) AS MAX_ID_PENGURANGAN, NOP, TAHUN FROM pengurangan_denda WHERE DELETED_AT IS NULL GROUP BY NOP, TAHUN) C', 'C.NOP = A.NOP AND C.TAHUN = A.SPPT_TAHUN_PAJAK', 'left');
        //$this->pbb->join('pengurangan_denda B', 'B.ID = C.MAX_ID_PENGURANGAN', 'left');
        /** END PENGURANGAN */
        
        $this->pbb->from('pbb_sppt A');
        $this->pbb->where('A.NOP', $nop);
        
        if (empty($tahun)) {
            $this->pbb->group_start();
                $this->pbb->where('A.PAYMENT_FLAG', 0);
                $this->pbb->or_where('A.PAYMENT_FLAG IS NULL');
            $this->pbb->group_end();
        } else {
            if (is_array($tahun)) {
                $this->pbb->where_in('A.SPPT_TAHUN_PAJAK', $tahun);
            } else {
                $this->pbb->where('A.SPPT_TAHUN_PAJAK', $tahun);
            }
        }

        $this->pbb->order_by('A.SPPT_TAHUN_PAJAK', 'DESC');
        $query = $this->pbb->get();
        $rows = $query->result();

        if (empty($rows)) {
            return false;
        }

        $results = array();
        foreach ($rows as $row) {
            $penaltyPercentagePerMonth = ($row->SPPT_TAHUN_PAJAK>='2024') ? 1 : 2;
            $getDenda = (new HitungDenda(($now !== null ? $now : $this->time)))->get($row->SPPT_TANGGAL_JATUH_TEMPO, $row->SPPT_PBB_HARUS_DIBAYAR, $daysInMonth=0, $maxPenaltyMonth=24, $penaltyPercentagePerMonth);
            //$denda = $getDenda - $row->NILAI_PENGURANGAN; /** $row->NILAI_PENGURANGAN | PENGURANGAN */
            $denda = $getDenda; /** $row->NILAI_PENGURANGAN | PENGURANGAN */
			$denda = $denda < 0 ? 0 : $denda;
			
			$row->WP_RT = $row->WP_RT ?: '-';
			$row->WP_RW = $row->WP_RW ?: '-';
			
			$row->OP_RT = $row->OP_RT ?: '-';
			$row->OP_RW = $row->OP_RW ?: '-';
			
            $result = array(
                "nop"                   => $row->NOP ?: '-',
                "tahun_pajak"           => $row->SPPT_TAHUN_PAJAK ?: '-',
                "nama_wp"               => $row->WP_NAMA ?: '-',
                "alamat_wp"             => $row->WP_ALAMAT ?: '-',
                "rt_rw_wp"              => "{$row->WP_RT}/{$row->WP_RW}",
                "kelurahan_wp"          => $row->WP_KELURAHAN ?: '-',
                "kecamatan_wp"          => $row->WP_KECAMATAN ?: '-',
                "kotakab_wp"            => $row->WP_KOTAKAB ?: '-',
                "alamat_op"             => $row->OP_ALAMAT ?: '-',
                "rt_rw_op"              => "{$row->OP_RT}/{$row->OP_RW}",
                "kelurahan_op"          => $row->OP_KELURAHAN ?: '-',
                "kecamatan_op"          => $row->OP_KECAMATAN ?: '-',
                "kotakab_op"            => $row->OP_KOTAKAB ?: '-',
                "luas_bumi_bangunan_op" => "{$row->OP_LUAS_BUMI}/{$row->OP_LUAS_BANGUNAN}",
                "nilai"                 => (string) ($row->SPPT_PBB_HARUS_DIBAYAR + 0),
                "denda"                 => (string) ($denda + 0),
                "total"                 => (string) (($row->SPPT_PBB_HARUS_DIBAYAR + $denda) + 0),
                "status_bayar"          => $row->PAYMENT_FLAG == "1" ? self::PBB_LUNAS : self::PBB_BELUM_LUNAS
            );

            if ($returnRow) {
                $result['row'] = $row;
            }

            $results[] = $result;
        }

        return $results;
    }

    public function updateBatchPBB($data, $nop)
    {
        $this->pbb->trans_start();
        $this->pbb->where('NOP', $nop);
        $this->pbb->update_batch('pbb_sppt', $data, 'SPPT_TAHUN_PAJAK');
        $this->pbb->trans_complete();

        return $this->pbb->trans_status();
    }

    protected function inquiryPajakSPajak($id)
    {   
        $kdbelakang = substr($id,-2);
        
        $this->spajak->select('payment_code as kode_billing,
            npwpd as npwpd,
            op_nomor as no_objek_pajak,
            wp_nama as nama_wajib_pajak,
            wp_alamat as alamat_wajib_pajak,
            op_nama as nama_objek_pajak,
            op_alamat As alamat_objek_pajak,
            simpatda_dibayar as nilai,
            simpatda_denda as denda,
            (simpatda_dibayar+simpatda_denda) as jumlah_pajak_dibayar,
            IF(payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar,
            masa_pajak_awal as pajak_awal,
            masa_pajak_akhir as pajak_akhir,
            id_switching as id_switching');
            $this->spajak->from('simpatda_gw');
            $rows = $this->spajak->where('payment_code', $id)->get()->result();
            
        if (empty($rows)) {
            return false;
        }

        $id_switching = $rows[0]->id_switching;

        if($kdbelakang == '06'){
            $this->spajak->select('B.CPM_NOP as nop, A.CPM_ATR_JUDUL as judul, A.CPM_ATR_LOKASI as lokasi, B.CPM_NAMA_OP as nama_op_reklame, B.CPM_ALAMAT_OP as alamat_op_reklame');
            $this->spajak->from('patda_reklame_doc_atr A');
            $this->spajak->join('patda_reklame_profil B', 'A.CPM_ATR_ID_PROFIL = B.CPM_ID');
            $rows_profil = $this->spajak->where('A.CPM_ATR_REKLAME_ID', $id_switching)->get()->result();
            
            foreach ($rows_profil as $rek){
                $details[]= array(
                    "no_objek_pajak" =>$rek->nop,
                    "nama_objek_pajak" =>$rek->nama_op_reklame,
                    "alamat_objek_pajak" =>$rek->alamat_op_reklame,
                    "judul_reklame" =>$rek->judul,
                    "lokasi_reklame" =>$rek->lokasi,
                );
            }

        }else{
            
            foreach ($rows as $row){
                $details[]= array(
                    "no_objek_pajak" =>$row->no_objek_pajak,
                    "nama_objek_pajak" =>$row->nama_objek_pajak,
                    "alamat_objek_pajak" =>$row->alamat_objek_pajak,
                );

            }
        }


        foreach ($rows as $row){
            $date_awal= date_create($row->pajak_awal);
            $date_awal= date_format($date_awal,"d-m-Y");
            $date_akhir= date_create($row->pajak_akhir);
            $date_akhir= date_format($date_akhir,"d-m-Y");

            $hasil= array(
                "kode_billing" =>$row->kode_billing,
                "npwpd" =>$row->npwpd,
                "nama_wajib_pajak" =>$row->nama_wajib_pajak,
                "alamat_wajib_pajak" =>$row->alamat_wajib_pajak,
                "nilai" =>$row->nilai,
                "denda" =>$row->denda,
                "total" =>$row->jumlah_pajak_dibayar,
                "status_bayar" =>$row->status_bayar,
                "masa_pajak_1" =>$date_awal,
                "masa_pajak_2" =>$date_akhir,
                "details_op" =>$details
            );
        }
        

        return $hasil;
    }

    protected function inquirybniSPajak($area_code,$tax_type,$id)
    {   
        $kdbelakang = substr($id,-2);


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
            // $rows = $this->spajak->where('payment_code', $id)->get()->result();
            // echo $this->spajak->last_query(); 
        if (empty($rows)) {
            return false;
        }
        $id_switching = $rows[0]->id_switching;

        if($kdbelakang == '06'){
            $this->spajak->select('B.CPM_NOP as nop, A.CPM_ATR_JUDUL as judul, A.CPM_ATR_LOKASI as lokasi, B.CPM_NAMA_OP as nama_op_reklame, B.CPM_ALAMAT_OP as alamat_op_reklame');
            $this->spajak->from('patda_reklame_doc_atr A');
            $this->spajak->join('patda_reklame_profil B', 'A.CPM_ATR_ID_PROFIL = B.CPM_ID');
            $rows_profil = $this->spajak->where('A.CPM_ATR_REKLAME_ID', $id_switching)->get()->result();
            
            foreach ($rows_profil as $rek){
                $details[]= array(
                    "no_objek_pajak" =>$rek->nop,
                    "nama_objek_pajak" =>$rek->nama_op_reklame,
                    "alamat_objek_pajak" =>$rek->alamat_op_reklame,
                    "judul_reklame" =>$rek->judul,
                    "lokasi_reklame" =>$rek->lokasi,
                );
            }

        }else{
            
            foreach ($rows as $row){
                $details[]= array(
                    "no_objek_pajak" =>$row->no_objek_pajak,
                    "nama_objek_pajak" =>$row->nama_objek_pajak,
                    "alamat_objek_pajak" =>$row->alamat_objek_pajak,
                );

            }
        }


        foreach ($rows as $row){
            $date_awal= date_create($row->pajak_awal);
            $date_awal= date_format($date_awal,"d-m-Y");
            $date_akhir= date_create($row->pajak_akhir);
            $date_akhir= date_format($date_akhir,"d-m-Y");

            $hasil= array(
                "area_code" =>$row->area_code,
                "tax_type" =>'00'.$row->tax_type,
                "billing_code" =>$row->billing_code,
                "refnum" =>$row->payment_ref_number,
                "total" =>$row->patda_total_bayar,
                "bill_amount" =>$row->jumlah_pajak_dibayar,
                "penalty" =>$row->denda,
                "name" =>$row->nama_wajib_pajak,
                "address" =>$row->alamat_wajib_pajak,
                "op_address" =>$row->alamat_objek_pajak,
                "nop" =>$row->no_objek_pajak,
                "op_name" =>$row->nama_objek_pajak,
                "pajak_awal" =>$date_awal,
                "pajak_akhir" =>$date_akhir,
                "kode_rek" =>$row->kode_rek,
                "nama_rek" =>$row->nama_rek,
                "jenis" =>$row->jenis,
                "due_date" =>$row->expired_date,
                "misc_fee" =>$row->patda_misc_fee,

                "rt_rw" =>$row->rt_rw,
                "kelurahan_wp" =>$row->kelurahan_wp,
                "kecamatan_wp" =>$row->kecamatan_wp,
                "kabupaten" =>'Way Kanan',
                "zip_code" =>$row->CPM_KODEPOS_OP,
                "op_rt_rw" =>$row->CPM_RT_OP .'/'. $row->CPM_RW_OP,
                "op_kelurahan" =>$row->kelurahan_op,
                "op_kecamatan" =>$row->kecamatan_op,
                "op_kabupaten" =>'Way Kanan',
                "discount" => '-',
                "tax_year" =>$row->simpatda_tahun_pajak,
                // "status_bayar" =>$row->status_bayar

            );
        }

          return $hasil;

    }

    protected function inquirybniSPajak_bancup($area_code,$tax_type,$id)
    {   
        $kdbelakang = substr($id,-1);
       
		if($kdbelakang == 05){
			$pjk = 'hiburan'; // hiburan 05
		}elseif($kdbelakang == 03){
			$pjk = 'hotel'; // hotel 03
		}elseif($kdbelakang == 08){
			$pjk = 'mineral'; // mineral 08
		}elseif($kdbelakang == 09){
			$pjk = 'parkir'; // parkir 09
			$type = '5'; // parkir 09
		}elseif($kdbelakang == 07){
			$pjk = '07'; //jalan 07
		}elseif($kdbelakang == 06){
			$pjk = 'reklame'; // reklame 06
		}elseif($kdbelakang == 04){
			$pjk = 'restoran'; // restoran 04
		}elseif($kdbelakang == 10){
			$pjk = 'airbawahtanah'; // airbawahtanah 10
             
        }elseif($kdbelakang == 10 ){
			$kdbelakang = 'airbawahtanah'; //airbawahtanah 10
		}else{
			$pjk = 'walet'; // wallet 11
            $type = '9';
		}
        var_dump($kdbelakang,$pjk,$type );
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
            
            gw.id_switching as id_switching,
            wp.CPM_RTRW_WP as rt_rw,
            type.jenis as jenis,
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
            $this->spajak->where('gw.area_code', $area_code);  // where kedua
            $this->spajak->where('gw.simpatda_type', $type);  // where kedua
            $rows = $this->spajak->get()->result();
            // $rows = $this->spajak->where('payment_code', $id)->get()->result();
            echo $this->spajak->last_query(); 
        if (empty($rows)) {
            return false;
        }
        $id_switching = $rows[0]->id_switching;

        if($kdbelakang == '06'){
            $this->spajak->select('B.CPM_NOP as nop, A.CPM_ATR_JUDUL as judul, A.CPM_ATR_LOKASI as lokasi, B.CPM_NAMA_OP as nama_op_reklame, B.CPM_ALAMAT_OP as alamat_op_reklame');
            $this->spajak->from('patda_reklame_doc_atr A');
            $this->spajak->join('patda_reklame_profil B', 'A.CPM_ATR_ID_PROFIL = B.CPM_ID');
            $rows_profil = $this->spajak->where('A.CPM_ATR_REKLAME_ID', $id_switching)->get()->result();
            
            foreach ($rows_profil as $rek){
                $details[]= array(
                    "no_objek_pajak" =>$rek->nop,
                    "nama_objek_pajak" =>$rek->nama_op_reklame,
                    "alamat_objek_pajak" =>$rek->alamat_op_reklame,
                    "judul_reklame" =>$rek->judul,
                    "lokasi_reklame" =>$rek->lokasi,
                );
            }

        }else{
            
            foreach ($rows as $row){
                $details[]= array(
                    "no_objek_pajak" =>$row->no_objek_pajak,
                    "nama_objek_pajak" =>$row->nama_objek_pajak,
                    "alamat_objek_pajak" =>$row->alamat_objek_pajak,
                );

            }
        }


        foreach ($rows as $row){
            $date_awal= date_create($row->pajak_awal);
            $date_awal= date_format($date_awal,"d-m-Y");
            $date_akhir= date_create($row->pajak_akhir);
            $date_akhir= date_format($date_akhir,"d-m-Y");

            $hasil= array(
                "area_code" =>$row->area_code,
                "tax_type" =>'00'.$row->tax_type,
                "billing_code" =>$row->billing_code,
                "refnum" =>$row->payment_ref_number,
                "total" =>$row->patda_total_bayar,
                "bill_amount" =>$row->jumlah_pajak_dibayar,
                "penalty" =>$row->denda,
                "name" =>$row->nama_wajib_pajak,
                "address" =>$row->alamat_wajib_pajak,
                "op_address" =>$row->alamat_objek_pajak,
                "nop" =>$row->no_objek_pajak,
                "op_name" =>$row->nama_objek_pajak,
                "pajak_awal" =>$date_awal,
                "pajak_akhir" =>$date_akhir,
                "kode_rek" =>$row->kode_rek,
                "nama_rek" =>$row->nama_rek,
                "jenis" =>$row->jenis,
                "due_date" =>$row->expired_date,
                "misc_fee" =>$row->patda_misc_fee,

                "rt_rw" =>$row->rt_rw,
                "kelurahan_wp" =>$row->kelurahan_wp,
                "kecamatan_wp" =>$row->kecamatan_wp,
                "kabupaten" =>'Way Kanan',
                "zip_code" =>$row->CPM_KODEPOS_OP,
                "op_rt_rw" =>$row->CPM_RT_OP .'/'. $row->CPM_RW_OP,
                "op_kelurahan" =>$row->kelurahan_op,
                "op_kecamatan" =>$row->kecamatan_op,
                "op_kabupaten" =>'Way Kanan',
                "discount" => '-',
                "tax_year" =>$row->simpatda_tahun_pajak,
                // "status_bayar" =>$row->status_bayar

            );
        }

          return $hasil;

    }


    protected function paymentbniSPajak($area_code,$tax_type,$id,$payment_amount)
    {   

        $kdbelakang = substr($id,-2);
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
            gw.simpatda_denda as denda,payment_ref_number,
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
            $this->spajak->where('(gw.simpatda_dibayar + gw.simpatda_denda) = ', $payment_amount);
            $rows = $this->spajak->get()->result();
            // $rows = $this->spajak->where('payment_code', $id)->get()->result();
            // echo $this->db->last_query(); 
        if (empty($rows)) {
            return false;
        }
        $id_switching = $rows[0]->id_switching;

        if($kdbelakang == '06'){
            $this->spajak->select('B.CPM_NOP as nop, A.CPM_ATR_JUDUL as judul, A.CPM_ATR_LOKASI as lokasi, B.CPM_NAMA_OP as nama_op_reklame, B.CPM_ALAMAT_OP as alamat_op_reklame');
            $this->spajak->from('patda_reklame_doc_atr A');
            $this->spajak->join('patda_reklame_profil B', 'A.CPM_ATR_ID_PROFIL = B.CPM_ID');
            $rows_profil = $this->spajak->where('A.CPM_ATR_REKLAME_ID', $id_switching)->get()->result();
            
            foreach ($rows_profil as $rek){
                $details[]= array(
                    "no_objek_pajak" =>$rek->nop,
                    "nama_objek_pajak" =>$rek->nama_op_reklame,
                    "alamat_objek_pajak" =>$rek->alamat_op_reklame,
                    "judul_reklame" =>$rek->judul,
                    "lokasi_reklame" =>$rek->lokasi,
                );
            }

        }else{
            
            foreach ($rows as $row){
                $details[]= array(
                    "no_objek_pajak" =>$row->no_objek_pajak,
                    "nama_objek_pajak" =>$row->nama_objek_pajak,
                    "alamat_objek_pajak" =>$row->alamat_objek_pajak,
                );

            }
        }


        foreach ($rows as $row){
            $date_awal= date_create($row->pajak_awal);
            $date_awal= date_format($date_awal,"d-m-Y");
            $date_akhir= date_create($row->pajak_akhir);
            $date_akhir= date_format($date_akhir,"d-m-Y");

            $hasil= array(
                "area_code" =>$row->area_code,
                "tax_type" =>$row->tax_type,
                "billing_code" =>$row->billing_code,
                "refnum" =>$row->payment_ref_number,
                "total" =>$row->patda_total_bayar,
                "bill_amount" =>$row->payment_amount,
                "penalty" =>$row->denda,
                "tagihan_pajak" =>$row->nilai,
                "name" =>$row->nama_wajib_pajak,
                "address" =>$row->alamat_wajib_pajak,
                "op_address" =>$row->alamat_objek_pajak,
                "nop" =>$row->no_objek_pajak,
                "op_name" =>$row->nama_objek_pajak,
                "payment_refnum" =>$row->payment_ref_number,
                "pajak_awal" =>$date_awal,
                "pajak_akhir" =>$date_akhir,
                "kode_rek" =>$row->kode_rek,
                "nama_rek" =>$row->nama_rek,
                "jenis" =>$row->jenis,
                "due_date" =>$row->expired_date,
                "misc_fee" =>$row->patda_misc_fee,

                "rt_rw" =>$row->rt_rw,
                "kelurahan" =>$row->kelurahan_wp,
                "kecamatan" =>$row->kecamatan_wp,
                "kabupaten" =>'Way Kanan',
                "zip_code" =>$row->CPM_KODEPOS_OP,
                "op_rt_rw" =>$row->CPM_RT_OP .'/'. $row->CPM_RW_OP,
                "op_kelurahan" =>$row->kelurahan_op,
                "op_kecamatan" =>$row->kecamatan_op,
                "op_kabupaten" =>'Way Kanan',
                "discount" => '-',
                "tax_year" =>$row->simpatda_tahun_pajak,
                "status_bayar" =>$row->status_bayar

            );
        }
        // $response->message = 'success';
        
        $response = ['message' => 'success'];

        header('Content-Type: application/json');
        echo json_encode($response);
        return $hasil;

    }

    protected function updatebniSPajak($inquiry, $params){
        $data = $inquiry;
        $uniqueNumber = uniqid();

        $current_year = date('Y');
        $current_month = date('m');

        $last_no_urut = $this->spajak->select('payment_ref_number')
                    ->from('simpatda_gw')
                    ->where('payment_ref_number LIKE', $current_year.$current_month.'%')
                    ->order_by('payment_ref_number', 'desc')
                    ->limit(1)
                    ->get()
                    ->row();
        if(empty($last_no_urut)) {
            $data['payment_ref_number'] = $current_year.$current_month.'00001';
        } else {
            $payment_ref_number = substr($last_no_urut->payment_ref_number, -5);
            $data['payment_ref_number'] = $current_year.$current_month.str_pad($payment_ref_number + 1, 5, '0', STR_PAD_LEFT);
        }

        $where = array(
            "area_code" =>$data['area_code'],
            "tax_type" =>$data['tax_type'],
            "payment_code" =>$data['billing_code'],
        );

        $data = array(
            "payment_flag" =>'1',
            "payment_bank_code" =>$params['payment_bank_code'],
            "operator" =>$params['operator'],
            "patda_collectible" =>$data['tagihan_pajak'],
            "patda_total_bayar" =>$data['bill_amount'],
            "patda_denda" =>$data['denda'],
            "payment_paid" =>$params['new_payment_paid'],
            "PAYMENT_SETTLEMENT_DATE" =>$params['payment_settlement_date'],
         
            "payment_merchant_code" =>$params['new_channel'],
            "payment_ref_number" =>$data['payment_ref_number'],

        );

        $update_bayar = $this->spajak->update("simpatda_gw", $data, $where);
        if($update_bayar){
            return true;
        }else{
            return false;
        }
    }

    protected function updatebniBPHTB($inquiry, $params){
        $data = $inquiry;
        $uniqueNumber = uniqid();

        $current_year = date('Y');
        $current_month = date('m');

        $last_no_urut = $this->gw_ssb->select('payment_ref_number')
                    ->from('ssb')
                    ->where('payment_ref_number LIKE', $current_year.$current_month.'%')
                    ->order_by('payment_ref_number', 'desc')
                    ->limit(1)
                    ->get()
                    ->row();
        if(empty($last_no_urut)) {
            $data['payment_ref_number'] = $current_year.$current_month.'00001';
        } else {
            $payment_ref_number = substr($last_no_urut->payment_ref_number, -5);
            $data['payment_ref_number'] = $current_year.$current_month.str_pad($payment_ref_number + 1, 5, '0', STR_PAD_LEFT);
        }

        $where = array(
            // "CPM_OP_THN_PEROLEH" =>$data['tax_year'],
            "payment_code" =>$data['billing_code'],
            "bphtb_dibayar" =>$data['bill_amount']
        );
                            
        $data = array(
            "payment_flag" =>'1',
            "payment_paid" =>$params['new_payment_paid'],
            "payment_ref_number" =>$data['payment_ref_number'],
            "payment_bank_code" =>$params['payment_bank_code'],
            "payment_merchant_code" =>$params['new_channel'],		
            "payment_settlement_date" =>$params['payment_settlement_date'],
            "payment_offline_user_id" =>$params['operator'],
            "payment_offline_paid" =>$params['new_payment_paid'],
            "bphtb_collectible" =>$data['bill_amount'],
        );

        $update_bayar = $this->gw_ssb->update("ssb", $data, $where);
            // print_r($this->gw_ssb->last_query());
        if($update_bayar){
            return true;
        }else{
            return false;
        }
    }



    protected function reversalSPajak($tax_year,$tax_type,$nop,$refnum)
    {   
        $kdbelakang = substr($id,-2);
        
        $this->spajak->select('payment_code as kode_billing,
            npwpd as npwpd,
            op_nomor as no_objek_pajak,
            wp_nama as nama_wajib_pajak,
            wp_alamat as alamat_wajib_pajak,
            op_nama as nama_objek_pajak,
            op_alamat As alamat_objek_pajak,
            simpatda_dibayar as nilai,
            simpatda_denda as denda,
            (simpatda_dibayar+simpatda_denda) as jumlah_pajak_dibayar,
            IF(payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar,
            masa_pajak_awal as pajak_awal,
            masa_pajak_akhir as pajak_akhir,
            id_switching as id_switching');
            $this->spajak->from('simpatda_gw');
            $rows = $this->spajak->where('payment_code', $id)->get()->result();
            
        if (empty($rows)) {
            return false;
        }

        $id_switching = $rows[0]->id_switching;
  
            foreach ($rows as $row){
                $details[]= array(
                    "no_objek_pajak" =>$row->no_objek_pajak,
                    "nama_objek_pajak" =>$row->nama_objek_pajak,
                    "alamat_objek_pajak" =>$row->alamat_objek_pajak,
                );

            }
        


        foreach ($rows as $row){
            $date_awal= date_create($row->pajak_awal);
            $date_awal= date_format($date_awal,"d-m-Y");
            $date_akhir= date_create($row->pajak_akhir);
            $date_akhir= date_format($date_akhir,"d-m-Y");

            $hasil= array(
                "kode_billing" =>$row->kode_billing,
                "npwpd" =>$row->npwpd,
                "nama_wajib_pajak" =>$row->nama_wajib_pajak,
                "alamat_wajib_pajak" =>$row->alamat_wajib_pajak,
                "nilai" =>$row->nilai,
                "denda" =>$row->denda,
                "total" =>$row->jumlah_pajak_dibayar,
                "status_bayar" =>$row->status_bayar,
                "masa_pajak_1" =>$date_awal,
                "masa_pajak_2" =>$date_akhir,
                "details_op" =>$details
            );
        }
        

        return $hasil;
    }


    protected function inquirybniBPHTB($area_code,$tax_type,$id)
    {   

        $kdbelakang = substr($id,-2);
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

        saved_date as masa_pajak');
        $this->gw_ssb->from('ssb');
        $rows = $this->gw_ssb->where('payment_code', $id)->get()->result();
            
        if (empty($rows)) {
            return false;
        }

        $id_switching = $rows[0]->id_switching;

        $this->sw_ssb->select('CPM_DENDA,CPM_OP_NPOP,CPM_PAYMENT_TIPE_OTHER,CPM_OP_THN_PEROLEH');
        $this->sw_ssb->from('cppmod_ssb_doc');
        $row_denda = $this->sw_ssb->where('CPM_SSB_ID', $id_switching)->get()->result();

        if($row_denda){
            $denda_bphtb = $row_denda[0]->CPM_DENDA;
            $CPM_OP_NPOP = $row_denda[0]->CPM_OP_NPOP;
            $CPM_PAYMENT_TIPE_OTHER = $row_denda[0]->CPM_PAYMENT_TIPE_OTHER;
            $CPM_OP_THN_PEROLEH = $row_denda[0]->CPM_OP_THN_PEROLEH;
        }else{
            $denda_bphtb = 0;
            $CPM_OP_NPOP = $row_denda->CPM_OP_NPOP;
            $CPM_PAYMENT_TIPE_OTHER = $row_denda->CPM_PAYMENT_TIPE_OTHER;
            $CPM_OP_THN_PEROLEH = $row_denda->CPM_OP_THN_PEROLEH;
        }

        foreach ($rows as $row){
            $nilai = $row->jumlah_pajak_dibayar -  $denda_bphtb;
            $nilai = (string) $nilai;

            $date_awal= date_create($row->masa_pajak);
            $date_awal= date_format($date_awal,"d-m-Y");
            
            $details[]= array(
                "no_objek_pajak" =>$row->no_objek_pajak,
            
                "alamat_objek_pajak" =>$row->alamat_objek_pajak,
            );

            $hasil= array(
                "area_code" =>'1808',
                "tax_type" =>'0002',
                "billing_code" =>$row->billing_code,
                "refnum" =>$row->payment_ref_number?: "-",
                "total" =>$nilai,
                "bill_amount" =>$row->jumlah_pajak_dibayar,
                "penalty" =>$denda_bphtb,
                "name" =>$row->nama_wajib_pajak,
                "address" =>$row->alamat_wajib_pajak,
                "op_address" =>$row->alamat_objek_pajak,
                "nop"=>$row->no_objek_pajak,
                "payment_refnum"=>'-',

                "op_luas_bumi" => $row->op_luas_tanah,
                "op_luas_bangunan" => $row->op_luas_bangunan,
                "op_npop" =>$CPM_OP_NPOP?: "-",
                "jenis_perolehan_hak" =>$CPM_PAYMENT_TIPE_OTHER?: "-",
                "notaris" => $row->bphtb_notaris ?: "-",
                "wp_npwp" => $row->wp_npwp ?: "-",
                "wp_noktp" => $row->wp_noktp ?: "-",
                "due_date" => $row->due_date ?: "-",
                // "misc_fee" => $row->misc_fee ?: "-",
                "rt_rw" => $row->wp_rt . "/". $row->wp_rw,
                "kelurahan" => $row->wp_kelurahan, 
                "kecamatan" => $row->wp_kecamatan,
                "kabupaten" => $row->wp_kabupaten, 
                "zip_code" => $row->wp_kodepos?: "-",
                "op_rt_rw" => $row->op_rt .'/'. $row->op_rw,
                "op_kelurahan" => $row->op_kelurahan,
                "op_kecamatan" => $row->op_kecamatan,
                "op_kabupaten" => $row->op_kabupaten,
                "discount" => '-',
                "tax_year" =>$CPM_OP_THN_PEROLEH?: "-"
                // "status_bayar"=>$row->status_bayar
                                                                  

            );
        }
        

        return $hasil;
    }

    protected function paymentbniBPHTB($area_code,$tax_type,$id)
    {   

        $kdbelakang = substr($id,-2);
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
        bphtb_dibayar as jumlah_pajak_dibayar,bphtb_collectible,
        IF(payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar,

        saved_date as masa_pajak');
        $this->gw_ssb->from('ssb');
        $rows = $this->gw_ssb->where('payment_code', $id)->get()->result();
            
        if (empty($rows)) {
            return false;
        }

        $id_switching = $rows[0]->id_switching;

        $this->sw_ssb->select('CPM_DENDA,CPM_OP_NPOP,CPM_PAYMENT_TIPE_OTHER,CPM_OP_THN_PEROLEH');
        $this->sw_ssb->from('cppmod_ssb_doc');
        $row_denda = $this->sw_ssb->where('CPM_SSB_ID', $id_switching)->get()->result();

        if($row_denda){
            $denda_bphtb = $row_denda[0]->CPM_DENDA;
            $CPM_OP_NPOP = $row_denda[0]->CPM_OP_NPOP;
            $CPM_PAYMENT_TIPE_OTHER = $row_denda[0]->CPM_PAYMENT_TIPE_OTHER;
            $CPM_OP_THN_PEROLEH = $row_denda[0]->CPM_OP_THN_PEROLEH;
        }else{
            $denda_bphtb = 0;
            $CPM_OP_NPOP = $row_denda->CPM_OP_NPOP;
            $CPM_PAYMENT_TIPE_OTHER = $row_denda->CPM_PAYMENT_TIPE_OTHER;
            $CPM_OP_THN_PEROLEH = $row_denda->CPM_OP_THN_PEROLEH;
        }

        foreach ($rows as $row){
            $nilai = $row->jumlah_pajak_dibayar -  $denda_bphtb;
            $nilai = (string) $nilai;

            $date_awal= date_create($row->masa_pajak);
            $date_awal= date_format($date_awal,"d-m-Y");
            
            $details[]= array(
                "no_objek_pajak" =>$row->no_objek_pajak,
            
                "alamat_objek_pajak" =>$row->alamat_objek_pajak,
            );

            $hasil= array(
                "area_code" =>'1808',
                "tax_type" =>'0002',
                "billing_code" =>$row->billing_code,
                "refnum" =>$row->payment_ref_number?: "-",
                "total" =>$row->bphtb_collectible,
                "bill_amount" =>$row->jumlah_pajak_dibayar,
                "penalty" =>$denda_bphtb,
                "name" =>$row->nama_wajib_pajak,
                "address" =>$row->alamat_wajib_pajak,
                "op_address" =>$row->alamat_objek_pajak,
                "nop" =>$row->no_objek_pajak,

                "op_luas_bumi" => $row->op_luas_tanah,
                "op_luas_bangunan" => $row->op_luas_bangunan,
                "op_npop" =>$CPM_OP_NPOP?: "-",
                "jenis_perolehan_hak" =>$CPM_PAYMENT_TIPE_OTHER?: "-",
                "notaris" => $row->bphtb_notaris ?: "-",
                "wp_npwp" => $row->wp_npwp ?: "-",
                "wp_noktp" => $row->wp_noktp ?: "-",
                "due_date" => $row->due_date ?: "-",
                // "misc_fee" => $row->misc_fee ?: "-",
                "rt_rw" => $row->wp_rt . "/". $row->wp_rw,
                "kelurahan" => $row->wp_kelurahan, 
                "kecamatan" => $row->wp_kecamatan,
                "kabupaten" => $row->wp_kabupaten, 
                "zip_code" => $row->wp_kodepos?: "-",
                "op_rt_rw" => $row->op_rt .'/'. $row->op_rw,
                "op_kelurahan" => $row->op_kelurahan,
                "op_kecamatan" => $row->op_kecamatan,
                "op_kabupaten" => $row->op_kabupaten,
                "discount" => '-',
                "tax_year" =>$CPM_OP_THN_PEROLEH?: "-",
                "status_bayar"=>$row->status_bayar
                                                                  

            );
        }
        

        return $hasil;
    }

    protected function inquirybniPBB_backup($tax_year = '',$tax_type,$nop,$now = null, $returnRow = false)
    {
        $this->pbb->select('*');
        /** PENGURANGAN */
        //$this->pbb->select('IFNULL(B.NILAI, 0) AS NILAI_PENGURANGAN, IFNULL(B.PERSENTASE, 0) AS PERSENTASE_PENGURANGAN, B.ID AS ID_PENGURANGAN, A.*');
        //$this->pbb->join('(SELECT MAX(ID) AS MAX_ID_PENGURANGAN, NOP, TAHUN FROM pengurangan_denda WHERE DELETED_AT IS NULL GROUP BY NOP, TAHUN) C', 'C.NOP = A.NOP AND C.TAHUN = A.SPPT_TAHUN_PAJAK', 'left');
        //$this->pbb->join('pengurangan_denda B', 'B.ID = C.MAX_ID_PENGURANGAN', 'left');
        /** END PENGURANGAN */
        
        $this->pbb->from('pbb_sppt A');
        $this->pbb->where('A.NOP', $nop);
        
        if (empty($tahun)) {
            $this->pbb->group_start();
                $this->pbb->where('A.PAYMENT_FLAG', 0);
                $this->pbb->or_where('A.PAYMENT_FLAG IS NULL');
            $this->pbb->group_end();
        } else {
            if (is_array($tahun)) {
                $this->pbb->where_in('A.SPPT_TAHUN_PAJAK', $tahun);
            } else {
                $this->pbb->where('A.SPPT_TAHUN_PAJAK', $tahun);
            }
        }

        $this->pbb->order_by('A.SPPT_TAHUN_PAJAK', 'DESC');
        $query = $this->pbb->get();
        $rows = $query->result();

        if (empty($rows)) {
            return false;
        }

        $results = array();
        foreach ($rows as $row) {
            $getDenda = (new HitungDenda(($now !== null ? $now : $this->time)))->get($row->SPPT_TANGGAL_JATUH_TEMPO, $row->SPPT_PBB_HARUS_DIBAYAR);
            //$denda = $getDenda - $row->NILAI_PENGURANGAN; /** $row->NILAI_PENGURANGAN | PENGURANGAN */
            $denda = $getDenda; /** $row->NILAI_PENGURANGAN | PENGURANGAN */
			$denda = $denda < 0 ? 0 : $denda;
			
			$row->WP_RT = $row->WP_RT ?: '-';
			$row->WP_RW = $row->WP_RW ?: '-';
			
			$row->OP_RT = $row->OP_RT ?: '-';
			$row->OP_RW = $row->OP_RW ?: '-';
			
            $result = array(
                "nop"                   => $row->NOP ?: '-',
                "tahun_pajak"           => $row->SPPT_TAHUN_PAJAK ?: '-',
                "nama_wp"               => $row->WP_NAMA ?: '-',
                "alamat_wp"             => $row->WP_ALAMAT ?: '-',
                "rt_rw_wp"              => "{$row->WP_RT}/{$row->WP_RW}",
                "kelurahan_wp"          => $row->WP_KELURAHAN ?: '-',
                "kecamatan_wp"          => $row->WP_KECAMATAN ?: '-',
                "kotakab_wp"            => $row->WP_KOTAKAB ?: '-',
                "alamat_op"             => $row->OP_ALAMAT ?: '-',
                "rt_rw_op"              => "{$row->OP_RT}/{$row->OP_RW}",
                "kelurahan_op"          => $row->OP_KELURAHAN ?: '-',
                "kecamatan_op"          => $row->OP_KECAMATAN ?: '-',
                "kotakab_op"            => $row->OP_KOTAKAB ?: '-',
                "luas_bumi_bangunan_op" => "{$row->OP_LUAS_BUMI}/{$row->OP_LUAS_BANGUNAN}",
                "nilai"                 => (string) ($row->SPPT_PBB_HARUS_DIBAYAR + 0),
                "denda"                 => (string) ($denda + 0),
                "total"                 => (string) (($row->SPPT_PBB_HARUS_DIBAYAR + $denda) + 0),
                "status_bayar"          => $row->PAYMENT_FLAG == "1" ? self::PBB_LUNAS : self::PBB_BELUM_LUNAS
            );

            if ($returnRow) {
                $result['row'] = $row;
            }

            $results[] = $result;
        }

        return $results;
    }


    protected function inquirybniPBB($tax_year,$tax_type,$nop)
    {   

        

        $this->pbb->select('*');        
        $this->pbb->from('pbb_sppt A');
        $this->pbb->where('A.NOP', $nop);
        $this->pbb->where('A.SPPT_TAHUN_PAJAK', $tax_year);
        $query = $this->pbb->get();
        $rows = $query->result();
             // echo $this->db->last_query(); 
        if (empty($rows)) {
            return false;
        }

        foreach ($rows as $row){
            
        

            $hasil= array(
                "area_code" =>'1808',
                "tax_type" =>'0002',
                // "billing_code" =>$row->billing_code,
                "refnum" =>$row->PAYMENT_REF_NUMBER?: "-",
                "total" =>$row->SPPT_PBB_HARUS_DIBAYAR?: "-",
                "bill_amount" =>$row->PBB_TOTAL_BAYAR?: "-",
                "penalty"  =>$row->PBB_DENDA,
                "name" =>$row->WP_NAMA,
                "address" =>$row->WP_ALAMAT,
                "op_address" =>$row->OP_ALAMAT,
                "nop" =>$row->NOP,

                "sppt_terbit" =>$row->SPPT_TANGGAL_TERBIT,
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
                "misc_fee" => $row->PBB_MISC_FEE,
                "rt_rw" => $row->WP_RT . "/". $row->WP_RW,
                "kelurahan" => $row->OP_KELURAHAN, 
                "kecamatan" => $row->OP_KECAMATAN,
                "kabupaten" => $row->WP_KOTAKAB, 
                "zip_code" => $row->WP_KODEPOS,
                "op_rt_rw" => $row->OP_RT .'/'. $row->OP_RW,
                "op_kelurahan" => $row->OP_KELURAHAN,
                "op_kecamatan" => $row->OP_KECAMATAN,
                "op_kabupaten" => $row->OP_KOTAKAB,
                "discount" => '-',
                "tax_year" => $row->SPPT_TAHUN_PAJAK
                // "status_bayar" => $row->PAYMENT_FLAG == "1" ? self::PBB_LUNAS : self::PBB_BELUM_LUNAS
           
            );
        }
        

        return $hasil;
    }


    protected function paymentbniPBB($tax_year,$tax_type,$nop,$payment_amount)
    {

        $this->pbb->select('*');
        $this->pbb->from('pbb_sppt A');
        $this->pbb->where('A.NOP', $nop);
        $this->pbb->where('A.SPPT_TAHUN_PAJAK', $tax_year);
        $this->pbb->where('A.SPPT_PBB_HARUS_DIBAYAR', $payment_amount);
        $query = $this->pbb->get();
        $rows = $query->result();
        
        if (empty($rows)) {
            return false;
        }

        foreach ($rows as $row){
            
            $hasil= array(
                "area_code" =>'1808',
                "tax_type" =>'0002',
                // "billing_code" =>$row->billing_code,
                "refnum" =>$row->PAYMENT_REF_NUMBER?: "-",
                "total" =>$row->PBB_TOTAL_BAYAR?: "-",
                "bill_amount" =>$row->SPPT_PBB_HARUS_DIBAYAR?: "-",
                "penalty"  =>$row->PBB_DENDA,
                "name" =>$row->WP_NAMA,
                "address" =>$row->WP_ALAMAT,
                "op_address" =>$row->OP_ALAMAT,
                "nop" =>$row->NOP,
                "payment_refnum" =>'-',

                "sppt_terbit" =>$row->SPPT_TANGGAL_TERBIT,
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
                "misc_fee" => $row->PBB_MISC_FEE,
                "rt_rw" => $row->WP_RT . "/". $row->WP_RW,
                "kelurahan" => $row->OP_KELURAHAN, 
                "kecamatan" => $row->OP_KECAMATAN,
                "kabupaten" => $row->WP_KOTAKAB, 
                "zip_code" => $row->WP_KODEPOS,
                "op_rt_rw" => $row->OP_RT .'/'. $row->OP_RW,
                "op_kelurahan" => $row->OP_KELURAHAN,
                "op_kecamatan" => $row->OP_KECAMATAN,
                "op_kabupaten" => $row->OP_KOTAKAB,
                "discount" => '-',
                "tax_year" => $row->SPPT_TAHUN_PAJAK,
                "status_bayar" => $row->PAYMENT_FLAG == "1" ? self::PBB_LUNAS : self::PBB_BELUM_LUNAS
           
            );
        }
        

        return $hasil;
    }

    protected function inquiryPajakBPTHB($id)
    {   

        $kdbelakang = substr($id,-2);
        
        $this->gw_ssb->select('id_switching,
        payment_code as kode_billing,
        op_nomor as npwpd,
        op_nomor as no_objek_pajak,
        wp_nama as nama_wajib_pajak,
        wp_alamat as alamat_wajib_pajak,
        wp_nama as nama_objek_pajak,
        op_letak As alamat_objek_pajak,
        bphtb_dibayar as jumlah_pajak_dibayar,
        saved_date as masa_pajak,
        IF(payment_flag=1, "LUNAS", "BELUM LUNAS") AS status_bayar');
        $this->gw_ssb->from('ssb');
        $rows = $this->gw_ssb->where('payment_code', $id)->get()->result();
            
        if (empty($rows)) {
            return false;
        }

        $id_switching = $rows[0]->id_switching;

        $this->sw_ssb->select('CPM_DENDA');
        $this->sw_ssb->from('cppmod_ssb_doc');
        $row_denda = $this->sw_ssb->where('CPM_SSB_ID', $id_switching)->get()->result();

        if($row_denda){
            $denda_bphtb = $row_denda[0]->CPM_DENDA;
        }else{
            $denda_bphtb = 0;
        }

        foreach ($rows as $row){
            $nilai = $row->jumlah_pajak_dibayar -  $denda_bphtb;
            $nilai = (string) $nilai;

            $date_awal= date_create($row->masa_pajak);
            $date_awal= date_format($date_awal,"d-m-Y");
            
            $details[]= array(
                "no_objek_pajak" =>$row->no_objek_pajak,
                "nama_objek_pajak" =>$row->nama_objek_pajak,
                "alamat_objek_pajak" =>$row->alamat_objek_pajak,
            );

            $hasil= array(
                "kode_billing" =>$row->kode_billing,
                "npwpd" =>$row->npwpd,
                "nama_wajib_pajak" =>$row->nama_wajib_pajak,
                "alamat_wajib_pajak" =>$row->alamat_wajib_pajak,
                "nilai" =>$nilai,
                "denda" =>$denda_bphtb,
                "total" =>$row->jumlah_pajak_dibayar,                                                      
                "status_bayar" =>$row->status_bayar,
                "masa_pajak_1" =>$date_awal,
                "masa_pajak_2" =>$date_awal,
                "details_op" => $details
            );
        }
        

        return $hasil;
    }

    protected function updatePajakSPajak($inquiry, $params){
        $data = $inquiry;

        $where = array(
            "payment_code" =>$data['kode_billing']
        );

        $data = array(
            "payment_flag" =>'1',
            "payment_bank_code" =>$params['payment_bank_code'],
            "operator" =>$params['operator'],
            "patda_collectible" =>$data['nilai'],
            "patda_total_bayar" =>$data['total'],
            "patda_denda" =>$data['denda'],
            "payment_paid" =>$params['new_payment_paid'],
            "PAYMENT_SETTLEMENT_DATE" =>$params['payment_settlement_date'],
            "PAYMENT_REF_NUMBER" =>$params['payment_ref_number'],
            "payment_merchant_code" =>$params['new_channel'],
        );

        $update_bayar = $this->spajak->update("simpatda_gw", $data, $where);
        if($update_bayar){
            return true;
        }else{
            return false;
        }
    }

    protected function updatePajakBPHTB($inquiry, $params){
        $data = $inquiry;
    
        $where = array(
            "payment_code" =>$data['kode_billing']
        );
                            
        $data = array(
            "payment_flag" =>'1',
            "payment_paid" =>$params['new_payment_paid'],
            "payment_offline_user_id" =>$params['operator'],
            "payment_offline_paid" =>$params['new_payment_paid'],
            "payment_bank_code" =>$params['payment_bank_code'],
            "bphtb_collectible" =>$data['total'],
            "payment_settlement_date" =>$params['payment_settlement_date'],
            "PAYMENT_REF_NUMBER" =>$params['payment_ref_number'],
            "payment_merchant_code" =>$params['new_channel'],		
        );

        $update_bayar = $this->gw_ssb->update("ssb", $data, $where);

        if($update_bayar){
            return true;
        }else{
            return false;
        }
    }

    protected function updatebniPBB($inquiry, $params){
        $data = $inquiry;
        $uniqueNumber = uniqid();

        $current_year = date('Y');
        $current_month = date('m');

        $last_no_urut = $this->pbb->select('PAYMENT_REF_NUMBER')
                    ->from('pbb_sppt')
                    ->where('PAYMENT_REF_NUMBER LIKE', $current_year.$current_month.'%')
                    ->order_by('PAYMENT_REF_NUMBER', 'desc')
                    ->limit(1)
                    ->get()
                    ->row();
        if(empty($last_no_urut)) {
            $data['PAYMENT_REF_NUMBER'] = $current_year.$current_month.'00001';
        } else {
            $payment_ref_number = substr($last_no_urut->PAYMENT_REF_NUMBER, -5);
            $data['PAYMENT_REF_NUMBER'] = $current_year.$current_month.str_pad($payment_ref_number + 1, 5, '0', STR_PAD_LEFT);
        }


        $where = array(
            "nop" =>$data['nop'],
            "SPPT_TAHUN_PAJAK" =>$data['tax_year'],
            "SPPT_PBB_HARUS_DIBAYAR" =>$data['bill_amount']
        );

        $data = array(
            "payment_flag" =>'1',
            "payment_bank_code" =>$params['payment_bank_code'],
            "payment_paid" =>$params['new_payment_paid'],
            "PAYMENT_OFFLINE_PAID" =>$params['new_payment_paid'],
            "PBB_DENDA" =>$data['penalty'],
            "PBB_TOTAL_BAYAR" =>$data['bill_amount'],
            "PAYMENT_REF_NUMBER" =>$data['PAYMENT_REF_NUMBER'],
            'PAYMENT_OFFLINE_USER_ID' => $params['operator'],
            "payment_merchant_code" =>$params['new_channel'],
            "PBB_collectible" =>$data['bill_amount'],
            "PAYMENT_SETTLEMENT_DATE" =>$params['payment_settlement_date'],
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

        if($update_bayar){
            return true;
        }else{
            return false;
        }
    }


    public function updatebniPBB_backup($data, $nop)
    {
        $this->pbb->trans_start();
        $this->pbb->where('NOP', $nop);
        $this->pbb->update_batch('pbb_sppt', $data, 'SPPT_TAHUN_PAJAK');
        $this->pbb->trans_complete();

        // echo $this->pbb->last_query();

        return $this->pbb->trans_status();
    }
}

/** REFACTORED BY ALDES DAN AAN */

