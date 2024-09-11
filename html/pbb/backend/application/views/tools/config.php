<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-right col-md-6 col-12">
          <div class="media width-250 float-right">
            <media-left class="media-middle">
              <div id="sp-bar-total-sales"></div>
            </media-left>
            <div class="media-body media-right text-right">
            </div>
          </div>
        </div>
      </div>
	  <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header">
                    <h4>Konfigurasi</h4>
                </div>
                <div class="card-body">
                    <?php echo $this->session->flashdata('item'); ?>
                    <form action="<?php echo base_url();?>tools_config/save" method="post" accept-charset="utf-8">
                    <?php
                        foreach($main as $row){
                            if($row['satuan'] != ""){
                                echo $this->html->input_group_addon($row['name'],$row['code'],$row['satuan'],$row['value']);
                            } else {
                                echo $this->html->input_group($row['name'],$row['code'],$row['value']);
                            }
                        }
                    ?>
                    <div class="form-actions">
                      <button type="button" class="btn btn-warning mr-1" onclick="window.history.back();">
                        <i class="ft-x"></i> Batal
                      </button>
                      <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check-square-o"></i> Simpan
                      </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
	  </div>
	</div>
</div>
