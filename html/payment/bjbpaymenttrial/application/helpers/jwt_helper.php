<?php 

use \Firebase\JWT\JWT;


function configToken(){
    $cnf['exp'] = 3600; //milisecond
    $cnf['secretkey'] = '2212336221';
    return $cnf;        
}

function getJWT($otentikasiHeader){
    if (is_null($otentikasiHeader)) {
        throw new  Exception("Otentikasi Berier Token Gagal", 1);
        
    }
    return explode (" ", $otentikasiHeader)[1];

}

function validateJWT($encodedToken){

    $key =  configToken()['secretkey'];
    $decodedToken = JWT::decode($decodedToken,$key,['HS256']);
}