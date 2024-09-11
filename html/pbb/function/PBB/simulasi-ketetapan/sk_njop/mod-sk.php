<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'nop/sk_njop', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
$tab  = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 0;

function displayDaftarWP() {  // srch
    global $a, $m, $data;
    
	$notifid  = @isset($_REQUEST['notifid']) ? $_REQUEST['notifid'] : "";
	
    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/nop/svc-list-nop.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">Dalam Proses</a></li>\n";
    echo "\t\t<li><a href=\"view/PBB/nop/svc-list-nop.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'1', 'n':'2', 'u':'$data->uname'}") . "\">Susulan</a></li>\n";
    echo "\t\t<li><a href=\"view/PBB/nop/svc-list-nop.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'2', 'n':'3', 'u':'$data->uname'}") . "\">Masal</a></li>\n";
    echo "\t</ul>\n";
}

?>

<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript">
	var app = "<?php echo $a; ?>";
	var page = 1;
	
	function setTabs (tab) {
		page = 1;
        var srcAlamat = $("#srcAlamat-"+tab).val();
        var srcNama = $("#srcNama-"+tab).val();
        var srcNOP = $("#srcNOP-"+tab).val();
                
        $( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: {srcAlamat:srcAlamat, srcNama:srcNama, srcNOP:srcNOP} } );
		$( "#tabsContent" ).tabs( "option", "selected", tab );
		$( "#tabsContent" ).tabs('load', tab);
	}
	
	function setPage (tab,np) {
		if (np==1) page++;
		else page--;
                
		var srcAlamat = $("#srcAlamat-"+tab).val();
        var srcNama = $("#srcNama-"+tab).val();
		var srcNOP = $("#srcNOP-"+tab).val();
                
		$( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: { page:page,np:np,srcAlamat:srcAlamat, srcNama:srcNama,srcNOP:srcNOP} } );
		$( "#tabsContent" ).tabs( "option", "selected", tab );
		$( "#tabsContent" ).tabs('load', tab);
		
	}
	
    $(document).ready(function() {
        
        $("#all-check-button").click(function() {
            $('.check-all').each(function(){ 
                this.checked = $("#all-check-button").is(':checked'); 
            });
        });
	
        $("#tabsContent").tabs({
            load: function (e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },
            select: function (e, ui) {
                var $panel = $(ui.panel);
                
                if ($panel.is(":empty")) {
                    $panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
                }
            }
        });
		
		$("#closedcontent").click(function(){
			$("#content1").fadeOut(500);
			$("#content2").fadeOut(500);
		});
        
    });
	
function listTagihan(nop){

	$.ajax({
        type: "POST",
        url: "./function/PBB/nop/svc-list-tagihan.php",
        data: "app="+app+"&nop="+nop,
		success: function(data){
			console.log(data)
			$("#content1").fadeIn(500);
			$("#content2").fadeIn(500);
			$("#showTable").html(data);
			$("#nop").attr("value",nop);
        },
		error : function(data){
			$("#content1").html("Loading...");
			console.log(data)
		}
    });
}

function toExcel(){
	var nop = $("#nop").val();
    window.open("function/PBB/nop/svc-toexcel-list-tagihan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m','uid':'$uid'}"); ?>&nop="+nop+"&app="+app);
}

</script>

<div id="tabsContent">
	<?php 
            if(isset($tab)){
                echo "<script language='javascript'>setTabs(".$tab.")</script>";
            }        
            echo displayDaftarWP() 
        ?>
</div>

<div id="content2">
    <div align="center" id="detail" style="width: 90%; height: auto; margin: auto; margin-top: 50px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 100%; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto; vertical-align: middle; align:left">
        <div style="float: left; margin: 3px; padding: 3px;">
          <b>LIST TAGIHAN</b>
        </div>
        <div id="closedcontent" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div></div>
        <div id="showTable" style="margin: 10px;margin-left: 10px; overflow-y:scroll; height:400px;">
        </div>
		<div align="right" style="margin: 10px;margin-left: 10px;">
			<input type="hidden" name="nop" id="nop"/>
			<input type="button" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="toExcel()"/>
		</div>
    </div>
</div>
<div id="content1"></div>
<style type="text/css">
    .linkLihatDetail:hover{color: #ce7b00;}
    .linkLihatDetail{text-decoration: underline; cursor: pointer;}
    #content1, #content2{
        display:none;
        position:fixed;
        height:100%;
        width:100%;
        top:0;
        left:0;
    }
    #content1{
        background-color:#000000;
        filter:alpha(opacity=70);
        opacity:0.7;
        z-index:1;
    }
    #content2{
        z-index: 2;
    }
    #closedcontent{cursor: pointer;}
</style>
