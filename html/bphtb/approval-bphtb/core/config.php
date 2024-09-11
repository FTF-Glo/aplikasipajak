<?php

date_default_timezone_set('Asia/Jakarta');

$config['session_expired_interval'] = 3600 * 2;
$config['table_config']             = 'central_app_config';
$config['config_key_key']           = 'CTR_AC_KEY';
$config['config_value_key']         = 'CTR_AC_VALUE';
$config['nama_pengesah_key']        = 'NAMA_PJB_PENGESAH';
$config['nip_pengesah_key']         = 'NIP_PJB_PENGESAH';
$config['table_user']               = 'central_user';
$config['user_id_col_name']         = 'CTR_U_ID';
$config['user_username_col_name']   = 'CTR_U_UID';
$config['user_password_col_name']   = 'CTR_U_PWD';

$config['auth_pages']               = array('home');
$config['auth_actions']             = array('approval');
$config['guest_pages']              = array('login');
$config['default_page']             = 'home';

$config['menus']                    = array(

    'Home'          => 'auth@/',
    'Keluar'        => 'auth@?action=logout'
);

$config['serial_length']            = 5;


class config
{
    public static function get($key)
    {
        global $config;

        return isset($config[$key]) ? $config[$key] : [];
    }
}
