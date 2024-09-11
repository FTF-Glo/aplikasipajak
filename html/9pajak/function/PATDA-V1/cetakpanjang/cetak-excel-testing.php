<?php

// ini_set('memory_limit', '2048M');
// if (session_id() == '') {
//   session_start();
// }
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set("log_errors", 1);
// ini_set("error_log", "/tmp/patda-base-v2-error.log");
class Cetak extends Pajak
{
  public function Cari()
  {
    // echo "string";
    // exit();
    $periode = '';
    $periode_bulan = '';
    $where = "(";
    $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
    // $where2 = '';
    if ($this->_mod == "pel") { #pelaporan
      if ($this->_s == 0) { #semua data
        $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ver") { #verifikasi
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "per") { #persetujuan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ply") { #pelayanan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    }
    $where .= ") ";
    //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
    $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
    // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

    // $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "AND CPM_TAHUN_PAJAK <=" . date('Y');
    $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";


    $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
    if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
      $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
      $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
      $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
    }
    return $where;
  }
  //Atb
  public function download_pajak_xls_atb_su()
  {
    // var_dump("asjdai");
    // var_dump($this->Cari());
    // die;
    $where = $this->Cari();

    $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
    $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';
    $z = 0;
    // pengambilan data wp dan npwpd
    $query_wp = "SELECT wp.*,pr.CPM_NAMA_OP FROM patda_wp wp 
      INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD  -- AND pr.CPM_AKTIF = '1' 
      JOIN `PATDA_{$JENIS_PAJAK}_DOC` pj ON pr.`CPM_ID`= pj.CPM_ID_PROFIL
              JOIN `PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN` tr ON pj.CPM_ID= tr.`CPM_TRAN_{$JENIS_PAJAK}_ID`
              JOIN `PATDA_{$JENIS_PAJAK}_DOC_ATR` atr ON pj.`CPM_ID`=atr.`CPM_ATR_{$JENIS_PAJAK}_ID` AND pj.`CPM_MASA_PAJAK`=atr.`CPM_ATR_BULAN`

     
      WHERE  {$where} and  wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' 
      GROUP BY
              CPM_NPWPD
      ORDER BY wp.CPM_KECAMATAN_WP ASC";
    // var_dump($query_wp);
    // die;
    // Pengambilan data Volume dan totakl Pajaknya 


    $queryVolume = "SELECT 
              SUM(pj.CPM_TOTAL_PAJAK) AS CPM_TOTAL_PAJAK,
                      YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
                      YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) AS CPM_YEAR,
                      MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) AS CPM_BULAN,

                      pr.CPM_NPWPD,
                      pr.CPM_NAMA_WP,
                      UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                      pr.CPM_ALAMAT_WP,
                      pr.CPM_ALAMAT_OP,
                      pr.CPM_KECAMATAN_WP,
                      pr.CPM_KECAMATAN_OP,
                      pj.CPM_PERUNTUKAN,
                      atr.CPM_ATR_VOLUME AS CPM_VOLUME
              FROM 
              `PATDA_WP` wp
              INNER JOIN `PATDA_{$JENIS_PAJAK}_PROFIL` pr ON wp.`CPM_NPWPD`= pr.`CPM_NPWPD`
              JOIN `PATDA_{$JENIS_PAJAK}_DOC` pj ON pr.`CPM_ID`= pj.CPM_ID_PROFIL
              JOIN `PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN` tr ON pj.CPM_ID= tr.`CPM_TRAN_{$JENIS_PAJAK}_ID`
              JOIN `PATDA_{$JENIS_PAJAK}_DOC_ATR` atr ON pj.`CPM_ID`=atr.`CPM_ATR_{$JENIS_PAJAK}_ID` AND pj.`CPM_MASA_PAJAK`=atr.`CPM_ATR_BULAN`

              WHERE {$where} and wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' 
              GROUP BY
              CPM_YEAR,CPM_BULAN,CPM_NPWPD
              ORDER BY wp.CPM_KECAMATAN_WP ASC";
    // var_dump($queryVolume);
    die;
    $data = array();
    $res = mysqli_query($this->Conn, $queryVolume);
    $hasil = 0;
    while ($row = mysqli_fetch_assoc($res)) {
      // var_dump($row);die;
      $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];


      switch (TRUE) {
        case ($row['CPM_YEAR'] != $row['TAHUN_LAPOR']):
          $data[$row['CPM_NPWPD']]['tahun'][$row['CPM_YEAR']] = array(
            'CPM_VOLUME' => $row['CPM_VOLUME'],
            'CPM_PAJAK' => $row['CPM_TOTAL_PAJAK']
          );
          $hasil += $row['CPM_TOTAL_PAJAK'];

          break;
        case ($row['CPM_YEAR'] == $row['TAHUN_LAPOR']):
          $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
            'CPM_VOLUME' => $row['CPM_VOLUME'],
            'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']
          );
          $hasil += $row['CPM_TOTAL_PAJAK'];

          break;
      }
    }
    $data_wp = array();
    $res_wp = mysqli_query($this->Conn, $query_wp);

    while ($row = mysqli_fetch_assoc($res_wp)) {
      $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
    }
    foreach ($data_wp as $npwpd => $rowDataWP) {
      $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
    }

    //Menghitung jumlah orang setiap kecamatan
    $jumlah_orang_kecamatan = array();

    foreach ($data_wp as $npwpd => $rawDataWP) {
      $kecamatan = $rawDataWP['CPM_KECAMATAN_WP'];

      if (isset($jumlah_orang_kecamatan[$kecamatan])) {
        $jumlah_orang_kecamatan[$kecamatan]++;
      } else {
        $jumlah_orang_kecamatan[$kecamatan] = 1;
      }
    }

    $jumlah_data = count($data_wp);
    $jumlah_dat = count($data);
    var_dump($jumlah_data, " sahdau ", $jumlah_dat);
    // die;
    $coba = 0;
    $coba1 = 0;
    foreach ($data_wp as $npwpd => $rowDataWP) {
      $rowData = $data[$rowDataWP['CPM_NPWPD']];
      // for ($i = 0; $i <= 12; $i++) {
      //   $coba += $data[$rowDataWP['CPM_NPWPD']["bulan"][$i]];
      // }]
      echo "<pre>";
      var_dump($data[$rowDataWP['CPM_NPWPD']]["tahun"][2022]["CPM_PAJAK"]);
      $coba1 += $data[$rowDataWP['CPM_NPWPD']]["tahun"][2022]['CPM_PAJAK'];

      foreach ($data[$rowDataWP['CPM_NPWPD']]["bulan"] as $key => $val) {
        $coba += $val["CPM_TOTAL_PAJAK"];
        // var_dump($key);
        // var_dump($val);
      }

      // $rowData3 = $data3[$rowDataWP['CPM_NPWPD']];
    }
    var_dump($coba);
    var_dump($coba1);
    var_dump($coba1 + $coba);
    var_dump($hasil);
    die;



    #query select list data

    // $query2 = "SELECT
    //       SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
    //       YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
    //       YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
    //       MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,

    //       pr.CPM_NPWPD,
    //       pr.CPM_NAMA_WP,
    //       UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
    //       pr.CPM_ALAMAT_WP,
    //       pr.CPM_ALAMAT_OP,
    //       pr.CPM_KECAMATAN_WP,
    //       pr.CPM_KECAMATAN_OP,
    //       pj.CPM_PERUNTUKAN,
    //       atr.CPM_ATR_VOLUME as CPM_VOLUME
    //     FROM
    //       PATDA_{$JENIS_PAJAK}_DOC pj
    //       LEFT JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL 
    //       LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
    //       LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_ATR atr ON pj.CPM_ID = atr.CPM_ATR_{$JENIS_PAJAK}_ID AND pj.`CPM_MASA_PAJAK`=atr.`CPM_ATR_BULAN`
    //       WHERE {$where}
    //       GROUP BY CPM_BULAN,CPM_NPWPD,CPM_YEAR,CPM_NO
    //       ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

    // echo "<pre>";
    // print_r($query2);
    // die;
    //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
    // $data = array();

    // $res = mysqli_query($this->Conn, $query2);
    // $hasil = 0;
    // while ($row = mysqli_fetch_assoc($res)) {
    //   // var_dump($row);die;
    //   $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
    //   $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
    //   $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
    //   $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
    //   $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
    //   $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
    //   $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
    //   $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
    //   $hasil += $row['CPM_TOTAL_PAJAK'];

    //   switch (TRUE) {
    //     case ($row['CPM_YEAR'] != $row['TAHUN_LAPOR']):
    //       $data[$row['CPM_NPWPD']]['tahun'][$row['CPM_YEAR']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_PAJAK' => $row['CPM_TOTAL_PAJAK']);

    //       break;
    //     case ($row['CPM_YEAR'] == $row['TAHUN_LAPOR']):
    //       $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);

    //       break;
    //   }
    // }
    // var_dump($hasil);
    // die;



    $where3 = $this->where3_cetak_bentang_tahun();
    $where4 = $this->where3_cetak_bentang();

    $data2 = array();


    $query4 = "SELECT
                  SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
                  -- pj.CPM_TOTAL_PAJAK as CPM_TOTAL_PAJAK,
                  YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
                  YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
                  MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                  pr.CPM_NPWPD,
                  pr.CPM_NAMA_WP,
                  UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                  pr.CPM_ALAMAT_WP,
                  pr.CPM_ALAMAT_OP,
                  pr.CPM_KECAMATAN_WP,
                  pr.CPM_KECAMATAN_OP,
                  -- atr.CPM_ATR_VOLUME as CPM_VOLUME
                  atr.CPM_ATR_VOLUME as CPM_VOLUME
                FROM
                  PATDA_{$JENIS_PAJAK}_DOC pj
                  LEFT JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
                  LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                  LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_ATR atr ON pj.CPM_ID = atr.CPM_ATR_{$JENIS_PAJAK}_ID 
                  WHERE {$where}
                  GROUP BY CPM_BULAN,CPM_NPWPD,CPM_YEAR,CPM_NO
          ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";
    // -- GROUP BY CPM_VOLUME, CPM_NPWPD, CPM_YEAR
    // ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP
    // ";
    // echo "<pre>";
    // print_r($query4);
    // die;

    $data3 = array();
    // $hasil1 = 0;

    $res3 = mysqli_query($this->Conn, $query4);
    // $jumlah_data;
    while ($row = mysqli_fetch_assoc($res3)) {
      $npwpd = $row['CPM_NPWPD'];
      $year = $row['CPM_YEAR'];
      // $data3[$npwpd]['tahun'][$year]['CPM_VOLUME'] += $row['CPM_VOLUME'];
      // $hasil1 += $row['CPM_TOTAL_PAJAK'];

      if (!isset($data3[$npwpd])) {
        $data3[$npwpd] = array(
          'CPM_NPWPD' => $npwpd,
          'CPM_NAMA_WP' => $row['CPM_NAMA_WP'],
          'CPM_NAMA_OP' => $row['CPM_NAMA_OP'],
          'CPM_ALAMAT_WP' => $row['CPM_ALAMAT_WP'],
          'CPM_ALAMAT_OP' => $row['CPM_ALAMAT_OP'],
          'CPM_KECAMATAN_OP' => $row['CPM_KECAMATAN_OP'],
          'PAJAK_TOT' => $row['CPM_TOTAL_PAJAK'],
          'tahun' => array()
        );
      }

      if (!isset($data3[$npwpd]['tahun'][$year])) {
        $data3[$npwpd]['tahun'][$year] = array(
          'CPM_VOLUME' => array($row['CPM_VOLUME']), // Initialize as an array with current value

          // 'CPM_TAHUN_TOTAL' => array_sum($row['CPM_VOLUME']), // initialize to 0
          'CPM_TAHUN_TOTAL' => array_sum(explode(',', $row['CPM_VOLUME'])),
        );
      } else {
        $data3[$npwpd]['tahun'][$year]['CPM_VOLUME'][] = $row['CPM_VOLUME']; // Add the current value to the existing array
        $data3[$npwpd]['tahun'][$year]['CPM_TAHUN_TOTAL'] = array_sum($data3[$npwpd]['tahun'][$year]['CPM_VOLUME']);
        // $data3[$npwpd]['tahun'][$year]['RP_TOTAL'][] = $row['CPM_TOTAL_PAJAK'];
      }


      // $data3[$npwpd]['tahun'][$year]['CPM_VOLUME'] += $row['CPM_VOLUME'];
    }

    var_dump($hasil);
    // var_dump($hasil1);

    $data_wp = array();
    $res_wp = mysqli_query($this->Conn, $query_wp);

    while ($row = mysqli_fetch_assoc($res_wp)) {
      $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
    }

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set properties
    $objPHPExcel->getProperties()->setCreator("vpost")
      ->setLastModifiedBy("vpost")
      ->setTitle("9 PAJAK ONLINE")
      ->setSubject("-")
      ->setDescription("bphtb")
      ->setKeywords("9 PAJAK ONLINE");

    switch (TRUE) {
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = date('Y');
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] != "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = $_REQUEST['CPM_TAHUN_PAJAK'];
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] != ""):

        $tahun_pajak_label =  date("Y", strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        break;
      default:
        $tahun_pajak_label = date('Y');
        break;
    }

    // Add some data

    $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "Triwulan IV " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "Triwulan IV " . (date('Y') - 1);

    $pajakk = '';
    $SPTPD = '';
    if ($JENIS_PAJAK === 'MINERAL') {
      $pajakk = 'MINERBA';
      $SPTPD = 'SPTPD';
    } elseif ($JENIS_PAJAK === 'AIRBAWAHTANAH') {
      $pajakk = 'PAJAK AIR TANAH';
      $SPTPD = 'SKPD';
    }


    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('A1', 'PEMERINTAH KABUPATEN LAMPUNG SELATAN')
      ->setCellValue('A2', 'REKAPITULASI ' . $SPTPD . '  ' . $pajakk)

      ->setCellValue('A3', 'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH')
      ->setCellValue('A4', 'MASA PAJAK TRIWULAN I s/d TRIWULAN IV ' . $tahun_pajak_label . '')
      ->setCellValue('A5', 'BIDANG PENGEMBANGAN DAN PENETAPAN')

      ->setCellValue('A7', 'NO.')
      ->setCellValue('B7', 'NAMA OBJEK PAJAK.')
      ->setCellValue('D7', 'PAJAK ' . $JENIS_PAJAK . ' TAHUN ')
      ->setCellValue('C8', $tahun_pajak_label)
      ->setCellValue('C7', 'TAPBOX.')
      ->setCellValue('I9', 'TRIWULAN I')
      ->setCellValue('J9', 'TRIWULAN II')
      ->setCellValue('K9', 'TRIWULAN III')
      ->setCellValue('L9', 'TRIWULAN IV')
      ->setCellValue('M7', 'VOLUME AIR M3')
      ->setCellValue('V7', 'JUMLAH VOLUME AIR. M3');

    switch (true) {
      case ($JENIS_PAJAK == 'AIRBAWAHTANAH'):
        $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('R9', 'TRIWULAN I')
          ->setCellValue('S9', 'TRIWULAN II')
          ->setCellValue('T9', 'TRIWULAN III')
          ->setCellValue('U9', 'TRIWULAN IV')
          ->setCellValue('W7', 'JUMLAH.');
        for ($i = 0; $i < 6; $i++) {
          $bar = 8;
          $column = PHPExcel_Cell::columnIndexFromString('Q') - $i; // hitung kolom baru
          $column_letter = PHPExcel_Cell::stringFromColumnIndex($column); // konversi angka kolom ke huruf
          $cell = $column_letter . $bar; // gabungkan huruf kolom dan nomor baris untuk membentuk string sel
          //  echo $cell;

          $year = $tahun_pajak_label - $i;

          $objPHPExcel->setActiveSheetIndex($z)
            ->setCellValue($cell, $year);
        }

        break;
    }

    if ($JENIS_PAJAK == 'AIRBAWAHTANAH') {
      for ($i = 0; $i < 6; $i++) {
        $bar = 8;
        $column = PHPExcel_Cell::columnIndexFromString('H') - $i; // hitung kolom baru
        $column_letter = PHPExcel_Cell::stringFromColumnIndex($column); // konversi angka kolom ke huruf
        $cell = $column_letter . $bar; // gabungkan huruf kolom dan nomor baris untuk membentuk string sel
        //  echo $cell;

        $year = $tahun_pajak_label - $i;

        $objPHPExcel->setActiveSheetIndex($z)
          ->setCellValue($cell, $year);
      }
      // die;
    }
    // var_dump($data);
    // die;

    // judul dok

    $objPHPExcel->getActiveSheet()->mergeCells("A1:M1");
    $objPHPExcel->getActiveSheet()->mergeCells("A2:M2");
    $objPHPExcel->getActiveSheet()->mergeCells("A3:M3");
    $objPHPExcel->getActiveSheet()->mergeCells("A4:M4");
    $objPHPExcel->getActiveSheet()->mergeCells("A5:M5");
    //$objPHPExcel->getActiveSheet()->mergeCells("A6:M6");
    $objPHPExcel->getActiveSheet()->mergeCells("I8:L8");
    $objPHPExcel->getActiveSheet()->mergeCells("R8:U8");
    $objPHPExcel->getActiveSheet()->mergeCells("M7:U7");

    $objPHPExcel->getActiveSheet()->mergeCells("A7:A9");
    $objPHPExcel->getActiveSheet()->mergeCells("B7:B9");
    $objPHPExcel->getActiveSheet()->mergeCells("C7:C9");
    $objPHPExcel->getActiveSheet()->mergeCells("D7:L7");


    // Miscellaneous glyphs, UTF-8
    $objPHPExcel->setActiveSheetIndex(0);

    $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
    $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
    $tab = $jns[$this->_s];
    $jml = 0;

    $row = 10;
    $sumRows = mysqli_num_rows($res);
    $total_pajak = 0;

    foreach ($data_wp as $npwpd => $rowDataWP) {
      $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
    }

    //Menghitung jumlah orang setiap kecamatan
    $jumlah_orang_kecamatan = array();

    foreach ($data_wp as $npwpd => $rawDataWP) {
      $kecamatan = $rawDataWP['CPM_KECAMATAN_WP'];

      if (isset($jumlah_orang_kecamatan[$kecamatan])) {
        $jumlah_orang_kecamatan[$kecamatan]++;
      } else {
        $jumlah_orang_kecamatan[$kecamatan] = 1;
      }
    }

    $jumlah_data = count($data_wp);
    // var_dump($data['P10000206113']['tahun']);
    // die;
    $coba = 0;
    foreach ($data_wp as $npwpd => $rowDataWP) {
      $rowData = $data[$rowDataWP['CPM_NPWPD']];
      $rowData3 = $data3[$rowDataWP['CPM_NPWPD']];
      // echo "<pre>";
      // var_dump($rowData['bulan']);
      // die;
      // var_dump($data3);

      // $rowData3 = $data2[$rowDataWP['CPM_NPWPD']];
      // print_r($cek_kecamatan);
      // die(var_dump($rowDataWP['CPM_KECAMATAN_WP']));


      if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
        $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);

        // die(var_dump("okay lajh"));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row,  $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row,  $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row,  $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row,  $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row,  $tahun5);
        //  $objPHPExcel->getActiveSheet()->getStyle($clm . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $jan);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $feb);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mar);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $apr);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $mei);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $jun);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $jul);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $agu);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $sep);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $okt);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $nov);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $des);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row,  $total_pajak);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {

          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':U' . $space);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffffff');
          $row++;
        }

        $no = 0;
        $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        $row++;
      }
      // die(var_dump("sini atuh"));

      if ($rowDataWP['CPM_KECAMATAN_WP']) {

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {

          $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':U' . $row);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );

          $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
          $row++;

          $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
        }
      }

      $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
      $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
      $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
      $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_OP'], PHPExcel_Cell_DataType::TYPE_STRING);

      $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');
      $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData3['tahun'][$year]['CPM_TOTAL_PAJAK'] + 0);

      $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData3['tahun'][$year + 1]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData3['tahun'][$year + 2]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData3['tahun'][$year + 3]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData3['tahun'][$year + 4]['CPM_TOTAL_PAJAK'] + 0);
      // $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK']);
      $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK']);
      $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK']);
      $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);

      $volarr0 = [];
      $volarr1 = [];
      $volarr2 = [];
      $volarr3 = [];
      $volarr4 = [];


      if (isset($rowData3['tahun'][$year])) {
        $temp_thn = $rowData3['tahun'][$year];
        foreach ($temp_thn['CPM_VOLUME'] as $r) array_push($volarr0, $r);
        $volarr0 = implode(' - ', $volarr0);
      } else {
        $volarr0 = 0;
      }

      if (isset($rowData3['tahun'][$year + 1])) {
        $temp_thn = $rowData3['tahun'][$year + 1];
        foreach ($temp_thn['CPM_VOLUME'] as $r) array_push($volarr1, $r);
        $volarr1 = implode(' - ', $volarr1);
      } else {
        $volarr1 = 0;
      }

      if (isset($rowData3['tahun'][$year + 2])) {
        $temp_thn = $rowData3['tahun'][$year + 2];
        foreach ($temp_thn['CPM_VOLUME'] as $r) array_push($volarr2, $r);
        $volarr2 = implode(' - ', $volarr2);
      } else {
        $volarr2 = 0;
      }

      if (isset($rowData3['tahun'][$year + 3])) {
        $temp_thn = $rowData3['tahun'][$year + 3];
        foreach ($temp_thn['CPM_VOLUME'] as $r) array_push($volarr3, $r);
        $volarr3 = implode(' - ', $volarr3);
      } else {
        $volarr3 = 0;
      }

      if (isset($rowData3['tahun'][$year + 4])) {
        $temp_thn = $rowData3['tahun'][$year + 4];
        foreach ($temp_thn['CPM_VOLUME'] as $r) array_push($volarr4, $r);
        $volarr4 = implode(' - ', $volarr4);
      } else {
        $volarr4 = 0;
      }

      $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $volarr0);
      $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $volarr1);
      $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $volarr2);
      $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $volarr3);
      $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $volarr4);


      $triwul1 = 0;
      $triwul2 = 0;
      $triwul3 = 0;
      $triwul4 = 0;

      $totTriwul1 = 0;
      $totTriwul2 = 0;
      $totTriwul3 = 0;
      $totTriwul4 = 0;

      if (isset($rowData3['tahun'][2023])) {
        $temp_thn = $rowData3['tahun'][2023];

        if (isset($temp_thn['CPM_VOLUME'][0]) || isset($temp_thn['CPM_VOLUME'][1]) || isset($temp_thn['CPM_VOLUME'][2])) {
          $triwul1 = implode(' - ', array($temp_thn['CPM_VOLUME'][0], $temp_thn['CPM_VOLUME'][1], $temp_thn['CPM_VOLUME'][2]));
          $totTriwul1 = $temp_thn['CPM_VOLUME'][0] + $temp_thn['CPM_VOLUME'][1] + $temp_thn['CPM_VOLUME'][2];
        }

        if (isset($temp_thn['CPM_VOLUME'][3]) || isset($temp_thn['CPM_VOLUME'][4]) || isset($temp_thn['CPM_VOLUME'][5])) {
          $triwul2 = implode(' - ', array($temp_thn['CPM_VOLUME'][3], $temp_thn['CPM_VOLUME'][4], $temp_thn['CPM_VOLUME'][5]));
          $totTriwul2 = $temp_thn['CPM_VOLUME'][3] + $temp_thn['CPM_VOLUME'][4] + $temp_thn['CPM_VOLUME'][5];
        }

        if (isset($temp_thn['CPM_VOLUME'][6]) || isset($temp_thn['CPM_VOLUME'][7]) || isset($temp_thn['CPM_VOLUME'][8])) {
          $triwul3 = implode(' - ', array($temp_thn['CPM_VOLUME'][6], $temp_thn['CPM_VOLUME'][7], $temp_thn['CPM_VOLUME'][8]));
          $totTriwul3 = $temp_thn['CPM_VOLUME'][6] + $temp_thn['CPM_VOLUME'][7] + $temp_thn['CPM_VOLUME'][8];
        }

        if (isset($temp_thn['CPM_VOLUME'][9]) || isset($temp_thn['CPM_VOLUME'][10]) || isset($temp_thn['CPM_VOLUME'][11])) {
          $triwul4 = implode(' - ', array($temp_thn['CPM_VOLUME'][9], $temp_thn['CPM_VOLUME'][10], $temp_thn['CPM_VOLUME'][11]));
          $totTriwul4 = $temp_thn['CPM_VOLUME'][9] + $temp_thn['CPM_VOLUME'][10] + $temp_thn['CPM_VOLUME'][11];
        }
      }

      // $grandTot = 0;

      // foreach ($rowData3['tahun'] as $tahun => $data) {
      //     if (isset($data['CPM_VOLUME'])) {
      //       foreach ($data['CPM_VOLUME'] as $r){
      //         $grandTot += (float)(str_replace('.','',$r));
      //       }
      //     }
      // }



      $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $triwul1);
      $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $triwul2);
      $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $triwul3);
      $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $triwul4);
      // echo"<pre>";
      // print_r($totTriwul1);die;


      $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $totTriwul1 + $totTriwul2 + $totTriwul3 + $totTriwul4);

      $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, $rowData['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowData['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowData['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] +  $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);

      if ($nama_kecamatan != $nama_kecamatans) {
        $total_pajak = 0;
        $tahun1 = 0;
        $tahun2 = 0;
        $tahun3 = 0;
        $tahun4 = 0;
        $tahun5 = 0;
        $triwulan_satu = 0;
        $triwulan_dua = 0;
        $triwulan_tiga = 0;
        $triwulan_empat = 0;
        $totalair = 0;
        $airtahun1 = 0;
        $airtahun2 = 0;
        $airtahun3 = 0;
        $airtahun4 = 0;
        $airtahun5 = 0;
        $airtriwulan1  = 0;
        $airtriwulan2 = 0;
        $airtriwulan3  = 0;
        $airtriwulan4 = 0;
        $totaldesbelum = 0;
      }
      // var_dump($rowData3["tahun"]);
      // die;
      $tahun1 += ($rowData3['tahun'][$year]['CPM_TAHUN_TOTAL'] == 'null') ? 0 : $rowData3['tahun'][$year]['CPM_TAHUN_TOTAL'] == 'null' + 0;
      $tahun2 += $rowData3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $tahun3 += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $tahun4 += $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $tahun5 += $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
      // var_dump(($rowData3['tahun'][$year + 1] == 'NULL') ? 0 : $rowData3['tahun'][$year]['CPM_VOLUME']);
      // var_dump($rowData3['tahun'][$year + 1]['CPM_VOLUME'][1]);
      // die;
      $sumvoltahun1 = $rowData3['tahun'][$year]['CPM_VOLUME'][0] + $rowData3['tahun'][$year]['CPM_VOLUME'][1] + $rowData3['tahun'][$year]['CPM_VOLUME'][2] + 0;
      $sumvoltahun2 = $rowData3['tahun'][$year + 1]['CPM_VOLUME'][0] + $rowData3['tahun'][$year + 1]['CPM_VOLUME'][1] + $rowData3['tahun'][$year + 1]['CPM_VOLUME'][2] + 0;
      $sumvoltahun3 = $rowData3['tahun'][$year + 2]['CPM_VOLUME'][0] + $rowData3['tahun'][$year + 2]['CPM_VOLUME'][1] + $rowData3['tahun'][$year + 2]['CPM_VOLUME'][2] + 0;
      $sumvoltahun4 = $rowData3['tahun'][$year + 3]['CPM_VOLUME'][0] + $rowData3['tahun'][$year + 3]['CPM_VOLUME'][1] + $rowData3['tahun'][$year + 3]['CPM_VOLUME'][2] + 0;
      $sumvoltahun5 = $rowData3['tahun'][$year + 4]['CPM_VOLUME'][0] + $rowData3['tahun'][$year + 4]['CPM_VOLUME'][1] + $rowData3['tahun'][$year + 4]['CPM_VOLUME'][2] + 0;



      $airtahun1 += $sumvoltahun1 + 0;
      $airtahun2 += $sumvoltahun2 + 0;
      $airtahun3 += $sumvoltahun3 + 0;
      $airtahun4 += $sumvoltahun4 + 0;
      $airtahun5 += $sumvoltahun5 + 0;


      // $test =  $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
      // die;
      // $rowData3['tahun'][$year + 2]['CPM_VOLUME'] + $rowData3['tahun'][$year + 3]['CPM_VOLUME'] + $rowData3['tahun'][$year + 4]['CPM_VOLUME'];
      $totalair +=  $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME'] + $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME'] + $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME'] + $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME'];

      $airtriwulan1 += $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME'] + 0;
      $airtriwulan2 += $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME'] + 0;
      $airtriwulan3 += $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME'] + 0;
      $airtriwulan4 += $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME'] + 0;

      $total_pajak += $rowData['tahun'][$year + 2]['CPM_PAJAK'] + $rowData['tahun'][$year + 3]['CPM_PAJAK'] + $rowData['tahun'][$year + 4]['CPM_PAJAK'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $triwulan_satu += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_dua += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_tiga += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_empat += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      // var_dump($year + 4);
      var_dump("sa", $rowData3["tahun"]);
      $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

      $pertama = ($rowData3['tahun'][$year + 2]['CPM_PAJAK'] == NULL) ? 0 : $rowData3['tahun'][$year + 2]['CPM_PAJAK'];
      $dua = ($rowData3['tahun'][$year + 3]['CPM_PAJAK'] == NULL) ? 0 : $rowData3['tahun'][$year + 3]['CPM_PAJAK'];
      $tiga = ($rowData3['tahun'][$year + 4]['CPM_PAJAK'] == NULL) ? 0 : $rowData3['tahun'][$year + 4]['CPM_PAJAK'];
      $tiga3 = ($rowData['tahun'][$year + 5]['CPM_PAJAK'] == NULL) ? 0 : $rowData['tahun'][$year + 4]['CPM_PAJAK'];
      $tiga1 = ($rowData['tahun'][$year + 1]['CPM_PAJAK'] == NULL) ? 0 : $rowData['tahun'][$year + 1]['CPM_PAJAK'];
      $tiga2 = ($rowData['tahun'][$year]['CPM_PAJAK'] == NULL) ? 0 : $rowData['tahun'][$year]['CPM_PAJAK'];

      $coba +=   $pertama + $dua + $tiga + $tiga1 + $tiga2 + $tiga3 + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      // echo $coba;
      //untuk total
      $total_total_pajak += $rowData['tahun'][$year + 2]['CPM_PAJAK'] + $rowData['tahun'][$year + 3]['CPM_PAJAK'] + $rowData['tahun'][$year + 4]['CPM_PAJAK'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $totalairtotal += $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME'] + $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME'] + $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME'] + $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME'];

      $totalairtriwulan1 += $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME'] + 0;
      $totalairtriwulan2 += $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME'] + 0;
      $totalairtriwulan3 += $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME'] + 0;
      $totalairtriwulan4 += $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME'] + 0;
      // $totaldesbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $totalairtahun1 += $sumvoltahun1 + 0;
      $totalairtahun2 += $sumvoltahun2 + 0;
      $totalairtahun3 += $sumvoltahun3 + 0;
      $totalairtahun4 += $sumvoltahun4 + 0;
      $totalairtahun5 += $sumvoltahun5 + 0;
      // $totalairtahun1 += $rowData3['tahun'][$year]['CPM_VOLUME'] + 0;
      // $totalairtahun2 += $rowData3['tahun'][$year + 1]['CPM_VOLUME'] + 0;
      // $totalairtahun3 += $rowData3['tahun'][$year + 2]['CPM_VOLUME'] + 0;
      // $totalairtahun4 += $rowData3['tahun'][$year + 3]['CPM_VOLUME'] + 0;
      // $totalairtahun5 += $rowData3['tahun'][$year + 4]['CPM_VOLUME'] + 0;

      $total_triwulan_satu += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $total_triwulan_dua += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $total_triwulan_tiga += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $total_triwulan_empat += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      // $totaldesbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $total_tahun1 += $rowData3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun2 += $rowData3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun3 += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun4 += $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun5 += $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
      //var_dump($total_pajak);die;

      $jml++;
      $row++;
      $no++;
      // var_dump($airtahun1);die;

      if ($jumlah_data == $jml) {
        // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tahun5);

        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $triwulan_satu);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $triwulan_dua);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $triwulan_tiga);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $triwulan_empat);

        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $airtahun1, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $airtahun2, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $airtahun3, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $airtahun4, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $airtahun5, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);

        $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $airtriwulan1, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $airtriwulan2, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $airtriwulan3, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $airtriwulan4, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);

        $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $totalair, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, $total_pajak);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );


        if ($jumlah_data == $jml) {
          // var_dump($row);die;
          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");

          $objPHPExcel->getActiveSheet()->setCellValue('D' . $space, $total_tahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $total_tahun2);
          $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $total_tahun3);
          $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $total_tahun4);
          $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_tahun5);

          $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_triwulan_satu);
          $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_triwulan_dua);
          $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_triwulan_tiga);
          $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_triwulan_empat);


          $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $totalairtahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $totalairtahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $totalairtahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $totalairtahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $totalairtahun1);

          $objPHPExcel->getActiveSheet()->setCellValue('R' . $space, $totalairtriwulan2);
          $objPHPExcel->getActiveSheet()->setCellValue('S' . $space, $totalairtriwulan2);
          $objPHPExcel->getActiveSheet()->setCellValue('T' . $space, $totalairtriwulan2);
          $objPHPExcel->getActiveSheet()->setCellValue('U' . $space, $totalairtriwulan2);

          $objPHPExcel->getActiveSheet()->setCellValue('V' . $space, $totalairtotal);
          $objPHPExcel->getActiveSheet()->setCellValue('W' . $space, $total_total_pajak);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }


        if ($jumlah_data == $jml) {
          //var_dump($row);die;
          $space = $row + 3;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':D' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN JUMLAH WP PERKECAMATAN");


          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->getStartColor()->setRGB('ffff00');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }

        // var_dump($jumlah_data);
        // die;
        $space = $space + 1;
        $no_keterangan = 0;
        $total_wp = 0;
        // $query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_JENIS_PAJAK like '%{$this->_idp}%'  GROUP BY CPM_KECAMATAN_WP  ORDER BY CPM_KECAMATAN_WP ASC";

        // $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
        // while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
        //   $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
        //   $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, $row_keterangan['CPM_KECAMATAN_WP']);
        //   $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $row_keterangan['TOTAL']);
        //   $totalwp += $row_keterangan['TOTAL'] + 0;

        //   $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->getStartColor()->setRGB('ffff00');
        //   $space++;
        //   $no_keterangan++;


        // Menampilkan hasil jumlah orang dalam setiap kecamatan
        foreach ($jumlah_orang_kecamatan as $kecamatan => $jumlah) {
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
          $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . ($kecamatan == '' ? "LAIN " : $kecamatan));
          $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $jumlah);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
          $space++;
          $no_keterangan++;
          $total_wp += $jumlah;
        }
        // CODINGAN UNTUK MENAMPILKAN TOTAL WP DI CETAK BENTANG PANJANG
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($space), "TOTAL");
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($space), $total_wp);
      }
    }
    // exit('sada');
    /** style **/
    // judul dok + judul tabel
    $objPHPExcel->getActiveSheet()->getStyle('A1:M5')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A7:W9')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A5:W7')->getAlignment()->setWrapText(true);

    $objPHPExcel->getActiveSheet()->getStyle('A7:W' . $row)->applyFromArray(
      array(
        'borders' => array(
          'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
          )
        )
      )
    );
    // var_dump("sinikah");
    // die;

    // fill tabel header
    $objPHPExcel->getActiveSheet()->getStyle('A7:W9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A7:W9')->getFill()->getStartColor()->setRGB('E4E4E4');

    // format angka col I & K
    $objPHPExcel->getActiveSheet()->getStyle('E10:L' . $row)->getNumberFormat()->setFormatCode('#,##0');
    $objPHPExcel->getActiveSheet()->getStyle('W10:W' . $row)->getNumberFormat()->setFormatCode('#,##0');



    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak ' . $tab);

    var_dump($coba);
    die;
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
    for ($x = "A"; $x <= "H"; $x++) {
      if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
      else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
    }
    ob_clean();
    // Redirect output to a client’s web browser (Excel5)

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
    $objWriter->save('php://output');
    mysqli_close($this->Conn);
  }
}
