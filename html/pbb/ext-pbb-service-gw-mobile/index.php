<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script src="js/jquery.js"></script>
</head>
<body>
<form action="index.php" method="post">
    <h3>Function : 
        <select name="type" onchange="submit()">
            <?php
            $pajak = array("getTagihanSPPT","getDaftarTagihanSPPT","getRealisasiSPPT");
            echo "<option value=''>Pilih</option>";
            foreach($pajak as $pjk){
                echo "<option value='{$pjk}' ".($pjk==$_POST['type']?"selected":"").">".$pjk."</option>";
            }
            ?>
        </select>
    </h3>
</form>
<?php
$type = isset($_POST['type'])?$_POST['type']:"";
if($type == "getTagihanSPPT"){
    echo "<input type=\"hidden\" name=\"fn\" id=\"fn\" value=\"getTagihanSPPT\">
    <table>
        <tr>
            <td align=\"left\">NOP</td>
            <td><input type=\"text\" name=\"nop\" id=\"nop\"></td>
        </tr>
        <tr>
            <td align=\"left\">Tahun Pajak</td>
            <td><input type=\"text\" name=\"thnpajak\" id=\"thnpajak\"></td>
        </tr>
        <tr>
            <td align=\"left\">User</td>
            <td><input type=\"text\" name=\"uid\" id=\"uid\"></td>
        </tr>
        <tr>
            <td align=\"left\">Password</td>
            <td><input type=\"text\" name=\"pass\" id=\"pass\"></td>
        </tr>
        <tr>
            <td align=\"right\" colspan=\"2\"><input type=\"button\" id=\"btnGetTagihanSPPT\" value=\"GET Json\"></td>
        </tr>
    </table>";	
} else if($type == "getDaftarTagihanSPPT"){
    echo "<input type=\"hidden\" name=\"fn\" id=\"fn\" value=\"getDaftarTagihanSPPT\">
    <table>
        <tr>
            <td align=\"left\">NOP</td>
            <td><input type=\"text\" name=\"nop\" id=\"nop\"></td>
        </tr>
        <tr>
            <td align=\"left\">User</td>
            <td><input type=\"text\" name=\"uid\" id=\"uid\"></td>
        </tr>
        <tr>
            <td align=\"left\">Password</td>
            <td><input type=\"text\" name=\"pass\" id=\"pass\"></td>
        </tr>
        <tr>
            <td align=\"right\" colspan=\"2\"><input type=\"button\" id=\"btnGetDaftarTagihanSPPT\" value=\"GET Json\"></td>
        </tr>
    </table>";	
} else if($type == "getRealisasiSPPT"){
    echo "<input type=\"hidden\" name=\"fn\" id=\"fn\" value=\"getRealisasiSPPT\">
    <table>
        <tr>
            <td align=\"left\">Tahun Pajak</td>
            <td>
            <select  name=\"tahunPajak\" id=\"tahunPajak\">
              <option value=''>Pilih</option>
              <option value=\"2017\">2017</option>
              <option value=\"2016\">2016</option>
              <option value=\"2015\">2015</option>
            </select>        
            </td>
        </tr>
        <tr>
            <td align=\"left\">Tahun Pembayaran</td>
            <td>
            <select  name=\"tahunBayar\" id=\"tahunBayar\">
              <option value=\"2017\">2017</option>
              <option value=\"2016\">2016</option>
              <option value=\"2015\">2015</option>
            </select>        
            </td>
        </tr>
        <tr>
            <td align=\"left\">Bulan Pembayaran</td>
            <td>
            <select  name=\"bulanBayar\" id=\"bulanBayar\">
              <option value=\"01\">Januari</option>
              <option value=\"02\">Februari</option>
              <option value=\"03\">Maret</option>
              <option value=\"04\">April</option>
              <option value=\"05\">Mei</option>
              <option value=\"06\">Juni</option>
              <option value=\"07\">Juli</option>
              <option value=\"08\">Agustus</option>
              <option value=\"09\">September</option>
              <option value=\"10\">Oktober</option>
              <option value=\"11\">November</option>
              <option value=\"12\">Desember</option>
            </select>        
            </td>
        </tr>
        <tr>
            <td align=\"left\">User</td>
            <td><input type=\"text\" name=\"uid\" id=\"uid\"></td>
        </tr>
        <tr>
            <td align=\"left\">Password</td>
            <td><input type=\"text\" name=\"pass\" id=\"pass\"></td>
        </tr>
        <tr>
            <td align=\"right\" colspan=\"2\"><input type=\"button\" id=\"btnGetRealisasiSPPT\" value=\"GET Json\"></td>
        </tr>
    </table>";	
}

?>
<div id="result"></div>
</body>
<script>
    $("#btnGetTagihanSPPT").click(function(){
        var tmpObj = new Object();
        tmpObj.fn = $('#fn').val();
        tmpObj.nop   = $('#nop').val();
        tmpObj.thnpajak   = $('#thnpajak').val();
        tmpObj.uid   = $('#uid').val();
        tmpObj.pass  = $('#pass').val();

        $.ajax({
            type:'post',
            url:'service.php',
            data: JSON.stringify(tmpObj),
            success:function(res){
                $("#result").html(res);
            }
        })
    })	
    $("#btnGetDaftarTagihanSPPT").click(function(){
        var tmpObj = new Object();
        tmpObj.fn = $('#fn').val();
        tmpObj.nop   = $('#nop').val();
        tmpObj.uid   = $('#uid').val();
        tmpObj.pass  = $('#pass').val();

        $.ajax({
            type:'post',
            url:'service.php',
            data: JSON.stringify(tmpObj),
            success:function(res){
                $("#result").html(res);
            }
        })
    })	    
    $("#btnGetRealisasiSPPT").click(function(){
        var tmpObj = new Object();
        tmpObj.fn = $('#fn').val();
        tmpObj.tahunPajak   = $('#tahunPajak').val();
        tmpObj.tahunBayar   = $('#tahunBayar').val();
        tmpObj.bulanBayar   = $('#bulanBayar').val();
        tmpObj.uid   = $('#uid').val();
        tmpObj.pass  = $('#pass').val();

        $.ajax({
            type:'post',
            url:'service.php',
            data: JSON.stringify(tmpObj),
            success:function(res){
                $("#result").html(res);
            }
        })
    })	        
</script>
</html>
