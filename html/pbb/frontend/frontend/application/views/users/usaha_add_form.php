<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container">
    <div class="content bg-light p-4">
        <h3>Form Pendaftaran Usaha</h3>
        <hr />
        <?php echo form_open('users/usaha_save'); ?>
        <?php
          if($this->session->flashdata('msg') != null){
            echo $this->session->flashdata('msg');
          }
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="p-3">
                    <div class="form-group">
                        <label>Nama Usaha</label>
                        <input name="txName" placeholder="Nama sesuai dengan Surat Izin Usaha" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Bidang Usaha</label>
                        <select name="cbUsaha" class="form-control" required>
                            <option value="0">- Pilih Badan Usaha -</option>
                            <?php
                                foreach($usaha as $row){
                                    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Jenis Pajak</label>
                        <div class="border p-3 bg-white rounded">
                            <label>Silahkan pilih (bisa lebih dari satu)</label>
                            <?php
                                foreach($type as $key=>$row){
                                    echo '
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="txTaxType[]" value="'.$row['id'].'" id="defaultCheck'.$key.'">
                                                <label class="form-check-label" for="defaultCheck'.$key.'">
                                                  '.$row['name'].'
                                                </label>
                                            </div>
                                         ';
                                }
                            ?>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3">
                    <div class="form-group">
                        <label>No Telepon</label>
                        <input name="txPhone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat Usaha</label>
                        <textarea name="txAddress" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <label>RT</label>
                                <input name="txRT" class="form-control">
                            </div>
                            <div class="col">
                                <label>RW</label>
                                <input name="txRW" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provinsi</label>
                                <select id="cbProv" name="cbProv" class="form-control" required>
                                    <option value="0">- Pilih -</option>
                                    <?php
                                        foreach($province as $row){
                                            echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kabupaten</label>
                                <select id="cbKab" name="cbKab" class="form-control" required>
                                    <option value="0">- Pilih -</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kecamatan</label>
                                <select id="cbKec" name="cbKec" class="form-control" required>
                                    <option value="0">- Pilih -</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kelurahan</label>
                                <select id="cbKel" name="cbKel" class="form-control" required>
                                    <option value="0">- Pilih -</option>
                                </select>
                            </div>   
                        </div>
                    </div>                     
                </div>
            </div>
        </div>
        <div class="p-3">
            <button class="btn btn-primary btn-block">Simpan</button>
        </div>
        </form>
    </div>
</div>

<script>
$('#cbProv').change(function(){
    var idProv = this.value;
    if(idProv != 0){
        $.ajax({
            type: "POST",
            data: {idProv:idProv},
            url: "<?php echo base_url(); ?>api/getKab",
            success: function(msg){
              var arr = JSON.parse(msg);
              for(var i=0; i<arr.length; i++){
                $('#cbKab').append('<option value="'+arr[i].id+'">'+arr[i].name+'</option>')
              }
            }
        });
    }
});    
$('#cbKab').change(function(){
    var idKab = this.value;
    if(idKab != 0){
        $.ajax({
            type: "POST",
            data: {idKab:idKab},
            url: "<?php echo base_url(); ?>api/getKec",
            success: function(msg){
              var arr = JSON.parse(msg);
              
              for(var i=0; i<arr.length; i++){
                $('#cbKec').append('<option  value="'+arr[i].id+'">'+arr[i].name+'</option>')
              }
            }
        });
    }
});    
$('#cbKec').change(function(){
    var idKec = this.value;
    if(idKec != 0){
        $.ajax({
            type: "POST",
            data: {idKec:idKec},
            url: "<?php echo base_url(); ?>api/getKel",
            success: function(msg){
              var arr = JSON.parse(msg);
              for(var i=0; i<arr.length; i++){
                $('#cbKel').append('<option value="'+arr[i].id+'">'+arr[i].name+'</option>')
              }
            }
        });
    }
});    
</script>