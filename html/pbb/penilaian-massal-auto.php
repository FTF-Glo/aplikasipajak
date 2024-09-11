<?php

require_once 'inc/PBB/SimpleDB.php';

$core = new SimpleDB();
$appConfig = $core->get('appConfig');
$tahun = $appConfig['tahun_tagihan'];

$kelurahan = $core->dbQuery('SELECT 
                                A.*,
                                IFNULL(B.CPM_KELURAHAN, 0) AS DONE
                            FROM 
                                cppmod_tax_kelurahan A 
                                LEFT JOIN cppmod_pbb_kalibrasi B ON A.CPC_TKL_ID = B.CPM_KELURAHAN AND B.CPM_TAHUN_PAJAK = "' . $tahun . '"
                            GROUP BY
                                A.CPC_TKL_ID')->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Massal Otomatis</title>
    <style>
        .berhasil {
            color: green
        }

        .gagal {
            color: red
        }

        .proses {
            color: orange
        }
    </style>
</head>

<body>
    <h1>Penilaian tahun <?= $tahun ?></h1>
    <button id="proses">Proses yang belum</button>
    <button id="proses_semua">Proses Semua</button>
	<button id="stop_next" onclick="stopNextRecursiveRequestMethod()" disabled>Berhentikan penilaian</button>
    <ul id="list-kelurahan">
        <?php foreach ($kelurahan as $row) {
            $id = $row['CPC_TKL_ID'];
            $kel = $row['CPC_TKL_KELURAHAN'];
            $sudah_pernah = !$row['DONE'] ? 0 : 1;
            $param = base64_encode('{"SVR_PRM":"eyJTZXJ2ZXJBZGRyZXNzIjoibG9jYWxob3N0IiwiU2VydmVyUG9ydCI6IjI3MDA4IiwiU2VydmVyVGltZU91dCI6IjEyMCJ9","KELURAHAN":"' . $id . '", "NOP":"", "TAHUN":"' . $tahun . '", "TIPE":"1", "SUSULAN":"0"}');
            $btn = "<button onclick='prosesIni(this)'>". ($sudah_pernah ? 'proses ulang' : 'proses ini aja') ."</button>";
            echo "<li>{$kel} status: <span data-proses-ini data-kelurahan='{$kel}' data-id='{$id}' data-param='{$param}' data-done='{$sudah_pernah}' class='" . ($sudah_pernah ? 'berhasil' : '...') . "'>" . ($sudah_pernah ? 'sudah pernah' : '...') . "</span> {$btn}</li>";
        } ?>
    </ul>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
		let stopNextRecursiveRequest = false;
		
		const stopNextRecursiveRequestMethod = () => {
			stopNextRecursiveRequest = true;
		};
	
        const prosesPenilaian = (id, param) => {			
            $.ajax({
                url: 'inc/PBB/svc-penilaian.php',
                type: 'POST',
                data: {
                    req: param
                },
                dataType: 'json',
                beforeSend: function() {
                    $(`[data-id=${id}]`).removeClass('gagal berhasil').addClass('proses').attr('data-done', 0).html('proses...');
                    $(`[data-id=${id}]`).next('button').hide();
                },
                success: function(data) {
                    if (typeof data.RC == 'undefined' || data.RC != "0000") {
                        $(`[data-id=${id}]`).removeClass('berhasil proses').addClass('gagal').attr('data-done', 0).html('gagal: ' + (typeof data.RC != 'undefined' ? data.RC : 'kosong'));
                        $(`[data-id=${id}]`).next('button').show().html('ulang');
                        console.error(`id: ${id}`, e, ee);
                        return;
                    }

                    $(`[data-id=${id}]`).removeClass('gagal proses').addClass('berhasil').attr('data-done', 1).html('berhasil');
                    $(`[data-id=${id}]`).next('button').remove();
					
                },
                error: function(e, ee) {
                    $(`[data-id=${id}]`).removeClass('berhasil proses').addClass('gagal').attr('data-done', 0).html('gagal');
                    $(`[data-id=${id}]`).next('button').show().html('ulang');
                    console.error(`id: ${id}`, e, ee);
                }
            })
        }
		
		const prosesPenilaianRecursive = (childNumber = 1) => {
			let liElement = $(`#list-kelurahan li:nth-child(${childNumber}) [data-proses-ini]`);
			if (!liElement.length) return;
			
			let id = liElement.attr('data-id');
			let param = liElement.attr('data-param');
			let kelName = liElement.attr('data-kelurahan');
			
            $.ajax({
                url: 'inc/PBB/svc-penilaian.php',
                type: 'POST',
                data: {
                    req: param
                },
                dataType: 'json',
                beforeSend: function() {
                    $(`[data-id=${id}]`).removeClass('gagal berhasil').addClass('proses').attr('data-done', 0).html('proses...');
                    $(`[data-id=${id}]`).next('button').hide();
                },
                success: function(data) {
                    if (typeof data.RC == 'undefined' || data.RC != "0000") {
                        $(`[data-id=${id}]`).removeClass('berhasil proses').addClass('gagal').attr('data-done', 0).html('gagal: ' + (typeof data.RC != 'undefined' ? data.RC : 'kosong'));
                        $(`[data-id=${id}]`).next('button').show().html('ulang');
                        console.error(`id: ${id}`, e, ee);
						
						alert(`Penialaian pada kelurahan ${kelName} gagal dan tidak dilanjut. Silakan cek console untuk detail.`);
                        return;
                    }

                    $(`[data-id=${id}]`).removeClass('gagal proses').addClass('berhasil').attr('data-done', 1).html('berhasil');
                    $(`[data-id=${id}]`).next('button').remove();
					
					
					if (!stopNextRecursiveRequest) prosesPenilaianRecursive(++childNumber);
					else alert(`Penialaian pada kelurahan ${kelName} selesai dan tidak dilanjut.`);
                },
                error: function(e, ee) {
                    $(`[data-id=${id}]`).removeClass('berhasil proses').addClass('gagal').attr('data-done', 0).html('gagal');
                    $(`[data-id=${id}]`).next('button').show().html('ulang');
                    console.error(`id: ${id}`, e, ee);
					
					alert(`Penialaian pada kelurahan ${kelName} gagal dan tidak dilanjut. Silakan cek console untuk detail.`);
                }
            })
        }

        let prosesIni = el => prosesPenilaian($(el).prev('span').attr('data-id'), $(el).prev('span').attr('data-param'));

        $(function() {

            $('#proses_semua').on('click', function() {
                if (!confirm('Yakin proses semua ?')) return;

                //$('[data-param][data-id]').each(function(i, el) {
                //    prosesPenilaian($(el).attr('data-id'), $(el).attr('data-param'));
                //})
				
				$('#stop_next').removeAttr('disabled');
				prosesPenilaianRecursive();
            })

            $('#proses').on('click', function() {
                if (!confirm('Yakin ?')) return;

                $('[data-param][data-id][data-done="0"]').each(function(i, el) {
                    prosesPenilaian($(el).attr('data-id'), $(el).attr('data-param'));
                })

            })
        })
    </script>
</body>

</html>