<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="container">
    <div class="content bg-light p-4">
        <h3><strong><?php echo $main['title'];?></strong></h3>
        <hr />
        <div class="my-4">
            <?php echo $main['content'];?>
        </div>
        <a href="<?php echo base_url('');?>" class="btn btn-secondary mt-3"><i class="icon-arrow-left mr-2"></i>Kembali</a>
    </div>
</div>