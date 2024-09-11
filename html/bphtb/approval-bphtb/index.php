<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'core/config.php';
require_once 'core/constants.php';
require_once 'core/db.php';
require_once 'core/db_init.php';
require_once 'core/auth.php';
require_once 'core/modal.php';
require_once 'functions/helper.php';
require_once 'functions/flash.php';
require_once 'functions/menu.php';
require_once 'functions/pagination.php';

$isUserLoggedIn = $auth->isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <meta name="viewport" content="width=device-width" />
    <!-- <link rel="icon" href="path/to/fav.png"> -->
    <title><?= appName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/halfmoon@1.1.1/css/halfmoon-variables.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/vanilla-datatables@latest/dist/vanilla-dataTables.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?= base_url('assets/my.css') ?>">
</head>

<body class="with-custom-webkit-scrollbars with-custom-css-scrollbars">
    <div class="page-wrapper with-navbar">
        <div class="sticky-alerts"></div>
        <nav class="navbar px-10">
            <a href="<?= base_url(); ?>" class="navbar-brand">
                <?= appName(); ?>
            </a>
            <ul class="navbar-nav ml-auto">
                <?php menu(); ?>
            </ul>
        </nav>

        <div class="content-wrapper">
            <div class="content">
                <?php flash(); ?>
                <?php include_once 'functions/page.php'; ?>
            </div>
        </div>
        <!-- Content wrapper end -->
    </div>
    <!-- Page wrapper end -->
    <?= $modal->get() ?>
    <div class="modal modal-full ie-scroll-fix" id="detail-bphtb" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#" class="close" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
                <div class="container">
                    <h3>
                        Form Surat Setoran Pajak Daerah
                        Bea Perolehan Hak Atas Tanah dan Bangunan
                        (SSPD-BPHTB)
                    </h3>
                    <div class="row">
                        <div class="col-md-8 offset-md-2">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Halfmoon JS -->
    <script src="https://cdn.jsdelivr.net/npm/halfmoon@1.1.1/js/halfmoon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanilla-datatables@latest/dist/vanilla-dataTables.min.js" type="text/javascript"></script>
    <script>
        window.BASEURL = '<?= base_url() ?>';
    </script>
    <script src="<?= base_url('assets/my.js') ?>"></script>
</body>

</html>