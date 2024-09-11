<?php
require_once 'inc/PBB/HitungDendaMassal.php';

(new HitungDendaMassal())->executeAsync();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(array('status' => true, 'message' => 'proses sedang dilakukan'));
exit;