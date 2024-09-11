<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fun
{
    public function calc_pajak_airtanah($data, $use, $tax)
    { //nilai pajak progresive berdasarkan pemakaian
        $value = '0';
        $take = '0';
        $value2 = '0';
        $status = true;
        foreach ($data as $row) {
            $range = explode(',', $row['ranges']);
            if ($range[1] != "") {
                if ($range[1] < $use) {
                    $value += $row['nilai'] * $range[1];
                    $take += $range[1];
                } else if ($range[1] >= $use && $range[0] <= $use) { //berhenti saat sampai di range
                    $value += $row['nilai'] * ($use - $take);
                    $status == false;
                }
            } else {
                if ($range[0] <= $use && $status == true) { //setelah melewati range akhir / infinity
                    $value += $row['nilai'] * ($use - $take);
                }
            }
        }
        $result = $value * ($tax / 100);
        return $result;
    }

    public function calc_pajak_airtanah_old($data, $use, $tax)
    { //nilai akhir langsung hitung berdasarkan range
        $value = '0';
        foreach ($data as $row) {
            $range = explode(',', $row['ranges']);
            if ($range[1] != "") {
                if ($range[0] <= $use && $range[1] >= $use) {
                    $value = $row['nilai'];
                }
            } else {
                if ($range[0] <= $use) {
                    $value = $row['nilai'];
                }
            }
        }
        $result = $value * $use * ($tax / 100);
        return $result;
    }

    public function calc_pajak_minerba($data, $use, $tax)
    {
        $value = '0';
        foreach ($data as $row) {
            $range = explode(',', $row['ranges']);
            if ($range[1] != "") {
                if ($range[0] <= $use && $range[1] >= $use) {
                    $value = $row['nilai'];
                }
            } else {
                if ($range[0] <= $use) {
                    $value = $row['nilai'];
                }
            }
        }
        $result = $value * $use * ($tax / 100);
        return $result;
    }
    function alert_box($alert, $message)
    {
        return '
                 <div class="alert alert-' . $alert . '">
                    ' . $message . '
                  </div>
               ';
    }
    public function sendMail($fromMail, $fromMailName, $email, $subject, $message)
    {
        $CI = &get_instance();
        $CI->load->library('email');
        $CI->email->from($fromMail, $fromMailName);
        $CI->email->to($email);
        $CI->email->subject($subject);
        $CI->email->message($message);
        $CI->email->set_mailtype("html");
        $CI->email->send();
    }

    public function getCurlTagihan($host, array $params)
    {
        $url_params = http_build_query($params);
        $host = $host . '?' . $url_params;
        $headers = array(
            'Content-Type:application/json',
            'Authorization: Basic ' . base64_encode(getConfig('api_gateway_auth'))
        );

        $ch = curl_init($host);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($ch);
        // curl_close($ch);
		
		if ($response === FALSE) {
			$error = sprintf("cUrl error (#%d): %s<br>\n",
				   curl_errno($ch),
				   htmlspecialchars(curl_error($ch)));
			return '{"code": "88", "description": "'. $error .'"}';
		}
        

        return $response;
    }

    public function curlTagihanFormatter($response)
    {
        $CI = &get_instance();
        $CI->load->library('format');

        $response = json_decode($response, true);
        if (empty($response) || !isset($response['code'])) {
            return false;
        }

        if (isset($response['description']) && $response['description']) {
            return '<span>' . $response['description'] . '</span>';
        }
        if ($response['code'] == '00' && !empty($response['data'])) {
            $html = '<div class="table-responsive"><table class="table">';

            if (isset($response['data']['bills'])) {
                if(is_array($response['data']['bills'])){
                    foreach ($response['data']['bills'] as $index => $bill_pbb) {
                        $html .= '<tr><td colspan="3"><strong>Tagihan ke-'. ($index + 1) .' Tahun '. $bill_pbb['tahun_pajak'] .'</strong></td></tr>';
                        $html .= '<tr><td>Nama WP</td> <td style="width:2px">:</td> <td>' . $bill_pbb['nama_wp'] . '</td></tr>';
                        $html .= '<tr><td>Alamat WP</td> <td style="width:2px">:</td> <td>' . $bill_pbb['alamat_wp'] . ' RT/RW ' . $bill_pbb['rt_rw_wp']. ', ' .$bill_pbb['kelurahan_wp'] . ', ' . $bill_pbb['kecamatan_wp'] . ', ' . $bill_pbb['kotakab_wp'] . '</td></tr>';
                        $html .= '<tr><td>Alamat OP</td> <td style="width:2px">:</td> <td>' . $bill_pbb['alamat_op'] . ' RT/RW ' . $bill_pbb['rt_rw_op']. ', ' .$bill_pbb['kelurahan_op'] . ', ' . $bill_pbb['kecamatan_op'] . ', ' . $bill_pbb['kotakab_op'] . '</td></tr>';
                        $html .= '<tr><td>Luas Bumi dan Bangunan</td> <td style="width:2px">:</td> <td>' . $bill_pbb['luas_bumi_bangunan_op'] . '</td></tr>';
                        $html .= '<tr><td>Nilai Pajak</td> <td style="width:2px">:</td> <td>Rp.' . $CI->format->currency($bill_pbb['nilai']) . '</td></tr>';
                        $html .= '<tr><td>Denda</td> <td style="width:2px">:</td> <td>Rp.' . $CI->format->currency($bill_pbb['denda']) . '</td></tr>';
                        $html .= '<tr><td>Tagihan</td> <td style="width:2px">:</td> <td>Rp.' . $CI->format->currency($bill_pbb['total']) . '</td></tr>';
                        $html .= '<tr><td>Status</td> <td style="width:2px">:</td> <td>' . $bill_pbb['status_bayar'] . '</td></tr>';
                    }
                }
            } else {
                if (isset($response['data']['kode_billing'])) {
                    $html .= '<tr><td>Kode&nbsp;Bayar</td> <td style="width:2px">:</td> <td class="px-0">' . $response['data']['kode_billing'] . '</td></tr>';
                }
                if (isset($response['data']['npwpd'])) {
                    $html .= '<tr><td>NPWPD</td> <td style="width:2px">:</td> <td class="px-0">' . $response['data']['npwpd'] . '</td></tr>';
                }
                if (isset($response['data']['nama_wajib_pajak'])) {
                    $html .= '<tr><td>Nama WP</td> <td style="width:2px">:</td> <td class="px-0">' . $response['data']['nama_wajib_pajak'] . '</td></tr>';
                }
                if (isset($response['data']['alamat_wajib_pajak'])) {
                    $html .= '<tr><td>Alamat WP</td> <td style="width:2px">:</td> <td class="px-0">' . $response['data']['alamat_wajib_pajak'] . '</td></tr>';
                }
                if (isset($response['data']['nilai'])) {
                    $html .= '<tr><td>Nilai Pajak</td> <td style="width:2px">:</td> <td class="px-0">Rp.' . ($CI->format->currency($response['data']['nilai'])) . '</td></tr>';
                }
                if (isset($response['data']['denda'])) {
                    $html .= '<tr><td>Denda</td> <td style="width:2px">:</td> <td class="px-0">Rp.' . ($CI->format->currency($response['data']['denda'])) . '</td></tr>';
                }
                if (isset($response['data']['total'])) {
                    $html .= '<tr><td>Tagihan</td> <td style="width:2px">:</td> <td class="px-0">Rp.' . ($CI->format->currency($response['data']['total'])) . '</td></tr>';
                }
                if (isset($response['data']['status_bayar'])) {
                    $html .= '<tr><td>Status</td> <td style="width:2px">:</td> <td class="px-0">' . $response['data']['status_bayar'] . '</td></tr>';
                }

                if (isset($response['data']['masa_pajak_1']) && isset($response['data']['masa_pajak_2'])) {
                    $html .= '<tr><td colspan="3"><strong>Masa Pajak</strong></td></tr>';
                    $html .= '<tr><td colspan="3">' . $this->formatDateLocalized($response['data']['masa_pajak_1']) . ' s/d ' . $this->formatDateLocalized($response['data']['masa_pajak_2']) . '</td></tr>';
                }
                if (isset($response['data']['details_op']) && !empty($response['data']['details_op'])) {
                    foreach ($response['data']['details_op'] as $index => $detail_op) {
                        $html .= '<tr><td colspan="3"><strong>Objek Pajak Ke-' . ($index + 1) . ' </strong></td></tr>';
                        $html .= '<tr><td>NOP</td> <td style="width:2px">:</td> <td class="px-0">' . $detail_op['no_objek_pajak'] . '</td></tr>';
                        $html .= '<tr><td>Nama OP</td> <td style="width:2px">:</td> <td class="px-0">' . $detail_op['nama_objek_pajak'] . '</td></tr>';
                        $html .= '<tr><td>Alamat OP</td> <td style="width:2px">:</td> <td class="px-0">' . $detail_op['alamat_objek_pajak'] . '</td></tr>';
                    }
                }
            }

            $html .= '</table></div>';

            return $html;
        }

        return false;
    }

    public function formatDateLocalized($date)
    {
        if (!$date) {
            return '';
        }

        setlocale(LC_ALL, 'id');
        return strftime('%A, %d %b %Y', strtotime($date));
    }
}
