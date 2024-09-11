<?php 
/*
    function excelModelRealisasi(displaypersen = 0) {
        var buku = $("#src-buku-realisasi").val();
        var tahun = $("#tahun-pajak-realisasi").val();
        var kecamatan = $("#kecamatan-realisasi").val();

        var namakec = $("#kecamatan-realisasi option:selected").text();
        // var e_periode = Number($("#periode2-realisasi").val());
        var eperiode = $("#periode1-realisasi").val();
        var eperiode2 = $("#periode2-realisasi").val();
        var sts = 1;
        var q = '<?= base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}") ?>';
        
        if (displaypersen == 1) {
            window.open("view/PBB/monitoring/svc-toexcel-realisasi-persen.php?q="+ q +"&buku=" + buku + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + eperiode + "&eperiode2=" + eperiode2 + "&target_ketetapan=semua");
            return;
        }

        window.open("view/PBB/monitoring/svc-toexcel-realisasi.php?q="+ q +"&buku=" + buku + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + eperiode + "&eperiode2=" + eperiode2 + "&target_ketetapan=semua");
    } */
class OPENBO{
    private $conn='';
    public function Connection(string $db_user =  NULL,string  $db_name =  NULL, string $db_pass = NULL, string $db_host = NULL){
        $this->conn	= new mysqli($db_host,$db_user,$db_pass,$db_name); 
        switch(TRUE){
            case $this->conn->connect_error:
                exit ('	<h1 align="center">	WEBSITE SEDANG DALAM PROSES PERBAIKAN (MAIN TENIS) ');
            break;
        };
    }
    public function getPokoktetap($kdkecam,$buku,$kecamatan,$tahun,$periode1,$periode2){
        $this->conn->query('SELECT OP_KECAMATAN AS KECAMATAN, OP_KECAMATAN_KODE AS KDKECAMATAN,');
    }    
}
?>