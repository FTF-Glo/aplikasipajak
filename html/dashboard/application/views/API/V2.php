<?php
date_default_timezone_set('Asia/Jakarta');

require(APPPATH . 'libraries/Format.php');
require('RestController.php');


// use chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');
class V2 extends RestController
{
    protected $imageUrl = 'images' . DIRECTORY_SEPARATOR . 'reklame_pict' . DIRECTORY_SEPARATOR;
    protected $imagePath = FCPATH . 'images' . DIRECTORY_SEPARATOR . 'reklame_pict' . DIRECTORY_SEPARATOR;
    protected $max_distance = 25;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function login_post()
    {
        $this->load->library('apiauth');
        $username = $this->post('username');
        $password = $this->post('password');
    
        if($this->apiauth->validate_password($username, $password)){
            $this->response([
                'status' => true,
                'msg' => 'Login berhasil'
            ],200);
        }else{
            $this->response([
                'status' => false,
                'msg' => 'Username atau password anda salah'
            ],401);
        }
    }

    // master
    public function wp_get()
    {
        $id = $this->get('id');
        $npwpd = $this->get('npwpd');
        if ($id) {
            $this->db->where('wp_usaha.id', $id);
        }
        if ($npwpd) {
            $this->db->where('wp_usaha.npwpd', $npwpd);
        }

        // join
        $this->db->join('place_prov', 'wp_usaha.prov = place_prov.id', 'left');
        $this->db->join('place_kab', 'wp_usaha.kab = place_kab.id', 'left');
        $this->db->join('place_kec', 'wp_usaha.kec = place_kec.id', 'left');
        $this->db->join('place_kel', 'wp_usaha.kel = place_kel.id', 'left');
        $this->db->join('usaha_badan', 'wp_usaha.id_badan_usaha = usaha_badan.id');

        // select
        $this->db->select([
            'wp_usaha.*',
            'place_prov.name as prov',
            'place_kab.name as kab',
            'place_kec.name as kec',
            'place_kel.name as kel',
            'CONCAT(wp_usaha.name, ", ", usaha_badan.code) as name'
        ]);
        $query = $this->db->get('wp_usaha');
        if ($id || $npwpd) {
            $result = $query->row();
        } else {
            $result = $query->result();
        }
        $this->response($result, 200);
    }
    public function jenisreklame_get()
    {
        $id = $this->get('id');
        if ($id) {
            $this->db->where('id', $id);
        }
        $this->db->where('active', 1);
        $query = $this->db->get('reklame_jenis');
        if ($id) {
            $result = $query->row();
        } else {
            $result = $query->result();
        }
        $this->response($result, 200);
    }
    public function fungsiruang_get()
    {
        $id = $this->get('id');
        if ($id) {
            $this->db->where('id', $id);
        }
        $this->db->where('active', 1);
        $query = $this->db->get('reklame_nfr');
        if ($id) {
            $result = $query->row();
        } else {
            $result = $query->result();
        }
        $this->response($result, 200);
    }
    public function fungsijalan_get()
    {
        $id = $this->get('id');
        if ($id) {
            $this->db->where('id', $id);
        }
        $this->db->where('active', 1);
        $query = $this->db->get('reklame_nfj');
        if ($id) {
            $result = $query->row();
        } else {
            $result = $query->result();
        }
        $this->response($result, 200);
    }
    public function status_get()
    {
        $id = $this->get('id');
        if ($id) {
            $this->db->where('id', $id);
        }
        $this->db->where('active', 1);
        $query = $this->db->get('status');
        if ($id) {
            $result = $query->row();
        } else {
            $result = $query->result();
        }
        $this->response($result, 200);
    }
    public function petugas_get()
    {
        $id = $this->get('id');
        if ($id) {
            $this->db->where('id', $id);
        }
        $this->db->where('active', 1);
        $query = $this->db->get('petugas');
        if ($id) {
            $result = $query->row();
        } else {
            $result = $query->result();
        }
        $this->response($result, 200);
    }

    // pendataan
    public function pendataan_get()
    {
        $id = $this->get('id');
        $kode = $this->get('kode');
        if ($id) {
            $this->db->where('pendataan.id', $id);
        }
        if ($kode) {
            $this->db->where('pendataan.kode', $kode);
        }

        $this->db
            ->select([
                'pendataan.*',
                'CONCAT(wp_usaha.name, ", ", usaha_badan.code) as wp',
                'reklame_jenis.title as jenis_reklame',
                'reklame_nfr.title as fungsi_ruang_reklame',
                'reklame_nfj.title as fungsi_jalan_reklame',
                'status.name as status',
                'petugas.name as petugas',
                'CONCAT("' . base_url() . '",reklame_pict_1) as reklame_pict_1',
                'CONCAT("' . base_url() . '",reklame_pict_2) as reklame_pict_2',
                'CONCAT("' . base_url() . '",reklame_pict_3) as reklame_pict_3',
                'place_kel.name as kel_name',
                'place_kec.name as kec_name',
            ])
            ->where('valid', 1)
            ->join('wp_usaha', 'wp_usaha_id = wp_usaha.id', 'left')
            ->join('reklame_jenis', 'reklame_jenis_id = reklame_jenis.id', 'left')
            ->join('reklame_nfr', 'reklame_nfr_id = reklame_nfr.id', 'left')
            ->join('reklame_nfj', 'reklame_nfj_id = reklame_nfj.id', 'left')
            ->join('status', 'status_id = status.id', 'left')
            ->join('petugas', 'petugas_id = petugas.id', 'left')
            ->join('place_kel', 'pendataan.id_kel = place_kel.id', 'left')
            ->join('place_kec', 'place_kel.id_kec = place_kec.id', 'left')
            ->join('usaha_badan', 'wp_usaha.id_badan_usaha = usaha_badan.id', 'left');

        $query = $this->db->get('pendataan');
        if ($id) {
            $result = $query->row();
        } else {
            $result = $query->result();
        }
        $this->response($result, 200);
    }
    public function pendataan_post()
    {
        $status = true;

        $kode = $this->post('kode');
        $wp_usaha_id = $this->post('wp_usaha_id');
        $jenis_reklame_id = $this->post('jenis_reklame_id');
        $fungsi_ruang_id = $this->post('fungsi_ruang_id');
        $fungsi_jalan_id = $this->post('fungsi_jalan_id');
        $data = [
            'kode' => $kode,
            // 'wp_usaha_id' => $wp_usaha_id,
            'reklame_jenis_id' => $jenis_reklame_id,
            'reklame_nfr_id' => $fungsi_ruang_id,
            'reklame_nfj_id' => $fungsi_jalan_id,
            'lokasi' => $this->post('lokasi'),
            'detail_lokasi' => $this->post('detail_lokasi'),
            'reklame_pict_1' => $this->_imageUpload('reklame_pict_1'),
            'reklame_pict_2' => $this->_imageUpload('reklame_pict_2'),
            'reklame_pict_3' => $this->_imageUpload('reklame_pict_3'),
            // 'status_id' => $this->post('status_id'),
            'id_kel' => $this->post('id_kel'),
            'petugas_id' => $this->post('petugas_id'),
            'lat' => $this->post('lat'),
            'long' => $this->post('long'),
            'catatan' => $this->post('catatan'),
            'tanggal' => date('Y-m-d H:i:s'),
        ];
        $pendataan = $this->db->get_where('pendataan', [
            'kode' => $kode,
            // 'wp_usaha_id' => $wp_usaha_id,
            'reklame_jenis_id' => $jenis_reklame_id,
            'reklame_nfr_id' => $fungsi_ruang_id,
            'reklame_nfj_id' => $fungsi_jalan_id
        ])->result();
        $this->db->trans_start();
        if ($pendataan) {
            $this->db->where([
                'kode' => $kode,
                'wp_usaha_id' => $wp_usaha_id,
                'reklame_jenis_id' => $jenis_reklame_id,
                'reklame_nfr_id' => $fungsi_ruang_id,
                'reklame_nfj_id' => $fungsi_jalan_id
            ])
                ->update('pendataan', $data);
        } else {
            $this->db->insert('pendataan', $data);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $status = false;
        }
        $this->response([
            'status' => $status
        ], 200);
    }

    // monitoring
    public function monitoring_post()
    {
        $status = true;

        $pendataan_id = $this->post('pendataan_id');
        $kode = $this->post('kode');
        $petugas_id = $this->post('petugas_id');
        $status_id = $this->post('status_id');
        $lat = $this->post('lat');
        $long = $this->post('long');

        $data = [
            'pendataan_id' => $pendataan_id,
            'kode' => $kode,
            'petugas_id' => $petugas_id,
            'status_id' => $status_id,
            'catatan' => $this->post('catatan'),
            'lat' => $lat,
            'long' => $long,
            'tanggal' => date('Y-m-d H:i:s'),
        ];
        $monitoring = $this->db->get_where('monitoring', [
            'pendataan_id' => $pendataan_id,
            'kode' => $kode,
            'petugas_id' => $petugas_id,
        ])
            ->result();

        $this->db->trans_begin();
        if ($monitoring) {
            $this->db->where([
                'pendataan_id' => $pendataan_id,
                'kode' => $kode,
                'petugas_id' => $petugas_id,
            ])
                ->update('monitoring', $data);
        } else {
            $this->db->insert('monitoring', $data);
        }
        // update pendataan
        $this->db->where('id', $pendataan_id)
            ->update('pendataan', [
                'status_id' => $status_id,
                'lat' => $lat,
                'long' => $long
            ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $status = false;
        } else {
            $this->db->trans_commit();
        }
        $this->response([
            'status' => $status
        ], 200);
    }

    // get reklame terdekat
    public function nearestreklame_get()
    {
        /** https://blog.bobbyallen.me/2015/04/04/get-nearest-places-from-mysql-with-latitude-and-longditude/ */
        $lat = $this->db->escape($this->get('lat'));
        $long = $this->db->escape($this->get('long'));
        $max_distance = ($this->get('max_distance') != null ? $this->get('max_distance') : $this->max_distance);
        $query = $this->db->select([
            'pendataan.*',
            '111.045 * DEGREES(ACOS(COS(RADIANS(' . $lat . ')) 
                * COS(RADIANS(pendataan.lat))
                * COS(RADIANS(pendataan.long) - RADIANS(' . $long . '))
                + SIN(RADIANS(' . $lat . '))
                * SIN(RADIANS(pendataan.lat)))) AS distance_in_km',
            'CONCAT("' . base_url() . '",reklame_pict_1) as reklame_pict_1',
            'CONCAT("' . base_url() . '",reklame_pict_2) as reklame_pict_2',
            'CONCAT("' . base_url() . '",reklame_pict_3) as reklame_pict_3'
        ], false)
            ->where('valid', 1)
            ->where('(111.045 * DEGREES(ACOS(COS(RADIANS(' . $lat . ')) 
                * COS(RADIANS(pendataan.lat))
                * COS(RADIANS(pendataan.long) - RADIANS(' . $long . '))
                + SIN(RADIANS(' . $lat . '))
                * SIN(RADIANS(pendataan.lat))))) <=', $max_distance)
            ->order_by('distance_in_km', 'asc')
            ->get('pendataan');

        $result = $query->result();
        $this->response($result, 200);
    }

    // upload image
    public function _imageUpload($name)
    {
        $imageUrl = '';

        $config['upload_path']          = $this->imagePath;
        $config['allowed_types']        = 'jpg|png';
        $config['max_size']             = 5120;
        $config['encrypt_name']         = true;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload($name)) {
            $uploadedFile = $this->upload->data();
            $imageUrl = $this->imageUrl . $uploadedFile['file_name'];
        }

        return $imageUrl;
    }

    // get kelurahan by Taufiq
    public function kel_get(){
        $id = $this->get('id');
        $id_kec = $this->get('id_kec');
        $code = $this->get('code');
        if ($id) {
            $this->db->where('place_kel.id', $id);
        }
        if ($code) {
            $this->db->where('place_kel.code', $code);
        }
        if ($id_kec) {
            $this->db->where('place_kel.id_kec', $id_kec);
        }
        $this->db
            ->select([
                'place_kel.*',
                'place_kec.name as kec_name',
                'place_kab.name as kab_name',
                'place_prov.name as prov_name',
            ])
            ->join('place_kec', 'place_kel.id_kec = place_kec.id', 'left')
            ->join('place_kab', 'place_kel.id_kab = place_kab.id', 'left')
            ->join('place_prov', 'place_kel.id_prov = place_prov.id', 'left');

        $query = $this->db->get('place_kel');
        if ($id) {
            $result = $query->row();
        } else {
            $result = $query->result();
        }
        $this->response($result, 200);
    }
    public function kec_get(){
        $id = $this->get('id');
        $code = $this->get('code');
        if ($id) {
            $this->db->where('place_kel.id', $id);
        }
        if ($id) {
            $this->db->where('place_kel.code', $code);
        }
        $this->db
            ->select([
                'place_kec.*',
                'place_kab.name as kab_name',
                'place_prov.name as prov_name',
            ])
            ->join('place_kab', 'place_kec.id_kab = place_kab.id', 'left')
            ->join('place_prov', 'place_kec.id_prov = place_prov.id', 'left');

        $query = $this->db->get('place_kec');
        if ($id) {
            $result = $query->row();
        } else {
            $result = $query->result();
        }
        $this->response($result, 200);
    }
}
