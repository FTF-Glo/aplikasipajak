<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jenis extends CI_Controller {
    function _remap($param) {
        $this->index($param);
    }
    
    public function index($name=""){
        $par = $this->input->get('page');
        $type = $this->m_db->get_where_col('pajak_type','code',$name);
        if($par == ''){
            $content = $this->m_db->get_where_col_2('pajak_content','id_type',$type[0]['id'],'hide', '0');
            $data['content'] = $content;
            $data['page'] = $name;
            $data['title'] = $type[0]['name'];
            $this->load->view('theme/header');
    		$this->load->view('jenis/list',$data);
    		$this->load->view('theme/footer');
        } else {
            if($par == "bayar"){
                if(isset($_SESSION['usera_id'])){
                    $usaha = $this->m_db->get_where_col_2('users_usaha','id_users',$_SESSION['usera_id'],'status','1');
                    if(count($usaha) != 0){
                        redirect('users/payment/'.$name); 
                    }else {
                        $message = "
                                    <ul>
                                        <li>Silahkan daftarkan usaha anda</li>
                                        <li>Isi data secara lengkap</li>
                                        <li>Permintaan akan segera diproses dan diberitahukan melalui E-mail tentang status pendaftaran Usaha</li>
                                    </ul>
                                   ";
                        $this->session->set_flashdata('msg', $this->fun->alert_box('warning',$message));
                        redirect('users/usaha_add');
                    }
                }else {
                    $this->session->set_flashdata('msg', $this->fun->alert_box('warning','Untuk melakukan pembayaran Pajak, silahkan Login atau Register'));
                    redirect('auth/login');
                }
            }else{
                $info = $this->m_db->get_where_col_2('pajak_info','id_type',$type[0]['id'],'page',$par);
                $data['title'] = $type[0]['name'];
                $data['code'] = $name;
                $data['id_type'] = $type[0]['id'];
                $data['tax_fine'] = $type[0]['tax_fine'];
                $data['tax'] = $type[0]['tax'];
                $data['info'] = $info[0]['content'];
                if($name == 'hiburan'){
                    $data['hiburan_type'] = $this->m_db->get('pajak_hiburan_type','title');
                }
                if($name == 'minerba'){
                    $data['type'] = $this->m_db->get('pajak_minerba_type');
                }
                if($name == 'airtanah'){
                    $data['tax'] = $type[0]['tax'];
                    $data['usaha'] = $this->m_db->get('usaha','title');
                }
                if($name=="reklame"){
                    $data['type'] = $this->m_db->get('reklame_jenis');
                    $data['nfr'] = $this->m_db->get('reklame_nfr');
                    $data['nfj'] = $this->m_db->get('reklame_nfj');
                    $data['nsp'] = $this->m_db->get('reklame_nsp');
                }
                if($name=="parkir"){
                    $data['tax_special'] = $type[0]['special_tax'];
                }
                $this->load->view('theme/header');
                if($par == 'info'){
                    $this->load->view('jenis/info',$data);
                }else {
        		  $this->load->view('jenis/'.$name.'/'.$par,$data);
                }
        		$this->load->view('theme/footer');
            }
        }
    }
}
