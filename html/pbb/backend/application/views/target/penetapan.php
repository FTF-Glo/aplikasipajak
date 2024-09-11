<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-9">
                    <h3>Penetapan Target Pajak <?php echo $year;?></h3>
                </div>
                <div class="col-md-3 menu-right pb-1">
                    <div class="row p-1">
                        <div class="col-8">
                            <input class="form-control" placeholder="tahun" id="txYear" value="<?php echo $year;?>">        
                        </div>
                        <div class="col-4">
                            <button class="btn btn-primary btn-block btn-year">Go</button>
                        </div>
                    </div>
                    
                </div>
            </div>
            
        </div>
        <div class="card">
            <div class="card-body">
                <?php
			          if($this->session->flashdata('item') != null){
			            echo $this->session->flashdata('item');
			          }
			    ?>
                <form action="<?php echo base_url();?>target/save" method="post" accept-charset="utf-8">
                <input name="txYear" value="<?php echo $year;?>" hidden>
                <div class="row">
                    <?php
                        if(count($target)!= 0){
                            foreach($type as $row){
                                foreach($target as $tg){
                                    if($tg['id_pajak_type'] == $row['id']){
                                        $value = $tg['value'];
                                    }
                                }
                                echo '
                                        <div class="col-md-4">
                                            <div class="form-group ">
                                                <label>'.$row['name'].'</label>
                                                <input class="form-control" name="txType'.$row['id'].'" value="'.$value.'" />
                                            </div>
                                        </div>
                                     ';
                            }
                        }else{
                            foreach($type as $row){
                                echo '
                                        <div class="col-md-4">
                                            <div class="form-group ">
                                                <label>'.$row['name'].'</label>
                                                <input class="form-control" name="txType'.$row['id'].'" />
                                            </div>
                                        </div>
                                     ';
                            }
                        }
                    ?>
                </div>
                <div>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
                </form>
            </div>
            
        </div>
        
    </div>
</div>
<script>
$(".btn-year").click(function(){
    var year = $("#txYear").val();
    var url = "<?php echo base_url('target/penetapan/');?>"+year;    
    $(location).attr('href',url);
});
</script>