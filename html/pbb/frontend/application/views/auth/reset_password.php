<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container">
    <div class="content bg-light p-4">

        <div class="mt-3">
            <div class="row">
                <div class="col-md-6">
                    <h2>Reset Kata Sandi</h2>
                    <hr/>
                    <div>
                           <?php echo form_open('auth/reset_confirm',['id' => 'myFormReset']); ?>
                            <?php
                              echo $this->session->flashdata('msg');
                            ?>
                            <div class="form-group">
                                <label for="txEmail">Email</label>
                                <input name="txID" value="<?php echo $id;?>" hidden>
                                <input name="txEmail" type="email" class="chkEmail form-control" value="<?php echo $email;?>" placeholder="" readonly>
                            </div>
                            <div class="form-group">
                                <label for="txPass">Password</label>
                                <input id="txPass" name="txPass" type="password" class="form-control" placeholder="kata sandi" required>
                            </div>
                            <div class="form-group">
                                <label for="txRePass">Ulangi Password</label>
                                <input id="txRePass" name="txRePass" type="password" class="form-control" placeholder="ketik kembali kata sandi" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn mt-4 btn-primary btn-block">Konfirmasi</button>
                            </div>

                          </form>
                      </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-infotext">
                        <h4>Informasi</h4>
                        <ul>

                        </ul>
                    </div>
                </div>
              
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url('ext/custom/jquery.validate.min.js');?>" ></script>
<script>
  $(function() {

$("#myFormReset").validate({
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
      required: "Silahkan masukkan password baru",
      minlength: "Password harus terdiri minimal 6 karakter"
    },
  txRePass: {
  equalTo : "Password harus sama",
  required: "Silahkan masukkan ulangi password"
  },
    action: "Silahkan masukkan password"
  }
});
});


</script>