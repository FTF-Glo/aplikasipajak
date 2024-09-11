<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('getConfig')) {
    function getConfig($code)
    {
        $CI = &get_instance();
        
        $config_file = $CI->config->item($code);
        if($config_file !== null) {
            return $config_file;
        }
        
        $config = $CI->m_db->get_where_col('config', 'code', $code);
        if (empty($config)) {
            return '';
        }

        return $config[0]['value'];
    }
}
