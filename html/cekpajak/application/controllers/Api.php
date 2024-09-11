<?php
date_default_timezone_set('Asia/Jakarta');

defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller
{
    public function getClass()
    {
        $ids = $this->input->post('ids');
        if ($ids != 0) {
            $data = $this->m_db->get_where_id('usaha_class', $ids);
            $array = [
                'class' => $data[0]['title'],
                'id' => $data[0]['id']
            ];
        } else {
            $array = ['class' => "", 'id' => ''];
        }
        echo  json_encode($array);
    }

    public function CalcTaxAirTanah()
    {
        $type = $this->m_db->get_where_col('pajak_type', 'code', 'airtanah');
        $value = $this->format->no_currency($this->input->post('iValue'));
        $idClass = $this->input->post('iClass');
        $tax = $this->input->post('iTax');
        $class = $this->m_db->get_where_col('pajak_airtanah', 'id_class', $idClass);
        $nilai_pajak = $this->fun->calc_pajak_airtanah($class, $value, $tax);
        $masa = $this->input->post('iMasa');
        if (isset($masa)) {
            $lastmonth = date("Y-m", strtotime("-1 month"));
            $thismonth = date("Y-m");
            $fine = $this->getFineCalc($masa, $nilai_pajak, $type[0]['tax_fine']);
            $diff = $this->getCountMonth($masa, $lastmonth);
            $timeMasa = strtotime($masa);
            if ($masa < $lastmonth) {
                $array = [
                    'result' => $this->format->currency($nilai_pajak),
                    'fine' => $this->format->currency($fine),
                    'diff' => $diff
                ];
            } else {
                $array = ['result' => $this->format->currency($nilai_pajak)];
            }
        } else {
            $array = ['result' => $this->format->currency($nilai_pajak)];
        }
        echo json_encode($array);
    }

    function getFineCalc($masa, $nilai, $fine)
    {
        $totalMonth = $this->getCountMonth($masa);
        $totalFine = 0;
        $totalNilai = $nilai;
        for ($i = 0; $i < $totalMonth; $i++) {
            $totalFine += $totalNilai * ($fine / 100);
            //$totalNilai += $totalFine; jika perhitungan bulan bertingkat/amortisasi
        }
        return $totalFine;
    }

    public function calcTaxOmzet()
    {
        $span = $this->input->post('iSpan');
        $omzet = $this->input->post('iOmzet');
        $fine = $this->input->post('iFine');
        $tax = $this->input->post('iTax');
        $masa = $this->input->post('iMasa');
        $dpp = $this->input->post('iDPP');
        $special = $this->input->post('iSpe');
        $totalTax = 0;
        if (isset($masa)) {

            if ($dpp == "0" || $dpp == "") {
                $totalTax = $omzet * ($tax / 100);
            } else {
                $dppTax = $omzet * (100 / 110);
                $totalTax = $dppTax * ($tax / 100);
            }

            $lastmonth = date("Y-m", strtotime("-1 month"));
            $thismonth = date("Y-m");
            $finetax = $this->getFineCalc($masa, $totalTax, $fine);
            $diff = $this->getCountMonth($masa, $lastmonth);
            $array = [
                'result' => $this->format->currency($totalTax),
                'fine' => $this->format->currency($finetax),
                'diff' => $diff
            ];
        } else {
            for ($i = 0; $i < $span; $i++) {
                if ($dpp == "0" || $dpp == "") {
                    $totalTax += $omzet * ($tax / 100);
                } else {
                    $dppTax = $omzet * (100 / 110);
                    $totalTax += $dppTax * ($tax / 100);
                }
            }
            $array = ['result' => $this->format->currency($totalTax)];
        }
        echo json_encode($array);
    }
    public function CalcTaxMinerba()
    {
        $array_value = $this->input->post('iValue');
        $array_type = $this->input->post('iType');
        $totalTax = 0;
        $tax = $this->input->post('iTax');
        for ($i = 0; $i < count($array_type); $i++) {
            $type = $this->m_db->get_where_col('pajak_minerba_type', 'id', $array_type[$i]);
            $totalTax += $array_value[$i] * $type[0]['harga_dasar'] * ($tax / 100);
        }

        // $type = $this->m_db->get_where_col('pajak_minerba','id_type',$idType);
        //$totalTax = $this->fun->calc_pajak_airtanah($type,$value,$tax);
        $fine = $this->input->post('iFine');
        $masa = $this->input->post('iMasa');
        if (isset($masa)) {
            $lastmonth = date("Y-m", strtotime("-1 month"));
            $thismonth = date("Y-m");
            $finetax = $this->getFineCalc($masa, $totalTax, $fine);
            $diff = $this->getCountMonth($masa, $lastmonth);
            $array = [
                'result' => $this->format->currency($totalTax),
                'fine' => $this->format->currency($finetax),
                'diff' => $diff
            ];
        } else {
            $array = ['result' => $this->format->currency($totalTax)];
        }
        echo json_encode($array);
    }

    public function getCountMonth($masa)
    {
        $lastmonth = date("Y-m", strtotime("-1 month"));
        $first = strtotime($masa);
        $end = strtotime($lastmonth);
        $min_date = min($first, $end);
        $max_date = max($first, $end);
        $i = 0;
        while (($min_date = strtotime("+1 MONTH", $min_date)) <= $max_date) {
            $i++;
        }
        return $i;
    }


    public function getKab()
    {
        $idProv = $this->input->post('idProv');
        $data = $this->m_db->get_where_col('place_kab', 'id_prov', $idProv);
        echo json_encode($data);
    }
    public function getKec()
    {
        $idKab = $this->input->post('idKab');
        $data = $this->m_db->get_where_col('place_kec', 'id_kab', $idKab);
        echo json_encode($data);
    }
    public function getKel()
    {
        $idKec = $this->input->post('idKec');
        $data = $this->m_db->get_where_col('place_kel', 'id_kec', $idKec);
        echo json_encode($data);
    }

    public function getUserOP()
    {
        $this->load->model('m_usaha');
        $user_usaha_id = $this->input->post('ids');
        $tax_type = $this->m_usaha->getUserObjekPajak($user_usaha_id);
        echo json_encode($tax_type);
    }

    public function getPajakTypeUn()
    {
        $data = $this->m_db->get('pajak_type');
        $user_usaha_id = $this->input->post('ids');

        foreach ($data as $row) {
            $user_tax = $this->m_db->get_where_col_2('users_objek_pajak', 'id_users_usaha', $user_usaha_id, 'id_pajak_type', $row['id']);
            if (count($user_tax) == 0) {
                $array[] = [
                    'id' => $row['id'],
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'icon' => $row['icon']
                ];
            }
        }
        echo json_encode($array);
    }

    public function getListTaxType()
    {
        $id_usaha = $this->input->post('iUsaha');
        $this->load->model('m_usaha');
        $tax_type_list = $this->m_usaha->getUserObjekPajak($id_usaha);
        echo json_encode($tax_type_list);
    }

    public function getLoc()
    {
        $type_id = $this->input->post('iType');
        $user_usaha_id = $this->input->post('ids');
        $data = $this->m_db->get_where_col_2('users_usaha_loc', 'id_users_usaha', $user_usaha_id, 'id_pajak_type', $type_id);
        echo json_encode($data);
    }

    public function getLastMasa()
    {
        $loc = $this->input->post('iLoc');
        $type = $this->input->post('iType');
        $sppt = $this->m_db->get_where_col_2('users_sppt', 'id_users_usaha_loc', '1', 'id_pajak_type', '1', 'masa_date', 'DESC');
        if (count($sppt) != 0) {
            $next = strtotime("+1 months", strtotime($sppt[0]['masa_date']));
            echo strftime('%Y-%m', $next);
        } else {
            echo "";
        }
    }

    public function getUsahaByID()
    {
        $usaha = $this->input->post('iUsaha');
        $data = $this->m_db->get_where_id('users_usaha', $usaha);
        echo json_encode($data);
    }

    public function getReklameTypeInfo()
    {
        $id_type = $this->input->post('iType');
        $type = $this->m_db->get_where_id('reklame_jenis', $id_type);

        echo json_encode($type[0]);
    }

    public function getCalcReklame1()
    {
        $pajak_type = $this->m_db->get_where_col('pajak_type', 'id', '7');
        $tax = $pajak_type[0]['tax'];
        $T = $this->input->post('iTinggi');
        $P = $this->input->post('iPanjang');
        $L = $this->input->post('iLebar');
        $sisi = $this->input->post('iSisi');
        $nfr = $this->input->post('iNFR');
        $nfj = $this->input->post('iNFJ');
        $nsp = $this->input->post('iNSP');
        $base_price = $this->input->post('iBasePrice');
        $high_price = $this->input->post('iHighPrice');
        $luas = $P * $L;
        $nilai_strategis = (int)$this->getReklameStrategic($this->m_db->get('reklame_nilai_strategis'), $luas);

        $njopr = $luas * $base_price; //dasar ukuran reklame
        $height = $T * $high_price; //dasar ketinggian reklame
        $total_njopr = $njopr + $height; //*** */
        $xnspr = $nfr + $nfj + $nsp;
        $nspr = $xnspr * $nilai_strategis;
        $total = (int)$nspr + (int)$total_njopr;
        if ($sisi == '1') {
            $totals = (int)$total * ($tax / 100);
        } else if ($sisi == '2') {
            $totals = (int)$total * 2 * ($tax / 100);
        }
        $array = [
            'type' => '1',
            'height' => $height,
            'luas' => $luas,
            'njopr' => $njopr,
            'xnspr' => $xnspr,
            'sisi' => $sisi,
            'nilai_strategis' => $nilai_strategis,
            'total_njopr' => $total_njopr,
            'nspr' => (int)$nspr,
            'total' => $total,
            'result' => (int)$totals
        ];
        echo json_encode($array);
    }
    public function getCalcReklame2()
    {
        $P = $this->input->post('iPanjang');
        $L = $this->input->post('iLebar');
        $sisi = $this->input->post('iSisi');
        $nfr = $this->input->post('iNFR');
        $nfj = $this->input->post('iNFJ');
        $nsp = $this->input->post('iNSP');
        $base_price = $this->input->post('iBasePrice');
        $high_price = $this->input->post('iHighPrice');
        $type = $this->input->post('iType');
        $luas = $P * $L;
        $nilai_strategis = (int)$this->getReklameStrategic($this->m_db->get('reklame_nilai_strategis'), $luas);
        $njopr = $luas * $base_price;
        $total_njopr = $njopr; //*** */
        $xnspr = $nfr + $nfj + $nsp;
        $nspr = $xnspr * $nilai_strategis;
        $total = (int)$nspr + (int)$total_njopr;
        if ($sisi == '1') {
            $totals = (int)$total * (25 / 100);
        } else if ($sisi == '2') {
            $totals = (int)$total * 2 * (25 / 100);
        }
        $array = [
            'type' => '1',
            'njopr' => $njopr,
            'xnspr' => $xnspr,
            'sisi' => $sisi,
            'nilai_strategis' => $nilai_strategis,
            'total_njopr' => $total_njopr,
            'nspr' => (int)$nspr,
            'total' => $total,
            'result' => (int)$totals
        ];
        echo json_encode($array);
    }


    public function getReklameStrategic($data, $luas)
    {
        $value = '0';
        foreach ($data as $row) {
            $range = explode(',', $row['size_range']);
            if ($range[1] != "") {
                if ($range[0] <= $luas && $range[1] >= $luas) {
                    $value = $row['value'];
                }
            } else {
                if ($range[0] <= $luas) {
                    $value = $row['value'];
                }
            }
        }
        return $value;
    }

    public function getCalcBPHTB()
    {
        $value = $this->input->post('iValue');
        $check = $this->input->post('iCheck');
        $npoptkp = '60000000';
        if ($check == "true") {
            $npoptkp = '300000000';
        }
        if ($npoptkp > $value) {
            $npopkp = 0;
            $bphtb = 0;
        } else {
            $npopkp = $value - $npoptkp;
            $bphtb = $npopkp * (5 / 100);
        }
        $array = [
            'check' => $check,
            'value' => $value,
            'npoptkp' => $npoptkp,
            'npopkp' => $npopkp,
            'bphtb' => $bphtb,
            'bphtb_cur' => $this->format->currency($bphtb)
        ];
        echo json_encode($array);
    }

    public function getCheckNOPPBB()
    {
        $nop = $this->input->post('iNOP');
        $data = $this->m_db->get_where_col_2('users_sppt_pbb', 'nop', $nop, 'status', '0');
        if (count($data) > 0) {
            echo json_encode($data);
        } else {
            echo "false";
        }
    }
    public function getCheckPBB()
    {
        $nop = $this->input->post('iNOP');
        $data = $this->m_db->get_where_col('users_sppt_pbb', 'nop', $nop, 'masa', 'DESC');
        if (count($data) > 0) {
            echo json_encode($data[0]);
        } else {
            echo "false";
        }
    }


    public function checkTagihanKodeBayar()
    {
        $tokenIDENTIFIKASI = 'BANDARLAMPUNG' . date('ymd');  /// T O K E N  I D E N T I F I K A S I

        $value = $this->format->no_currency($this->input->post('iValue'));
        $curlResponse = $this->fun->getCurlTagihan(getConfig('api_gateway_pajak'), array('kdbill' => $value));
        $response = $this->fun->curlTagihanFormatter($curlResponse);

        $modal = [];
        $r = json_decode($curlResponse);
        if(isset($r->data->total) && (float)$r->data->total>0){
            
            $qrIMG = false;
            // $qrIMG = base_url('images/qris/qr_temp.png');
            if(isset($r->data->qris) && $r->data->qris){
                $this->load->library('ciqrcode');
                $params['data'] = $r->data->qris;
                $params['level'] = 'H';
                $params['size'] = 10;
                $params['savename'] = FCPATH.'images/qris/'.$r->data->npwpd.'.png';
                $this->ciqrcode->generate($params);
                $qrIMG = base_url('images/qris/'.$r->data->npwpd.'.png');
            }

            $alamat = trim($r->data->details_op[0]->alamat_objek_pajak);
            $status = ($r->data->status_bayar=='BELUM LUNAS') ? 0 : 1;
            $status = ($r->data->total>0 && $r->data->total<=10000000) ? $status : 9;

            $modal          = (object)[];
            if($status==0) {
                $modal->token = password_hash($tokenIDENTIFIKASI, PASSWORD_DEFAULT);
            }
            $modal->nop     = $r->data->details_op[0]->no_objek_pajak;
            $modal->nama_op = $r->data->details_op[0]->nama_objek_pajak;
            $modal->status  = $status ;
            $modal->alamat  = $alamat;
            $modal->tagihan = 'Rp.'.$this->format->currency($r->data->total);
            $modal->expired = $r->data->expired_date;
            $modal->qr      = $qrIMG;
        }

        if (strpos($response, 'SUDAH LUNAS') !== false) {
            $response = str_replace('</span>','</div>',$response);
            $response = str_replace('<span>','<div class="text-center"><i class="text-success fa fa-check-square-o fa-3x mb-2"></i><br>',$response);
        }

        echo json_encode(array(
            'result'=> $response,
            'modal' => $modal
        ));
    }

    public function checkTagihanPBB()
    {
        $tokenIDENTIFIKASI = 'BANDARLAMPUNG' . date('ymd');  /// T O K E N  I D E N T I F I K A S I

        $value        = $this->format->no_currency($this->input->post('iValue'));
        $value2       = $this->format->no_currency($this->input->post('iValue2'));
        $curlResponse = $this->fun->getCurlTagihan(getConfig('api_gateway_pbb'), array('nop' => $value, 'tahun' => $value2));
        $response     = $this->fun->curlTagihanFormatter($curlResponse);

        $modal = [];
        $r = json_decode($curlResponse);
        if(isset($r->data->bills[0]->total) && (float)$r->data->bills[0]->total>0){
            
            $qrIMG = false;
            if(isset($r->data->bills[0]->qris) && $r->data->bills[0]->qris){
                $this->load->library('ciqrcode');
                $params['data'] = $r->data->bills[0]->qris;
                $params['level'] = 'H';
                $params['size'] = 10;
                $params['savename'] = FCPATH.'images/qris/'.$r->data->bills[0]->nop.'.png';
                $this->ciqrcode->generate($params);
                $qrIMG = base_url('images/qris/'.$r->data->bills[0]->nop.'.png');
            }

            $alamat = [];
            if(trim($r->data->bills[0]->alamat_op!=''))     $alamat[] = trim($r->data->bills[0]->alamat_op);
            if(trim($r->data->bills[0]->rt_rw_op!=''))      $alamat[] = 'RT/RW ' . trim($r->data->bills[0]->rt_rw_op);
            if(trim($r->data->bills[0]->kelurahan_op!=''))  $alamat[] = trim($r->data->bills[0]->kelurahan_op);
            if(trim($r->data->bills[0]->kecamatan_op!=''))  $alamat[] = trim($r->data->bills[0]->kecamatan_op);
            $alamat = implode(', ',$alamat);
            $status = ($r->data->bills[0]->status_bayar=='BELUM LUNAS') ? 0 : 1;
            $status = ($r->data->bills[0]->total>0 && $r->data->bills[0]->total<=10000000) ? $status : 9;

            $modal          = (object)[];
            if($status==0) {
                $modal->token = password_hash($tokenIDENTIFIKASI, PASSWORD_DEFAULT);
            }
            $modal->nop     = $r->data->bills[0]->nop;
            $modal->tahun   = $r->data->bills[0]->tahun_pajak;
            $modal->status  = $status;
            $modal->nama    = $r->data->bills[0]->nama_wp;
            $modal->alamat  = $alamat;
            $modal->total   = 'Rp.'.$this->format->currency($r->data->bills[0]->total);
            $modal->qr      = $qrIMG;

        }

        if (strpos($response, 'SUDAH LUNAS') !== false) {
            $response = str_replace('</span>','</div>',$response);
            $response = str_replace('<span>','<div class="text-center"><i class="text-success fa fa-check-square-o fa-3x mb-2"></i><br>',$response);
        }

        echo json_encode(array(
            'result'=> $response,
            'modal' => $modal
        ));
        
        /*$bl          = null;
        $rawResponse = json_decode($curlResponse, true);
        $status      = isset($rawResponse['code']) && $rawResponse['code'] === '00';

        if ($status && isset($rawResponse['data']['bills'][0]['tahun_pajak'])) {
            $blPbb = $this->getPbbBankLampung($rawResponse['data']['bills'][0]['nop'], $rawResponse['data']['bills'][0]['tahun_pajak']);

            if ($blPbb && isset($blPbb['data']['qr-image'])) {
                $bl = [
                    'image'  => $blPbb['data']['qr-image'],
                    'nop'    => $blPbb['data']['result']['data']['billing']['tax_object']['number'],
                    'tahun'  => $blPbb['data']['result']['data']['billing']['tax_year'],
                    'alamat' => $blPbb['data']['result']['data']['billing']['tax_object']['address'],
                    'nama'   => $blPbb['data']['result']['data']['billing']['taxpayer']['full_name'],
                    'bayar'  => $this->format->currency($blPbb['data']['result']['data']['trx_total_amount'])
                ];
            }
        }


        echo json_encode(array(
            'result' => $response,
            'status' => $status,
            'bl'     => $bl
        ));
        */
    }

    protected function getPbbBankLampung($nop, $tahun)
    {
        $expires_on      = strtotime(date('Y-m-t H:i:s', strtotime(date('Y') . '-12-01 23:59:59')));
        $now             = strtotime(date('Y-m-d H:i:s'));
        $diff            = ($expires_on - $now);
        $diff_in_minutes = round(abs($diff) / 60);
        $city_code       = substr($nop,0,4);//'1871';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => "http://117.53.45.7/mst/bank/services/inquiryqr?city_code={$city_code}&expired_duration={$diff_in_minutes}&tax_object_number={$nop}&tax_year={$tahun}&type_tax_code=00",
            CURLOPT_HTTPHEADER     => array("Channel-Id: QRIS"),
            CURLOPT_RETURNTRANSFER => 1
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);

        return $data;
    }
}
