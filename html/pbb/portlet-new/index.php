<?php

require_once 'Portlet.php';

$idwp   = $portlet->getRequest('idwp');
$nop    = $portlet->getRequest('nop');
$tahun1 = $portlet->getRequest('tahun1', $portlet::MIN_TAHUN);
$tahun2 = $portlet->getRequest('tahun2', $portlet->appConfig['tahun_tagihan']);

$submit = $portlet->getRequest('submit');
$portlet->set('idwp', $idwp)
    ->set('nop', $nop)
    ->set('tahun1', $tahun1)
    ->set('tahun2', $tahun2);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> -->
    <title>Portlet</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/custom.css">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF" crossorigin="anonymous"></script>
    <script src="assets/js/custom.js"></script>
</head>

<body>
    <?php if (strpos($submit, 'export') !== false): include_once $submit; ?>
    <?php else: ?>
    <main role="main" class="container">
        <div class="mt-2 mb-4">
            <img src="../style/style-new/little_logo.png" alt="<?= $portlet::NAMA_BAPENDA ?>" class="img-fluid">
        </div>

        <ul class="nav nav-tabs" id="mainTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="sppt-tab" data-toggle="tab" href="#sppt" role="tab" aria-controls="sppt" aria-selected="true">Cetak Tagihan</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="sppt" role="tabpanel" aria-labelledby="sppt-tab">
                <?php include_once 'tabs/sppt.php'; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <span class="text-muted"><?= date('Y') ?>&copy; <?= $portlet::NAMA_BAPENDA ?></span>
        </div>
    </footer>

    <?php endif; ?>
</body>

</html>