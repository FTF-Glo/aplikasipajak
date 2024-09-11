<?php
	function getFasum(){
		$FASUMe = [	'1' => 'Tanah + bangunan',
					'2' => 'kavling siap bangun',
					'6' => 'Tanah Pertanian',
					'7' => 'Tanah Peternakan / Perikanan',
					'3' => 'tanah kosong',
					'4' => 'fasum',
					'5' => 'NON AKTIF'];
		$arek	= NULL;
		$arek	.='<select class="form-control" name="fasume" id="fasume">
					<option value="">SEMUA</option>';

		foreach($FASUMe as $isine => $kunci){
			$arek	.='<option value="'.$isine.'">'.strtoupper($kunci).'</option>';
		}
		$arek	.='<select cla</select>';
		echo $arek;
	}
	$uid = $data->uid;
	// echo $uid;

	// get module
	$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
	$appConfig = $User->GetAppConfig($application);
	//prevent access to not accessible module
	if (!$bOK) {
		return false;
	}

	if (!isset($opt)) {
?>
<link href="view/PBB/pembatalan-sppt/monitoring.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<!--  -->

<script>
	var GW_DBHOST = '<?php echo $appConfig['GW_DBHOST']; ?>';
	var GW_DBPORT = '<?php echo isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306'; ?>';
	var GW_DBNAME = '<?php echo $appConfig['GW_DBNAME']; ?>';
	var GW_DBUSER = '<?php echo $appConfig['GW_DBUSER']; ?>';
	var GW_DBPWD = '<?php echo $appConfig['GW_DBPWD']; ?>';
	var TAHUN_TAGIHAN = '<?php echo $appConfig['tahun_tagihan']; ?>';
	var USER_LOGIN = '<?php echo $uname; ?>';

	$(document).ready(function() {
		$("input:submit, input:button").button();
		$("#closeCBox").click(function() {
			$("#cBox").css("display", "none");
		})
	})

	function onSubmit(sts) {
		var tempo1 	= $("#jatuh-tempo1-" + sts).val();
		var tempo2 	= $("#jatuh-tempo2-" + sts).val();
		var tahun 	= $("#tahun-pajak-" + sts).val();
		var nop1 	= $("#nop-" + sts + "-1").val();
		var nop2 	= $("#nop-" + sts + "-2").val();
		var nop3 	= $("#nop-" + sts + "-3").val();
		var nop4 	= $("#nop-" + sts + "-4").val();
		var nop5 	= $("#nop-" + sts + "-5").val();
		var nop6 	= $("#nop-" + sts + "-6").val();
		var nop7 	= $("#nop-" + sts + "-7").val();
		var nama 	= $("#wp-name-" + sts).val();
		nama = nama.replace(" ", "%20");
		var jmlBaris = $("#jml-baris").val();
		var kc = $("#kecamatan-" + sts).val();
		var kl = $("#kelurahan-" + sts).val();
		$("#monitoring-content-" + sts).html("loading ...");


		var svc = "";
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-search.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
			na: nama,
			t1: tempo1,
			t2: tempo2,
			th: tahun,
			n1: nop1,
			n2: nop2,
			n3: nop3,
			n4: nop4,
			n5: nop5,
			n6: nop6,
			n7: nop7,
			st: sts,
			kc: kc,
			kl: kl,
			GW_DBHOST: GW_DBHOST,
			GW_DBNAME: GW_DBNAME,
			GW_DBUSER: GW_DBUSER,
			GW_DBPWD: GW_DBPWD,
			GW_DBPORT: GW_DBPORT,
			TAHUN_TAGIHAN: TAHUN_TAGIHAN
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});

	}

	function toExcel(sts) {
		// alert(sts);
		var count = $(".count-data").html();
		// alert(count);
		if (isNaN(count)) {
			alert("Data Belum ditampilkan");
		} else {
			// if ()
			// alert(count);
			var nop1 = $("#nop-" + sts + "-1").val();
			var nop2 = $("#nop-" + sts + "-2").val();
			var nop3 = $("#nop-" + sts + "-3").val();
			var nop4 = $("#nop-" + sts + "-4").val();
			var nop5 = $("#nop-" + sts + "-5").val();
			var nop6 = $("#nop-" + sts + "-6").val();
			var nop7 = $("#nop-" + sts + "-7").val();
			var nmfileAll = '<?php echo date('yymdhmi'); ?>';
			var nmfile = nmfileAll + '-part-';
			// $("#monitoring-content-"+sts).html("loading ...");        
			var svc = "";

			// $.ajax({
			//                type: "POST",
			//                url: "./view/PBB/pembatalan-sppt/mod-pembatalan-sppt.php",
			//                data: "q=<?php //echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); 
												?>" + "&n=" + nop + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
			//                success: function (msg) {
			var msg = count;
			var per_file = 2000;
			var sumOfPage = Math.ceil(msg / per_file);
			var strOfLink = "";
			// strOfLink+="<a class='download-all'>Download All</a><br>";
			if (msg < per_file) {
				strOfLink += '<a target="_blank" href="view/PBB/pembatalan-sppt/svc-toexcel-riwayat.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&GW_DBHOST=' + GW_DBHOST + '&GW_DBNAME=' + GW_DBNAME + '&GW_DBUSER=' + GW_DBUSER + '&GW_DBPWD=' + GW_DBPWD + '&GW_DBPORT=' + GW_DBPORT + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
			} else {
				for (var page = 1; page <= sumOfPage; page++) {
					strOfLink += '<a target="_blank" class="download-all-' + sts + '" href="view/PBB/pembatalan-sppt/svc-toexcel-riwayat.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&GW_DBHOST=' + GW_DBHOST + '&GW_DBNAME=' + GW_DBNAME + '&GW_DBUSER=' + GW_DBUSER + '&GW_DBPWD=' + GW_DBPWD + '&GW_DBPORT=' + GW_DBPORT + '&p = ' + page + '">' + nmfile + page + '</a><br/>';
				}
			}
			// alert(strOfLink);
			$("#contentLink").html(strOfLink);
			$("#contentLink").append("<b class='close-link' style='position: absolute;right: 5px;top: 0px;'>X</b>");

			$("#contentLink").show();
			$("#cBox").css("display", "block");
			$("#loadlink1").hide();


		}

	}

	function onSearch(sts) {
		var nop1 = $("#nop-" + sts + "-1").val();
		var nop2 = $("#nop-" + sts + "-2").val();
		var nop3 = $("#nop-" + sts + "-3").val();
		var nop4 = $("#nop-" + sts + "-4").val();
		var nop5 = $("#nop-" + sts + "-5").val();
		var nop6 = $("#nop-" + sts + "-6").val();
		var nop7 = $("#nop-" + sts + "-7").val();
		var nama = $("#wp-name-" + sts).val();
		nama = nama.replace(" ", "%20");
		var jmlBaris = $("#jml-baris").val();
		$("#monitoring-content-" + sts).html("loading ...");

		var svc = "";
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-search.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
			na: nama,
			n1: nop1,
			n2: nop2,
			n3: nop3,
			n4: nop4,
			n5: nop5,
			n6: nop6,
			n7: nop7,
			st: sts,
			GW_DBHOST: GW_DBHOST,
			GW_DBNAME: GW_DBNAME,
			GW_DBUSER: GW_DBUSER,
			GW_DBPWD: GW_DBPWD,
			GW_DBPORT: GW_DBPORT,
			USER_LOGIN: USER_LOGIN
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});

	}

	function onSearchDataSPPTFinal(sts) {
		var nop1 = $("#nop-" + sts + "-1").val();
		var nop2 = $("#nop-" + sts + "-2").val();
		var nop3 = $("#nop-" + sts + "-3").val();
		var nop4 = $("#nop-" + sts + "-4").val();
		var nop5 = $("#nop-" + sts + "-5").val();
		var nop6 = $("#nop-" + sts + "-6").val();
		var nop7 = $("#nop-" + sts + "-7").val();
		$("#monitoring-content-" + sts).html("loading ...");

		var svc = "";
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-searchspptfinal.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'24','uid':'$uid'}"); ?>", {
			n1: nop1,
			n2: nop2,
			n3: nop3,
			n4: nop4,
			n5: nop5,
			n6: nop6,
			n7: nop7,
			st: sts,
			t: <?php echo $appConfig['tahun_tagihan'] ?>,
			GW_DBHOST: GW_DBHOST,
			GW_DBNAME: GW_DBNAME,
			GW_DBUSER: GW_DBUSER,
			GW_DBPWD: GW_DBPWD,
			GW_DBPORT: GW_DBPORT
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});
	}

	function onSearchPembatalanSPPT(sts) {
		var nop = $("#nop-" + sts).val();
		if(nop=='') return false;

		// $("#monitoring-content-"+sts).html("loading ...");
		$("#monitoring-content-" + sts).html("loading ...");

		var svc = "";
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-pembatalan-sppt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'25','uid':'$uid'}"); ?>", {
			n: nop,
			st: sts,
			t: <?=$appConfig['tahun_tagihan']?>,
			GW_DBHOST: GW_DBHOST,
			GW_DBNAME: GW_DBNAME,
			GW_DBUSER: GW_DBUSER,
			GW_DBPWD: GW_DBPWD,
			GW_DBPORT: GW_DBPORT
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});
	}


	function onSearchPembatalanSPPTMulti(sts) {
		// alert(sts);
		// var nop = $("#nop-" + sts).val();
		var tahun = $("#tahun-" + sts).val();
		var kec = $("#kecamatan-" + sts).val();
		var kel = $("#kelurahan-" + sts).val();
		var blok = $("#blok-" + sts).val();
		var urut = $("#urut-" + sts).val();
		if(tahun==''){
			alert('Pilih Tahun Pajak Dulu');
			return;
		}else if(kec==''){
			alert('Pilih Kecamatan Dulu');
			return;
		}else if(kel==''){
			alert('Pilih Desa/Kelurahan Dulu');
			return;
		}

		// alert
		// $("#monitoring-content-"+sts).html("loading ...");
		$("#monitoring-content-" + sts).html("<br>loading ...");
		var svc = "";
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-pembatalan-sppt-multi.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'25','uid':'$uid'}"); ?>", {
			tahun: tahun,
			st: sts,
			t: <?php echo $appConfig['tahun_tagihan'] ?>,
			kel:kel,
			blok:blok,
			urut:urut,
			GW_DBHOST: GW_DBHOST,
			GW_DBNAME: GW_DBNAME,
			GW_DBUSER: GW_DBUSER,
			GW_DBPWD: GW_DBPWD,
			GW_DBPORT: GW_DBPORT
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});
	}

	function onSearchRiwayatPembatalanSPPT(sts) {
		// alert(sts);
		$("#contentLink").hide();

		var nop = $("#nop-" + sts).val();
		var kec = $("#kecamatan-" + sts).val();
		var kel = $("#kelurahan-" + sts).val();
		var jns = $("#fasume").val();
		if(kec=='' && nop=='') return false;
		// $("#monitoring-content-"+sts).html("loading ...");
		$("#monitoring-content-" + sts).html("loading ...");

		var svc = "";
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-riwayat-pembatalan.php?q=<?=base64_encode("{'a':'$a', 'm':'$m', 's':'25','uid':'$uid'}")?>", {
			n: nop,
			kel: kel,
			kec: kec,
			jns: jns,
			st: sts,
			t: <?=$appConfig['tahun_tagihan']?>,
			GW_DBHOST: GW_DBHOST,
			GW_DBNAME: GW_DBNAME,
			GW_DBUSER: GW_DBUSER,
			GW_DBPWD: GW_DBPWD,
			GW_DBPORT: GW_DBPORT
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});
	}

	function onSearchRiwayatPenerbitan(sts) {
		// alert(sts);
		$("#contentLink").hide();

		var nop = $("#nop-" + sts).val();
		var kec = $("#kecamatan-" + sts).val();
		var kel = $("#kelurahan-" + sts).val();
		var jns = $("#fasume").val();
		if(kec=='' && nop=='') return false;
		// $("#monitoring-content-"+sts).html("loading ...");
		$("#monitoring-content-" + sts).html("loading ...");

		var svc = "";
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-riwayat-penerbitan.php?q=<?=base64_encode("{'a':'$a', 'm':'$m', 's':'25','uid':'$uid'}"); ?>", {
			n: nop,
			kel: kel,
			kec: kec,
			jns: jns,
			st: sts,
			t: <?=$appConfig['tahun_tagihan']?>,
			GW_DBHOST: GW_DBHOST,
			GW_DBNAME: GW_DBNAME,
			GW_DBUSER: GW_DBUSER,
			GW_DBPWD: GW_DBPWD,
			GW_DBPORT: GW_DBPORT
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});
	}

	function viewPembatalanPerKel() {
		var kec = $("#kecamatan-8").val();
		var nop = $("#kelurahan-8").val();
		var thn = $("#tahun-8").val();

		if (kec != '') {

			$("#monitoring-content-8").html("<img src=\"image/icon/loading-big.gif\">");

			var svc = "";
			$("#monitoring-content-8").load("view/PBB/pembatalan-sppt/svc-pembatalan-perkel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'25','uid':'$uid'}"); ?>", {
				n: nop,
				thn: thn,
				t: <?php echo $appConfig['tahun_tagihan'] ?>,
				GW_DBHOST: GW_DBHOST,
				GW_DBNAME: GW_DBNAME,
				GW_DBUSER: GW_DBUSER,
				GW_DBPWD: GW_DBPWD,
				GW_DBPORT: GW_DBPORT
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-8").html(msg + xhr.status + " " + xhr.statusText);
				}
			});
		} else {
			$("<div>Silahkan pilih kecamatan dan kelurahan</div>").dialog({
				modal: true,
				buttons: {
					OK: function() {
						$(this).dialog("close");
					}
				}
			});
		}
	}

	function confirmProsesPembatalan() {
		var kel = $("#kelurahan-8 option:selected").text();
		var lblKelurahan = '<?php echo $appConfig['LABEL_KELURAHAN'] ?>';

		$("<div>Anda yakin akan me-rollback Penetapan untuk " + lblKelurahan + " " + kel + "?</div>").dialog({
			modal: true,
			buttons: {
				Ya: function() {
					$(this).dialog("close");
					prosesPembatalan();
				},
				Tidak: function() {
					$(this).dialog("close");
				}
			}
		});
	}

	function prosesPembatalan() {
		var uid = '<?php echo $uid ?>';
		var a = '<?php echo $a ?>';
		var m = '<?php echo $m ?>';

		var kec = $("#kecamatan-8").val();
		var nop = $("#kelurahan-8").val();
		var thn = $("#tahun-8").val();

		$.ajax({
			type: "POST",
			url: "view/PBB/pembatalan-sppt/svc-proses-pembatalan-perkel.php",
			data: "uid=" + uid + "&nop=" + nop + "&tahun=" + thn + "&a=" + a + "&m=" + a,
			dataType: "json",
			success: function(data) {
				console.log(data.message)
				if (data.respon == true) {
					// alert('Pembatalan SPPT Sukses!');
					$("<div>Pembatalan SPPT Masal Sukses!</div>").dialog({
						modal: true,
						buttons: {
							OK: function() {
								$(this).dialog("close");
							}
						}
					});
				} else {
					// alert('Pembatalan SPPT Gagal!');
					$("<div>Pembatalan SPPT Masal Gagal!</div>").dialog({
						modal: true,
						buttons: {
							OK: function() {
								$(this).dialog("close");
							}
						}
					});
				}
				viewPembatalanPerKel();
			},
			error: function(data) {
				console.log(data)
			}
		});
	}

	function onSearchPenerbitanSPPT(sts) {
		var nop1 = $("#nop-" + sts + "-1").val();
		var nop2 = $("#nop-" + sts + "-2").val();
		var nop3 = $("#nop-" + sts + "-3").val();
		var nop4 = $("#nop-" + sts + "-4").val();
		var nop5 = $("#nop-" + sts + "-5").val();
		var nop6 = $("#nop-" + sts + "-6").val();
		var nop7 = $("#nop-" + sts + "-7").val();
		$("#monitoring-content-" + sts).html("loading ...");

		var svc = "";
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-penerbitan-sppt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'26','uid':'$uid'}"); ?>", {
			n1: nop1,
			n2: nop2,
			n3: nop3,
			n4: nop4,
			n5: nop5,
			n6: nop6,
			n7: nop7,
			st: sts,
			t: <?php echo $appConfig['tahun_tagihan'] ?>,
			GW_DBHOST: GW_DBHOST,
			GW_DBNAME: GW_DBNAME,
			GW_DBUSER: GW_DBUSER,
			GW_DBPWD: GW_DBPWD,
			GW_DBPORT: GW_DBPORT
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});
	}

	function updateCount() {
		var tahun = $("#tahun-pajak-" + 1).val();
		$("#ketAkm").html('<span style="font-size: 12px;">Loading...</span>');
		$.ajax({
			type: "POST",
			url: "./view/PBB/pembatalan-sppt/svc-count.php",
			data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT,
			success: function(msg) {
				var vcount = msg.split("/");
				$("#ketAkm").html('<span style="font-size: 13px">Tahun Berjalan (<b>' + vcount[0] + '</b>) + Tunggakan (<b>' + vcount[1] + '</b>) = Total Pembayaran (<b>' + vcount[2] + '</b>)</span>');
			}
		});
	}


	function setPage(pg, sts) {
		var nop1 = $("#nop-" + sts + "-1").val();
		var nop2 = $("#nop-" + sts + "-2").val();
		var nop3 = $("#nop-" + sts + "-3").val();
		var nop4 = $("#nop-" + sts + "-4").val();
		var nop5 = $("#nop-" + sts + "-5").val();
		var nop6 = $("#nop-" + sts + "-6").val();
		var nop7 = $("#nop-" + sts + "-7").val();
		//var status = $("#sel-status").val();
		var nama = $("#wp-name-" + sts).val();
		nama = nama.replace(" ", "%20");
		//var jmlBaris = $("#jml-baris").val();
		$("#monitoring-content-" + sts).html("loading ...");
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-search.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); //base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); 
																													?>&p=" + pg, {
			na: nama,
			n1: nop1,
			n2: nop2,
			n3: nop3,
			n4: nop4,
			n5: nop5,
			n6: nop6,
			n7: nop7,
			st: sts,
			GW_DBHOST: GW_DBHOST,
			GW_DBNAME: GW_DBNAME,
			GW_DBUSER: GW_DBUSER,
			GW_DBPWD: GW_DBPWD,
			GW_DBPORT: GW_DBPORT
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});
	}

	function setJatuhTempo() {
		var tglJatuhTempo = $("#jatuh-tempo").val();
		$("#monitoring-content-" + sts).html("loading ...");
		$("#monitoring-content-" + sts).load("view/PBB/pembatalan-sppt/svc-update-jatuh-tempo.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','GW_DBHOST':'" . $appConfig['GW_DBHOST'] . "','GW_DBNAME':'" . $appConfig['GW_DBNAME'] . "','GW_DBUSER':'" . $appConfig['GW_DBUSER'] . "','GW_DBPWD':'" . $appConfig['GW_DBPWD'] . "','GW_DBPORT':'" . $appConfig['GW_DBPORT'] . "'}"); ?>", {
			tglJatuhTempo: tglJatuhTempo
		}, function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
			}
		});

		$.ajax({
			type: "POST",
			url: "./view/PBB/pembatalan-sppt/svc-update-jatuh-tempo.php",
			data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','GW_DBHOST':'" . $appConfig['GW_DBHOST'] . "','GW_DBNAME':'" . $appConfig['GW_DBNAME'] . "','GW_DBUSER':'" . $appConfig['GW_DBUSER'] . "','GW_DBPWD':'" . $appConfig['GW_DBPWD'] . "','GW_DBPORT':'" . $appConfig['GW_DBPORT'] . "'}"); ?>",
			success: function(msg) {
				alert("Berhasil");
			}
		});

	}

	$(function() {
		$("#tabs").tabs();
		$("#jatuh-tempo").datepicker({
			dateFormat: "yy-mm-dd"
		});
	});

	function showKecamatanAll() {
		var request = $.ajax({
			url: "view/PBB/monitoring/svc-kecamatan.php",
			type: "POST",
			data: {
				id: "<?php echo $appConfig['KODE_KOTA'] ?>"
			},
			dataType: "json",
			success: function(data) {
				var c = data.msg.length;
				var options = '';
				options += '<option value="">Semua Kecamatan</option>';
				for (var i = 0; i < c; i++) {
					options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
				}
				$("select#kecamatan-8").html(options);
				$("select#kecamatan-9").html(options);
				$("select#kecamatan-10").html(options);
			}
		});
	}

	function showKelurahan(sts) {
		var kecamatan = $("select#kecamatan-"+ sts).val();
		if(kecamatan==''){
			$("select#kelurahan-"+ sts).html('<option value="">Semua Kelurahan/Desa</option>');
		}else{
			var request = $.ajax({
				url: "view/PBB/monitoring/svc-kecamatan.php",
				type: "POST",
				data: {
					id: kecamatan,
					kel: kecamatan
				},
				dataType: "json",
				success: function(data) {
					var c = data.msg.length;
					var options = '';
					//options += '';
					options += '<option value="">Semua Kelurahan/Desa</option>';
					for (var i = 0; i < c; i++) {
						options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
					}
					$("select#kelurahan-"+ sts).html(options);
				}
			});
		}
	}

	function showBulan(id) {
		var bulan = Array("Semua", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
		var options = '';
		for (var i = 0; i < 13; i++) {
			options += '<option value="' + i + '">' + bulan[i] + '</option>';
			$("select#" + id).html(options);
		}
	}

	$(document).ready(function() {
		showKecamatanAll();
		showBulan("bulan-1");
		showBulan("bulan-2");

		$('#tabs').tabs({
			select: function(event, ui) { // select event
				$(ui.tab); // the tab selected
				if (ui.index == 2) {
					//showModelE2();
				}
			}
		});

		$("select#kecamatan-8").change(function() {
			showKelurahan(8);
		})
		
		$("select#kecamatan-10").change(function() {
			showKelurahan(10);
		})
		$("select#kecamatan-9").change(function() {
			showKelurahan(9);
		})
	});
</script>
<style type="text/css">
	.multi-csv {
		display: none;

	}

	#contentLink {
		padding: 10px;
		width: 150px;
		min-height: 200px;
		overflow: auto;
		position: absolute;
		background-color: white;
		/* right: -20px; */
		margin-left: 200px;
		margin-top: -50px;
		border: 1px solid black;
		display: none;
	}
</style>

<div class="col-md-12">
	<div id="div-search">
		<div id="tabs">
			<ul>
				<li><a href="#tabs-6">Pembatalan SPPT Per NOP</a></li>
				<li><a href="#tabs-10">Pembatalan SPPT Masal</a></li>
				<li><a href="#tabs-7">Penerbitan SPPT Per NOP</a></li>
				<li><a href="#tabs-8">Rollback Penetapan</a></li>
				<li><a onclick="onSearchRiwayatPembatalanSPPT(9)" href="#tabs-9">Riwayat Pembatalan NOP </a></li>
				<li><a onclick="onSearchRiwayatPenerbitan(11)" href="#tabs-11">Riwayat Re-Aktif NOP</a></li>
			</ul>
			
			<div id="tabs-6">
				<fieldset>
					<form id="TheForm-2" method="post">
						<table border="0" cellspacing="0" cellpadding="2">
							<tr>
								<td width="73">NOP </td>
								<td width="3">:</td>
								<td width="60"><input type="text" id="nop-6" name="nop-6" style="width: 160px;margin-right:10px" maxlength="18" class="form-control"></td>
								<td width="180">
									<button type="button" name="button2" id="button2" value="Tampilkan" onClick="onSearchPembatalanSPPT(6)" class="btn btn-primary bg-maka">Tampilkan</button>
									<!--<input type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="toExcel(1)"/>-->

								</td>
							</tr>
						</table>
					</form>
				</fieldset>
				<div id="monitoring-content-6" class="monitoring-content"></div>
			</div>
			
			<div id="tabs-10">
				<fieldset>
					<label>
						<input type="radio" name="tipeFilter" id="multi" value="multi" checked> Filter Multiple NOP
					</label>
					<!--<label>
						<input type="radio" name="tipeFilter" id="csv" value="single"> Upload CSV
					</label>-->

					<table border="0">
						<tr>
							<td>Tahun Pajak</td>
							<td>
								<select name="tahun-10" id="tahun-10" class="form-control">
									<option value="">Semua Tahun</option>
									<?php for($t = $appConfig['tahun_tagihan']; $t > 1993; $t--): ?>
										<option value="<?= $t ?>" <?php if($appConfig['tahun_tagihan']==$t) echo 'selected'; ?>><?= $t ?></option>
									<?php endfor; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Kecamatan :</td>
							<td>
								<select name="kecamatan-10" id="kecamatan-10" class="form-control"></select>
							</td>
						</tr>
						<tr>
							<td>Desa/Kelurahan :</td>
							<td>
								<select id="kelurahan-10" class="form-control"></select>
							</td>
						</tr>
						<tr>
							<td>Blok :</td>
							<td>
								<input type="text" id="blok-10" style="width:50px;text-align:center" maxlength="3" class="form-control">
							</td>
						</tr>
						<tr>
							<td>No Urut :</td>
							<td>
								<div style="display:block">
									<textarea id="urut-10" class="form-control"></textarea>
									<span>Gunakan koma (,) untuk pemisah</span>
								</div>
							</td>
						</tr>
						<tr style="text-align:right">
							<td>&nbsp;</td>
							<td>
								<button class="btn btn-primary bg-maka" id="btn-src" type="button" onclick="onSearchPembatalanSPPTMulti(10)"> Cari </button>
							</td>
						</tr>
					
						
						<!--tr class="multi">
							<td style="vertical-align: top">Masukkan NOP</td>
							<td>
								<div style="display: block; margin-left: 1em">
									<textarea id="daftarNOP" class="form-control"></textarea>
									<span>Gunakan koma (,) untuk pemisah</span>
								</div>
							</td>
						</tr-->

						<!--tr class="multi-csv">
							<td colspan="2">
								<p>Ambil dari CSV</p>
								<div style="display: block">
									<form id="TheForm-upload-csv"  method="post" action="view/PBB/pembatalan-sppt/svc-get-csv-data.php" enctype="multipart/form-data">
										<input type="file" name="file" accept=".csv" required>
										<button class="btn btn-primary btn-orange" style="margin-top: 1em" type="submit">Cari</button>
									</form>
								</div>
							</td>
						</tr-->
					</table>
					<div id="monitoring-content-10" class="monitoring-content"></div>
				</fieldset>
			</div>
			<div id="tabs-9">
				<fieldset>
					<form id="TheForm-2" method="post">
						<table>
							<tr>
								<td>Kecamatan :</td>
								<td>
									<select name="kecamatan-9" id="kecamatan-9" class="form-control"></select>
								</td>
								<td style="padding-left:20px">Kelurahan/Desa :</td>
								<td>
									<select id="kelurahan-9" name="kelurahan-9" class="form-control"><option value="">Semua Kelurahan/Desa</option></select>
								</td>
								<td style="padding-left:20px">Jenis tanah :</td>
								<td>
									<?php getFasum(); ?>
								</td>
							</tr>
							<tr>
								
							</tr>
						</table>
						<table border="0" cellspacing="0" cellpadding="2" style="margin-top:20px">
							<!-- <tr>
								<td colspan="3">
									<label><input type="radio" name="filter-riwayat" id="filter-riwayat-multinop" value="multinop" checked> Multiple NOP</label>
									<label><input type="radio" name="filter-riwayat" id="filter-riwayat-csv" value="csv"> CSV</label>
								</td>
							</tr> -->
							<tr data-display="multinop">
								<td width="73">NOP </td>
								<td width="3">:</td>
								<td>
									<!--<input type="text" id="nop-9" name="nop-9" size="30" maxlength="18" class="form-control" style="width: 160px;">-->
									<textarea name="nop-9" id="nop-9" cols="100" rows="4" class="form-control"></textarea>
									<span>Gunakan koma (,) untuk pemisah</span>
								</td>
								<td width="180">
									<button type="button" name="button2" id="button2" value="Cari" onClick="onSearchRiwayatPembatalanSPPT(9)" class="btn btn-primary btn-block bg-maka" style="margin-left: 10px;">Cari</button>
								</td>
							</tr>
							<tr data-display="csv" style="display: none">
								<td width="73">CSV</td>
								<td width="3">:</td>
								<td colspan="2">
									<input type="file" name="csv-riwayat" id="csv-riwayat" style="display: inline" accept=".csv">
									<button type="button" id="form-riwayat-csv" style="display: inline">Kirim</button>
								</td>
							</tr>
							<tr>
								<td colspan="4">
									<button type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="toExcel(9)" class="btn btn-primary bg-maka">Export to xls</button>
									<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
									<div id="contentLink"></div>
								</td>
							</tr>
						</table>
					</form>
				</fieldset>
				<div id="monitoring-content-9" class="monitoring-content"></div>
			</div>
			<div id="tabs-7">
				<fieldset>
					<form id="TheForm-2" method="post">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="">NOP: </label><br />
									<div class="col-md-1" style="padding: 0">
										<input type="text" class="form-control text-center nop-inputss-1" style="padding: 6px;" name="nop-7-1" id="nop-7-1" placeholder="PR">
									</div>
									<div class="col-md-1" style="padding: 0">
										<input type="text" class="form-control text-center nop-inputss-2" style="padding: 6px;" name="nop-7-2" id="nop-7-2" placeholder="DTII">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control text-center nop-inputss-3" style="padding: 6px;" name="nop-7-3" id="nop-7-3" placeholder="KEC">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control text-center nop-inputss-4" style="padding: 6px;" name="nop-7-4" id="nop-7-4" placeholder="KEL">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control text-center nop-inputss-5" style="padding: 6px;" name="nop-7-5" id="nop-7-5" placeholder="BLOK">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control text-center nop-inputss-6" style="padding: 6px;" name="nop-7-6" id="nop-7-6" placeholder="NO.URUT">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control text-center nop-inputss-7" style="padding: 6px;" name="nop-7-7" id="nop-7-7" placeholder="KODE">
									</div>
									<!--<input type="text" id="nops" class="form-control">-->
								</div>
							</div>
							<div class="col-md-2" style="margin-top: 25px">
								<button type="button" name="button2" id="button2" value="Tampilkan" onClick="onSearchPenerbitanSPPT(7)" class="btn btn-primary btn-orange">Tampilkan</button>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
							</div>
						</div>
						<script>
							$(".nop-inputss-1").on("keyup", function() {
								var len = $(this).val().length;
								let nopLengkap = $(this).val();
								
								if(len > 2)  $(".nop-inputss-2").val(nopLengkap.substr(2, 2));
								if(len > 4)  $(".nop-inputss-3").val(nopLengkap.substr(4, 3));
								if(len > 7)  $(".nop-inputss-4").val(nopLengkap.substr(7, 3));
								if(len > 10)  $(".nop-inputss-5").val(nopLengkap.substr(10, 3));
								if(len > 13)  $(".nop-inputss-6").val(nopLengkap.substr(13, 4));
								if(len > 17)  $(".nop-inputss-7").val(nopLengkap.substr(17, 1));
								if(len > 2) $(this).val(nopLengkap.substr(0, 2));
								if (len == 2) {
									$(".nop-inputss-2").focus();
								}
							});

							$(".nop-inputss-2").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 2) {
									$(".nop-inputss-3").focus();
								}
							});

							$(".nop-inputss-3").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 3) {
									$(".nop-inputss-4").focus();
								}
							});

							$(".nop-inputss-4").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 3) {
									$(".nop-inputss-5").focus();
								}
							});

							$(".nop-inputss-5").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 3) {
									$(".nop-inputss-6").focus();
								}
							});

							$(".nop-inputss-6").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 4) {
									$(".nop-inputss-7").focus();
								}
							});

							$(".nop-inputss-7").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 1) {
									onSearchPenerbitanSPPT(7);
								}
							});
						</script>
						<!--<table border="0" cellspacing="0" cellpadding="2">
							<tr>
								<td width="73">NOP </td>
								<td width="3">:</td>
								<td width="60"><input type="text" id="nop-7" name="nop-7" size="30" maxlength="18" style="width: 160px;margin-right:10px" class="form-control"></td>
								<td width="180">
									<button type="button" name="button2" id="button2" value="Tampilkan" onClick="onSearchPenerbitanSPPT(7)" class="btn btn-primary btn-orange">Tampilkan</button>
									<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
								</td>
							</tr>
						</table>-->
					</form>
				</fieldset>
				<div id="monitoring-content-7" class="monitoring-content"></div>
			</div>
			<div id="tabs-8">
				<fieldset>
					<form id="TheForm-2" method="post">
						<table border="0" cellspacing="0" cellpadding="2">
							<tr>
								<td width="45">Tahun</td>
								<td width="3">:</td>
								<td width="75">
									<select name="tahun-8" id="tahun-8" class="form-control">
										<?php
										for ($t = $appConfig['tahun_tagihan']; $t > 1993; $t--) {
											echo "<option value=\"$t\">$t</option>";
										}
										?>
								</td>
								<td width="69">Kecamatan</td>
								<td width="3">:</td>
								<td width="138"><select name="kecamatan-8" id="kecamatan-8" class="form-control"></select></td>
								<td width="8">&nbsp;</td>
								<td width="69"><?php echo $appConfig['LABEL_KELURAHAN'] ?></td>
								<td width="3">:</td>
								<td width="130"><select id="kelurahan-8" class="form-control"></select></td>
								<td width="300">
									<button type="button" name="button2" id="button2" value="Tampilkan" onClick="viewPembatalanPerKel()" class="btn btn-primary btn-orange" style="margin-left:10px">Tampilkan</button>&nbsp;&nbsp;
									<button type="button" name="btnProsesPembatalan" id="btnProsesPembatalan" value="Proses Rollback Penetapan" onClick="confirmProsesPembatalan()" class="btn btn-primary btn-blue">Proses Rollback Penetapan</button>
									<span id="loadlink1" style="font-size: 10px; display: none;"><img src="image/icon/loading.gif"></span>
									<!-- <span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span> -->
								</td>
							</tr>
						</table>
					</form>
				</fieldset>
				<div id="monitoring-content-8" class="monitoring-content"></div>
			</div>
			<div id="tabs-11">
				<fieldset>
					<form id="TheForm-2" method="post">
						<table>
							<tr>
								<td>Kecamatan :</td>
								<td>
									<select name="kecamatan-9" id="kecamatan-9" class="form-control"></select>
								</td>
								<td style="padding-left:20px">Kelurahan/Desa :</td>
								<td>
									<select id="kelurahan-9" name="kelurahan-9" class="form-control"><option value="">Semua Kelurahan/Desa</option></select>
								</td>
								<td style="padding-left:20px">Jenis tanah :</td>
								<td>
									<?php getFasum(); ?>
								</td>
							</tr>
							<tr>
								
							</tr>
						</table>
						<table border="0" cellspacing="0" cellpadding="2" style="margin-top:20px">
							<tr data-display="multinop">
								<td width="73">NOP </td>
								<td width="3">:</td>
								<td>
									<textarea name="nop-11" id="nop-11" cols="100" rows="4" class="form-control"></textarea>
									<span>Gunakan koma (,) untuk pemisah</span>
								</td>
								<td width="180">
									<button type="button" name="button2" id="button2" value="Cari" onClick="onSearchRiwayatPenerbitan(11)" class="btn btn-primary btn-block bg-maka" style="margin-left:10px">Cari</button>
								</td>
							</tr>
							<tr data-display="csv" style="display: none">
								<td width="73">CSV</td>
								<td width="3">:</td>
								<td colspan="2">
									<input type="file" name="csv-riwayat-aktif" id="csv-riwayat-aktif" style="display: inline" accept=".csv">
									<button type="button" id="form-riwayat-csv" style="display: inline">Kirim</button>
								</td>
							</tr>
							<tr>
								<td colspan="4">
									<button type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="toExcel(9)" class="btn btn-primary bg-maka">Export to xls</button>
									<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
									<div id="contentLink"></div>
								</td>
							</tr>
						</table>
					</form>
				</fieldset>
				<div id="monitoring-content-11" class="monitoring-content"></div>
			</div>
		</div>
	</div>
</div>

<?php
}
?>

<script type="text/javascript">
	$(document).ready(function(e) {

		// $("form").submit(function(event){
		//      event.preventDefault();
		//     alert("Submitted");
		// 	// e.prevenDefault();
		// });
		$("#TheForm-upload-csv").submit(function(e) {
			e.preventDefault();
			$.ajax({
				type: "POST",
				url: "view/PBB/pembatalan-sppt/svc-get-csv-data.php",
				contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
				processData: false, // NEEDED, DON'T OMIT THIS
				data: new FormData(this),
				// data: "uid="+uid+"&nop="+nop+"&tahun="+thn+"&a="+a+"&m="+a,
				// dataType : "json",
				success: function(data) {
					// alert(data);
					$("#daftarNOP").val(data);
					onSearchPembatalanSPPTMulti(10);
					$("#daftarNOP").val("");

					// alert(JSON.stringify(data));
					// console.log(data.message)

				},
				error: function(data) {
					console.log(data)
				}
			});


			// alert("123");
			// TheForm-upload-csv
		});

		$(document).on("click", ".close-link", function() {
			$("#contentLink").hide();
		});
		$(document).on("click", "#multi", function() {
			// $(".multi").css("display", "inline");
			// $(".multi-csv").hide();
			// $("#monitoring-content-10").html("");

		});
		$(document).on("click", "#csv", function() {
			$(".multi").hide();
			$(".multi-csv").css("display", "inline");
			$("#monitoring-content-10").html("");

		});

		$(document).on("click", "#all-check-button", function() {
			// alert("123");
			$('.cek').each(function() {
				this.checked = $("#all-check-button").is(':checked');
			});
		});

		$("#btn-batalkan-masal").click(function(e) {
			var jml = $(".cek[checked=true]").length;
			// alert(jml);
			var co = confirm(" Yakin ?  ");
			if (!co) {
				return false;
			}
			var array_select = [];

			$('.cek').each(function(e) {
				if ($(this).is(':checked')) {
					array_select.push($(this).attr("data-nop"));
				}

			});

			if (array_select.length > 0) {
				$("#box1-multi").fadeIn();
				$("#box2-multi").fadeIn();
			} else {
				alert("Silahkan Pilih NOP Terlebih Dahulu");
			}
		});

	});
</script>