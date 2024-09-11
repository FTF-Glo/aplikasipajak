<?php

function c_nid()
{
  // 12 in length
  return sprintf('%d%d%d%d%d%d%d%d%d%d%d%d',
    mt_rand(0, 9), mt_rand(0, 9),
    mt_rand(0, 9), mt_rand(0, 9),
    mt_rand(0, 9), mt_rand(0, 9),
    mt_rand(0, 9), mt_rand(0, 9),
    mt_rand(0, 9), mt_rand(0, 9),
    mt_rand(0, 9), mt_rand(0, 9)
  );
} // end of c_nid

function c_stan()
{
  // 700000 - 799999
  return sprintf('7%d%d%d%d%d',
    mt_rand(0, 9), mt_rand(0, 9),
    mt_rand(0, 9), mt_rand(0, 9),
    mt_rand(0, 9)
  );
} // end of c_stan


?>
