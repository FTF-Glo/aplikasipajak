<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container">
    <div class="content bg-light p-4">
        <div class="p-4">
            <h3>Pajak <?php echo $title;?></h3>
            <div class="row">
                <?php
                    foreach($content as $row){
                        echo '
                                <div class="col-6 col-md-4 my-3">
                                  <div class="p-lg-5 border rounded-lg bg-red">
                                    <div>
                                        <a href="'.base_url('jenis/'.$page.'?page='.$row['link']).'"><img src="'.base_url('images/icon/'.$row['icon']).'"></a>
                                        </div>
                                    <div class="text-center p-2">
                                        <a href="'.base_url('jenis/'.$page.'?page='.$row['link']).'" class="a-white">'.$row['title'].'</a>
                                    </div>
                                  </div>
                                </div>
                               ';
                    }
                ?>
            </div>
            <a href="<?php echo base_url(); ?>" class="btn btn-secondary"><i class="icon-arrow-left mr-2"></i>Kembali</a>
        </div>
    </div>
</div>