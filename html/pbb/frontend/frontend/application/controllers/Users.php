<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Users extends CI_Controller {
    public function usaha(){
        $this->check_user();
        $this->load->model('m_usaha');
        $data['type'] = $this->m_db->get('pajak_type');
        $users_usaha = $this->m_usaha->getByUserActive($_SESSION['usera_id']);
        $users_usaha_pending = $this->m_usaha->getByUserPending($_SESSION['usera_id']);
        $data['main'] = $this->getReUsahaList($users_usaha);
        $data['pending'] = $this->getReUsahaList($users_usaha_pending);
        $this->load->view('theme/header');
        $this->load->view('users/usaha',$data);
        $this->load->view('theme/footer');
    } 
    
    public function transaksi(){
        $this->check_user();
        $this->load->model('m_sppt');
        $main = $this->m_sppt->getByUser($_SESSION['usera_id']);
        $data['main'] = $main;
        $this->load->view('theme/header');
        $this->load->view('users/transaksi',$data);
        $this->load->view('theme/footer');
    }
    
    public function transaksi_del(){
        $this->check_user();
	    $id = $this->input->post('txDID');
	    $array_update = ['status' => '4'];
	    $this->m_db->_update_where('users_sppt','id',$id,$array_update);
	    $array_pbb = ['id_users_sppt' => 0]; 
	    $this->m_db->_update_where('users_sppt_pbb','id_users_sppt',$id,$array_pbb);
        $this->session->set_flashdata('msg', "Transaksi berhasil dibatalkan");
	    redirect('users/transaksi');
    }
    
    public function usaha_detail($id){
        $this->check_user();
        $this->load->model('m_usaha');
        $usaha = $this->m_db->get_where_id('users_usaha',$id);
        $sppt = $this->m_usaha->getSpptList($usaha[0]['id']);
        $data['usaha'] = $usaha[0];
        $data['sppt'] = $sppt;
        $this->load->view('theme/header');
        $this->load->view('users/usaha_detail',$data);
        $this->load->view('theme/footer');
    }
    
    public function sppt($id){
        $this->check_user();
        $this->load->model('m_usaha');
        $sppt = $this->m_usaha->getSpptDetail($id);
        $data['main'] = $sppt[0];
        $this->load->view('theme/header');
        $this->load->view('users/sppt',$data);
        $this->load->view('theme/footer');
    }
    
    public function print_sppt($id){
        $this->check_user();
        $this->load->model('m_usaha');
        $sppt = $this->m_usaha->getSpptDetail($id);
        $data['main'] = $sppt[0];
        $this->load->view('theme/print_sppt',$data);
    }
    
    public function sppt_bphtb($id){
        $this->check_user();
        $this->load->model('m_usaha');
        $sppt = $this->m_usaha->getSpptDetail($id);
        $data['main'] = $sppt[0];
        $this->load->view('theme/header');
        $this->load->view('users/sppt_bphtb',$data);
        $this->load->view('theme/footer');
    }
    
    public function print_sppt_bphtb($id){
        $this->check_user();
        $this->load->model('m_usaha');
        $sppt = $this->m_usaha->getSpptDetail($id);
        $data['main'] = $sppt[0];
        $this->load->view('theme/print_sppt_bphtb',$data);
    }
    
    public function print_sppt_pbb($id,$id_pbb){
        $this->check_user();
        $this->load->model('m_usaha');
        $pbb = $this->m_db->get_where_id('users_sppt_pbb',$id_pbb);
        $sppt = $this->m_db->get_where_id('users_sppt',$id);
        $data['main'] = $pbb[0];
        $data['id'] = $id;
        $data['sppt'] = $sppt[0];
        $this->load->view('theme/print_sppt_pbb',$data);
    }

    public function sppt_pbb($id,$id_pbb){
        $this->check_user();
        $this->load->model('m_usaha');
        $pbb = $this->m_db->get_where_id('users_sppt_pbb',$id_pbb);
        $sppt = $this->m_db->get_where_id('users_sppt',$id);
        $data['main'] = $pbb[0];
        $data['id'] = $id;
        $data['sppt'] = $sppt[0];
        $this->load->view('theme/header');
        $this->load->view('users/sppt_pbb',$data);
        $this->load->view('theme/footer');
    }
    
    function getReUsahaList($data){
        $this->load->model('m_usaha');
        if(count($data) != 0){
            foreach($data as $row){
                $tax_type = $this->m_usaha->getUserObjekPajak($row['id_users_usaha']);
                $data_array[] = [
                                 'id' => $row['id_users_usaha'],
                                 'npwpd' => $row['npwpd'],
                                 'name' => $row['name'],
                                 'badan_usaha_name' => $row['badan_usaha_name'],
                                 'created_date' => $row['created_date'],
                                 'tax_type' => $tax_type
                                ];
            }
            return $data_array;
        }
    }
    
    public function form_usaha($type){
        $this->check_user();
        $this->load->view('theme/header');
        $this->load->view('jenis/'.$type.'/add_form');
        $this->load->view('theme/footer');
    }
    
    public function usaha_add(){
        $this->check_user();
        $data['type'] = $this->m_db->get('pajak_type');
        $data['usaha'] = $this->m_db->get('badan_usaha','name');
        $data['province'] = $this->m_db->get('place_prov','name');
        $this->check_user();
        $this->load->view('theme/header');
        $this->load->view('users/usaha_add_form',$data);
        $this->load->view('theme/footer');
    }
    
    public function usaha_save(){
        $this->check_user();
        $name = $this->getInputReady($this->input->post('txName'));
        $usaha = (int)$this->input->post('cbUsaha');
        $phone = $this->getInputReady($this->input->post('txPhone'));
        $address = $this->getInputReady($this->input->post('txAddress'));
        $rt = $this->getInputReady($this->input->post('txRT'));
        $rw = $this->getInputReady($this->input->post('txRW'));
        $prov = (int)$this->input->post('cbProv');
        $kab = (int)$this->input->post('cbKab');
        $kec = (int)$this->input->post('cbKec');
        $kel = (int)$this->input->post('cbKel');
        $array = [
                    'name' => $name,
                    'id_users' => $_SESSION['usera_id'],
                    'id_badan_usaha' => $usaha,
                    'phone' => $phone,
                    'address' => $address,
                    'rt' => $rt,
                    'rw' => $rw,
                    'prov' => $this->getPlaceName('place_prov',$prov),
                    'kab' => $this->getPlaceName('place_kab',$kab),
                    'kec' => $this->getPlaceName('place_kec',$kec),
                    'kel' => $this->getPlaceName('place_kel',$kel),
                 ];
        $this->m_db->_insert('users_usaha',$array);
        $insert_id = $this->db->insert_id();
        $tax_type = $this->input->post('txTaxType');
        foreach($tax_type as $row){
            $array_type = [
                            'id_users_usaha' => $insert_id,
                            'id_pajak_type' => $row
                          ];
            $this->m_db->_insert('users_objek_pajak',$array_type);
        }

        $this->session->set_flashdata('msg', $this->fun->alert_box('success',"Usaha <strong>".$name."</strong> telah berhasil ditambahkan, silahkan tunggu validasi data."));
        redirect('users/usaha');
    }
    
    public function add_loc($code="",$page=""){
        $nop = $this->input->post('txNOP');
        $usaha = $this->input->post('txModUsaha');
        $type = $this->input->post('txModType');
        $name = strip_tags(trim($this->input->post('txName')));
        $desc = strip_tags(trim($this->input->post('txDesc')));
        $coord = $this->input->post('txLat').",".$this->input->post('txLong');
        $array = [
                    'nop' => $nop,
                    'id_users_usaha' => (int)$usaha,
                    'id_users' => $_SESSION['usera_id'],
                    'id_pajak_type' => (int)$type,
                    'name' => $name,
                    'coordinates' => $coord,
                    'description' => $desc
                 ];
        $this->m_db->_insert('users_usaha_loc',$array);
        $datausaha = $this->m_db->get_where_id('users_usaha',$usaha);
        $this->session->set_flashdata('msg', $this->fun->alert_box('success',"Lokasi baru telah ditambahkan ke <strong>".$datausaha[0]['name'].'</strong>'));
        if($page == ""){
            redirect('users/payment/'.$code);
        }else{
            redirect('users/'.$page);
        }
    }
    
    public function add_tax($id_pajak,$id_usaha){
        $data = $this->m_db->get_where_id('pajak_type', $id_pajak);
        $usaha = $this->m_db->get_where_id('users_usaha', $id_usaha);
        $array = [
                    'id_users_usaha' => $id_usaha,
                    'id_pajak_type' => $id_pajak
                 ];
        $this->m_db->_insert('users_objek_pajak',$array);
        $this->session->set_flashdata('msg', '<strong>'.$data[0]['name']."</strong> telah ditambahkan ke <strong>".$usaha[0]['name'].'</strong>');
        redirect('users/usaha');
    }
    
    public function payment($code,$usaha=""){
        $this->check_user();
        
        $type = $this->m_db->get_where_col('pajak_type','code',$code);
        $info = $this->m_db->get_where_col_2('pajak_info','id_type',$type[0]['id'],'page',"bayar");
        
        if($usaha!=""){
            $users_usaha = $this->m_db->get_where_id('users_usaha',$usaha);
            $loc = $this->m_db->get_where_col_2('users_usaha_loc','id_users_usaha',$usaha,'id_pajak_type',$type[0]['id']);
            $data['loc'] = $loc;
            $data['users_usaha'] = $users_usaha;
            $data['sel'] = '1';
        } else {
            $data['users_usaha'] = $this->m_db->get_where_col_2('users_usaha','id_users',$_SESSION['usera_id'],'status','1');
            $data['loc'] = 0;
            $data['sel'] = 0;
        }
        
        $data['tax_type_id'] = $type[0]['id'];
        $data['tax_fine'] = $type[0]['tax_fine'];
        $data['info'] = $info[0]['content'];
       
        switch ($code){
            case "airtanah":
                $data['usaha'] = $this->m_db->get('usaha','title');
                $data['type'] = $type;
                break; 
            case "hiburan":
                $data['type'] = $type;
                $data['hiburan_type'] = $this->m_db->get('pajak_hiburan_type','title');
                break;
            case "minerba":
                $data['type'] = $type;
                $data['type_minerba'] = $this->m_db->get('pajak_minerba_type');
                $data['tax'] = $type[0]['tax'];
                break;
            case "reklame":
                $data['type'] = $type;
                $data['type_reklame'] = $this->m_db->get('reklame_jenis');
                $data['nfr'] = $this->m_db->get('reklame_nfr');
                $data['nfj'] = $this->m_db->get('reklame_nfj');
                $data['nsp'] = $this->m_db->get('reklame_nsp');
                break;
            default:
                $data['type'] = $type;
                $data['tax'] = $type[0]['tax'];
        }
        


        $this->load->view('theme/header');
        $this->load->view('jenis/'.$code.'/form',$data);
        $this->load->view('theme/footer');
    }
    
    public function add_sppt($type){
        $user = $_SESSION['usera_id'];
        $id_usaha = (int)$this->input->post('txUsersUsahaID');
        $pajak_type = $this->m_db->get_where_col('pajak_type','code',$type);
        $usaha_id = (int)$this->input->post('cbType');
        $usaha = $this->m_db->get_where_id('usaha',$usaha_id);
        switch($type){
            case "airtanah":
                $class = $this->input->post('txClassName');
                $volume = (int)$this->input->post('txUse');
                $loc = strip_tags(trim($this->input->post('txLoc')));
                $inputDesc = 'Usaha : '.$usaha[0]['title'].'<br />Kelas : '.$class.'<br />Pemakaian: '.$volume;
            break;
            case "hiburan":
                $type_value = $this->input->post('cbType');
                $type = $this->m_db->get_where_id('pajak_hiburan_type',$type_value);
                $inputDesc = "Pajak Hiburan kategori <strong>".$type['title'].'</strong>';
            break;
            case "bphtb":
                $nop = (int)$this->input->post('txNOP');
                $bphtb = $this->input->post('txResult');
                $id_pbb = (int)$this->input->post('txIDPBB');
                $njop = $this->input->post('txNJOP');
                if($this->input->post('chkCheck') != null){
                    $warisan = '1';
                }else{
                    $warisan = '0';
                }
                $pbb_array = ['id_pbb' => $id_pbb,
                              'nop' => $nop,
                              'value' => $bphtb
                            ];
                $inputDesc = "";
            break;
            case "pbb":
                $pbb = $this->input->post('txIDPBB');
                $pbb_array = ['id_pbb' => (int)$pbb,
                              'status' => '1'
                             ];
                $inputDesc = "";
            break;
            case "reklame":
                $type_reklame = $this->input->post('cbType');
                $lembar = $this->input->post('txLembar');
                $panjang = strip_tags($this->input->post('txP'));
                $lebar = strip_tags($this->input->post('txL'));
                $tinggi = strip_tags($this->input->post('txT'));
                $sisi = $this->input->post('txS');
                $qty = (int)$this->input->post('txLembar');
                $nfr_id = $this->input->post('txNFR');
                $nfr_val = $this->input->post('cbNFR');
                $nfj_id = $this->input->post('txNFJ');
                $nfj_val = $this->input->post('cbNFJ');
                $nsp_id = $this->input->post('txNSP');
                $nsp_val = $this->input->post('cbNSP');
                $total_pajak = $this->input->post('txResult');
                $loc = strip_tags(trim($this->input->post('txLoc')));
            default:
                $inputDesc = "-";
        }
        $prep_array = [
                        'id_users' => $user,
                        'id_usaha' => $id_usaha,
                        'id_objek_pajak' => $pajak_type[0]['id'],
                        'id_pajak_type' => $pajak_type[0]['id'],
                        'description' => $inputDesc,
                        'masa_date' => $this->input->post('txMasa'),
                        'token' => $this->getPaymentToken($user),
                        'created_date' => date('Y-m-d H:i:s'),
                        'tax' => $this->format->no_currency($this->input->post('txResult')),
                        'fine' => $this->format->no_currency($this->input->post('txFine')),
                        'id_users_usaha_loc' => (int)$this->input->post('cbLoc')
                        
                      ];
        if($type == "pbb" || $type=="bphtb"){
            $prep_array = array_merge($prep_array,$pbb_array);
        }
        $this->m_db->_insert('users_sppt',$prep_array);
        $insert_id = $this->db->insert_id();
        if($type == "reklame"){
            $array_sppt = [
                            'id_users_sppt' => $insert_id,
                            'id_type_reklame' => $type_reklame,
                            'panjang' => $panjang,
                            'lebar' => $lebar,
                            'tinggi' => $tinggi,
                            'sisi' => $sisi,
                            'qty' => $qty,
                            'nfr_id' => $nfr_id,
                            'nfr_value' => $nfr_val,
                            'nfj_id' => $nfj_id,
                            'nfj_value' => $nfj_val,
                            'nsp_id' => $nsp_id,
                            'nsp_value' => $nsp_val,
                            'total_pajak' => $total_pajak
                          ];
            $this->m_db->_insert('users_sppt_reklame',$array_sppt);
        }

        if($type == 'pbb'){
            $array_pbb = ['id_users_sppt'=>$insert_id];
            $this->m_db->_update_where('users_sppt_pbb','id',$pbb,$array_pbb);
        }
        
        redirect('users/transaksi');
    }
    
    function add_sppt_pbb($id){
        $user = $_SESSION['usera_id'];
        $pbb_data = $this->m_db->get_where_id('users_sppt_pbb',$id);
        $main = $pbb_data[0];
        $total_njop_bumi = $main['op_price_bumi'] * $main['op_luas_bumi'];
        $total_njop_bangunan = $main['op_price_bangunan'] * $main['op_luas_bangunan'];
        $njop_dasar = $total_njop_bumi + $total_njop_bangunan;
        $njop_pbb = $njop_dasar - $main['njoptkp'];
        $njkp = $njop_pbb * ($main['njkp_percent']/100);
        $total_pbb = $njkp * ($main['pbb_percent']/100);
        $array = [
            'id_users' => $user,
            'id_usaha' => '0',
            'id_objek_pajak' => '6',
            'id_pajak_type' => '6',
            'description' => "",
            'masa_date' => $main['masa'].'-00',
            'token' => $this->getPaymentToken($user),
            'created_date' => date('Y-m-d H:i:s'),
            'tax' => $this->format->no_currency($total_pbb),
            'fine' => '0',
            'id_users_usaha_loc' => '0',
            'id_pbb' => $id,
            'status' => '1'
          ];
          $this->m_db->_insert('users_sppt',$array); 
          redirect('users/transaksi');
    }
    
    function add_sppt_minerba(){
        $user = $_SESSION['usera_id'];
        $id_usaha = $this->input->post('txUsersUsahaID');
        $id_loc = $this->input->post('cbLoc');
        $tax = $this->input->post('txTax');
        $array_type = $this->input->post('cbType');
        $array_use = $this->input->post('txUse');
        $totalTax = 0;
        $masa = $this->input->post('txMasa');
        for($i=0;$i<count($array_type);$i++){
            $type = $this->m_db->get_where_col('pajak_minerba_type','id',$array_type[$i]);
            $totalTax += $array_use[$i]*$type[0]['harga_dasar']*($tax/100);
        }
        $array = [
                    'id_users' => $user,
                    'id_usaha' => $id_usaha,
                    'id_users_usaha_loc' => $id_loc,
                    'id_objek_pajak' => '4', //pajak type minerba
                    'id_pajak_type' => '4',
                    'description' => "",
                    'masa_date' => $masa,
                    'token' => $this->getPaymentToken($user),
                    'created_date' => date('Y-m-d H:i:s'),
                    'tax' => $this->format->no_currency($totalTax),
                    'fine' => $this->format->no_currency($this->input->post('txFine')),
                    'status' => 0
                 ];
        $this->m_db->_insert('users_sppt',$array); 
        $insert_id = $this->db->insert_id();
        for($i=0;$i<count($array_type);$i++){
            $type = $this->m_db->get_where_col('pajak_minerba_type','id',$array_type[$i]);
            $array_minerba = [
                                'name' => $type[0]['title'],
                                'harga_dasar' => $type[0]['harga_dasar'],
                                'pemakaian' => $array_use[$i],
                                'total' => $array_use[$i]*$type[0]['harga_dasar'],
                                'id_sppt' => $insert_id
                             ];    
            $this->m_db->_insert('users_sppt_minerba',$array_minerba);
        }
         
         redirect('users/transaksi');
    }
    
    
    function getPaymentToken($id){ //untuk membuat token bayar & dan barcode code 
        $set = '1234567890';
		$code = substr(str_shuffle($set), 0, 8);
		$result = $id.$code;
		return $result;
    }
    
    public function check_user(){
        if(!isset($_SESSION['usera_id'])){
            redirect('auth/login');
        }
    }
    
    function getInputReady($val){
        return strip_tags(trim($val));
    }
    
    function getPlaceName($table,$id){
        $data = $this->m_db->get_where_col($table,'id',$id);
        return $data[0]['name'];
    }
}
