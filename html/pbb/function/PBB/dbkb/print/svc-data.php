<?php
function qBengkelGudangPertanian(){    
    global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_TINGGI_KOLOM_MIN_DBKB_JPB8 as TMIN,
				CPM_TINGGI_KOLOM_MAX_DBKB_JPB8 as TMAX,
				CPM_LBR_BENT_MIN_DBKB_JPB8 as LMIN,
				CPM_LBR_BENT_MAX_DBKB_JPB8 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB8) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb8
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB8 = '{$tahun}'";
	 #echo $qry;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['TMIN']][$row['TMAX']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
        }
    }
    return $data;
}
function qStandard(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;
	$data = array();
	
    $qry = "SELECT
				CPM_KD_JPB as JPB,
				CPM_TIPE_BNG as TIPE_BNG,
				CPM_KD_BNG_LANTAI as LANTAI,
				ROUND(CPM_NILAI_DBKB_STANDARD) AS NILAI
			FROM
				cppmod_pbb_dbkb_standard
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_STANDARD = '{$tahun}'";
	//echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['JPB']][$row['TIPE_BNG']][$row['LANTAI']] = $row['NILAI'];
        }
    }
    return $data;
	
}
function qPabrik(){    
    global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_TINGGI_KOLOM_MIN_DBKB_JPB3 as TMIN,
				CPM_TINGGI_KOLOM_MAX_DBKB_JPB3 as TMAX,
				CPM_LBR_BENT_MIN_DBKB_JPB3 as LMIN,
				CPM_LBR_BENT_MAX_DBKB_JPB3 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB3) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb3
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB3 = '{$tahun}'";
	# echo $qry;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['TMIN']][$row['TMAX']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
        }
    }
    return $data;
}

function qKantor(){    
    global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_KLS_DBKB_JPB2 as KLS,
				CPM_LANTAI_MIN_JPB2 as LMIN,
				CPM_LANTAI_MAX_JPB2 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB2) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb2
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB2 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
        }
    }
    return $data;
}

function qPertokoan(){    
    global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_KLS_DBKB_JPB4 as KLS,
				CPM_LANTAI_MIN_DBKB_JPB4 as LMIN,
				CPM_LANTAI_MAX_DBKB_JPB4 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB4) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb4
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB4 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
        }
    }
    return $data;
}

function qRmhSakit(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_KLS_DBKB_JPB5 as KLS,
				CPM_LANTAI_MIN_JPB5 as LMIN,
				CPM_LANTAI_MAX_JPB5 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB5) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb5
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB5 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
        }
    }
    return $data;
	
}

function qOlahRaga(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_KLS_DBKB_JPB6 as KLS,
				ROUND(CPM_NILAI_DBKB_JPB6) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb6
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB6 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']] = $row['NILAI'];
        }
    }
    return $data;
}

function qHotelNonResort(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_JNS_DBKB_JPB7 AS JNS,
				CPM_LANTAI_MIN_JPB7 as LMIN,
				CPM_LANTAI_MAX_JPB7 as LMAX,
				CPM_BINTANG_DBKB_JPB7 as BINTANG,
				ROUND(CPM_NILAI_DBKB_JPB7) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb7
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB7 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['JNS']][$row['LMIN']][$row['LMAX']][$row['BINTANG']] = $row['NILAI'];
        }
    }
    return $data;    
}

function qHotelResort(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_JNS_DBKB_JPB7 AS JNS,
				CPM_LANTAI_MIN_JPB7 as LMIN,
				CPM_LANTAI_MAX_JPB7 as LMAX,
				CPM_BINTANG_DBKB_JPB7 as BINTANG,
				ROUND(CPM_NILAI_DBKB_JPB7) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb7
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB7 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['JNS']][$row['LMIN']][$row['LMAX']][$row['BINTANG']] = $row['NILAI'];
        }
    }
    return $data;
}

function qParkir(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_TYPE_DBKB_JPB12 AS TYPE,
				ROUND(CPM_NILAI_DBKB_JPB12) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb12
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB12 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['TYPE']] = $row['NILAI'];
        }
    }
    return $data;
}

function qApartemen(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_KLS_DBKB_JPB13 AS KLS,
			     CPM_LANTAI_MIN_JPB13 AS LMIN,
			     CPM_LANTAI_MAX_JPB13 AS LMAX,
				ROUND(CPM_NILAI_DBKB_JPB13) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb13
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB13 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
        }
    }
    return $data;
}

function qSekolah(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_KLS_DBKB_JPB16 AS KLS,
			     CPM_LANTAI_MIN_JPB16 AS LMIN,
			     CPM_LANTAI_MAX_JPB16 AS LMAX,
				ROUND(CPM_NILAI_DBKB_JPB16) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb16
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB16 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
        }
    }
    return $data;
}

function qMezanin(){    
    global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	
    $qry = "SELECT
				ROUND(CPM_NILAI_DBKB_MEZANIN) AS NILAI
			FROM
				cppmod_pbb_dbkb_mezanin
			WHERE
				CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_MEZANIN = '{$tahun}' ";
			
	// echo $qry;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$nilai = $row['NILAI'];
        }
		return isset($nilai)? $nilai : 0;
    }
}

function qKanopiBensin(){    
    global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	
    $qry = "SELECT
				ROUND(CPM_NILAI_DBKB_JPB14) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb14
			WHERE
				CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB14 = '{$tahun}' ";
			
	// echo $qry;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$nilai = $row['NILAI'];
        }
		return isset($nilai)? $nilai : 0;
    }
}

function qDayaDukung(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_TYPE_KONSTRUKSI AS TYPE,
				ROUND(CPM_NILAI_DBKB_DAYA_DUKUNG) AS NILAI
			FROM
				cppmod_pbb_dbkb_daya_dukung
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_DAYA_DUKUNG = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['TYPE']] = $row['NILAI'];
        }
    }
    return $data;
}

function qTangkiBawahTanah(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_JNS_TANGKI_DBKB_JPB15 AS JNS,
			     CPM_KAPASITAS_MIN_DBKB_JPB15 AS KMIN,
			     CPM_KAPASITAS_MAX_DBKB_JPB15 AS KMAX,
				ROUND(CPM_NILAI_DBKB_JPB15) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb15
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB15 = '{$tahun}'";
	#echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[(int) $row['JNS']][(int) $row['KMIN']][(int) $row['KMAX']] = $row['NILAI'];
        }
    }
    return $data;
}

function qTangkiAtasTanah(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_JNS_TANGKI_DBKB_JPB15 AS JNS,
			     CPM_KAPASITAS_MIN_DBKB_JPB15 AS KMIN,
			     CPM_KAPASITAS_MAX_DBKB_JPB15 AS KMAX,
				ROUND(CPM_NILAI_DBKB_JPB15) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb15
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB15 = '{$tahun}'";
	
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['JNS']][$row['KMIN']][$row['KMAX']] = $row['NILAI'];
        }
    }
    return $data;
}
function qFasNonDep(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				KD_FASILITAS AS KODE,
				ROUND(NILAI_NON_DEP) AS NILAI
			FROM
				cppmod_pbb_fas_non_dep
			WHERE
			KD_PROPINSI = '{$kdPropinsi}'
			AND KD_DATI2 = '{$kdDati2}'
			AND THN_NON_DEP = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KODE']] = $row['NILAI'];
        }
    }
    return $data;
}
function qNilaiFasDepKlsBintang(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				KD_FASILITAS AS KODE,
				KD_JPB AS JPB,
				KLS_BINTANG AS KLS,
				ROUND(NILAI_FASILITAS_KLS_BINTANG) as NILAI
			FROM
				cppmod_pbb_fas_dep_jpb_kls_bintang
			WHERE
			KD_PROPINSI = '{$kdPropinsi}'
			AND KD_DATI2 = '{$kdDati2}'
			AND THN_DEP_JPB_KLS_BINTANG = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KODE']][$row['JPB']][(int)$row['KLS']] = $row['NILAI'];
        }
    }
    return $data;
}
function qNilaiFasDepMinMax(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				KD_FASILITAS AS KODE,
				KLS_DEP_MIN AS KMIN,
                    KLS_DEP_MAX AS KMAX,
				ROUND(NILAI_DEP_MIN_MAX) AS NILAI
			FROM
				cppmod_pbb_fas_dep_min_max
			WHERE
			KD_PROPINSI = '{$kdPropinsi}'
			AND KD_DATI2 = '{$kdDati2}'
			AND THN_DEP_MIN_MAX = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KODE']][$row['KMIN']][$row['KMAX']] = $row['NILAI'];
        }
    }
    return $data;
}
function qNilaiMaterial(){
	global $DBLink,$tahun,$kdPropinsi,$kdDati2;	
	$data = array();
	
    $qry = "SELECT
				CPM_KD_PEKERJAAN AS PEKERJAAN,
				CPM_KD_KEGIATAN AS KEGIATAN,
				ROUND(CPM_NILAI_DBKB_MATERIAL) AS NILAI
			FROM
				cppmod_pbb_dbkb_material
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_MATERIAL = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['PEKERJAAN']][$row['KEGIATAN']] = $row['NILAI'];
        }
    }
    return $data;
}

function getValuesForPrint()
{
	global $tahun, $propinsi, $kota, $kanwil, $kpp, $kepala_nama, $kepala_nip;
	$arrValue = array();
	$arrValue['TAHUN'] = $tahun;
	$arrValue['PROPINSI'] = $propinsi;
	$arrValue['KOTA'] = $kota;
	$arrValue['KANWIL'] = $kanwil;
	$arrValue['KPP'] = $kpp;
	$arrValue['KEPALA'] = $kepala_nama;
	$arrValue['NIP'] = $kepala_nip;
	
	//Q.1. Komponen Utama
	$qStandard  = qStandard();
	//Q.1.1. Perumahan
	$arrValue['qStandardrmh1']  = @$qStandard['01']['045']['1_1_045'];
	$arrValue['qStandardrmh2']  = @$qStandard['01']['076']['1_1_076'];
    $arrValue['qStandardrmh3']  = @$qStandard['01']['145']['1_1_145'];
	$arrValue['qStandardrmh4']  = @$qStandard['01']['150']['1_1_150'];
	$arrValue['qStandardrmh5']  = @$qStandard['01']['295']['1_1_295'];
	$arrValue['qStandardrmh6']  = @$qStandard['01']['314']['1_1_314'];
	$arrValue['qStandardrmh7']  = @$qStandard['01']['500']['1_1_500'];
	$arrValue['qStandardrmh8']  = @$qStandard['01']['656']['1_1_656'];
	$arrValue['qStandardrmh9']  = @$qStandard['01']['045']['1_1_045'];
	$arrValue['qStandardrmh10'] = @$qStandard['01']['076']['1_2_076'];
	$arrValue['qStandardrmh11'] = @$qStandard['01']['134']['1_2_134'];
	$arrValue['qStandardrmh12'] = @$qStandard['01']['218']['1_2_218'];
	$arrValue['qStandardrmh13'] = @$qStandard['01']['257']['1_2_257'];
	$arrValue['qStandardrmh14'] = @$qStandard['01']['375']['1_2_375'];
	$arrValue['qStandardrmh15'] = @$qStandard['01']['474']['1_2_474'];
	$arrValue['qStandardrmh16'] = @$qStandard['01']['555']['1_2_555'];
	
	//Q.1.2. Kantor, Apotik, Toko, Pasar, Ruko, Restoran, Hotel, Wisma, Gedung Pemerintah
	$arrValue['qStandardkantor1'] =@$qStandard['02']['045']['2_1_045'];
	$arrValue['qStandardkantor2'] =@$qStandard['02']['076']['2_1_076'];
	$arrValue['qStandardkantor3'] =@$qStandard['02']['134']['2_1_134'];
	$arrValue['qStandardkantor4'] =@$qStandard['02']['216']['2_1_216'];
	$arrValue['qStandardkantor5'] =@$qStandard['02']['262']['2_1_262'];
	$arrValue['qStandardkantor6'] =@$qStandard['02']['432']['2_1_432'];
	$arrValue['qStandardkantor7'] =@$qStandard['02']['648']['2_1_648'];
	$arrValue['qStandardkantor8'] =@$qStandard['02']['864']['2_1_864'];
	$arrValue['qStandardkantor9'] =@$qStandard['02']['045']['2_2_045'];
	$arrValue['qStandardkantor10']=@$qStandard['02']['076']['2_2_076'];
	$arrValue['qStandardkantor11']=@$qStandard['02']['134']['2_2_134'];
	$arrValue['qStandardkantor12']=@$qStandard['02']['216']['2_2_216'];
	$arrValue['qStandardkantor13']=@$qStandard['02']['262']['2_2_262'];
	$arrValue['qStandardkantor14']=@$qStandard['02']['432']['2_2_432'];
	$arrValue['qStandardkantor15']=@$qStandard['02']['648']['2_2_648'];
	$arrValue['qStandardkantor16']=@$qStandard['02']['864']['2_2_864'];
	
	$arrValue['qStandardRmhSkt1'] =@$qStandard['05']['045']['5_1_045'];
	$arrValue['qStandardRmhSkt2'] =@$qStandard['05']['076']['5_1_076'];
	$arrValue['qStandardRmhSkt3'] =@$qStandard['05']['134']['5_1_134'];
	$arrValue['qStandardRmhSkt4'] =@$qStandard['05']['216']['5_1_216'];
	$arrValue['qStandardRmhSkt5'] =@$qStandard['05']['262']['5_1_262'];
	$arrValue['qStandardRmhSkt6'] =@$qStandard['05']['432']['5_1_432'];
	$arrValue['qStandardRmhSkt7'] =@$qStandard['05']['648']['5_1_648'];
	$arrValue['qStandardRmhSkt8'] =@$qStandard['05']['864']['5_1_864'];
	$arrValue['qStandardRmhSkt9'] =@$qStandard['05']['045']['5_2_045'];
	$arrValue['qStandardRmhSkt10']=@$qStandard['05']['076']['5_2_076'];
	$arrValue['qStandardRmhSkt11']=@$qStandard['05']['134']['5_2_134'];
	$arrValue['qStandardRmhSkt12']=@$qStandard['05']['216']['5_2_216'];
	$arrValue['qStandardRmhSkt13']=@$qStandard['05']['262']['5_2_262'];
	$arrValue['qStandardRmhSkt14']=@$qStandard['05']['432']['5_2_432'];
	$arrValue['qStandardRmhSkt15']=@$qStandard['05']['648']['5_2_648'];
	$arrValue['qStandardRmhSkt16']=@$qStandard['05']['864']['5_2_864'];
	
	//Q14 Bengkel/Gudang/Pertanian
	$qBengkelGudangPertanian = qBengkelGudangPertanian();
	$arrValue['qBGP01'] = @$qBengkelGudangPertanian['0']['4']['0']['9'];
	$arrValue['qBGP02'] = @$qBengkelGudangPertanian['0']['4']['10']['13'];
	$arrValue['qBGP03'] = @$qBengkelGudangPertanian['0']['4']['14']['17'];
	$arrValue['qBGP04'] = @$qBengkelGudangPertanian['0']['4']['18']['21'];
	$arrValue['qBGP05'] = @$qBengkelGudangPertanian['0']['4']['22']['25'];
	$arrValue['qBGP06'] = @$qBengkelGudangPertanian['0']['4']['26']['29'];
	$arrValue['qBGP07'] = @$qBengkelGudangPertanian['0']['4']['30']['33'];
	$arrValue['qBGP08'] = @$qBengkelGudangPertanian['0']['4']['34']['37'];
	$arrValue['qBGP09'] = @$qBengkelGudangPertanian['0']['4']['38']['99'];
	
	$arrValue['qBGP11'] = @$qBengkelGudangPertanian['5']['7']['0']['9'];
	$arrValue['qBGP12'] = @$qBengkelGudangPertanian['5']['7']['10']['13'];
	$arrValue['qBGP13'] = @$qBengkelGudangPertanian['5']['7']['14']['17'];
	$arrValue['qBGP14'] = @$qBengkelGudangPertanian['5']['7']['18']['21'];
	$arrValue['qBGP15'] = @$qBengkelGudangPertanian['5']['7']['22']['25'];
	$arrValue['qBGP16'] = @$qBengkelGudangPertanian['5']['7']['26']['29'];
	$arrValue['qBGP17'] = @$qBengkelGudangPertanian['5']['7']['30']['33'];
	$arrValue['qBGP18'] = @$qBengkelGudangPertanian['5']['7']['34']['37'];
	$arrValue['qBGP19'] = @$qBengkelGudangPertanian['5']['7']['38']['99'];
	
	$arrValue['qBGP21'] = @$qBengkelGudangPertanian['8']['10']['0']['9'];
	$arrValue['qBGP22'] = @$qBengkelGudangPertanian['8']['10']['10']['13'];
	$arrValue['qBGP23'] = @$qBengkelGudangPertanian['8']['10']['14']['17'];
	$arrValue['qBGP24'] = @$qBengkelGudangPertanian['8']['10']['18']['21'];
	$arrValue['qBGP25'] = @$qBengkelGudangPertanian['8']['10']['22']['25'];
	$arrValue['qBGP26'] = @$qBengkelGudangPertanian['8']['10']['26']['29'];
	$arrValue['qBGP27'] = @$qBengkelGudangPertanian['8']['10']['30']['33'];
	$arrValue['qBGP28'] = @$qBengkelGudangPertanian['8']['10']['34']['37'];
	$arrValue['qBGP29'] = @$qBengkelGudangPertanian['8']['10']['38']['99'];
	
	$arrValue['qBGP31'] = @$qBengkelGudangPertanian['11']['99']['0']['9'];
	$arrValue['qBGP32'] = @$qBengkelGudangPertanian['11']['99']['10']['13'];
	$arrValue['qBGP33'] = @$qBengkelGudangPertanian['11']['99']['14']['17'];
	$arrValue['qBGP34'] = @$qBengkelGudangPertanian['11']['99']['18']['21'];
	$arrValue['qBGP35'] = @$qBengkelGudangPertanian['11']['99']['22']['25'];
	$arrValue['qBGP36'] = @$qBengkelGudangPertanian['11']['99']['26']['29'];
	$arrValue['qBGP37'] = @$qBengkelGudangPertanian['11']['99']['30']['33'];
	$arrValue['qBGP38'] = @$qBengkelGudangPertanian['11']['99']['34']['37'];
	$arrValue['qBGP39'] = @$qBengkelGudangPertanian['11']['99']['38']['99'];
	
	//Q15 Pabrik
	$qPabrik = qPabrik();
	$arrValue['qPabrik01'] = @$qPabrik['0']['4']['0']['9'];
	$arrValue['qPabrik02'] = @$qPabrik['0']['4']['10']['13'];
	$arrValue['qPabrik03'] = @$qPabrik['0']['4']['14']['17'];
	$arrValue['qPabrik04'] = @$qPabrik['0']['4']['18']['21'];
	$arrValue['qPabrik05'] = @$qPabrik['0']['4']['22']['25'];
	$arrValue['qPabrik06'] = @$qPabrik['0']['4']['26']['29'];
	$arrValue['qPabrik07'] = @$qPabrik['0']['4']['30']['33'];
	$arrValue['qPabrik08'] = @$qPabrik['0']['4']['34']['37'];
	$arrValue['qPabrik09'] = @$qPabrik['0']['4']['38']['99'];
	
	$arrValue['qPabrik11'] = @$qPabrik['5']['7']['0']['9'];
	$arrValue['qPabrik12'] = @$qPabrik['5']['7']['10']['13'];
	$arrValue['qPabrik13'] = @$qPabrik['5']['7']['14']['17'];
	$arrValue['qPabrik14'] = @$qPabrik['5']['7']['18']['21'];
	$arrValue['qPabrik15'] = @$qPabrik['5']['7']['22']['25'];
	$arrValue['qPabrik16'] = @$qPabrik['5']['7']['26']['29'];
	$arrValue['qPabrik17'] = @$qPabrik['5']['7']['30']['33'];
	$arrValue['qPabrik18'] = @$qPabrik['5']['7']['34']['37'];
	$arrValue['qPabrik19'] = @$qPabrik['5']['7']['38']['99'];
	
	$arrValue['qPabrik21'] = @$qPabrik['8']['10']['0']['9'];
	$arrValue['qPabrik22'] = @$qPabrik['8']['10']['10']['13'];
	$arrValue['qPabrik23'] = @$qPabrik['8']['10']['14']['17'];
	$arrValue['qPabrik24'] = @$qPabrik['8']['10']['18']['21'];
	$arrValue['qPabrik25'] = @$qPabrik['8']['10']['22']['25'];
	$arrValue['qPabrik26'] = @$qPabrik['8']['10']['26']['29'];
	$arrValue['qPabrik27'] = @$qPabrik['8']['10']['30']['33'];
	$arrValue['qPabrik28'] = @$qPabrik['8']['10']['34']['37'];
	$arrValue['qPabrik29'] = @$qPabrik['8']['10']['38']['99'];
	
	$arrValue['qPabrik31'] = @$qPabrik['11']['99']['0']['9'];
	$arrValue['qPabrik32'] = @$qPabrik['11']['99']['10']['13'];
	$arrValue['qPabrik33'] = @$qPabrik['11']['99']['14']['17'];
	$arrValue['qPabrik34'] = @$qPabrik['11']['99']['18']['21'];
	$arrValue['qPabrik35'] = @$qPabrik['11']['99']['22']['25'];
	$arrValue['qPabrik36'] = @$qPabrik['11']['99']['26']['29'];
	$arrValue['qPabrik37'] = @$qPabrik['11']['99']['30']['33'];
	$arrValue['qPabrik38'] = @$qPabrik['11']['99']['34']['37'];
	$arrValue['qPabrik39'] = @$qPabrik['11']['99']['38']['99'];
	
	//Q16 KANTOR
	$qKantor = qKantor();
	$arrValue['qKantor01'] = @$qKantor['3']['1']['2'];
	$arrValue['qKantor02'] = @$qKantor['4']['1']['2'];
	$arrValue['qKantor03'] = @$qKantor['3']['3']['5'];
	$arrValue['qKantor04'] = @$qKantor['4']['3']['5'];
	$arrValue['qKantor05'] = @$qKantor['1']['6']['12'];
	$arrValue['qKantor06'] = @$qKantor['2']['6']['12'];
	$arrValue['qKantor07'] = @$qKantor['3']['6']['12'];
	$arrValue['qKantor08'] = @$qKantor['1']['13']['19'];
	$arrValue['qKantor09'] = @$qKantor['2']['13']['19'];
	$arrValue['qKantor10'] = @$qKantor['3']['13']['19'];
	$arrValue['qKantor11'] = @$qKantor['1']['20']['24'];
	$arrValue['qKantor12'] = @$qKantor['2']['20']['24'];
	$arrValue['qKantor13'] = @$qKantor['1']['25']['99'];
	$arrValue['qKantor14'] = @$qKantor['2']['25']['99'];
	
	//Q 1.7 PERTOKOAN
	$qPertokoan = qPertokoan();
	$arrValue['qPertokoan01'] = @$qPertokoan['2']['1']['2'];
	$arrValue['qPertokoan02'] = @$qPertokoan['3']['1']['2'];
	$arrValue['qPertokoan03'] = @$qPertokoan['1']['3']['4'];
	$arrValue['qPertokoan04'] = @$qPertokoan['2']['3']['4'];
	$arrValue['qPertokoan05'] = @$qPertokoan['1']['5']['99'];
	$arrValue['qPertokoan06'] = @$qPertokoan['2']['5']['99'];
	
	//Q 1.8 RUMAH SAKIT
	$qRmhSakit = qRmhSakit();
	$arrValue['qRmhSakit01'] = @$qRmhSakit['2']['1']['2'];
	$arrValue['qRmhSakit02'] = @$qRmhSakit['3']['1']['2'];
	$arrValue['qRmhSakit03'] = @$qRmhSakit['4']['1']['2'];
	$arrValue['qRmhSakit04'] = @$qRmhSakit['1']['3']['5'];
	$arrValue['qRmhSakit05'] = @$qRmhSakit['2']['3']['5'];
	$arrValue['qRmhSakit06'] = @$qRmhSakit['3']['3']['5'];
	$arrValue['qRmhSakit07'] = @$qRmhSakit['1']['6']['99'];
	$arrValue['qRmhSakit08'] = @$qRmhSakit['2']['6']['99'];
	
	//Q 1.9 OLAHRAGA
	$qOlahRaga = qOlahRaga();
	$arrValue['qOlahRaga01'] = @$qOlahRaga['1'];
	$arrValue['qOlahRaga02'] = @$qOlahRaga['2'];
	
	//Q 1.10 HOTEL NON RESORT
	$qHotelNonResort = qHotelNonResort();
	$arrValue['qHotelNonResort01'] = @$qHotelNonResort['1']['1']['2']['4'];
	$arrValue['qHotelNonResort02'] = @$qHotelNonResort['1']['1']['2']['4'];
	$arrValue['qHotelNonResort03'] = @$qHotelNonResort['1']['1']['2']['3'];
	$arrValue['qHotelNonResort04'] = @$qHotelNonResort['1']['1']['2']['5'];
	$arrValue['qHotelNonResort05'] = @$qHotelNonResort['1']['3']['5']['4'];
	$arrValue['qHotelNonResort06'] = @$qHotelNonResort['1']['3']['5']['4'];
	$arrValue['qHotelNonResort07'] = @$qHotelNonResort['1']['3']['5']['3'];
	$arrValue['qHotelNonResort08'] = @$qHotelNonResort['1']['3']['5']['2'];
	$arrValue['qHotelNonResort09'] = @$qHotelNonResort['1']['3']['5']['5'];
	$arrValue['qHotelNonResort10'] = @$qHotelNonResort['1']['6']['12']['4'];
	$arrValue['qHotelNonResort11'] = @$qHotelNonResort['1']['6']['12']['4'];
	$arrValue['qHotelNonResort12'] = @$qHotelNonResort['1']['6']['12']['3'];
	$arrValue['qHotelNonResort13'] = @$qHotelNonResort['1']['6']['12']['2'];
	$arrValue['qHotelNonResort14'] = @$qHotelNonResort['1']['6']['12']['1'];
	$arrValue['qHotelNonResort15'] = @$qHotelNonResort['1']['6']['12']['4'];
	$arrValue['qHotelNonResort16'] = @$qHotelNonResort['1']['6']['12']['5'];
	$arrValue['qHotelNonResort17'] = @$qHotelNonResort['1']['13']['20']['3'];
	$arrValue['qHotelNonResort18'] = @$qHotelNonResort['1']['13']['20']['2'];
	$arrValue['qHotelNonResort19'] = @$qHotelNonResort['1']['13']['20']['1'];
	$arrValue['qHotelNonResort20'] = @$qHotelNonResort['1']['21']['24']['2'];
	$arrValue['qHotelNonResort21'] = @$qHotelNonResort['1']['21']['24']['1'];
	$arrValue['qHotelNonResort22'] = @$qHotelNonResort['1']['25']['99']['2'];
	$arrValue['qHotelNonResort23'] = @$qHotelNonResort['1']['25']['99']['1'];
	
	
	//Q 1.11 HOTEL RESORT
	$qHotelResort = qHotelResort();
	$arrValue['qHotelResort01'] = @$qHotelResort['2']['1']['2']['4'];
	$arrValue['qHotelResort02'] = @$qHotelResort['2']['1']['2']['4'];
	$arrValue['qHotelResort03'] = @$qHotelResort['2']['1']['2']['3'];
	$arrValue['qHotelResort04'] = @$qHotelResort['2']['1']['2']['2'];
	$arrValue['qHotelResort05'] = @$qHotelResort['2']['1']['2']['1'];
	$arrValue['qHotelResort06'] = @$qHotelResort['2']['1']['2']['5'];
	
	$arrValue['qHotelResort07'] = @$qHotelResort['2']['3']['5']['4'];
	$arrValue['qHotelResort08'] = @$qHotelResort['2']['3']['5']['4'];
	$arrValue['qHotelResort09'] = @$qHotelResort['2']['3']['5']['3'];
	$arrValue['qHotelResort10'] = @$qHotelResort['2']['3']['5']['2'];
	$arrValue['qHotelResort11'] = @$qHotelResort['2']['3']['5']['1'];
	$arrValue['qHotelResort12'] = @$qHotelResort['2']['3']['5']['5'];
	
	$arrValue['qHotelResort13'] = @$qHotelResort['2']['6']['12']['4'];
	$arrValue['qHotelResort14'] = @$qHotelResort['2']['6']['12']['4'];
	$arrValue['qHotelResort15'] = @$qHotelResort['2']['6']['12']['3'];
	$arrValue['qHotelResort16'] = @$qHotelResort['2']['6']['12']['2'];
	$arrValue['qHotelResort17'] = @$qHotelResort['2']['6']['12']['1'];
	
	//Q 1.12 PARKIR
	$qParkir = qParkir();
	$arrValue['qParkir01'] = @$qParkir['1'];
	$arrValue['qParkir02'] = @$qParkir['2'];
	$arrValue['qParkir03'] = @$qParkir['3'];
	$arrValue['qParkir04'] = @$qParkir['4'];
	
	//Q 1.13 Apartemen
	$qApartemen = qApartemen();
	$arrValue['qApartemen01'] = @$qApartemen['3']['1']['2'];
	$arrValue['qApartemen02'] = @$qApartemen['4']['1']['2'];
	$arrValue['qApartemen03'] = @$qApartemen['2']['3']['5'];
	$arrValue['qApartemen04'] = @$qApartemen['3']['3']['5'];
	$arrValue['qApartemen05'] = @$qApartemen['4']['3']['5'];
	$arrValue['qApartemen06'] = @$qApartemen['1']['6']['12'];
	$arrValue['qApartemen07'] = @$qApartemen['2']['6']['12'];
	$arrValue['qApartemen08'] = @$qApartemen['3']['6']['12'];
	$arrValue['qApartemen09'] = @$qApartemen['4']['6']['12'];
	$arrValue['qApartemen10'] = @$qApartemen['1']['13']['20'];
	$arrValue['qApartemen11'] = @$qApartemen['2']['13']['20'];
	$arrValue['qApartemen12'] = @$qApartemen['3']['13']['20'];
	$arrValue['qApartemen13'] = @$qApartemen['1']['21']['24'];
	$arrValue['qApartemen14'] = @$qApartemen['2']['21']['24'];
	$arrValue['qApartemen15'] = @$qApartemen['1']['25']['99'];
	$arrValue['qApartemen16'] = @$qApartemen['2']['25']['99'];
	
	//Q 1.14
	$qSekolah = qSekolah();
	$arrValue['qSekolah01'] = @$qSekolah['1']['1']['2'];
	$arrValue['qSekolah02'] = @$qSekolah['2']['1']['2'];
	$arrValue['qSekolah03'] = @$qSekolah['1']['3']['5'];
	$arrValue['qSekolah04'] = @$qSekolah['2']['3']['5'];
	$arrValue['qSekolah05'] = @$qSekolah['1']['6']['99'];
	
	//Q 1.15
	$arrValue['qMezanin'] = qMezanin();
	
	//Q 1.16
	$arrValue['qKanopiBensin'] = qKanopiBensin();
	
	//Q 1.17
	$qDayaDukung = qDayaDukung();
	$arrValue['qDayaDukung01'] = @$qDayaDukung['1'];
	$arrValue['qDayaDukung02'] = @$qDayaDukung['2'];
	$arrValue['qDayaDukung03'] = @$qDayaDukung['3'];
	$arrValue['qDayaDukung04'] = @$qDayaDukung['4'];
	$arrValue['qDayaDukung05'] = @$qDayaDukung['5'];
	
	//Q 1.18
	$qTangkiAtasTanah = $qTangkiBawahTanah = qTangkiBawahTanah();
	$arrValue['qTangkiBawahTanah01'] = @$qTangkiBawahTanah['2']['0']['1'];
	$arrValue['qTangkiBawahTanah02'] = @$qTangkiBawahTanah['2']['2']['3'];
	$arrValue['qTangkiBawahTanah03'] = @$qTangkiBawahTanah['2']['4']['5'];
	$arrValue['qTangkiBawahTanah04'] = @$qTangkiBawahTanah['2']['6']['7'];
	$arrValue['qTangkiBawahTanah05'] = @$qTangkiBawahTanah['2']['8']['10'];
	$arrValue['qTangkiBawahTanah06'] = @$qTangkiBawahTanah['2']['11']['13'];
	$arrValue['qTangkiBawahTanah07'] = @$qTangkiBawahTanah['2']['14']['16'];
	$arrValue['qTangkiBawahTanah08'] = @$qTangkiBawahTanah['2']['17']['20'];
	$arrValue['qTangkiBawahTanah09'] = @$qTangkiBawahTanah['2']['21']['25'];
	$arrValue['qTangkiBawahTanah10'] = @$qTangkiBawahTanah['2']['26']['30'];
	$arrValue['qTangkiBawahTanah11'] = @$qTangkiBawahTanah['2']['31']['40'];
	$arrValue['qTangkiBawahTanah12'] = @$qTangkiBawahTanah['2']['41']['50'];
	$arrValue['qTangkiBawahTanah13'] = @$qTangkiBawahTanah['2']['51']['60'];
	$arrValue['qTangkiBawahTanah14'] = @$qTangkiBawahTanah['2']['61']['80'];
	$arrValue['qTangkiBawahTanah15'] = @$qTangkiBawahTanah['2']['81']['99999'];
	
	//Q 1.19
	$arrValue['qTangkiAtasTanah01'] = @$qTangkiAtasTanah['1']['0']['50'];
	$arrValue['qTangkiAtasTanah02'] = @$qTangkiAtasTanah['1']['51']['75'];
	$arrValue['qTangkiAtasTanah03'] = @$qTangkiAtasTanah['1']['76']['100'];
	$arrValue['qTangkiAtasTanah04'] = @$qTangkiAtasTanah['1']['101']['150'];
	$arrValue['qTangkiAtasTanah05'] = @$qTangkiAtasTanah['1']['151']['200'];
	$arrValue['qTangkiAtasTanah06'] = @$qTangkiAtasTanah['1']['201']['250'];
	$arrValue['qTangkiAtasTanah07'] = @$qTangkiAtasTanah['1']['251']['500'];
	$arrValue['qTangkiAtasTanah08'] = @$qTangkiAtasTanah['1']['501']['750'];
	$arrValue['qTangkiAtasTanah09'] = @$qTangkiAtasTanah['1']['751']['1250'];
	$arrValue['qTangkiAtasTanah10'] = @$qTangkiAtasTanah['1']['1251']['1500'];
	$arrValue['qTangkiAtasTanah11'] = @$qTangkiAtasTanah['1']['1501']['1750'];
	$arrValue['qTangkiAtasTanah12'] = @$qTangkiAtasTanah['1']['1751']['2000'];
	$arrValue['qTangkiAtasTanah13'] = @$qTangkiAtasTanah['1']['2001']['2250'];
	$arrValue['qTangkiAtasTanah14'] = @$qTangkiAtasTanah['1']['2251']['2500'];
	$arrValue['qTangkiAtasTanah15'] = @$qTangkiAtasTanah['1']['2501']['2700'];
	$arrValue['qTangkiAtasTanah16'] = @$qTangkiAtasTanah['1']['2701']['3000'];
	$arrValue['qTangkiAtasTanah17'] = @$qTangkiAtasTanah['1']['3001']['3500'];
	$arrValue['qTangkiAtasTanah18'] = @$qTangkiAtasTanah['1']['3501']['4000'];
	$arrValue['qTangkiAtasTanah19'] = @$qTangkiAtasTanah['1']['4001']['4500'];
	$arrValue['qTangkiAtasTanah20'] = @$qTangkiAtasTanah['1']['4501']['5000'];
	$arrValue['qTangkiAtasTanah21'] = @$qTangkiAtasTanah['1']['5001']['6000'];
	$arrValue['qTangkiAtasTanah22'] = @$qTangkiAtasTanah['1']['6001']['7000'];
	$arrValue['qTangkiAtasTanah23'] = @$qTangkiAtasTanah['1']['7001']['8000'];
	$arrValue['qTangkiAtasTanah24'] = @$qTangkiAtasTanah['1']['8001']['9000'];
	$arrValue['qTangkiAtasTanah25'] = @$qTangkiAtasTanah['1']['9001']['10000'];
	$arrValue['qTangkiAtasTanah26'] = @$qTangkiAtasTanah['1']['10001']['12500'];
	$arrValue['qTangkiAtasTanah27'] = @$qTangkiAtasTanah['1']['12501']['15000'];
	$arrValue['qTangkiAtasTanah28'] = @$qTangkiAtasTanah['1']['15001']['17500'];
	$arrValue['qTangkiAtasTanah29'] = @$qTangkiAtasTanah['1']['17501']['99999'];
	
	//Q 2.1 A
	$qFasNonDep = qFasNonDep();
	$arrValue['qACSplit'] = @$qFasNonDep['01'];
	//Q 2.1 B
	$arrValue['qACWindow'] = @$qFasNonDep['02'];
	//Q 2.1 A.a
	$qNilaiFasDepKlsBintang = qNilaiFasDepKlsBintang();
	$arrValue['qACcentkantor01'] = @$qNilaiFasDepKlsBintang['03']['02']['1'];
	$arrValue['qACcentkantor02'] = @$qNilaiFasDepKlsBintang['03']['02']['2'];
	$arrValue['qACcentkantor03'] = @$qNilaiFasDepKlsBintang['03']['02']['3'];
	$arrValue['qACcentkantor04'] = @$qNilaiFasDepKlsBintang['03']['02']['4'];
	$arrValue['qACcentkantor05'] = @$qNilaiFasDepKlsBintang['03']['02']['5'];

	$arrValue['qACCentHotelkamar01'] = @$qNilaiFasDepKlsBintang['04']['07']['1'];
	$arrValue['qACCentHotelkamar02'] = @$qNilaiFasDepKlsBintang['04']['07']['2'];
	$arrValue['qACCentHotelkamar03'] = @$qNilaiFasDepKlsBintang['04']['07']['3'];
	$arrValue['qACCentHotelkamar04'] = @$qNilaiFasDepKlsBintang['04']['07']['4'];
	
	$arrValue['qACCentHotelRlain01'] = @$qNilaiFasDepKlsBintang['05']['07']['1'];
	$arrValue['qACCentHotelRlain02'] = @$qNilaiFasDepKlsBintang['05']['07']['2'];
	$arrValue['qACCentHotelRlain03'] = @$qNilaiFasDepKlsBintang['05']['07']['3'];
	$arrValue['qACCentHotelRlain04'] = @$qNilaiFasDepKlsBintang['05']['07']['4'];
	
	$arrValue['qACCentToko01'] = @$qNilaiFasDepKlsBintang['06']['04']['1'];
	$arrValue['qACCentToko02'] = @$qNilaiFasDepKlsBintang['06']['04']['2'];
	$arrValue['qACCentToko03'] = @$qNilaiFasDepKlsBintang['06']['04']['3'];
	
	$arrValue['qACCentRSKamar01'] = @$qNilaiFasDepKlsBintang['07']['05']['1'];
	$arrValue['qACCentRSKamar02'] = @$qNilaiFasDepKlsBintang['07']['05']['2'];
	$arrValue['qACCentRSKamar03'] = @$qNilaiFasDepKlsBintang['07']['05']['3'];
	
	$arrValue['qACCentRSRLain01'] = @$qNilaiFasDepKlsBintang['08']['05']['1'];
	$arrValue['qACCentRSRLain02'] = @$qNilaiFasDepKlsBintang['08']['05']['2'];
	$arrValue['qACCentRSRLain03'] = @$qNilaiFasDepKlsBintang['08']['05']['3'];
	
	$arrValue['qACApartKamar01'] = @$qNilaiFasDepKlsBintang['09']['13']['1'];
	$arrValue['qACApartKamar02'] = @$qNilaiFasDepKlsBintang['09']['13']['2'];
	
	$arrValue['qACApartRlain01'] = @$qNilaiFasDepKlsBintang['10']['13']['1'];
	$arrValue['qACApartRlain02'] = @$qNilaiFasDepKlsBintang['10']['13']['2'];
	
	$arrValue['qACCentBangLain'] = @$qFasNonDep['11'];
	
	$arrValue['qBoilerHotel01'] = @$qNilaiFasDepKlsBintang['43']['07']['1'];
	$arrValue['qBoilerHotel02'] = @$qNilaiFasDepKlsBintang['43']['07']['2'];
	$arrValue['qBoilerHotel03'] = @$qNilaiFasDepKlsBintang['43']['07']['3'];
	$arrValue['qBoilerHotel04'] = @$qNilaiFasDepKlsBintang['43']['07']['4'];
	$arrValue['qBoilerHotel05'] = @$qNilaiFasDepKlsBintang['43']['07']['5'];
	
	$arrValue['qBoilerApart01'] = @$qNilaiFasDepKlsBintang['45']['13']['1'];
	$arrValue['qBoilerApart02'] = @$qNilaiFasDepKlsBintang['45']['13']['2'];
	$arrValue['qBoilerApart03'] = @$qNilaiFasDepKlsBintang['45']['13']['3'];
	
	$qNilaiFasDepMinMax = qNilaiFasDepMinMax();
	
	$arrValue['kolplest1'] = @$qNilaiFasDepMinMax['12']['0']['50'];
	$arrValue['kolplest2'] = @$qNilaiFasDepMinMax['12']['51']['100'];
	$arrValue['kolplest3'] = @$qNilaiFasDepMinMax['12']['101']['200'];
	$arrValue['kolplest4'] = @$qNilaiFasDepMinMax['12']['201']['400'];
	$arrValue['kolplest5'] = @$qNilaiFasDepMinMax['12']['401']['999999'];
	
	
	$arrValue['kolpelapis1'] = @$qNilaiFasDepMinMax['13']['0']['50'];
	$arrValue['kolpelapis2'] = @$qNilaiFasDepMinMax['13']['51']['100'];
	$arrValue['kolpelapis3'] = @$qNilaiFasDepMinMax['13']['101']['200'];
	$arrValue['kolpelapis4'] = @$qNilaiFasDepMinMax['13']['201']['400'];
	$arrValue['kolpelapis5'] = @$qNilaiFasDepMinMax['13']['401']['999999'];
	
	$arrValue['kerasringan'] = @$qFasNonDep['14'];
	$arrValue['kerasberat'] = @$qFasNonDep['15'];
	$arrValue['kerassedang'] = @$qFasNonDep['16'];
	$arrValue['keraspenutup'] = @$qFasNonDep['17'];
	
	$arrValue['tenissatulamp1'] = @$qFasNonDep['18'];
	$arrValue['tenissatulamp2'] = @$qFasNonDep['19'];
	$arrValue['tenissatulamp3'] = @$qFasNonDep['20'];
	
	$arrValue['tenisnolamp1'] = @$qFasNonDep['21'];
	$arrValue['tenisnolamp2'] = @$qFasNonDep['22'];
	$arrValue['tenisnolamp3'] = @$qFasNonDep['23'];
	
	$arrValue['tenislsatulamp1'] = @$qFasNonDep['24'];
	$arrValue['tenislsatulamp2'] = @$qFasNonDep['25'];
	$arrValue['tenislsatulamp3'] = @$qFasNonDep['26'];
	
	$arrValue['tenisksatulamp1'] = @$qFasNonDep['27'];
	$arrValue['tenisksatulamp2'] = @$qFasNonDep['28'];
	$arrValue['tenisksatulamp3'] = @$qFasNonDep['29'];
	
	$arrValue['liftbiasa1'] = @$qNilaiFasDepMinMax['30']['0']['4'];
	$arrValue['liftbiasa2'] = @$qNilaiFasDepMinMax['30']['5']['9'];
	$arrValue['liftbiasa3'] = @$qNilaiFasDepMinMax['30']['10']['19'];
	$arrValue['liftbiasa4'] = @$qNilaiFasDepMinMax['30']['20']['99']; 
	
	$arrValue['liftkapsul1'] = @$qNilaiFasDepMinMax['31']['0']['4'];
	$arrValue['liftkapsul2'] = @$qNilaiFasDepMinMax['31']['5']['9'];
	$arrValue['liftkapsul3'] = @$qNilaiFasDepMinMax['31']['10']['19'];
	$arrValue['liftkapsul4'] = @$qNilaiFasDepMinMax['31']['20']['99'];
	
	$arrValue['liftbarang1'] = @$qNilaiFasDepMinMax['32']['0']['4'];
	$arrValue['liftbarang2'] = @$qNilaiFasDepMinMax['32']['5']['9'];
	$arrValue['liftbarang3'] = @$qNilaiFasDepMinMax['32']['10']['19'];
	$arrValue['liftbarang4'] = @$qNilaiFasDepMinMax['32']['20']['99'];
	
	$arrValue['tanggajalan1'] = @$qFasNonDep['33'];
	$arrValue['tanggajalan2'] = @$qFasNonDep['34'];
	
	$arrValue['pagar1'] = @$qFasNonDep['35'];
	$arrValue['pagar2'] = @$qFasNonDep['36'];
	
	$arrValue['protek1'] = @$qFasNonDep['37'];
	$arrValue['protek2'] = @$qFasNonDep['38'];
	$arrValue['protek3'] = @$qFasNonDep['39'];
	
	$arrValue['genset'] = @$qFasNonDep['40'];	
	$arrValue['pabx'] = @$qFasNonDep['41'];	
	$arrValue['artesis'] = @$qFasNonDep['42'];	
	$arrValue['listrik'] = @$qFasNonDep['44'];
	
	$qNilaiMaterial = qNilaiMaterial();
	$arrValue['atap1'] = @$qNilaiMaterial['23']['01'];
	$arrValue['atap2'] = @$qNilaiMaterial['23']['02'];
	$arrValue['atap3'] = @$qNilaiMaterial['23']['03'];
	$arrValue['atap4'] = @$qNilaiMaterial['23']['04'];
	$arrValue['atap5'] = @$qNilaiMaterial['23']['05'];
	
	$arrValue['dinding1'] = @$qNilaiMaterial['21']['01'];
	$arrValue['dinding2'] = @$qNilaiMaterial['21']['09'];
	$arrValue['dinding3'] = @$qNilaiMaterial['21']['02'];
	$arrValue['dinding4'] = @$qNilaiMaterial['21']['03'];
	$arrValue['dinding5'] = @$qNilaiMaterial['21']['07'];
	$arrValue['dinding6'] = @$qNilaiMaterial['21']['08'];
	
	$arrValue['lantai1'] = @$qNilaiMaterial['22']['01'];
	$arrValue['lantai2'] = @$qNilaiMaterial['22']['02'];
	$arrValue['lantai3'] = @$qNilaiMaterial['22']['03'];
	$arrValue['lantai4'] = @$qNilaiMaterial['22']['04'];
	$arrValue['lantai5'] = @$qNilaiMaterial['22']['05'];
	
	$arrValue['langit1'] = @$qNilaiMaterial['24']['01'];
	$arrValue['langit2'] = @$qNilaiMaterial['24']['02'];
	return $arrValue;
}
?>
