<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Apiauth
{
    public function validate_password($username, $password)
    {
		$CI =& get_instance();
        
        $petugas = $CI->db->where('username', $username)
            ->get('petugas')
            ->row();

        if($petugas && password_verify($password, $petugas->password)) {
            return true;
        }

        return false;
    }
}
