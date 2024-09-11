<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container">
    <div class="content bg-light p-4">

        <div class="mt-3">
            <div class="row">
                <div class="col-md-6">
                <h2>Login</h2>
                    <hr/>
                    <?php echo form_open('auth/check'); ?>
                    <?php
                      if($this->session->flashdata('msg') != null){
                        echo $this->session->flashdata('msg');
                      }
                    ?>
                    <div class="form-group px-3">
                        <label>E-mail</label>
                        <input class="form-control" name="txEmail">
                    </div>
                    <div class="form-group px-3">
                        <label>Password :</label>
                        <input type="password" class="form-control" name="txPass" id="txPass" autocomplete="new-password">
                    </div>
                    <button type="button" class="btn btn-none" data-toggle="modal" data-target="#exampleModalCenter">Lupa Password ?</button>
                    <div class="form-group px-3">
                        <button type="submit" class="btn mt-4 btn-primary btn-block">Masuk</button>
                    </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-infotext">
                        <h4>Informasi</h4>
                        <ul>
                            <li>Proses pembayaran pajak daerah lebih mudah dengan website ini</li>
                            <li>Anda dapat mendaftarkan usaha anda setelah melakukan proses registrasi untuk selanjutnya melakukan pembayaran pajak </li>
                            <li>Terdapat pencatatan histori tentang pembayaran pajak</li>
                            <li>Apabila anda belum pernah mendaftar diwebsite ini silahkan <a href="<?php echo base_url('auth/register');?>">Registrasi</a></li>
                        </ul>
                    </div>
                </div>
              
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
     <form action="<?php echo base_url();?>auth/getResetPassword" method="post" accept-charset="utf-8">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Reset Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>E-mail</label>
          <input name="txEmail" type="email" required placeholder="" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Kirim</button>
      </div>
      </form>
    </div>
  </div>
</div>