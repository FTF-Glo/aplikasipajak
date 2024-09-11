<?php 
//global $juenis;
$jenis = [
    "4.1.01.09.01.01" => [
        "jenis" 				 => "Papan Reklame/Balcho/Bando",
        "harga_dasar_ukuran" 	 => 250000,
        "harga_dasar_ketinggian" => 150000,
    ],
    "4.1.01.09.01.02" => [
        "jenis" 				 => "Neon Box",
        "harga_dasar_ukuran" 	 => 300000,
        "harga_dasar_ketinggian" => 150000,
    ],
    "4.1.01.09.01.04" => [
        "jenis" 				 => "Videotron",
        "harga_dasar_ukuran" 	 => 2200000,
        "harga_dasar_ketinggian" => null,
    ],
	 "4.1.01.09.01.04" => [
        "jenis" 				 => "Wallpainting",
        "harga_dasar_ukuran" 	 => 75000,
        "harga_dasar_ketinggian" => null,
    ],
    "4.1.01.09.01.05" => [
        "jenis" 				 => "Spanduk Kain",
        "harga_dasar_ukuran" 	 => 30000,
        "harga_dasar_ketinggian" => null,
    ],
	"4.1.01.09.01.03" => [
        "jenis" 				 => "Banner",
        "harga_dasar_ukuran" 	 => 40000,
        "harga_dasar_ketinggian" => null,
    ],
	"4.1.01.09.02.01" => [
        "jenis" 				 => "Umbul-umbul",
        "harga_dasar_ukuran" 	 => 30000,
        "harga_dasar_ketinggian" => null,
    ],
    "4.1.01.09.01.06" => [
        "jenis" 				 => "Tenda",
        "harga_dasar_ukuran" 	 => 250000,
        "harga_dasar_ketinggian" => null,
    ],
    "4.1.01.09.02.01" => [
        "jenis" 				 => "Stiker",
        "harga_dasar_ukuran" 	 => 500,
        "harga_dasar_ketinggian" => null,
    ],
	"4.1.01.09.05.01" => [
        "jenis" 				 => "Reklame Udara",
        "harga_dasar_ukuran" 	 => 450000,
        "harga_dasar_ketinggian" => null,
    ],
	"4.1.01.09.06.01" => [
        "jenis" => "Reklame Apung",
        "harga_dasar_ukuran" => 300000,
        "harga_dasar_ketinggian" => null,
    ],
    "4.1.01.09.03.01" => [
        "jenis" => "Selebaran",
        "harga_dasar_ukuran" => 300,
        "harga_dasar_ketinggian" => null,
    ],
	
    "4.1.01.09.04.01" => [
        "jenis" => "Reklame Berjalan/Kendaraan",
        "harga_dasar_ukuran" => 300000,
        "harga_dasar_ketinggian" => null,
    ],
    
    "4.1.01.09.07.01" => [
        "jenis" => "Suara",
        "harga_dasar_ukuran" => 150000,
        "harga_dasar_ketinggian" => null,
    ],
    "4.1.01.09.08.01" => [
        "jenis" => "Film/Slide",
        "harga_dasar_ukuran" => 300000,
        "harga_dasar_ketinggian" => null,
    ],
    "4.1.01.09.09.01" => [
        "jenis" => "Peragaan",
        "harga_dasar_ukuran" => 300000,
        "harga_dasar_ketinggian" => null,
    ],
];
//pertarifan();
$nsl['a0'] = ["tarif_pajak" => "Jalan Utama/Jalan Nasional", "nilai_poin" => 0.25];
$nsl['a1'] = ["tarif_pajak" => "Jalan Provinsi", "nilai_poin" 			  => 0.20];
$nsl['a2'] = ["tarif_pajak" => "Jalan Kabupaten", "nilai_poin" 			  => 0.15];
$nsl['a3'] = ["tarif_pajak" => "Jalan Desa", "nilai_poin" 				  => 0.10];

function hitung_pajak($ukuran_reklame=array('PANJANGRE' => 0, 'LEBARRE'   => 0, 'UNIT' => 0), $ketinggian_reklame, $jenis_reklame){
    global $jenis; // Akses variabel global di dalam fungsi
	
    // Ambil nilai panjang dan lebar reklame dari array
    $panjang_reklame = $ukuran_reklame['PANJANGRE'];
    $lebar_reklame 	 = $ukuran_reklame['LEBARRE'];
    $unit_reklame 	 = $ukuran_reklame['UNIT'];

    // Hitung NJOPR
    $harga_dasar_ukuran = $jenis[$jenis_reklame]['harga_dasar_ukuran'];
    $harga_dasar_ketinggian = $jenis[$jenis_reklame]['harga_dasar_ketinggian'];
    $NJOPR = ($panjang_reklame * $lebar_reklame * $unit_reklame * $harga_dasar_ukuran) + ($ketinggian_reklame * $harga_dasar_ketinggian) * 4;

    // Hitung NSR
    $NSR = 0.25 * $NJOPR;

    // Hitung total pajak
    $total_pajak = 0.25 * $NSR;

    return $total_pajak;
}
// Contoh pemanggilan fungsi
$ukuran_reklame = array('PANJANGRE' => 1, 'LEBARRE' => 3, 'UNIT' => 3); // Misalnya, ukuran reklame dalam meter persegi dan jumlah unit
$ketinggian_reklame = 0; // Misalnya, ketinggian reklame dalam meter
$jenis_reklame = "4.1.01.09.01.06"; // Misalnya, jenis reklame dari array jenis

$total_pajak = hitung_pajak($ukuran_reklame, $ketinggian_reklame, $jenis_reklame);
echo "Total pajak: $total_pajak";

?>

