<?php

function check_bank($username){

        $bank = array(
                'default' => array(
                        'operator'=>'FTFUSER',
                        'payment_bank_code'=>'0' 
                ),
                'posuser' => array(
                        'operator'=>'Sistem Bank Lampung',
                        'payment_bank_code'=>'1' 
                ),
                'posuser' => array(
                        'operator'=>'POS',
                        'payment_bank_code'=>'2'
                )
        )

        $return = isset($bank[$username]) ? $bank[$username] : $bank['default'];

        return $return;
}