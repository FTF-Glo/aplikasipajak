<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container">
    <div class="content bg-light p-4">
        <h2>Formulir Pendaftaran Pajak Online</h2>
        

        <div class="mt-3">
        <form action="<?php echo base_url();?>auth/getRegister" method="post" accept-charset="utf-8" id="myFormRegister">
            <div class="row">
                <div class="col-md-6">
                    <div class="bg-infotext p-3">
                        <div><strong>Perhatian :</strong></div>
                        <ul>
                            <li>Data yang diisi harus sesuai dengan KTP</li>
                            <li>Alamat Email harus dapat digunakan atau masih aktif, untuk proses verifikasi</li>
                            <li>Pastikan No. Handphone anda aktif dan benar, karena akan dipergunakan untuk pengiriman notifikasi/pemberitahuan</li>
                        </ul>
                    </div>
                    <?php
                      if($this->session->flashdata('msg') != null){
                        echo $this->session->flashdata('msg');
                      }
                    ?>
                    <h4 class="mt-3">Login</h4>
                    <hr/>
            
                   
                    <div class="form-group px-3">
                        <label>E-mail</label>
                        <input type="email" class="form-control chkEmail"  name="txEmail" value="" required autocomplete="username">
                        <span class="msgEmail error"></span>
                        <script> 
                            $(".chkEmail").keyup(function(){
                              var email = $(".chkEmail").val();
                              $.ajax({
                                type: "POST",
                                data: {email:email},
                                url: "<?php echo base_url(); ?>auth/checkEmailExist",
                                success: function(msg){
                                  var arr = JSON.parse(msg);
                                  if(arr.status != 0){
                                    $(".msgEmail").html('Email sudah terdaftar, silahkan Login !!')
                                    $('.btn-register').prop('disabled', true);
                                  }else {
                                    $(".msgEmail").html("");
                                    $('.btn-register').prop('disabled', false);
                                  }					
                                }
                              });	
                            });
                        </script>
                    </div>
                    <div class="form-group px-3">
                        <label>Kata Sandi :</label>
                        <input type="password" class="form-control" name="txPass" id="txPass" required autocomplete="new-password">
                    </div>
                    <div class="form-group px-3">
                        <label>Ketik Ulang Kata Sandi :</label>
                        <input type="password" class="form-control" name="txRePass" id="txRePass" required autocomplete="new-password">
                    </div>
                </div>
                <div class="col-md-6">
                    <h4>Data Wajib Pajak</h4>
                    <hr/>
                    <div class="form-group px-3">
                        <label>Nama Wajib Pajak</label>
                        <input class="form-control" name="txName" id="txName" required>
                    </div>
                    <div class="row px-3">
                        <div class="col-12 form-group">
                            <label>Nomor Identitas / No.KTP</label>
                            <input type="number" class="form-control chkKTP" name="txKTP" id="txKTP" required>
                            <span class="msgKTP error"></span>
                            <script> 
                                $(".chkKTP").keyup(function(){
                                  var ktp = $(".chkKTP").val();
                                  $.ajax({
                                    type: "POST",
                                    data: {ktp:ktp},
                                    url: "<?php echo base_url(); ?>auth/checkKTPExist",
                                    success: function(msg){
                                      var arr = JSON.parse(msg);
                                      if(arr.status != 0){
                                        $(".msgKTP").html('Nomor KTP sudah terdaftar!')
                                        $('.btn-register').prop('disabled', true);
                                      }else {
                                        $(".msgKTP").html("");
                                        $('.btn-register').prop('disabled', false);
                                      }					
                                    }
                                  });	
                                });
                            </script>
                        </div>
                    </div>
                    <div class="row px-3">
                        <div class="col-6 form-group">
                            <label>Tempat Lahir</label>
                            <input class="form-control" id="txBirthPlace" name="txBirthPlace" required>
                        </div>
                        <div class="col-6 form-group">
                            <label>Tanggal Lahir</label>
                            <input type="date" class="form-control" name="txDOB" id="txDOB" placeholder="Tanggal Lahir" required>
                        </div>
                    </div>
                    <div class="form-group px-3">
                        <label>No Telepon</label>
                        <input type="number" class="form-control" name="txPhone" id="txPhone" required>
                    </div>
                    <div class="form-group px-3">
                        <label>Alamat Lengkap</label>
                        <textarea class="form-control" name="txAddress" id="txAddress" required></textarea>
                    </div>

                </div>
            </div><!--row-->
            <div class="mt-3 p-3">
                <button class="btn btn-primary btn-register btn-block">Daftarkan</button>
            </div>
          </div>
        </form>
    </div>
</div>

<script src="<?php echo base_url('ext/custom/jquery.validate.min.js');?>" ></script>
<script>

$("#myFormRegister ").validate({
  rules: {
    txPass: {
      required: true,
      minlength: 6
    },
    txRePass: {
      equalTo: "#txPass"
    },
    action: "required"
  },
    messages: {
      txPass: {
        required: "Silahkan masukkan password",
        minlength: "Password harus terdiri minimal 6 karakter"
      },
    
      txRePass: {
        equalTo : "Password harus sama",
        required: "Silahkan masukkan ulangi password"
      },
      action: "Silahkan masukkan password",
      
    }
});
$.validator.messages.required = "Harus diisi !";
</script>