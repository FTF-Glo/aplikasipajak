<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>List SPPT Yang ditolak</h3>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
					<?php
			          if($this->session->flashdata('item') != null){
			            echo $this->session->flashdata('item');
			          }
			        ?>
                    <table id="myTable" class="table table-bordered table-striped table-sm table-hover table-select">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tanggal Buat</th>
                                <th>Masa</th>
                                <th>Usaha</th>
                                <th>Jenis Pajak</th>
                                <th>Total Pajak</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modAct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Verifikasi</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <?php echo form_open('sppt/verification_act'); ?>
	  <input id="txStatus" name="txStatus" hidden>
	  <input id="txID" name="txID" hidden>
      <div class="modal-body">
        <div class="form-group">
            <label>Nama Usaha</label>
            <div><strong class="oUsaha"></strong></div>
        </div>
        <div class="form-group">
            <label>Jenis Pajak</label>
            <div><strong class="oType"></strong></div>
        </div>
        <div class="form-group">
            <label>Masa</label>
            <div><strong class="oMasa"></strong></div>
        </div>
        <div class="form-group">
            <label>Total Pajak</label>
            <div><strong class="oTotal"></strong></div>
        </div>
        <div class="form-group">
            <label>Tanggal Lapor</label>
            <div><strong class="oDate"></strong></div>
        </div>
        Yakin ingin <span class="appr"></span> pelaporan pajak ini?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" >Ya</button>
      </div>
	  </form>
    </div>
  </div>
</div>
<script type="text/javascript">

var modAct = $('#modAct');

$('#myTable tbody').on('click','.btn-status', function(){
    var status = $(this).attr('data-status');
    var usaha = $(this).attr('data-usaha');
    var type = $(this).attr('data-type');
    var date = $(this).attr('data-date');
    var total = $(this).attr('data-total');
    var masa = $(this).attr('data-masa');
    var id = $(this).attr('data-id');
    if(status == '1'){
        modAct.find('.appr').html("<strong>mensetujui</strong>");
    }else{
        modAct.find('.appr').html("<strong>menolak</strong>");
    }
    modAct.find('#txID').val(id);
    modAct.find('#txStatus').val(status);
	modAct.find('.oUsaha').html(usaha);
	modAct.find('.oMasa').html(masa);
	modAct.find('.oType').html(type);
	modAct.find('.oDate').html(date);
	modAct.find('.oTotal').html(total);
	modAct.modal('show');
    return false;
});

    var table;
    $(document).ready(function() {
        table = $('#myTable').DataTable({ 
            "processing": true, 
            "serverSide": true, 
            "order": [], 
            "ajax": {
                "url": "<?php echo site_url('sppt/getSPPTListReject')?>",
                "type": "POST"
            },
            //"columnDefs": [{ 
               // "targets": [1], 
               // "orderable": false, 
            //}],
        });
    });

</script>
