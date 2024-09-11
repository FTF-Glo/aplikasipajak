<?php
//ini_set("display_errors",1); error_reporting(E_ALL);
if(!isset($data)){
	die();
}

//NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if(isset($arAreaConfig['terminalColumn'])){
	$terminalColumn = $arAreaConfig['terminalColumn'];
	$accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
	if(!$accessible){
		echo"Illegal access";
		return;
	}
}

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$User	 	= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$tahun		= $appConfig['tahun_tagihan'];
?>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<center>

<font size="4"><b>Klik Copy Data untuk melakukan copy master data</b></font> <br><br>
<table style="border: 1px solid;" width="200" height="50">
	<tr>
		<td align="center">
			<input type="button"  style="width: 170px; heigth: 35px;" name="copy_data" value="Copy Data" id="copy_data"/>
		</td>
	</tr>
</table>
</center>
<style type="text/css">
    #load-mask, #load-content{
        display:none;
        position:fixed;
        height:100%;
        width:100%;
        top:0;
        left:0;
    }
    #load-mask{
        background-color:#000000;
        filter:alpha(opacity=70);
        opacity:0.7;
        z-index:1;
    }
    #load-content{
        z-index: 2;
    }
    #loader {
        margin-right: auto;
        margin-left: auto; 
        background-color: #ffffff;
        width: 100px;
        height: 100px;
        margin-top: 200px;
    }
</style>
<div id="load-content">
    <div id="loader">
        <img src="image/icon/loading-big.gif"  style="margin-right: auto;margin-left: auto;"/>
    </div>
</div>
<div id="load-mask"></div>

<script type="text/javascript">
$("#copy_data").click(function(){

    $("#load-mask").css("display","block");
    $("#load-content").fadeIn();
        
    copyMaster('<?php echo $tahun?>');
});


function copyMasterSuccess(params){
        $("#load-content").css("display","none");
        $("#load-mask").css("display","none");
	
	if(params.responseText){
		var objResult=Ext.decode(params.responseText);
		// alert(objResult);
		if (objResult == "0000") {
			alert('Copy Master Data sukses.');
                        document.location.reload(true);
		} else {
			alert('Copy Master Data gagal. Terjadi kesalahan server');
		}
	} else {
		alert('Copy Master Data gagal. Terjadi kesalahan server');
	}
}

function copyMasterFailure(params){
	$("#load-content").css("display","none");
        $("#load-mask").css("display","none");
        alert('Gagal melakukan penilaian. Terjadi kesalahan server');
}

function copyMaster(tahun) {

        var params = "{\"TAHUN\":\""+tahun+"\"}";
        params = Base64.encode(params);
        Ext.Ajax.request({
                url : 'inc/PBB/svc-copy-master-data.php',
                success: copyMasterSuccess,
                failure: copyMasterFailure,			
                params :{req:params}
        });   

}
</script>