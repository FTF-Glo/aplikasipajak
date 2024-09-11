
<footer class="bg-dark">
    <div class="container py-3">
        <div class="row">
            <div class="col-md-3 my-3">
                <h5>Tentang Kami</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo base_url('page/about');?>">Tentang Online Pajak</a></li>
                    <li><a href="<?php echo base_url('page/term');?>">Syarat & Ketentuan</a></li>
                </ul>
            </div>
            <!-- div class="col-md-3 my-3">
                <h5>Lokasi</h5>
              
            </div>
            <div class="col-md-3 text-center my-3">
                <h5>Ikuti Informasi</h5>
                <div class="icon-social">
                    <a href=""><i class="icon-facebook2"></i></a>
                    <a href=""><i class="icon-instagram"></i></a>
                    <a href=""><i class="icon-twitter"></i></a>
                </div>
            </div -->
            <div class="col-md-4 offset-md-5 my-3">
                <div class="bg-gray c-dark p-3">
                    <h6>Hubungi Kami</h6>
                    <ul class="list-unstyled">
                    <li><i class="icon-phone"></i> <?= getConfig('phone') ?></li>
                    <li><i class="icon-whatsapp"></i> <?= getConfig('contact_whatsapp') ?></li>
                    <li><i class="icon-mail2"></i> <?= getConfig('contact_email') ?></li>

                </ul>
                <div><strong>Alamat:</strong></div>
                <div>
                   <?= getConfig('wilayah_alamat') ?>
                </div>
                </div>
            </div>
        </div>
        <hr />
        <p>&copy; <?= date('Y', now()) ?> <?= getConfig('title') ?> </p>
    <div>
</footer>
