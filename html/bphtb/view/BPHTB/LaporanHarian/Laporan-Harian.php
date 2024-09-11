<?php
if (!isset($data)) {
    return;
}
?>
<link href="/inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jtable/themes/lightcolor/gray/jtable.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="/inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="/inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>
<script type="text/javascript" src="/inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jtable/jquery.jtable.js"></script>
<script type="text/javascript">
      
    $(document).ready(function() {            
        //$( "#tanggal1" ).datepicker();
        $( "#tanggal1" ).datepicker({dateFormat: "yy-mm-dd"});
        $( "#tanggal1" ).datepicker('setDate',new Date());
        $( "#tabs" ).tabs();
        $("input:submit, input:button").button(); 
        
    });   
</script>
<body>

        <div id="tabs">
                <ul>
                    <li><a href="#tabs-1">Laporan Harian</a></li>                   
                </ul>
              <div id="tabs-1">
                  <fieldset style="border:0">
                    <form id="TheForm-2" name="form1" method="post" action="#">
                            <table border="0" cellpadding="0" cellspacing="0" class='transparent'>
                                <tr>
                                    <td width="20" style="font-size:medium">Tanggal</td>
                                    <td width="9">&nbsp;:&nbsp;</td>
                                    <td><input id="tanggal1" name="tanggal1" type="text"  size="20" /></td>
                                    <td>&nbsp;</td>
                                    <td ><input type="submit" name="Show2" id="Show2" value="Tampilkan" onClick="onSubmit()" />
                                         <input type="button" name="Show3" id="Show3" value="download" />
                                    </td>
                                   <tr/>
                            </table>
                     </form>
                </fieldset>  
                <div id="TableConent1" style="width: 100%;" class="TableConent1"></div>
            </div>
       </div>

<script>

        function onSubmit() {
            // alert();
            var tgl = $("#tanggal1").val();
            $('#TableConent1').jtable({
                paging : true,
                pageSize : 20,
                actions: {
                    listAction: 'view/BPHTB/LaporanHarian/laporanList.php?action=list&hari='+tgl
                }, 
                fields: {
                    No :{
                        key : true,
                        list : true,
                        title :'No',
                        width :'2%'
                             
                    },
                    Nama :{
                        title :'Nama WAJIB PAJAK',
                        width :'15%'
                    },
                    Alamat  : {
                        title :'ALAMAT OBJECT PAJAK',
                        width : '20%'
                    },
                    Luas  : {
                        title : 'LUAS TANAH',
                        width : '7%'
                    },
                    Bangunan : {
                        title : 'LUAS BANGUNAN',
                        width : '9%'
                    },
                    Harga : {
                        title : 'HARGA TRANSAKSI',
                        width : '10%'
                    },
                    Bphtb : {
                        title : 'BPHTB DIBAYAR',
                        width : '10%'
                    },
                    
                    Jenis : {
                        title : 'HAK',
                        width : '7%'
                    }
                     
                }  
            });
            $('#TableConent1').jtable('load');

        }
		$(document).ready(function(){
           var tgl = $("#tanggal1").val();
            $('#TableConent1').jtable({
                paging : true,
                pageSize : 20,
                actions: {
                    listAction: 'view/BPHTB/LaporanHarian/laporanList.php?action=list&hari='+tgl
                }, 
                fields: {
                    No :{
                        key : true,
                        list : true,
                        title :'No',
                        width :'2%'
                             
                    },
                    Nama :{
                        title :'Nama WAJIB PAJAK',
                        width :'15%'
                    },
                    Alamat  : {
                        title :'ALAMAT OBJECT PAJAK',
                        width : '20%'
                    },
                    Luas  : {
                        title : 'LUAS TANAH',
                        width : '7%'
                    },
                    Bangunan : {
                        title : 'LUAS BANGUNAN',
                        width : '9%'
                    },
                    Harga : {
                        title : 'HARGA TRANSAKSI',
                        width : '10%'
                    },
                    Bphtb : {
                        title : 'BPHTB DIBAYAR',
                        width : '10%'
                    },
                    
                    Jenis : {
                        title : 'HAK',
                        width : '7%'
                    }
                     
                }  
            });
            $('#TableConent1').jtable('load');
            $("#Show3").click(function(){
                var tanggal = $("#tanggal1").val();
                 location.href="view/BPHTB/LaporanHarian/download.php?tgl="+tanggal; 
            });
        });   
                             
</script>                             
</body>