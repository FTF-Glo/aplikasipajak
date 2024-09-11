<!-- font awesome -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<?php if(count($dt_stts)>0){ 
?>
<?php
	$thn1	=isset($_POST["tahun-pajak-1"])?$_POST["tahun-pajak-1"]:"";
	$thn2	=isset($_POST["tahun-pajak-2"])?$_POST["tahun-pajak-2"]:"";
?>
<!-- <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"> 
<script src="bootstrap/js/jquery-3.1.1.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script> -->
<script>
// function viewtahun(){
// 		var nop= $("#nop").val();
// 		var idwp=$("#idwp").val();
// 		var thn1=$("#tahun-pajak-1").val();
// 		var thn2=$("#tahun-pajak-2").val();
//         $.ajax({
//             type: "POST",
//             url: "view_thn.php",
// 			data: {nop: nop, idwp: idwp, thn1:thn1, thn2:thn2}
//             }).done(function(data) {
//             $('#content').html(data);
//         });
//     }
</script>

<?php 
	// exit;
?>
<!-- <div class="form-inline pull-left">
	<div class="form-group">
		<select name="tahun-pajak-1" id="tahun-pajak-1" class="form-control">
          <option value="">Semua</option>
		  <?php
		  for ($t = $thn; $t > 1993; $t--) {
          ?> 
				<option value="<?php echo $t ?>"><?php echo $t ?></option>
          <?php    
               }
		  ?>
		  </select>
		  S/D
		  <select name="tahun-pajak-2" id="tahun-pajak-2" class="form-control">
          <option value="">Semua</option>
		  <?php
		  for ($t = $thn; $t > 1993; $t--) {
          ?> 
				<option value="<?php echo $t ?>"><?php echo $t ?></option>
          <?php    
               }
		  ?>
		  </select>
	  </div>
	  <div class="form-group">
		<button onclick="viewtahun()" class="col-sm-12 btn btn-success">Cari</button>
	  </div>
	</div> -->	

 	<div class="form-inline pull-right">
	  <div class="form-group">
		<!-- <button onclick="printToPDFSTTS('<?php echo $nop ?>','<?php echo $idwp ?>','cetak_ulang','budi','27-12-2017')" class="col-sm-12 btn btn-success"><img src="./image/printer.png"/> Cetak STTS</button> -->
	  </div>
	  <!-- <div class="form-group">
		<button onclick="printToExcel('<?php echo $nop?>','<?php echo $idwp?>')" class="col-sm-12 btn btn-success"><img src="./image/printer.png"/> Cetak Excel</button>
	  </div> -->
	</div>
	<br>
	<br>
	<div id="content"> <!-- start content -->

	<?php 
	
	// if ($_REQUEST[''])
	?>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th class="text-center">NO</th>
				<th class="text-center">JUMLAH NOP</th>
				<th class="text-center">TAHUN BAYAR KOLEKTIF </th>
				<th class="text-center">TOTAL PBB</th>
				<th class="text-center">TOTAL DENDA (*)</th>
				<th class="text-center">TOTAL KURANG BAYAR</th>
				<th class="text-center">STATUS BAYAR</th>
				<th class="text-center">STTS</th>
			</tr>
			</thead>
			<tbody>
			<?php 
				$i		= 1;
				$total  = 0;
				foreach($dt_stts as $dt_stts){
					if($nop==""){
						$dt_sttsNOP = substr($dt_stts['NOP'],0,2).'.'.substr($dt_stts['NOP'],2,2).'.'.substr($dt_stts['NOP'],4,3).'.'.substr($dt_stts['NOP'],7,3).'.'.substr($dt_stts['NOP'],10,3).'-'.substr($dt_stts['NOP'],13,4).'.'.substr($dt_stts['NOP'],17,1);
					} else {
						$dt_sttsNOP = $dt_stts['WP_NAMA'];
					}
					?>
					<tr>
						<td class="text-right"><?php echo $i; ?></td>
						<td class="text-center"><?php echo $dt_stts['CPM_CG_NOP_NUMBER']; ?></td>
						<td class="text-center"><?php echo date("Y"); ?></td>
						<td class="text-right">Rp <?php echo number_format($dt_stts['CPM_CG_ORIGINAL_AMOUNT']); ?></td>
						<td class="text-right">Rp <?php echo $dt_stts['CPM_CG_PENALTY_FEE']; ?></td>

						<td class="text-right">Rp 

						<?php 

						echo number_format($dt_stts['CPM_CG_ORIGINAL_AMOUNT']+$dt_stts['CPM_CG_PENALTY_FEE']); ?></td>
						<td><?php
						
						echo $dt_stts['STATUS_NAME'];
						// $date =  date("d-m-Y",strtotime($dt_stts['PAYMENT_PAID']));

						 ?></td>
						<td>
					<?PHP 

						?>
							<button data-group-id="<?php echo $dt_stts[CPM_CG_ID] ?>" id="btn-generate-report"  class="col-sm-12 btn btn-success">
							<i class="fa fa-download"></i>
							 </button>
						</td>
					</tr>
					<?php
					$total += ($dt_stts['CPM_CG_ORIGINAL_AMOUNT']+$dt_stts['CPM_CG_PENALTY_FEE']);
					$i++;
				}
			?>
			<tr>
				<th class="text-right" colspan="5">TOTAL</th>
				<th class="text-right">Rp <?php echo number_format($total); ?></th>
				<th class="text-center"></th>
				<th class="text-center"></th>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="8" id="link-download">
					
				</td>
			</tr>
		</tfoot>
	</table>

	<div id="stts-wrapper" >
	<!-- STTS -->
	<H2>Download STTS</H2>
	<hr>
	<table class="table table-bordered" cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">
		<thead>
			<tr>
				<td class=\"tdheader\" width=\"50px\">No</td>
				<td class=\"tdheader\" width=\"110px\">Jumlah NOP</td>
				<td class=\"tdheader\" width=\"100px\">Size</td>
				<td class=\"tdheader\" width=\"150px\">Tanggal</td>
				<td class=\"tdheader\" width=\"50px\">Status</td>
				<td class=\"tdheader\" width=\"200px\">Download</td>
				<td class=\"tdheader\" width=\"50px\">Hapus</td>
			</tr>
		</thead>
		<tbody id="table-pencetakan-stts" class="table-pencetakan-stts">
			
		</tbody>
	</table>
	<!-- END STTS -->
	</div>

	</div> <!-- end content -->
	<div class="alert alert-info">
	  *Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.
	</div>
<?php 
	} else {
		?>
		<div class="alert alert-danger">
			<strong>Perhatian!</strong> Data tidak ditemukan.
		</div>
		<?php
	}
?>
<script type="text/javascript">

$(document).ready(function(){

	$(document).on("click",".hapus-download",function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		var postData = {
			action:'hapusData',
			id:id
			// q:'<?php echo $_REQUEST['q']?>'
		};

		if(confirm('Apakah anda yakin untuk menghapus hasil download ini?') === false) return false;

		$.ajax({
			type: 'POST',
			url: 'cetak-masal/svc-pencetakan-stts.php',
			// url: 'view/PBB/pencetakan-dhkp-sppt/svc-pencetakan-stts.php',
			data: postData,
			dataType:'json',
			success : function(res){
				alert(res.msg);
				loadDataSTTS();
			}
		});
	});
	$('#btn-generate-report').click(function(){
		// alert("123");
			$("#stts-wrapper").show();
			var $btn = $(this);
			var group_id = $btn.attr("data-group-id");
			// alert(group_id);
			// var $blok = $('.tab3 #blok');
			// var $blok2 = $('.tab3 #blok2');
			// var $kel = $('.tab3 #kelurahanOP option:selected');
			// var $buku = $('.tab3 #buku option:selected');

			// var $btn = $(this);
			// var $blok = 123;
			// var $blok2 = 123;
			// var $kel = 123;
			// var $buku = 123;
			
					
			var postData = {
				kd_kel:000,
				blok:000,
				blok2:999,
				thn:2018,
				group_id:group_id,
				buku:213,
				q:'<?php echo $_REQUEST['q']?>'
			};
			
			if(postData.blok.length == 0 || postData.blok2.length == 0){ 
				alert("Isi Blok terlebuh dahulu!");
				$blok.focus();
				return false;
			}
			
			if(confirm('Apakah anda yakin untuk download STTS?') === false) return false;
			
			//post('view/PBB/pencetakan-dhkp-sppt/svc-topdf-sppt.php', postData);
			$btn.attr('disabled',true).val('Loading...');
			$.ajax({
				type: 'POST',
				url: 'cetak-masal/svc-topdf-stts.php',
				data: postData,
				synch:true,
				success:function(res){
					if($.trim(res).length > 0) alert(res);
				}
			 });
			alert('Data sedang diproses..');
			$btn.removeAttr('disabled').val('Download STTS');
			loadDataSTTS();
	});
});


var myVar = setInterval(function(){ startData() }, 5000);
function startData() {
	loadDataSTTS();
}

function stopData() {
	clearInterval(myVar);
}

function loadDataSTTS(){
	var postData = {
		action:'loadDataSTTS',
		group_id:$("#btn-generate-report").attr("data-group-id")
	}
	$.ajax({
		type: 'POST',
		//url: 'view/PBB/pencetakan-dhkp-sppt/svc-pencetakan-stts.php',
		url: 'cetak-masal/svc-pencetakan-stts.php',
		data: postData,
		dataType:'json',
		success : function(res){
			// alert(res.table);
			$('#table-pencetakan-stts').html(res.table);
			$('#table-pencetakan-stts-totalRows').html(res.totalRows);
		}
	});
}

function printToPDFSTTSCol(id,kode,jml) {
	msg = jml;
	var per_file = 100;
    var sumOfPage = Math.ceil(msg / per_file);
    var strOfLink = "";
	var nmfileAll = '<?php echo date('yymdhmi'); ?>';
    var nmfile = nmfileAll + '-part-';


    for (var page = 1; page <= sumOfPage; page++) {
		var params = {id:id,p:page,per_file:per_file};
		params = Base64.encode(Ext.encode(params));	
    	var link = "stts-pdf-collective.php?req="+params;
        strOfLink += '<a class="link-download-stts" href="'+link+'" target="_blank"  >'+ kode +" part-"+ page +' <i class="fa fa-download"></i> </a>'; 
    }
    $("#link-download").html(strOfLink);


    // var params = {id:id};
    // params = Base64.encode(Ext.encode(params));
    // window.open('stts-pdf-collective.php?req='+params, '_newtab');
}
function linkDownload(id){
	var params = {id:id};
	params = Base64.encode(Ext.encode(params));
	var klik = window.open('stts-pdf-collective.php?req='+params, '_newtab');
}
	// function printToPDF(nop,idwp){
	// 	var thn1=$("#tahun-pajak-1").val();
	// 	var thn2=$("#tahun-pajak-2").val();
	// 	console.log("print ...");
	// 	window.open("./print-pdf.php?nop="+nop+"&idwp="+idwp+"&thn1="+thn1+"&thn2="+thn2, "_newtab");
	// }
	// function printToExcel(nop,idwp){
	// 	var thn1=$("#tahun-pajak-1").val();
	// 	var thn2=$("#tahun-pajak-2").val();
	// 	console.log("print ...");
	// 	window.open("./print-excel.php?nop="+nop+"&idwp="+idwp+"&thn1="+thn1+"&thn2="+thn2, "_newtab");
	// }
</script>
<style type="text/css">
	.link-download-stts{
		float: left;
		margin: 15px;
		width: 80px;
	}
</style>
<!-- 
						//onclick="printToPDFSTTSCol('<?php echo $dt_stts['CPM_CG_ID'] ?>','<?php echo $dt_stts['CPM_CG_PAYMENT_CODE'] ?>','<?php echo $dt_stts['CPM_CG_NOP_NUMBER'] ?>')"

-->