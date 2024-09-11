<?php
    $fget = fopen("nourut.txt", "r");
    $tmp = stream_get_contents($fget);
    fclose($fget);

    $fset = fopen("nourut.txt", "w");
    fwrite($fset, ($tmp+1));
    fclose($fset);
?>