<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
	public function register(){
		$this->load->view('theme/header');
		$this->load->view('auth/register');
		$this->load->view('theme/footer');
	}
	
	public function login(){
		$this->load->view('theme/header');
		$this->load->view('auth/login');
		$this->load->view('theme/footer');
	}
	
	public function logout(){
		session_destroy();
		unset($_SESSION['usera_id']);
		unset($_SESSION['usera_email']);
		unset($_SESSION['usera_name']);
		redirect();
	}
	
	public function check(){ //login proses
		$this->load->library('form_validation');
		$this->load->helper('email');
		$this->form_validation->set_rules('txEmail', 'Email', 'trim|required|xss_clean|valid_email');
		$this->form_validation->set_rules('txPass', 'Password', 'trim|required|xss_clean');
		if($this->form_validation->run() == true){
			$pass = $this->input->post('txPass');
			$email = strip_tags($this->input->post("txEmail"));
			$cust = $this->m_db->get_where_col('users','email',$email,'users_id');
			if(count($cust) > 0){
				if(password_verify($pass,$cust[0]['password'])){
					$session_data = [
										'usera_id' => $cust[0]['users_id'],
										'usera_email' => $cust[0]['email'],
										'usera_name' => $cust[0]['fullname']
									];
					$this->session->set_userdata($session_data);
					$data_array = ['last_login'=>date('Y-m-d H:i:s')];
					$this->m_db->_update_where('users', 'users_id', $cust[0]['users_id'], $data_array);
					redirect();
				}else {
					$this->session->set_flashdata('msg', $this->fun->alert_box('warning','Email atau Password salah'));
					redirect('auth/login');
				}
				
			} else if($cust[0]['status'] == '0'){
				$this->session->set_flashdata('msg', $this->fun->alert_box('warning','Silahkan lakukan konfirmasi pendaftaran Email !'));
		    }else {
				$this->session->set_flashdata('msg', $this->fun->alert_box('warning','Email atau Password salah'));
				redirect('auth/login');
			}
		}else {
			$this->session->set_flashdata('msg', validation_errors());
			redirect('auth/login');
		}	
	}
	
	
	public function getRegister(){
		$this->load->library('form_validation');
		$this->load->helper('email');
		$this->form_validation->set_rules('txEmail', 'Email', 'trim|required|xss_clean|valid_email');
		$this->form_validation->set_rules('txPass', 'Password', 'trim|required|xss_clean');
		$this->form_validation->set_rules('txName', 'Fullname', 'trim|required|xss_clean');
		$this->form_validation->set_rules('txAddress','Address', 'trim|required|xss_clean');
		$this->form_validation->set_rules('txBirthPlace','Birth Place', 'trim|required|xss_clean');
		
		if($this->form_validation->run() == true){
			$pass = $this->input->post('txPass');
			$mail = strip_tags($this->input->post('txEmail'));
			$name = strip_tags($this->input->post('txName'));
			$birthPlace = strip_tags($this->input->post('txBirthPlace'));
			$ktp = (int)$this->input->post('txKTP');
			$dob = $this->input->post('txDOB');
			$phone = strip_tags($this->input->post('txPhone'));
			$address = strip_tags($this->input->post('txAddress'));
			$hash = password_hash($pass, PASSWORD_DEFAULT);
			$code = $this->generateCode();
			$array = [
				        'fullname' => $name,
				        'ktp' => $ktp,
				        'email' => $mail,
				        'password' => $hash,
				        'created' => date('Y-m-d H:i:s'),
				        'activation_key' => $code,
				        'status' => '0'
				     ];
			$this->m_db->_insert('users',$array);
			$curID = $this->db->insert_id();
			$array_detail = [
								'id_users' => $curID,
								'birth_place' => $birthPlace,
								'dob' => $dob,
								'phone' => $phone,
								'address' => $address
							];
			$this->m_db->_insert('users_detail',$array_detail);
			$to = $mail;
			$subject = "Konfirmasi Pendaftaran";
			$message = 'Hallo '.$name.'<br />
						<p>Permintaan pendaftaran telah dilakukan ke '.$this->config->item('website').'</p>
						<p>Silahkan lakukan konfirmasi dengan <a href="'.base_url().'auth/validation/'.$code.'">klik disini untuk melanjutkan</a></p><br />
						<p>*Abaikan email ini apabila Anda tidak melakukan pendaftaran</p>
						<p>Terima Kasih</p>
					   ';
			$this->fun->sendMail($this->config->item('email'),$this->config->item('email_name'),$to,$subject,$message);
			$this->session->set_userdata('email_regis',$mail);
			$this->session->set_flashdata('msg', "Akun telah berhasil dibuat, silahkan melakukan login");
			redirect('auth/login');	
		}else {
			$this->session->set_flashdata('msg', validation_errors());
			redirect('auth/register');
		}	
	}
	

	
	public function getResetPassword(){
		$email = strip_tags($this->input->post('txEmail'));
		$user = $this->m_db->get_where_col('users','email',$email);
		if(count($user) > 0){
			$code = $this->generateCode().$user[0]['id'];
			$info_array = [
							'reset_key' => $code,
							'reset_date' => date('Y-m-d H:i:s')
						  ];
			$this->m_db->_update('users',$user[0]['id'],$info_array);
			$to = $email;
			$subject = "Permintaan Reset Password di ".$this->config->item('title');
			$message = 'Hallo '.$user[0]['fullname'].'<br />
						<p>Permintaan reset password telah dilakukan di '.$this->config->item('website').'</p>
						<p>Silahkan klik <a href="'.base_url().'auth/validation/'.$code.'">link ini</a> untuk melakukan pembaharuan terhadap password Anda.</p><br />
						<p>*Abaikan email ini apabila Anda tidak melakukan permintaan reset password</p>
						<p>Terima Kasih</p>
					   ';
			$this->fun->sendMail($this->config->item('email'),$this->config->item('email_name'),$to,$subject,$message);
			$this->session->set_flashdata('msg', $this->fun->alert_box('success','Permintaan reset password telah berhasil terkirim ke email '.$email.' !, silahkan check email Anda'));
			redirect('auth/login');
		} else{
			$this->session->set_flashdata('msg', $this->fun->alert_box('warning','Email untuk reset password tidak terdaftar !'));
			redirect('auth/login');
		}
	}
	
	public function reset_pass($code = ""){
		if($code != ""){
			$iCode = strip_tags($code);
			$data = $this->m_db->get_where_col('users','reset_key',$code);
			if(count($data) > 0){
				$exp_date = date("Y-m-d H:i:s", strtotime("+120 minutes",$data[0]['reset_date']));
				if($exp_date > date("Y-m-d H:i:s")){
					$data['id'] = $data[0]['id'];
					$data['email'] = $data[0]['email'];
					$this->load->view('themes/header');
					$this->load->view('auth/reset_password',$data);
					$this->load->view('themes/footer');
				} else {
					$this->session->set_flashdata('msg', $this->fun->alert_box('warning','Reset Password gagal, permintaan sudah melewati batas waktu'));
					redirect('auth/login');
				}
			}
		}else {
			redirect();
		}
	}
	public function reset_confirm(){
		$this->load->library('form_validation');
		$this->load->helper('email');
		
		$this->form_validation->set_rules('txEmail', 'Email', 'trim|required|xss_clean|valid_email');
		$this->form_validation->set_rules('txPass', 'Password', 'trim|required|xss_clean');
		$this->form_validation->set_rules('txName', 'Fullname', 'trim|required|xss_clean');
		if($this->form_validation->run() == true){
			$id = (int)$this->input->post('txID');
			$email = $this->input->post('txEmail');
			$pass = $this->input->post('txPass');
			$hash = password_hash($pass, PASSWORD_DEFAULT);
			$array = ['password' => $hash];
			$this->m_db->_update('users',$id,$hash);
			$this->session->set_flashdata('msg', $this->fun->alert_box('success','Password telah berhasil di-ubah, silahkan login dengan password yang baru'));
			redirect('auth/login');
		} else {
			redirect();
		}
	}
	public function checkEmailExist(){
		$email = $this->input->post('email');
		$data = $this->m_db->get_count_where('users','email',$email);
	    if($data == 0){
	        $array = ['status' => '0'];
	    } else {
	        $array = ['status' => '1'];
	    }
	    echo json_encode($array);
	}
	
	public function checkKTPExist(){
		$ktp = $this->input->post('ktp');
		$data = $this->m_db->get_count_where('users','ktp',$ktp);
	    if($data == 0){
	        $array = ['status' => '0'];
	    } else {
	        $array = ['status' => '1'];
	    }
	    echo json_encode($array);
	}
	public function generateCode(){
		$set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code = substr(str_shuffle($set), 0, 12);
		return $code;
	}
	
	

}
